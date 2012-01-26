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
     * Note module walkthrough tests for a regular user.
     */
    class NotesRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function testRegularUserAllControllerActions()
        {
            //Now test all portlet controller actions

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead

            //Now test peon with elevated permissions to models.
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccount = AccountTestHelper::createAccountByNameForOwner('accountOwnedBySuper', $super);
            $note = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('noteCreatedBySuper', $super, $superAccount);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test account details portlet controller actions
            $this->setGetArray(array('id' => $superAccount->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //Now test all notes portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/createFromRelation');
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/inlineCreateSave');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');
        }

         /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to accounts
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($nobody->save());

            //create the account as nobody user as the owner
            $account = AccountTestHelper::createAccountByNameForOwner('accountOwnedByNobody', $nobody);

            //Test whether the nobody user is able to view the account that he created
            $this->setGetArray(array('id' => $account->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Now test peon with elevated rights to notes
            $nobody->setRight('NotesModule', NotesModule::RIGHT_ACCESS_NOTES);
            $nobody->setRight('NotesModule', NotesModule::RIGHT_CREATE_NOTES);
            $nobody->setRight('NotesModule', NotesModule::RIGHT_DELETE_NOTES);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $note = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('noteCreatedByNobody', $nobody, $account);

            //Test whether the nobody user is able to view the note details and edit that he created
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Test validating an existing note via the inline edit validation (Success)
            $activityItemPostData = array('Account' => array('id' => $account->id));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'ajax' => 'inline-edit-form',
                                      'Note' => array('description' => 'a Valid Name of a Note')));
            $this->setGetArray(array('id' => $note->id, 'redirectUrl' => 'someRedirect'));
            $content = $this->runControllerWithExitExceptionAndGetContent('notes/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //add related note for account using createFromRelation action
            $activityItemPostData = array('account' => array('id' => $account->id));
            $this->setGetArray(array('relationAttributeName' => 'Account', 'relationModelId' => $account->id,
                                     'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Note' => array('description' => 'myNote')));
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/createFromRelation');

            //Test nobody can delete an existing note he created and it redirects to index.
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/delete');
        }

         /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create superAccount owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccount = AccountTestHelper::createAccountByNameForOwner('AccountsForElevationToModelTest', $super);

            //Test nobody, access to edit and details of superAccount should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $superAccount->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $superAccount->addPermissions($nobody, Permission::READ);
            $this->assertTrue($superAccount->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $superAccount->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create note for an superAccount using the super user
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $note = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('noteCreatedBySuper', $super, $superAccount);

            //Test nobody, access to edit and details of notes should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give nobody access to details view only
            Yii::app()->user->userModel = $super;
            $note->addPermissions($nobody, Permission::READ);
            $this->assertTrue($note->save());

            //Now access to notes view by Nobody should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //Now access to notes edit and delete by Nobody should fail
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give nobody access to both details and edit view
            Yii::app()->user->userModel = $super;
            $note->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note->save());

            //Now access to notes view and edit by Nobody should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Now access to notes delete by Nobody should fail
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //revoke the permission from the nobody user to access the note
            Yii::app()->user->userModel = $super;
            $note->removePermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note->save());

            //Now nobodys, access to edit, details and delete of notes should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give nobody access to details, edit and delete view
            Yii::app()->user->userModel = $super;
            $note->addPermissions($nobody, Permission::READ_WRITE_DELETE);
            $this->assertTrue($note->save());

            //Now nobodys, access to delete of notes should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $note->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/delete');

            //create some roles
            Yii::app()->user->userModel = $super;
            $parentRole = new Role();
            $parentRole->name = 'AAA';
            $this->assertTrue($parentRole->save());

            $childRole = new Role();
            $childRole->name = 'BBB';
            $this->assertTrue($childRole->save());

            $userInParentRole = User::getByUsername('confused');
            $userInChildRole = User::getByUsername('nobody');

            $childRole->users->add($userInChildRole);
            $this->assertTrue($childRole->save());
            $parentRole->users->add($userInParentRole);
            $parentRole->roles->add($childRole);
            $this->assertTrue($parentRole->save());

            //create account owned by super
            $account2 = AccountTestHelper::createAccountByNameForOwner('AccountsParentRolePermission', $super);

            //Test userInParentRole, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $account2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create a note owned by super
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $note2 = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('noteCreatedBySuperForRole', $super, $account2);

            //Test userInChildRole, access to notes details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInParentRole, access to notes details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give userInChildRole access to READ permision for notes
            Yii::app()->user->userModel = $super;
            $note2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($note2->save());

            //Test userInChildRole, access to notes details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //Test userInChildRole, access to notes edit and delete should fail.
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInParentRole, access to notes details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //Test userInParentRole, access to notes edit and delete should fail.
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give userInChildRole access to read and write for the notes
            Yii::app()->user->userModel = $super;
            $note2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note2->save());

            //Test userInChildRole, access to notes edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Test userInChildRole, access to notes delete should fail.
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInParentRole, access to notes edit should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Test userInParentRole, access to notes delete should fail.
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //revoke userInChildRole access to read and write notes
            Yii::app()->user->userModel = $super;
            $note2->removePermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note2->save());

            //Test userInChildRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInParentRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give userInChildRole access to read, write and delete for the notes
            Yii::app()->user->userModel = $super;
            $note2->addPermissions($userInChildRole, Permission::READ_WRITE_DELETE);
            $this->assertTrue($note2->save());

            //Test userInParentRole, access to delete should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $note2->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/delete');

            //clear up the role relationships between users so not to effect next assertions
            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            //create some groups and assign users to groups
            Yii::app()->user->userModel = $super;
            $parentGroup = new Group();
            $parentGroup->name = 'AAA';
            $this->assertTrue($parentGroup->save());

            $childGroup = new Group();
            $childGroup->name = 'BBB';
            $this->assertTrue($childGroup->save());

            $userInChildGroup = User::getByUsername('confused');
            $userInParentGroup = User::getByUsername('nobody');

            $childGroup->users->add($userInChildGroup);
            $this->assertTrue($childGroup->save());
            $parentGroup->users->add($userInParentGroup);
            $parentGroup->groups->add($childGroup);
            $this->assertTrue($parentGroup->save());
            $parentGroup->forget();
            $childGroup->forget();
            $parentGroup = Group::getByName('AAA');
            $childGroup = Group::getByName('BBB');

            //Add access for the confused user to accounts and creation of accounts.
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($userInChildGroup->save());

            //create account owned by super
            $account3 = AccountTestHelper::createAccountByNameForOwner('testingAccountsParentGroupPermission', $super);

            //Test userInParentGroup, access to details should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //Test userInChildGroup, access to details should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $account3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create a note owned by super
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $note3 = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('noteCreatedBySuperForGroup', $super, $account3);

            //Add access for the confused user to accounts and creation of accounts.
            $userInChildGroup->setRight('NotesModule', NotesModule::RIGHT_ACCESS_NOTES);
            $userInChildGroup->setRight('NotesModule', NotesModule::RIGHT_CREATE_NOTES);
            $userInChildGroup->setRight('NotesModule', NotesModule::RIGHT_DELETE_NOTES);
            $this->assertTrue($userInChildGroup->save());

            //Test userInParentGroup, access to notes details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInChildGroup, access to notes details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $note3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($note3->save());

            //Test userInParentGroup, access to notes details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //Test userInParentGroup, access to notes edit and delete should fail.
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInChildGroup, access to notes details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //Test userInChildGroup, access to notes edit and delete should fail.
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $note3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note3->save());

            //Test userInParentGroup, access to edit notes should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Test userInParentGroup, access to notes delete should fail.
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInChildGroup, access to edit notes should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');

            //Test userInChildGroup, access to notes delete should fail.
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //revoke parentGroup access to notes read and write
            Yii::app()->user->userModel = $super;
            $note3->removePermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($note3->save());

            //Test userInChildGroup, access to notes detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //Test userInParentGroup, access to notes detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/details');
            $this->setGetArray(array('id' => $note3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('notes/default/delete');

            //give parentGroup access to read, write and delete
            Yii::app()->user->userModel = $super;
            $note3->addPermissions($parentGroup, Permission::READ_WRITE_DELETE);
            $this->assertTrue($note3->save());

            //Test userInChildGroup, access to notes delete should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $note3->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/delete');

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $parentGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');
            $parentGroup                = Group::getByName('AAA');

            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }
    }
?>
