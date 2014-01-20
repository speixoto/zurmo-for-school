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

    class CalendarUtil
    {
        /**
         * Make calendar items by model.
         * @param RedBeanModel $model
         * @param SavedCalendar $savedCalendar
         * @return CalendarItem
         */
        public static function makeCalendarItemByModel(RedBeanModel $model, SavedCalendar $savedCalendar)
        {
            $calendarItem   = new CalendarItem();
            $startAttribute = $savedCalendar->startAttributeName;
            $endAttribute   = $savedCalendar->endAttributeName;
            $calendarItem->setTitle($model->name);
            $calendarItem->setStartDateTime($model->$startAttribute);
            if($endAttribute != null)
            {
                $calendarItem->setEndDateTime($model->$endAttribute);
            }
            $calendarItem->setCalendarId($savedCalendar->id);
            $calendarItem->setModelClass(get_class($model));
            $calendarItem->setModelId($model->id);
            $calendarItem->setModuleClassName($savedCalendar->moduleClassName);
            return $calendarItem;
        }

        public static function getDateRangeType()
        {
            return SavedCalendar::DATERANGE_TYPE_MONTH;
        }

        public static function getStartDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                return DateTimeUtil::getFirstDayOfAMonthDate();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                return DateTimeUtil::getFirstDayOfAWeek();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTodaysDate();
            }
        }

        public static function getEndDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                $dateTime = new DateTime();
                $dateTime->modify('last day of this month');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                $dateTime       = new DateTime('last day of this week');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTodaysDate();
            }
        }

        /**
         * Get saved calendars for user.
         * @param User $user
         * @return array
         */
        public static function getUserSavedCalendars(User $user)
        {
            $metadata = array();
            $metadata['clauses'] = array(
                1 => array(
                    'attributeName'        => 'createdByUser',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                )
            );
            $metadata['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('SavedCalendar');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendar', $metadata, $joinTablesAdapter);
            return SavedCalendar::getSubset($joinTablesAdapter, null, null, $where);
        }

        /**
         * Process user calendars and get data provider.
         * @return CalendarItemsDataProvider
         */
        public static function processUserCalendarsAndMakeDataProviderForCombinedView()
        {
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser(Yii::app()->user->userModel);
            $dateRangeType              = CalendarUtil::getDateRangeType(); //is this sticky? maybe this is sticky
            $startDate                  = CalendarUtil::getStartDate($dateRangeType); //is this sticky? i dont know. maybe it defaults to TODAY
            $endDate                    = CalendarUtil::getEndDate($dateRangeType);
            $dataProvider               = CalendarItemsDataProviderFactory::getDataProviderByDateRangeType($savedCalendarSubscriptions,
                                                                                                $startDate, $endDate, $dateRangeType);
            return $dataProvider;
        }

    }
?>