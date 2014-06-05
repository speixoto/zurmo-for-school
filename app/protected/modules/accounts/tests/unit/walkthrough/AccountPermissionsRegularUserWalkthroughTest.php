<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Account Permissions Regular User Walkthrough.
     * Walkthrough for the regular user of all possible permissions scenarios. Primarily focuses on changing the
     * DerivedExplicitReadWriteModelPermissions element values.
     */
    class AccountPermissionsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AllPermissionsOptimizationUtil::rebuild();

            //Add the nobody user to an account, but only read only.
            $nobody = User::getByUsername('nobody');
            $account = AccountTestHelper::createAccountByNameForOwner('superAccountReadableByNobody',  Yii::app()->user->userModel);
            $account->addPermissions($nobody, Permission::READ, Permission::ALLOW);
            assert($account->save()); // Not Coding Standard
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($account, $nobody);

            //Give the nobody user rights to the accounts module.
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            assert($nobody->save()); // Not Coding Standard

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            assert($everyoneGroup->save()); // Not Coding Standard

            $group1        = new Group();
            $group1->name  = 'Group1';
            assert($group1->save()); // Not Coding Standard
        }

        /**
         * The createdByUser should be able to create an account and set the owner to a different owner. At the same
         * time the user should also be able to add permissions for the EVERYONE group.
         */
        public function testCreateAccountWithDifferentOwnerThanCreatedByUser()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $postData = array('Account' =>
                                array('name' => 'Switcheroo Inc.',
                                      'owner' => array('id' => $super->id),
                                      'explicitReadWriteModelPermissions' =>
                                            array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP)));
            $this->setPostArray($postData);
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/create');
        }

        /**
         * @depends testCreateAccountWithDifferentOwnerThanCreatedByUser
         */
        public function testRegularUserCanViewOrNotViewDerivedExplicitReadWriteModelPermissionsElement()
        {
            //Set the current user as the nobody user.
            $nobody          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Create an account by the nobody user.
            $account = AccountTestHelper::
                       createAccountByNameForOwner('nobodyAccount',  $nobody);

            //Confirm the nobody user can view the details of that account and can see the
            //DerivedExplicitReadWriteModelPermissions element.
            $this->setGetArray(array('id' => $account->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            //Confirm content does have the security element
            $this->assertFalse(strpos($content, 'Who can read and write') === false);

            //Now go to an account details with nobody where nobody can read, but not write.
            //In this scenario the DerivedExplicitReadWriteModelPermissions element is still shown
            //at the bottom of the details area.
            $accounts = Account::getByName('superAccountReadableByNobody');
            $this->assertEquals(1, count($accounts));
            $accountId = $accounts[0]->id;
            $this->setGetArray(array('id' => $accountId));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            //Confirm content does not have security element
            $this->assertFalse(strpos($content, 'Who can read and write') === false);
        }

        /**
         * @depends testRegularUserCanViewOrNotViewDerivedExplicitReadWriteModelPermissionsElement
         */
        public function testRegularUserEditExistingAccountAndChangeExplicitPermissions()
        {
            $nobody         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $accountId      = self::getModelIdByModelNameAndName ('Account', 'nobodyAccount');
            $group1         = Group::getByName('Group1');
            $everyoneGroup  = Group::getByName(Group::EVERYONE_GROUP_NAME);

            //Edit nobody's account and add an explicit permissions.
            //Save account and add a non-everyone group permission.
            //Permissions is the only thing changing on the account.
            $this->setGetArray(array('id' => $accountId));
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $group1->id);
            $this->setPostArray(array('Account' =>
                    array('explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));
            $this->assertEquals($group1, $readWritePermitables[$group1->getClassId('Permitable')]);

            //Edit nobody's account and change the explicit permissions.
            $this->setGetArray(array('id' => $accountId));
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP);
            $this->setPostArray(array('Account' =>
                array('explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));
            $this->assertEquals($everyoneGroup, $readWritePermitables[$everyoneGroup->getClassId('Permitable')]);

            //Edit nobody's account and remove the explicit permissions.
            $this->setGetArray(array('id' => $accountId));
            $postData = array('type' => null);
            $this->setPostArray(array('Account' =>
                array('explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));
        }

        /**
         * @depends testRegularUserEditExistingAccountAndChangeExplicitPermissions
         */
        public function testRegularUserCreateAccountAndChangeExplicitPermissions()
        {
            $nobody         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $group1         = Group::getByName('Group1');
            $everyoneGroup  = Group::getByName(Group::EVERYONE_GROUP_NAME);

            //Create an account for nobody with no explicit permissions.
            $this->resetGetArray();
            $postData = array('type' => null);
            $this->setPostArray(array('Account' => array(
                                            'name'        => 'myNewAccount',
                                            'officePhone' => '456765421',
                                            'explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/create'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
            $accounts = Account::getByName('myNewAccount');
            $this->assertEquals(1, count($accounts));
            $accountId = $accounts[0]->id;
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));

            //Create an account for nobody and add explicit permissions for the everyone group.
            $this->resetGetArray();
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP);
            $this->setPostArray(array('Account' => array(
                                            'name'        => 'myNewAccount2',
                                            'officePhone' => '456765421',
                                            'explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/create'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
                        $accounts = Account::getByName('myNewAccount2');
            $this->assertEquals(1, count($accounts));
            $accountId = $accounts[0]->id;
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));
            $this->assertEquals($everyoneGroup, $readWritePermitables[$everyoneGroup->getClassId('Permitable')]);

            //Create an account for nobody and add explicit permissions for a non-everyone group.
            $this->resetGetArray();
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                  'nonEveryoneGroup' => $group1->id);
            $this->setPostArray(array('Account' => array(
                                            'name'        => 'myNewAccount3',
                                            'officePhone' => '456765421',
                                            'explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/create'); // Not Coding Standard
            //Confirm the permissions are set right based on how the account was saved.
                        $accounts = Account::getByName('myNewAccount3');
            $this->assertEquals(1, count($accounts));
            $accountId = $accounts[0]->id;
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($accountId));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(0, count($readOnlyPermitables));
            $this->assertEquals($group1, $readWritePermitables[$group1->getClassId('Permitable')]);
        }

        /**
         * Testing when a user who is not a super user, has a model owned by themselves but not created by themselves.
         * Then that user tries to change the owner to someone else and at the same time change the read/write from
         * owner only to everyone.
         */
        public function testRegularUserChangingOwnershipToEveryoneOnNonCreatedAccount()
        {
            $super   = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $nobody   = User::getByUsername('nobody');
            $account = AccountTestHelper::createAccountByNameForOwner('superAccountReadableByNobody',  $nobody);

            $nobody  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //First set the read/write as owner only.
            $this->setGetArray(array('id' => $account->id));
            $postData = array('type' => '');
            $this->setPostArray(array('Account' =>
            array('owner' => array('id' => $nobody->id), 'explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit'); // Not Coding Standard

            $accountId = $account->id;
            $account->forget();
            $account   = Account::getById($accountId);

            $this->setGetArray(array('id' => $account->id));
            $postData = array('type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP);
            $this->setPostArray(array('Account' =>
            array('owner' => array('id' => $super->id), 'explicitReadWriteModelPermissions' => $postData)));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit'); // Not Coding Standard
            //Make sure user can still go to details view
            $this->setGetArray(array('id' => $account->id));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
        }
    }
?>