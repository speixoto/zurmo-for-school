<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for converting date time stamps between time zones and working
     * with date formats and locales.
     */
    class DateTimeUtil
    {
        const DATETIME_FORMAT_DATE_WIDTH                     = 'short';

        const DATETIME_FORMAT_TIME_WIDTH                     = 'short';

        const DISPLAY_FORMAT_FOR_INPUT                       = 'Display Format For Input';

        const DISPLAY_FORMAT_ABBREVIATED_MONTH_ONLY_WIDTH    = 'Display Format Abbreviated Month';

        const DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH = 'Display Format Abbreviated Month And Day';

        /**
         * Given a datetime, return a string representation of how much time has elapsed since the $dateTime to now
         * @param $dateTime
         * @return string
         */
        public static function getTimeSinceDisplayContent($dateTime)
        {
            $nowTimeStamp           = time();
            $dateTimeStamp          = DateTimeUtil::convertDbFormatDateTimeToTimeStamp($dateTime);
            $timeSinceLatestUpdate  = $nowTimeStamp - $dateTimeStamp;
            $timeForString = array(
                'days'  => floor($timeSinceLatestUpdate / 86400),
                'hours' => floor($timeSinceLatestUpdate / 3600),
            );
            if ($timeForString['days'] == 0)
            {
                if ($timeForString['hours'] == 1)
                {
                    $string = Zurmo::t('MashableInboxModule', '{hours} hour ago', array('{hours}' => $timeForString['hours']));
                }
                else
                {
                    $string = Zurmo::t('MashableInboxModule', '{hours} hours ago', array('{hours}' => $timeForString['hours']));
                }
            }
            elseif (($timeForString['days'] == 1))
            {
                $string = Zurmo::t('MashableInboxModule', '{days} day ago', array('{days}' => $timeForString['days']));
            }
            else
            {
                $string = Zurmo::t('MashableInboxModule', '{days} days ago', array('{days}' => $timeForString['days']));
            }
            return $string;
        }

        /**
         * Convert month to a display label. If the month is invalid then it just returns the month passed in.
         * @param string $month
         * @return mixed
         */
        public static function getMonthName($month)
        {
            if ($month != null)
            {
                return Yii::app()->locale->getMonthName((int)$month);
            }
            return $month;
        }

        /**
         * For the DateTime formatted attributes, get the locale specific date time format string.
         * @return string - datetime format.
         */
        public static function getLocaleDateTimeFormat()
        {
            $dateTimePattern = Yii::app()->locale->getDateTimeFormat();
            $timeFormat      = Yii::app()->locale->getTimeFormat(self::DATETIME_FORMAT_TIME_WIDTH);
            $dateFormat      = Yii::app()->locale->getDateFormat(self::DATETIME_FORMAT_DATE_WIDTH);
            return strtr($dateTimePattern, array('{0}' => $timeFormat, '{1}' => $dateFormat));
        }

        public static function getLocaleDateTimeFormatForInput()
        {
            $dateTimePattern = Yii::app()->locale->getDateTimeFormat();
            $timeFormat      = Yii::app()->locale->getTimeFormat(self::DATETIME_FORMAT_TIME_WIDTH);
            $dateFormat      = Yii::app()->locale->getDateFormatForInput(self::DATETIME_FORMAT_DATE_WIDTH);
            return strtr($dateTimePattern, array('{0}' => $timeFormat, '{1}' => $dateFormat));
        }

        public static function getLocaleDateFormat($dateWidth = self::DATETIME_FORMAT_DATE_WIDTH)
        {
            return Yii::app()->locale->getDateFormat($dateWidth);
        }

        public static function getLocaleDateFormatForInput($dateWidth = self::DATETIME_FORMAT_DATE_WIDTH)
        {
            return Yii::app()->locale->getDateFormatForInput($dateWidth);
        }

        public static function getLocaleTimeFormat($timeWidth = self::DATETIME_FORMAT_TIME_WIDTH)
        {
            return Yii::app()->locale->getTimeFormat($timeWidth);
        }

        public static function isLocaleTimeDisplayedAs12Hours()
        {
            $timeFormat = DateTimeUtil::getLocaleTimeFormat();
            if (strpos($timeFormat, 'H') === false)
            {
                return true;
            }
            return false;
        }

        public static function resolveTimeStampForDateTimeLocaleFormattedDisplay($value,
                                    $dateWidth = self::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = self::DATETIME_FORMAT_TIME_WIDTH,
                                    $forInput = false)
        {
            if ($value == null)
            {
                return null;
            }
            if ($forInput)
            {
                $pattern = static::getLocaleDateTimeFormatForInput();
                return Yii::app()->dateFormatter->format($pattern, $value);
            }
            return Yii::app()->dateFormatter->formatDateTime($value, $dateWidth, $timeWidth);
        }

        /**
         * DateFormatter format can take a pattern found here:
         * (See {@link http://www.unicode.org/reports/tr35/#Date_Format_Patterns})
         * @param $date
         * @param string $dateWidth
         * @return null
         */
        public static function resolveValueForDateLocaleFormattedDisplay(
                                    $date, $displayFormat = null)
        {
            if ($date == null)
            {
                return null;
            }
            $parsedTimeStamp = CDateTimeParser::parse($date, DatabaseCompatibilityUtil::getDateFormat());
            if ($parsedTimeStamp === false)
            {
                return null;
            }
            if ($displayFormat == self::DISPLAY_FORMAT_ABBREVIATED_MONTH_ONLY_WIDTH)
            {
                $month = Yii::app()->dateFormatter->format('M', $parsedTimeStamp);
                return Yii::app()->locale->getMonthName($month, 'abbreviated', true);
            }
            elseif ($displayFormat == self::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH)
            {
                $month    = Yii::app()->dateFormatter->format('M', $parsedTimeStamp);
                $content  = Yii::app()->locale->getMonthName($month, 'abbreviated', true);
                $content .= ' ' . Yii::app()->dateFormatter->format('d', $parsedTimeStamp);
                return $content;
            }
            elseif ($displayFormat == static::DISPLAY_FORMAT_FOR_INPUT)
            {
                return Yii::app()->dateFormatter->format(static::getLocaleDateFormatForInput(self::DATETIME_FORMAT_DATE_WIDTH), $parsedTimeStamp);
            }
            return Yii::app()->dateFormatter->format(static::getLocaleDateFormat(self::DATETIME_FORMAT_DATE_WIDTH), $parsedTimeStamp);
        }

        public static function resolveValueForDateDBFormatted($value)
        {
            if ($value == null)
            {
                return null;
            }
            $localeDateFormat = DateTimeUtil::getLocaleDateFormatForInput();
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                CDateTimeParser::parse($value, $localeDateFormat));
        }

        /**
         * @return local timezone adjusted unix timestamp
         */
        public static function convertFromUtcUnixStampByTimeZone($utcTimeStamp, $timeZone)
        {
            assert('is_string($timeZone)');
            $timeZoneObject = new DateTimeZone($timeZone);
            $offset = $timeZoneObject->getOffset(new DateTime());
            return $utcTimeStamp + $offset;
        }

        /**
         * @return timezone adjusted utc unix timestamp
         */
        public static function convertFromLocalUnixStampByTimeZoneToUtcUnixStamp($utcTimeStamp, $timeZone)
        {
            assert('is_string($timeZone)');
            $timeZoneObject = new DateTimeZone($timeZone);
            $offset = $timeZoneObject->getOffset(new DateTime());
            return $utcTimeStamp - $offset;
        }

        public static function convertTimestampToDbFormatDate($timestamp)
        {
            assert('is_int($timestamp)');
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $result   = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                                     $timestamp);
            date_default_timezone_set($timeZone);
            return $result;
        }

        public static function convertTimestampToDbFormatDateTime($timestamp)
        {
            assert('is_int($timestamp)');
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $result = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateTimeFormat(),
                                                     $timestamp);
            date_default_timezone_set($timeZone);
            return $result;
        }

        public static function convertDbFormatDateTimeToTimestamp($dbFormatDateTime)
        {
            assert('is_string($dbFormatDateTime)');
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $result = strtotime($dbFormatDateTime);
            date_default_timezone_set($timeZone);
            return $result;
        }

        public static function convertTimestampToDisplayFormat($timestamp,
                                    $dateWidth = self::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = self::DATETIME_FORMAT_TIME_WIDTH,
                                    $forInput = false)
        {
            assert('is_int($timestamp)');
            return self::resolveTimeStampForDateTimeLocaleFormattedDisplay($timestamp, $dateWidth, $timeWidth, $forInput);
        }

        public static function isValidDbFormattedDate($date) // Basic version, feel free to enhance.
        {
            if ($date == '0000-00-00')
            {
                return true;
            }
            return preg_match('/^[1-2][0-9][0-9][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|[3][0-1])$/',
                        $date) == 1;
        }

        public static function isValidDbFormattedDateTime($datetime) // Basic version, feel free to enhance.
        {
            if ($datetime == '0000-00-00 00:00:00')
            {
                return true;
            }
            return preg_match(  '/^[1-2][0-9][0-9][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|[3][0-1]) ' .
                                '(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',
                                $datetime) == 1;
        }

        public static function convertDbFormattedDateTimeToLocaleFormattedDisplay($dbFormatDateTime,
                                    $dateWidth = self::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = self::DATETIME_FORMAT_TIME_WIDTH,
                                    $forInput = false)
        {
            assert('is_string($dbFormatDateTime) || $dbFormatDateTime == null');
            if ($dbFormatDateTime == null || $dbFormatDateTime == '0000-00-00 00:00:00')
            {
                return null;
            }
            $timestamp = self::convertDbFormatDateTimeToTimestamp($dbFormatDateTime);
            return self::convertTimestampToDisplayFormat((int)$timestamp, $dateWidth, $timeWidth, $forInput);
        }

        /**
         * Given a locale formatted date time string.
         * Convert to db formatted date time setting the seconds always as 00.
         * @param string $localeFormattedDateTime
         */
        public static function convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($localeFormattedDateTime)
        {
            assert('is_string($localeFormattedDateTime) || $localeFormattedDateTime == null');
            if ($localeFormattedDateTime == null)
            {
                return null;
            }
            $timestamp = CDateTimeParser::parse($localeFormattedDateTime, self::getLocaleDateTimeFormatForInput());
            if ($timestamp == null)
            {
                return null;
            }
            $dbFormattedDateTime =  self::convertTimestampToDbFormatDateTime($timestamp);
            //todo deal with potential diffferent db format
            return substr_replace($dbFormattedDateTime, '00', -2, 2);
        }

        /**
         * Given a db formatted date string, return the db formatted dateTime stamp representing the first minute of
         *  the provided date.  This will be adjusted for the current user's timezone.
         *  Example: date provided is 1980-06-03, the first minute is '1980-06-03 00:00:00'.  If the user is in Chicago
         *  then the time needs to be adjusted 5 or 6 hours forward depending on daylight savings time
         * @param string $dateValue - db formatted
         */
        public static function convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue)
        {
            assert('is_string($dateValue) && DateTimeUtil::isValidDbFormattedDate($dateValue)');
            $greaterThanValue = $dateValue . ' 00:00:00';
            $adjustedTimeStamp = Yii::app()->timeZoneHelper->convertFromLocalTimeStampForCurrentUser(
                                 static::convertDbFormatDateTimeToTimestamp($greaterThanValue));
            return               static::convertTimestampToDbFormatDateTime($adjustedTimeStamp);
        }

        /**
         *
         * Given a db formatted date string, return the db formatted dateTime stamp representing the last minute of
         *  the provided date.  This will be adjusted for the current user's timezone.
         *  Example: date provided is 1980-06-03, the first minute is '1980-06-03 23:59:59'.  If the user is in Chicago
         *  then the time needs to be adjusted 5 or 6 hours forward depending on daylight savings time
         * @param string $dateValue - db formatted
         */
        public static function convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue)
        {
            assert('is_string($dateValue) && DateTimeUtil::isValidDbFormattedDate($dateValue)');
            $lessThanValue     = $dateValue . ' 23:59:59';
            $adjustedTimeStamp = Yii::app()->timeZoneHelper->convertFromLocalTimeStampForCurrentUser(
                                 static::convertDbFormatDateTimeToTimestamp($lessThanValue));
            return               static::convertTimestampToDbFormatDateTime($adjustedTimeStamp);
        }

        public static function getFirstDayOfAMonthDate($stringTime = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $dateTime = new DateTime($stringTime);
            $dateTime->modify('first day of this month');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
        }

        public static function getLastDayOfAMonthDate($stringTime = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $dateTime = new DateTime($stringTime);
            $dateTime->modify('last day of this month');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
        }

        public static function isDateValueNull(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName) || $attributeName == null');
            return self::isDateStringNull($model->$attributeName);
        }

        public static function isDateStringNull($date)
        {
            assert('is_string($date) || $date == null');
            if ($date != null && $date != '0000-00-00')
            {
                return false;
            }
            return true;
        }

        public static function isDateTimeValueNull(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName) || $attributeName == null');
            return self::isDateTimeStringNull($model->$attributeName);
        }

        public static function isDateTimeStringNull($dateTime)
        {
            assert('is_string($dateTime) || $dateTime == null');
            if ($dateTime != null && $dateTime != '0000-00-00 00:00:00')
            {
                return false;
            }
            return true;
        }

        public static function resolveDateAsDateTime($date)
        {
            assert('is_string($date)');
            return $date . ' 00:00:00';
        }

        /**
         * Given 2 dates in a range, return an array of all days that occur during that range
         * @param string $beginDate
         * @param string $endDate
         * @return array
         */
        public static function getDatesBetweenTwoDatesInARange($beginDate, $endDate)
        {
            assert('is_string($beginDate)');
            assert('is_string($endDate)');
            $beginTimeStamp = strtotime($beginDate);
            $endTimeStamp   = strtotime($endDate);
            $quantityOfDays = round(($endTimeStamp - $beginTimeStamp) / 86400) + 1;
            $daysData       = array();
            for ($i = 0; $i < $quantityOfDays; $i++)
            {
                $daysData[] = date('Y-m-d', ($beginTimeStamp + ($i * 86400)));
            }
            return $daysData;
        }

        /**
         * Given 2 dates in a range, return an array of all weeks in the date range organized by start/end date of
         * those months
         * @param string $beginDate
         * @param string $endDate
         * @return array
         */
        public static function getWeekStartAndEndDatesBetweenTwoDatesInARange($beginDate, $endDate)
        {
            assert('is_string($beginDate)');
            assert('is_string($endDate)');
            $weekTimeStamp      = strtotime($beginDate);
            $endTimeStamp       = strtotime($endDate);
            $weeksData          = array();
            while ($weekTimeStamp <= $endTimeStamp)
            {
                $date = new DateTime(date('Y-m-d', $weekTimeStamp));
                $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
                $beginDateOfWeek = $date->format('Y-m-d');
                $date->modify('this week next sunday');
                $endDateOfWeek = $date->format('Y-m-d');
                $weeksData[$beginDateOfWeek] = $endDateOfWeek;
                $weekTimeStamp = strtotime("+1 week", $weekTimeStamp); // Not Coding Standard
            }
            //Capture dates in last week if needed
            $date = new DateTime(date('Y-m-d', $weekTimeStamp));
            $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
            $beginDateOfWeek = $date->format('Y-m-d');
            if ($beginDateOfWeek < $endDate)
            {
                $date->modify('this week next sunday');
                $endDateOfWeek = $date->format('Y-m-d');
                $weeksData[$beginDateOfWeek] = $endDateOfWeek;
                $weekTimeStamp               = strtotime("+1 week", $weekTimeStamp); // Not Coding Standard
            }
            if ($endDateOfWeek < $endDate)
            {
                $date = new DateTime(date('Y-m-d', $weekTimeStamp));
                $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
                $beginDateOfWeek = $date->format('Y-m-d');
                $date->modify('this week next sunday');
                $endDateOfWeek = $date->format('Y-m-d');
                $weeksData[$beginDateOfWeek] = $endDateOfWeek;
            }
            return $weeksData;
        }

        /**
         * Given 2 dates in a range, return an array of all months in the date range organized by start/end date of
         * those months
         * @param string $beginDate
         * @param string $endDate
         * @return array
         */
        public static function getMonthStartAndEndDatesBetweenTwoDatesInARange($beginDate, $endDate)
        {
            assert('is_string($beginDate)');
            assert('is_string($endDate)');
            $monthTimeStamp      = strtotime($beginDate);
            $endTimeStamp        = strtotime($endDate);
            $monthsData          = array();
            while ($monthTimeStamp <= $endTimeStamp)
            {
                $date = new DateTime(date('Y-m-d', $monthTimeStamp));
                $date->modify('first day of this month');
                $beginDateOfMonth = $date->format('Y-m-d');
                $date->modify('last day of this month');
                $endDateOfMonth = $date->format('Y-m-d');
                $monthsData[$beginDateOfMonth] = $endDateOfMonth;
                $monthTimeStamp = strtotime("+1 month", $monthTimeStamp); // Not Coding Standard
            }
            //Capture dates in last month if needed
            $date = new DateTime(date('Y-m-d', $monthTimeStamp));
            $date->modify('first day of this month');
            $beginDateOfMonth = $date->format('Y-m-d');
            if ($beginDateOfMonth < $endDate)
            {
                $date->modify('last day of this month');
                $endDateOfMonth = $date->format('Y-m-d');
                $monthsData[$beginDateOfMonth] = $endDateOfMonth;
                $monthTimeStamp = strtotime("+1 month", $monthTimeStamp); // Not Coding Standard
            }
            if ($endDateOfMonth < $endDate)
            {
                $date = new DateTime(date('Y-m-d', $monthTimeStamp));
                $date->modify('first day of this month');
                $beginDateOfMonth = $date->format('Y-m-d');
                $date->modify('last day of this month');
                $endDateOfMonth = $date->format('Y-m-d');
                $monthsData[$beginDateOfMonth] = $endDateOfMonth;
            }
            return $monthsData;
        }
    }
?>
