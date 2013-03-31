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

    class WorkflowActionsUtilTest extends WorkflowBaseTest
    {
        public function testProcessBeforeSave()
        {
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add trigger
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array('string' => array('shouldSetValue'    => '1',
                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Confirm that the workflow processes and the attribute gets updated
            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            WorkflowActionsUtil::processBeforeSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals('jason', $model->string);
            $this->assertTrue($model->id < 0);
        }

        /**
         * @depends testProcessBeforeSave
         */
        public function testProcessAfterSave()
        {
            //Create workflow
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            $workflow->setIsActive(true);
            //Add trigger
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasOne';
            $attributes                   = array('name' => array('shouldSetValue'    => '1',
                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $saved         = $savedWorkflow->save();
            $this->assertTrue($saved);

            $this->assertEquals(0, count(WorkflowModelTestItem2::getAll()));
            WorkflowActionsUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals(1, count(WorkflowModelTestItem2::getAll()));
        }

        /**
         * @depends testProcessAfterSave
         */
        public function testProcessOnByTimeWorkflowInQueueJob()
        {
            $model       = WorkflowTestHelper::createWorkflowModelTestItem('Green', '514');
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            $workflow->setIsActive(true);
            //Add action
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $action->relation             = 'hasOne';
            $attributes                   = array('string' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            WorkflowActionsUtil::processOnByTimeWorkflowInQueueJob($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals('jason', $model->string);
        }
    }
?>