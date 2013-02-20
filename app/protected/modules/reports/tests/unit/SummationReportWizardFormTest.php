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
    * Test ReportWizardForm validation functions.
    */
    class SummationReportWizardFormTest extends ZurmoBaseTest
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
        
        public function testValidateFilters()
        {
            $summationReportWizardForm               = new SummationReportWizardForm();                  
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $filter->value                           = 'Zurmo';
            $summationReportWizardForm->filters = array($filter);            
            $summationReportWizardForm->validateFilters();
            $this->assertFalse($summationReportWizardForm->hasErrors());            
        }
        
        public function testValidateFiltersForErrors()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;            
            $summationReportWizardForm->filters = array($filter);            
            $content = $summationReportWizardForm->validateFilters();             
            $this->assertTrue(strpos($content,  'Value cannot be blank.')           === false);
            $this->assertTrue($summationReportWizardForm->hasErrors()); 
        }
        
        public function testValidateFiltersStructure()
        {
            $summationReportWizardForm                    = new SummationReportWizardForm();
            $filter                                       = new FilterForReportForm('ReportsTestModule', 
                                                                'ReportModelTestItem', Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType          = 'createdDateTime';
            $filter->operator                             = OperatorRules::TYPE_BETWEEN;            
            $filter->value                                = '2013-02-19 00:00';
            $filter->secondValue                          = '2013-02-20 00:00';                   
            $summationReportWizardForm->filters           = array($filter);  
            $summationReportWizardForm->filtersStructure  = '1';            
            $summationReportWizardForm->validateFiltersStructure();            
            $this->assertFalse($summationReportWizardForm->hasErrors());
        }

        public function testValidateFiltersStructureForError()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'createdDateTime';
            $filter->operator                        = OperatorRules::TYPE_BETWEEN;            
            $filter->value                           = '2013-02-19 00:00';
            $filter->secondValue                     = '2013-02-20 00:00';                   
            $summationReportWizardForm->filters = array($filter);  
            $summationReportWizardForm->filtersStructure  = '2';            
            $content = $summationReportWizardForm->validateFiltersStructure();            
            $this->assertTrue(strpos($content,  'The structure is invalid. Please use only integers less than 2.')           === false);            
            $this->assertTrue($summationReportWizardForm->hasErrors());
        }         
        
        public function testValidateDisplayAttributes()
        {     
            $summationReportWizardForm            = new SummationReportWizardForm();
            $reportModelTestItem                  = new ReportModelTestItem();
            $reportModelTestItem->date            = '2013-02-12';
            $displayAttribute                     = new DisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem', Report::TYPE_SUMMATION);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $summationReportWizardForm->displayAttributes  = array($displayAttribute);
            $summationReportWizardForm->validateDisplayAttributes();
            $this->assertFalse($summationReportWizardForm->hasErrors());
        }
        
        public function testValidateDisplayAttributesForError()
        {                       
            $summationReportWizardForm          = new SummationReportWizardForm();
            
            $displayAttribute                     = new DisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem', Report::TYPE_SUMMATION);
            $summationReportWizardForm->displayAttributes = array();                                                        
            $content = $summationReportWizardForm->validateDisplayAttributes();            
            $this->assertTrue(strpos($content,  'At least one display column must be selected')           === false);            
            $this->assertTrue($summationReportWizardForm->hasErrors());
        }        
        
        public function testValidateOrderBys()
        {
            $summationReportWizardForm               = new SummationReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                          = 'desc';
            $summationReportWizardForm->orderBys     = array($orderBy);
            $summationReportWizardForm->validateOrderBys();
            $this->assertFalse($summationReportWizardForm->hasErrors());
        }
        
        public function testValidateOrderBysForErrorsButIssueNoOrderByColumn()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);            
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                           = 'desc';
            $summationReportWizardForm->orderBys = array($orderBy);            
            $summationReportWizardForm->validateOrderBys();             
            $this->assertFalse($summationReportWizardForm->hasErrors());
        }

        public function testValidateOrderBysForErrors()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);            
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $orderBy->order                           = 'desc1';
            $summationReportWizardForm->orderBys = array($orderBy);            
            $content = $summationReportWizardForm->validateOrderBys();
            $this->assertTrue(strpos($content,  'Order must be asc or desc.')           === false);            
            $this->assertTrue($summationReportWizardForm->hasErrors());
        }         

        public function testValidateSpotConversionCurrencyCode()
        {
           $summationReportWizardForm                         = new SummationReportWizardForm();
           $summationReportWizardForm->currencyConversionType = 2;
           $summationReportWizardForm->spotConversionCurrencyCode = 'CAD';
           $summationReportWizardForm->validateSpotConversionCurrencyCode();
           $this->assertFalse($summationReportWizardForm->hasErrors());
        }
        
        public function testValidateSpotConversionCurrencyCodeForErrors()
        {
           $summationReportWizardForm                         = new SummationReportWizardForm();
           $summationReportWizardForm->currencyConversionType = 3;           
           $summationReportWizardForm->spotConversionCurrencyCode = null;
           $summationReportWizardForm->validateSpotConversionCurrencyCode();           
           $this->assertTrue($summationReportWizardForm->hasErrors());
        }  
        
        public function testValidateGroupBys()
        {
            $summationReportWizardForm            = new SummationReportWizardForm();
            $groupBy                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('x', $groupBy->axis);
            $groupBy->axis                        = 'y';
            $summationReportWizardForm->groupBys  = array($groupBy);
            $summationReportWizardForm->validateGroupBys();
            $this->assertFalse($summationReportWizardForm->hasErrors());
        }
        
        public function testValidateGroupBysForErrors()
        {
            $summationReportWizardForm            = new SummationReportWizardForm();
            $groupByX                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $groupByX->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('x', $groupByX->axis);
            $groupByX->axis                        = 'y';            
            $groupByY                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_SUMMATION);
            $groupByY->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('x', $groupByY->axis);
            $groupByY->axis                        = 'y';
            $summationReportWizardForm->groupBys  = array($groupByX,$groupByY);
            $content = $summationReportWizardForm->validateGroupBys();            
            $this->assertTrue(strpos($content,  'Each grouping must be unique')           === false);
            $this->assertTrue($summationReportWizardForm->hasErrors());
        }

        public function testValidateDrillDownDisplayAttributes()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $drillDownDisplayAttributes         = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem',Report::TYPE_SUMMATION);
            $drillDownDisplayAttributes->attributeIndexOrDerivedType    = 'integer__Maximum';
            $drillDownDisplayAttributes->madeViaSelectInsteadOfViaModel = true;            
            $this->assertTrue($drillDownDisplayAttributes->columnAliasName == 'col0');            
            $summationReportWizardForm->drillDownDisplayAttributes         = array($drillDownDisplayAttributes);
            $summationReportWizardForm->validateDrillDownDisplayAttributes(); 
            $this->assertFalse($summationReportWizardForm->hasErrors());            
        }
        
        public function testValidateDrillDownDisplayAttributesForErrors()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $drillDownDisplayAttributes         = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem',Report::TYPE_SUMMATION);                                                       
            $summationReportWizardForm->drillDownDisplayAttributes         = array($drillDownDisplayAttributes);
            $content = $summationReportWizardForm->validateDrillDownDisplayAttributes(); 
            $this->assertTrue(strpos($content,  'Label cannot be blank.')           === false);
            $this->assertTrue($summationReportWizardForm->hasErrors());           
        }

        public function testValidateChart()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $chart                              = new ChartForReportForm();
            $chart->firstSeries                 = 'dropDown';
            $chart->firstRange                  = 'float__Summation';
            $validated                          = $chart->validate();
            $this->assertTrue($validated);
            $summationReportWizardForm->chart = $chart;
            $summationReportWizardForm->validateChart();
            $this->assertFalse($summationReportWizardForm->hasErrors());            
        }

        public function testValidateChartForErrorsButIssue()
        {
            $summationReportWizardForm          = new SummationReportWizardForm();
            $chart                              = new ChartForReportForm();            
            $summationReportWizardForm->chart = $chart;
            $chart->firstSeries                 = '';
            $summationReportWizardForm->validateChart();
            $summationReportWizardForm->chart->validate();
            $this->assertFalse($summationReportWizardForm->hasErrors());            
        }         
    }
?>    