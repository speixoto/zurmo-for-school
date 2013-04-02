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

    class WorkflowMessageInQueueJobTest extends WorkflowBaseTest
    {
        public function testRun()
        {
            $model       = WorkflowTestHelper::createWorkflowModelTestItem('Green', '514');
            $timeTrigger = array('attributeIndexOrDerivedType' => 'string',
                                 'operator'                    => OperatorRules::TYPE_EQUALS,
                                 'value'                       => '514',
                                 'durationSeconds'             => '333');
            $actions     = array(array('type' => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                       ActionForWorkflowForm::ACTION_ATTRIBUTES =>
                                            array('string' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'))));
            $savedWorkflow         = WorkflowTestHelper::createByTimeSavedWorkflow($timeTrigger, array(), $actions);
            WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow);

            $this->assertEquals(1, count(WorkflowMessageInQueue::getAll()));
            $job = new WorkflowMessageInQueueJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));
        }

        /**
         * @depends testRun
         */
        public function testRunAgainstWorkflowThatWasDeleted()
        {
            $model       = WorkflowTestHelper::createWorkflowModelTestItem('Green', '514');
            $timeTrigger = array('attributeIndexOrDerivedType' => 'string',
                                    'operator'                    => OperatorRules::TYPE_EQUALS,
                                    'value'                       => '514',
                                    'durationSeconds'             => '333');
            $actions     = array(array('type' => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                    ActionForWorkflowForm::ACTION_ATTRIBUTES =>
                                    array('string' => array('shouldSetValue'    => '1',
                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                        'value'  => 'jason'))));
            $savedWorkflow         = WorkflowTestHelper::createByTimeSavedWorkflow($timeTrigger, array(), $actions);
            WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow);

            //Now delete the old workflow
            $deleted = $savedWorkflow->delete();
            $this->assertTrue($deleted);

            $this->assertEquals(1, count(WorkflowMessageInQueue::getAll()));
            $job = new WorkflowMessageInQueueJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));
        }
    }
?>