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

    class BeginRequestBehavior extends CBehavior
    {
        protected static $allowedGuestUserRoutes = array(
            'zurmo/default/unsupportedBrowser',
            'zurmo/default/login',
            'tracking/default/track',
            'marketingLists/external/',
            'contacts/external/',
            'zurmo/imageModel/getImage/',
            'zurmo/imageModel/getThumb/',
            'min/serve');

        public function attach($owner)
        {
            if ($this->resolveIsApiRequest())
            {
                $this->attachApiRequestBehaviors($owner);
                if (Yii::app()->isApplicationInstalled())
                {
                    $this->attachApiRequestBehaviorsForInstalledApplication($owner);
                }
            }
            else
            {
                $this->attachNonApiRequestBehaviors($owner);
                if (!Yii::app()->isApplicationInstalled())
                {
                    $this->attachNonApiRequestBehaviorsForNonInstalledApplication($owner);
                }
                else
                {
                    $this->attachNonApiRequestBehaviorsForInstalledApplication($owner);
                }
            }
        }

        protected function resolveIsApiRequest()
        {
            return ApiRequest::isApiRequest();
        }

        protected function attachApiRequestBehaviors(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleSentryLogs'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleApplicationCache'));
            $owner->detachEventHandler('onBeginRequest', array(Yii::app()->request, 'validateCsrfToken'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleImports'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleSetupDatabaseConnection'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleDisableGamification'));
            $this->resolveBeginApiRequest($owner);
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLibraryCompatibilityCheck'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleStartPerformanceClock'));
        }

        protected function resolveBeginApiRequest(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleInitApiRequest'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginApiRequest'));
        }

        protected function attachApiRequestBehaviorsForInstalledApplication(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleClearCache'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadWorkflowsObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadReadPermissionSubscriptionObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadContactLatestActivityDateTimeObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadAccountLatestActivityDateTimeObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleCheckAndUpdateCurrencyRates'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleResolveCustomData'));
        }

        protected function attachNonApiRequestBehaviors(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleSentryLogs'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleApplicationCache'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleImports'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLibraryCompatibilityCheck'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleStartPerformanceClock'));
        }

        protected function attachNonApiRequestBehaviorsForNonInstalledApplication(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleInstanceFolderCheck'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleInstallCheck'));
        }

        /**
         * @see CommandBeginRequestBehavior, make sure if you change this array, you add anything needed
         * for the command behavior as well.
         * @param CComponent $owner
         */
        protected function attachNonApiRequestBehaviorsForInstalledApplication(CComponent $owner)
        {
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleSetupDatabaseConnection'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleBeginRequest'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleClearCache'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadLanguage'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadTimeZone'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleUserTimeZoneConfirmed'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadActivitiesObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadConversationsObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadEmailMessagesObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadWorkflowsObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadReadPermissionSubscriptionObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadContactLatestActivityDateTimeObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadAccountLatestActivityDateTimeObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadAccountContactAffiliationObserver'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleLoadGamification'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleCheckAndUpdateCurrencyRates'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handleResolveCustomData'));
            $owner->attachEventHandler('onBeginRequest', array($this, 'handlePublishLogoAssets'));
        }

        public function handleSentryLogs()
        {
            if (!YII_DEBUG && defined('SUBMIT_CRASH_TO_SENTRY') && SUBMIT_CRASH_TO_SENTRY)
            {
                Yii::import('application.extensions.sentrylog.RSentryLog');
                $rSentryLog = Yii::createComponent(
                    array('class' => 'RSentryLog', 'dsn' => Yii::app()->params['sentryDsn']));
                // Have to invoke component init(), because it is not called automatically
                $rSentryLog->init();
                $component   = Yii::app()->getComponent('log');
                $allRoutes   = $component->getRoutes();
                $allRoutes[] = $rSentryLog;
                $component->setRoutes($allRoutes);
                Yii::app()->setComponent('log', $component);
            }
        }

        /**
        * Load memcache extension if memcache extension is
        * loaded and if memcache server is avalable
        * @param $event
        */
        public function handleApplicationCache($event)
        {
            if (MEMCACHE_ON)
            {
                //Yii::import('application.core.components.ZurmoMemCache');
                $memcacheServiceHelper = new MemcacheServiceHelper();
                if ($memcacheServiceHelper->runCheckAndGetIfSuccessful())
                {
                    $cacheComponent = Yii::createComponent(array(
                        'class'     => 'CMemCache',
                        'keyPrefix' => ZURMO_TOKEN,
                        'servers'   => Yii::app()->params['memcacheServers']));
                    Yii::app()->setComponent('cache', $cacheComponent);
                }
                // todo: Find better way to append this prefix for tests.
                // We can't put this code only in BeginRequestTestBehavior, because for API tests we are using  BeginRequestBehavior
                if (defined('IS_TEST'))
                {
                    ZurmoCache::setAdditionalStringForCachePrefix('Test');
                }
            }
        }

        /**
        * Import all files that need to be included(for lazy loading)
        * @param $event
        */
        public function handleImports($event)
        {
            //Clears file cache so that everything is clean.
            if (isset($_GET['clearCache']) && $_GET['clearCache'] == 1)
            {
                GeneralCache::forgetEntry('filesClassMap');
            }
            try
            {
                // not using default value to save cpu cycles on requests that follow the first exception.
                Yii::$classMap = GeneralCache::getEntry('filesClassMap');
            }
            catch (NotFoundException $e)
            {
                $filesToInclude   = FileUtil::getFilesFromDir(Yii::app()->basePath . '/modules', Yii::app()->basePath . '/modules', 'application.modules');
                $filesToIncludeFromCore = FileUtil::getFilesFromDir(Yii::app()->basePath . '/core', Yii::app()->basePath . '/core', 'application.core');
                $totalFilesToIncludeFromModules = count($filesToInclude);

                foreach ($filesToIncludeFromCore as $key => $file)
                {
                    $filesToInclude[$totalFilesToIncludeFromModules + $key] = $file;
                }
                foreach ($filesToInclude as $file)
                {
                    Yii::import($file);
                }
                GeneralCache::cacheEntry('filesClassMap', Yii::$classMap);
            }
        }

        /**
        * This check is required during installation since if runtime, assets and data folders are missing
        * yii web application can not be started correctly.
        * @param $event
        */
        public function handleInstanceFolderCheck($event)
        {
            $instanceFoldersServiceHelper = new InstanceFoldersServiceHelper();
            if (!$instanceFoldersServiceHelper->runCheckAndGetIfSuccessful())
            {
                echo $instanceFoldersServiceHelper->getMessage();
                Yii::app()->end(0, false);
            }
        }

        public function handleInstallCheck($event)
        {
            $allowedInstallUrls = array (
                Yii::app()->createUrl('zurmo/default/unsupportedBrowser'),
                Yii::app()->createUrl('install/default'),
                Yii::app()->createUrl('install/default/welcome'),
                Yii::app()->createUrl('install/default/checkSystem'),
                Yii::app()->createUrl('install/default/settings'),
                Yii::app()->createUrl('install/default/runInstallation'),
                Yii::app()->createUrl('install/default/installDemoData'),
                Yii::app()->createUrl('min/serve')
            );
            $reqestedUrl = Yii::app()->getRequest()->getUrl();
            $redirect = true;
            foreach ($allowedInstallUrls as $allowedUrl)
            {
                if (strpos($reqestedUrl, $allowedUrl) === 0)
                {
                    $redirect = false;
                    break;
                }
            }
            if ($redirect)
            {
                $url = Yii::app()->createUrl('install/default');
                Yii::app()->request->redirect($url);
            }
        }

        /**
         * Called if installed, and logged in.
         * @param CEvent $event
         */
        public function handleUserTimeZoneConfirmed($event)
        {
            if (!Yii::app()->user->isGuest && !Yii::app()->timeZoneHelper->isCurrentUsersTimeZoneConfirmed())
            {
                $allowedTimeZoneConfirmBypassUrls = array (
                    Yii::app()->createUrl('users/default/confirmTimeZone'),
                    Yii::app()->createUrl('min/serve'),
                    Yii::app()->createUrl('zurmo/default/logout'),
                );
                $reqestedUrl = Yii::app()->getRequest()->getUrl();
                $isUrlAllowedToByPass = false;
                foreach ($allowedTimeZoneConfirmBypassUrls as $url)
                {
                    if (strpos($reqestedUrl, $url) === 0)
                    {
                        $isUrlAllowedToByPass = true;
                    }
                }
                if (!$isUrlAllowedToByPass)
                {
                    $url = Yii::app()->createUrl('users/default/confirmTimeZone');
                    Yii::app()->request->redirect($url);
                }
            }
        }

        public function handleBeginRequest($event)
        {
            // Create list of allowed urls.
            // Those urls should be accessed during upgrade process too.
            $allowedGuestUserRoutes = static::getAllowedGuestUserRoutes();
            foreach ($allowedGuestUserRoutes as $allowedGuestUserRoute)
            {
                $allowedGuestUserUrls[] = Yii::app()->createUrl($allowedGuestUserRoute);
            }
            $requestedUrl = Yii::app()->getRequest()->getUrl();
            $isUrlAllowedToGuests = false;
            foreach ($allowedGuestUserUrls as $url)
            {
                if (strpos($requestedUrl, $url) === 0)
                {
                    $isUrlAllowedToGuests = true;
                }
            }

            if (Yii::app()->user->isGuest)
            {
                if (!$isUrlAllowedToGuests)
                {
                    Yii::app()->user->loginRequired();
                }
            }
            else
            {
                if (Yii::app()->isApplicationInMaintenanceMode())
                {
                    if (!$isUrlAllowedToGuests)
                    {
                        // Allow access only to users that belongs to Super Administrators.
                        $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
                        if (!$group->users->contains(Yii::app()->user->userModel))
                        {
                            echo Zurmo::t('ZurmoModule', 'Application is in maintenance mode. Please try again later.');
                            exit;
                        }
                        else
                        {
                            // Super Administrators can access all pages, but inform them that application is in maintenance mode.
                            Yii::app()->user->setFlash('notification', Zurmo::t('ZurmoModule', 'Application is in maintenance mode, and only Super Administrators can access it.'));
                        }
                    }
                }
            }
        }

        public function handleInitApiRequest($event)
        {
            $apiRequest = Yii::createComponent(
                array('class' => 'application.modules.api.components.ApiRequest'));
            $apiRequest->init();
            Yii::app()->setComponent('apiRequest', $apiRequest);
            Yii::app()->apiRequest->init();

            $apiHelper = Yii::createComponent(
                array('class' => 'application.modules.api.components.ZurmoApiHelper'));
            //Have to invoke component init(), because it is not called automatically
            $apiHelper->init();
            Yii::app()->setComponent('apiHelper', $apiHelper);
        }

        public function handleBeginApiRequest($event)
        {
            if (Yii::app()->isApplicationInMaintenanceMode())
            {
                $message = Zurmo::t('ZurmoModule', 'Application is in maintenance mode. Please try again later.');
                $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, null);
                Yii::app()->apiHelper->sendResponse($result);
                exit;
            }
            if (Yii::app()->user->isGuest)
            {
                $allowedGuestUserUrls = array (
                    Yii::app()->createUrl('zurmo/api/login'),
                    Yii::app()->createUrl('zurmo/api/logout'),
                );
                $isUrlAllowedToGuests = false;
                foreach ($allowedGuestUserUrls as $url)
                {
                    if (Yii::app()->urlManager->getPositionOfPathInUrl($url) !== false)
                    {
                        $isUrlAllowedToGuests = true;
                        break;
                    }
                }

                if (!$isUrlAllowedToGuests)
                {
                    $message = Zurmo::t('ZurmoModule', 'Sign in required.');
                    $result = new ApiResult(ApiResponse::STATUS_FAILURE, null, $message, null);
                    Yii::app()->apiHelper->sendResponse($result);
                    exit;
                }
            }
        }

        public function handleLibraryCompatibilityCheck($event)
        {
            $basePath       = Yii::app()->getBasePath();
            require_once("$basePath/../../redbean/rb.php");
            $redBeanVersion =  ZurmoRedBean::getVersion();
            $yiiVersion     =  YiiBase::getVersion();
            if ( $redBeanVersion != Yii::app()->params['redBeanVersion'])
            {
                echo Zurmo::t('ZurmoModule', 'Your RedBean version is currentVersion and it should be acceptableVersion.',
                                array(  'currentVersion' => $redBeanVersion,
                                        'acceptableVersion' => Yii::app()->params['redBeanVersion']));
                Yii::app()->end(0, false);
            }
            if ( $yiiVersion != Yii::app()->params['yiiVersion'])
            {
                echo Zurmo::t('ZurmoModule', 'Your Yii version is currentVersion and it should be acceptableVersion.',
                                array(  'currentVersion' => $yiiVersion,
                                        'acceptableVersion' => Yii::app()->params['yiiVersion']));
                Yii::app()->end(0, false);
            }
        }

        /**
         * In the case where you have reloaded the database, some cached items might still exist.  This is a way
         * to clear that cache. Helpful during development and testing.
         */
        public function handleClearCache($event)
        {
            if (isset($_GET['clearCache']) && $_GET['clearCache'] == 1)
            {
                ForgetAllCacheUtil::forgetAllCaches();
                $this->clearCacheDirectories();
            }
        }

        public function handleStartPerformanceClock($event)
        {
            Yii::app()->performance->startClock();
        }

        public function handleSetupDatabaseConnection($event)
        {
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password);
            if (!Yii::app()->isApplicationInstalled())
            {
                throw new NotSupportedException();
            }
        }

        public function handleLoadLanguage($event)
        {
            if (!ApiRequest::isApiRequest())
            {
                if (isset($_GET['lang']) && $_GET['lang'] != null)
                {
                    Yii::app()->languageHelper->setActive($_GET['lang']);
                }
            }
            else
            {
                if ($lang = Yii::app()->apiRequest->getLanguage())
                {
                    Yii::app()->languageHelper->setActive($lang);
                }
            }
            Yii::app()->languageHelper->load();
        }

        public function handleLoadTimeZone($event)
        {
            Yii::app()->timeZoneHelper->load();
        }

        public function handleCheckAndUpdateCurrencyRates($event)
        {
            Yii::app()->currencyHelper->checkAndUpdateCurrencyRates();
        }

        public function handleResolveCustomData($event)
        {
            if (isset($_GET['resolveCustomData']) && $_GET['resolveCustomData'] == 1)
            {
                Yii::app()->custom->resolveIsCustomDataLoaded();
            }
        }

        public function handleLoadActivitiesObserver($event)
        {
            $activitiesObserver = new ActivitiesObserver();
            $activitiesObserver->init();
        }

        public function handleLoadConversationsObserver($event)
        {
            $conversationsObserver = new ConversationsObserver();
            $conversationsObserver->init();
        }

        public function handleLoadEmailMessagesObserver($event)
        {
            $emailMessagesObserver = new EmailMessagesObserver();
            $emailMessagesObserver->init();
        }

        public function handleLoadWorkflowsObserver($event)
        {
            Yii::app()->workflowsObserver; //runs init();
        }

        public function handleLoadReadPermissionSubscriptionObserver($event)
        {
            Yii::app()->readPermissionSubscriptionObserver; // runs init()
        }

        public function handleLoadContactLatestActivityDateTimeObserver($event)
        {
            Yii::app()->contactLatestActivityDateTimeObserver;
        }

        public function handleLoadAccountLatestActivityDateTimeObserver($event)
        {
            Yii::app()->accountLatestActivityDateTimeObserver;
        }

        public function handleLoadAccountContactAffiliationObserver($event)
        {
            $accountContactAffiliationObserver = new AccountContactAffiliationObserver();
            $accountContactAffiliationObserver->init();
        }

        public function handleLoadGamification($event)
        {
            Yii::app()->gameHelper;
            Yii::app()->gamificationObserver; //runs init();
        }

        public function handleDisableGamification($event)
        {
            Yii::app()->gameHelper->enabled = false;
            Yii::app()->gamificationObserver->enabled = false;
        }

        public function handlePublishLogoAssets($event)
        {
            if (null !== ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                $logoFileModelId        = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId');
                $logoFileModel          = FileModel::getById($logoFileModelId);
                $logoFileSrc            = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias('application.runtime.uploads') .
                                                                                         DIRECTORY_SEPARATOR . $logoFileModel->name);
                //logoFile is either not published or we have dangling url for asset
                if ($logoFileSrc === false || file_exists($logoFileSrc) === false)
                {
                    //Logo file is not published in assets
                    //Check if it exists in runtime/uploads
                    if (file_exists(Yii::getPathOfAlias('application.runtime.uploads') .
                                                        DIRECTORY_SEPARATOR . $logoFileModel->name) === false)
                    {
                        $logoFilePath    = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $logoFileModel->name;
                        file_put_contents($logoFilePath, $logoFileModel->fileContent->content, LOCK_EX);
                        ZurmoUserInterfaceConfigurationFormAdapter::publishLogo($logoFileModel->name, $logoFilePath);
                    }
                    else
                    {
                        //Logo File exist in runtime/uploads but not published
                        Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.runtime.uploads') .
                                                               DIRECTORY_SEPARATOR . $logoFileModel->name);
                    }
                }
            }
        }

        /**
         * Get Allowed Guest User Routes
         * @return array
         */
        protected static function getAllowedGuestUserRoutes()
        {
            return self::$allowedGuestUserRoutes;
        }

        protected function clearCacheDirectories()
        {
            $cacheDirectories   = $this->resolveCacheDirectoryPaths();
            foreach ($cacheDirectories as $cacheDirectory)
            {
                $this->clearCacheDirectory($cacheDirectory);
            }
        }

        protected function clearCacheDirectory(array $cacheDirectory)
        {
            $excludedFiles          = array('index.html');
            $path                   = null;
            $removeDirectoryItself  = false;
            extract($cacheDirectory);
            if (is_dir($path))
            {
                FileUtil::deleteDirectoryRecursive($path, $removeDirectoryItself, $excludedFiles);
            }
        }

        protected function resolveCacheDirectoryPaths()
        {
            $cacheDirectories       = array(
                array(  'path'                  => Yii::app()->assetManager->getBasePath(),
                        'removeDirectoryItself' => false),
                array(  'path'                 => Yii::getPathOfAlias('application.runtime.themes'),
                        'removeDirectoryItself' => false),
                array(  'path'                 => Yii::getPathOfAlias('application.runtime.minscript.cache'),
                        'removeDirectoryItself' => false));
            return $cacheDirectories;
        }
     }
?>