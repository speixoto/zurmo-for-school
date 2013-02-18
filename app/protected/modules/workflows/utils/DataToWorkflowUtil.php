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

    class DataToWorkflowUtil
    {
        public static function resolveWorkflowByWizardPostData(Workflow $workflow, $postData, $wizardFormClassName)
        {
            assert('is_array($postData)');
            $data = ArrayUtil::getArrayValue($postData, $wizardFormClassName);
            if(isset($data['description']))
            {
                $workflow->setDescription($data['description']);
            }
            if(isset($data['moduleClassName']))
            {
                $workflow->setModuleClassName($data['moduleClassName']);
            }
            if(isset($data['name']))
            {
                $workflow->setName($data['name']);
            }
            if(isset($data['triggersStructure']))
            {
                $workflow->setTriggersStructure($data['triggersStructure']);
            }
            self::resolveTriggers                   ($data, $workflow);
            self::resolveActions                    ($data, $workflow);
            self::resolveTimeTrigger                ($data, $workflow);
        }

        public static function resolveTriggers($data, Workflow $workflow)
        {
            $workflow->removeAllTriggers();
            $moduleClassName = $workflow->getModuleClassName();
            if(count($triggersData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_TRIGGERS)) > 0)
            {
                $sanitizedTriggersData = self::sanitizeTriggersData($moduleClassName, $workflow->getType(), $triggersData);
                foreach($sanitizedTriggersData as $triggerData)
                {
                    $trigger = new TriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                          $workflow->getType());
                    $trigger->setAttributes($triggersData);
                    $workflow->addTrigger($trigger);
                }
            }
            else
            {
                $workflow->removeAllTriggers();
            }
        }

        public static function sanitizeTriggersData($moduleClassName, $workflowType, array $triggersData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($workflowType)');
            $sanitizedTriggersData = array();
            foreach($triggersData as $triggerData)
            {
                $sanitizedTriggersData[] = static::sanitizeTriggerData($moduleClassName,
                                                                     $moduleClassName::getPrimaryModelName(),
                                                                     $workflowType,
                                                                     $triggerData);
            }
            return $sanitizedTriggersData;
        }

        protected static function sanitizeTriggerData($moduleClassName, $modelClassName, $workflowType, $triggerData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            assert('is_array($triggerData)');
            $triggerForSanitizing = new TriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                               $workflowType);

            $triggerForSanitizing->setAttributes($triggerData);
            $valueElementType = null;
            $valueElementType    = $triggerForSanitizing->getValueElementType();
            if($valueElementType == 'MixedDateTypesForWorkflow')
            {
                if(isset($triggerData['value']) && $triggerData['value'] !== null)
                {
                    $triggerData['value']       = DateTimeUtil::resolveValueForDateDBFormatted($triggerData['value']);
                }
                if(isset($triggerData['secondValue']) && $triggerData['secondValue'] !== null)
                {
                    $triggerData['secondValue'] = DateTimeUtil::resolveValueForDateDBFormatted($triggerData['secondValue']);
                }
            }
            return $triggerData;
        }

        /**
         * Public for testing purposes
         * @param $data
         * @param Workflow $workflow
         */
        public static function resolveActions($data, Workflow $workflow)
        {
            //todo: we need to sanitize action data too since we can be populating things like static dates..
            $workflow->removeAllActions();
            $moduleClassName = $workflow->getModuleClassName();
            if(count($actionsData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_ACTIONS)) > 0)
            {
                foreach($actionsData as $actionData)
                {
                    $action = new ActionForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                        $workflow->getType());
                    $action->setAttributes($actionData);
                    $workflow->addAction($action);
                }
            }
            else
            {
                $workflow->removeAllActions();
            }
        }

        protected static function resolveTimeTrigger($data, Workflow $workflow)
        {
            if($workflow->getType() != Workflow::TYPE_BY_TIME)
            {
                return;
            }
            $timeTrigger             = new TimeTriggerForWorkflowForm();
            if(null != $timeTriggerData = ArrayUtil::getArrayValue($data, 'TimeTriggerForWorkflowForm'))
            {
                $timeTrigger->setAttributes($timeTriggerData);
            }
            $workflow->setTimeTrigger($timeTrigger);
        }
    }
?>