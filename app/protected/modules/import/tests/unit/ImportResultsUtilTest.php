<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ImportResultsUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testProcessStatusAndMessagesForEachRow()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());

            $testTableName = $import->getTempTableName();
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $count = ImportDatabaseUtil::getCount($testTableName);
            $this->assertEquals(5, $count);

            //Now add import results.
            $resultsUtil = new ImportResultsUtil($import);

            $rowDataResultsUtil = new ImportRowDataResultsUtil(2);
            $rowDataResultsUtil->setStatusToUpdated();
            $rowDataResultsUtil->addMessage('the first message');
            $resultsUtil->addRowDataResults($rowDataResultsUtil);

            $rowDataResultsUtil = new ImportRowDataResultsUtil(3);
            $rowDataResultsUtil->setStatusToCreated();
            $rowDataResultsUtil->addMessage('the second message');
            $resultsUtil->addRowDataResults($rowDataResultsUtil);

            $rowDataResultsUtil = new ImportRowDataResultsUtil(4);
            $rowDataResultsUtil->setStatusToError();
            $rowDataResultsUtil->addMessage('the third message');
            $resultsUtil->addRowDataResults($rowDataResultsUtil);

            $resultsUtil->processStatusAndMessagesForEachRow();

            $sql = 'select * from ' . $testTableName . ' where id != 1';
            $tempTableData = R::getAll($sql);
            $compareData   = array(
                array
                (
                    'id' => 2,
                    'column_0'           => 'abc',
                    'column_1'           => '123',
                    'column_2'           => 'a',
                    'status'             => 1,
                    'serializedMessages' => serialize(array('the first message')),
                    'analysisStatus'     => null,
                    'serializedAnalysisMessages' => null,
                ),
                array
                (
                    'id' => 3,
                    'column_0'           => 'def',
                    'column_1'           => '563',
                    'column_2'           => 'b',
                    'status'             => 2,
                    'serializedMessages' => serialize(array('the second message')),
                    'analysisStatus'     => null,
                    'serializedAnalysisMessages' => null,
                ),
                array
                (
                    'id' => 4,
                    'column_0'           => 'efg',
                    'column_1'           => '456',
                    'column_2'           => 'a',
                    'status'             => 3,
                    'serializedMessages' => serialize(array('the third message')),
                    'analysisStatus'     => null,
                    'serializedAnalysisMessages' => null,
                ),
                array
                (
                    'id' => 5,
                    'column_0'           => 'we1s',
                    'column_1'           => null,
                    'column_2'           => 'b',
                    'status'             => null,
                    'serializedMessages' => null,
                    'analysisStatus'     => null,
                    'serializedAnalysisMessages' => null,
                ),
            );
            $this->assertEquals($compareData, $tempTableData);
        }

        public function testConvertSerializedMessagesToDisplayReadyString()
        {
            $messages = array('a', 'b', 'c');
            $string = ImportResultsUtil::convertSerializedMessagesToDisplayReadyString(serialize($messages));
            $compareString = 'a<br/>b<br/>c';
            $this->assertEquals($compareString, $string);
        }
    }
?>