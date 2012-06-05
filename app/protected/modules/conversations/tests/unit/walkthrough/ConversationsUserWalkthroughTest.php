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
            $steven                    = User::getByUsername('steven');
            $sally                     = UserTestHelper::createBasicUser('steven');
            $mary                      = UserTestHelper::createBasicUser('mary');

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

        public function testSuperUserCreateConversation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test creating conversation via POST, invite Mary
            $conversations = Conversation::getAll();
            $this->assertEquals(0, count($conversations));
            $this->setPostArray(array('Conversation' => array('subject'     => 'TestSubject',
                                                              'description' => 'TestDescription')));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/create');

            //Confirm conversation saved.

            //Confirm conversation is connected to the related account.

            //Confirm Mary is invited.

            //Confirm Mary is the only one with explicit permissions on the conversation

            //Confirm a notification gets emailed to Mary
        }




            //new test - test inviting steven (via detailview)
                //steven should get email
                //should be 2 explicits read/write

            //new test - uninvite mary (via detailview)
                //should be 1 explicit read/write

            //new test - add comment
                //should update latest activity stamp

            //new test - mary can add comment ok

            //new test - mary cannot go to edit of conversation because she is not the owner

            //new test - mary cannot delete a conversation she does not own

            //new test - mary can delete a comment she wrote

            //new test - mary cannot delete a comment she did not write.



            //new test , super can edit the conversation

            //new test , super can delete the conversation



            //new test - latest activities shows filtered by conversations

            //new test - latest activities shows filtered by conversations with roll up on




            //test filtering by started and trying strpos on something
            //test filtering by participated in in listview. (using strpos to confirm)



            //need to test CreateFromModel for conversation created from account detailview to make sure it populates right?


            //test adding/editing/remove of attachments via UI.



















/**
            //Save account.
            $superAccount = Account::getById($superAccountId);
            $this->assertEquals(null, $superAccount->officePhone);
            $this->setPostArray(array('Account' => array('officePhone' => '456765421')));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit',
                        Yii::app()->createUrl('accounts/default/details', array('id' => $superAccountId)));
            $superAccount = Account::getById($superAccountId);
            $this->assertEquals('456765421', $superAccount->officePhone);
            //Test having a failed validation on the account during save.
            $this->setGetArray (array('id'      => $superAccountId));
            $this->setPostArray(array('Account' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;records selected for updating') === false);

            //save Model MassEdit for selected Ids
            //Test that the 2 accounts do not have the office phone number we are populating them with.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertNotEquals('7788', $account1->officePhone);
            $this->assertNotEquals('7788', $account2->officePhone);
            $this->assertNotEquals('7788', $account3->officePhone);
            $this->assertNotEquals('7788', $account4->officePhone);
            $this->setGetArray(array(
                'selectedIds' => $superAccountId . ',' . $superAccountId2, // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('officePhone' => '7788'),
                'MassEdit' => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            //Test that the 2 accounts have the new office phone number and the other accounts do not.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertEquals   ('7788', $account1->officePhone);
            $this->assertEquals   ('7788', $account2->officePhone);
            $this->assertNotEquals('7788', $account3->officePhone);
            $this->assertNotEquals('7788', $account4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('officePhone' => '4455'),
                'MassEdit' => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            //Test that all accounts have the new phone number.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertEquals('4455', $account1->officePhone);
            $this->assertEquals('4455', $account2->officePhone);
            $this->assertEquals('4455', $account3->officePhone);
            $this->assertEquals('4455', $account4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('accounts/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":50') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":75') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":100') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for Account
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/auditEventsModalList');
        }
        **/
    }
?>