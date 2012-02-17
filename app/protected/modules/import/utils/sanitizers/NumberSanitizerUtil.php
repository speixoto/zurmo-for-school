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
     * Sanitizer for attributes that are numbers.
     */
    class NumberSanitizerUtil extends SanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'Number';
        }

        /**
         * If a number value is invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        /**
         * Given a value, resolve that the value is a correctly formatted number. If not, an
         * InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$mappingRuleData == null');
            if ($value == null)
            {
                return $value;
            }
            $sanitizedValue = str_replace('$', '', $value);
            $sanitizedValue = str_replace(',', '', $sanitizedValue); // Not Coding Standard
            $model          = new $modelClassName(false);
            $type           = ModelAttributeToMixedTypeUtil::getType($model, $attributeName);
            $validator      = new RedBeanModelNumberValidator();
            if ($validator->integerOnly === true)
            {
                if (!preg_match($validator->integerPattern, $sanitizedValue))
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'Invalid integer format.'));
                }
            }
            else
            {
                if (!preg_match($validator->numberPattern, $sanitizedValue))
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'Invalid number format.'));
                }
            }
            return $sanitizedValue;
        }
    }
?>