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
     * Test owner__User (user) attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     */
    class WorkflowTriggersUtilForUserTest extends WorkflowTriggersUtilBaseTest
    {
        protected static $superUserId;

        protected static $bobbyUserId;

        protected static $sarahUserId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            $bobby = UserTestHelper::createBasicUser('bobby');
            $sarah = UserTestHelper::createBasicUser('sarah');
            self::$superUserId = $super->id;
            self::$bobbyUserId = $bobby->id;
            self::$sarahUserId = $sarah->id;
        }

        public function testTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'equals', self::$superUserId);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->user     = User::getByUsername('super');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('bobby');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('super');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveEquals
         */
        public function testTriggerBeforeSaveDoesNotEqual()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'doesNotEqual', self::$superUserId);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->user     = User::getByUsername('super');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('bobby');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('super');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotEqual
         */
        public function testTriggerBeforeSaveBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'becomes', self::$superUserId);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->user     = User::getByUsername('super');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->user     = User::getByUsername('bobby');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->user     = User::getByUsername('sarah');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->user     = User::getByUsername('super');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomes
         */
        public function testTriggerBeforeSaveWas()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'was', self::$superUserId);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->user     = User::getByUsername('super');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->user     = User::getByUsername('bobby');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->user     = User::getByUsername('super');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' aValue and is now bValue
            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveWas
         */
        public function testTriggerBeforeSaveChanges()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->user     = User::getByUsername('bobby');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->user     = User::getByUsername('super');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'doesNotChange', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->user     = User::getByUsername('bobby');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->user     = User::getByUsername('super');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'isEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('bobby');
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsEmpty
         */
        public function testTriggerBeforeSaveIsNotEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('user__User', 'isNotEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->user     = User::getByUsername('bobby');
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>