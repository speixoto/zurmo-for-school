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
     * Data analysis for attributes that are user model types.
     */
    class UserValueTypeBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                       implements LinkedToMappingRuleDataAnalyzerInterface
    {
        /**
         * Array of acceptable ids for a user value based on the value type.
         * @var array
         */
        protected $acceptableValues;

        /**
         * Type of user value. Zurmo user id, external system id, or username.
         * @var integer
         */
        protected $type;

        /**
         * @see LinkedToMappingRuleDataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName,
                                         $mappingRuleType, $mappingRuleData)
        {
            assert('is_string($columnName)');
            assert('is_string($mappingRuleType)');
            assert('is_array($mappingRuleData)');
            assert('is_int($mappingRuleData["type"])');
            assert('$mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID ||
                    $mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME');
            $this->type = $mappingRuleData["type"];
            if ($mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)
            {
                $this->acceptableValues = UserValueTypeSanitizerUtil::getUserIds();
            }
            elseif ($mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)
            {
                $this->acceptableValues = UserValueTypeSanitizerUtil::getUserExternalSystemIds();
            }
            else
            {
                $acceptableValues       = UserValueTypeSanitizerUtil::getUsernames();
                $this->acceptableValues = ArrayUtil::resolveArrayToLowerCase($acceptableValues);
            }
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::analyzeByValue()
         */
        protected function analyzeByValue($value)
        {
            if ($this->type == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)
            {
                $compareValue = TextUtil::strToLowerWithDefaultEncoding($value);
            }
            else
            {
                $compareValue = $value;
            }
            if ($value != null && !in_array($compareValue, $this->acceptableValues))
            {
                $this->messageCountData[static::INVALID] ++;
            }
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $invalid  = $this->messageCountData[static::INVALID];
            if ($invalid > 0)
            {
                $label   = Yii::t('Default',  '{count} value(s) have invalid user values. ' .
                                              'These values will not be used during the import.',
                                              array('{count}' => $invalid));
                $this->addMessage($label);
            }
        }
    }
?>