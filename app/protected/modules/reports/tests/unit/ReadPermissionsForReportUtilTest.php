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

    class ReadPermissionsForReportUtilTest extends ZurmoBaseTest
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

        public function testResolveReadPermissionAttributeIndexesForComponentWithNoNesting()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'name';
            $indexes                             = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $this->assertEquals(array('ReadOptimization'), $indexes);
        }

        public function testResolveReadPermissionAttributeIndexesForComponentWithSingleNesting()
        {
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $this->assertEquals(array('ReadOptimization', 'hasOne___ReadOptimization'), $indexes);
        }

        public function testResolveReadPermissionAttributeIndexesForComponentWithDoubleNesting()
        {
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___hasMany3___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $compareIndexes = array('ReadOptimization',
                                    'hasOne___ReadOptimization',
                                    'hasOne___hasMany3___ReadOptimization');
            $this->assertEquals($compareIndexes, $indexes);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $compareIndexes                        = array('ReadOptimization',
                                                           'meetings___ReadOptimization');
            $this->assertEquals($compareIndexes, $indexes);
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___meetings___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $compareIndexes                        = array('ReadOptimization',
                                                           'opportunities___ReadOptimization',
                                                           'opportunities___meetings___ReadOptimization');
            $this->assertEquals($compareIndexes, $indexes);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $compareIndexes                        = array('ReadOptimization',
                                                           'Account__activityItems__Inferred___ReadOptimization');
            $this->assertEquals($compareIndexes, $indexes);
        }

        public function testInferredRelationModelAttributeWithYetAnotherRelation()
        {
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___name';
            $indexes                               = ReadPermissionsForReportUtil::resolveAttributeIndexesByComponent($filter);
            $compareIndexes                        = array('ReadOptimization',
                                                           'Account__activityItems__Inferred___ReadOptimization',
                                                           'Account__activityItems__Inferred___opportunities___ReadOptimization');
            $this->assertEquals($compareIndexes, $indexes);
        }
    }
?>