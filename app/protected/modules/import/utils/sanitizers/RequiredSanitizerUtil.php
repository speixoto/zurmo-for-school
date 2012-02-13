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
     * Sanitizer for resolving if an attribute is required or not and whether the value is present.
     */
    class RequiredSanitizerUtil extends SanitizerUtil
    {
        public static function supportsBatchAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'Required';
        }

        public static function getLinkedMappingRuleType()
        {
            return 'DefaultValueModelAttribute';
        }

        /**
         * If a required value is missing or invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        /**
         * Resolves that the value is not null or the value is null and a valid default value is available for
         * the model id. If not, then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null || is_string($attributeName)');
            assert('$mappingRuleData["defaultValue"] == null || is_string($mappingRuleData["defaultValue"])');
            if ($value != null)
            {
                return $value;
            }
            if ($mappingRuleData['defaultValue'] != null)
            {
                return $mappingRuleData['defaultValue'];
            }
            else
            {
                $model = new $modelClassName(false);
                if (!$model->isAttributeRequired($attributeName))
                {
                    return $value;
                }
                throw new InvalidValueToSanitizeException(Yii::t('Default',
                'This field is required and neither a value nor a default value was specified.'));
            }
        }
    }
?>