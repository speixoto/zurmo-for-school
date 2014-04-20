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

    class WorkflowTriggersUtilBaseTest extends WorkflowBaseTest
    {
        public static function getDependentTestModelClassNames()
        {
            return array('WorkflowModelTestItem');
        }

        public static function makeOnSaveWorkflowAndTriggerWithoutValueType($attributeIndexOrDerivedType, $operator,
                                                                            $value,
                                                                            $moduleClassName = 'WorkflowsTestModule',
                                                                            $modelClassName  = 'WorkflowModelTestItem',
                                                                            $secondValue     = null)
        {
            assert('is_string($attributeIndexOrDerivedType)'); // Not Coding Standard
            assert('is_string($operator)');                    // Not Coding Standard
            assert('is_string($moduleClassName)');             // Not Coding Standard
            assert('is_string($modelClassName)');              // Not Coding Standard
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            $trigger = new TriggerForWorkflowForm($moduleClassName, $modelClassName, $workflow->getType());
            $trigger->attributeIndexOrDerivedType = $attributeIndexOrDerivedType;
            $trigger->value                       = $value;
            $trigger->secondValue                 = $secondValue;
            $trigger->operator                    = $operator;
            $workflow->addTrigger($trigger);
            return $workflow;
        }

        public static function makeOnSaveWorkflowAndTimeTriggerWithoutValueType($attributeIndexOrDerivedType, $operator,
                                                                                $value,
                                                                                $durationInterval = 0,
                                                                                $moduleClassName   = 'WorkflowsTestModule',
                                                                                $modelClassName    = 'WorkflowModelTestItem',
                                                                                $secondValue       = null)
        {
            assert('is_string($attributeIndexOrDerivedType)'); // Not Coding Standard
            assert('is_string($operator)');                    // Not Coding Standard
            assert('is_int($durationInterval)');                // Not Coding Standard
            assert('is_string($moduleClassName)');             // Not Coding Standard
            assert('is_string($modelClassName)');              // Not Coding Standard
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setTriggersStructure('1');
            $trigger = new TimeTriggerForWorkflowForm($moduleClassName, $modelClassName, $workflow->getType());
            $trigger->attributeIndexOrDerivedType = $attributeIndexOrDerivedType;
            $trigger->value                       = $value;
            $trigger->secondValue                 = $secondValue;
            $trigger->operator                    = $operator;
            $trigger->durationInterval            = $durationInterval;
            $workflow->setTimeTrigger($trigger);
            return $workflow;
        }

        public static function makeOnSaveWorkflowAndTriggerForDateOrDateTime($attributeIndexOrDerivedType, $valueType,
                                                                                 $value,
                                                                                 $moduleClassName = 'WorkflowsTestModule',
                                                                                 $modelClassName  = 'WorkflowModelTestItem',
                                                                                 $secondValue     = null,
                                                                                 $thirdValueDurationInterval = null,
                                                                                 $thirdValueDurationType     = null)
        {
            assert('is_string($attributeIndexOrDerivedType)'); // Not Coding Standard
            assert('is_string($valueType)');                   // Not Coding Standard
            assert('is_string($moduleClassName)');             // Not Coding Standard
            assert('is_string($modelClassName)');              // Not Coding Standard
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            $trigger = new TriggerForWorkflowForm($moduleClassName, $modelClassName, $workflow->getType());
            $trigger->attributeIndexOrDerivedType = $attributeIndexOrDerivedType;
            $trigger->valueType                   = $valueType;
            $trigger->value                       = $value;
            $trigger->secondValue                 = $secondValue;
            $trigger->thirdValueDurationInterval  = $thirdValueDurationInterval;
            $trigger->thirdValueDurationType      = $thirdValueDurationType;
            $workflow->addTrigger($trigger);
            return $workflow;
        }

         public static function makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime($attributeIndexOrDerivedType, $valueType,
                                                                                 $value,
                                                                                 $durationInterval = 0,
                                                                                 $moduleClassName = 'WorkflowsTestModule',
                                                                                 $modelClassName  = 'WorkflowModelTestItem',
                                                                                 $secondValue     = null,
                                                                                 $durationSign    = TimeDurationUtil::DURATION_SIGN_POSITIVE,
                                                                                 $durationType    = TimeDurationUtil::DURATION_TYPE_DAY)
        {
            assert('is_string($attributeIndexOrDerivedType)'); // Not Coding Standard
            assert('is_string($valueType)');                   // Not Coding Standard
            assert('is_int($durationInterval)');                // Not Coding Standard
            assert('is_string($moduleClassName)');             // Not Coding Standard
            assert('is_string($modelClassName)');              // Not Coding Standard
            assert('is_string($durationSign)');                // Not Coding Standard
            assert('is_string($durationType)');                // Not Coding Standard
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setTriggersStructure('1');
            $trigger = new TimeTriggerForWorkflowForm($moduleClassName, $modelClassName, $workflow->getType());
            $trigger->attributeIndexOrDerivedType = $attributeIndexOrDerivedType;
            $trigger->valueType                   = $valueType;
            $trigger->value                       = $value;
            $trigger->secondValue                 = $secondValue;
            $trigger->durationInterval            = $durationInterval;
            $trigger->durationSign                = $durationSign;
            $trigger->durationType                = $durationType;
            $workflow->setTimeTrigger($trigger);
            return $workflow;
        }

        public static function saveAndReloadModel(RedBeanModel $model)
        {
            $saved = $model->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $modelId        = $model->id;
            $modelClassName = get_class($model);
            $model->forget();
            unset($model);
            return $modelClassName::getById($modelId);
        }
    }
?>