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

    /**
     * The purpose of this class is to drill through Modules,
     * build the database for freezing, and provide the other
     * function required to complete an install.
     */
    class AutoBuildOptimizedInstallUtil extends InstallUtil
    {
        /**
         * Given an installSettingsForm, run the install including the schema creation and default data load. This is
         * used by the interactice install and the command line install.
         * @param object $form
         * @param object $messageStreamer
         */
        public static function runInstallation($form, & $messageStreamer)
        {
            Yii::app()->params['isFreshInstall']    = true;
            assert('$form instanceof InstallSettingsForm');
            assert('$messageStreamer instanceof MessageStreamer');

            if (defined('IS_TEST'))
            {
                $perInstanceFilename     = "perInstanceTest.php";
                $debugFilename     = "debugTest.php";
            }
            else
            {
                @set_time_limit(1200);
                $perInstanceFilename     = "perInstance.php";
                $debugFilename     = "debug.php";
            }

            $messageStreamer->add(Zurmo::t('InstallModule', 'Connecting to Database.'));
            static::connectToDatabase($form->databaseType,
                                            $form->databaseHostname,
                                            $form->databaseName,
                                            $form->databaseUsername,
                                            $form->databasePassword,
                                            $form->databasePort);
            ForgetAllCacheUtil::forgetAllCaches();
            $messageStreamer->add(Zurmo::t('InstallModule', 'Dropping existing tables.'));
            static::dropAllTables();
            $messageStreamer->add(Zurmo::t('InstallModule', 'Creating super user.'));

            $messageLogger = new MessageLogger($messageStreamer);
            Yii::app()->custom->runBeforeInstallationAutoBuildDatabase($messageLogger);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Starting database schema creation.'));
            $startTime = microtime(true);
            $messageStreamer->add('debugOn:' . BooleanUtil::boolToString(YII_DEBUG));
            $messageStreamer->add('phpLevelCaching:' . BooleanUtil::boolToString(PHP_CACHING_ON));
            $messageStreamer->add('memcacheLevelCaching:' . BooleanUtil::boolToString(MEMCACHE_ON));
            static::autoBuildDatabase($messageLogger);
            $endTime = microtime(true);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Total autobuild time: {formattedTime} seconds.',
                array('{formattedTime}' => number_format(($endTime - $startTime), 3))));
            if (SHOW_QUERY_DATA)
            {
                $messageStreamer->add(PageView::getTotalAndDuplicateQueryCountContent());
                $messageStreamer->add(PageView::makeNonHtmlDuplicateCountAndQueryContent());
            }
            $messageStreamer->add(Zurmo::t('InstallModule', 'Database schema creation complete.'));
            $messageStreamer->add(Zurmo::t('InstallModule', 'Rebuilding Permissions.'));
            ReadPermissionsOptimizationUtil::rebuild();
            $messageStreamer->add(Zurmo::t('InstallModule', 'Freezing database.'));
            static::freezeDatabase();
            $messageStreamer->add(Zurmo::t('InstallModule', 'Writing Configuration File.'));

            static::writeConfiguration(INSTANCE_ROOT,
                                            $form->databaseType,
                                            $form->databaseHostname,
                                            $form->databaseName,
                                            $form->databaseUsername,
                                            $form->databasePassword,
                                            $form->databasePort,
                                            $form->memcacheHostname,
                                            (int)$form->memcachePortNumber,
                                            true,
                                            Yii::app()->language,
                                            $perInstanceFilename,
                                            $debugFilename,
                                            $form->hostInfo,
                                            $form->scriptUrl,
                                            $form->submitCrashToSentry);
            static::setZurmoTokenAndWriteToPerInstanceFile(INSTANCE_ROOT);
            ZurmoPasswordSecurityUtil::setPasswordSaltAndWriteToPerInstanceFile(INSTANCE_ROOT);
            static::createSuperUser('super', $form->superUserPassword);
            $messageStreamer->add(Zurmo::t('InstallModule', 'Setting up default data.'));
            DefaultDataUtil::load($messageLogger);
            Yii::app()->custom->runAfterInstallationDefaultDataLoad($messageLogger);

            // Send notification to super admin to delete test.php file in case if this
            // installation is used in production mode.
            $message                    = new NotificationMessage();
            $message->textContent       = Zurmo::t('InstallModule', 'If this website is in production mode, please remove the app/test.php file.');
            $rules                      = new RemoveApiTestEntryScriptFileNotificationRules();
            NotificationsUtil::submit($message, $rules);

            // If minify is disabled, inform user that they should fix issues and enable minify
            $setIncludePathServiceHelper = new SetIncludePathServiceHelper();
            if (!$setIncludePathServiceHelper->runCheckAndGetIfSuccessful())
            {
                $message                    = new NotificationMessage();
                $message->textContent       = Zurmo::t('InstallModule', 'Minify has been disabled due to a system issue. Try to resolve the problem and re-enable Minify.');
                $rules                      = new EnableMinifyNotificationRules();
                NotificationsUtil::submit($message, $rules);
            }
            $messageStreamer->add(Zurmo::t('InstallModule', 'Installation Complete.'));
        }

        /**
         * Auto builds the database.  Must manually set AuditEvent first to avoid issues building the AuditEvent
         * table. This is because AuditEvent is specially optimized during this build process to reduce how
         * long this takes to do.
         */
        public static function autoBuildDatabase(& $messageLogger)
        {
            ZurmoDatabaseCompatibilityUtil::createStoredFunctionsAndProcedures();
            $messageLogger->addInfoMessage(Zurmo::t('InstallModule','Searching for models'));
            $rootModels = PathUtil::getAllCanHaveBeanModelClassNames();
            $messageLogger->addInfoMessage(Zurmo::t('InstallModule', 'Models catalog built.'));
            RedBeanModelsToTablesAdapter::generateTablesFromModelClassNames($rootModels, $messageLogger);
        }
    }
?>