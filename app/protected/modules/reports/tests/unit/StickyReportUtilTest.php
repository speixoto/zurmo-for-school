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

    class StickyReportUtilTest extends ZurmoBaseTest
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

        public function testResolveStickyDataToReport()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $report                              = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $report->addFilter($filter);
            $stickyData                                         = array();
            $stickyData[ComponentForReportForm::TYPE_FILTERS][] = array('value' => 'changedValue');
            StickyReportUtil::resolveStickyDataToReport($report, $stickyData);
            $filters                             = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('changedValue', $filters[0]->value);

            //Test where the stickyData is malformed.
            $stickyData   = array();
            $stickyData[] = array('value' => 'changedValue2');
            StickyReportUtil::resolveStickyDataToReport($report, $stickyData);
            $filters                             = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('changedValue', $filters[0]->value);
        }

        //todo: test when isNull or isNotNull that the empty string gets converted properly to null. also check date
        //since it uses value type, we might need to check valueType sometimes too and convert to null. not sure.
    }
?>