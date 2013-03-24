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
     * Test float attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     */
    class WorkflowTriggersUtilForDecimalTest extends WorkflowTriggersUtilBaseTest
    {
        public function testTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'equals', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '54.78';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '54.78';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = -12.23;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveEquals
         */
        public function testTriggerBeforeSaveDoesNotEqual()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'doesNotEqual', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '54.78';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '54.78';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = -12.23;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotEqual
         */
        public function testTriggerBeforeSaveIsNull()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'isNull', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsNull
         */
        public function testTriggerBeforeSaveDoesIsNotNull()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'isNotNull', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesIsNotNull
         */
        public function testTriggerBeforeSaveBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'becomes', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '54.78';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->float = '72.67';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->float = '54.78';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomes
         */
        public function testTriggerBeforeSaveWas()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'was', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '54.78';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->float = '54.78';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' aValue and is now bValue
            $model->float = '-42.532';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveWas
         */
        public function testTriggerBeforeSaveChanges()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->float = '-42.532';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->float = '54.78';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'doesNotChange', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->float = '54.78';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'isEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsEmpty
         */
        public function testTriggerBeforeSaveIsNotEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'isNotEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsNotEmpty
         */
        public function testTriggerBeforeSaveGreaterThan()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'greaterThan', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '80';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '97';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveGreaterThan
         */
        public function testTriggerBeforeSaveGreaterThanOrEqual()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'greaterThanOrEqualTo', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '80.34';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '-42.532';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '97.678';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveGreaterThanOrEqual
         */
        public function testTriggerBeforeSaveLessThan()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'lessThan', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '10.12';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '100';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '43.32';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveLessThan
         */
        public function testTriggerBeforeSaveLessThanOrEqualTo()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'lessThanOrEqualTo', '54.78');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '10.12';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '100.123';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '43';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveLessThanOrEqualTo
         */
        public function testTriggerBeforeSaveBetween()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('float', 'between', '54.78',
                        'WorkflowsTestModule', 'WorkflowModelTestItem', '97');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $model->float = '65.12';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '32.12';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = '76.12';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 55.12;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 54.78;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->float = 97.12;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>