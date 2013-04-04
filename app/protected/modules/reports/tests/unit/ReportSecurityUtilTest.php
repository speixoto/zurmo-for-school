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

    class ReportSecurityUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('bobby');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCanCurrentUserCanAccessModule()
        {
            $this->assertTrue(ReportSecurityUtil::canCurrentUserCanAccessModule('AccountsModule'));
            $this->assertTrue(ReportSecurityUtil::canCurrentUserCanAccessModule('ContactsModule'));

        }

        public function testCanCurrentUserAccessAllComponentsWithSuperUser()
        {
            $componentForms = array();
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $componentForms[] = $filter;
            $filter2                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType = 'hasOne___name';
            $filter2->operator                    = OperatorRules::TYPE_EQUALS;
            $filter2->value                       = 'Jason';
            $componentForms[] = $filter2;
            $this->assertTrue(ReportSecurityUtil::canCurrentUserAccessAllComponents($componentForms));
        }

        public function testCanCurrentUserAccessAllComponentsWithLimitedAccessUser()
        {
            Yii::app()->user->userModel = User::getByUserName('bobby');
            $componentForms = array();
            $filter                              = new FilterForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'officePhone';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '123456789';
            $componentForms[] = $filter;
            $this->assertFalse(ReportSecurityUtil::canCurrentUserAccessAllComponents($componentForms));

            Yii::app()->user->userModel->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            Yii::app()->user->userModel->save();
            $this->assertTrue(ReportSecurityUtil::canCurrentUserAccessAllComponents($componentForms));

            //Test that bobby cannot access the related contacts
            $filter2                              = new FilterForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType = 'contacts___website';
            $filter2->operator                    = OperatorRules::TYPE_EQUALS;
            $filter2->value                       = 'zurmo.com';
            $componentForms[] = $filter2;
            $this->assertFalse(ReportSecurityUtil::canCurrentUserAccessAllComponents($componentForms));


            //Now add access, and bobby can.
            Yii::app()->user->userModel->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            Yii::app()->user->userModel->save();
            $this->assertTrue(ReportSecurityUtil::canCurrentUserAccessAllComponents($componentForms));
        }
    }
?>