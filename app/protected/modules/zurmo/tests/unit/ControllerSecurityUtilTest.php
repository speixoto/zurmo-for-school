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

    class ControllerSecurityUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createAccounts();
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->clientScript->reset();
        }

        public function testResolveAccessCanCurrentUserWriteModule()
        {
            $betty                      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;
            TestHelpers::createControllerAndModuleByRoute('accounts/default');
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModule('Accounts', true);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals('failure', $content);
            }
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModule('Accounts', false);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $compareString = 'You have tried to access a page you do not have access to';
                $this->assertFalse(strpos($this->endAndGetOutputBuffer(), $compareString) === false);
            }

            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            TestHelpers::createControllerAndModuleByRoute('accounts/default');
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModule('Accounts', true);
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModule('Accounts', false);
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals(null, $content);
            }
            catch (ExitException $e)
            {
                $this->endPrintOutputBufferAndFail();
            }
        }

        public function testResolveAccessCanCurrentUserReadModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts                   = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $betty                      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;
            TestHelpers::createControllerAndModuleByRoute('accounts/default');
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($accounts[0], true);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals('failure', $content);
            }
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($accounts[0], false);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $compareString = 'You have tried to access a page you do not have access to';
                $this->assertFalse(strpos($this->endAndGetOutputBuffer(), $compareString) === false);
            }

            $account = AccountTestHelper::createAccountByNameForOwner('BettyInc', $betty);
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account, true);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account, false);
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals(null, $content);
            }
            catch (ExitException $e)
            {
                $this->endPrintOutputBufferAndFail();
            }
        }

        /**
         * @depends testResolveAccessCanCurrentUserReadModel
         */
        public function testResolveAccessCanCurrentUserWriteModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts                   = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $betty                      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;

            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($accounts[0], true);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals('failure', $content);
            }
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($accounts[0], false);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $compareString = 'You have tried to access a page you do not have access to';
                $this->assertFalse(strpos($this->endAndGetOutputBuffer(), $compareString) === false);
            }

            $accounts = Account::getByName('BettyInc');
            $this->assertEquals(1, count($accounts));
            $account = $accounts[0];
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($account, true);
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($account, false);
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals(null, $content);
            }
            catch (ExitException $e)
            {
                $this->endPrintOutputBufferAndFail();
            }
        }

        /**
         * @depends testResolveAccessCanCurrentUserWriteModel
         */
        public function testResolveAccessCanCurrentUserDeleteModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts                   = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $betty                      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;

            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($accounts[0], true);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals('failure', $content);
            }
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($accounts[0], false);
                $this->endPrintOutputBufferAndFail();
            }
            catch (ExitException $e)
            {
                $compareString = 'You have tried to access a page you do not have access to';
                $this->assertFalse(strpos($this->endAndGetOutputBuffer(), $compareString) === false);
            }

            $accounts = Account::getByName('BettyInc');
            $this->assertEquals(1, count($accounts));
            $account = $accounts[0];
            $this->startOutputBuffer();
            try
            {
                ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($account, true);
                ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($account, false);
                $content = $this->endAndGetOutputBuffer();
                $this->assertEquals(null, $content);
            }
            catch (ExitException $e)
            {
                $this->endPrintOutputBufferAndFail();
            }
        }
    }
?>