<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class SocialItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetSocialItemById()
        {
            $super                     = User::getByUsername('super');
            $fileModel                 = ZurmoTestHelper::createFileModel();
            $accounts                  = Account::getByName('anAccount');
            $steven                    = UserTestHelper::createBasicUser('steven');
            $note                      = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('aNote', $super, $accounts[0]);

            $socialItem              = new SocialItem();
            $socialItem->owner       = $super;
            $socialItem->description = 'My test description';
            $socialItem->note        = $note;
            $socialItem->files->add($fileModel);
            $this->assertTrue($socialItem->save());

            $id = $socialItem->id;
            $socialItem->forget();
            unset($socialItem);

            $socialItem = SocialItem::getById($id);
            $this->assertEquals($super,                           $socialItem->owner);
            $this->assertEquals('My test description',            $socialItem->description);
            $this->assertEquals($super,                           $socialItem->createdByUser);
            $this->assertEquals($note,                                $socialItem->note);
            $this->assertEquals(1,                                $socialItem->files->count());
            $this->assertEquals($fileModel,                       $socialItem->files->offsetGet(0));
        }

        /**
         * @depends testCreateAndGetSocialItemById
         */
        public function testAddingComments()
        {
            $socialItems = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $socialItem  = $socialItems[0];
            $steven        = User::getByUserName('steven');
            $latestStamp   = $socialItem->latestDateTime;

            //latestDateTime should not change when just saving the conversation
            $socialItem->conversationParticipants->offsetGet(0)->hasReadLatest = true;
            $socialItem->ownerHasReadLatest                                    = true;
            $this->assertTrue($socialItem->save());
            $this->assertEquals($latestStamp, $socialItem->latestDateTime);
            $this->assertEquals(1, $socialItem->ownerHasReadLatest);

            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Add comment, this should update the latestDateTime,
            //and also it should reset hasReadLatest on conversation participants
            $comment              = new Comment();
            $comment->description = 'This is my first comment';
            $socialItem->comments->add($comment);
            $this->assertTrue($socialItem->save());
            $this->assertNotEquals($latestStamp, $socialItem->latestDateTime);
            $this->assertEquals(0, $socialItem->conversationParticipants->offsetGet(0)->hasReadLatest);
            //super made the comment, so this should remain the same.
            $this->assertEquals(1, $socialItem->ownerHasReadLatest);

            //set it to read latest
            $socialItem->conversationParticipants->offsetGet(0)->hasReadLatest = true;
            $this->assertTrue($socialItem->save());
            $this->assertEquals(1, $socialItem->conversationParticipants->offsetGet(0)->hasReadLatest);

            //have steven make the comment. Now the ownerHasReadLatest should set to false, and hasReadLatest should remain true
            Yii::app()->user->userModel = $steven;
            $socialItem               = Conversation::getById($socialItem->id);
            $comment                    = new Comment();
            $comment->description       = 'This is steven`\s first comment';
            $socialItem->comments->add($comment);
            $this->assertTrue($socialItem->save());
            $this->assertEquals(1, $socialItem->conversationParticipants->offsetGet(0)->hasReadLatest);
            $this->assertEquals(0, $socialItem->ownerHasReadLatest);
        }

        /**
         * @depends testAddingComments
         */
        public function testResolveConversationParticipantsForExplicitModelPermissions()
        {
            $super                     = User::getByUsername('super');
            $steven                    = User::getByUsername('steven');
            $sally                     = UserTestHelper::createBasicUser('sally');
            $mary                      = UserTestHelper::createBasicUser('mary');

            $socialItem              = new Conversation();
            $socialItem->owner       = $super;
            $socialItem->subject     = 'My test subject2';
            $socialItem->description = 'My test description2';
            $this->assertTrue($socialItem->save());

            //Set explicitPermissions. Should not add any at this point
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($socialItem);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));

            //Attempt to resolve against posted conversationParticipants data
            $postData = array();
            $postData['itemIds'] = $super->getClassId('Item');
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $socialItem, $postData, $explicitReadWriteModelPermissions);
            //Should still be 0, because super is the owner, and would not be specially added. (This is just a safety test here)
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, $socialItem->conversationParticipants->count());

            //Add steven as a conversation participant.
            $postData = array();
            $postData['itemIds'] = $super->getClassId('Item') . ',' . $steven->getClassId('Item'); // Not Coding Standard
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost($socialItem,
                                                                                         $postData,
                                                                                         $explicitReadWriteModelPermissions);
            $this->assertTrue($socialItem->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($socialItem, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);

            //At this point there should be one readWritePermitable.  "Steven"
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($socialItem);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals($steven, $readWritePermitables[$steven->id]);
            $this->assertEquals(1, $socialItem->conversationParticipants->count());
            $this->assertEquals($steven, $socialItem->conversationParticipants[0]->person);
        }

        /**
         * @depends testResolveConversationParticipantsForExplicitModelPermissions
         */
        public function testGetUnreadConversationCount()
        {
            $super                     = User::getByUsername('super');
            $mary                      = User::getByUsername('mary');
            $count                     = Conversation::getUnreadCountByUser($super);
            $account2                  = AccountTestHelper::createAccountByNameForOwner('anAccount2', $super);

            $socialItem              = new Conversation();
            $socialItem->owner       = $super;
            $socialItem->subject     = 'My test subject2';
            $socialItem->description = 'My test description2';
            $socialItem->conversationItems->add($account2);
            $this->assertTrue($socialItem->save());

            //when super adds a comment, it should remain same count
            $comment                   = new Comment();
            $comment->description      = 'This is my first comment';
            $socialItem->comments->add($comment);
            $this->assertTrue($socialItem->save());
            $count                     = Conversation::getUnreadCountByUser($super);
            $this->assertEquals(1, $count);

            //Add mary as a participant
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($socialItem);
            $postData            = array();
            $postData['itemIds'] = $super->getClassId('Item') . ',' . $mary->getClassId('Item'); // Not Coding Standard
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost($socialItem,
                                                                                         $postData,
                                                                                         $explicitReadWriteModelPermissions);
            $success              = ExplicitReadWriteModelPermissionsUtil::
                                        resolveExplicitReadWriteModelPermissions($socialItem,
                                                                                 $explicitReadWriteModelPermissions);
            $this->assertTrue($success);
            $socialItem->save();

            //when mary adds a comment, super's count should go up (assumming count was previously 0)
            Yii::app()->user->userModel = $mary;
            $comment                    = new Comment();
            $comment->description       = 'This is mary\'s first comment';
            $socialItem->comments->add($comment);
            $this->assertTrue($socialItem->save());
            Yii::app()->user->userModel = $super;
            $count                      = Conversation::getUnreadCountByUser($super);
            $this->assertEquals(2, $count);
        }

        /**
         * @depends testGetUnreadConversationCount
         */
        public function testDeleteConversation()
        {
            $socialItems = Conversation::getAll();
            $this->assertEquals(3, count($socialItems));
            $comments = Comment::getAll();
            $this->assertEquals(4, count($comments));

            //check count of conversation_items
            $count   = R::getRow('select count(*) count from conversation_item');
            $this->assertEquals(2, $count['count']);

            //remove the account, tests tthat ConversationObserver is correctly removing data from conversation_item
            $accounts                  = Account::getByName('anAccount2');
            $this->assertTrue($accounts[0]->delete());

            $count   = R::getRow('select count(*) count from conversation_item');
            $this->assertEquals(1, $count['count']);

            foreach ($socialItems as $socialItem)
            {
                $socialItemId = $socialItem->id;
                $socialItem->forget();
                $socialItem   = Conversation::getById($socialItemId);
                $deleted        = $socialItem->delete();
                $this->assertTrue($deleted);
            }

            //Count of conversation items should be 0 since the ConversationsObserver should make sure it gets removed correctly.
            $count   = R::getRow('select count(*) count from conversation_item');
            $this->assertEquals(0, $count['count']);

            //check that all comments are removed, since they are owned.
            $comments = Comment::getAll();
            $this->assertEquals(0, count($comments));
        }
    }
?>