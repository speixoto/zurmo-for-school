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

    class ZurmoNestedGroupPermissionsFlushWalkThroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $super;

        protected static $jim;

        protected static $childGroup;

        protected static $parentGroup;

        protected static $contact;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            static::$super = User::getByUsername('super');
            Yii::app()->user->userModel = static::$super;
        }

        public function setup()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }


        public function testArePermissionsFlushedOnDeletingParentGroup()
        {
            // we could have used helpers to do a lot of the following stuff (such as creating users, groups,
            // etc) but we wanted to mimic user's interaction as closely as possible. Hence using walkthroughs
            // for everything

            // create Parent and Child Groups, Create Jim to be member of Child group
            $this->createUsersAndGroups();

            // create a contact with permissions to Parent group
            $this->createContactOwnedByParent();

            // ensure jim can see that contact everywhere
            $this->ensureJimCanListEditAndViewDetailOfContact();

            // delete Parent group
            $this->deleteParentGroup();

            // ensure jim can not see that contact everywhere
            $this->ensureJimCannotListEditAndViewDetailOfContact();
        }

        protected function createUsersAndGroups()
        {
            $this->createGroups();
            $this->createJimUser();
        }

        protected function createGroups()
        {
            $this->createParentGroup();
            $this->createChildGroup();
        }

        protected function createParentGroup()
        {
            static::$parentGroup    = $this->createGroup('Parent');
            static::$parentGroup->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue(static::$parentGroup->save());
            $id                     = static::$parentGroup->id;
            static::$parentGroup->forgetAll();
            static::$parentGroup    = Group::getById($id);
        }

        protected function createChildGroup()
        {
            static::$childGroup    = $this->createGroup('Child', static::$parentGroup->id);
        }

        protected function createGroup($groupName, $parentGroupId = null)
        {
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => $groupName,
                'group' => array('id' => $parentGroupId),
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $group  = Group::getByName($groupName);
            $this->assertNotNull($group);
            $this->assertEquals($groupName, strval($group));
            if (isset($parentGroupId))
            {
                $this->assertContains($group, Group::getById($parentGroupId)->groups);
            }
            return $group;
        }

        protected function createJimUser()
        {
            static::$jim    = $this->createUserWithGroup('jim', static::$childGroup->id);
        }

        protected function createUserWithGroup($username , $groupId  = null)
        {
            $this->assertNotNull($username);
            // create user
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
            $user = User::getByUsername($username);
            $this->assertNotNull($user);

            // give group membership
            if ($groupId)
            {
                $this->setGetArray(array('id' => $groupId));
                $this->setPostArray(array(
                    'GroupUserMembershipForm' => array('userMembershipData' => array($user->id)
                    )));
                $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
                $user->forgetAll();
                $user = User::getByUsername($username);
                $this->assertNotNull($user);
                $this->assertContains(Group::getById($groupId), $user->groups);
            }
            return $user;
        }

        protected function createContactOwnedByParent()
        {
            // create ContactStates
            ContactsModule::loadStartingData();
            // ensure contact states have been created
            $this->assertEquals(6, count(ContactState::GetAll()));
            // go ahead and create contact
            $startingState = ContactsUtil::getStartingState();
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                    'firstName'        => 'John',
                    'lastName'         => 'Doe',
                    'officePhone'      => '456765421',
                    'state'            => array('id' => $startingState->id),
                    'explicitReadWriteModelPermissions' => array(
                        'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                        'nonEveryoneGroup' => static::$parentGroup->id
                    ))));
            $url    = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $contactId  = intval(substr($url, strpos($url, 'id=') + 3));
            static::$contact    = Contact::getById($contactId);
            $this->assertNotNull(static::$contact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $contactId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write Parent', $content);
        }

        protected function deleteParentGroup()
        {
            $this->resetPostArray();
            $this->setGetArray(array('id' => static::$parentGroup->id));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/delete');
            try
            {
                Group::getByName('Parent');
                $this->fail('Parent group should be deleted');
            }
            catch (NotFoundException $e)
            {
                static::$parentGroup = null;
            }
        }

        protected function doesJimHasAccessToContactsOnListView()
        {
            $this->resetPostArray();
            $this->resetGetArray();
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            // get the page, ensure the name of contact does show up there.
            $content    = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default');
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            return (strpos($content, strval(static::$contact)) !== false);
        }

        protected function doesJimHasAccessToContactsOnDetailsView()
        {
            return $this->doesJimHasAccessToContactsEditOrDetailsPage();
        }

        protected function doesJimHasAccessToContactsOnEditView()
        {
            return $this->doesJimHasAccessToContactsEditOrDetailsPage(false);
        }

        protected function doesJimHasAccessToContactsEditOrDetailsPage($detailsView = true)
        {
            $this->resetPostArray();
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('jim');
            $url    = '/contacts/default/details';
            if (!$detailsView)
            {
                $url    = '/contacts/default/edit';
            }
            try
            {
                $this->setGetArray(array('id' => static::$contact->id));
                $this->runControllerWithNoExceptionsAndGetContent($url);
                $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
                return true;
            }
            catch (AccessDeniedSecurityException $e)
            {
                $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
                return false;
            }
        }

        protected function ensureJimCanListEditAndViewDetailOfContact()
        {
            $this->assertTrue($this->doesJimHasAccessToContactsOnListView());
            $this->assertTrue($this->doesJimHasAccessToContactsOnDetailsView());
            $this->assertTrue($this->doesJimHasAccessToContactsOnEditView());
        }

        protected function ensureJimCannotListEditAndViewDetailOfContact()
        {
            $this->assertTrue($this->ensureMethodThrowsExitException('doesJimHasAccessToContactsOnListView'));
            $this->assertTrue($this->ensureMethodThrowsExitException('doesJimHasAccessToContactsOnDetailsView'));
            $this->assertTrue($this->ensureMethodThrowsExitException('doesJimHasAccessToContactsOnEditView'));
        }

        protected function ensureMethodThrowsExitException($method)
        {
            try
            {
                $this->$method();
                return false;
            }
            catch (ExitException $e)
            {
                // just cleanup buffer
                $this->endAndGetOutputBuffer();
                return true;
            }
        }


        protected function runControllerWithNoExceptionsAndGetContent($route, $empty = false)
        {
            // this is same as parent except we do not care about the exception being throw
            // we did this because in this specific test Exit Exception would only be caused if
            // a user did not have access to an action
            $_SERVER['REQUEST_URI'] = '/index.php';
            $this->startOutputBuffer();
            Yii::app()->runController($route);
            $content = $this->endAndGetOutputBuffer();
            $this->doApplicationScriptPathsAllExist();
            if ($empty)
            {
                $this->assertEmpty($content);
            }
            else
            {
                $this->assertNotEmpty($content);
            }
            return $content;
        }
    }
?>