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

    class TriggerRulesFactoryTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateTriggerRulesByTrigger()
        {
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'string';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof TextTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'phone';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof TextTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'textArea';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof TextTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'url';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof TextTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof TextTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'boolean';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof CheckBoxTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'dropDown';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof DropDownTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'radioDropDown';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof DropDownTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'multiDropDown';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof MultiSelectDropDownTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'tagCloud';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof MultiSelectDropDownTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'integer';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof IntegerTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'float';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof DecimalTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'currencyValue';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof CurrencyValueTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'likeContactState';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof ContactStateTriggerRules);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'user';
            $triggerRules = TriggerRulesFactory::createTriggerRulesByTrigger($trigger);
            $this->assertTrue($triggerRules instanceof UserTriggerRules);
        }
    }
?>