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
     * Helper class for working with Workflow objects and processing the triggers against a model
     */
    class WorkflowTriggersUtil
    {
        /**
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @return bool|void
         * @throws NotSupportedException
         */
        public static function areTriggersTrueBeforeSave(Workflow $workflow, RedBeanModel $model)
        {
            if($workflow->getType() == Workflow::TYPE_BY_TIME)
            {
                return self::resolveByTimeTriggerIsTrueBeforeSave($workflow, $model);
            }
            elseif($workflow->getType() == Workflow::TYPE_ON_SAVE)
            {
                return self::resolveTriggersAreTrueBeforeSave($workflow, $model);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Utilized during @see ByTimeWorkflowQueueJob to process workflows that are by-time
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @return bool|void
         * @throws NotSupportedException
         */
        public static function areTriggersTrueOnByTimeWorkflowQueueJob(Workflow $workflow, RedBeanModel $model)
        {
            if($workflow->getType() == Workflow::TYPE_BY_TIME)
            {
                if(self::resolveTimeTriggerIsTrueBeforeSave($workflow, $model))
                {
                    return self::resolveTriggersAreTrueBeforeSave($workflow, $model);
                }
                return false;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param $triggersStructure
         * @return mixed
         */
        public static function resolveStructureToPHPString($triggersStructure)
        {
            assert('is_string($triggersStructure)');
            $resolvedStructure = str_replace('and', '&&', strtolower($triggersStructure));
            return               str_replace('or', '||',  strtolower($resolvedStructure));
        }

        /**
         * @param $structureAsPHPString
         * @param array $dataToEvaluate
         * @return mixed
         * @throws NotSupportedException
         */
        public static function resolveBooleansDataToPHPString($structureAsPHPString, Array $dataToEvaluate)
        {
            assert('is_string($structureAsPHPString)');
            $evaluatedString = $structureAsPHPString;
            foreach($dataToEvaluate as $key => $boolean)
            {
                if(!is_bool($boolean))
                {
                    throw new NotSupportedException();
                }
                $evaluatedString = str_replace($key, BooleanUtil::boolToString($boolean), strtolower($evaluatedString));
            }
            return $evaluatedString;
        }

        /**
         * Public for testing purposes only
         * @param $phpStringReadyToEvaluate
         */
        public static function evaluatePHPString($phpStringReadyToEvaluate)
        {
            //todo: add final safety that ther is just &&, ||, (, ), and 'true' and 'false.
            return eval('return (' . $phpStringReadyToEvaluate. ');');
        }

        protected static function resolveTimeTriggerIsTrueBeforeSave(Workflow $workflow, RedBeanModel $model)
        {
            if(count($workflow->getTimeTrigger()) != 1)
            {
                throw new NotSupportedException();
            }
            return self::isTriggerTrueByModel($workflow, $workflow->getTimeTrigger(), $model);
        }

        /**
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @return bool|void
         */
        protected static function resolveTriggersAreTrueBeforeSave(Workflow $workflow, RedBeanModel $model)
        {
            if(count($workflow->getTriggers()) == 0)
            {
                return true;
            }
            $structureAsPHPString = WorkflowTriggersUtil::resolveStructureToPHPString($workflow->getTriggersStructure());
            $dataToEvaluate       = array();
            $count                = 0;
            foreach($workflow->getTriggers() as $trigger)
            {
                $dataToEvaluate[$count + 1] = self::isTriggerTrueByModel($workflow, $trigger, $model);
                $count ++;
            }
            $phpStringReadyToEvaluate = WorkflowTriggersUtil::resolveBooleansDataToPHPString(
                                        $structureAsPHPString, $dataToEvaluate);
            return WorkflowTriggersUtil::evaluatePHPString($phpStringReadyToEvaluate);
        }

        /**
         * First check the time trigger specifically. In the case of date/dateTime, it should just check if the
         * value has 'changed'.  For other attributes, it should check if the value has 'changed' and mactches
         * the condition of the time trigger.  If the time trigger is true, then it will evaluate the rest of the
         * triggers.
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @return bool|void
         */
        protected static function resolveByTimeTriggerIsTrueBeforeSave(Workflow $workflow, RedBeanModel $model)
        {
            if(self::resolveTimeTriggerIsTrueBeforeSave($workflow, $model))
            {
                return self::resolveTriggersAreTrueBeforeSave($workflow, $model);
            }
            return false;
        }

        protected static function isTriggerTrueByModel(Workflow $workflow, TriggerForWorkflowForm $trigger, RedBeanModel $model)
        {
            if($trigger->getAttribute() == null)
            {
                $attributeAndRelationData = $trigger->getAttributeAndRelationData();
                if(count($attributeAndRelationData) == 2)
                {
                    $penultimateRelation = $trigger->getPenultimateRelation();
                    $resolvedAttribute   = $trigger->getResolvedAttributeRealAttributeName();
                    if($model->$penultimateRelation instanceof RedBeanMutableRelatedModels)
                    {
                        //ManyMany or HasMany
                        foreach($model->{$penultimateRelation} as $resolvedModel)
                        {
                            if(self::resolveIsTrueByEvaluationRules($workflow, $trigger, $resolvedModel, $resolvedAttribute) &&
                               $trigger->relationFilter == TriggerForWorkflowForm::RELATION_FILTER_ANY)
                            {
                                return true;
                            }
                        }
                        return false;
                    }
                    else
                    {
                        $resolvedModel       = $model->{$penultimateRelation};
                        return self::resolveIsTrueByEvaluationRules($workflow, $trigger, $resolvedModel, $resolvedAttribute);
                    }
                }
                elseif(count($attributeAndRelationData) == 3)
                {
                    $firstRelation       = $trigger->getResolvedRealAttributeNameForFirstRelation();
                    $resolvedAttribute   = $trigger->getResolvedAttributeRealAttributeName();
                    $penultimateRelation = $trigger->getResolvedRealAttributeNameForPenultimateRelation();
                    if($model->{$firstRelation} instanceof RedBeanMutableRelatedModels)
                    {
                        //ManyMany or HasMany
                        foreach($model->{$firstRelation} as $relatedModel)
                        {
                            $resolvedModel  = $relatedModel->{$penultimateRelation};
                            if(self::resolveIsTrueByEvaluationRules($workflow, $trigger,
                                                                    $resolvedModel,
                                                                    $resolvedAttribute) &&
                                $trigger->relationFilter == TriggerForWorkflowForm::RELATION_FILTER_ANY)
                            {
                                return true;
                            }
                        }
                        return false;
                    }
                    else
                    {
                        $relatedModel        = $model->{$firstRelation};
                        $resolvedModel       = $relatedModel->{$penultimateRelation};
                        return self::resolveIsTrueByEvaluationRules($workflow, $trigger, $resolvedModel, $resolvedAttribute);
                    }
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                $attribute     = $trigger->getResolvedAttributeRealAttributeName();
                $resolvedModel = $model;
                return self::resolveIsTrueByEvaluationRules($workflow, $trigger, $resolvedModel, $attribute);
            }
        }

        protected static function resolveIsTrueByEvaluationRules(Workflow $workflow, TriggerForWorkflowForm $trigger,
                                                                 RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            if($workflow->getType() == Workflow::TYPE_BY_TIME)
            {
                return $triggerRules->evaluateTimeTriggerBeforeSave($model, $attribute,
                       $workflow->doesTimeTriggerRequireChangeToProcess());
            }
            elseif($workflow->getType() == Workflow::TYPE_ON_SAVE)
            {
                return $triggerRules->evaluateBeforeSave($model, $attribute);
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>