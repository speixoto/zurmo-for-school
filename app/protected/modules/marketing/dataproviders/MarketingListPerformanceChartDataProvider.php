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
            //echo "<pre>";
            //print_r($chartData);
           // echo "</pre>";
            $rows      = $this->makeCombinedData();
            foreach ($rows as $row)
            {
                $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                if($chartData[$chartIndexToCompare])
                {
                    $uniqueOpenRate         = NumberUtil::divisionForZero($row[self::UNIQUE_OPENS_COUNT], $row[self::COUNT]);
                    $uniqueClickThroughRate = NumberUtil::divisionForZero($row[self::UNIQUE_CLICKS_COUNT], $row[self::COUNT]);
                    $chartData[$chartIndexToCompare][self::UNIQUE_OPEN_RATE]          = round($uniqueOpenRate * 100, 2);
                    $chartData[$chartIndexToCompare][self::UNIQUE_CLICK_THROUGH_RATE] = round($uniqueClickThroughRate * 100, 2);
                }
            }
            $newChartData = array();
            foreach($chartData as $data)
            {
                $newChartData[] = $data;
            }
           // echo "<pre>";
           // print_r($newChartData);
            //echo "</pre>";
            return $newChartData;
        }


        protected function makeCombinedData()
        {
            $combinedRows        = array();
            $groupBy             = $this->resolveGroupBy();
            $beginDateTime       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->beginDate);
            $endDateTime         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->endDate);
            if($this->marketingList == null)
            {
                $searchAttributeData = static::makeCampaignsSearchAttributeData($beginDateTime, $endDateTime, $this->campaign);
                $sql                 = static::makeCampaignsSqlQuery($searchAttributeData, $groupBy);
                echo $sql . "<BR>";
                $rows                = R::getAll($sql);
               // echo "<pre>";
               // print_r($rows);
                //echo "</pre>";
                foreach ($rows as $row)
                {
                    $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                    $combinedRows[$chartIndexToCompare] = $row;
                }
            }
            if($this->campaign == null)
            {
                $searchAttributeData = static::makeAutorespondersSearchAttributeData($beginDateTime, $endDateTime, $this->marketingList);
                $sql                 = static::makeAutorespondersSqlQuery($searchAttributeData, $groupBy);
               // echo $sql . "<BR>";
                $rows                = R::getAll($sql);
                // echo "<pre>";
                // print_r($rows);
                //echo "</pre>";
                foreach ($rows as $row)
                {
                    $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                    if(!isset($combinedRows[$chartIndexToCompare]))
                    {
                        $combinedRows[$chartIndexToCompare] = $row;
                    }
                    else
                    {
                        $combinedRows[$chartIndexToCompare][self::COUNT]               += $row[self::COUNT];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_OPENS_COUNT]  += $row[self::UNIQUE_OPENS_COUNT];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_CLICKS_COUNT] += $row[self::UNIQUE_CLICKS_COUNT];
                    }
                }
            }
           // echo "<pre>";
           // print_r($combinedRows);
            //echo "</pre>";
            return $combinedRows;
        }

        protected static function makeCampaignsSqlQuery($searchAttributeData, $groupBy)
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $campaignTableName         = Campaign::getTableName('Campaign');
            $campaignItemTableName     = CampaignItem::getTableName('CampaignItem');
            $emailMessageTableName     = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName    = EmailMessage::getColumnNameByAttribute('sentDateTime');

            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Campaign');
            Campaign::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                         $joinTablesAdapter,
                                         $where,
                                         $selectDistinct);
            $uniqueOpensSelectPart = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            static::addEmailMessageDayDateClause            ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfWeekDateClause ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfMonthDateClause($selectQueryAdapter, $sentDateTimeColumnName);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS_COUNT);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS_COUNT);
            $joinTablesAdapter->addLeftTableAndGetAliasName($campaignItemTableName, 'id', $campaignTableName, 'campaign_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $campaignItemTableName, 'id');
            $where   = RedBeanModelDataProvider::makeWhere('Campaign', $searchAttributeData, $joinTablesAdapter);
            $sql   = SQLQueryUtil::makeQuery($campaignTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }

        protected static function makeCampaignsSearchAttributeData($beginDateTime, $endDateTime, $campaign)
        {
            assert('is_string($beginDateTime)');
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
                            'operatorType'      => 'greaterThanOrEqualTo',
                            'value'             => $beginDateTime,
                        ),
                    ),
                ),
                2 => array(
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
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $campaign->id);
                $searchAttributeData['structure'] = '1 and 2 and 3';
            }
            else
            {
                $searchAttributeData['structure'] = '1 and 2';
            }
            return $searchAttributeData;
        }

        protected static function resolveCampaignTypeSubQuery($type)
        {
            assert('is_int($type)');
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $where                         = null;
            $selectDistinct                = true;
            $campaignItemTableName         = CampaignItem::getTableName('CampaignItem');
            $campaignItemActivityTableName = CampaignItemActivity::getTableName('CampaignItemActivity');
            $emailMessageActivityTableName = EmailMessageActivity::getTableName('EmailMessageActivity');
            $selectQueryAdapter            = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter             = new RedBeanModelJoinTablesQueryAdapter('CampaignItemActivity');
            $selectQueryAdapter->addClauseByQueryString("campaign_id");
            $joinTablesAdapter->addFromTableAndGetAliasName($emailMessageActivityTableName, 'emailmessageactivity_id',
                                             $campaignItemActivityTableName);
            $where                         = "type = " . $type . " and {$quote}{$campaignItemActivityTableName}{$quote}" .
                                             ".campaignitem_id = {$quote}{$campaignItemTableName}{$quote}.id";
            $sql                           = SQLQueryUtil::makeQuery($campaignItemActivityTableName, $selectQueryAdapter,
                                             $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        protected static function makeAutorespondersSqlQuery($searchAttributeData, $groupBy)
        {
            $quote                      = DatabaseCompatibilityUtil::getQuote();
            $where                      = null;
            $selectDistinct             = false;
            $autoresponderTableName     = Autoresponder::getTableName('Autoresponder');
            $autoresponderItemTableName = AutoresponderItem::getTableName('AutoresponderItem');
            $emailMessageTableName      = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName     = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $selectQueryAdapter         = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter          = new RedBeanModelJoinTablesQueryAdapter('Autoresponder');

            //todo:@story task, add this permission thing as a final thing to look into.
            //todo: do we need to filter on markting list? because we have perms on the related marketing list? probably? but not really when you are in a
            //todo: look what we did in reporting, we should be able to add munge on related marketing list when needed
            //marketing list specifically... it wouldnt matter
            /**
            Autoresponder::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                $joinTablesAdapter,
                $where,
                $selectDistinct);
             * **/
            //todo: fix use of tables, columns, also constants on type
            $uniqueOpensSelectPart = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            static::addEmailMessageDayDateClause            ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfWeekDateClause ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfMonthDateClause($selectQueryAdapter, $sentDateTimeColumnName);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS_COUNT);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS_COUNT);
            $joinTablesAdapter->addLeftTableAndGetAliasName($autoresponderItemTableName, 'id', $autoresponderTableName, 'autoresponder_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $autoresponderItemTableName, 'id');
            $where   = RedBeanModelDataProvider::makeWhere('Autoresponder', $searchAttributeData, $joinTablesAdapter);
            $sql   = SQLQueryUtil::makeQuery($autoresponderTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }

        protected static function makeAutorespondersSearchAttributeData($beginDateTime, $endDateTime, $marketingList)
        {
            assert('is_string($beginDateTime)');
            assert('is_string($endDateTime)');
            assert('$marketingList == null || ($marketingList instanceof MarketingList && $marketingList->id > 0)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'autoresponderItems',
                    'relatedModelData' => array(
                        'attributeName'     => 'emailMessage',
                        'relatedModelData'  => array(
                            'attributeName'     => 'sentDateTime',
                            'operatorType'      => 'greaterThanOrEqualTo',
                            'value'             => $beginDateTime,
                        ),
                    ),
                ),
                2 => array(
                    'attributeName' => 'autoresponderItems',
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
            if($marketingList instanceof MarketingList && $marketingList->id > 0)
            {
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'        => 'marketingList',
                    'operatorType'         => 'equals',
                    'value'                => $marketingList->id);
                $searchAttributeData['structure'] = '1 and 2 and 3';
            }
            else
            {
                $searchAttributeData['structure'] = '1 and 2';
            }
            return $searchAttributeData;
        }

        protected static function resolveAutoresponderTypeSubQuery($type)
        {
            //todo: clean up usage of columns etc.
            assert('is_int($type)');
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $where                         = null;
            $selectDistinct                = true;
            $autoresponderItemActivityTableName     = AutoresponderItemActivity::getTableName('AutoresponderItemActivity');
            $emailMessageActivityTableName = EmailMessageActivity::getTableName('EmailMessageActivity');
            $selectQueryAdapter            = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $joinTablesAdapter             = new RedBeanModelJoinTablesQueryAdapter('AutoresponderItemActivity');
            $selectQueryAdapter->addClauseByQueryString("autoresponder_id");
            $joinTablesAdapter->addFromTableAndGetAliasName($emailMessageActivityTableName, 'emailmessageactivity_id', $autoresponderItemActivityTableName);
            $where                         = "type = " . $type . " and AutoresponderItemActivity.autoresponderitem_id = autoresponderitem.id";
            $sql                           = SQLQueryUtil::makeQuery($autoresponderItemActivityTableName, $selectQueryAdapter,
                $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        protected function resolveIndexGroupByToUse()
        {
            if($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_DAY)
            {
                return self::DAY_DATE;
            }
            elseif($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_WEEK)
            {
                return self::FIRST_DAY_OF_WEEK_DATE;
            }
            elseif($this->groupBy == MarketingOverallMetricsForm::GROUPING_TYPE_MONTH)
            {
                return self::FIRST_DAY_OF_MONTH_DATE;
            }
            else
            {
                throw new NotSupportedException();
            }
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
                $chartData[$groupData['beginDate']] = array(self::UNIQUE_CLICK_THROUGH_RATE => 0,
                                                            self::UNIQUE_OPEN_RATE          => 0,
                                                            'displayLabel'                  => $groupData['displayLabel'],
                                                            'dateBalloonLabel'              =>
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