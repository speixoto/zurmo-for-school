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

        public static function createMashableInboxRulesByModel($modelClassName)
        {
            assert('is_string($modelClassName)');
            $mashableInboxRulesType = $modelClassName::getMashableInboxRulesType();
            assert('$mashableInboxRulesType !== null');
            $mashableInboxRulesClassName = $mashableInboxRulesType . 'MashableInboxRules';
            return new $mashableInboxRulesClassName();
        }

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
                    MashableUtil::createMashableInboxRulesByModel($modelClassName);
            return $mashableInboxRules->getUnreadCountForCurrentUser();
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
    }
?>
