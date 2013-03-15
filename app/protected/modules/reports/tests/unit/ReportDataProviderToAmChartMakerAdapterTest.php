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

    class ReportDataProviderToAmChartMakerAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveFirstSeriesValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName('abc');
            $this->assertEquals('FirstSeriesValueabc', $value);
        }

        public function testResolveFirstSeriesDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName('abc');
            $this->assertEquals('FirstSeriesDisplayLabelabc', $value);
        }

        public function testResolveFirstRangeDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName('abc');
            $this->assertEquals('FirstRangeDisplayLabelabc', $value);
        }

        public function testResolveFirstSeriesFormattedValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesFormattedValueName('abc');
            $this->assertEquals('FirstSeriesFormattedValueabc', $value);
        }

        public function testResolveSecondSeriesValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName('abc');
            $this->assertEquals('SecondSeriesValueabc', $value);
        }

        public function testResolveSecondSeriesDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName('abc');
            $this->assertEquals('SecondSeriesDisplayLabelabc', $value);
        }

        public function testResolveSecondSeriesFormattedValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesFormattedValueName('abc');
            $this->assertEquals('SecondSeriesFormattedValueabc', $value);
        }

        public function testGetType()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
           $this->assertEquals('Bar2D', $adapter->getType());
        }

        public function testGetDataNonStacked()
        {
            $data                       = array('redbluegreen');
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $this->assertEquals(array('redbluegreen'), $adapter->getData());
        }

        public function testGetDataStacked()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $data                       = array(1 =>
                                                array(ReportDataProviderToAmChartMakerAdapter::FIRST_SERIES_VALUE . 0
                                                => 500.42134),
                                                2 =>
                                                array(ReportDataProviderToAmChartMakerAdapter::SECOND_SERIES_VALUE . 0
                                                => 32),
            );
            $secondSeriesValueData      = array(0);
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'StackedBar3D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $displayAttribute           = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                          Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'float__Summation';
            $report->addDisplayAttribute($displayAttribute);
            $displayAttribute2          = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                          Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer__Summation';
            $report->addDisplayAttribute($displayAttribute2);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $compareData = array(1 => array('FirstSeriesValue0'           => 500.42134,
                                            'FirstSeriesFormattedValue0'  => 500.421),
                                 2 => array('SecondSeriesValue0'          => 32,
                                            'SecondSeriesFormattedValue0' => 32),
            );
            $this->assertEquals($compareData, $adapter->getData());
            //todo: more coverage needed. date, dateTime, string, and currency
        }

        public function testGetSecondSeriesValueCount()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                       $secondSeriesValueCount);
            $this->assertEquals(5, $adapter->getSecondSeriesValueCount());
        }

        public function testIsStackedFalse()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
            $this->assertFalse($adapter->isStacked());
        }

        public function testIsStackedTrue()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'StackedBar3D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $this->assertTrue($adapter->isStacked());
        }

        public function testGetSecondSeriesDisplayLabelByKey()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array('abc', 'def');
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
            $this->assertEquals('def', $adapter->getSecondSeriesDisplayLabelByKey(1));
        }
    }
?>