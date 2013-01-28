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

    Yii::import('ext.csvparser.CsvParser');

    /**
    * Test ExportItemToCsvFileUtil functions.
    */
    class ExportItemToCsvFileUtilTest extends ZurmoBaseTest
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

        public function testGetDataWithHasOneRelatedModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ExportTestModelItem2();
            $testItem2->name     = 'John';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ExportTestModelItem4();
            $testItem4->name     = 'John';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ExportTestModelItem3();
            $testItem3_1->name     = 'Kevin';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ExportTestModelItem3();
            $testItem3_2->name     = 'Jim';
            $this->assertTrue($testItem3_2->save());

            $testItem = new ExportTestModelItem();
            $testItem->firstName     = 'Bob3';
            $testItem->lastName      = 'Bob3';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString';
            $testItem->textArea      = 'Some Text Area';
            $testItem->url           = 'http://www.asite.com';
            $testItem->email       = 'a@a.com';
            $testItem->owner         = $super;
            $testItem->currencyValue = $currencyValue;
            $testItem->hasOne        = $testItem2;
            $testItem->hasMany->add($testItem3_1);
            $testItem->hasMany->add($testItem3_2);
            $testItem->hasOneAlso    = $testItem4;

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testItem->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testItem->tagCloud->values->add($customFieldValue);

            $createStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($testItem->save());
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $data = array();
            $testItem    = ExportTestModelItem::getById($id);
            $adapter     = new ModelToExportAdapter($testItem);
            $data[]        = $adapter->getData();

            // Export data to csv, and then revert csv back to array, so we compare data
            $csvData = ExportItemToCsvFileUtil::export($data, '', false);
            $revertedData = CsvParser::parseFromString($csvData);

            // We are testing ModelToExportAdapter in details in another test
            // so in this test we suppose that ModelToExportAdapter::getData
            // return correct results
            $adapter     = new ModelToExportAdapter($testItem);
            $compareData        = $adapter->getData();

            $this->assertEquals($compareData, $revertedData[0]);
        }

        public function testExportItemToCsvWorksWithNormalData()
        {
            $this->assertTrue($this->isValidCsvConversion('Data without linebreaks or commas'));
        }

        public function testExportItemToCsvWorksWithDataContainingComma()
        {
            $this->assertTrue($this->isValidCsvConversion('Data, with, multiple, comma, occurances'));
        }

        public function testExportItemToCsvWorksWithDataContainingLineBreaks()
        {
            $this->assertTrue($this->isValidCsvConversion("Data \n with \n linebreaks \n"));
        }

        public function testExportItemToCsvWorksWithDataContainingCommaAndLineBreaks()
        {
            $this->assertTrue($this->isValidCsvConversion("Data, \n with, \n ,linebreaks, \n")); // Not Coding Standard
        }

        protected function isValidCsvConversion($textAreaContent)
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $testItem = new ExportTestModelItem();
            $testItem->firstName    = 'Bob3';
            $testItem->lastName     = 'Bob3';
            $testItem->boolean      = true;
            $testItem->date         = '2002-04-03';
            $testItem->dateTime     = '2002-04-03 02:00:43';
            $testItem->float        = 54.22;
            $testItem->integer      = 10;
            $testItem->phone        = '21313213';
            $testItem->string       = 'aString';
            $testItem->textArea     = $textAreaContent;
            $testItem->url          = 'http://www.asite.com';
            $testItem->email        = 'a@a.com';

            $testItem->save();
            $id = $testItem->id;
            $testItem->forget();
            unset($testItem);

            $data = array();
            $testItem = ExportTestModelItem::getById($id);
            $adapter = new ModelToExportAdapter($testItem);
            $data[] = $adapter->getData();

            // Export data to csv, and then revert csv back to array, so we compare data
            $csvData = ExportItemToCsvFileUtil::export($data, '', false);
            $revertedData = CsvParser::parseFromString($csvData);

            // We are testing ModelToExportAdapter in details in another test
            // so in this test we suppose that ModelToExportAdapter::getData
            // return correct results
            $adapter = new ModelToExportAdapter($testItem);
            $compareData = $adapter->getData();

            // Using === here would fail as we are not setting all keys part of getData()'s return array
            return $compareData == $revertedData[0];
        }
    }
?>