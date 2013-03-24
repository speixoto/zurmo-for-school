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
     * Test date attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     */
    class WorkflowTriggersUtilForDateTest extends WorkflowTriggersUtilBaseTest
    {
        public function testTriggerBeforeSaveOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'On', '2007-07-01');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = '2007-07-01';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = '2007-07-02';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = '2007-07-01';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveOn
         */
        public function testTriggerBeforeSaveBetween()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Between', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->date = 'aVAlUe';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBetween
         */
        public function testTriggerBeforeSaveAfter()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'After', 'abc');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = 'abc123';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'abc';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'ABC';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'ab';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveAfter
         */
        public function testTriggerBeforeSaveBefore()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Before', 'def');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = 'abcdef';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'def123';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'def';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'redDEF';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'de';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBefore
         */
        public function testTriggerBeforeSaveBecomesOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Becomes On', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->date = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->date = 'cValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->date = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomesOn
         */
        public function testTriggerBeforeSaveWasOn()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Was On', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $model->date = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->date = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->date = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' aValue and is now bValue
            $model->date = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveWas
         */
        public function testTriggerBeforeSaveChanges()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->date = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->date = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Does Not Change', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->date = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->date = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Is Empty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsEmpty
         */
        public function testTriggerBeforeSaveIsNotEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerForDateOrDateTime('date', 'Is Not Empty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someString';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->date = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>