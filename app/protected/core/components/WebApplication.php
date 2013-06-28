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

    class WebApplication extends CWebApplication
    {
        /**
         * Label describing application
         * @var string
         */
        public $label;

        /**
         * If the application has been installed or not.
         * @var boolean
         */
        protected $installed;

        /**
         * @var string
         */
        protected $edition;

        /**
         * Is application in maintenance mode or not.
         * @var boolean
         */
        protected $maintenanceMode;

        /**
         * Override to handle when debug is turned on and the checksum fails on cached models.
         */
        public function run()
        {
            try
            {
                parent::run();
            }
            catch (ChecksumMismatchException $e)
            {
                echo Zurmo::t('Core', 'A checksum mismatch has occurred while retrieving a cached model. ' .
                                       'This is most likely caused by setting debug=true. The cache must be cleared.'); // Not Coding Standard
                echo '<br/>';
                exit;
                $url = Yii::app()->createUrl('zurmo/default/index/', array('clearCache' => true));
                echo ZurmoHtml::link('Click here to clear the cache', $url);
                Yii::app()->end(0, false);
            }
        }

        /**
         * Returns the locale instance.
         * This overrides the default CApplication->getLocale() function.
         * @param string $localeID the locale ID (e.g. en_US). If null, the {@link getLanguage application language ID} will be used.
         * @return CLocale the locale instance
         */
        public function getLocale($localeID = null)
        {
            if ($localeID == null && $this->user->userModel != null && $this->user->userModel->id > 0 &&
               $this->user->userModel->locale != null)
            {
                $localeID = $this->user->userModel->locale;
            }
            elseif ($localeID == null)
            {
                $localeID = $this->getLanguage();
            }
            return ZurmoLocale::getInstance($localeID);
        }

        /**
         * Override so that the application looks at the controller class name differently.
         * Instead of having controllers with the same class name across the application,
         * each class name must be different.
         * Each controller class name is expected to include the module class name as the
         * prefix to the controller class name.
         * Creates a controller instance based on a route.
         *
         */
        public function createController($route, $owner = null)
        {
            if ($owner === null)
            {
                $owner = $this;
            }
            if (($route = trim($route, '/')) === '')
            {
                $route = $owner->defaultController;
            }
            $caseSensitive = $this->getUrlManager()->caseSensitive;
            $route .= '/';
            while (($pos = strpos($route, '/')) !== false)
            {
                $id = substr($route, 0, $pos);
                if (!preg_match('/^\w+$/', $id)) // Not Coding Standard
                {
                    return null;
                }
                if (!$caseSensitive)
                {
                    $id = strtolower($id);
                }
                $route = (string)substr($route, $pos + 1);
                if (!isset($basePath))
                {
                    if (isset($owner->controllerMap[$id]))
                    {
                        return array(
                            Yii::createComponent(
                                    $owner->controllerMap[$id],
                                    $id,
                                    $this->resolveWhatToPassAsParameterForOwner($owner)),
                            $this->parseActionParams($route),
                        );
                    }

                    if (($module    = $owner->getModule($id)) !== null)
                    {
                        return $this->createController($route, $module);
                    }
                    $basePath      = $owner->getControllerPath();
                    $controllerID  = '';
                }
                else
                {
                    $controllerID .= '/';
                }

                $baseClassName = ucfirst($id) . 'Controller';
                //this assumes owner is the module, which i am not sure is always true...
                if ($this->isOwnerTheController($owner))
                {
                    $className     = $baseClassName;
                }
                else
                {
                    $className     = $owner::getPluralCamelCasedName() . $baseClassName;
                }
                $classFile     = $basePath . DIRECTORY_SEPARATOR   . $baseClassName . '.php';
                if (is_file($classFile))
                {
                    if (!class_exists($className, false))
                    {
                        require($classFile);
                    }
                    if (class_exists($className, false) && is_subclass_of($className, 'CController'))
                    {
                        $id[0] = strtolower($id[0]);
                        return array(
                            new $className($controllerID . $id, $this->resolveWhatToPassAsParameterForOwner($owner)),
                            $this->parseActionParams($route),
                        );
                    }
                    return null;
                }
                $controllerID .= $id;
                $basePath     .= DIRECTORY_SEPARATOR . $id;
            }
        }

        protected function resolveWhatToPassAsParameterForOwner($owner)
        {
            if ($owner === $this)
            {
                return null;
            }
            return $owner;
        }

        protected function isOwnerTheController($owner)
        {
            if ($owner === $this)
            {
                return true;
            }
            return false;
        }

        /**
         * Override to provide proper search of nested modules.
         */
        public function findModule($moduleID)
        {
            return self::findInModule(Yii::app(), $moduleID);
        }

        /**
         * Extra method so the findModule can be called statically from outside this class.
         * @param string $moduleID
         */
        public static function findModuleInApplication($moduleID)
        {
            return self::findInModule(Yii::app(), $moduleID);
        }

        /**
         * Recursively searches for module including nested modules.
         */
        private static function findInModule($parentModule, $moduleId)
        {
            if ($parentModule->getModule($moduleId))
            {
                return $parentModule->getModule($moduleId);
            }
            else
            {
                $modules = $parentModule->getModules();
                foreach ($modules as $module => $moduleConfiguration)
                {
                    $module = self::findInModule($parentModule->getModule($module), $moduleId);
                    if ($module)
                    {
                        return $module;
                    }
                }
            }
            return null;
        }

        public function isApplicationInstalled()
        {
            return $this->installed;
        }

        public function setApplicationInstalled($installed)
        {
            $this->installed = $installed;
            return true;
        }

        public function isApplicationInMaintenanceMode()
        {
            return $this->maintenanceMode;
        }

        public function getEdition()
        {
            return $this->edition;
        }
    }
?>