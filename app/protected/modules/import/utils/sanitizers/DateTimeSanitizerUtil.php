<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Sanitizer for date time type attributes.
     */
    class DateTimeSanitizerUtil extends SanitizerUtil
    {
        /**
         * @see DateTimeParser
         */
        public static function getAcceptableFormats()
        {
            return array(
                'yyyy-MM-dd hh:mm',
                'MM-dd-yyyy hh:mm',
                'dd-MM-yyyy hh:mm',
                'MM/dd/yyyy hh:mm',
                'M/d/yyyy hh:mm',
                'd/M/yyyy hh:mm',
                'yyyy-M-d hh:mm'
            );
        }

        public static function getLinkedMappingRuleType()
        {
            return 'ValueFormat';
        }

        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if ($rowBean->{$this->columnName} != null &&
                CDateTimeParser::parse($rowBean->{$this->columnName}, $this->mappingRuleData['format']) === false)
            {
                $label = Zurmo::t('ImportModule', 'Is an invalid date time format. This value will be skipped during import.');
                $this->analysisMessages[] = $label;
            }
        }

        /**
         * Given a value, attempt to convert the value to a db datetime format based on the format provided.
         * If the value does not convert properly, meaning the value is not really in the format specified, then a
         * InvalidValueToSanitizeException will be thrown.
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            if ($value == null)
            {
                return $value;
            }
            $timeStamp = CDateTimeParser::parse($value, $this->mappingRuleData['format']);
            if ($timeStamp === false || !is_int($timeStamp))
            {
                throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'Invalid datetime format.'));
            }
            return DateTimeUtil::convertTimestampToDbFormatDateTime($timeStamp);
        }

        protected function assertMappingRuleDataIsValid()
        {
            assert('isset($this->mappingRuleData["format"])');
        }
    }
?>