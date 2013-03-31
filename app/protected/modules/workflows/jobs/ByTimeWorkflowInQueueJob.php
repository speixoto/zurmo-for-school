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
     * A job for processing expired By-Time workflow objects
     */
    class ByTimeWorkflowInQueueJob extends BaseJob
    {
        protected static $pageSize = 200;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('WorkflowsModule', 'Process by-time workflow rules');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'ByTimeWorkflowInQueue';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Every 15 minutes');
        }

        /**
         * @see BaseJob::run()
         */
        public function run()
        {
            $originalUser               = Yii::app()->user->userModel;
            Yii::app()->user->userModel = WorkflowUtil::getUserToRunWorkflowsAs();
            foreach (ByTimeWorkflowInQueue::getModelsToProcess(self::$pageSize) as $byTimeWorkflowInQueue)
            {
                try
                {
                    $model = $this->resolveModel($byTimeWorkflowInQueue);
                    $this->resolveSavedWorkflowIsValid($byTimeWorkflowInQueue);
                    $this->processByTimeWorkflowInQueue($byTimeWorkflowInQueue, $model);
                }
                catch (NotFoundException $e)
                {
                    WorkflowUtil::handleProcessingException($e,
                        'application.modules.workflows.jobs.ByTimeWorkflowInQueueJob.run');
                }
                $byTimeWorkflowInQueue->delete();
            }
            Yii::app()->user->userModel = $originalUser;
            return true;
        }

        protected function resolveModel(ByTimeWorkflowInQueue $byTimeWorkflowInQueue)
        {
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($byTimeWorkflowInQueue->modelClassName);
            return $byTimeWorkflowInQueue->modelItem->castDown(array($modelDerivationPathToItem));
        }

        protected function resolveSavedWorkflowIsValid(ByTimeWorkflowInQueue $byTimeWorkflowInQueue)
        {
            if($byTimeWorkflowInQueue->savedWorkflow->id < 0)
            {
                throw new NotFoundException();
            }
        }

        protected function processByTimeWorkflowInQueue(ByTimeWorkflowInQueue $byTimeWorkflowInQueue, RedBeanModel $model)
        {
            $workflow = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($byTimeWorkflowInQueue->savedWorkflow);
            if(!$workflow->getIsActive())
            {
                return;
            }
            $workflow->setTimeTriggerRequireChangeToProcessToFalse();
            if(WorkflowTriggersUtil::areTriggersTrueOnByTimeWorkflowQueueJob($workflow, $model))
            {
                WorkflowActionsUtil::processOnByTimeWorkflowInQueueJob($workflow, $model, Yii::app()->user->userModel);
                WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
                if($model->isModified())
                {
                    $saved = $model->save();
                    if(!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
        }
    }
?>