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
     * Data analyzer for columns mapped to full name derived attributes.
     */
    class FullNameBatchAttributeValueDataAnalyzer extends BatchAttributeValueDataAnalyzer
                                                  implements DataAnalyzerInterface
    {
        /**
         * A variable used to index a count of values that are too long.
         * @var string
         */
        const FULL_NAME_TOO_LONG = 'Full name too long';

        /**
         * A variable used to index a count of values that are too long.
         * @var string
         */
        const FULL_NAME_TOO_SHORT = 'Full name too short';

        /**
         * The max allowed length for the first name attribute.
         * @var integer
         */
        protected $firstNameMaxLength;

        /**
         * The max allowed length for the last name attribute.
         * @var integer
         */
        protected $lastNameMaxLength;

        /**
         * The minimum allowed length for the last name attribute.
         * @var integer
         */
        protected $lastNameMinLength;

        public function __construct($modelClassName, $attributeName)
        {
            parent:: __construct($modelClassName, $attributeName);
            assert('$attributeName == null');
            $this->messageCountData[static::FULL_NAME_TOO_LONG] = 0;
            $this->messageCountData[static::FULL_NAME_TOO_SHORT] = 0;
            $model                    = new $modelClassName(false);
            $this->firstNameMaxLength = StringValidatorHelper::
                                        getMaxLengthByModelAndAttributeName($model, 'firstName');
            $this->lastNameMaxLength  = StringValidatorHelper::
                                        getMaxLengthByModelAndAttributeName($model, 'lastName');
            $this->lastNameMinLength  = StringValidatorHelper::
                                        getMinLengthByModelAndAttributeName($model, 'lastName');
        }

        /**
         * @see DataAnalyzerInterface::runAndMakeMessages()
         */
        public function runAndMakeMessages(AnalyzerSupportedDataProvider $dataProvider, $columnName)
        {
            assert('is_string($columnName)');
            $this->processAndMakeMessage($dataProvider, $columnName);
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::analyzeByValue()
         */
        protected function analyzeByValue($value)
        {
            if ($value == null)
            {
                return;
            }
            @list($firstName, $lastName) = explode(' ', trim($value));
            if ($lastName == null)
            {
                $lastName  = $firstName;
                $firstName = null;
            }
            if (strlen($lastName) > $this->lastNameMaxLength || strlen($firstName) > $this->firstNameMaxLength)
            {
                $this->messageCountData[static::FULL_NAME_TOO_LONG] ++;
            }
            if (strlen($lastName) < $this->lastNameMinLength)
            {
                $this->messageCountData[static::FULL_NAME_TOO_SHORT] ++;
            }
        }

        /**
         * @see BatchAttributeValueDataAnalyzer::makeMessages()
         */
        protected function makeMessages()
        {
            $tooLarge = $this->messageCountData[static::FULL_NAME_TOO_LONG];
            $tooShort = $this->messageCountData[static::FULL_NAME_TOO_SHORT];
            if ($tooLarge > 0)
            {
                $label   = Yii::t('Default', '{count} value(s) are too large for this field. ' .
                                             'These rows will be skipped during import.',
                                             array('{count}' => $tooLarge));
                $this->addMessage($label);
            }
                    $tooLarge = $this->messageCountData[static::FULL_NAME_TOO_LONG];
            if ($tooShort > 0)
            {
                $label   = Yii::t('Default', '{count} value(s) are too short for this field. ' .
                                             'These rows will be skipped during import.',
                                             array('{count}' => $tooShort));
                $this->addMessage($label);
            }
        }
    }
?>