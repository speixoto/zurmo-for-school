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

    class MashableUtil
    {

        /**
         * Create the MashableInboxRules for the model
         * @param type $modelClassName
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

        public static function getUnreadCountForCurrentUserByModelClassName($modelClassName)
        {
            $mashableInboxRules =
                    static::createMashableInboxRulesByModel($modelClassName);
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
                    = static::mergeMetada($metadataForMashableInbox, $searchAttributesData);
                $metadataFilteredBy
                    = $mashableActivityRules->getMetadataFilteredByFilteredBy($filteredBy);
                $searchAttributesDataAndByFiltered
                    = static::mergeMetada($metadataForMashableInboxAndSearch, $metadataFilteredBy);
                $modelClassNamesAndSearchAttributeData[]
                    = array($modelClassName => $searchAttributesDataAndByFiltered);
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        public static function getSortAttributesByMashableInboxModelClassNames($modelClassNames)
        {
            assert('is_array($modelClassNames)');
            $modelClassNamesAndSortAttributes = array();
            foreach ($modelClassNames as $modelClassName)
            {
                $mashableActivityRules =
                        static::createMashableInboxRulesByModel($modelClassName);
                $modelClassNamesAndSortAttributes[$modelClassName] =
                        $mashableActivityRules->getMachableInboxOrderByAttributeName();
            }
            return $modelClassNamesAndSortAttributes;
        }

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

            $content  = self::resolveContentTemplate($summaryContentTemplate, $data);
            $content .= $spanForTag;
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

        public static function getTimeSinceLatestUpdate($latestDateTime)
        {
            $nowTimestamp           = time();
            $lastUpdatedTimestamp   = DateTimeUtil::convertDbFormatDateTimeToTimestamp($latestDateTime);
            $timeSinceLatestUpdate  = $nowTimestamp - $lastUpdatedTimestamp;
            $timeForString = array(
                    'days'  => floor($timeSinceLatestUpdate / 86400),
                    'hours' => floor($timeSinceLatestUpdate / 3600),
                );
            if ($timeForString['days'] == 0)
            {
                if ($timeForString['hours'] == 1)
                {
                    $string = Zurmo::t('MashableInboxModule', '{hours} hour ago', array('{hours}' => $timeForString['hours']));
                }
                else
                {
                    $string = Zurmo::t('MashableInboxModule', '{hours} hours ago', array('{hours}' => $timeForString['hours']));
                }
            }
            else if (($timeForString['days'] == 1))
            {
                $string = Zurmo::t('MashableInboxModule', '{days} day ago', array('{days}' => $timeForString['days']));
            }
            else
            {
                $string = Zurmo::t('MashableInboxModule', '{days} days ago', array('{days}' => $timeForString['days']));
            }
            return $string;
        }

        public static function mergeMetada($firstMetadata, $secondMetadata, $isAnd = true)
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
            $clauseNumber = count($firstMetadata['clauses']) + 1;
            foreach ($secondMetadata['clauses'] as $clause)
            {
                $patterns[]     = '/' . ($clauseNumber - $firstMetadataClausesCount). '/';
                $replacements[] = (string)$clauseNumber;
                $firstMetadata['clauses'][$clauseNumber++] = $clause;
            }
            if ($isAnd)
            {
                $operator = ' and ';
            }
            else
            {
                $operator = ' or ';
            }
            $firstMetadata['structure'] = '(' . $firstMetadata['structure'] . ')' . $operator .
                                          '(' . preg_replace($patterns, $replacements, $secondMetadata['structure']) . ')';
            return $firstMetadata;
        }
    }
?>
