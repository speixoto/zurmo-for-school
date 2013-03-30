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

    class WorkflowTestHelper
    {
        public static function createWorkflowModelTestItem($lastName, $string)
        {
            assert('is_string($lastName)');
            assert('is_string($string)');
            $model = new WorkflowModelTestItem();
            $model->lastName = $lastName;
            $model->string   = $string;
            $saved = $model->save();
            assert($saved); //Not Coding Standard
            return $model;
        }

        public static function createByTimeSavedWorkflow(Array $timeTrigger = array(), Array $triggers = array(),
                                                         Array $actions = array(), Array $messages = array())
        {
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'WorkflowsTestModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_BY_TIME;
            $data[ComponentForWorkflowForm::TYPE_TIME_TRIGGER]   = $timeTrigger;
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS]       = $triggers;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS]        = $actions;
            $data[ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES] = $messages;
            $savedWorkflow->serializedData  = serialize($data);
            $savedWorkflow->isActive        = true;
            $saved                          = $savedWorkflow->save();
            assert($saved); //Not Coding Standard
            return $savedWorkflow;
        }

        public static function createExpiredByTimeWorkflowInQueue(RedBeanModel $model, SavedWorkflow $savedWorkflow)
        {
            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueue();
            $byTimeWorkflowInQueue->modelClassName  = get_class($model);
            $byTimeWorkflowInQueue->modelItem       = $model;
            $byTimeWorkflowInQueue->processDateTime = '2007-02-02 00:00:00';
            $byTimeWorkflowInQueue->savedWorkflow   = $savedWorkflow;
            $saved = $byTimeWorkflowInQueue->save();
            assert($saved); //Not Coding Standard
            return $byTimeWorkflowInQueue;
        }


    }
?>