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
    * Test RedBeanModelAttributeValueToExportValueAdapter functions.
    */
    class ReportToExportAdapterTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            $multiSelectValues = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestMultiDropDown');
            $customFieldData->serializedData = serialize($multiSelectValues);
            $save = $customFieldData->save();
            assert('$save'); // Not Coding Standard

            $tagCloudValues = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('ExportTestTagCloud');
            $customFieldData->serializedData = serialize($tagCloudValues);
            $save = $customFieldData->save();
            assert('$save'); // Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testGetDataWithNoRelationsSet()
        {
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->string = 'xFirst';
            $reportModelTestItemX->phone = 'xLast';
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'FullName';

            $reportModelTestItemY = new ReportModelTestItem();
            $reportModelTestItemY->string = 'yFirst';
            $reportModelTestItemY->phone = 'yLast';
            $displayAttributeY    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeY->setModelAliasUsingTableAliasName('def');
            $displayAttributeY->attributeIndexOrDerivedType = 'FullName';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemY, 'def');
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();

            $model1 = $reportResultsRowData->getModel('attribute0');
            $this->assertEquals('xFirst xLast', strval($model1));
            $model2 = $reportResultsRowData->getModel('attribute1');
            $this->assertEquals('yFirst yLast', strval($model2)); 
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            $compareData = array(array('Full Name', 'Full Name'), array('xFirst xLast', 'yFirst yLast'));
            $this->assertEquals($compareData, $data);
        }
    }
?>