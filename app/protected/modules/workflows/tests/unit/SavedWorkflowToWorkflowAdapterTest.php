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

    class SavedWorkflowToWorkflowAdapterTest extends ZurmoBaseTest
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

        public function testResolveWorkflowToSavedWorkflow()
        {
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1 and 2 or 3');

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'currencyValue';
            $trigger->value                       = 'aValue';
            $trigger->secondValue                 = 'bValue';
            $trigger->operator                    = 'between';
            $trigger->currencyIdForValue          = '4';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'owner__User';
            $trigger->value                       = 'aValue';
            $trigger->stringifiedModelForValue    = 'someName';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'createdDateTime';
            $trigger->value                       = 'aValue';
            $trigger->secondValue                 = 'bValue';
            $trigger->operator                    = null;
            $trigger->currencyIdForValue          = null;
            $trigger->valueType                   = 'Between';
            $workflow->addTrigger($trigger);

            //todo: add TimeTrigger
            //todo: add Action, test that stringifiedModelValue does not get saved to SavedWorkflow
            //todo: add emailMessage

            $savedWorkflow = new SavedWorkflow();
            $this->assertNull($savedWorkflow->serializedData);

            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);

            $this->assertEquals('WorkflowsTestModule',         $savedWorkflow->moduleClassName);
            $this->assertTrue($savedWorkflow->isActive);
            $this->assertEquals('myFirstWorkflow',               $savedWorkflow->name);
            $this->assertEquals('aDescription',                $savedWorkflow->description);
            $this->assertEquals(5,                             $savedWorkflow->order);
            $this->assertEquals(Workflow::TRIGGER_ON_NEW,      $savedWorkflow->triggerOn);
            $this->assertEquals(Workflow::TYPE_ON_SAVE,        $savedWorkflow->type);
            $this->assertEquals('1 and 2 or 3',                $workflow->getTriggersStructure());
            $compareData = array('Triggers' => array(
                array(
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'string',
                    'operator'					   => 'equals',
                ),
                array(
                    'currencyIdForValue'           => '4',
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'currencyValue',
                    'operator'					   => 'between',
                ),
                array(
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => 'someName',
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'owner__User',
                    'operator'					   => null,
                ),
                array(
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => 'Between',
                    'attributeIndexOrDerivedType'  => 'createdDateTime',
                    'operator'					   => null,
                    'currencyIdForValue'           => null,
                ),
            ));
            $unserializedData = unserialize($savedWorkflow->serializedData);
            $this->assertEquals($compareData['Triggers'],                     $unserializedData['Triggers']);
            $this->assertEquals('1 and 2 or 3',                              $unserializedData['triggersStructure']);
            $this->assertEquals(Workflow::CURRENCY_CONVERSION_TYPE_SPOT,       $unserializedData['currencyConversionType']);
            $this->assertEquals('CAD',                                       $unserializedData['spotConversionCurrencyCode']);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveWorkflowToSavedWorkflow
         */
        public function testMakeWorkflowBySavedWorkflow()
        {
            //todo: add TimeTrigger and test timeTriggerAttribute gets populated correctly.
            //todo: add Action
            //todo: add emailMessage

            $savedWorkflows               = SavedWorkflow::getAll();
            $this->assertEquals           (1, count($savedWorkflows));
            $savedWorkflow                = $savedWorkflows[0];
            $workflow                     = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            $triggers                    = $workflow->getTriggers();
            $this->assertEquals    	      ('WorkflowsTestModule',         $workflow->getModuleClassName());
            $this->assertEquals           ('myFirstWorkflow',               $workflow->getName());
            $this->assertEquals           ('aDescription',                $workflow->getDescription());
            $this->assertTrue             ($workflow->getIsActive());
            $this->assertEquals           (5,                             $workflow->getOrder());
            $this->assertEquals           (Workflow::TRIGGER_ON_NEW,      $workflow->getTriggerOn());
            $this->assertEquals           (Workflow::TYPE_ROWS_AND_COLUMNS, $workflow->getType());
            $this->assertEquals           ('1 and 2 or 3',                $workflow->getTriggersStructure());
            $this->assertCount            (4, $triggers);

            $this->assertEquals           (true,         $triggers[0]->availableAtRunTime);
            $this->assertEquals           ('aValue',     $triggers[0]->value);
            $this->assertEquals           ('string',     $triggers[0]->attributeIndexOrDerivedType);
            $this->assertNull             ($triggers[0]->currencyIdForValue);
            $this->assertNull             ($triggers[0]->secondValue);
            $this->assertNull             ($triggers[0]->stringifiedModelForValue);
            $this->assertNull             ($triggers[0]->valueType);
            $this->assertEquals           ('equals',     $triggers[0]->operator);

            $this->assertEquals           (true,             $triggers[1]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $triggers[1]->value);
            $this->assertEquals           ('currencyValue',  $triggers[1]->attributeIndexOrDerivedType);
            $this->assertEquals           (4,                $triggers[1]->currencyIdForValue);
            $this->assertEquals           ('bValue',         $triggers[1]->secondValue);
            $this->assertNull             ($triggers[1]->stringifiedModelForValue);
            $this->assertNull             ($triggers[1]->valueType);
            $this->assertEquals           ('between',         $triggers[1]->operator);

            $this->assertEquals           (false,            $triggers[2]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $triggers[2]->value);
            $this->assertEquals           ('owner__User',    $triggers[2]->attributeIndexOrDerivedType);
            $this->assertNull             ($triggers[2]->currencyIdForValue);
            $this->assertNull             ($triggers[2]->secondValue);
            $this->assertEquals           ('someName',       $triggers[2]->stringifiedModelForValue);
            $this->assertNull             ($triggers[2]->valueType);
            $this->assertNull             ($triggers[2]->operator);

            $this->assertEquals           (true,               $triggers[3]->availableAtRunTime);
            $this->assertEquals           ('aValue',           $triggers[3]->value);
            $this->assertEquals           ('createdDateTime',  $triggers[3]->attributeIndexOrDerivedType);
            $this->assertNull             ($triggers[3]->currencyIdForValue);
            $this->assertEquals           ('bValue',           $triggers[3]->secondValue);
            $this->assertNull             ($triggers[3]->stringifiedModelForValue);
            $this->assertNull             ($triggers[3]->operator);
            $this->assertEquals           ('Between',          $triggers[3]->valueType);
        }
    }
?>