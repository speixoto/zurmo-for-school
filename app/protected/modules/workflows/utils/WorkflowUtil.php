<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Helper class for working with Workflow objects
     */
    class WorkflowUtil
    {
        /**
         * @param $type
         * @return null | string
         */
        public static function renderNonEditableTypeStringContent($type)
        {
            assert('is_string($type)');
            $typesAndLabels = Workflow::getTypeDropDownArray();
            if(isset($typesAndLabels[$type]))
            {
                return $typesAndLabels[$type];
            }
        }

        /**
         * @param $moduleClassName
         * @return null | string
         */
        public static function renderNonEditableModuleStringContent($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            if(isset($modulesAndLabels[$moduleClassName]))
            {
                return $modulesAndLabels[$moduleClassName];
            }
        }

        public static function resolveDataAndLabelsForTimeTriggerAvailableAttributes($moduleClassName, $modelClassName,
                                                                                     $workflowType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $modelToWorkflowAdapter             = ModelRelationsAndAttributesToWorkflowAdapter::
                make($moduleClassName, $modelClassName, $workflowType);
            if(!$modelToWorkflowAdapter instanceof ModelRelationsAndAttributesToByTimeWorkflowAdapter)
            {
                throw new NotSupportedException();
            }
            $attributes     = $modelToWorkflowAdapter->getAttributesForTimeTrigger();
            $dataAndLabels  = array('' => Zurmo::t('Core', '(None)'));
            return array_merge($dataAndLabels, WorkflowUtil::renderDataAndLabelsFromAdaptedAttributes($attributes));
        }

        /**
         * Given an array of attributes generated from $modelToWorkflowAdapter->getAttributesForTimeTrigger()
         * return an array indexed by the attribute and the value is the label
         * @param array $attributes
         * @return array
         */
        public static function renderDataAndLabelsFromAdaptedAttributes($attributes)
        {
            assert('is_array($attributes)');
            $dataAndLabels = array();
            foreach($attributes as $attribute => $data)
            {
                $dataAndLabels[$attribute] = $data['label'];
            }
            return $dataAndLabels;
        }
    }
?>