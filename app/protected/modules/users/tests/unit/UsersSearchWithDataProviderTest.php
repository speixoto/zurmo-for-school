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
     * Tests on users with data provider
     */
    class UsersSearchWithDataProviderTest extends ZurmoDataProviderBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testDefaultFullnameOrderOnUsers()
        {
            $super                              = User::getByUsername('super');
            Yii::app()->user->userModel         = $super;

            $user1                              = new User();
            $user1->username                    = 'user1';
            $user1->firstName                   = 'abel';
            $user1->lastName                    = 'zitabina';
            $user1->setPassword('myuser');
            $this->assertTrue($user1->save());

            $user2                              = new User();
            $user2->username                    = 'user2';
            $user2->firstName                   = 'zitabina';
            $user2->lastName                    = 'abel';
            $user2->setPassword('myuser');
            $this->assertTrue($user2->save());

            $user3                              = new User();
            $user3->username                    = 'user3';
            $user3->firstName                   = 'abel';
            $user3->lastName                    = 'abel';
            $user3->setPassword('myuser');
            $this->assertTrue($user3->save());

            $searchAttributeData        = array();
            $dataProvider               = new RedBeanModelDataProvider('User', null, false, $searchAttributeData);
            $data                       = $dataProvider->getData();
            $this->assertEquals($user3, $data[0]);
            $this->assertEquals($user1, $data[1]);
            $this->assertEquals($super, $data[2]);
            $this->assertEquals($user2, $data[3]);
        }
    }
?>