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

    class WorkflowTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('nobody');
            $somebody = UserTestHelper::createBasicUser('somebody');
            $somebody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $somebody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            assert($somebody->save()); // Not Coding Standard
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetTypeDropDownArray()
        {
            $dropDownArray = Workflow::getTypeDropDownArray();
            $this->assertCount(2, $dropDownArray);
        }

        /**
         * @depends testGetTypeDropDownArray
         */
        public function testGetWorkflowSupportedModulesAndLabelsForCurrentUser()
        {
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(6, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(0, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('somebody');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(1, $modulesAndLabels);
        }

        /**
         * @depends testGetWorkflowSupportedModulesAndLabelsForCurrentUser
         */
        public function testGetWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(6, $moduleClassNames);
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(0, $moduleClassNames);
            Yii::app()->user->userModel = User::getByUsername('somebody');
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(1, $moduleClassNames);
        }

        /**
         * @depends testGetWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo
         */
        public function testSetAndGetWorkflow()
        {
            $timeTrigger = new TimeTriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action      = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessage  = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger     = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $workflow = new Workflow();
            $workflow->setModuleClassName('SomeModule');
            $workflow->setDescription('a description');
            $workflow->setTriggersStructure('1 AND 2');
            $workflow->setTimeTriggerAttribute('something');
            $workflow->setId(5);
            $workflow->setIsActive(true);
            $workflow->setOrder(6);
            $workflow->setName('my workflow rule');
            $workflow->setTriggerOn(Workflow::TRIGGER_ON_NEW);
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setTimeTrigger($timeTrigger);
            $workflow->addAction($action);

            $this->assertEquals('SomeModule',             $workflow->getModuleClassName());
            $this->assertEquals('a description',          $workflow->getDescription());
            $this->assertEquals('1 AND 2',                $workflow->getTriggersStructure());
            $this->assertEquals('something',              $workflow->getTimeTriggerAttribute());
            $this->assertEquals(5,                        $workflow->getId());
            $this->assertTrue  ($workflow->getIsActive());
            $this->assertEquals(6,                        $workflow->getOrder());
            $this->assertEquals('my workflow rule',       $workflow->getName());
            $this->assertEquals(Workflow::TRIGGER_ON_NEW, $workflow->getTriggerOne());
            $this->assertEquals(Workflow::TYPE_ON_SAVE,   $workflow->getType());
            $this->assertEquals($timeTrigger,             $workflow->getTimeTrigger());
            $actions = $workflow->getActions();
            $this->assertEquals($action,                $actions[0]);
            $this->assertCount(1,                       $actions);
            $emailMessages = $workflow->getEmailMessages();
            $this->assertEquals($emailMessage,            $emailMessages[0]);
            $this->assertCount(1,                       $emailMessages);
            $triggers = $workflow->getTriggers();
            $this->assertEquals($trigger,               $triggers[0]);
            $this->assertCount(1,                       $triggers);

            $workflow->removeAllActions();
            $actions = $workflow->getActions();
            $this->assertCount(0,                       $actions);

            $workflow->removeAllEmailMessages();
            $actions = $workflow->getEmailMessages();
            $this->assertCount(0,                       $emailMessages);


            $workflow->removeAllTriggers();
            $triggers = $workflow->getTriggers();
            $this->assertCount(0,                       $triggers);

            $workflow->removeTimeTrigger();
            $this->assertEquals(array(),           $workflow->getTimeTrigger());
        }
    }
?>