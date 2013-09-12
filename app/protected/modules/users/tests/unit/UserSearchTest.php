<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class UserSearchTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetUsersByPartialFullName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            UserTestHelper::createBasicUser('Azo');
            UserTestHelper::createBasicUser('Bdo');
            UserTestHelper::createBasicUser('Abzo');

            $users = UserSearch::getUsersByPartialFullName('A', 5);
            $this->assertEquals(2, count($users));
            $users = UserSearch::getUsersByPartialFullName('bd', 5);
            $this->assertEquals(1, count($users));
            $users = UserSearch::getUsersByPartialFullName('Cz', 5);
            $this->assertEquals(0, count($users));
            $users = UserSearch::getUsersByPartialFullName('Ab', 5);
            $this->assertEquals(1, count($users));
        }

        public function testGetUsersByEmailAddress()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = UserTestHelper::createBasicUser('Steve');
            $user->primaryEmail->emailAddress = 'steve@example.com';
            $user->primaryEmail->optOut       = 1;
            $user->primaryEmail->isInvalid    = 0;
            $this->assertTrue($user->save());

            $users = UserSearch::getUsersByEmailAddress('steve@example.com');
            $this->assertEquals(1, count($users));
            $this->assertEquals($user->id, $users[0]->id);
        }

        /**
         * Test users count using NonSystemUsersStateMetadataAdapter
         */
        public function testGetUsersListUsingNonSystemUsersStateMetadataAdapter()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $users                      = User::getAll();
            $this->assertEquals(5, count($users));
            $user                       = UserTestHelper::createBasicUser('mysysuser');
            $user->setIsSystemUser();
            $this->assertTrue($user->save());

            $nonSystemUsersStateMetadataAdapter = new NonSystemUsersStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $metadata                           = $nonSystemUsersStateMetadataAdapter->getAdaptedDataProviderMetadata();
            $joinTablesAdapter                  = new RedBeanModelJoinTablesQueryAdapter('User');
            $where  = RedBeanModelDataProvider::makeWhere('User', $metadata, $joinTablesAdapter);
            $models = User::getSubset($joinTablesAdapter, null, null, $where, null);
            $this->assertEquals(5, count($models));

            $actualUsers = User::getAll();
            $this->assertEquals(6, count($actualUsers));

            unset($user);
            $user   = User::getByUsername('mysysuser');
            $this->assertTrue((bool)$user->isSystemUser);

            $user->setIsNotSystemUser();
            $this->assertTrue($user->save());
            unset($user);
            $user   = User::getByUsername('mysysuser');
            $this->assertEquals(0, $user->isSystemUser);

            $where  = RedBeanModelDataProvider::makeWhere('User', $metadata, $joinTablesAdapter);
            $models = User::getSubset($joinTablesAdapter, null, null, $where, null);
            $this->assertEquals(6, count($models));
        }
    }
?>
