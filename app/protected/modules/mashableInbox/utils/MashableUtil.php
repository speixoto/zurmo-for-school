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

    class MashableUtil
    {
        /**
         * Create the MashableInboxRules for the model
         * @param string $modelClassName
         * @return MashableInboxRules
         */
        public static function createMashableInboxRulesByModel($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            $mashableInboxRulesType = $modelClassName::getMashableInboxRulesType();
            assert('$mashableInboxRulesType !== null');
            $mashableInboxRulesClassName = $mashableInboxRulesType . 'MashableInboxRules';
            $mashableInboxRules = new $mashableInboxRulesClassName();
            return $mashableInboxRules;
        }

        /**
         * @param String $interfaceClassName The name of the interface to check model implementation
         * @return Array Contains the modelClassNames of models that implements the interface
         * @param string $interfaceClassName
         * @param bool $includeHavingRelatedItems
         * @return array
         */
        public static function getModelDataForCurrentUserByInterfaceName($interfaceClassName, $includeHavingRelatedItems = true)
        {
            assert('is_string($interfaceClassName)');
            $interfaceModelClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $modelClassNames = $module::getModelClassNames();
                foreach ($modelClassNames as $modelClassName)
                {
                    $classToEvaluate     = new ReflectionClass($modelClassName);
                    if ($classToEvaluate->implementsInterface($interfaceClassName) &&
                    !$classToEvaluate->isAbstract())
                    {
                        if (RightsUtil::canUserAccessModule(get_class($module), Yii::app()->user->userModel))
                        {
                            if (!$includeHavingRelatedItems && !$modelClassName::hasRelatedItems())
                            {
                                continue;
                            }
                            $interfaceModelClassNames[$modelClassName] =
                                $modelClassName::getModelLabelByTypeAndLanguage('Plural');
                        }
                    }
                }
            }
            return $interfaceModelClassNames;
        }

        /**
         * @param string $modelClassName
         * @return int
         */
        public static function getUnreadCountForCurrentUserByModelClassName($modelClassName)
        {
            $mashableInboxRules = static::createMashableInboxRulesByModel($modelClassName);
            return (int)$mashableInboxRules->getUnreadCountForCurrentUser();
        }

        public static function getUnreadCountMashableInboxForCurrentUser()
        {
            $unreadCount = 0;
            $mashableInboxModels = static::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            foreach ($mashableInboxModels as $modelClassName => $modelLabel)
            {
                $unreadCount += static::getUnreadCountForCurrentUserByModelClassName($modelClassName);
            }
            return $unreadCount;
        }

        public static function renderUnreadCountForDynamicLabelContent()
        {
            $unreadCount = self::getUnreadCountMashableInboxForCurrentUser();
            return ZurmoHtml::wrapLabel($unreadCount, 'unread-inbox-count');
        }

        /**
         * @param array $modelClassNames
         * @param string $filteredBy
         * @param string $searchTerm
         * @return array
         */
        public static function getSearchAttributeMetadataForMashableInboxByModelClassName($modelClassNames, $filteredBy, $searchTerm = '')
        {
            $modelClassNamesAndSearchAttributeData = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules
                    = static::createMashableInboxRulesByModel($modelClassName);
                $metadataForMashableInbox
                    = $mashableActivityRules->getMetadataForMashableInbox();
                $searchAttributesData
                    = $mashableActivityRules->getSearchAttributeData($searchTerm);
                $metadataForMashableInboxAndSearch
                    = static::mergeMetadata($metadataForMashableInbox, $searchAttributesData);
                $metadataFilteredBy
                    = $mashableActivityRules->getMetadataFilteredByFilteredBy($filteredBy);
                $searchAttributesDataAndByFiltered
                    = static::mergeMetadata($metadataForMashableInboxAndSearch, $metadataFilteredBy);
                $modelClassNamesAndSearchAttributeData[]
                    = array($modelClassName => $searchAttributesDataAndByFiltered);
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        /**
         * @param array $modelClassNames
         * @return array
         */
        public static function getSortAttributesByMashableInboxModelClassNames($modelClassNames)
        {
            assert('is_array($modelClassNames)');
            $modelClassNamesAndSortAttributes = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules = static::createMashableInboxRulesByModel($modelClassName);
                $modelClassNamesAndSortAttributes[$modelClassName] =
                        $mashableActivityRules->getMachableInboxOrderByAttributeName();
            }
            return $modelClassNamesAndSortAttributes;
        }

        /**
         * @param RedBeanModel $model
         * @return string
         */
        public static function renderSummaryContent(RedBeanModel $model)
        {
            $mashableInboxRules                 = static::createMashableInboxRulesByModel(get_class($model));
            $summaryContentTemplate             = $mashableInboxRules->getSummaryContentTemplate();
            $data                               = array();
            $data['modelStringContent']         = static::renderModelStringContent($model, $mashableInboxRules);
            $data['modelCreationTimeContent']   = static::renderModelCreationTimeContent($model, $mashableInboxRules);
            $spanForTag                         = ZurmoHtml::tag(
                                                            'div',
                                                            array(
                                                                "class" => "model-tag " . strtolower($mashableInboxRules->getModelClassName())
                                                            ),
                                                            ZurmoHtml::tag('span', array(), $mashableInboxRules->getModelClassName()));
            $content = ZurmoHtml::tag('div', array('class' => 'inbox-item'), $spanForTag . self::resolveContentTemplate($summaryContentTemplate, $data));
            return $content;
        }

        protected static function renderModelStringContent(RedBeanModel $model, $mashableInboxRules)
        {
            return $mashableInboxRules->getModelStringContent($model);
        }

        protected static function renderModelCreationTimeContent(RedBeanModel $model, $mashableInboxRules)
        {
            return $mashableInboxRules->getModelCreationTimeContent($model);
        }

        /**
         * @param string $template
         * @param array $data
         * @return string
         */
        public static function resolveContentTemplate($template, $data)
        {
            assert('is_string($template)');
            assert('is_array($data)');
            $preparedContent = array();
            foreach ($data as $templateVar => $content)
            {
                $preparedContent["{" . $templateVar . "}"] = $content;
            }
            return strtr($template, $preparedContent);
        }

        /**
         * @param array $firstMetadata
         * @param array $secondMetadata
         * @param bool $isAnd
         * @return mixed
         */
        public static function mergeMetadata($firstMetadata, $secondMetadata, $isAnd = true)
        {
            if ($firstMetadata == null && $secondMetadata == null)
            {
                $metadata['clauses']    = array();
                $metadata['structure']  = null;
                return $metadata;
            }
            if ($firstMetadata == null)
            {
                return $secondMetadata;
            }
            if ($secondMetadata == null)
            {
                return $firstMetadata;
            }
            $firstMetadataClausesCount = count($firstMetadata['clauses']);
            foreach ($secondMetadata['clauses'] as $clauseNumber => $clause)
            {
                $firstMetadata['clauses'][$clauseNumber + $firstMetadataClausesCount] = $clause;
            }
            if ($isAnd)
            {
                $operator = ' and ';
            }
            else
            {
                $operator = ' or ';
            }
            $callback = function($match) use ($firstMetadataClausesCount)
            {
                return (($match[0] + $firstMetadataClausesCount));
            };
            $firstMetadata['structure'] = '(' . $firstMetadata['structure'] . ')' . $operator .
                '(' . preg_replace_callback("/(\\d+)/", $callback, $secondMetadata['structure']) . ')'; // Not Coding Standard
            return $firstMetadata;
        }

        /**
         * @param MashableInboxForm $mashableInboxForm
         * @param string $modelClassName
         */
        public static function saveSelectedOptionsAsStickyData(MashableInboxForm $mashableInboxForm, $modelClassName)
        {
            assert('strlen($modelClassName) > 0 || ($modelClassName === null)');
            $key = self::resolveKeyByModuleAndModel('MashableInboxModule', $modelClassName);
            StickyUtil::setDataByKeyAndData($key, $mashableInboxForm->getAttributes(
                                                        array('optionForModel', 'filteredBy', 'searchTerm')));
        }

        /**
         * @param string $modelClassName
         * @return MashableInboxForm
         */
        public static function restoreSelectedOptionsAsStickyData($modelClassName)
        {
            assert('strlen($modelClassName) > 0 || ($modelClassName === null)');
            $key  = self::resolveKeyByModuleAndModel('MashableInboxModule', $modelClassName);
            $data = StickyUtil::getDataByKey($key);
            $mashableInboxForm = new MashableInboxForm();
            $mashableInboxForm->attributes = $data;
            return $mashableInboxForm;
        }

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @return string
         */
        public static function resolveKeyByModuleAndModel($moduleClassName, $modelClassName)
        {
            assert('strlen($moduleClassName) > 0');
            if ($modelClassName == null)
            {
                $modelClassName = 'default';
            }
            return $moduleClassName . '_' . $modelClassName;
        }
    }
?>
