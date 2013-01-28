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
     * Contacts Module Walkthrough.
     *
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class ContactsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            //Setup test data owned by the super user.
            $super = Yii::app()->user->userModel;
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount',  $super);
            AccountTestHelper::createAccountByNameForOwner           ('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact',  $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact4', $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            //Make contact DetailsAndRelations portlets
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //todo: look at account regular user walkthrough for idea.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $contact = ContactTestHelper::createContactByNameForOwner('Switcheroo', $super);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');

            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/massEdit');
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 2));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/massEditProgressSave');

            //Autocomplete for Contact should fail.
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/autoComplete');

            //actionModalList should fail.
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/modalList');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $contact->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test peon with elevated rights to contacts
            $nobody = User::getByUsername('nobody');
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_DELETE_CONTACTS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertFalse(strpos($content, 'Arthur Conan') === false);
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');

            //Test nobody can view an existing contact he owns.
            $contact = ContactTestHelper::createContactByNameForOwner('Switcheroo', $nobody);

            //At this point the listview for leads should show the search/list and not the helper screen.
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->assertTrue(strpos($content, 'Arthur Conan') === false);

            //Go to the a ccount editview.
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test nobody can delete an existing contact he owns and it redirects to index.
            $this->setGetArray(array('id' => $contact->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete',
                        Yii::app()->createUrl('contacts/default/index'));

            //Autocomplete for Contact should not fail.
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/autoComplete');

            //actionModalList for Contact should not fail.
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/modalList');

            //todo: more.
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $nobody = User::getByUsername('nobody');

            //Created contact owned by user super.
            $contact = ContactTestHelper::createContactByNameForOwner('testingElavationToModel', $super);

            //Test nobody, access to edit, details and delete should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $contact->addPermissions($nobody, Permission::READ);
            $this->assertTrue($contact->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Test nobody, access to edit and delete should fail.
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $contact->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact->save());

            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test nobody, access to delete should fail.
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $contact->removePermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact->save());

            //Test nobody, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give nobody access to read, write and delete
            Yii::app()->user->userModel = $super;
            $contact->addPermissions($nobody, Permission::READ_WRITE_DELETE);
            $this->assertTrue($contact->save());

            //Test nobody, access to delete should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contact->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete',
                        Yii::app()->createUrl('contacts/default/index'));

            Yii::app()->user->userModel = $super;
            //create some roles
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

            $contact2 = ContactTestHelper::createContactByNameForOwner('testingParentRolePermission', $super);

            //Test userInChildRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInParentRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $contact2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($contact2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Test userInChildRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Test userInParentRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $contact2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact2->save());

            //Test userInChildRole, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test userInChildRole, access to delete should fail.
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test userInParentRole, access to delete should fail.
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $contact2->removePermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact2->save());

            //Test userInChildRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInParentRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $contact2->addPermissions($userInChildRole, Permission::READ_WRITE_DELETE);
            $this->assertTrue($contact2->save());

            //Test userInParentRole, access to delete should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contact2->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete',
                       Yii::app()->createUrl('contacts/default/index'));

            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            Yii::app()->user->userModel = $super;
            //create some groups and assign users to groups
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

            //Add access for the confused user to contacts and creation of contacts.
            $userInChildGroup->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $userInChildGroup->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $userInChildGroup->setRight('ContactsModule', ContactsModule::RIGHT_DELETE_CONTACTS);
            $this->assertTrue($userInChildGroup->save());

            $contact3 = ContactTestHelper::createContactByNameForOwner('testingParentGroupPermission', $super);

            //Test userInParentGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInChildGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $contact3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($contact3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Test userInParentGroup, access to edit and delete should fail.
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Test userInChildGroup, access to edit and delete should fail.
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $contact3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact3->save());

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test userInParentGroup, access to delete should fail.
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');

            //Test userInChildGroup, access to delete should fail.
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $contact3->removePermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contact3->save());

            //Test userInChildGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //Test userInParentGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('id' => $contact3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $contact3->addPermissions($parentGroup, Permission::READ_WRITE_DELETE);
            $this->assertTrue($contact3->save());

            //Test userInChildGroup, access to delete should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contact3->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete',
                        Yii::app()->createUrl('contacts/default/index'));

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

         /**
         * @deletes selected contacts.
         */
        public function testRegularMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $nobody = User::getByUsername('nobody');
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE));
            $confused->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE);
            //Load MassDelete view for the 3 contacts.
            $contacts = Contact::getAll();
            $this->assertEquals(5, count($contacts));
            $contact1 = ContactTestHelper::createContactByNameForOwner('contactDelete1', $confused);
            $contact2 = ContactTestHelper::createContactByNameForOwner('contactDelete2', $confused);
            $contact3 = ContactTestHelper::createContactByNameForOwner('contactDelete3', $nobody);
            $contact4 = ContactTestHelper::createContactByNameForOwner('contactDelete4', $confused);
            $contact5 = ContactTestHelper::createContactByNameForOwner('contactDelete5', $confused);
            $contact6 = ContactTestHelper::createContactByNameForOwner('contactDelete6', $nobody);
            $contact7 = ContactTestHelper::createContactByNameForOwner('contactDelete7', $confused);
            $contact8 = ContactTestHelper::createContactByNameForOwner('contactDelete8', $confused);
            $contact9 = ContactTestHelper::createContactByNameForOwner('contactDelete9', $nobody);
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $selectedIds = $contact1->id . ',' . $contact2->id . ',' . $contact3->id ;    // Not Coding Standard
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>3</strong>&#160;Contacts selected for removal') === false);

            //calculating contacts after adding 9 new records
            $contacts = Contact::getAll();
            $this->assertEquals(14, count($contacts));
            //Deleting 6 contacts for pagination scenario
            //Run Mass Delete using progress save for page1
            $selectedIds = $contact1->id . ',' . $contact2->id . ',' . // Not Coding Standard
                           $contact3->id . ',' . $contact4->id . ',' . // Not Coding Standard
                           $contact5->id . ',' . $contact6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Contact_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithExitExceptionAndGetContent('contacts/default/massDelete');
            $contacts = Contact::getAll();
            $this->assertEquals(9, count($contacts));

            //Run Mass Delete using progress save for page2
            $selectedIds = $contact1->id . ',' . $contact2->id . ',' . // Not Coding Standard
                           $contact3->id . ',' . $contact4->id . ',' . // Not Coding Standard
                           $contact5->id . ',' . $contact6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Contact_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massDeleteProgress');
            $contacts = Contact::getAll();
            $this->assertEquals(8, count($contacts));
        }

         /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testRegularMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $billy = User::getByUsername('billy');

            //Load MassDelete view for the 8 contacts.
            $contacts = Contact::getAll();
            $this->assertEquals(8, count($contacts));
             //Deleting all contacts

            //mass Delete pagination scenario
            //Run Mass Delete using progress save for page1
            $this->setGetArray(array(
                'selectAll' => '1',
                'Contact_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithExitExceptionAndGetContent('contacts/default/massDelete');
            $contacts = Contact::getAll();
            $this->assertEquals(3, count($contacts));

           //Run Mass Delete using progress save for page2
            $this->setGetArray(array(
                'selectAll' => '1',
                'Contact_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massDeleteProgress');
            $contacts = Contact::getAll();
            $this->assertEquals(0, count($contacts));
        }
    }
?>
