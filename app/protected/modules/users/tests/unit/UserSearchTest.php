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
    }
?>
