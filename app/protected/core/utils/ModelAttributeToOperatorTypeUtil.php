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
     * Given a model and its attribute, find the appropriate operator type.
     */
    class ModelAttributeToOperatorTypeUtil
    {
        const AVAILABLE_OPERATORS_TYPE_STRING   = 'String';

        const AVAILABLE_OPERATORS_TYPE_NUMBER   = 'Number';

        const AVAILABLE_OPERATORS_TYPE_DROPDOWN = 'DropDown';

        const AVAILABLE_OPERATORS_TYPE_HAS_ONE  = 'HasOne';

        public static function resolveOperatorsToIncludeByType(& $data, $type)
        {
            $data[OperatorRules::TYPE_EQUALS] =
                OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_EQUALS);
            $data[OperatorRules::TYPE_DOES_NOT_EQUAL] =
                OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_EQUAL);
            static::resolveIsNullAndIsNotNullOperatorsToInclude($data, $type);
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
            elseif($type == ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                return;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected static function resolveIsNullAndIsNotNullOperatorsToInclude(& $data, $type)
        {
            $data[OperatorRules::TYPE_IS_NULL] =
                OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NULL);
            $data[OperatorRules::TYPE_IS_NOT_NULL] =
                OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_NULL);
        }

        /**
         * Returns the operator type
         * that should be used with the named attribute
         * of the given model.  If the model is a customField, it assumes some sort of dropdown and returns
         * 'equals'.
         * @param $model - instance of a RedBeanModel or RedBeanModels if the model is a HAS_MANY relation on the
         *                 original model.
         * @param string $attributeName
         * @return string
         * @throws NotSupportedException
         */
        public static function getOperatorType($model, $attributeName)
        {
            assert('$model instanceof RedBeanModel || $model instanceof RedBeanModels || $model instanceof ModelForm');
            assert('is_string($attributeName) && $attributeName != ""');
            if ($model instanceof CustomField || $attributeName == 'id')
            {
                return 'equals';
            }
            if ($model instanceof MultipleValuesCustomField)
            {
                return 'oneOf';
            }
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    $operatorType = self::getOperatorTypeFromModelMetadataElement($perClassMetadata['elements'][$attributeName]);
                    if ($operatorType == null)
                    {
                        break;
                    }
                    else
                    {
                        return $operatorType;
                    }
                }
            }
            if ($model->isRelation($attributeName))
            {
                throw new NotSupportedException('Unsupported operator type for Model Class: ' . get_class($model) .
                                                ' with attribute: ' . $attributeName);
            }
            else
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    switch(get_class($validator))
                    {
                        case 'CBooleanValidator':
                            return 'equals';

                        case 'CEmailValidator':
                            return 'startsWith';

                        case 'RedBeanModelTypeValidator':
                        case 'TypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return 'equals';

                                case 'datetime':
                                    return 'equals';

                                case 'integer':
                                    return 'equals';

                                case 'float':
                                    return 'equals';

                                case 'time':
                                    return 'equals';

                                case 'array':
                                    throw new NotSupportedException();
                            }
                            break;

                        case 'CUrlValidator':
                            return 'contains';
                    }
                }
            }
            return 'startsWith';
        }

        protected static function getOperatorTypeFromModelMetadataElement($element)
        {
            assert('is_string($element)');
            switch ($element)
            {
                case 'CurrencyValue':        //todo: once currency has validation rules, this can be removed.
                    return 'equals';

                case 'DropDown':
                    return 'equals';

                case 'MultiSelectDropDown':        //tbd.
                    return 'equals';        //tbd.

                case 'Phone':
                    return 'startsWith';

                case 'RadioDropDown':
                    return 'equals';

                case 'TextArea':
                    return 'contains';

                default :
                    null;
            }
        }

        /**
         * Returns the available operators type.  A string for example has 'String' as the available operators type.
         * This can than be adapted into a dropDown to display possible operators that can be used with a string.
         * @param  $model - instance of a RedBeanModel or RedBeanModels if the model is a HAS_MANY relation on the
         *                  original model.
         * @param  $attributeName
         * @return string representing the type. if no type is available then null is returned.
         * @throws NotSupportedException
         */
        public static function getAvailableOperatorsType($model, $attributeName)
        {
            if ($attributeName == 'id')
            {
                return null;
            }
            if ($model->$attributeName instanceof MultipleValuesCustomField ||
                $model->$attributeName instanceof CustomField)
            {
                return self::AVAILABLE_OPERATORS_TYPE_DROPDOWN;
            }
            $metadata = $model->getMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                if (isset($perClassMetadata['elements'][$attributeName]))
                {
                    $operatorType = self::getAvailableOperatorsTypeFromModelMetadataElement(
                                                $perClassMetadata['elements'][$attributeName]);
                    if ($operatorType == null)
                    {
                        break;
                    }
                    else
                    {
                        return $operatorType;
                    }
                }
            }
            if ($model->isRelation($attributeName))
            {
                throw new NotSupportedException('Unsupported available operators type for Model Class: ' . get_class($model) .
                                                ' with attribute: ' . $attributeName);
            }
            else
            {
                $validators = $model->getValidators($attributeName);
                foreach ($validators as $validator)
                {
                    switch(get_class($validator))
                    {
                        case 'CBooleanValidator':
                            return static::getAvailableOperatorsTypeForBoolean();

                        case 'CEmailValidator':
                            return self::AVAILABLE_OPERATORS_TYPE_STRING;

                        case 'RedBeanModelTypeValidator':
                        case 'TypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return null; //managed through valueType not operator

                                case 'datetime':
                                    return null; //managed through valueType not operator

                                case 'integer':
                                    return self::AVAILABLE_OPERATORS_TYPE_NUMBER;

                                case 'float':
                                    return self::AVAILABLE_OPERATORS_TYPE_NUMBER;

                                case 'time':
                                    return null; //todo:

                                case 'array':
                                    throw new NotSupportedException();
                                case 'string':
                                    return self::AVAILABLE_OPERATORS_TYPE_STRING;
                            }
                            break;

                        case 'CUrlValidator':
                            return self::AVAILABLE_OPERATORS_TYPE_STRING;
                    }
                }
            }
            return null;
        }

        protected static function getAvailableOperatorsTypeFromModelMetadataElement($element)
        {
            assert('is_string($element)');
            switch ($element)
            {
                case 'CurrencyValue':
                    return self::AVAILABLE_OPERATORS_TYPE_NUMBER;
                case 'Phone':
                    return self::AVAILABLE_OPERATORS_TYPE_STRING;
                case 'TextArea':
                    return self::AVAILABLE_OPERATORS_TYPE_STRING;
                default :
                    null;
            }
        }

        protected static function getAvailableOperatorsTypeForBoolean()
        {
            return null;
        }
    }
?>