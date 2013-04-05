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

    class ByTimeWorkflowInQueueTest extends WorkflowBaseTest
    {
        public function testSetAndGet()
        {
            $model = new WorkflowModelTestItem();
            $model->lastName = 'Green';
            $model->string   = 'string';
            $saved = $model->save();
            $this->assertTrue($saved);

            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow->save();
            $this->assertTrue($saved);


            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueue();
            $byTimeWorkflowInQueue->modelClassName  = get_class($model);
            $byTimeWorkflowInQueue->modelItem       = $model;
            $byTimeWorkflowInQueue->processDateTime = '2007-02-02 00:00:00';
            $byTimeWorkflowInQueue->savedWorkflow   = $savedWorkflow;
            $saved = $byTimeWorkflowInQueue->save();
            $this->assertTrue($saved);
            $id = $byTimeWorkflowInQueue->id;
            $byTimeWorkflowInQueue->forget();

            //Retrieve and compare
            $byTimeWorkflowInQueue = ByTimeWorkflowInQueue::getById($id);
            $this->assertEquals('WorkflowModelTestItem',    $byTimeWorkflowInQueue->modelClassName);
            $this->assertTrue  ($byTimeWorkflowInQueue->modelItem->isSame($model));
            $this->assertEquals('2007-02-02 00:00:00',      $byTimeWorkflowInQueue->processDateTime);
            $this->assertTrue  ($byTimeWorkflowInQueue->savedWorkflow->isSame($savedWorkflow));
        }

        /**
         * @depends testSetAndGet
         */
        public function testResolveByWorkflowIdAndModel()
        {
            $model2 = new WorkflowModelTestItem();
            $model2->lastName = 'Engel';
            $model2->string   = 'string';
            $saved = $model2->save();
            $this->assertTrue($saved);

            $savedWorkflows = SavedWorkflow::getByName('some workflow');
            $models          = WorkflowModelTestItem::getByLastName('Green');
            //Test when there should be an existing model.
            $byTimeWorkflowInQueue = ByTimeWorkflowInQueue::resolveByWorkflowIdAndModel($savedWorkflows[0], $models[0]);
            $this->assertTrue($byTimeWorkflowInQueue->id > 0);


            $savedWorkflow2                  = new SavedWorkflow();
            $savedWorkflow2->name            = 'some workflow2';
            $savedWorkflow2->description     = 'description';
            $savedWorkflow2->moduleClassName = 'moduleClassName';
            $savedWorkflow2->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow2->type            = 'some type';
            $savedWorkflow2->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow2->save();
            $this->assertTrue($saved);

            //Test when there should not be an existing ByTimeWorkflowInQueue because there is no existing
            //workflow attached to the model
            $byTimeWorkflowInQueue = ByTimeWorkflowInQueue::resolveByWorkflowIdAndModel($savedWorkflow2, $models[0]);
            $this->assertFalse($byTimeWorkflowInQueue->id > 0);

            //Test where we use an existing savedWorkflow but the model is not on it.
            $byTimeWorkflowInQueue = ByTimeWorkflowInQueue::resolveByWorkflowIdAndModel($savedWorkflows[0], $model2);
            $this->assertFalse($byTimeWorkflowInQueue->id > 0);
        }

        /**
         * @depends testResolveByWorkflowIdAndModel
         */
        public function testGetModelsToProcess($pageSize)
        {
            $this->assertEquals(1, count(ByTimeWorkflowInQueue::getAll()));
            $models = ByTimeWorkflowInQueue::getModelsToProcess(10);
            $this->assertEquals(1, count($models));

            //Now have one that is not ready for processing. It should still only get 1
            $model = new WorkflowModelTestItem();
            $model->lastName = 'Green2';
            $model->string   = 'string2';
            $saved = $model->save();
            $this->assertTrue($saved);

            $savedWorkflows = SavedWorkflow::getByName('some workflow2');

            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueue();
            $byTimeWorkflowInQueue->modelClassName  = get_class($model);
            $byTimeWorkflowInQueue->modelItem       = $model;
            $byTimeWorkflowInQueue->processDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 86400);
            $byTimeWorkflowInQueue->savedWorkflow   = $savedWorkflows[0];
            $saved = $byTimeWorkflowInQueue->save();
            $this->assertTrue($saved);

            $models = ByTimeWorkflowInQueue::getModelsToProcess(10);
            $this->assertEquals(1, count($models));
        }
    }
?>