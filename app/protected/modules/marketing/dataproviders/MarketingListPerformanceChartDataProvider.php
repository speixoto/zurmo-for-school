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

    class MarketingListPerformanceChartDataProvider extends MarketingChartDataProvider
    {
        const UNIQUE_OPENS_COUNT      = 'uniqueOpensCount';

        const UNIQUE_CLICKS_COUNT     = 'uniqueClicksCount';

        const DAY_DATE                = 'dayDate';

        const FIRST_DAY_OF_WEEK_DATE  = 'firstDayOfWeekDate';

        const FIRST_DAY_OF_MONTH_DATE = 'firstDayOfMonthDate';

        public function getXAxisName()
        {
            return null;
        }

        public function getYAxisName()
        {
            return null;
        }

        public function getChartData()
        {
            //todo: pass through form params like date begin/end and grouping
            //todo: we need demo data then for campaign items, autoresponder items, tracking etc to have these charts render
            //anything meaningful
            //emails sent today, for those emails, how many unique opens, how many unique clicks

            $chartData = array();
            /**
            $chartData[] = array('uniqueClickThroughRate' => 5,  'uniqueOpenRate' => 7,   'displayLabel' => 'Apr 17');
            $chartData[] = array('uniqueClickThroughRate' => 10, 'uniqueOpenRate' => 17,  'displayLabel' => 'Apr 18');
            $chartData[] = array('uniqueClickThroughRate' => 15, 'uniqueOpenRate' => 22,  'displayLabel' => 'Apr 19');
            $chartData[] = array('uniqueClickThroughRate' => 14, 'uniqueOpenRate' => 20,  'displayLabel' => 'Apr 20');
            $chartData[] = array('uniqueClickThroughRate' => 12, 'uniqueOpenRate' => 18,  'displayLabel' => 'Apr 21');
            $chartData[] = array('uniqueClickThroughRate' => 11, 'uniqueOpenRate' => 16,  'displayLabel' => 'Apr 22');
             * **/
            //echo "<pre>";
            //print_r($chartData);
            //echo "</pre>";
            //return $chartData;


            $chartData = $this->resolveChartDataStructure();
            echo "<pre>";
            print_r($chartData);
            echo "</pre>";

//todo: convert indexes to nothing



//todo: combine 2 queries one is marketing list the other is cmapaigns
//todo: only use 1 and filter if on marketing lists or cmpaings

            $groupBy             = $this->resolveGroupBy();
            $beginDateTime       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->beginDate);
            $endDateTime         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->endDate);
            $searchAttributeData = static::makeCampaignsSearchAttributeData($endDateTime, $this->campaign);
            $sql                 = static::makeCampaignsSqlQuery($beginDateTime, $searchAttributeData, $groupBy);
            echo $sql . "<BR>";
            exit;
            $rows                = R::getAll($sql);
            $chartData           = array();
            foreach ($rows as $row)
            {
                $chartData[] = array(
                    'value'        => $utf8_text = $this->resolveCurrencyValueConversionRateForCurrentUserForDisplay($row['amount']),
                    'displayLabel' => static::resolveLabelByValueAndLabels($row['source'], $labels),
                );
            }
            return $chartData;
        }

        protected static function makeCampaignsSqlQuery($beginDateTime, $searchAttributeData, $groupBy)
        {
            assert('is_string($beginDateTime)');
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $campaignTableName         = Campaign::getTableName('Campaign');
            $campaignItemTableName     = Campaign::getTableName('CampaignItem');
            $emailMessageTableName     = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName    = EmailMessage::getColumnNameByAttribute('sentDateTime');

            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Campaign');
            Campaign::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                         $joinTablesAdapter,
                                         $where,
                                         $selectDistinct);
            //todo: fix use of tables, columns, also constants on type
            //todo:!!!! weeeks. if you gorup by week what day is extracted?

            //day - need just date_format
            //DATE_FORMAT(DATE_ADD(createddatetime, INTERVAL(2-DAYOFWEEK({$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}{$quote})) day), '%Y-%m-%d')
            //month first day of month? what about in ranging?
            //todo: the challenge is this returns the FIRST date of something regardless of $this->beginDate,endDate so we need
            //to keep that in mind when comparing chartData template.
            //todo: NEED TO ADD HERE CLAUSE IN SUBS WHEN CAMPAIGN is present, because we need to also filter by that.
            //todo:we can probably turn this sub into another query function and just use natural zurmo to do it. ADD FILTERING BY DATE ALSO (BEGIN/END
            $uniqueOpensSelectPart = "(select count(DISTINCT person_id) from campaignitemactivity, emailmessageactivity where emailmessageactivity_id = emailmessageactivity.id and type=1)";
            $uniqueClicksSelectPart = "(select count(DISTINCT person_id) from campaignitemactivity, emailmessageactivity where emailmessageactivity_id = emailmessageactivity.id and type=2)";
            $selectQueryAdapter->addClauseByQueryString("DATE_FORMAT(day({$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}{$quote}), '%Y-%m-%d')", static::DAY_DATE);
            $selectQueryAdapter->addClauseByQueryString("DATE_FORMAT(DATE_ADD(sentdatetime, INTERVAL(2-DAYOFWEEK({$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}{$quote})) day), '%Y-%m-%d')", static::FIRST_DAY_OF_WEEK_DATE);
            $selectQueryAdapter->addClauseByQueryString("DATE_FORMAT(DATE_ADD(sentdatetime, INTERVAL(1-DAYOFMONTH({$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}{$quote})) day), '%Y-%m-%d')", static::FIRST_DAY_OF_MONTH_DATE);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString($uniqueOpensSelectPart,  static::UNIQUE_OPENS_COUNT);
            $selectQueryAdapter->addClauseByQueryString($uniqueClicksSelectPart, static::UNIQUE_CLICKS_COUNT);
            $joinTablesAdapter->addLeftTableAndGetAliasName($campaignItemTableName, 'id', $campaignTableName, 'campaign_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $campaignItemTableName, 'id');
            //todo: groupings
            //todo: potential munge problem with email messages, not sure how to dealwith that, i suppose we can ignore it since we are
            //raw sqling...
            $where   = RedBeanModelDataProvider::makeWhere('Campaign', $searchAttributeData, $joinTablesAdapter);
            //todo: add month,day,week,qualifier, u have to use m/y together, for example, same with day/m/y week/m/y
            //todo: well you can do year_month
            $sql   = SQLQueryUtil::makeQuery($campaignTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }
//todo: ADD BEGIN DATE SINCE NOW THAT MATTERS....
        protected static function makeCampaignsSearchAttributeData($endDateTime, $campaign)
        {
            assert('is_string($endDateTime)');
            assert('$campaign == null || ($campaign instanceof Campaign && $campaign->id > 0)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'campaignItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => 'sentDateTime',
                            'operatorType'      => 'lessThanOrEqualTo',
                            'value'             => $endDateTime,
                        ),
                    ),
                ),
            );
            if($campaign instanceof Campaign && $campaign->id > 0)
            {
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $campaign->id);
                $searchAttributeData['structure'] = '1 and 2';
            }
            else
            {
                $searchAttributeData['structure'] = '1';
            }
            return $searchAttributeData;
        }

        protected function resolveChartDataStructure()
        {
            $chartData           = array();
            $groupedDateTimeData = static::makeGroupedDateTimeData($this->beginDate, $this->endDate, $this->groupBy);
            //echo "<pre>";
            //print_r($groupedDateTimeData);
            //echo "</pre>";
            foreach($groupedDateTimeData as $groupData)
            {
                $chartData[$groupData['beginDate']] = array('uniqueClickThroughRate' => 0,
                                                            'uniqueOpenRate'         => 0,
                                                            'displayLabel'           => $groupData['displayLabel'],
                                                            'dateBalloonLabel'       =>
                                                            $this->resolveDateBalloonLabel($groupData['displayLabel']));
            }
            return $chartData;
        }

        protected function resolveGroupBy()
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $emailMessageTableName     = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName    = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $groupByColumnString       = "{$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}{$quote}";
            if($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
            {
                return $groupByColumnString;
            }
            elseif($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            {
                return "YEARWEEK(" . $groupByColumnString . ")";
            }
            elseif($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
            {
                return "extract(YEAR_MONTH from " . $groupByColumnString . ")";
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>