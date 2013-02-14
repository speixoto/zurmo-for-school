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
            //for fullname attribute  
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->firstName = 'xFirst';
            $reportModelTestItem->lastName = 'xLast';
            $displayAttributeF    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeF->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeF->attributeIndexOrDerivedType = 'FullName';            
            
            //for boolean attribute
            $reportModelTestItem->boolean = true;
            $displayAttributeB    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeB->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeB->attributeIndexOrDerivedType = 'boolean'; 
                    
            //for date attribute                  
            $reportModelTestItem->date = '2013-02-12';
            $displayAttributeD    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeD->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeD->attributeIndexOrDerivedType = 'date';             

            //for datetime attribute
            $reportModelTestItem->dateTime = '2013-02-12 10:15';
            $displayAttributeDT    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeDT->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeDT->attributeIndexOrDerivedType = 'dateTime'; 
            
            //for float attribute
            $reportModelTestItem->float = 10.5;
            $displayAttributeFT    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeFT->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeFT->attributeIndexOrDerivedType = 'float'; 
            
            //for integer attribute
            $reportModelTestItem->integer = 10;
            $displayAttributeI    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeI->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeI->attributeIndexOrDerivedType = 'integer';             
            
            //for phone attribute
            $reportModelTestItem->phone = '7842151012';
            $displayAttributeP    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeP->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeP->attributeIndexOrDerivedType = 'phone'; 
                        
            //for string attribute                        
            $reportModelTestItem->string = 'xString';
            $displayAttributeS    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeS->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeS->attributeIndexOrDerivedType = 'string'; 
            
            //for textArea attribute            
            $reportModelTestItem->textArea = 'xtextAreatest';
            $displayAttributeTA    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeTA->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeTA->attributeIndexOrDerivedType = 'textArea'; 
            
            //for url attribute            
            $reportModelTestItem->url = 'http://www.test.com'; 
            $displayAttributeU    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeU->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeU->attributeIndexOrDerivedType = 'url'; 

            //for dropdown attribute
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
            $this->assertTrue($saved);               
            $reportModelTestItem->dropDown->value = $values[1];  
            $displayAttributeCD    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeCD->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeCD->attributeIndexOrDerivedType = 'dropDown';             

            //for currency attribute
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);  
            
            $reportModelTestItem->currencyValue   = $currencyValue;
            $displayAttributeC    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeC->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeC->attributeIndexOrDerivedType = 'currencyValue'; 
            
            //for primaryAddress attribute
            $reportModelTestItem->primaryAddress->street1 = 'someString';
            $displayAttributePA   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributePA->setModelAliasUsingTableAliasName('model1');  
            $displayAttributePA->attributeIndexOrDerivedType = 'primaryAddress___street1';

            //for primaryEmail attribute
            $reportModelTestItem->primaryEmail->emailAddress = "test@someString.com";
            $displayAttributePE   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributePE->setModelAliasUsingTableAliasName('model1');  
            $displayAttributePE->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            
            //for multiDropDown attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);            
            $displayAttributeMD   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeMD->setModelAliasUsingTableAliasName('model1');  
            $displayAttributeMD->attributeIndexOrDerivedType = 'multiDropDown';            
            
            
            $reportResultsRowData = new ReportResultsRowData(array(
                                        $displayAttributeF, $displayAttributeB, $displayAttributeD,
                                        $displayAttributeDT, $displayAttributeFT, $displayAttributeI,
                                        $displayAttributeP, $displayAttributeS, $displayAttributeTA,
                                        $displayAttributeU, $displayAttributeCD, $displayAttributeC,
                                        $displayAttributePA, $displayAttributePE, $displayAttributeMD), 24);
                                                                    
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
    }        