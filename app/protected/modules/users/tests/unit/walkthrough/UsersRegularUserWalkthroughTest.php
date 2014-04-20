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
     * User Module
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class UsersRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $aUser = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $aUser->save();
            $bUser = UserTestHelper::createBasicUser('bUser');
            $bUser->setRight('UsersModule', UsersModule::RIGHT_ACCESS_USERS);
            $bUser->save();
            $cUser = UserTestHelper::createBasicUser('cUser');
            $dUser = UserTestHelper::createBasicUser('dUser');
        }

        public function testRegularUserAllControllerActions()
        {
            $aUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
            $bUser = User::getByUsername('bUser');

            //Access to admin configuration should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('configuration');

            //Access to users list to modify users should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default');

            $this->setGetArray(array('id' => $bUser->id));
            //Access to view other users Audit Trail should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default/auditEventsModalList');

            //Access to edit other User and Role should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default/edit');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            //Access to User Role edit link and control not available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertFalse(strpos($content, 'User_role_SelectLink') !== false);
            $this->assertFalse(strpos($content, 'User_role_name') !== false);

            //Check if the user who has right access for users can access any users audit trail.
            $bUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('bUser');
            $this->setGetArray(array('id' => $bUser->id));
            //Access to audit Trail should not fail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to other user audit Trail should not fail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            //Now test all portlet controller actions
            $portlet = new Portlet();
            $portlet->column    = 1;
            $portlet->position  = 1;
            $portlet->layoutId = 'xyz';
            $portlet->collapsed = false;
            $portlet->viewType = 'UserLatestActivitiesForPortlet';
            $portlet->user = $bUser;
            $portlet->save();
            $this->setGetArray(array('id' => $aUser->id, 'portletId' => $portlet->id)); //Using dummy portlet id
            //Access to details of a portlet for self user should be fine.
            $this->runControllerWithNoExceptionsAndGetContent('users/defaultPortlet/details');

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead
            //Now test peon with elevated permissions to models.
            //actionModalList
            //Autocomplete for User
        }

        /**
         * @depends testRegularUserAllControllerActions
         */
        public function testBulkWriteSecurityCheck()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('aUser');
            $this->assertEquals(Right::DENY, $aUser->getEffectiveRight('UserModule', UsersModule::RIGHT_ACCESS_USERS));
            $this->assertEquals(Right::DENY, $aUser->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE));
            $aUser->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE);
            $this->assertTrue($aUser->save());

            //Confirm user cannot access the massEdit view even though he/she has bulk write access.
            Yii::app()->user->userModel = $aUser;
            $this->setGetArray(array('selectedIds' => '1,2,3', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/massEdit');
            $this->assertFalse(strpos($content, 'You have tried to access a page you do not have access to') === false);
        }

        /**
         * @depends testBulkWriteSecurityCheck
         */
        public function testRegularUserAfterChangeOfUserName()
        {
            $cUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('cUser');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');

            $this->setGetArray(array('id' => $cUser->id));
            //Access to User to change the username.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');

            $this->assertTrue(strpos($content, 'User_lastName') !== false);
            $this->assertTrue(strpos($content, 'User_username') !== false);

            $this->setGetArray(array('id' => $cUser->id));
            $this->setPostArray(array(
                'User'  => array('username' => 'zuser', 'firstName' => 'cUser', 'lastName' => 'cUserson'),
                'save' => 'Save'
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');

            $zUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('zUser');
            $this->resetPostArray();
            $this->setGetArray(array('id' => $zUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/gameDashboard');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
        }

        public function testRegularUserChangeAvatar()
        {
            $aUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $bUser = User::getByUsername('bUser');

            //User as access to change is avatar
            $this->setGetArray(array('id' => $aUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');

            //User cant access change other user avatar
            $this->setGetArray(array('id' => $bUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/changeAvatar');
            $this->assertContains('You have tried to access a page you do not have access to.', $content);

            //Failed change avatar validation
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('ajax'           => 'edit-form',
                                      'UserAvatarForm' => array('avatarType'               => '3',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changeAvatar');
            $this->assertContains('You need to choose an email address', $content);

            //Successful change avatar validation
            $this->setGetArray (array('id'      => $aUser->id));
            $this->setPostArray(array('ajax'           => 'edit-form',
                                      'UserAvatarForm' => array('avatarType'               => '1',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changeAvatar');
            $this->assertContains('[]', $content);

            //Successful save avatar change.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('save'           => 'Save',
                                      'UserAvatarForm' => array('avatarType'               => '2',
                                                                'customAvatarEmailAddress' => ''))
                                );
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/changeAvatar');
        }

        public function testRegularUserAccessGroupsAndRolesButNotCreateAndDelete()
        {
            $user = UserTestHelper::createBasicUser('Dood1');
            $group = new Group();
            $group->name = 'Doods';
            $group->users->add($user);
            $this->assertTrue($group->save());
            $this->assertEquals(1, count($user->groups));
            $this->assertEquals('Doods', $user->groups[0]->name);

            $role = new Role();
            $role->name = 'myRole';
            $saved = $role->save();
            $this->assertTrue($saved);

            $user->setRight('GroupsModule', GroupsModule::RIGHT_ACCESS_GROUPS);
            $user->setRight('RolesModule', RolesModule::RIGHT_ACCESS_ROLES);
            $user->setRight('ZurmoModule', ZurmoModule::RIGHT_ACCESS_ADMINISTRATION);
            $this->assertTrue($user->save());
            $user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('Dood1');
            //Check Create button not present in group
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/group/list');
            $find = 'icon-create';
            $this->assertEquals(0, preg_match('~\b' . $find . '\b~i', $content));
            //Check Delete button not present in group
            $this->setGetArray(array('id' => $group->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/group/edit');
            $find = 'Delete Group';
            $this->assertEquals(0, preg_match('~\b' . $find . '\b~i', $content));
            //Check Create button not present in role
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/role/list');
            $find = 'icon-create';
            $this->assertEquals(0, preg_match('~\b' . $find . '\b~i', $content));
            //Check Delete button not present in role
            $this->setGetArray(array('id' => $role->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/role/details');
            $find = 'Delete Group';
            $this->assertEquals(0, preg_match('~\b' . $find . '\b~i', $content));
            //Access to create action in group should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('zurmo/group/create');
            //Access to delete action in group should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('zurmo/group/delete');
            //Access to create action in role should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('zurmo/role/create');
            //Access to delete action in role should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('zurmo/role/delete');
        }
    }
?>