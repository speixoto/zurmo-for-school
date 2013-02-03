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

    /**
     * Application loaded component at run time.  @see BeginBehavior - calls load() method.
     */
    class ZurmoLanguageHelper extends CApplicationComponent
    {
        /**
         * The base language as defined by the config file. This language cannot be inactivated.
         * @var string
         */
        protected $baseLanguage;

        /**
         * Sets active language.
         */
        public function setActive($language)
        {
            assert('is_string($language)');
            Yii::app()->user->setState('language', $language);
            $this->flushModuleLabelTranslationParameters();
        }

        /**
         * Loads language for current user.  This is called by BeginBehavior. This will also copy the base language
         * into a parameter $baseLanguage in this class.
         */
        public function load()
        {
            $this->baseLanguage = Yii::app()->language;
            if (Yii::app()->user->userModel == null)
            {
                $language = $this->getForCurrentUser();
            }
            else
            {
                if (null == $language = Yii::app()->user->getState('language'))
                {
                    $language = $this->getForCurrentUser();
                    Yii::app()->user->setState('language', $language);
                    $this->flushModuleLabelTranslationParameters();
                }
            }
            Yii::app()->language = $language;
        }

        public function getBaseLanguage()
        {
            return $this->baseLanguage;
        }

        /**
         * For the current user, get the language setting.
         * The current user is specified here: Yii::app()->user->userModel
         * @return string - language.
         */
        public function getForCurrentUser()
        {
            if (Yii::app()->user->userModel != null && Yii::app()->user->userModel->language != null)
            {
                return Yii::app()->user->userModel->language;
            }
            return Yii::app()->language;
        }

        /**
         * Get supported languages and translates names of language. Uses language id as
         * key.
         * @param bool $localDisplay Display names of languages in local language?
         * @return array of language keys/ translated names.
         */
        public function getSupportedLanguagesData($localDisplay = false)
        {
            $data = array();
            foreach (Yii::app()->params['supportedLanguages'] as $language => $name)
            {
                if ($localDisplay)
                {
                    $data[$language] = Yii::app()->getLocale($language)->getLanguage($language);
                }
                else
                {
                    $data[$language] = Yii::app()->getLocale($this->getForCurrentUser())->getLanguage($language);
                }

                // In case the language name in local language is not available,
                // fallback to the base language for the language name.
                if (!isset($data[$language]))
                {
                    $data[$language] = Yii::app()->getLocale($this->baseLanguage)->getLanguage($language);
                }
            }
            return $data;
        }

        /**
         * Module translation parameters are used by Yii::t as the third parameter to define the module labels.  These
         * parameter values resolve any custom module label names that have been specified in the module metadata.
         * @return array of key/value module label pairings.
         * Caches results to improve performance.
         */
        public function getAllModuleLabelsAsTranslationParameters()
        {
            try
            {
                $moduleLabelTranslationParameters = GeneralCache::
                                                    getEntry('moduleLabelTranslationParameters' . Yii::app()->language);
                return $moduleLabelTranslationParameters;
            }
            catch (NotFoundException $e)
            {
                $modules = Module::getModuleObjects();
                $params  = array();
                foreach ($modules as $module)
                {
                    $params[get_class($module) . 'SingularLabel']
                        = $module::getModuleLabelByTypeAndLanguage('Singular', Yii::app()->language);
                    $params[get_class($module) . 'SingularLowerCaseLabel']
                        = $module::getModuleLabelByTypeAndLanguage('SingularLowerCase', Yii::app()->language);
                    $params[get_class($module) . 'PluralLabel']
                        = $module::getModuleLabelByTypeAndLanguage('Plural', Yii::app()->language);
                    $params[get_class($module) . 'PluralLowerCaseLabel']
                        = $module::getModuleLabelByTypeAndLanguage('PluralLowerCase', Yii::app()->language);
                }
                GeneralCache::cacheEntry('moduleLabelTranslationParameters' . Yii::app()->language, $params);
                return $params;
            }
        }

        /**
         * Used by tests to reset value between tests.
         */
        public function flushModuleLabelTranslationParameters()
        {
            foreach (Yii::app()->params['supportedLanguages'] as $language => $notUsed)
            {
                GeneralCache::forgetEntry('moduleLabelTranslationParameters' . $language);
            }
        }

        /**
         * Returns an array of active language data which includes the language as the index, and the translated
         * name of the language as the value.
         * @param bool $localDisplay Display names of languages in local language?
         */
        public function getActiveLanguagesData($localDisplay = false)
        {
            $supportedLanguages = $this->getSupportedLanguagesData($localDisplay);
            $activeLanguages    = $this->getActiveLanguages();

            foreach ($supportedLanguages as $language => $notUsed)
            {
                if (!in_array($language, $activeLanguages))
                {
                    unset($supportedLanguages[$language]);
                }
            }

            // Sort languages alphabetically
            ksort($supportedLanguages);

            return $supportedLanguages;
        }

        /**
         * A language that is the base language or currently selected as a user's default language, cannot be removed.
         * @return true if the specified language can be removed.
         */
        public function canInactivateLanguage($language)
        {
            assert('is_string($language)');
            if ($language == $this->baseLanguage || $this->isLanguageADefaultLanguageForAnyUsers($language))
            {
                return false;
            }
            return true;
        }

        /**
         * Given a language, returns true if the language is active, otherwise false.
         */
        public function getActiveLanguages()
        {
            $activeLanguages = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'activeLanguages');
            if ($activeLanguages == null)
            {
                $activeLanguages = array();
            }
            if (!in_array($this->baseLanguage, $activeLanguages))
            {
                $activeLanguages[] = $this->baseLanguage;
            }
            return $activeLanguages;
        }

        /**
         * Set the array of active languages that can be selected in the user interface by users.
         * @param array $activeLanguages
         */
        public function setActiveLanguages($activeLanguages)
        {
            assert('is_array($activeLanguages)');
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'activeLanguages', $activeLanguages);
        }

        /**
         * Given a language, is it in use as a default language by any of the users.
         * @param string $language
         * @return true if in use, otherwise returns false.
         */
        protected function isLanguageADefaultLanguageForAnyUsers($language)
        {
            assert('is_string($language)');
            $tableName = User::getTableName('User');
            $beans = R::find($tableName, "language = '$language'");
            if (count($beans) > 0)
            {
                return true;
            }
            return false;
        }
    }
?>