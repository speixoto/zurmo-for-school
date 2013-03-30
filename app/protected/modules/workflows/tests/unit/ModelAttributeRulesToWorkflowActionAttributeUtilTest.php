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

    class ModelAttributeRulesToWorkflowActionAttributeUtilTest extends WorkflowBaseTest
    {
        public function testGetApplicableRulesByModelClassNameAndAttributeName()
        {
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'boolean', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'date', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'dateTime', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'float', 'value');
            $compareData = array(array('value',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'integer', 'value');
            $compareData = array(array('value',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'phone', 'value');
            $compareData = array(array('value',  'length',  'min'  => 1, 'max' => 14));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'string', 'value');
            $compareData = array(array('value',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'textArea', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'url', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'firstName', 'value');
            $compareData = array(array('value',  'length',  'min'  => 1, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'lastName', 'value');
            $compareData = array(array('value',  'length',  'min'  => 2, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            //Now test lastName and string with required as applicable.
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'string', 'value', true);
            $compareData = array(array('value',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            //Variations of dropdowns should return array() because they will be always the same and already defined
            //in the rules of the corresponding forms.
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'dropDown', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'multiDropDown', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'radioDropDown', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'tagCloud', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            //CurrencyValue should return array() as well since the rules never change so they can be specified in the
            //form.
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('WorkflowModelTestItem', 'currencyValue', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
        }

        /**
         * @depends testGetApplicableRulesByModelClassNameAndAttributeName
         */
        public function testOwnedEmailModel()
        {
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('Email', 'emailAddress', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName('Email', 'optOut', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
        }

        /**
         * @depends testOwnedEmailModel
         */
        public function testOwnedAddressModel()
        {
            $rules       = ModelAttributeRulesToWorkflowActionAttributeUtil::
                           getApplicableRulesByModelClassNameAndAttributeName('Address', 'city', 'value');
            $compareData = array(array('value',  'length', 'max' => 32));
            $this->assertEquals($compareData, $rules);

            $rules       = ModelAttributeRulesToWorkflowActionAttributeUtil::
                           getApplicableRulesByModelClassNameAndAttributeName('Address', 'latitude', 'value');
            $compareData = array(array('value',  'length',  'max'  => 11),
                                 array('value',  'RedBeanModelNumberValidator',  'precision' => 7));
            $this->assertEquals($compareData, $rules);
        }

        /**
         * @depends testOwnedAddressModel
         */
        public function testGetApplicableRulesByModelClassNameAndAttributeNameForCustomCreatedTypes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $import = new Import();
            $import->serializedData = serialize(array('importRulesType' => 'Accounts'));
            $this->assertTrue($import->save());

            ModulesSearchWithDataProviderTestHelper::createDateAttribute(new Account(), 'date');
            ModulesSearchWithDataProviderTestHelper::createDateTimeAttribute(new Account(), 'dateTime');
            ModulesSearchWithDataProviderTestHelper::createCheckBoxAttribute(new Account(), 'checkbox');
            ModulesSearchWithDataProviderTestHelper::createDecimalAttribute(new Account(), 'decimal');
            ModulesSearchWithDataProviderTestHelper::createIntegerAttribute(new Account(), 'integer');
            ModulesSearchWithDataProviderTestHelper::createPhoneAttribute(new Account(), 'phone');
            ModulesSearchWithDataProviderTestHelper::createTextAttribute(new Account(), 'text');
            ModulesSearchWithDataProviderTestHelper::createTextAreaAttribute(new Account(), 'textArea');
            ModulesSearchWithDataProviderTestHelper::createUrlAttribute(new Account(), 'url');

            //Test All custom created types since their rules could vary
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'dateCstm', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'dateTimeCstm', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'checkboxCstm', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'decimalCstm', 'value');
            $compareData = array(array('value',  'length',  'max'  => 6),
                                 array('value',  'RedBeanModelNumberValidator', 'precision' => 2));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'integerCstm', 'value');
            $compareData = array(array('value',  'length',  'max'  => 11),
                                 array('value',  'numerical', 'min'  => -500000, 'max'  => 500000));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'phoneCstm', 'value');
            $compareData = array(array('value',  'length',  'max' => 20));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'textCstm', 'value');
            $compareData = array(array('value',  'length',  'max' => 50));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'textAreaCstm', 'value');
            $compareData = array();
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'urlCstm', 'value');
            $compareData = array(array('value',  'length',  'max' => 50));
            $this->assertEquals($compareData, $rules);
        }
    }
?>