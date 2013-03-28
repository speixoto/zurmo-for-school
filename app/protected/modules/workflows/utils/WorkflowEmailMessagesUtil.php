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
     * Helper class for working with Workflow objects and processing the email messages that are triggered on a model
     */
    class WorkflowEmailMessagesUtil
    {
        public static function processAfterSave(Workflow $workflow, RedBeanModel $model, User $triggeredByUser)
        {
            foreach($workflow->getEmailAlerts() as $emailMessage)
            {
                try
                {
                    if($emailMessage->getEmailAlertRecipientFormsCount() > 0)
                    {
                        self::processEmailMessageAfterSave($workflow, $emailMessage, $model, $triggeredByUser);
                    }
                }
                catch(Exception $e)
                {
                    //todo: what to do?
                }
            }
        }

        public static function processOnWorkflowMessageInQueueJob(Workflow $workflow, RedBeanModel $model, User $triggeredByUser)
        {
            foreach($workflow->getEmailAlerts() as $emailMessage)
            {
                try
                {
                    if($emailMessage->getEmailAlertRecipientFormsCount() > 0)
                    {
                        $helper = new WorkflowEmailMessageProcessingHelper($emailMessage, $model, $triggeredByUser);
                        $helper->process();
                    }
                }
                catch(Exception $e)
                {
                    //todo: what to do?
                }
            }
        }

        protected static function processEmailMessageAfterSave(Workflow $workflow,
                                                               EmailAlertForWorkflowForm $emailMessage,
                                                               RedBeanModel $model,
                                                               User $triggeredByUser)
        {
            if($emailMessage->sendAfterDurationSeconds == 0)
            {
                $helper = new WorkflowEmailMessageProcessingHelper($emailMessage, $model, $triggeredByUser);
                $helper->process();
            }
            else
            {
                $emailMessageData                        = SavedWorkflowToWorkflowAdapter::
                                                           makeArrayFromEmailAlertForWorkflowFormAttributesData(array($emailMessage));
                $workflowMessageInQueue                  = new WorkflowMessageInQueue();
                $workflowMessageInQueue->processDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() +
                                                           $emailMessage->sendAfterDurationSeconds);
                $workflowMessageInQueue->savedWorkflow   = SavedWorkflow::getById((int)$workflow->getId());
                $workflowMessageInQueue->modelClassName  = get_class($model);
                $workflowMessageInQueue->modelItem       = $model;
                $workflowMessageInQueue->serializedData  = serialize($emailMessageData);
                $workflowMessageInQueue->triggeredByUser = $triggeredByUser;
                $saved                                   = $workflowMessageInQueue->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
        }
    }
?>