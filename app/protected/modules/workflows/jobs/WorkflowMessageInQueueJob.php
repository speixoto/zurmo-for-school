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
     * A job for processing workflow messages that are not sent immediately when triggered
     */
    class WorkflowMessageInQueueJob extends BaseJob
    {
        protected static $pageSize = 200;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('WorkflowsModule', 'Process workflow messages');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'WorkflowMessageInQueue';
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
            foreach (WorkflowMessageInQueue::getModelsToProcess(self::$pageSize) as $workflowMessageInQueue)
            {
                try
                {
                    $model = $this->resolveModel($workflowMessageInQueue);
                    $this->resolveSavedWorkflowIsValid($workflowMessageInQueue);
                    $this->processWorkflowMessageInQueue($workflowMessageInQueue, $model);
                }
                catch (NotFoundException $e)
                {
                }
                $workflowMessageInQueue->delete();
            }
            return true;
        }

        protected function resolveModel(WorkflowMessageInQueue $workflowMessageInQueue)
        {
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($workflowMessageInQueue->modelClassName);
            return $workflowMessageInQueue->modelItem->castDown(array($modelDerivationPathToItem));
        }

        protected function resolveSavedWorkflowIsValid(WorkflowMessageInQueue $workflowMessageInQueue)
        {
            if($workflowMessageInQueue->savedWorkflow->id < 0)
            {
                throw new NotFoundException();
            }
        }

        protected function processWorkflowMessageInQueue(WorkflowMessageInQueue $workflowMessageInQueue, RedBeanModel $model)
        {
            $workflow = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($workflowMessageInQueue->savedWorkflow);
            if(!$workflow->getIsActive())
            {
                return;
            }
            WorkflowEmailMessagesUtil::processOnWorkflowMessageInQueueJob($workflow, $model,
                                       self::resolveTriggeredByUser($workflowMessageInQueue));
        }

        protected static function resolveTriggeredByUser(WorkflowMessageInQueue $workflowMessageInQueue)
        {
            if($workflowMessageInQueue->triggeredByUser->id < 0)
            {
                return Yii::app()->user->userModel;
            }
            return $workflowMessageInQueue->triggeredByUser;
        }
    }
?>