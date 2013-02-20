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
    class MatrixReportWizardFormTest extends ZurmoBaseTest
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
            $matrixReportWizardForm                  = new MatrixReportWizardForm();                  
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $filter->value                           = 'Zurmo';
            $matrixReportWizardForm->filters         = array($filter);            
            $matrixReportWizardForm->validateFilters();
            $this->assertFalse($matrixReportWizardForm->hasErrors());            
        }
        
        public function testValidateFiltersForErrors()
        {
            $matrixReportWizardForm          = new MatrixReportWizardForm();
            
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;            
            $matrixReportWizardForm->filters = array($filter);            
            $content = $matrixReportWizardForm->validateFilters();             
            $this->assertTrue(strpos($content,  'Value cannot be blank.')           === false);
            $this->assertTrue($matrixReportWizardForm->hasErrors()); 
        }

        public function testValidateFiltersStructure()
        {
            $matrixReportWizardForm                       = new MatrixReportWizardForm();
            $filter                                       = new FilterForReportForm('ReportsTestModule', 
                                                                'ReportModelTestItem', Report::TYPE_MATRIX);
            $filter->attributeIndexOrDerivedType          = 'createdDateTime';
            $filter->operator                             = OperatorRules::TYPE_BETWEEN;            
            $filter->value                                = '2013-02-19 00:00';
            $filter->secondValue                          = '2013-02-20 00:00';                   
            $matrixReportWizardForm->filters              = array($filter);  
            $matrixReportWizardForm->filtersStructure     = '1';            
            $matrixReportWizardForm->validateFiltersStructure();            
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }
        
        public function testValidateFiltersStructureForError()
        {
            $matrixReportWizardForm          = new MatrixReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'createdDateTime';
            $filter->operator                        = OperatorRules::TYPE_BETWEEN;            
            $filter->value                           = '2013-02-19 00:00';
            $filter->secondValue                     = '2013-02-20 00:00';                   
            $matrixReportWizardForm->filters = array($filter);  
            $matrixReportWizardForm->filtersStructure  = '2';            
            $content = $matrixReportWizardForm->validateFiltersStructure();            
            $this->assertTrue(strpos($content,  'The structure is invalid. Please use only integers less than 2.')           === false);            
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        } 
        
        public function testValidateGroupBys()
        {
            $matrixReportWizardForm                = new MatrixReportWizardForm();
            $groupByX                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByX->attributeIndexOrDerivedType = 'string';
            $groupByX->axis                        = 'x';
            $this->assertEquals('x', $groupByX->axis);            
            $groupByY                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByY->attributeIndexOrDerivedType = 'integer';
            $groupByY->axis                        = 'y';
            $this->assertEquals('y', $groupByY->axis);
            $matrixReportWizardForm->groupBys      = array($groupByX, $groupByY);
            $matrixReportWizardForm->validateGroupBys();            
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }
        
        public function testValidateGroupBysForErrors()
        {
            $matrixReportWizardForm                = new MatrixReportWizardForm();
            $groupByX                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByX->attributeIndexOrDerivedType = 'string';
            $groupByX->axis                        = 'x';
            $this->assertEquals('x', $groupByX->axis);            
            $matrixReportWizardForm->groupBys      = array($groupByX);
            $content = $matrixReportWizardForm->validateGroupBys();            
            $this->assertTrue(strpos($content,  'At least one x-axis and one y-axis grouping must be selected')           === false);            
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }
        
        public function testValidateDisplayAttributes()
        {                       
            $matrixReportWizardForm               = new MatrixReportWizardForm();
            $reportModelTestItem                  = new ReportModelTestItem();
            $reportModelTestItem->date            = '2013-02-12';
            $displayAttribute                     = new DisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem', Report::TYPE_MATRIX);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $matrixReportWizardForm->displayAttributes     = array($displayAttribute);
            $matrixReportWizardForm->validateDisplayAttributes();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }
        
        public function testValidateDisplayAttributesForError()
        {                       
            $matrixReportWizardForm          = new MatrixReportWizardForm();
            
            $displayAttribute                     = new DisplayAttributeForReportForm('ReportsTestModule', 
                                                        'ReportModelTestItem', Report::TYPE_SUMMATION);
            $matrixReportWizardForm->displayAttributes = array();                                                        
            $content = $matrixReportWizardForm->validateDisplayAttributes();            
            $this->assertTrue(strpos($content,  'At least one display column must be selected')           === false);            
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        } 
        
        public function testValidateOrderBys()
        {
            $matrixReportWizardForm                  = new MatrixReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                          = 'desc';
            $matrixReportWizardForm->orderBys        = array($orderBy);
            $matrixReportWizardForm->validateOrderBys();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateOrderBysForErrors()
        {
            $matrixReportWizardForm          = new MatrixReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);            
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $orderBy->order                           = 'desc1';
            $matrixReportWizardForm->orderBys = array($orderBy);            
            $content = $matrixReportWizardForm->validateOrderBys();
            $this->assertTrue(strpos($content,  'Order must be asc or desc.')           === false);            
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }         

        public function testValidateSpotConversionCurrencyCode()
        {
           $matrixReportWizardForm                         = new MatrixReportWizardForm();
           $matrixReportWizardForm->currencyConversionType = 2;
           $matrixReportWizardForm->spotConversionCurrencyCode = 'CAD';
           $matrixReportWizardForm->validateSpotConversionCurrencyCode();
           $this->assertFalse($matrixReportWizardForm->hasErrors());
        }
        
        public function testValidateSpotConversionCurrencyCodeForErrors()
        {
           $matrixReportWizardForm                         = new MatrixReportWizardForm();
           $matrixReportWizardForm->currencyConversionType = 3;           
           $matrixReportWizardForm->spotConversionCurrencyCode = null;
           $matrixReportWizardForm->validateSpotConversionCurrencyCode();           
           $this->assertTrue($matrixReportWizardForm->hasErrors());
        }
              
    }
?>    