<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ImportMappingUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeMappingDataByTableName()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName, true));
            $mappingData = ImportMappingUtil::makeMappingDataByTableName($testTableName);
            $compareData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
            );
            $this->assertEquals($compareData, $mappingData);
        }

        /**
         * @expectedException NoRowsInTableException
         */
        public function testMakeMappingDataOnFileWithNoRows()
        {
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest3.csv', $testTableName));
            ImportMappingUtil::makeMappingDataByTableName($testTableName);
        }

        public function testGetMappedAttributeIndicesOrDerivedAttributeTypesByMappingData()
        {
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'a', 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'b', 'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'c', 'mappingRulesData' => null),
            );
            $data = ImportMappingUtil::getMappedAttributeIndicesOrDerivedAttributeTypesByMappingData($mappingData);
            $this->assertEquals(array('a', 'b', 'c'), $data);

            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
            );
            $data = ImportMappingUtil::getMappedAttributeIndicesOrDerivedAttributeTypesByMappingData($mappingData);
            $this->assertNull($data);
        }

        public function testMakeExtraColumnNameByColumnCount()
        {
            $this->assertEquals('column_5', ImportMappingUtil::makeExtraColumnNameByColumnCount(4));
        }

        public function testReIndexExtraColumnNamesByPostData()
        {
            $postData = array(
                'column_0'  => array('type' => 'importColumn'),
                'column_1'  => array('type' => 'importColumn'),
                'column_2'  => array('type' => 'importColumn'),
                'column_5'  => array('type' => 'extraColumn'),
                'column_55' => array('type' => 'extraColumn'),
            );
            $reIndexedPostData = ImportMappingUtil::reIndexExtraColumnNamesByPostData($postData);
            $compareData = array(
                'column_0'  => array('type' => 'importColumn'),
                'column_1'  => array('type' => 'importColumn'),
                'column_2'  => array('type' => 'importColumn'),
                'column_3'  => array('type' => 'extraColumn'),
                'column_4'  => array('type' => 'extraColumn'),
            );
            $this->assertEquals($compareData, $reIndexedPostData);
        }

        public function testMakeColumnNamesAndAttributeIndexOrDerivedTypeLabels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mappingData = array(
                'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string', 'mappingRulesData' => null),
                'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => null, 'mappingRulesData' => null),
                'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'FullName', 'mappingRulesData' => null),
            );
            $data = ImportMappingUtil::makeColumnNamesAndAttributeIndexOrDerivedTypeLabels($mappingData, 'ImportModelTestItem');
            $compareData = array('column_0' => 'String', 'column_1' => null, 'column_2' => 'Full Name');
            $this->assertEquals($compareData, $data);
        }
    }
?>