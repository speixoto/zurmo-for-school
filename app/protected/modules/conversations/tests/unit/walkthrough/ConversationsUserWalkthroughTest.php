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

    /**
     * Conversations Module Super User Walkthrough.
     * Walkthrough for the users of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ConversationsUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create test users
            $steven                             = UserTestHelper::createBasicUser('steven');
            $steven->primaryEmail->emailAddress = 'steven@testzurmo.com';
            $sally                              = UserTestHelper::createBasicUser('sally');
            $sally->primaryEmail->emailAddress  = 'sally@testzurmo.com';
            $mary                               = UserTestHelper::createBasicUser('mary');
            $mary->primaryEmail->emailAddress  = 'mary@testzurmo.com';

            //give 3 users access, create, delete for conversation rights.
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $steven->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $sally->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $mary->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function testSuperUserAllSimpleControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/create');
        }

        /**
         * @depends testSuperUserAllSimpleControllerActions
         */
        public function testSuperUserCreateConversation()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            //Test creating conversation via POST, invite Mary
            $conversations = Conversation::getAll();
            $this->assertEquals(0, count($conversations));
            $itemPostData = array('Account' => array('id' => $superAccountId));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item')),
                                      'ConversationItemForm'         => $itemPostData,
                                      'Conversation'                 => array('subject' => 'TestSubject',
                                                                          'description' => 'TestDescription')));
            $this->runControllerWithRedirectExceptionAndGetContent('conversations/default/create');

            //Confirm conversation saved.
            $conversations = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            //Confirm conversation is connected to the related account.
            $this->assertEquals(1, $conversations[0]->conversationItems->count());
            $this->assertEquals($accounts[0], $conversations[0]->conversationItems->offsetGet(0));

            //Confirm Mary is invited.
            $this->assertEquals(1,     $conversations[0]->conversationParticipants->count());
            $this->assertEquals($mary, $conversations[0]->conversationParticipants->offsetGet(0)->person);
            $this->assertNull($conversations[0]->conversationParticipants->offsetGet(0)->hasReadLatest);

            //Confirm Mary is the only one with explicit permissions on the conversation
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$mary->id]));
        }

        /**
         * @depends testSuperUserCreateConversation
         */
        public function testInvitingAndUnivitingUsersOnExistingConversation()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Test inviting steven and sally (via detailview)
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item') . ',' .
                                                                                           $steven->getClassId('Item') . ',' .
                                                                                           $sally->getClassId('Item'))));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //should be 2 explicits read/write
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(3, count($readWritePermitables));

            //Uninvite mary (via detailview)
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array($mary->id => '0', $steven->id => '1', $sally->id => '0')));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            //should be 2 explicits read/write
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(2, count($readWritePermitables));
        }

        /**
         * @depends testInvitingAndUnivitingUsersOnExistingConversation
         */
        public function testAddingCommentsAndUpdatingActivityStampsOnConversation()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally           = User::getByUsername('sally');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->assertEquals(0, $conversations[0]->comments->count());
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $this->assertEquals($oldStamp,                $conversation[0]->latestDateTime);

            //new test - add comment (DetailView)

            //hmm. if we save from the inlineCreateSave in comments... how can we update latestStamp.

            //Validate comment
            $this->setGetArray(array('relatedModelId' => $conversations[0]->id, 'relatedModelClassName' => 'Conversation',
                                     'relatedModelRelationName' => 'comments'));
            $this->setPostArray(array('ajax' => 'inline-edit-form',
                                      'Comment' => array('description' => 'a ValidComment Name')));
            $this->setGetArray(array('id'       => $note->id, 'redirectUrl' => 'someRedirect'));
            $content = $this->runControllerWithExitExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //Now save that comment.
            $this->setGetArray(array('relatedModelId' => $conversations[0]->id, 'relatedModelClassName' => 'Conversation',
                                     'relatedModelRelationName' => 'comments'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');

            //should update latest activity stamp
            $this->assertNotEquals($oldStamp, $conversation[0]->latestDateTime);
            //The comment should have everyone explicitly on it
            $this->assertEquals(1, $conversations[0]->comments->count());
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]->comments->offsetGet(0));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals($everyoneGroup, $readWritePermitables[$everyoneGroup->id]);

            $mary          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');

            //new test - mary can add comment ok
            $this->setGetArray(array('relatedModelId' => $conversations[0]->id, 'relatedModelClassName' => 'Conversation',
                                     'relatedModelRelationName' => 'comments'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals(2, $conversations[0]->comments->count());
        }

        /**
         * @depends testAddingCommentsAndUpdatingActivityStampsOnConversation
         */
        public function testUserEditAndDeletePermissions()
        {
            $mary          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));

            //new test - mary cannot go to edit of conversation because she is not the owner
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('conversations/default/edit');

            //new test - mary cannot delete a conversation she does not own
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('conversations/default/delete');

            //new test - mary can delete a comment she wrote
            $maryCommentId = $conversations[1]->comments->offsetGet(1)->id;
            $superCommentId = $conversations[0]->comments->offsetGet(0)->id;
            $this->setGetArray(array('id' => $maryCommentId));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete');
            $this->assertEquals(1, $conversations[0]->comments->count());

            //new test - mary cannot delete a comment she did not write.
            $this->setGetArray(array('id' => $superCommentId));
            $this->runControllerShouldResultInAccessFailureAndGetContent('conversations/default/delete');
            $this->assertEquals(1, $conversations[0]->comments->count());

            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //new test , super can edit the conversation
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //new test , super can delete the conversation
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete');

            $conversations  = Conversation::getAll();
            $this->assertEquals(0, count($conversations));
        }

        /**
         * @depends testUserEditAndDeletePermissions
         */
        public function testDetailViewPortletFilteringOnConversations()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            //Load Details view to generate the portlets.
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Find the LatestActivity portlet.
            $portletToUse = null;
            $portlets     = Portlet::getAll();
            foreach ($portlets as $portlet)
            {
                if ($portlet->viewType == 'AccountLatestActivtiesForPortlet')
                {
                    $portletToUse = $portlet;
                    break;
                }
            }
            $this->assertNotNull($portletToUse);
            $this->assertEquals('AccountLatestActivtiesForPortletView', get_class($portletToUse->getView()));

            //Load the portlet details for latest activity
            $getData = array('id' => $superAccountId,
                             'portletId' => 2,
                             'uniqueLayoutId' => 'AccountDetailsAndRelationsView_2',
                             'LatestActivitiesConfigurationForm' => array(
                                'filteredByModelName' => 'all',
                                'rollup' => false
                             ));
            $this->setGetArray($getData);
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Now add roll up
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now filter by conversation
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Conversation';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Now do the same thing with filtering but turn off rollup.
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Conversation';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
        }

        /**
         * @depends testDetailViewPortletFilteringOnConversations
         */
        public function testListViewFiltering()
        {
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
            $this->setGetArray(array(
                'type' => ConversationUtil::LIST_TYPE_CREATED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
            $this->setGetArray(array(
                'type' => ConversationUtil::LIST_TYPE_PARTICIPANT));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
        }

        /**
         * @depends testListViewFiltering
         */
        public function testCreateFromModel()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            $conversations  = Conversation::getAll();
            $this->assertEquals(0, count($conversations));

            //add related note for account using createFromRelation action
            $conversationItemPostData = array('account' => array('id' => $account->id));
            $this->setGetArray(array('relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                     'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ConversationItemForm' => $conversationItemPostData,
                                      'Conversation' => array('subject' => 'Conversation Subject', 'description' => 'A description')));
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/createFromRelation');

            $conversations  = Conversation::getAll();
            $this->assertEquals(1,            count($conversations));
            $this->assertEquals(1,            $conversations[0]->conversationItems->count());
            $this->assertEquals($accounts[0], $conversations[0]->conversationItems->getOffset(0));
        }

        /**
         * @depends testCreateFromModel
         */
        public function testAttachments()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            //test adding/editing/remove of attachments via UI.
            //should be similar to how notes works with attachments.
            $this->fail(); //remove once this is completed.

        }

        /**
         * @depends testAttachments
         */
        public function testUnparticipatingACurrentUser()
        {
            //todo: also test unparticipating yourself.!!! which you can only do if you did not create the conversation.
        }

        /**
         * @depends testUnparticipatingACurrentUser
         */
        public function testCommentsAjaxListForRelatedModel()
        {
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->setGetArray(array('relatedModelId' => $conversations[0]->id, 'relatedModelClassName' => 'Conversation',
                                     'relatedModelRelationName' => 'comments'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('comments/default/ajaxListForRelatedModel');
            echo $content;
        }
    }
?>