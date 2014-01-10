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

    class ExportCleanupJobTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testRun()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            //Create 2 export items, and set one with a date over a week ago (8 days ago) for the modifiedDateTime
            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize(array('test', 'test2'));
            $this->assertTrue($exportItem->save());

            $fileContent          = new FileContent();
            $fileContent->content = 'test';

            $exportFileModel = new ExportFileModel();
            $exportFileModel->fileContent = $fileContent;
            $exportFileModel->name = $exportItem->exportFileName . ".csv";
            $exportFileModel->type    = 'application/octet-stream';
            $exportFileModel->size    = strlen($fileContent->content);

            $this->assertTrue($exportFileModel->save());
            $exportFileModel1Id = $exportFileModel->id;

            $exportItem->exportFileModel = $exportFileModel;
            $this->assertTrue($exportItem->save());

            $modifiedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (60 * 60 *24 * 8));
            $sql = "Update item set modifieddatetime = '" . $modifiedDateTime . "' where id = " .
                $exportItem->getClassId('Item');
            ZurmoRedBean::exec($sql);

            // Second exportItem, that shouldn't be deleted.
            $exportItem2 = new ExportItem();
            $exportItem2->isCompleted = 0;
            $exportItem2->exportFileType = 'csv';
            $exportItem2->exportFileName = 'test';
            $exportItem2->modelClassName = 'Account';
            $exportItem2->serializedData = serialize(array('test', 'test2'));
            $this->assertTrue($exportItem2->save());

            $fileContent2          = new FileContent();
            $fileContent2->content = 'test';

            $exportFileModel2 = new ExportFileModel();
            $exportFileModel2->fileContent = $fileContent2;
            $exportFileModel2->name = $exportItem->exportFileName . ".csv";
            $exportFileModel2->type    = 'application/octet-stream';
            $exportFileModel2->size    = strlen($fileContent->content);

            $this->assertTrue($exportFileModel2->save());
            $exportFileModel2Id = $exportFileModel2->id;

            $exportItem2->exportFileModel = $exportFileModel2;
            $this->assertTrue($exportItem2->save());

            $job = new ExportCleanupJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $this->assertEquals($exportItem2->id, $exportItems[0]->id);
        }
    }
?>