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

        private $_operator;

        public $value;

        public $secondValue;

        public $stringifiedModelForValue;

        public $valueType;

        private $_availableOperatorsType;

        public function attributeNames()
        {
            return array_merge(parent::attributeNames(), array('operator'));
        }

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

        public function setOperator($value)
        {
            if(!in_array($value, OperatorRules::availableTypes()) && $value != null)
            {
                throw new NotSupportedException();
            }
            $this->_operator = $value;
        }

        public function getOperator()
        {
            return $this->_operator;
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('operator',                    'type', 'type' => 'string'),
                array('operator',  	 				 'validateOperator'),
                array('value',  	 				 'safe'),
                array('value',  	 				 'validateValue'),
                array('secondValue', 				 'safe'),
                array('secondValue',                 'validateSecondValue'),
                array('currencyIdForValue',  	     'safe'),
                array('stringifiedModelForValue',  	 'safe'),
                array('availableAtRunTime',          'boolean'),
                array('valueType',                   'type', 'type' => 'string'),
                array('valueType',                   'validateValueType'),
            ));
        }

        public function validateOperator()
        {
            if($this->getAvailableOperatorsType() != null && $this->operator == null)
            {
                $this->addError('operator', Yii::t('yii', 'Operator cannot be blank.'));
                return  false;
            }
        }

        public function validateValue()
        {
            if((in_array($this->operator, self::getOperatorsWhereValueIsRequired()) ||
               in_array($this->valueType, self::getValueTypesWhereValueIsRequired()) ||
               ($this->getValueElementType() == 'BooleanForReportStaticDropDown' ||
               $this->getValueElementType()  == 'UserNameId' ||
               $this->getValueElementType()  == 'MixedDateTypesForReport')) &&
               $this->value == null)
            {
                $this->addError('value', Yii::t('yii', 'Value cannot be blank.'));
            }
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
                        $this->addError('value', Yii::t('Default', 'Value must be a string.'));
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
                if(in_array($this->operator, self::getOperatorsWhereSecondValueIsRequired()) ||
                   in_array($this->valueType, self::getValueTypesWhereSecondValueIsRequired()))
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

        public function validateValueType()
        {
            if($this->getValueElementType() == 'MixedDateTypesForReport' && $this->valueType == null)
            {
                $this->addError('valueType', Yii::t('yii', 'Type cannot be blank.'));
                return false;
            }
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
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
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
            $modelToReportAdapter          = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
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
            $data[OperatorRules::TYPE_EQUALS] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_EQUALS);
            $data[OperatorRules::TYPE_DOES_NOT_EQUAL] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_EQUAL);
            $data[OperatorRules::TYPE_IS_NULL] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NULL);
            $data[OperatorRules::TYPE_IS_NOT_NULL] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_NULL);
            if($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING)
            {
                $data[OperatorRules::TYPE_STARTS_WITH] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_STARTS_WITH);
                $data[OperatorRules::TYPE_ENDS_WITH] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_ENDS_WITH);
                $data[OperatorRules::TYPE_CONTAINS] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_CONTAINS);
            }
            elseif($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER)
            {
                $data[OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO);
                $data[OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO);
                $data[OperatorRules::TYPE_GREATER_THAN] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_GREATER_THAN);
                $data[OperatorRules::TYPE_LESS_THAN] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_LESS_THAN);
                $data[OperatorRules::TYPE_BETWEEN] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_BETWEEN);
            }
            elseif($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN)
            {
                $data[OperatorRules::TYPE_ONE_OF] =
                        OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_ONE_OF);
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
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
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

        protected static function getValueTypesWhereValueIsRequired()
        {
            return array(MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE,
                         MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER,
                         MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON,
                         MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN);
        }

        protected static function getValueTypesWhereSecondValueIsRequired()
        {
            return array(MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN);
        }

        protected static function getOperatorsWhereValueIsRequired()
        {
            return array(OperatorRules::TYPE_EQUALS,
                         OperatorRules::TYPE_DOES_NOT_EQUAL,
                         OperatorRules::TYPE_STARTS_WITH,
                         OperatorRules::TYPE_ENDS_WITH,
                         OperatorRules::TYPE_CONTAINS,
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_GREATER_THAN,
                         OperatorRules::TYPE_LESS_THAN,
                         OperatorRules::TYPE_ONE_OF,
                         OperatorRules::TYPE_BETWEEN);
        }

        protected static function getOperatorsWhereSecondValueIsRequired()
        {
            return array(OperatorRules::TYPE_BETWEEN);
        }
    }
?>