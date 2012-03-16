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
     * Sanitizer for resolving if an attribute is required or not and whether the value is present. Override to
     * handle multi-select drop down type attributes specifically.
     */
    class MultiSelectDropDownRequiredSanitizerUtil extends RequiredSanitizerUtil
    {
        public static function getLinkedMappingRuleType()
        {
            return 'DefaultValueMultiSelectDropDownModelAttribute';
        }

        /**
         * If the attribute specified is required and the value is null, attempt to utilize a default value if it is
         * specified. If it is not specified or the default value specified is not a valid custom field data value, then
         * an InvalidValueToSanitizeException will be thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            if ($value != null)
            {
                return $value;
            }
            assert('$value == null || $value instanceof OwnedMultipleValuesCustomField');
            assert('$mappingRuleData["defaultValue"] == null || is_string($mappingRuleData["defaultValue"])');
            if ($mappingRuleData['defaultValue'] != null)
            {
                try
                {
                    $customField = new OwnedMultipleValuesCustomField();
                    foreach ($mappingRuleData['defaultValue'] as $aDefaultValue)
                    {
                        $customFieldValue = new CustomFieldValue();
                        $customFieldValue->value = $aDefaultValue;
                        $customField->values->add($customFieldValue);
                    }
                    $customField->data  = CustomFieldDataModelUtil::
                                          getDataByModelClassNameAndAttributeName($modelClassName, $attributeName);
                }
                catch (NotSupportedException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'Pick list is missing corresponding custom field data.'));
                }
                return $customField;
            }
            else
            {
                $model = new $modelClassName(false);
                if (!$model->isAttributeRequired($attributeName))
                {
                    return $value;
                }
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'Multi-Select Pick list value required, but missing.'));
            }
            return $value;
        }
    }
?>