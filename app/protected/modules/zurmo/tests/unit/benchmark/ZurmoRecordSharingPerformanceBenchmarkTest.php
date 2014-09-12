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

    class ZurmoRecordSharingPerformanceBenchmarkTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function setup()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testRecordSharingPerformanceTime()
        {
            $singleUserGroupMembers   = array();
            $hundredUsersGroupMembers = array();
            // we could have used helpers to do a lot of the following stuff (such as creating users, groups,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create single user group
            $this->setPostArray(array('Group' => array(
                'name'  => 'Single User Group',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $singleUserGroup                = Group::getByName('Single User Group');
            $this->assertNotNull($singleUserGroup);
            $this->assertEquals('Single User Group', strval($singleUserGroup));
            $singleUserGroupId              = $singleUserGroup->id;
            $singleUserGroup->setRight('ContactsModule', ContactsModule::getAccessRight());
            $singleUserGroup->setRight('ContactsModule', ContactsModule::getCreateRight());
            $singleUserGroup->setRight('ContactsModule', ContactsModule::getDeleteRight());
            $this->assertTrue($singleUserGroup->save());
            $singleUserGroup->forgetAll();
            $singleUserGroup                = Group::getById($singleUserGroupId);

            $baseUserName                   = StringUtil::generateRandomString(6, implode(range('a', 'z')));
            // Populate singleUserGroup
            $username                       = $baseUserName . '0';
            $this->resetGetArray();
            $this->setPostArray(array('UserPasswordForm' =>
                array('firstName'           => 'Some',
                    'lastName'              => 'Body',
                    'username'              => $username,
                    'newPassword'           => 'myPassword123',
                    'newPassword_repeat'    => 'myPassword123',
                    'officePhone'           => '456765421',
                    'userStatus'            => 'Active')));
            $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
            $user                           = User::getByUsername($username);
            $this->assertNotNull($user);

            // set user's group
            $this->setGetArray(array('id' => $singleUserGroupId));
            $this->setPostArray(array(
                'GroupUserMembershipForm' => array('userMembershipData' => array($user->id)
                )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
            $user->forgetAll();
            $user                           = User::getByUsername($username);
            $this->assertNotNull($user);
            $singleUserGroup->forgetAll();
            $singleUserGroup                = Group::getById($singleUserGroupId);
            $this->assertContains($singleUserGroup, $user->groups);
            $singleUserGroupMembers[]       = $user;

            // create hundred users group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => 'Hundred Users Group',
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $hundredUsersGroup              = Group::getByName('Hundred Users Group');
            $this->assertNotNull($hundredUsersGroup);
            $this->assertEquals('Hundred Users Group', strval($hundredUsersGroup));
            $hundredUsersGroup->setRight('ContactsModule', ContactsModule::getAccessRight());
            $hundredUsersGroup->setRight('ContactsModule', ContactsModule::getCreateRight());
            $hundredUsersGroup->setRight('ContactsModule', ContactsModule::getDeleteRight());
            $this->assertTrue($hundredUsersGroup->save());
            $hundredUsersGroupId            = $hundredUsersGroup->id;
            $hundredUsersGroup->forgetAll();
            $hundredUsersGroup              = Group::getById($hundredUsersGroupId);

            for ($i = 1; $i < 5; $i++)
            {
                $username                   = $baseUserName . $i;
                // Populate hundredUsersGroup
                $this->resetGetArray();
                $this->setPostArray(array('UserPasswordForm' =>
                    array('firstName'           => 'Some',
                        'lastName'              => 'Body',
                        'username'              => $username,
                        'newPassword'           => 'myPassword123',
                        'newPassword_repeat'    => 'myPassword123',
                        'officePhone'           => '456765421',
                        'userStatus'            => 'Active')));
                $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
                $user                       = User::getByUsername($username);
                $this->assertNotNull($user);

                // set user's group
                $this->setGetArray(array('id' => $hundredUsersGroupId));
                $this->setPostArray(array(
                    'GroupUserMembershipForm' => array('userMembershipData' => array($user->id)
                    )));
                $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
                $user->forgetAll();
                $user                       = User::getByUsername($username);
                $this->assertNotNull($user);
                $hundredUsersGroup->forgetAll();
                $hundredUsersGroup          = Group::getById($hundredUsersGroupId);
                $this->assertContains($hundredUsersGroup, $user->groups);
                $hundredUsersGroupMembers[] = $user;
                unset($user);
            }

            // create a contact with permissions to Single User Group group
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            // go ahead and create contact with Single User Group group given readwrite.
            $startingState                  = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'John',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
                'explicitReadWriteModelPermissions' => array(
                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $singleUserGroupId
                ))));
            $startTime                      = microtime(true);
            $url                            = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $timeTakenForSingleUserGroup    = microtime(true) - $startTime;
            $johnDoeContactId               = intval(substr($url, strpos($url, 'id=') + 3));
            $johnDoeContact                 = Contact::getById($johnDoeContactId);
            $this->assertNotNull($johnDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $johnDoeContactId));
            $content                        = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Single User Group', $content);

            // ensure singleUserGroup members have access
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($singleUserGroupMembers[0]->username);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $johnDoeContactId));
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            // create a contact with permissions to Hundred Users Group group
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'Jim',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
                'explicitReadWriteModelPermissions' => array(
                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $hundredUsersGroupId
                ))));
            $startTime                      = microtime(true);
            $url                            = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $timeTakenForHundredUsersGroup  = microtime(true) - $startTime;
            $jimDoeContactId                = intval(substr($url, strpos($url, 'id=') + 3));
            $jimDoeContact                  = Contact::getById($jimDoeContactId);
            $this->assertNotNull($jimDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $jimDoeContactId));
            $content                        = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Hundred Users Group', $content);

            $this->resetPostArray();
            // ensure hundredUsersGroup members have access
            foreach ($hundredUsersGroupMembers as $member)
            {
                $this->logoutCurrentUserLoginNewUserAndGetByUsername($member->username);
                $this->setGetArray(array('id' => $jimDoeContactId));
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');
            }
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            var_dump("Time taken for single user Group save:");
            var_dump($timeTakenForSingleUserGroup);
            var_dump("Time taken for hundreds user Group save:");
            var_dump($timeTakenForHundredUsersGroup);
            var_dump("Count of members of hundred users group");
            var_dump(count($hundredUsersGroupMembers));
        }
    }
?>