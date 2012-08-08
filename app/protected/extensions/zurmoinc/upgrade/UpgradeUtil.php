<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * This program is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * This program is distributed in the hope that it will be useful, but WITHOUT
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
     *
     * The interactive user interfaces in modified source and object code versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the "Powered by
     * Zurmo" logo. If the display of the logo is not reasonably feasible for
     * technical reasons, the Appropriate Legal Notices must display the words
     * "Powered by Zurmo".
     ********************************************************************************/

    class UpgradeUtil
    {
        public static $currentZurmoVersion;

        public static function run(MessageLogger $messageLogger)
        {
            try {
                self::isApplicationInUpgradeMode();
                self::checkPermissions();
                self::checkIfZipExtensionIsLoaded();
                self::setCurrentZurmoVersion();
                $upgradeZipFile = self::checkForUpgradeZip();
                $upgradeExtractPath = self::unzipUpgradeZip($upgradeZipFile);
                $configuration = self::checkManifestIfVersionIsOk($upgradeExtractPath);
                self::loadUpgraderComponent($upgradeExtractPath);
                self::clearCache();

                $source = $upgradeExtractPath . DIRECTORY_SEPARATOR . 'filesToUpload';
                $destination = COMMON_ROOT . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

                $pathToConfigurationFolder = COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config';
                self::processBeforeConfigFiles();
                self::processConfigFiles($pathToConfigurationFolder);
                self::processAfterConfigFiles();

                self::processBeforeFiles();
                self::processFiles($source, $destination, $configuration);
                self::processAfterFiles();

                self::processBeforeUpdateSchema();
                self::processUpdateSchema();
                self::processAfterUpdateSchema();
                self::clearAssetsAndRunTimeItems();

                self::processFinalTouches();
                self::clearCache(); // you might have to do this after each of these steps.
                self::markUpgradeDone();
                self::removeUpgradeFiles();  //Should we do this?
            }
            catch (CException $e)
            {
                echo "\n\n" . 'Error during upgrade!' . "\n\n";
                echo $e->getMessage() . "\n";
                echo "Please fix error(s) and try again, or restore your database/files.\n\n";
                exit;
            }
        }

        /*
         * Check if application is in maintanance mode
         * @throws NotSupportedException
         * @return boolean
         */
        public function isApplicationInUpgradeMode()
        {
            if (isset(Yii::app()->maintananceMode) && Yii::app()->maintananceMode)
            {
                $message = 'Application is not in maintanance mode. Please edit perInstance.php file, and set "$maintananceMode  = true;"';
                throw new NotSupportedException($message);
            }
            return true;
        }

        /**
         * Check if all files are directories are writeable by user.
         * @throws FileNotWriteableException
         * @return boolean
         */
        public function checkPermissions()
        {
            // All files/folders must be writeable by user that runs upgrade process.
            $nonWriteableFilesOrFolders = FileUtil::getNonWriteableFilesOrFolders(COMMON_ROOT);
            if (!empty($nonWriteableFilesOrFolders))
            {
                $message = 'Not all files and folders are writeable by upgrade user. Please make next files or folders writeable:' . "\n";
                foreach ($nonWriteableFilesOrFolders as $nonWriteableFileOrFolder)
                {
                    $message .= $nonWriteableFileOrFolder . "\n";
                }
                throw new FileNotWriteableException($message);
            }
            return true;
        }

        /**
         * Check if PHP Zip extension is loaded.
         * @throws NotSupportedException
         * @return boolean
         */
        public function checkIfZipExtensionIsLoaded()
        {
            $isZipExtensionInstalled =  InstallUtil::checkZip();
            if (!$isZipExtensionInstalled)
            {
                $message = 'Zip PHP extension is required by upgrade process, please install it.';
                throw new NotSupportedException($message);
            }
            return true;
        }

        /**
         * Set current Zurmo version
         */
        public function setCurrentZurmoVersion()
        {
            self::$currentZurmoVersion = join('.', array(MAJOR_VERSION, MINOR_VERSION, PATCH_VERSION));
        }

        /**
         * Check if one and only one zip file exist, so upgrade process will use it.
         * @throws NotFoundException
         * @throws NotSupportedException
         * @return string $upgradeZipFile - path to zip file
         */
        public function checkForUpgradeZip()
        {
            $numberOfZipFiles = 0;
            $upgradePath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'upgrade';
            if (!is_dir($upgradePath))
            {
                $message = 'Please upload upgrade zip file to runtime/upgrade folder.';
                throw new NotFoundException($message);
            }

            $handle = opendir($upgradePath);
            while (($item = readdir($handle)) !== false)
            {
                if (end(explode('.', $item)) == 'zip')
                {
                    $upgradeZipFile = $upgradePath . DIRECTORY_SEPARATOR . $item;
                    $numberOfZipFiles++;
                }
            }

            if ($numberOfZipFiles != 1)
            {
                closedir($handle);
                $message = 'More then one zip files exist in runtime/upgrade folder. Please delete them all except one that you want to use for upgrade.';
                throw new NotSupportedException($message);
            }
            closedir($handle);
            return $upgradeZipFile;
        }

        /**
         * Unzip upgrade files.
         * @param string $upgradeZipFilePath
         * @throws NotSupportedException
         * @return string - path to unzipped files
         */
        public function unzipUpgradeZip($upgradeZipFilePath)
        {
            $isExtracted = false;
            $zip = new ZipArchive();
            $upgradeExtractPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "upgrade";
            $fileInfo = pathinfo($upgradeZipFilePath);
            if ($zip->open($upgradeZipFilePath) === true)
            {
                $isExtracted = $zip->extractTo($upgradeExtractPath);
                $zip->close();
            }
            if (!$isExtracted)
            {
                $message  = 'There was error during extraction process of ' . $upgradeZipFilePath . ". \n";
                $message .= 'Please check if the file is valid zip archive.';
                throw new NotSupportedException($message);
            }
            return $upgradeExtractPath . DIRECTORY_SEPARATOR . $fileInfo['filename'];
        }

        /**
         * Check if upgrade version is correct, and if can be executed for current ZUrmo version.
         * @param string $upgradeExtractPath
         * @throws NotSupportedException
         * @return array
         */
        public function checkManifestIfVersionIsOk($upgradeExtractPath)
        {
            require_once($upgradeExtractPath . DIRECTORY_SEPARATOR . 'manifest.php');
            if (preg_match_all('/^(\d+)\.(\d+)\.(\d+)$/', $configuration['fromVersion'], $fromVersionMatches) !== false)
            {
                if (preg_match_all('/^(\d+)\.(\d+)\.(\d+)$/', $configuration['toVersion'], $toVersionMatches) !== false)
                {
                    if ($fromVersionMatches[1][0] == MAJOR_VERSION && $fromVersionMatches[2][0] == MINOR_VERSION &&
                        $fromVersionMatches[3][0] == PATCH_VERSION)
                    {
                        return $configuration;
                    }
                    else
                    {
                        $upgradeZurmoVersion = "{$fromVersionMatches[1][0]}.{$fromVersionMatches[2][0]}.{$fromVersionMatches[3][0]}";
                        $installedZurmoVersion = MAJOR_VERSION . '.' . MINOR_VERSION . '.' . PATCH_VERSION;
                        $message  = "This upgrade is for different version of Zurmo ($upgradeZurmoVersion) \n";
                        $message .= "Installed Zurmo version is: {$installedZurmoVersion}";
                        throw new NotSupportedException($message);
                    }
                }
                else
                {
                    $message = 'Could not extract upgrade to version from manifest file.';
                    throw new NotSupportedException($message);
                }
            }
            else
            {
                $message = 'Could not extract upgrade from version from manifest file.';
                throw new NotSupportedException($message);
            }
        }

        /**
         * Load upgrader component  as yii component from upgrade files.
         * @param string $upgradeExtractPath
         */
        public function loadUpgraderComponent($upgradeExtractPath)
        {
            require_once($upgradeExtractPath . DIRECTORY_SEPARATOR . 'UpgraderComponent.php');
            $upgraderComponent = Yii::createComponent('UpgraderComponent');
            Yii::app()->setComponent('upgrader', $upgraderComponent);
        }

        /**
         * Clear cache
         */
        public function clearCache()
        {
            ForgetAllCacheUtil::forgetAllCaches();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processBeforeConfigFiles()
        {
            Yii::app()->upgrader->processBeforeConfigFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processConfigFiles($pathToConfigurationFolder)
        {
            Yii::app()->upgrader->processConfigFiles($pathToConfigurationFolder);
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processAfterConfigFiles()
        {
            Yii::app()->upgrader->processAfterConfigFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processBeforeFiles()
        {
            Yii::app()->upgrader->processBeforeFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processFiles($source, $destination, $configuration)
        {
            Yii::app()->upgrader->processFiles($source, $destination, $configuration);
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processAfterFiles()
        {
            Yii::app()->upgrader->processAfterFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processBeforeUpdateSchema()
        {
            Yii::app()->upgrader->processBeforeUpdateSchema();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processUpdateSchema()
        {
            Yii::app()->upgrader->processUpdateSchema();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processAfterUpdateSchema()
        {
            Yii::app()->upgrader->processAfterUpdateSchema();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function clearAssetsAndRunTimeItems()
        {
            Yii::app()->upgrader->clearAssetsAndRunTimeItems();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        public function processFinalTouches()
        {
            Yii::app()->upgrader->processFinalTouches();
        }

        public function markUpgradeDone()
        {
        }

        public function removeUpgradeFiles()
        {
        }
    }
?>