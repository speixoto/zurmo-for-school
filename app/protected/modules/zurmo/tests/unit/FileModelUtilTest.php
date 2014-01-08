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

    class FileModelUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public static function tearDownAfterClass()
        {
            parent::tearDownAfterClass();
            $_FILES = null;
        }

        public static function getDependentTestModelClassNames()
        {
            return array('ModelWithAttachmentTestItem');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveModelsHasManyFilesFromPost()
        {
            $fileCount = FileModel::getCount();
            $this->assertEquals(0, $fileCount);
            $file1 = ZurmoTestHelper::createFileModel('testNote.txt');
            $file2 = ZurmoTestHelper::createFileModel('testNote.txt');
            $file3 = ZurmoTestHelper::createFileModel('testNote.txt');

            $model = new ModelWithAttachmentTestItem();
            $_POST['myTest'] = array($file1->id, $file2->id, $file3->id);
            FileModelUtil::resolveModelsHasManyFilesFromPost($model, 'files', 'myTest');
            $model->member = 'test';
            $saved = $model->save();
            $this->assertTrue($saved);

            $fileCount = FileModel::getCount();
            $this->assertEquals(3, $fileCount);

            $modelId = $model->id;
            $model->forget();
            $model = ModelWithAttachmentTestItem::getById($modelId);
            $this->assertEquals(3, $model->files->count());

            //Add a fourth file.
            $file4 = ZurmoTestHelper::createFileModel('testNote.txt');
            $_POST['myTest'] = array($file1->id, $file2->id, $file3->id, $file4->id);
            FileModelUtil::resolveModelsHasManyFilesFromPost($model, 'files', 'myTest');
            $saved = $model->save();
            $this->assertTrue($saved);
            $fileCount = FileModel::getCount();
            $this->assertEquals(4, $fileCount);
            $model->forget();
            $model = ModelWithAttachmentTestItem::getById($modelId);
            $this->assertEquals(4, $model->files->count());

            //Remove the 2nd file.
            $_POST['myTest'] = array($file1->id, $file3->id, $file4->id);
            FileModelUtil::resolveModelsHasManyFilesFromPost($model, 'files', 'myTest');
            $saved = $model->save();
            $this->assertTrue($saved);
            $fileCount = FileModel::getCount();
            $this->assertEquals(3, $fileCount);
            $model->forget();
            $model = ModelWithAttachmentTestItem::getById($modelId);
            $this->assertEquals(3, $model->files->count());
            $compareIds = array($file1->id, $file3->id, $file4->id);
            foreach ($model->files as $fileModel)
            {
                $this->assertTrue(in_array($fileModel->id, $compareIds));
            }
        }

        public function testDifferentMimeTypes()
        {
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $contents1    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt');
            $contents2    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testDocument.docx');
            $contents3    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png');
            $contents4    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testPDF.pdf');
            $contents5    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testZip.zip');

            $file1 = ZurmoTestHelper::createFileModel('testNote.txt');
            $file2 = ZurmoTestHelper::createFileModel('testDocument.docx');
            $file3 = ZurmoTestHelper::createFileModel('testImage.png');
            $file4 = ZurmoTestHelper::createFileModel('testPDF.pdf');
            $file5 = ZurmoTestHelper::createFileModel('testZip.zip');

            $file1Id = $file1->id;
            $file2Id = $file2->id;
            $file3Id = $file3->id;
            $file4Id = $file4->id;
            $file5Id = $file5->id;

            $file1->forget();
            $file2->forget();
            $file3->forget();
            $file4->forget();
            $file5->forget();

            $file1 = FileModel::getById($file1Id);
            $this->assertEquals($contents1, $file1->fileContent->content);
            $this->assertEquals('testNote.txt', $file1->name);
            $this->assertEquals('text/plain', $file1->type);
            $this->assertEquals(6495, $file1->size);

            $file2 = FileModel::getById($file2Id);
            $this->assertEquals($contents2, $file2->fileContent->content);
            $this->assertEquals('testDocument.docx', $file2->name);
            $this->assertEquals('application/msword', $file2->type);
            $this->assertEquals(14166, $file2->size);

            $file3 = FileModel::getById($file3Id);
            $this->assertEquals($contents3, $file3->fileContent->content);
            $this->assertEquals('testImage.png', $file3->name);
            $this->assertEquals('image/png', $file3->type);
            $this->assertEquals(3332, $file3->size);

            $file4 = FileModel::getById($file4Id);
            $this->assertEquals($contents4, $file4->fileContent->content);
            $this->assertEquals('testPDF.pdf', $file4->name);
            $this->assertEquals('application/pdf', $file4->type);
            $this->assertEquals(81075, $file4->size);

            $file5 = FileModel::getById($file5Id);
            $this->assertEquals($contents5, $file5->fileContent->content);
            $this->assertEquals('testZip.zip', $file5->name);
            $this->assertEquals('application/zip', $file5->type);
            $this->assertEquals(3492, $file5->size);
        }

        public function testMakeByExistingFileModelId()
        {
            $oldFileContentsCount = FileContent::getCount();
            $file1          = ZurmoTestHelper::createFileModel('testNote.txt');
            $file2          = FileModelUtil::makeByExistingFileModelId($file1->id, true);
            $file1Content   = $file1->fileContent;
            $file2Id        = $file2->id;
            $this->assertTrue($file2 !== false);
            $this->assertEquals($file1->name, $file2->name);
            $this->assertEquals($file1->type, $file2->type);
            $this->assertEquals($file1->size, $file2->size);
            $this->assertEquals($file1Content->content, $file2->fileContent->content);
            // because fileContent was not replicated but referenced in file2.
            $this->assertEquals($file1Content->id, $file2->fileContent->id);
            $this->assertEquals($oldFileContentsCount + 1, FileContent::getCount());

            $file1->delete();
            FileModel::forgetAll();
            $file2 = FileModel::getById($file2Id);
            $this->assertEquals($file1Content->content, $file2->fileContent->content);
            // deleting file1 should still preserve its content as its being used by file2.
            $this->assertEquals($oldFileContentsCount + 1, FileContent::getCount());

            $file2->delete();
            // deleting fil2 should get rid of the fileContent too.
            $this->assertEquals($oldFileContentsCount, FileContent::getCount());
        }

        public function testMakeByExistingFileModelIdWithoutSharedContent()
        {
            $oldFileContentsCount = FileContent::getCount();
            $file1  = ZurmoTestHelper::createFileModel('testNote.txt');
            $file2  = FileModelUtil::makeByExistingFileModelId($file1->id, false);
            $this->assertTrue($file2 !== false);
            $this->assertEquals($file1->name, $file2->name);
            $this->assertEquals($file1->type, $file2->type);
            $this->assertEquals($file1->size, $file2->size);
            $this->assertEquals($file1->fileContent->content, $file2->fileContent->content);
            // fileContent should be replicated as we explicitly passed false to sharedContent
            $this->assertNotEquals($file1->fileContent->id, $file2->fileContent->id);
            $this->assertEquals($oldFileContentsCount + 2, FileContent::getCount());
        }
    }
?>