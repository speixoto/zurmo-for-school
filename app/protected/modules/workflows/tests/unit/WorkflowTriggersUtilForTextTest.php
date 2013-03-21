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
     * Test text/string attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     * #2 - Test equals, becomes, was on owned hasOne and hasMany relation models
     * #3 - Test equals on non-owned hasOne, hasMany, and manyMany relation models
     */
    class WorkflowTriggersUtilForTextTest extends WorkflowTriggersUtilBaseTest
    {
        public function testTextTriggerBeforeSaveEquals()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'equals', 'aValue');
            $model         = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTextTriggerBeforeSaveEquals
         */
        public function testTextTriggerBeforeSaveDoesNotEqual()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'doesNotEqual', 'aValue');
            $model         = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTextTriggerBeforeSaveDoesNotEqual
         */
        public function testTextTriggerBeforeSaveIsNull()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isNull', 'aValue');
            $model         = new WorkflowModelTestItem();
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = null;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTextTriggerBeforeSaveDoesIsNull
         */
        public function testTextTriggerBeforeSaveDoesIsNotNull()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isNotNull', 'aValue');
            $model         = new WorkflowModelTestItem();
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = '';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTextTriggerBeforeSaveDoesIsNotNull
         */
        public function testTextTriggerBeforeSaveStartsWith()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'equals', 'aValue');
            $model         = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        public function testTextTriggerBeforeSaveBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'becomes', 'aValue');
            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->lastName = 'lastName';
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->string = 'cValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        //todO: finish #1,#2, and #3 from documentation above
    }
?>