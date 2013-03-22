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
        public function testTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'equals', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->string = 'aVAlUe';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveEquals
         */
        public function testTriggerBeforeSaveDoesNotEqual()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'doesNotEqual', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->string = 'aVAlUe';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotEqual
         */
        public function testTriggerBeforeSaveIsNull()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isNull', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
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
         * @depends testTriggerBeforeSaveIsNull
         */
        public function testTriggerBeforeSaveDoesIsNotNull()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isNotNull', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
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
         * @depends testTriggerBeforeSaveDoesIsNotNull
         */
        public function testTriggerBeforeSaveStartsWith()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'startsWith', 'abc');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'abc123';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'abc';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'ABC';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'ab';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveStartsWith
         */
        public function testTriggerBeforeSaveEndsWith()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'endsWith', 'def');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'abcdef';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'def123';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'def';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'redDEF';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'de';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveEndsWith
         */
        public function testTriggerBeforeSaveContains()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'contains', 'chocolate');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'AchocolateB';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'def123';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'chocolate';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'chocolateCookie';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'chocol';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveContains
         */
        public function testTriggerBeforeSaveBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'becomes', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->string = 'cValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomes
         */
        public function testTriggerBeforeSaveWas()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'was', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->string = 'aValue';
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
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->string = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'doesNotChange', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->string = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $model->string = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
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
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('string', 'isNotEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = null;
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->string = '';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsNotEmpty
         */
        public function testTriggerBeforeSaveHasOneOwnedEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('primaryAddress___street1', 'equals', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $address                        = new Address();
            $model->primaryAddress          = $address;
            $model->primaryAddress->street1 = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->primaryAddress->street1 = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->primaryAddress->street1 = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->primaryAddress->street1 = 'aVAlUe';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveHasOneOwnedEquals
         */
        public function testTriggerBeforeSaveHasOneOwnedBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('primaryAddress___street1', 'becomes', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $address                        = new Address();
            $model->primaryAddress          = $address;
            $model->primaryAddress->street1 = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->primaryAddress->street1 = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->primaryAddress->street1 = 'cValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->primaryAddress->street1 = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveHasOneOwnedBecomes
         */
        public function testTriggerBeforeSaveHasOneOwnedWas()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('primaryAddress___street1', 'was', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $address                        = new Address();
            $model->primaryAddress          = $address;
            $model->primaryAddress->street1 = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $model->primaryAddress->street1 = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->primaryAddress->street1 = 'aValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' aValue and now is bValue
            $model->primaryAddress->street1 = 'bValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveHasOneOwnedWas
         */
        public function testTriggerBeforeSaveHasOneNotOwnedEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('hasOne___name', 'equals', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $relatedModel    = new WorkflowModelTestItem2();
            $model->hasOne   = $relatedModel;
            $model->hasOne->name = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasOne->name = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasOne->name = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->hasOne->name = 'aVAlUe';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveHasOneNotOwnedEquals
         */
        public function testTriggerBeforeSaveHasManyNotOwnedEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('hasMany___name', 'equals', 'aValue');
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $relatedModel    = new WorkflowModelTestItem3();
            $relatedModel->name = 'aValue';
            $model->hasMany->add($relatedModel);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasMany[0]->name = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasMany[0]->name = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->hasMany[0]->name = 'aVAlUe';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveHasManyNotOwnedEquals
         */
        public function testTriggerBeforeSaveManyManyNotOwnedEquals()
        {
            $workflow      = self::makeOnSaveWorkflowAndTriggerWithoutValueType('hasMany3___name', 'equals', 'aValue',
                            'WorkflowsTestModule', 'WorkflowModelTestItem2');
            $model         = new WorkflowModelTestItem2();
            $relatedModel  = new WorkflowModelTestItem3();
            $this->assertTrue($relatedModel->save());
            $model->hasMany3->add($relatedModel);
            $model->hasMany3[0]->name = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasMany3[0]->name = 'bValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->hasMany3[0]->name = 'aValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //should be case-insensitive
            $model->hasMany3[0]->name = 'aVAlUe';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>