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

    class UserLDAPTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        /**
        *user exists in zurmo but not on ldap
        */
        public function testUserExitsInZurmoButNotOnldap()
        {            
            //Now attempt to login as bill a user in zurmo but not on ldap
            $bill       = User::getByUsername('abcdefg');
            $this->assertEquals(md5('abcdefgN4'), $bill->hash);
            $bill->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB, RIGHT::ALLOW);
            $this->assertTrue($bill->save());
            //for normal user
            $identity = new UserIdentity('abcdefg', 'abcdefgN4');
            $authenticated = $identity->authenticate();
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);
            $bill->forget();            
        }
        
        /**
        *one where it exists in both, but the pass is wrong for ldap, but ok for zurmo pass.
        */
        public function testUserExitsInBothButWrongPasswordForldap()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //creating user same as on ldap with different password
            $admin = new User();
            $admin->username           = 'admin';
            $admin->title->value       = 'Mr.';
            $admin->firstName          = 'admin';
            $admin->lastName           = 'admin';
            $admin->setPassword('test123');
            $this->assertTrue($admin->save());
            $admin->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB, RIGHT::ALLOW);
            $this->assertTrue($admin->save());        
            $identity = new UserLDAPIdentity('admin','test123');                        
            $authenticated = $identity->authenticate(true);
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);     
        }
        
        /**
        *one for when the user exists in ldap but not zurmo
        */
        public function testUserExitsInldapNotInZurmo()
        {
            Yii::app()->user->userModel = User::getByUsername('super');     
            $identity = new UserLDAPIdentity('john','johnldap');                        
            $authenticated = $identity->authenticate(true);
            $this->assertEquals(1, $identity->errorCode);
            $this->assertFalse($authenticated);     
        }                
        
        /**
        *one for when the user exists in ldap and zurmo
        */
        public function testUserExitsInldapAndZurmo()
        {
            Yii::app()->user->userModel = User::getByUsername('super');     
            $identity = new UserLDAPIdentity('admin','ldap123');                        
            $authenticated = $identity->authenticate(true);
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);     
        }                  
    }
?>
