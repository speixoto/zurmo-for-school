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
            
            //for likeContactState 
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';            
            $reportModelTestItem->likeContactState = $reportModelTestItem7;
            $displayAttribute18            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute18->setModelAliasUsingTableAliasName('model1');
            $displayAttribute18->attributeIndexOrDerivedType = 'likeContactState';
                                                                                            
            $reportResultsRowData = new ReportResultsRowData(array(
                                        $displayAttribute1, $displayAttribute2, $displayAttribute3,
                                        $displayAttribute4, $displayAttribute5, $displayAttribute6,
                                        $displayAttribute7, $displayAttribute8, $displayAttribute9,
                                        $displayAttribute10, $displayAttribute11, $displayAttribute12,
                                        $displayAttribute13, $displayAttribute14, $displayAttribute15,
                                        $displayAttribute16, $displayAttribute17, $displayAttribute18), 24);
                                                                    
            $reportResultsRowData->addModelAndAlias($reportModelTestItem,  'model1');
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            
            $headerdata  = array('Full Name', 'Boolean', 'Date', 'DateTime', 'Float'
                                 , 'Integer', 'Phone', 'String', 'TextArea', 'Url', 'Dropdown'
                                 , 'Currency', 'PrimaryAddress', 'PrimaryEmail', 'MultiDropDown'
                                 , 'tagCloud', 'radioDropDown', 'Contact State');
            $content     = array('xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15',
                                 10.5, 10, 'xNr', '7842151012', 'xString', 'xtextAreatest',
                                 'http://www.test.com', 'Test2', 'USD', 'someString', 'test@someString.com',
                                 'Multi 1 Multi 2', 'Cloud 2 Cloud 3', 'Test2', 'someName');
            
            $compareData = array($headerdata, $content);
            $this->assertEquals($compareData, $data);
        }
        
        public function testRelationalFields()
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
            $displayAttribute1    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute1->attributeIndexOrDerivedType = 'hasMany2___FullName';            
            
            //for boolean attribute
            $reportModelTestItem->boolean = true;
            $displayAttribute2    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany2___boolean'; 
                    
            //for date attribute                  
            $reportModelTestItem->date = '2013-02-12';
            $displayAttribute3    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany2___date';             

            //for datetime attribute
            $reportModelTestItem->dateTime = '2013-02-12 10:15';
            $displayAttribute4    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute4->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute4->attributeIndexOrDerivedType = 'hasMany2___dateTime'; 
            
            //for float attribute
            $reportModelTestItem->float = 10.5;
            $displayAttribute5    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute5->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute5->attributeIndexOrDerivedType = 'hasMany2___float'; 
            
            //for integer attribute
            $reportModelTestItem->integer = 10;
            $displayAttribute6    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute6->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute6->attributeIndexOrDerivedType = 'hasMany2___integer';             
            
            //for phone attribute
            $reportModelTestItem->phone = '7842151012';
            $displayAttribute7    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute7->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute7->attributeIndexOrDerivedType = 'hasMany2___phone'; 
                        
            //for string attribute                        
            $reportModelTestItem->string = 'xString';
            $displayAttribute8    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute8->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute8->attributeIndexOrDerivedType = 'hasMany2___string'; 
            
            //for textArea attribute            
            $reportModelTestItem->textArea = 'xtextAreatest';
            $displayAttribute9    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute9->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute9->attributeIndexOrDerivedType = 'hasMany2___textArea'; 
            
            //for url attribute            
            $reportModelTestItem->url = 'http://www.test.com'; 
            $displayAttribute10    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute10->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute10->attributeIndexOrDerivedType = 'hasMany2___url'; 

            //for dropdown attribute           
            $reportModelTestItem->dropDown->value = $values[1];  
            $displayAttribute11    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute11->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute11->attributeIndexOrDerivedType = 'hasMany2___dropDown';             

            //for currency attribute
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);  
            
            $reportModelTestItem->currencyValue   = $currencyValue;
            $displayAttribute12    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute12->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute12->attributeIndexOrDerivedType = 'hasMany2___currencyValue'; 
            
            //for primaryAddress attribute
            $reportModelTestItem->primaryAddress->street1 = 'someString';
            $displayAttribute13   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute13->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute13->attributeIndexOrDerivedType = 'hasMany2___primaryAddress___street1';

            //for primaryEmail attribute
            $reportModelTestItem->primaryEmail->emailAddress = "test@someString.com";
            $displayAttribute14   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute14->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute14->attributeIndexOrDerivedType = 'hasMany2___primaryEmail___emailAddress';
            
            //for multiDropDown attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);            
            $displayAttribute15   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute15->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute15->attributeIndexOrDerivedType = 'hasMany2___multiDropDown';

            //for tagCloud attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $displayAttribute16   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute16->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute16->attributeIndexOrDerivedType = 'hasMany2___tagCloud';
            
            //for radioDropDown attribute
            $reportModelTestItem->radioDropDown->value = $values[1];
            $displayAttribute17   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute17->setModelAliasUsingTableAliasName('relatedModel');  
            $displayAttribute17->attributeIndexOrDerivedType = 'hasMany2___radioDropDown';
            
            //for likeContactState 
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';            
            $reportModelTestItem->likeContactState = $reportModelTestItem7;
            $displayAttribute18            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute18->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute18->attributeIndexOrDerivedType = 'hasMany2___likeContactState';
                                                                                            
            $reportResultsRowData = new ReportResultsRowData(array(
                                        $displayAttribute1, $displayAttribute2, $displayAttribute3,
                                        $displayAttribute4, $displayAttribute5, $displayAttribute6,
                                        $displayAttribute7, $displayAttribute8, $displayAttribute9,
                                        $displayAttribute10, $displayAttribute11, $displayAttribute12,
                                        $displayAttribute13, $displayAttribute14, $displayAttribute15,
                                        $displayAttribute16, $displayAttribute17, $displayAttribute18), 24);
                                                                    
            $reportResultsRowData->addModelAndAlias($reportModelTestItem,  'relatedModel');
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            
            $headerdata  = array('Full Name', 'Boolean', 'Date', 'DateTime', 'Float'
                                 , 'Integer', 'Phone', 'String', 'TextArea', 'Url', 'Dropdown'
                                 , 'Currency', 'PrimaryAddress', 'PrimaryEmail', 'MultiDropDown'
                                 , 'tagCloud', 'radioDropDown', 'Contact State');
            $content     = array('xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15',
                                 10.5, 10, 'xNr', '7842151012', 'xString', 'xtextAreatest',
                                 'http://www.test.com', 'Test2', 'USD', 'someString', 'test@someString.com',
                                 'Multi 1 Multi 2', 'Cloud 2 Cloud 3', 'Test2', 'someName');
            
            $compareData = array($headerdata, $content);
            $this->assertEquals($compareData, $data);
                    
        }
        
        public function testSummationfields()
        {  
            
            //for date summation
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'date__Maximum';                        
            $displayAttribute1->madeViaSelectInsteadOfViaModel = true;    
            
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'date__Minimum';                        
            $displayAttribute2->madeViaSelectInsteadOfViaModel = true;    
            
            //for dateTime summation
            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType = 'dateTime__Minimum';                        
            $displayAttribute3->madeViaSelectInsteadOfViaModel = true; 
            
            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType = 'dateTime__Minimum';                        
            $displayAttribute4->madeViaSelectInsteadOfViaModel = true; 
            
            //for createdDateTime summation 
            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType = 'createdDateTime__Maximum';                        
            $displayAttribute5->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType = 'createdDateTime__Minimum';                        
            $displayAttribute6->madeViaSelectInsteadOfViaModel = true;

            //for modifiedDateTime summation
            $displayAttribute7 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute7->attributeIndexOrDerivedType = 'modifiedDateTime__Maximum';                        
            $displayAttribute7->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute8 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute8->attributeIndexOrDerivedType = 'modifiedDateTime__Minimum';                        
            $displayAttribute8->madeViaSelectInsteadOfViaModel = true;   

            //for float summation
            $displayAttribute9 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute9->attributeIndexOrDerivedType = 'float__Minimum';                        
            $displayAttribute9->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute10 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute10->attributeIndexOrDerivedType = 'float__Maximum';                        
            $displayAttribute10->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute11 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute11->attributeIndexOrDerivedType = 'float__Summation';                        
            $displayAttribute11->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute12 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute12->attributeIndexOrDerivedType = 'float__Average';                        
            $displayAttribute12->madeViaSelectInsteadOfViaModel = true;
            
            //for integer summation
            $displayAttribute13 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute13->attributeIndexOrDerivedType = 'integer__Minimum';                        
            $displayAttribute13->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute14 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute14->attributeIndexOrDerivedType = 'integer__Maximum';                        
            $displayAttribute14->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute15 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute15->attributeIndexOrDerivedType = 'integer__Summation';                        
            $displayAttribute15->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute16 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute16->attributeIndexOrDerivedType = 'integer__Average';                        
            $displayAttribute16->madeViaSelectInsteadOfViaModel = true;
            
            //for currency summation
            $displayAttribute17 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute17->attributeIndexOrDerivedType = 'currencyValue__Minimum';                        
            $displayAttribute17->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute18 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute18->attributeIndexOrDerivedType = 'currencyValue__Maximum';                        
            $displayAttribute18->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute19 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute19->attributeIndexOrDerivedType = 'currencyValue__Summation';                        
            $displayAttribute19->madeViaSelectInsteadOfViaModel = true;
            
            $displayAttribute20 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute20->attributeIndexOrDerivedType = 'currencyValue__Average';                        
            $displayAttribute20->madeViaSelectInsteadOfViaModel = true;
                                                                  
            $reportResultsRowData = new ReportResultsRowData(array(
                                    $displayAttribute1, $displayAttribute2, $displayAttribute3,
                                    $displayAttribute4, $displayAttribute5, $displayAttribute6, 
                                    $displayAttribute7, $displayAttribute8, $displayAttribute9,
                                    $displayAttribute10, $displayAttribute11, $displayAttribute12,
                                    $displayAttribute13, $displayAttribute14, $displayAttribute15,
                                    $displayAttribute16, $displayAttribute17, $displayAttribute18,
                                    $displayAttribute19, $displayAttribute20), 4);
            $reportResultsRowData->addSelectedColumnNameAndValue('col1', '2013-02-14');
            $reportResultsRowData->addSelectedColumnNameAndValue('col2', '2013-02-12');
            $reportResultsRowData->addSelectedColumnNameAndValue('col3', '2013-02-14 00:00');
            $reportResultsRowData->addSelectedColumnNameAndValue('col4', '2013-02-12 00:59');
            $reportResultsRowData->addSelectedColumnNameAndValue('col5', '2013-02-14 00:00');
            $reportResultsRowData->addSelectedColumnNameAndValue('col6', '2013-02-12 00:59');
            $reportResultsRowData->addSelectedColumnNameAndValue('col7', '2013-02-14 00:00');
            $reportResultsRowData->addSelectedColumnNameAndValue('col8', '2013-02-12 00:59');
            $reportResultsRowData->addSelectedColumnNameAndValue('col9', 18.45);
            $reportResultsRowData->addSelectedColumnNameAndValue('col10', 19.41);
            $reportResultsRowData->addSelectedColumnNameAndValue('col11', 192.15);
            $reportResultsRowData->addSelectedColumnNameAndValue('col12', 180.21);
            $reportResultsRowData->addSelectedColumnNameAndValue('col13', 2000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col14', 5000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col15', 1000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col16', 9000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col17', 5000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col18', 6000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col19', 7000);
            $reportResultsRowData->addSelectedColumnNameAndValue('col20', 8000);
            //$this->assertTrue($displayattribute1->columnNameAlias == 'col1');
            //$this->assertTrue($displayattribute2->columnNameAlias == 'col2');
                        
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            
            $headerdata  = array('col1', 'col2', 'col3', 'col4', 'col5', 'col6', 'col7',
                                 'col8', 'col9', 'col10', 'col11', 'col12', 'col13', 'col14',
                                 'col15', 'col16', 'col17', 'col18', 'col19', 'col20');
            $content     = array('2013-02-14', '2013-02-12', '2013-02-14 00:00', '2013-02-12 00:59',
                                 '2013-02-14 00:00', '2013-02-12 00:59', '2013-02-14 00:00',
                                 '2013-02-12 00:59', 18.45, 19.41, 192.15, 180.21, 2000,
                                 5000, 1000, 9000, 5000, 6000, 7000, 8000);
            
            $compareData = array($headerdata, $content);
            $this->assertEquals($compareData, $data);
        }
        
    }        