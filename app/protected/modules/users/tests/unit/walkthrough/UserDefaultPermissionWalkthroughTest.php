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

    class UserDefaultPermissionWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $testGroup1        = new Group();
            $testGroup1->name  = 'testGroup1';
            assert($testGroup1->save()); // Not Coding Standard
            $testGroup2        = new Group();
            $testGroup2->name  = 'testGroup2';
            assert($testGroup2->save()); // Not Coding Standard
        }

        public function testUserCanSaveDefaultPermissions()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $group = Group::getByName('testGroup2');

            // set permission setting to 'everyone' and permission group settings to 'testGroup2'
            $this->setGetArray(array('id' => $super->id));
            $postData = array('defaultPermissionSetting' => UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE,
                                'defaultPermissionGroupSetting' => $group->id);
            $this->setPostArray(array('UserConfigurationForm' => $postData));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit',
                                Yii::app()->createUrl('users/default/details', array('id' => $super->id)));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionGroupSetting($super));

            // set permission setting to 'users and group', set permission group settings to 'testGroup2'
            $this->resetGetArray();
            $this->resetPostArray();
            $this->setGetArray(array('id' => $super->id));
            $postData = array('defaultPermissionSetting' => UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP,
                                'defaultPermissionGroupSetting' => $group->id);
            $this->setPostArray(array('UserConfigurationForm' => $postData));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit',
                                Yii::app()->createUrl('users/default/details', array('id' => $super->id)));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionGroupSetting($super),
                                $group->id);
        }

        /**
         * @depends testUserCanSaveDefaultPermissions
         */
        public function testUserDefaultPermissionsLoadedOnCreate()
        {
            // TODO @Shoaibi waiting on input on DOM Suggestion.
            // make a get to account's create and ensure that permissions are loaded there
        }

        /**
         * @depends testUserCanSaveDefaultPermissions
         */
        public function testUserDefaultPermissionsLoadedOnlyOnCreate()
        {
            // TODO @Shoaibi waiting on input on DOM Suggestion.
            // make a get to edit url of an account and ensure that permissions aren't loaded there.
        }

        public function testGlobalDefaultsLoadedOnCreateInAbsenceOfUserDefaultPermissions()
        {
            // TODO @Shoaibi waiting on input on DOM Suggestion.
            // delete user's default values by setting them to null. zurmoconfig
            // make a get request to create screen
            // compare the values to global defaults
        }
    }
?>