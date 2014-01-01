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

    class ImportCreateUpdateModelsSequentialProcessTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
        }

        public static function getDependentTestModelClassNames()
        {
            return array('ImportModelTestItem');
        }

        public function testSequentialProcessViewFactory()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $testModels                        = ImportModelTestItem::getAll();
            $this->assertEquals(0, count($testModels));

            $import                            = new Import();
            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                'column_23' => array('attributeIndexOrDerivedType' => 'FullName',     'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                                        );
            $serializedData['importRulesType']     = 'ImportModelTestItem';
            $serializedData['mappingData']         = $mappingData;
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(), true);
            $config            = array('pagination' => array('pageSize' => 2));
            $dataProvider      = new ImportDataProvider($import->getTempTableName(), true, $config);
            $sequentialProcess = new ImportCreateUpdateModelsSequentialProcess($import, $dataProvider);
            $sequentialProcess->run(null, null);
            $route   = 'default/someAction';
            $view    = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            $content = $view->render();
            $this->assertNotNull($content);
            $this->assertEquals('SequentialProcessView', get_class($view));
            $this->assertEquals('processRows', $sequentialProcess->getNextStep());

            //Now process the first run. Will process page 0.
            $sequentialProcess = new ImportCreateUpdateModelsSequentialProcess($import, $dataProvider);
            $sequentialProcess->run('processRows', null);
            $route   = 'default/someAction';
            $view    = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            $content = $view->render();
            $this->assertNotNull($content);
            $this->assertEquals('SequentialProcessView', get_class($view));
            $this->assertEquals(array('page' => 1),  $sequentialProcess->getNextParams());

            //Confirm 2 models were successfully added.
            $testModels = ImportModelTestItem::getAll();
            $this->assertEquals(2, count($testModels));
        }
    }
?>