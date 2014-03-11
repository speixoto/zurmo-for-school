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
     * Rule used in search form to define how the different datetimes types are proceesed.
     */
    class MixedDateTimeTypesSearchFormAttributeMappingRules extends MixedDateTypesSearchFormAttributeMappingRules
    {
        /**
         * The value['type'] determines how the attributeAndRelations is structured.
         * @param string $attributeName
         * @param array $attributeAndRelations
         * @param mixed $value
         */
        public static function resolveAttributesAndRelations($attributeName, & $attributeAndRelations, $value)
        {
            assert('is_string($attributeName)');
            assert('$attributeAndRelations == "resolveEntireMappingByRules"');
            assert('empty($value) || $value == null || is_array($value)');
            $delimiter                      = FormModelUtil::DELIMITER;
            $parts = explode($delimiter, $attributeName);
            if (count($parts) != 2)
            {
                throw new NotSupportedException();
            }
            list($realAttributeName, $type) = $parts;
            if (isset($value['type']) && $value['type'] != null)
            {
                if ($value['type'] == self::TYPE_YESTERDAY ||
                   $value['type'] == self::TYPE_TODAY ||
                   $value['type'] == self::TYPE_TOMORROW ||
                   $value['type'] == self::TYPE_ON)
                {
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_AFTER)
                {
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue));
                }
                elseif ($value['type'] == self::TYPE_BEFORE)
                {
                    $dateValue             = static::resolveValueDataIntoUsableValue($value);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'lessThanOrEqualTo', $lessThanValue));
                }
                elseif ($value['type'] == self::TYPE_BETWEEN)
                {
                    $firstDateValue        = static::resolveValueDataForBetweenIntoUsableFirstDateValue($value);
                    $secondDateValue       = static::resolveValueDataForBetweenIntoUsableSecondDateValue($value);
                    if ($firstDateValue == null)
                    {
                        $greaterThanValue  = null;
                    }
                    else
                    {
                        $greaterThanValue  = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($firstDateValue);
                    }
                    if ($secondDateValue == null)
                    {
                        $lessThanValue     = null;
                    }
                    else
                    {
                        $lessThanValue     = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($secondDateValue);
                    }
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_NEXT_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayPlusSevenDays    = static::calculateNewDateByDaysFromNow(7);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($today);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($todayPlusSevenDays);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_LAST_7_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayMinusSevenDays   = static::calculateNewDateByDaysFromNow(-7);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusSevenDays);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_LAST_30_DAYS)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $todayMinusThirtyDays   = static::calculateNewDateByDaysFromNow(-30);
                    $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusThirtyDays);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_THIS_MONTH)
                {
                    $firstDateValue        = DateTimeUtil::getFirstDayOfAMonthDate();
                    $secondDateValue       = DateTimeUtil::getLastDayOfAMonthDate();
                    $greaterThanValue  = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($firstDateValue);
                    $lessThanValue     = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($secondDateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_LAST_MONTH)
                {
                    $firstDateValue        = DateTimeUtil::getFirstDayOfLastMonthDate();
                    $secondDateValue       = DateTimeUtil::getLastDayOfLastMonthDate();
                    $greaterThanValue  = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($firstDateValue);
                    $lessThanValue     = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($secondDateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_NEXT_MONTH)
                {
                    $firstDateValue        = DateTimeUtil::getFirstDayOfNextMonthDate();
                    $secondDateValue       = DateTimeUtil::getLastDayOfNextMonthDate();
                    $greaterThanValue  = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($firstDateValue);
                    $lessThanValue     = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($secondDateValue);
                    $attributeAndRelations = array(array($realAttributeName, null, 'greaterThanOrEqualTo', $greaterThanValue, true),
                                                   array($realAttributeName, null, 'lessThanOrEqualTo',    $lessThanValue, true));
                }
                elseif ($value['type'] == self::TYPE_BEFORE_TODAY)
                {
                    $today                 = static::calculateNewDateByDaysFromNow(0);
                    $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                    $attributeAndRelations = array(array($realAttributeName, null, 'lessThanOrEqualTo', $lessThanValue));
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                $attributeAndRelations = array(array($realAttributeName, null, null, 'resolveValueByRules'));
            }
        }
    }
?>