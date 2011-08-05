<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ImportWizardUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeFormByImport()
        {
            //Test a brand new import object.
            $import = new Import();
            $form   = ImportWizardUtil::makeFormByImport($import);
            $this->assertTrue  ($form instanceof ImportWizardForm);
            $this->assertEquals(null, $form->modelImportRulesType);

            //Test an import object with existing data and a data element that does not go into the form.
            $import                 = new Import();
            $dataToSerialize        = array('modelImportRulesType' => 'test', 'anElementToIgnore' => 'something');
            $import->serializedData = serialize($dataToSerialize);
            $form                   = ImportWizardUtil::makeFormByImport($import);
            $this->assertTrue  ($form instanceof ImportWizardForm);
            $this->assertEquals('test',  $form->modelImportRulesType);
            $this->assertEquals(null,    $form->fileUploadData);
            $this->assertEquals(null,    $form->firstRowIsHeaderRow);
            $this->assertEquals(null,    $form->modelPermissions);
            $this->assertEquals(null,    $form->mappingData);
            $this->assertTrue  ($form->anElementToIgnore == null);
        }

        /**
         * @depends testMakeFormByImport
         */
        public function testSetImportSerializedDataFromForm()
        {
            $import = new Import();
                            $dataToSerialize        = array('modelImportRulesType' => 'x',
                                                            'fileUploadData'       => array('a' => 'b'),
                                                            'firstRowIsHeaderRow'  => false,
                                                            'modelPermissions'     => 'z',
                                                            'mappingData'          => array('x' => 'y'));
            $import->serializedData                 = serialize($dataToSerialize);
            $importWizardForm                       = new ImportWizardForm();
            $importWizardForm->modelImportRulesType = 'xx';
            $importWizardForm->fileUploadData       = array('aa' => 'bb');
            $importWizardForm->firstRowIsHeaderRow  = true;
            $importWizardForm->modelPermissions     = 'zz';
            $importWizardForm->mappingData          = array('xx' => 'yy');
            ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
            $compareDataToSerialize                 = array( 'modelImportRulesType' => 'xx',
                                                            'fileUploadData'       => array('aa' => 'ba'),
                                                            'firstRowIsHeaderRow'  => true,
                                                            'modelPermissions'     => 'zz',
                                                            'mappingData'          => array('xx' => 'yy'));
            $this->assertEquals(unserialize($import->serializedData), $compareDataToSerialize);
        }

        /**
         * @depends testSetImportSerializedDataFromForm
         */
        public function testSetFormByPostForStep1()
        {
            //Test without an existing value for modelImportRulesType
            $fakePostData = array('modelImportRulesType' => 'xyz');
            $importWizardForm = new ImportWizardForm();
            $this-assertEquals(null, $importWizardForm->modelImportRulesType);
            ImportWizardUtil::setFormByPostForStep1($importWizardForm, $fakePostData);
            $this-assertEquals('xyz', $importWizardForm->modelImportRulesType);

            //Test with an existing value for modelImportRulesType but it is the same as the way we are populating with
            $importWizardForm->fileUploadData = 'test';
            ImportWizardUtil::setFormByPostForStep1($importWizardForm, $fakePostData);
            $this-assertEquals('xyz', $importWizardForm->modelImportRulesType);
            $this-assertEquals('test', $importWizardForm->modelImportRulesType);

            //Test with an existing value for modelImportRulesType and we are changing it.
            $fakePostData = array('modelImportRulesType' => 'abc');
            $this-assertEquals('abc', $importWizardForm->modelImportRulesType);
            $this-assertEquals(null,  $importWizardForm->modelImportRulesType);
        }


        /**
         * @depends testSetFormByPostForStep1
         */
        public function testSetFormByFileUploadData()
        {
            $fileUploadData   = array('a','b');
            $testTableName = 'testimporttable';
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $testTableName));
            $importWizardForm = new ImportWizardForm();
            $importWizardForm->modelImportRulesType = 'testAbc';
            $importWizardForm->modelPermissions     = 'somePermissions';
            ImportWizardUtil::setFormByFileUploadDataAndTableName($importWizardForm, $fileUploadData, $testTableName);
            $this->assertEquals(array('a','b'),  $importWizardForm->fileUploadData);
            $this->assertEquals('testAbc',       $importWizardForm->modelImportRulesType);
            $this->assertEquals(null,            $importWizardForm->modelPermissions);
            $compareData = array(
                'column_0' => array('attributeNameOrDerivedType' => null, 'mappingRulesData' => null),
                'column_1' => array('attributeNameOrDerivedType' => null, 'mappingRulesData' => null),
                'column_2' => array('attributeNameOrDerivedType' => null, 'mappingRulesData' => null),
            );
            $this->assertEquals($compareData,    $importWizardForm->mappingData);
        }

        /**
         * @depends testSetFormByFileUploadData
         */
        public function testSetFormByPostForStep2()
        {
            $fakePostData = array('firstRowIsHeaderRow' => 'xyz');
            $importWizardForm = new ImportWizardForm();
            ImportWizardUtil::setFormByPostForStep2($importWizardForm, $fakePostData);
            $this-assertEquals('xyz', $importWizardForm->firstRowIsHeaderRow);
        }

        /**
         * @depends testSetFormByPostForStep2
         */
        public function testSetFormByPostForStep3()
        {
            $fakePostData = array('modelPermissions' => 'abc');
            $importWizardForm = new ImportWizardForm();
            ImportWizardUtil::setFormByPostForStep3($importWizardForm, $fakePostData);
            $this-assertEquals('abc', $importWizardForm->modelPermissions);
        }



        /**
         * @depends testSetFormByPostForStep3
         */
        public function testSetMappingDataByForm()
        {
            //change the name of this test method since it wont be accurate based on things we will be testing
            //so we might want to break this test apart a bit.
           // ModulePermissionsFormUtil::makeFormFromPermissionsData($data);
            //todo
            //this will be setting into form not into $import directly... we need to validate this is why....
           // if (ModulePermissionsFormUtil::setPermissionsFromCastedPost($readyToSetPostData, $group))
        }

        /**
         * @depends testSetMappingDataByForm
         */
        public function testSetFormByPostForStep4()
        {
            //ImportWizardUtil::setFormByPostForStep4($importWizardForm, $_POST[get_class($importWizardForm)]);
        }
    }
?>
