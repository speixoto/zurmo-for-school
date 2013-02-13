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
            $reportModelTestItemF = new ReportModelTestItem();
            $reportModelTestItemF->firstName = 'xFirst';
            $reportModelTestItemF->lastName = 'xLast';
            $displayAttributeF    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeF->setModelAliasUsingTableAliasName('abc');  
            $displayAttributeF->attributeIndexOrDerivedType = 'FullName';            

            $reportModelTestItemB = new ReportModelTestItem();
            $reportModelTestItemB->boolean = true;
            $displayAttributeB    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeB->setModelAliasUsingTableAliasName('def');  
            $displayAttributeB->attributeIndexOrDerivedType = 'boolean'; 
            
            $reportModelTestItemD = new ReportModelTestItem();
            $reportModelTestItemD->date = '2013-02-12';
            $displayAttributeD    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeD->setModelAliasUsingTableAliasName('lmn');  
            $displayAttributeD->attributeIndexOrDerivedType = 'date'; 
            
            $reportModelTestItemDT = new ReportModelTestItem();
            $reportModelTestItemDT->dateTime = '2013-02-12 10:15';
            $displayAttributeDT    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeDT->setModelAliasUsingTableAliasName('pqr');  
            $displayAttributeDT->attributeIndexOrDerivedType = 'dateTime'; 
            
            $reportModelTestItemFT = new ReportModelTestItem();
            $reportModelTestItemFT->float = 10.5;
            $displayAttributeFT    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeFT->setModelAliasUsingTableAliasName('stu');  
            $displayAttributeFT->attributeIndexOrDerivedType = 'float'; 
            
            $reportModelTestItemI = new ReportModelTestItem();
            $reportModelTestItemI->integer = 10;
            $displayAttributeI    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeI->setModelAliasUsingTableAliasName('uvw');  
            $displayAttributeI->attributeIndexOrDerivedType = 'float';             

            $reportModelTestItemP = new ReportModelTestItem();
            $reportModelTestItemP->phone = '7842151012';
            $displayAttributeP    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeP->setModelAliasUsingTableAliasName('xyz');  
            $displayAttributeP->attributeIndexOrDerivedType = 'phone'; 
            
            $reportModelTestItemS = new ReportModelTestItem();
            $reportModelTestItemS->string = 'xString';
            $displayAttributeS    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeS->setModelAliasUsingTableAliasName('hqq');  
            $displayAttributeS->attributeIndexOrDerivedType = 'string'; 
            
            $reportModelTestItemTA = new ReportModelTestItem();
            $reportModelTestItemTA->textArea = 'xtextAreatest';
            $displayAttributeTA    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeTA->setModelAliasUsingTableAliasName('tqq');  
            $displayAttributeTA->attributeIndexOrDerivedType = 'textArea'; 
            
            
            $reportModelTestItemU = new ReportModelTestItem();
            $reportModelTestItemU->url = 'http://www.test.com'; 
            $displayAttributeU    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeU->setModelAliasUsingTableAliasName('sqq');  
            $displayAttributeU->attributeIndexOrDerivedType = 'url';                         

            $reportResultsRowData = new ReportResultsRowData(array(
                                        $displayAttributeF, $displayAttributeB, $displayAttributeD,
                                        $displayAttributeDT, $displayAttributeFT, $displayAttributeI,
                                        $displayAttributeP, $displayAttributeS, $displayAttributeTA,
                                        $displayAttributeU), 24);
                                                                    
            $reportResultsRowData->addModelAndAlias($reportModelTestItemF,  'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemB,  'def');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemD,  'lmn');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemDT, 'pqr');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemFT, 'stu');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemI,  'uvw');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemP,  'xyz');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemS,  'hqq');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemTA, 'tqq');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemU,  'sqq');
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();
            
            $headerdata  = array('Full Name', 'Boolean', 'Date', 'DateTime', 'Float'
                                 ,'Integer', 'Phone', 'String', 'TextArea', 'Url');
            $content     = array('xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15',
                                 10.5, 10, 'xNr', '7842151012', 'xString', 'xtextAreatest',
                                 'http://www.test.com');
            
            $compareData = array($headerdata, $content);
            $this->assertEquals($compareData, $data);
        }

        public function testGetDataWithAllHasOneOrOwnedRelationsSet()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);            
            
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->firstName = 'yFirst';
            $reportModelTestItemX->lastName = 'yLast';
            $reportModelTestItemX->boolean = true;
            $reportModelTestItemX->date = '2013-02-14';
            $reportModelTestItemX->dateTime = '2013-02-12 10:15';
            $reportModelTestItemX->float = 10.5;
            $reportModelTestItemX->integer = 10;
            $reportModelTestItemX->nonReportable = 'xNr';
            $reportModelTestItemX->phone = '7842151012';
            $reportModelTestItemX->string = 'yString';
            $reportModelTestItemX->textArea = 'ytextArea test';
            $reportModelTestItemX->url = 'http://www.test.com';
            $reportModelTestItemX->currencyValue   = $currencyValue;
            $reportModelTestItemX->dropDown->value = $values[1];            
        
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'likeContactState';             

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 14);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');            

            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();    
            $compareData = array(array('Contact State'), array('someString someName'));
            $this->assertEquals($compareData, $data);                        
        } 
        
        public function testGetDataWithHasOneOrOwnedRelationsSet()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';                
        
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->primaryAddress->street1 = 'someString';
            $reportModelTestItemX->likeContactState = $reportModelTestItem7;        
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'likeContactState';             

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 2);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');            

            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();    
            $compareData = array(array('Contact State'), array('someString someName'));
            $this->assertEquals($compareData, $data);                        
        } 

        public function testGetDataWithHasOneRelatedModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $reportModelTestItem2 = new ReportModelTestItem2();
            $reportModelTestItem2->name     = 'John';
            $this->assertTrue($reportModelTestItem2->save());

            $reportModelTestItem4 = new ReportModelTestItem4();
            $reportModelTestItem4->name     = 'John';
            $this->assertTrue($reportModelTestItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $reportModelTestItem3_1 = new ReportModelTestItem3();
            $reportModelTestItem3_1->name     = 'Kevin';
            $this->assertTrue($reportModelTestItem3_1->save());

            $reportModelTestItem3_2 = new ReportModelTestItem3();
            $reportModelTestItem3_2->name     = 'Jim';
            $this->assertTrue($reportModelTestItem3_2->save());

            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->firstName = 'yFirst';
            $reportModelTestItemX->lastName = 'yLast';
            $reportModelTestItemX->boolean = true;
            $reportModelTestItemX->date = '2013-02-14';
            $reportModelTestItemX->dateTime = '2013-02-12 10:15';
            $reportModelTestItemX->float = 10.5;
            $reportModelTestItemX->integer = 10;
            $reportModelTestItemX->nonReportable = 'xNr';
            $reportModelTestItemX->phone = '7842151012';
            $reportModelTestItemX->string = 'yString';
            $reportModelTestItemX->textArea = 'ytextArea test';
            $reportModelTestItemX->url = 'http://www.test.com';
            $reportModelTestItemX->currencyValue = $currencyValue;
            $reportModelTestItemX->hasOne        = $reportModelTestItem2;
            $reportModelTestItemX->hasMany->add($reportModelTestItem3_1);
            $reportModelTestItemX->hasMany->add($reportModelTestItem3_2);
            $reportModelTestItemX->hasOneAlso    = $reportModelTestItem4;
            
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeX->setModelAliasUsingTableAliasName('xyz');
            $displayAttributeX->attributeIndexOrDerivedType = 'likeContactState'; 
            
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 2);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc'); 
            
            $adapter     = new ReportToExportAdapter($reportResultsRowData);
            $data        = $adapter->getData();

            $compareData = array(array('Contact State'), array('someString someName'));
            $this->assertEquals($compareData, $data);
        }        
    }
?>