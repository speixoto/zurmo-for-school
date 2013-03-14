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

    class DataToReportUtilTest extends ZurmoBaseTest
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

        public function testResolveReportByWizardPostData()
        {
            $bobby               = User::getByUserName('bobby');
            $wizardFormClassName = 'RowsAndColumnsReportWizardForm';
            $report              = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $data                     = array();
            $data['moduleClassName']  = 'ReportsTestModule';
            $data['description']      = 'a description';
            $data['name']             = 'name';
            $data['filtersStructure'] = '1 AND 2';
            $data['ownerId']          = $bobby->id;
            $data['currencyConversionType']     = Report::CURRENCY_CONVERSION_TYPE_SPOT;
            $data['spotConversionCurrencyCode'] = 'EUR';
            $data[ComponentForReportForm::TYPE_FILTERS][] = array('attributeIndexOrDerivedType' => 'date',
                'operator'                    => 'between',
                'value'                       => '2/24/12',
                'secondValue'                 => '2/28/12');

            DataToReportUtil::resolveReportByWizardPostData($report, array('RowsAndColumnsReportWizardForm' => $data),
                                                            $wizardFormClassName);
            $this->assertEquals('ReportsTestModule',                    $report->getModuleClassName());
            $this->assertEquals('a description',                        $report->getDescription());
            $this->assertEquals('name',                                 $report->getName());
            $this->assertEquals('1 AND 2',                              $report->getFiltersStructure());
            $this->assertEquals($bobby->id,                             $report->getOwner()->id);
            $this->assertEquals(Report::CURRENCY_CONVERSION_TYPE_SPOT,  $report->getCurrencyConversionType());
            $this->assertEquals('EUR',                                  $report->getSpotConversionCurrencyCode());

            $filters = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('2012-02-24', $filters[0]->value);
            $this->assertEquals('2012-02-28', $filters[0]->secondValue);


            //todo: test rest of components
        }

        public function testResolveFiltersAndDateConvertsProperlyToDbFormat()
        {
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $data   = array();
            $data[ComponentForReportForm::TYPE_FILTERS][] = array('attributeIndexOrDerivedType' => 'date',
                                                                  'operator'                    => 'between',
                                                                  'value'                       => '2/24/12',
                                                                  'secondValue'                 => '2/28/12');

            DataToReportUtil::resolveFilters($data, $report);
            $filters = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('2012-02-24', $filters[0]->value);
            $this->assertEquals('2012-02-28', $filters[0]->secondValue);
        }

        public function testSanitizeFiltersData()
        {
            //test specifically for date/dateTime conversion from local to db format.
            $filtersData         = array();
            $filtersData[0]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12');
            $filtersData[1]      = array('attributeIndexOrDerivedType' => 'dateTime', 'value' => '2/25/12');
            $filtersData[2]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12',
                                         'secondValue'                 => '2/28/12');
            $sanitizedFilterData = DataToReportUtil::sanitizeFiltersData('ReportsTestModule',
                                                                         Report::TYPE_ROWS_AND_COLUMNS, $filtersData);
            $this->assertEquals('2012-02-24', $sanitizedFilterData[0]['value']);
            $this->assertEquals('2012-02-25', $sanitizedFilterData[1]['value']);
            $this->assertEquals('2012-02-24', $sanitizedFilterData[2]['value']);
            $this->assertEquals('2012-02-28', $sanitizedFilterData[2]['secondValue']);
        }

    }
?>