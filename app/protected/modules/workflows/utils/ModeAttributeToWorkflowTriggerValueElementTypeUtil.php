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
     * Helper functionality for finding the filter value element
     * associated with a model's attribute
     */
    class ModeAttributeToWorkflowTriggerValueElementTypeUtil
    {
        /**
         * @param $model
         * @param string $attributeName
         * @return null|string
         * @throws NotSupportedException if the attributeName is a relation on the model
         */
        public static function getType($model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return 'Text';
            }
            if ($model->$attributeName instanceof MultipleValuesCustomField || $model->$attributeName instanceof CustomField)
            {
                return 'StaticDropDownForWorkflow';
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
                throw new NotSupportedException('Unsupported type for Model Class: ' . get_class($model) .
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
                            return 'BooleanForWizardStaticDropDown';

                        case 'CEmailValidator':
                            return 'Text';

                        case 'RedBeanModelTypeValidator':
                        case 'TypeValidator':
                            switch ($validator->type)
                            {
                                case 'date':
                                    return 'MixedDateTypesForWorkflow';

                                case 'datetime':
                                    return 'MixedDateTypesForWorkflow';

                                case 'integer':
                                    return 'MixedNumberTypes';

                                case 'float':
                                    return 'MixedNumberTypes';

                                case 'time':
                                    throw new NotSupportedException();

                                case 'array':
                                    throw new NotSupportedException();
                                case 'string':
                                    return 'Text';
                            }
                            break;

                        case 'CUrlValidator':
                            return 'Text';
                    }
                }
            }
            throw new NotSupportedException();
        }

        /**
         * @param string $elementType
         * @return null|string
         */
        protected static function getAvailableOperatorsTypeFromModelMetadataElement($elementType)
        {
            assert('is_string($elementType)');
            switch ($elementType)
            {
                case 'CurrencyValue':
                    return 'MixedCurrencyValueTypes';
                case 'Phone':
                    return 'Text';
                case 'TextArea':
                    return 'Text';
                default :
                    return null;
            }
        }
    }
?>
