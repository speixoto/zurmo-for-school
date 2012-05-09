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

    class ImportDataAnalysisSequentialProcessTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
        }

        public function testSequentialProcessViewFactory()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_1' => array('attributeIndexOrDerivedType' => 'phone',         'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))));

            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $serializedData['mappingData']     = $mappingData;
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            $config            = array('pagination' => array('pageSize' => 2));
            $dataProvider      = new ImportDataProvider($import->getTempTableName(), true, $config);
            $sequentialProcess = new ImportDataAnalysisSequentialProcess($import, $dataProvider);
            $sequentialProcess->run(null, null);
            $route = 'default/someAction';
            $view = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            $content = $view->render();
            $this->assertNotNull($content);
            $this->assertEquals('SequentialProcessView', get_class($view));

            //Now process the first run. Will process page 0.
            $sequentialProcess = new ImportDataAnalysisSequentialProcess($import, $dataProvider);
            $sequentialProcess->run('processColumns', null);
            $route   = 'default/someAction';
            $view    = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            $content = $view->render();
            $this->assertNotNull($content);
            $this->assertEquals('SequentialProcessView', get_class($view));
            $this->assertEquals(array('columnNameToProcess' => 'column_1'),  $sequentialProcess->getNextParams());
        }
    }
?>