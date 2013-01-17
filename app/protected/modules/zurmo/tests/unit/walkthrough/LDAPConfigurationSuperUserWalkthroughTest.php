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
     * Testing the views for configuring LDAP server
     */
    class LDAPConfigurationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/ldap/configurationEditLDAP');
        }

        public function testSuperUserModifyLDAPConfiguration()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
           

            //Change LDAP settings
            $this->resetGetArray();
            $this->setPostArray(array('LDAPConfigurationForm' => array(
                                    'host'                              => '192.168.1.185',
                                    'port'                              => '389',
                                    'bindRegisteredDomain'              => 'admin',
                                    'bindPassword'                      => 'ldap123',
                                    'baseDomain'                        => 'dc=debuntu,dc=local',
									'enabled'                        => '1')));
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/ldap/configurationEditLDAP');
            $this->assertEquals('LDAP Configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            //Confirm the setting did in fact change correctly
            $authenticationHelper = new ZurmoAuthenticationHelper;
            $this->assertEquals('192.168.1.185',        Yii::app()->authenticationHelper->ldapHost);
            $this->assertEquals('389',                  Yii::app()->authenticationHelper->ldapPort);
            $this->assertEquals('admin',                Yii::app()->authenticationHelper->ldapBindRegisteredDomain);
            $this->assertEquals('ldap123',              Yii::app()->authenticationHelper->ldapBindPassword);
            $this->assertEquals('dc=debuntu,dc=local',  Yii::app()->authenticationHelper->ldapBaseDomain);
            $this->assertEquals('1',                    Yii::app()->authenticationHelper->enabled);
        }
        
        public function testSuperUserTestLDAPConnection()
        {
         $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
         //check LDAP connection         
         $this->resetGetArray();
         $this->setPostArray(array('LDAPConfigurationForm' => array(
                                    'host'                              => '192.168.1.185',
                                    'port'                              => '389',
                                    'bindRegisteredDomain'              => 'admin',
                                    'bindPassword'                      => 'ldap123',
                                    'baseDomain'                        => 'dc=debuntu,dc=local',
									'enabled'                        => '1')));
         $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/ldap/testConnection');  
         //$this->assertTrue(strpos($content, "Successfully Connected to LDAP Server") > 0);         
         //$this->assertEquals('Successfully Connected to LDAP Server', $content);
        }
    }
?>