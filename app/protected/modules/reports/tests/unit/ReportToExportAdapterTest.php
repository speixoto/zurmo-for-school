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
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
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

            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert('$saved'); // Not Coding Standard               
            
            //for fullname attribute  
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->firstName = 'xFirst';
            $reportModelTestItem->lastName = 'xLast';
            $displayAttribute1    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute1->attributeIndexOrDerivedType = 'FullName';            
            
            //for boolean attribute
            $reportModelTestItem->boolean = true;
            $displayAttribute2    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute2->attributeIndexOrDerivedType = 'boolean'; 
                    
            //for date attribute                  
            $reportModelTestItem->date = '2013-02-12';
            $displayAttribute3    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute3->attributeIndexOrDerivedType = 'date';             

            //for datetime attribute
            $reportModelTestItem->dateTime = '2013-02-12 10:15';
            $displayAttribute4    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute4->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute4->attributeIndexOrDerivedType = 'dateTime'; 
            
            //for float attribute
            $reportModelTestItem->float = 10.5;
            $displayAttribute5    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute5->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute5->attributeIndexOrDerivedType = 'float'; 
            
            //for integer attribute
            $reportModelTestItem->integer = 10;
            $displayAttribute6    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute6->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute6->attributeIndexOrDerivedType = 'integer';             
            
            //for phone attribute
            $reportModelTestItem->phone = '7842151012';
            $displayAttribute7    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute7->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute7->attributeIndexOrDerivedType = 'phone'; 
                        
            //for string attribute                        
            $reportModelTestItem->string = 'xString';
            $displayAttribute8    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute8->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute8->attributeIndexOrDerivedType = 'string'; 
            
            //for textArea attribute            
            $reportModelTestItem->textArea = 'xtextAreatest';
            $displayAttribute9    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute9->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute9->attributeIndexOrDerivedType = 'textArea'; 
            
            //for url attribute            
            $reportModelTestItem->url = 'http://www.test.com'; 
            $displayAttribute10    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute10->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute10->attributeIndexOrDerivedType = 'url'; 

            //for dropdown attribute           
            $reportModelTestItem->dropDown->value = $values[1];  
            $displayAttribute11    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute11->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute11->attributeIndexOrDerivedType = 'dropDown';             

            //for currency attribute
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);  
            
            $reportModelTestItem->currencyValue   = $currencyValue;
            $displayAttribute12    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute12->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute12->attributeIndexOrDerivedType = 'currencyValue'; 
            
            //for primaryAddress attribute
            $reportModelTestItem->primaryAddress->street1 = 'someString';
            $displayAttribute13   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute13->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute13->attributeIndexOrDerivedType = 'primaryAddress___street1';

            //for primaryEmail attribute
            $reportModelTestItem->primaryEmail->emailAddress = "test@someString.com";
            $displayAttribute14   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute14->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute14->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            
            //for multiDropDown attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);            
            $displayAttribute15   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute15->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute15->attributeIndexOrDerivedType = 'multiDropDown';

            //for tagCloud attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $displayAttribute16   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute16->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute16->attributeIndexOrDerivedType = 'tagCloud';
            
            //for radioDropDown attribute
            $reportModelTestItem->radioDropDown->value = $values[1];
            $displayAttribute17   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute17->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute17->attributeIndexOrDerivedType = 'radioDropDown';
            
            
            
            $reportResultsRowData = new ReportResultsRowData(array(
                                        $displayAttribute1, $displayAttribute2, $displayAttribute3,
                                        $displayAttribute4, $displayAttribute5, $displayAttribute6,
                                        $displayAttribute7, $displayAttribute8, $displayAttribute9,
                                        $displayAttribute10, $displayAttribute11, $displayAttribute12,
                                        $displayAttribute13, $displayAttribute14, $displayAttribute15), 24);
                                                                    
            $reportResultsRowData->addModelAndAlias($reportModelTestItem,  'model1');
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            
            $headerdata  = array('Full Name', 'Boolean', 'Date', 'DateTime', 'Float'
                                 , 'Integer', 'Phone', 'String', 'TextArea', 'Url', 'Dropdown'
                                 , 'Currency', 'PrimaryAddress', 'PrimaryEmail', 'MultiDropDown');
            $content     = array('xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15',
                                 10.5, 10, 'xNr', '7842151012', 'xString', 'xtextAreatest',
                                 'http://www.test.com', 'Test2', 'USD', 'someString', 'test@someString.com',
                                 'Multi 1 Multi 2');
            
            $compareData = array($headerdata, $content);
            $this->assertEquals($compareData, $data);
        }
        
        public function testSummationfields()
        {  


            //for summation 
            
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);            
            $displayAttribute1->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute1->attributeIndexOrDerivedType = 'SUM(Amount)';
            $displayAttribute1->label                       = 'Amount';  

            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);            
            $displayAttribute2->setModelAliasUsingTableAliasName('model1');  
            $displayAttribute2->attributeIndexOrDerivedType = 'MAX(closeDate)';
            $displayAttribute2->label                       = 'Close Date';                

            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'stage';
            $groupBy->axis                        = 'y';            
            $report->addGroupBy($groupBy);
            
            //

            //            
            //for date summation
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'date__Maximum';                        
            $displayAttribute1->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'date__Minimum';                        
            $displayAttribute2->setModelAliasUsingTableAliasName('model2');
            
            //for dateTime summation
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'dateTime__Minimum';                        
            $displayAttribute2->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'date__Minimum';                        
            $displayAttribute2->setModelAliasUsingTableAliasName('dateTime__Maximum');
            
            
            //for createdDateTime summation 
            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType = 'createdDateTime__Maximum';                        
            $displayAttribute3->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType = 'createdDateTime__Minimum';                        
            $displayAttribute4->setModelAliasUsingTableAliasName('model2');
            
            
            //for modifiedDateTime summation
            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType = 'modifiedDateTime__Maximum';                        
            $displayAttribute5->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType = 'modifiedDateTime__Minimum';                        
            $displayAttribute6->setModelAliasUsingTableAliasName('model2');
            
            //for count summation
            $displayAttribute7 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute7->attributeIndexOrDerivedType = 'count';                        
            $displayAttribute7->setModelAliasUsingTableAliasName('model2');
            
            //for float summation
            $displayAttribute8 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute8->attributeIndexOrDerivedType = 'float__Minimum';                        
            $displayAttribute8->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute9 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute9->attributeIndexOrDerivedType = 'float__Maximum';                        
            $displayAttribute9->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute10 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute10->attributeIndexOrDerivedType = 'float__Summation';                        
            $displayAttribute10->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute11 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute11->attributeIndexOrDerivedType = 'float__Average';                        
            $displayAttribute11->setModelAliasUsingTableAliasName('model2');
            
            
            //for integer summation
            $displayAttribute12 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute12->attributeIndexOrDerivedType = 'integer__Minimum';                        
            $displayAttribute12->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute13 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute13->attributeIndexOrDerivedType = 'integer__Maximum';                        
            $displayAttribute13->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute14 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute14->attributeIndexOrDerivedType = 'integer__Summation';                        
            $displayAttribute14->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute15 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute15->attributeIndexOrDerivedType = 'integer__Average';                        
            $displayAttribute15->setModelAliasUsingTableAliasName('model2');
            
            //for currency summation
            $displayAttribute16 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute16->attributeIndexOrDerivedType = 'currencyValue__Minimum';                        
            $displayAttribute16->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute17 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute17->attributeIndexOrDerivedType = 'currencyValue__Maximum';                        
            $displayAttribute17->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute18 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute18->attributeIndexOrDerivedType = 'currencyValue__Summation';                        
            $displayAttribute18->setModelAliasUsingTableAliasName('model2');
            
            $displayAttribute19 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute19->attributeIndexOrDerivedType = 'currencyValue__Average';                        
            $displayAttribute19->setModelAliasUsingTableAliasName('model2');            
            
            
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addSelectedColumnNameAndValue('col1', '2013-02-14 00:00');
        
        }
    }        