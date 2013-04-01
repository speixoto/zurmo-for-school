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
     * Form to work with currencyValue attributes
     */
    class CurrencyValueWorkflowActionAttributeForm extends WorkflowActionAttributeForm
    {
        public function getValueElementType()
        {
            return 'CurrencyValueAndCurrencyIdForWorkflowActionAttribute';
        }

        /**
         * Defines currency id for the value as being a specific currency id.  In the future additional options will
         * be addded to use the user's currency or maybe an existing model's currency
         */
        const CURRENCY_ID_TYPE_STATIC = 'Static';

        /**
         * @var integer
         */
        public $currencyId;

        /**
         * @var string
         */
        public $currencyIdType = self::CURRENCY_ID_TYPE_STATIC;
        /**
         * Override to make sure value is a float and adding in additional attribute rules
         */
        public function rules()
        {
            return array_merge(parent::rules(),
                array(array('value',           'type', 'type' =>  'float'),
                      array('currencyId',      'type', 'type' =>  'integer'),
                      array('currencyIdType',  'validateCurrencyId'),
                      array('currencyIdType',  'type', 'type' =>  'string'),
                      array('currencyIdType',  'required')));
        }

        /**
         * Value is required based on the type. Override in children as needed to add more scenarios.
         * @return bool
         */
        public function validateCurrencyId()
        {
            if($this->currencyId == null && $this->shouldSetValue)
            {
                $this->addError('currencyId', Zurmo::t('WorkflowsModule', 'Currency Id cannot be blank.'));
                return false;
            }
            return true;
        }

        /**
         * Utilized to create or update model attribute values after a workflow's triggers are fired as true.
         * @param WorkflowActionProcessingModelAdapter $adapter
         * @param $attribute
         * @throws NotSupportedException
         */
        public function resolveValueAndSetToModel(WorkflowActionProcessingModelAdapter $adapter, $attribute)
        {
            assert('is_string($attribute)');
            if($this->type == static::TYPE_STATIC)
            {
                $adapter->getModel()->{$attribute}->value    = $this->value;
                $adapter->getModel()->{$attribute}->currency = Currency::getById((int)$this->currencyId);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param bool $isCreatingNewModel
         * @param bool $isRequired
         * @return array
         */
        protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            assert('is_bool($isCreatingNewModel)');
            assert('is_bool($isRequired)');
            $data                           = array();
            $data[static::TYPE_STATIC]      = Zurmo::t('WorkflowsModule', 'As');
            return $data;
        }
    }
?>