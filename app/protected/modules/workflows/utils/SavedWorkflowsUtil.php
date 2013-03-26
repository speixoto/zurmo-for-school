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
     * Helper class for working with SavedWorkflow objects
     */
    class SavedWorkflowsUtil
    {
        /**
         * Given an array of moduleClassNames, construct the searchAttributeData
         * @param $searchAttributeData
         * @param $moduleClassNames
         * @return array
         */
        public static function resolveSearchAttributeDataByModuleClassNames($searchAttributeData, $moduleClassNames)
        {
            assert('is_array($searchAttributeData)');
            assert('is_array($moduleClassNames)');
            $clausesCount = count($searchAttributeData['clauses']);
            $clauseStructure = null;

            if(count($moduleClassNames) == 0)
            {
                $searchAttributeData['clauses'][$clausesCount + 1] = array(
                    'attributeName'        => 'moduleClassName',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                );
                $clauseStructure =  ($clausesCount + 1);
            }
            else
            {
                foreach ($moduleClassNames as $moduleClassName)
                {
                    $searchAttributeData['clauses'][$clausesCount + 1] = array(
                        'attributeName'        => 'moduleClassName',
                        'operatorType'         => 'equals',
                        'value'                => $moduleClassName
                    );
                    if ($clauseStructure != null)
                    {
                        $clauseStructure .= ' or ';
                    }
                    $clauseStructure .=  ($clausesCount + 1);
                    $clausesCount ++;
                }
            }

            if ($searchAttributeData['structure'] != null)
            {
                $searchAttributeData['structure'] .= ' and ';
            }
            $searchAttributeData['structure'] .=  "(" . $clauseStructure . ")";
            return $searchAttributeData;
        }

        /**
         * Resolve the correct order for a savedWorkflow. If it is a new savedWorkflow then set the order to max
         * plus 1.  'Max' is a calculation of the existing workflows that are for the specific moduleClassName.
         * If the workflow is an existing workflow, then if moduleClassName has changed, the 'max' plus 1 should be
         * used.  Otherwise if it is new and the moduleClassName has not changed, then leave it alone
         * @param SavedWorkflow $savedWorkflow
         * @throws NotSupportedException if the moduleClassName has not been defined yet
         */
        public static function resolveOrder(SavedWorkflow $savedWorkflow)
        {
            if($savedWorkflow->moduleClassName == null)
            {
                throw new NotSupportedException();
            }
            $q   = DatabaseCompatibilityUtil::getQuote();
            $sql = "select max({$q}order{$q}) maxorder from " . SavedWorkflow::getTableName('SavedWorkflow');
            $sql .= " where moduleclassname = '" . $savedWorkflow->moduleClassName . "'";
            if($savedWorkflow->id < 0 || array_key_exists('moduleClassName', $savedWorkflow->originalAttributeValues))
            {
                $maxOrder             = R::getCell($sql);
                $savedWorkflow->order = (int)$maxOrder +  1;
            }
        }

        /**
         * Given a RedBeanModel, query workflow rules and process any beforeSave triggers for either on-save or
         * by-time workflows.  Called from @see WokflowsObserver->processWorkflowBeforeSave
         * @param Item $model
         * @throws NotSupportedException if the workflow type is not valid
         */
        public static function resolveBeforeSaveByModel(Item $model)
        {
            $savedWorkflows = SavedWorkflow::getActiveByModuleClassNameAndIsNewModel(
                                             $model::getModuleClassName(), $model->isNewModel);
            foreach($savedWorkflows as $savedWorkflow)
            {
                $workflow = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
                if(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model))
                {
                    if($workflow->getType() == Workflow::TYPE_BY_TIME)
                    {
                        $model->addWorkflowToProcessAfterSave($workflow);

                    }
                    elseif($workflow->getType() == Workflow::TYPE_ON_SAVE)
                    {
                        WorkflowActionsUtil::processBeforeSave($workflow, $model);
                        $model->addWorkflowToProcessAfterSave($workflow);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
        }

        /**
         * Given a RedBeanModel, process afterSave actions such as update related, create, and create related.
         * Also process any email messages.  If the workflow is by-time, then we should process the ByTimeWorkflowInQueue
         * model.
         * @param Item $model
         * @throws NotSupportedException
         */
        public static function resolveAfterSaveByModel(Item $model)
        {
            foreach($model->getWorkflowsToProcessAfterSave() as $workflow)
            {
                if($workflow->getType() == Workflow::TYPE_BY_TIME)
                {
                    static::processToByTimeWorkflowInQueue($workflow, $model);
                }
                elseif($workflow->getType() == Workflow::TYPE_ON_SAVE)
                {
                    WorkflowActionsUtil::processAfterSave($workflow, $model);
                    WorkflowEmailMessagesUtil::processAfterSave($workflow, $model);
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
        }

        /**
         * Public for testing purposes only
         * @param Workflow $workflow
         * @param RedBeanModel $model
         */
        public static function resolveProcessDateTimeByWorkflowAndModel(Workflow $workflow, RedBeanModel $model)
        {
            $workflow->getTimeTrigger()->durationSeconds;
            $valueEvaluationType = $workflow->getTimeTrigger()->getValueEvaluationType();
            if($valueEvaluationType == 'Date')
            {
                $timeStamp = static::resolveTimeStampForDateAttributeForProcessDateTime($workflow->getTimeTrigger(), $model);
            }
            elseif($valueEvaluationType == 'DateTime')
            {
                $timeStamp = static::resolveTimeStampForDateTimeAttributeForProcessDateTime($workflow->getTimeTrigger(), $model);
            }
            else
            {
                $timeStamp = time() + $workflow->getTimeTrigger()->durationSeconds;
            }
            return DateTimeUtil::convertTimestampToDbFormatDateTime($timeStamp);
        }

        protected static function resolveTimeStampForDateAttributeForProcessDateTime(TimeTriggerForWorkflowForm $trigger,
                                                                                     RedBeanModel $model)
        {
            $date = static::resolveModelValueByTimeTrigger($trigger, $model);
            if(DateTimeUtil::isDateStringNull($date))
            {
                throw new ValueForProcessDateTimeIsNullException();
            }
            else
            {
                return DateTimeUtil::convertDbFormatDateTimeToTimestamp(DateTimeUtil::resolveDateAsDateTime($date)) +
                        $trigger->durationSeconds;
            }
        }

        protected static function resolveTimeStampForDateTimeAttributeForProcessDateTime(TimeTriggerForWorkflowForm $trigger,
                                                                                         RedBeanModel $model)
        {
            $dateTime = static::resolveModelValueByTimeTrigger($trigger, $model);
            if(DateTimeUtil::isDateTimeStringNull($dateTime))
            {
                throw new ValueForProcessDateTimeIsNullException();
            }
            else
            {
                return DateTimeUtil::convertDbFormatDateTimeToTimestamp($dateTime) + $trigger->durationSeconds;
            }
        }


        protected static function resolveModelValueByTimeTrigger(TimeTriggerForWorkflowForm $trigger, RedBeanModel $model)
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
                        throw new NotSupportedException();
                    }
                    else
                    {
                        $resolvedModel       = $model->{$penultimateRelation};
                        return $resolvedModel->{$resolvedAttribute};
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
                return $model->{$attribute};
            }
        }


        /**
         * @param Workflow $workflow
         * @param RedBeanModel $model
         * @throws FailedToSaveModelException
         */
        protected static function processToByTimeWorkflowInQueue(Workflow $workflow, RedBeanModel $model)
        {
            assert('$workflow->getId() > 0');
            try
            {
                //todo: are we sure want to then retrieve the savedWorkflow again? i guess it would be cached...
                $byTimeWorkflowInQueue = ByTimeWorkflowInQueue::
                    resolveByWorkflowIdAndModel(SavedWorkflow::getById((int)$workflow->getId()), $model);
                $byTimeWorkflowInQueue->processDateTime = static::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
                $saved                 = $byTimeWorkflowInQueue->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
            catch(ValueForProcessDateTimeIsNullException $e)
            {
                //todo: for now do nothing, but this means a date or dateTime somehow was set to empty, so we can't
                //properly process this. so we just don't create or update it.
            }
        }
    }
?>