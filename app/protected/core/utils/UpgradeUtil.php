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

    class UpgradeUtil
    {
        const UPGRADE_STATE_KEY = 'zurmoUpgrade';

        /**
         * Run first part of upgrade process, which includes:
         * - Checking if application is in maintenance mode
         * - Check file permissions
         * - Check if php zip extension is loaded
         * - Load UpgraderComponent from extracted upgrade files
         * - Modify configure files if needed
         * - Copy, add and remove files
         * @param MessageStreamer $messageStreamer
         */
        public static function runPart1(MessageStreamer $messageStreamer, $doNotlAlterFiles = false)
        {
            try
            {
                $messageStreamer->add(Yii::t('Default', 'Checking permissions, files, upgrade version....'));
                $messageLogger = new MessageLogger($messageStreamer);

                self::setUpgradeState('zurmoUpgradeTimestamp', time());
                self::isApplicationInUpgradeMode();
                self::checkPermissions();
                self::checkIfZipExtensionIsLoaded();
                self::setCurrentZurmoVersion();
                $upgradeZipFile = self::checkForUpgradeZip();
                $upgradeExtractPath = self::unzipUpgradeZip($upgradeZipFile);
                self::setUpgradeState('zurmoUpgradeFolderPath', $upgradeExtractPath);

                $configuration = self::checkManifestIfVersionIsOk($upgradeExtractPath);
                $messageStreamer->add(Yii::t('Default', 'Check completed.'));
                $messageStreamer->add(Yii::t('Default', 'Loading UpgraderComponent.'));
                self::loadUpgraderComponent($upgradeExtractPath, $messageLogger);
                $messageStreamer->add(Yii::t('Default', 'UpgraderComponent loaded.'));
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();

                $messageStreamer->add(Yii::t('Default', 'Altering configuration files.'));
                $pathToConfigurationFolder = COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config';
                self::processBeforeConfigFiles();
                self::processConfigFiles($pathToConfigurationFolder);
                self::processAfterConfigFiles();

                if (!$doNotlAlterFiles)
                {
                    $messageStreamer->add(Yii::t('Default', 'Copying files.'));
                    self::processBeforeFiles();
                    self::processFiles($upgradeExtractPath, $configuration);
                    self::processAfterFiles();
                }

                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                $messageStreamer->add(Yii::t('Default', 'Part 1 complete.'));
            }
            catch (CException $e)
            {
                $messageStreamer->add(Yii::t('Default', 'Error during upgrade!'));
                $messageStreamer->add($e->getMessage());
                $messageStreamer->add(Yii::t('Default', 'Please fix error(s) and try again, or restore your database/files.'));
                Yii::app()->end();
            }
        }

        /**
         * Run second and last part of upgrade process, which include:
         * - Update schema
         * - Clean assets and runtime foders
         * - Process final tasks
         * - Remove upgrade files
         * - Clear cache
         * @param MessageStreamer $messageStreamer
         */
        public static function runPart2(MessageStreamer $messageStreamer)
        {
            try
            {
                $upgradeExtractPath = self::getUpgradeState('zurmoUpgradeFolderPath');
                $messageLogger = new MessageLogger($messageStreamer);

                self::isApplicationInUpgradeMode();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Loading UpgraderComponent.'));
                self::loadUpgraderComponent($upgradeExtractPath, $messageLogger);
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Running tasks before updating schema.'));
                self::processBeforeUpdateSchema();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Updating schema.'));
                self::processUpdateSchema($messageLogger);
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Running tasks after schema is updated.'));
                self::processAfterUpdateSchema();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Clearing assets and runtime folders.'));
                self::clearAssetsAndRunTimeItems();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Processing final touches.'));
                self::processFinalTouches();
                $messageStreamer->add(Yii::t('Default', 'Clearing cache.'));
                self::clearCache();
                $messageStreamer->add(Yii::t('Default', 'Removing upgrade files.'));
                self::removeUpgradeFiles($upgradeExtractPath);
                self::unsetUpgradeState();
                $messageStreamer->add(Yii::t('Default', 'Upgrade process completed.'));
            }
            catch (CException $e)
            {
                $messageStreamer->add(Yii::t('Default', 'Error during upgrade!'));
                $messageStreamer->add($e->getMessage());
                $messageStreamer->add(Yii::t('Default', 'Please fix error(s) and try again, or restore your database/files.'));
                Yii::app()->end();
            }
        }

        /*
         * Check if application is in maintanance mode
         * @throws NotSupportedException
         * @return boolean
         */
        public static function isApplicationInUpgradeMode()
        {
            if (isset(Yii::app()->maintenanceMode) && Yii::app()->maintenanceMode)
            {
                $message = Yii::t('Default', 'Application is not in maintenance mode. Please edit perInstance.php file, and set "$maintenanceMode = true;"');
                throw new NotSupportedException($message);
            }
            return true;
        }

        /**
         * Check if all files are directories are writeable by user.
         * @throws FileNotWriteableException
         * @return boolean
         */
        protected static function checkPermissions()
        {
            // All files/folders must be writeable by user that runs upgrade process.
            $nonWriteableFilesOrFolders = FileUtil::getNonWriteableFilesOrFolders(COMMON_ROOT);
            if (!empty($nonWriteableFilesOrFolders))
            {
                $message = Yii::t('Default', 'Not all files and folders are writeable by upgrade user. Please make these files or folders writeable:');
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
        protected static function checkIfZipExtensionIsLoaded()
        {
            $isZipExtensionInstalled =  InstallUtil::checkZip();
            if (!$isZipExtensionInstalled)
            {
                $message = Yii::t('Default', 'Zip PHP extension is required by upgrade process, please install it.');
                throw new NotSupportedException($message);
            }
            return true;
        }

        /**
         * Set current Zurmo version
         */
        protected static function setCurrentZurmoVersion()
        {
            $currentZurmoVersion = join('.', array(MAJOR_VERSION, MINOR_VERSION, PATCH_VERSION));
            self::setUpgradeState('zurmoVersionBeforeUpgrade', $currentZurmoVersion);
        }

        /**
         * Check if one and only one zip file exist, so upgrade process will use it.
         * @throws NotFoundException
         * @throws NotSupportedException
         * @return string $upgradeZipFile - path to zip file
         */
        protected static function checkForUpgradeZip()
        {
            $numberOfZipFiles = 0;
            $upgradePath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'upgrade';
            if (!is_dir($upgradePath))
            {
                $message = Yii::t('Default', 'Please upload upgrade zip file to runtime/upgrade folder.');
                throw new NotFoundException($message);
            }

            $handle = opendir($upgradePath);
            while (($item = readdir($handle)) !== false)
            {
                $filePath = explode('.', $item);
                if (end($filePath) == 'zip')
                {
                    $upgradeZipFile = $upgradePath . DIRECTORY_SEPARATOR . $item;
                    $numberOfZipFiles++;
                }
            }

            if ($numberOfZipFiles != 1)
            {
                closedir($handle);
                $message = Yii::t('Default', 'More then one zip file exists in runtime/upgrade folder. ' .
                                             'Please delete them all except the one that you want to use for the upgrade.');
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
        protected static function unzipUpgradeZip($upgradeZipFilePath)
        {
            // Remove extracted files, if they already exists.
            $fileInfo = pathinfo($upgradeZipFilePath);
            FileUtil::deleteDirectoryRecursive($fileInfo['dirname'], false, array($fileInfo['basename'], 'index.html'));

            $isExtracted = false;
            $zip = new ZipArchive();
            $upgradeExtractPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "upgrade";

            if ($zip->open($upgradeZipFilePath) === true)
            {
                $isExtracted = $zip->extractTo($upgradeExtractPath);
                $zip->close();
            }
            if (!$isExtracted)
            {
                $message  = Yii::t('Default', 'There was an error during the extraction process of {zipFilePath}', array('{zipFilePath}' => $upgradeZipFilePath));
                $message .= Yii::t('Default', 'Please check if the file is a valid zip archive.');
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
        protected static function checkManifestIfVersionIsOk($upgradeExtractPath)
        {
            require_once($upgradeExtractPath . DIRECTORY_SEPARATOR . 'manifest.php');
            if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $configuration['fromVersion'], $upgradeFromVersionMatches) !== false) // Not Coding Standard
            {
                if (preg_match('/^(\d+)\.(\d+)\.(\d+)$/', $configuration['toVersion'], $upgradeToVersionMatches) !== false) // Not Coding Standard
                {
                    $currentZurmoVersion = MAJOR_VERSION . '.' . MINOR_VERSION . '.' . PATCH_VERSION;
                    if (version_compare($currentZurmoVersion, $upgradeFromVersionMatches[0], '>=') &&
                        version_compare($currentZurmoVersion, $upgradeToVersionMatches[0],   '<=') )
                    {
                        return $configuration;
                    }
                    else
                    {
                        $message  = Yii::t('Default', 'This upgrade is for Zurmo ({fromVersion} - {toVersion})',
                            array ('{fromVersion}' => $upgradeFromVersionMatches[0], '{toVersion}' => $upgradeToVersionMatches[0]));
                        $message .= Yii::t('Default', 'Installed Zurmo version is: {currentZurmoVersion}',
                            array('{currentZurmoVersion}' => $currentZurmoVersion));
                        throw new NotSupportedException($message);
                    }
                }
                else
                {
                    $message = Yii::t('Default', 'Could not extract upgrade "to version" in the manifest file.');
                    throw new NotSupportedException($message);
                }
            }
            else
            {
                $message = Yii::t('Default', 'Could not extract upgrade "from version" in the manifest file.');
                throw new NotSupportedException($message);
            }
        }

        /**
         * Load upgrader component  as yii component from upgrade files.
         * @param string $upgradeExtractPath
         */
        protected static function loadUpgraderComponent($upgradeExtractPath, MessageLogger $messageLogger)
        {
            if (file_exists($upgradeExtractPath . DIRECTORY_SEPARATOR . 'UpgraderComponent.php'))
            {
                require_once($upgradeExtractPath . DIRECTORY_SEPARATOR . 'UpgraderComponent.php');

                $upgraderComponent = Yii::createComponent(
                    array('class' => 'UpgraderComponent', 'messageLogger' => $messageLogger)
                );
                Yii::app()->setComponent('upgrader', $upgraderComponent);
            }
            else
            {
                $message = Yii::t('Default', 'Upgrade file is missing.');
                throw new NotSupportedException($message);
            }
        }

        /**
         * Clear cache
         */
        protected static function clearCache()
        {
            ForgetAllCacheUtil::forgetAllCaches();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processBeforeConfigFiles()
        {
            Yii::app()->upgrader->processBeforeConfigFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processConfigFiles($pathToConfigurationFolder)
        {
            Yii::app()->upgrader->processConfigFiles($pathToConfigurationFolder);
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processAfterConfigFiles()
        {
            Yii::app()->upgrader->processAfterConfigFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processBeforeFiles()
        {
            Yii::app()->upgrader->processBeforeFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         * @param string $upgradeExtractPath
         * @param array $configuration
         */
        protected static function processFiles($upgradeExtractPath, $configuration)
        {
            $source = $upgradeExtractPath . DIRECTORY_SEPARATOR . 'filesToUpload';
            $destination = COMMON_ROOT . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            Yii::app()->upgrader->processFiles($source, $destination, $configuration);
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processAfterFiles()
        {
            Yii::app()->upgrader->processAfterFiles();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processBeforeUpdateSchema()
        {
            Yii::app()->upgrader->processBeforeUpdateSchema();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processUpdateSchema($messageLogger)
        {
            Yii::app()->upgrader->processUpdateSchema($messageLogger);
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processAfterUpdateSchema()
        {
            Yii::app()->upgrader->processAfterUpdateSchema();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function clearAssetsAndRunTimeItems()
        {
            Yii::app()->upgrader->clearAssetsAndRunTimeItems();
        }

        /**
         * This is just wrapper function to call function from UpgraderComponent
         */
        protected static function processFinalTouches()
        {
            Yii::app()->upgrader->processFinalTouches();
        }

        protected static function removeUpgradeFiles($upgradeExtractPath)
        {
            FileUtil::deleteDirectoryRecursive($upgradeExtractPath, true);
        }

        /**
         * Set upgrade state into Zurmo persistent storage
         * @param string $key
         * @param string $value
         * @return boolean
         */
        public static function setUpgradeState($key, $value)
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            $state[self::UPGRADE_STATE_KEY][$key] = $value;
            $statePersister->save($state);
            return true;
        }

        /**
         * Get upgrade state from Zurmo persistent storage
         * @param string $key
         * @return mixed
         */
        public static function getUpgradeState($key)
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            if (isset($state[self::UPGRADE_STATE_KEY][$key]))
            {
                return $state[self::UPGRADE_STATE_KEY][$key];
            }
            return null;
        }

        /**
         * Clear upgrade info from Zurmo state persister
         */
        public static function unsetUpgradeState()
        {
            $statePersister = Yii::app()->getStatePersister();
            $state = $statePersister->load();
            unset($state[self::UPGRADE_STATE_KEY]);
            $statePersister->save($state);
            return true;
        }

        /**
         * Check if upgrade state still valid
         * @return boolean
         */
        public static function isUpgradeStateValid()
        {
            $zurmoUpgradeTimestamp = self::getUpgradeState('zurmoUpgradeTimestamp');
            if ((time() - $zurmoUpgradeTimestamp) > 24 * 60 * 60)
            {
                self::unsetUpgradeState();
                return false;
            }
            else
            {
                return true;
            }
        }
    }
?>