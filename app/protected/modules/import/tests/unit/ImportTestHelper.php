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

    class ImportTestHelper
    {
        public static function createTempTableByFileNameAndTableName($fileName,
                                                                     $tableName,
                                                                     $pathToFiles = null,
                                                                     $delimiter = ',', // Not Coding Standard
                                                                     $enclosure = '"')
        {
            assert('is_string($fileName)');
            assert('is_string($tableName)');
            if ($pathToFiles == null)
            {
                $pathToFiles = Yii::getPathOfAlias('application.modules.import.tests.unit.files');
            }
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . $fileName;
            // Use this for just those two files, because orginal files will be overwritten
            // For these two files, we make backup
            if ($fileName == 'importTestMacOsLineEndingsCopy.csv' || $fileName == 'importTestWindowsCopy.csv')
            {
                ImportUploadedFileUtil::convertWindowsAndMacLineEndingsIntoUnixLineEndings($filePath);
            }
            $fileHandle  = fopen($filePath, 'r');
            if ($fileHandle !== false)
            {
                $created = ImportDatabaseUtil::makeDatabaseTableByFileHandleAndTableName($fileHandle, $tableName,
                                                                                         $delimiter, $enclosure);
                assert('$created');
                return true;
            }
            return false;
        }

        public static function createImportModelTestItem($string, $lastName)
        {
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $model = new ImportModelTestItem();
            $model->string   = $string;
            $model->lastName = $lastName;
            $saved           = $model->save();
            assert('$saved');
            if ($freeze)
            {
                RedBeanDatabase::freeze();
            }
            return $model;
        }

        public static function createImportModelTestItem2($name)
        {
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $model = new ImportModelTestItem2();
            $model->name = $name;
            $saved = $model->save();
            assert('$saved');
            if ($freeze)
            {
                RedBeanDatabase::freeze();
            }
            return $model;
        }

        public static function createImportModelTestItem3($name)
        {
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $model = new ImportModelTestItem3();
            $model->name = $name;
            $saved = $model->save();
            assert('$saved');
            if ($freeze)
            {
                RedBeanDatabase::freeze();
            }
            return $model;
        }

        public static function createImportModelTestItem4($name)
        {
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $model = new ImportModelTestItem4();
            $model->name = $name;
            $saved = $model->save();
            assert('$saved');
            if ($freeze)
            {
                RedBeanDatabase::freeze();
            }
            return $model;
        }

        public static function updateModelsExternalId(RedBeanModel $model, $externalId)
        {
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            R::exec("update " . $model::getTableName(get_class($model))
            . " set $columnName = '" . $externalId . "' where id = {$model->id}");
        }
    }
?>