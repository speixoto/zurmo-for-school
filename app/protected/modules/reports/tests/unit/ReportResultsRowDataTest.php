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

    class ReportResultsRowDataTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
        }

        public function testGetModel()
        {
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->firstName = 'xFirst';
            $reportModelTestItemX->lastName = 'xLast';
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'FullName';

            $reportModelTestItemY = new ReportModelTestItem();
            $reportModelTestItemY->firstName = 'yFirst';
            $reportModelTestItemY->lastName = 'yLast';
            $displayAttributeY    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeY->setModelAliasUsingTableAliasName('def');
            $displayAttributeY->attributeIndexOrDerivedType = 'FullName';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemY, 'def');

            $model1 = $reportResultsRowData->getModel('attribute0');
            $this->assertEquals('xFirst xLast', strval($model1));
            $model2 = $reportResultsRowData->getModel('attribute1');
            $this->assertEquals('yFirst yLast', strval($model2));
        }

        public function testGettingAttributeForString()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->string = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'string';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someString', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeForOwnedString()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->primaryAddress->street1 = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'primaryAddress___street1';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someString', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeForLikeContactState()
        {
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->likeContactState = $reportModelTestItem7;
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'likeContactState';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someName', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeWhenMadeViaSelect()
        {
            $displayAttributeX = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->attributeIndexOrDerivedType = 'integer__Maximum';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addSelectedColumnNameAndValue('col5', 55);

            $this->assertEquals(55, $reportResultsRowData->col5);
        }

        public function testGetDataParamsForDrillDownAjaxCall()
        {
            $reportModelTestItem                       = new ReportModelTestItem();
            $reportModelTestItem->dropDown->value      = 'dropDownValue';
            $reportModelTestItem->currencyValue->value = 45.05;
            $reportModelTestItem->owner                = Yii::app()->user->userModel;

            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItem->likeContactState = $reportModelTestItem7;

            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'dropDown';
            $displayAttribute1->valueUsedAsDrillDownFilter = true;
            $displayAttribute1->setModelAliasUsingTableAliasName('abc');

            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';

            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType = 'currencyValue';
            $displayAttribute3->valueUsedAsDrillDownFilter = true;
            $displayAttribute3->setModelAliasUsingTableAliasName('abc');

            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType = 'createdDateTime__Day';
            $displayAttribute4->valueUsedAsDrillDownFilter = true;


            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType = 'owner__User';
            $displayAttribute5->valueUsedAsDrillDownFilter = true;
            $displayAttribute5->setModelAliasUsingTableAliasName('abc');

            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType = 'likeContactState';
            $displayAttribute6->valueUsedAsDrillDownFilter = true;
            $displayAttribute6->setModelAliasUsingTableAliasName('abc');

            $reportResultsRowData = new ReportResultsRowData(array($displayAttribute1, $displayAttribute2,
                                                                   $displayAttribute3, $displayAttribute4,
                                                                   $displayAttribute5, $displayAttribute6), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItem, 'abc');
            $reportResultsRowData->addSelectedColumnNameAndValue('col3', 15);
            $data   = $reportResultsRowData->getDataParamsForDrillDownAjaxCall();
            $userId = Yii::app()->user->userModel->id;
            $this->assertEquals('dropDownValue', $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('dropDown')]);
            $this->assertEquals(45.05,           $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('currencyValue')]);
            $this->assertEquals(15,              $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('createdDateTime__Day')]);
            $this->assertEquals($userId,         $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('owner__User')]);
            $this->assertEquals('someName',      $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('likeContactState')]);
        }

        public function testResolveDataParamKeyForDrillDown()
        {
            $this->assertEquals(ReportResultsRowData::DRILL_DOWN_GROUP_BY_VALUE_PREFIX . 'abc',
                                ReportResultsRowData::resolveDataParamKeyForDrillDown('abc'));
        }
    }
?>