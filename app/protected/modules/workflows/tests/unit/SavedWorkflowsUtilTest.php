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

    class SavedWorkflowsUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveProcessDateTimeByWorkflowAndModel()
        {
            //Test Date
            $model    = new WorkflowModelTestItem();
            $model->date = '2007-02-02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-02-03 00:00:00', $processDateTime);

            //Test Date with negative duration
            $model    = new WorkflowModelTestItem();
            $model->date = '2007-02-02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, -86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-02-01 00:00:00', $processDateTime);

            //Test DateTime
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '2007-05-02 04:00:02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-05-03 04:00:02', $processDateTime);

            //Test DateTime with negative duration
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '2007-05-02 04:00:02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, -86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-05-01 04:00:02', $processDateTime);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModel
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithNullDate()
        {
            $model    = new WorkflowModelTestItem();
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithNullDate
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDate()
        {
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '0000-00-00';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDate
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithNullDateTime()
        {
            $model    = new WorkflowModelTestItem();
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithNullDateTime
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDateTime()
        {
            $model    = new WorkflowModelTestItem();
            $model->dateTime = '0000-00-00 00:00:00';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDateTime
         */
        public function testResolveOrder()
        {
            $this->assertCount(0, SavedWorkflow::getAll());
            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId1 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 2';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(2, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId2 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 3';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(3, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId3 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 4';
            $savedWorkflow->moduleClassName = 'ContactsModule';
            $savedWorkflow->serializedData  = serialize(array('some data'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId4 = $savedWorkflow->id;

            $savedWorkflow = SavedWorkflow::getById($savedWorkflowId2);
            $this->assertEquals(2, $savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(2, $savedWorkflow->order);

            //Change the moduleClassName to opportunities, it should show 1
            $savedWorkflow->moduleClassName = 'OpportunitiesModule';
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);

            //Delete the workflow. When creating a new AccountsWorkflow, it should show order 4 since the max
            //is still 3.
            $deleted = $savedWorkflow->delete();
            $this->assertTrue($deleted);

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 5';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(4, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveOrder
         */
        public function testResolveBeforeSaveByModel()
        {
            $this->fail();
            //resolveBeforeSaveByRedBeanModel($model)
        }

        /**
         * @depends testResolveBeforeSaveByModel
         */
        public function testResolveAfterSaveByModel()
        {
            $this->fail();
            //resolveAfterSaveByRedBeanModel($model)
        }
    }
?>