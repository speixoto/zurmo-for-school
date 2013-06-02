<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base date provider for working with marketing metrics that have a begin date, end date, and group by
     */
    abstract class MarketingChartDataProvider extends ChartDataProvider
    {
        const NEW_SUBSCRIBERS_COUNT      = 'newSubscribersCount';

        const EXISTING_SUBSCRIBERS_COUNT = 'existingSubscribersCount';

        const UNIQUE_OPEN_RATE           = 'uniqueOpenRate';

        const UNIQUE_CLICK_THROUGH_RATE  = 'uniqueClickThroughRate';

        const UNIQUE_OPENS_COUNT         = 'uniqueOpensCount';

        const UNIQUE_CLICKS_COUNT        = 'uniqueClicksCount';

        const DAY_DATE                   = 'dayDate';

        const FIRST_DAY_OF_WEEK_DATE     = 'firstDayOfWeekDate';

        const FIRST_DAY_OF_MONTH_DATE    = 'firstDayOfMonthDate';

        const COUNT                      = 'count(*)';

        protected $beginDate;

        protected $endDate;

        protected $groupBy;

        /**
         * @var MarketingList
         */
        protected $marketingList;

        /**
         * @var Campaign
         */
        protected $campaign;


        /**
         * Given a begin date, end date and grouping type, return array of data that includes information on how the
         * grouping breaks up by the date range including the start/end dateTime for each range and a display label
         * @param string $beginDate
         * @param string $endDate
         * @param string $groupBy
         * @throws NotSupportedException
         * @return array
         */
        public static function makeGroupedDateTimeData($beginDate, $endDate, $groupBy)
        {
            assert('is_string($beginDate)');
            assert('is_string($endDate)');
            assert('is_string($groupBy)');
            $data = array();
            if($groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
            {
                foreach(DateTimeUtil::getDatesBetweenTwoDatesInARange($beginDate, $endDate) as $date)
                {
                    $data[] = array('beginDate' => $date, 'endDate' => $date,
                                    'displayLabel' => static::resolveAbbreviatedDayMonthDisplayLabel($date));
                }
            }
            elseif($groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            {
                foreach(DateTimeUtil::
                        getWeekStartAndEndDatesBetweenTwoDatesInARange($beginDate, $endDate) as $beginWeekDate => $endWeekDate)
                {
                    if($beginWeekDate < $beginDate)
                    {
                        $beginWeekDate = $beginDate;
                    }
                    if($endWeekDate > $endDate)
                    {
                        $endWeekDate   = $endDate;
                    }
                    $data[] = array('beginDate'    => $beginWeekDate, 'endDate' => $endWeekDate,
                                    'displayLabel' => static::resolveAbbreviatedDayMonthDisplayLabel($beginWeekDate));
                }
            }
            elseif($groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
            {
                foreach(DateTimeUtil::
                        getMonthStartAndEndDatesBetweenTwoDatesInARange($beginDate, $endDate) as $beginMonthDate => $endMonthDate)
                {
                    if($beginMonthDate < $beginDate)
                    {
                        $beginMonthDate = $beginDate;
                    }
                    if($endMonthDate > $endDate)
                    {
                        $endMonthDate   = $endDate;
                    }
                    $data[] = array('beginDate'    => $beginMonthDate, 'endDate' => $endMonthDate,
                                    'displayLabel' => static::resolveAbbreviatedMonthDisplayLabel($beginMonthDate));
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            return $data;
        }

        protected static function resolveAbbreviatedMonthDisplayLabel($date)
        {
            assert('is_string($date)');
            return DateTimeUtil::resolveValueForDateLocaleFormattedDisplay($date,
                DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_ONLY_WIDTH);
        }

        protected static function resolveAbbreviatedDayMonthDisplayLabel($date)
        {
            assert('is_string($date)');
            return DateTimeUtil::resolveValueForDateLocaleFormattedDisplay($date,
                DateTimeUtil::DISPLAY_FORMAT_ABBREVIATED_MONTH_AND_DAY_WIDTH);
        }

        public function setBeginDate($beginDate)
        {
            assert('is_string($beginDate)');
            $this->beginDate = $beginDate;
        }

        public function setEndDate($endDate)
        {
            assert('is_string($endDate)');
            $this->endDate = $endDate;
        }

        public function setGroupBy($groupBy)
        {
            assert('is_string($groupBy)');
            $this->groupBy = $groupBy;
        }

        public function setMarketingList(MarketingList $marketingList)
        {
            if ($this->campaign != null)
            {
                throw new NotSupportedException();
            }
            $this->marketingList = $marketingList;
        }

        public function setCampaign(Campaign $campaign)
        {
            if ($this->marketingList != null)
            {
                throw new NotSupportedException();
            }
            $this->campaign = $campaign;
        }

        protected function resolveDateBalloonLabel($displayLabel)
        {
            assert('is_string($displayLabel)');
            if($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            {
                return Zurmo::t('Core', 'Week of {dateLabel}', array('{dateLabel}' => $displayLabel));
            }
            else
            {
                return $displayLabel;
            }
        }

        protected static function addEmailMessageDayDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote       = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $queryString = "DATE_FORMAT({$quote}{$emailMessageTableName}{$quote}.{$quote}{$columnName}{$quote}, '%Y-%m-%d')";
            $selectQueryAdapter->addClauseByQueryString($queryString, static::DAY_DATE);
        }

        protected static function addEmailMessageFirstDayOfWeekDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote                 = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $queryString = "DATE_FORMAT(DATE_ADD({$columnName}, INTERVAL(2-DAYOFWEEK(" .
                           "{$quote}{$emailMessageTableName}{$quote}.{$quote}{$columnName}{$quote})) day), '%Y-%m-%d')";
            $selectQueryAdapter->addClauseByQueryString($queryString, static::FIRST_DAY_OF_WEEK_DATE);
        }

        protected static function addEmailMessageFirstDayOfMonthDateClause(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $columnName)
        {
            assert('is_string($columnName)');
            $quote                 = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName = EmailMessage::getTableName('EmailMessage');
            $queryString = "DATE_FORMAT(DATE_ADD({$columnName}, INTERVAL(1-DAYOFMONTH(" .
                           "{$quote}{$emailMessageTableName}{$quote}.{$quote}{$columnName}{$quote})) day), '%Y-%m-%d')";
            $selectQueryAdapter->addClauseByQueryString($queryString, static::FIRST_DAY_OF_MONTH_DATE);
        }
    }
?>