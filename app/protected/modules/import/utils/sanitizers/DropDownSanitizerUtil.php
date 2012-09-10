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
     * Sanitizer for drop down attributes.
     */
    class DropDownSanitizerUtil extends SanitizerUtil
    {
        /**
         * Variable used to indicate a drop down value is missing from zurmo and will need to be added during import.
         * @var string
         */
        const ADD_MISSING_VALUE = 'Add missing value';

        /**
         * Variable used to indicate a drop down value is missing from zurmo and will need to map to an existing value
         * based on what is provided.
         * @var string
         */
        const MAP_MISSING_VALUES = 'Map missing values';

        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'DropDown';
        }

        /**
         * Override to support instructions for drop downs. An example is if there is a missing drop down value,
         * information is provided in the instructions explainnig whether to add the missing drop down, delete the value,
         * or merge the value into an existing drop down.
         */
        public static function supportsSanitizingWithInstructions()
        {
            return true;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'DropDown';
        }

        /**
         * Given a value, resolve that the value is a valid custom field data value. If the value does not exist yet,
         * check the import instructions data to determine how to handle the missing value.
         *
         * Example of importInstructionsData
         * array('DropDown' => array(DropDownSanitizerUtil::ADD_MISSING_VALUE => array('neverPresent', 'notPresent')))
         *
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         * @param array $importInstructionsData
         */
        public static function sanitizeValueWithInstructions($modelClassName, $attributeName, $value, $mappingRuleData,
                                             $importInstructionsData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$mappingRuleData == null');
            if (!isset($importInstructionsData["DropDown"][DropDownSanitizerUtil::ADD_MISSING_VALUE]))
            {
                $importInstructionsData["DropDown"][DropDownSanitizerUtil::ADD_MISSING_VALUE] = array();
            }
            if ($value == null)
            {
                return $value;
            }
            $customFieldData                     = CustomFieldDataModelUtil::
                                                   getDataByModelClassNameAndAttributeName(
                                                   $modelClassName, $attributeName);
            $dropDownValues                      = unserialize($customFieldData->serializedData);
            $lowerCaseDropDownValues             = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
            $generateMissingPickListError        = false;
            //does the value already exist in the custom field data
            if (in_array(TextUtil::strToLowerWithDefaultEncoding($value), $lowerCaseDropDownValues))
            {
                $keyToUse                        = array_search(TextUtil::strToLowerWithDefaultEncoding($value), $lowerCaseDropDownValues);
                $resolvedValueToUse              = $dropDownValues[$keyToUse];
            }
            else
            {
                //if the value does not already exist, then check the instructions data.
                $lowerCaseValuesToAdd                = ArrayUtil::resolveArrayToLowerCase(
                                                       $importInstructionsData['DropDown']
                                                       [DropDownSanitizerUtil::ADD_MISSING_VALUE]);
                if (in_array(TextUtil::strToLowerWithDefaultEncoding($value), $lowerCaseValuesToAdd))
                {
                    $keyToAddAndUse                  = array_search(TextUtil::strToLowerWithDefaultEncoding($value), $lowerCaseValuesToAdd);
                    $resolvedValueToUse              = $importInstructionsData['DropDown']
                                                       [DropDownSanitizerUtil::ADD_MISSING_VALUE][$keyToAddAndUse];
                    $unserializedData                = unserialize($customFieldData->serializedData);
                    $unserializedData[]              = $resolvedValueToUse;
                    $customFieldData->serializedData = serialize($unserializedData);
                    $saved                           = $customFieldData->save();
                    assert('$saved');
                }
                elseif (isset($importInstructionsData['DropDown'][DropDownSanitizerUtil::MAP_MISSING_VALUES]))
                {
                    $lowerCaseMissingValuesToMap = ArrayUtil::resolveArrayToLowerCase(
                                                       $importInstructionsData['DropDown']
                                                       [DropDownSanitizerUtil::MAP_MISSING_VALUES]);
                    if (isset($lowerCaseMissingValuesToMap[TextUtil::strToLowerWithDefaultEncoding($value)]))
                    {
                        $keyToUse           = array_search($lowerCaseMissingValuesToMap[TextUtil::strToLowerWithDefaultEncoding($value)],
                                                           $lowerCaseDropDownValues);
                        if ($keyToUse === false)
                        {
                            $message = 'Pick list value specified is missing from existing pick list, has a specified mapping value' .
                               ', but the mapping value is not a valid value.';
                            throw new InvalidValueToSanitizeException(Yii::t('Default', $message));
                        }
                        else
                        {
                            $resolvedValueToUse = $dropDownValues[$keyToUse];
                        }
                    }
                    else
                    {
                        $generateMissingPickListError = true;
                    }
                }
                else
                {
                     $generateMissingPickListError = true;
                }
                if ($generateMissingPickListError)
                {
                    $message = 'Pick list value specified is missing from existing pick list and no valid instructions' .
                               ' were provided on how to resolve this.';
                    throw new InvalidValueToSanitizeException(Yii::t('Default', $message));
                }
            }
            $customField        = new OwnedCustomField();
            $customField->value = $resolvedValueToUse;
            $customField->data  = $customFieldData;
            return $customField;
        }
    }
?>