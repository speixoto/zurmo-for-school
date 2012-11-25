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

    class FilterForReportForm extends ComponentForReportForm
    {
        public $availableAtRunTime;

        public $currencyIdForValue;

        public $operator;

        public $value;

        public $secondValue;

        public $stringifiedModelForValue;

        public $valueType;

        private $_availableOperatorsType;

        /**
         * Reset availabelOperatorsType cache whenever a new attribute is set
         * (non-PHPdoc)
         * @see ComponentForReportForm::__set()
         */
        public function __set($name, $value)
        {
            parent::__set($name, $value);
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->_availableOperatorsType = null;
            }
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('operator',                    'required'),
                array('operator',                    'type', 'type' => 'string'),
                array('value',  	                 'required'),
                array('value',  	 				 'safe'),
                array('value',  	 				 'validateValue'),
                array('secondValue', 				 'safe'),
                array('secondValue',                 'validateSecondValue'),
                array('currencyIdForValue',  	     'safe'),
                array('stringifiedModelForValue',  	 'safe'),
                array('availableAtRunTime',          'boolean'),
                array('valueType',                   'type', 'type' => 'string'),
                array('valueType',                   'required', 'on' => 'something'),
            ));
        }

        public function validateValue()
        {
            $passedValidation = true;
            $rules            = array();
            if(!is_array($this->value))
            {
                $this->resolveAndValidateValueData($rules, $passedValidation, 'value');
            }
            else
            {
                //Assume array has only string values
                foreach($this->value as $subValue)
                {
                    if(!is_string($subValue))
                    {
                        $this->addError('value', 'Value must be a string.');
                        $passedValidation = false;
                    }
                }
            }
            return $passedValidation;
        }

        /**
         * When the operator type is Between the secondValue is required. Also if the valueType, which is used by
         * date/datetime attributes is set to Between than the secondValue is required.
         */
        public function validateSecondValue()
        {
            $passedValidation = true;
            $rules            = array();
            if(!is_array($this->secondValue))
            {
                if($this->operator == 'Between' || $this->valueType == 'Between')
                {
                    $rules[] = array('secondValue', 'required');
                }
                $this->resolveAndValidateValueData($rules, $passedValidation, 'secondValue');
            }
            else
            {
                throw new NotSupportedException();
            }
            return $passedValidation;
        }

        private function createValueValidatorsByRules(Array $rules)
        {
            $validators=new CList;
            foreach($rules as $rule)
            {
                if(isset($rule[0],$rule[1]))
                {
                    $validators->add(CValidator::createValidator($rule[1],$this,$rule[0],array_slice($rule,2)));
                }
                else
                {
                    throw new CException(Yii::t('yii','{class} has an invalid validation rule. The rule must specify ' .
                                                      'attributes to be validated and the validator name.' ,
                        array('{class}'=>get_class($this))));
                }
            }
            return $validators;
        }

        private function resolveAndValidateValueData(Array $rules, & $passedValidation, $ruleAttributeName)
        {
                $modelClassName       = $this->getResolvedAttributeModelClassName();
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($modelClassName::getModuleClassName(), $modelClassName, $this->reportType);
                $rules                = array_merge($rules,
                                        $modelToReportAdapter->getFilterRulesByAttribute(
                                        $this->getResolvedAttribute(), $ruleAttributeName));
                $validators           = $this->createValueValidatorsByRules($rules);
                foreach($validators as $validator)
                {
                    $validated = $validator->validate($this);
                    if(!$validated)
                    {
                        $passedValidation = false;
                    }
                }
        }

        public function hasAvailableOperatorsType()
        {
            if($this->getAvailableOperatorsType() != null)
            {
                return true;
            }
            return false;
        }

        protected function getAvailableOperatorsType()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            if($this->_availableOperatorsType != null)
            {
                return $this->_availableOperatorsType;
            }
            $moduleClassName               = $this->getResolvedAttributeModuleClassName();
            $modelClassName                = $this->getResolvedAttributeModelClassName();
            $modelToReportAdapter          = ModelRelationsAndAttributesToReportAdapter::
                                             make($moduleClassName, $modelClassName, $this->reportType);
            $availableOperatorsType        = $modelToReportAdapter->getAvailableOperatorsType($this->getResolvedAttribute());
            $this->_availableOperatorsType = $availableOperatorsType;
            return $availableOperatorsType;
        }

        public function getOperatorValuesAndLabels()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $type = $this->getAvailableOperatorsType();
            $data = array();
            $data['equals']       = Yii::t('Default', 'Equals');
            $data['doesNotEqual'] = Yii::t('Default', 'Does Not Equals');
            $data['isNull']       = Yii::t('Default', 'Is Null');
            $data['isNotNull']    = Yii::t('Default', 'Is Not Null');
            if($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING)
            {
                $data['startsWith']            = Yii::t('Default', 'Starts With');
                $data['endsWith']              = Yii::t('Default', 'Starts With');
                $data['contains']              = Yii::t('Default', 'Contains');
            }
            elseif($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER)
            {
                $data['greaterThanOrEqualTo']  = Yii::t('Default', 'Greater Than Or Equal To');
                $data['lessThanOrEqualTo']     = Yii::t('Default', 'Less Than Or Equal To');
                $data['greaterThan']           = Yii::t('Default', 'Greater Than');
                $data['lessThan']              = Yii::t('Default', 'Less Than');
                $data['between']               = Yii::t('Default', 'Between');
            }
            elseif($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN)
            {
                $data['oneOf']                 = Yii::t('Default', 'One Of');
            }
            else
            {
                throw new NotSupportedException();
            }
            return $data;
        }

        public function getValueElementType()
        {
            if($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $moduleClassName      = $this->getResolvedAttributeModuleClassName();
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                    make($moduleClassName, $modelClassName, $this->reportType);
            return $modelToReportAdapter->getFilterValueElementType($this->getResolvedAttribute());
        }

        public function getCustomFieldDataAndLabels()
        {
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            $attribute            = $this->getResolvedAttribute();
            $model                = new $modelClassName();
            if($model->isAttribute($attribute))
            {
                $dataAndLabels    = CustomFieldDataUtil::
                                    getDataIndexedByDataAndTranslatedLabelsByLanguage($model->{$attribute}->data, Yii::app()->language);
                return $dataAndLabels;
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>