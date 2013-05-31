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

    class MarketingEmailsInThisListChartDataProvider extends MarketingChartDataProvider
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
            //todo: hard to read the label text in the graph
            //todo: pass through form params like date begin/end and grouping
            //todo: we need demo data then for campaign items, autoresponder items, tracking etc to have these charts render
            //anything meaningful
            //emails sent today, for those emails, how many unique opens, how many unique clicks

            $chartData = array();
            $chartData[] = array('queued' => 2,  'sent' => 50, 'opened' => 10, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 17');
            $chartData[] = array('queued' => 4,  'sent' => 60, 'opened' => 20, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 18');
            $chartData[] = array('queued' => 6,  'sent' => 70, 'opened' => 30, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 19');
            $chartData[] = array('queued' => 8,  'sent' => 80, 'opened' => 40, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 20');
            $chartData[] = array('queued' => 10,  'sent' => 90, 'opened' => 50, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 21');
            $chartData[] = array('queued' => 12,  'sent' => 100, 'opened' => 60, 'clicked' => 5, 'bounced' => 2, 'optedOut' => 3, 'displayLabel' => 'Apr 22');
            //echo "<pre>";
            //print_r($chartData);
            //echo "</pre>";
            return $chartData;

            $customFieldData = CustomFieldDataModelUtil::
                               getDataByModelClassNameAndAttributeName('Opportunity', 'source');
            $labels          = CustomFieldDataUtil::
                               getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, Yii::app()->language);
            $sql             = static::makeChartSqlQuery();
            $rows            = R::getAll($sql);
            $chartData       = array();
            foreach ($rows as $row)
            {
                $chartData[] = array(
                    'value'        => $utf8_text = $this->resolveCurrencyValueConversionRateForCurrentUserForDisplay($row['amount']),
                    'displayLabel' => static::resolveLabelByValueAndLabels($row['source'], $labels),
                );
            }
            return $chartData;
        }

        protected static function makeChartSqlQuery()
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Opportunity');
            Opportunity::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                                                      $joinTablesAdapter,
                                                                      $where,
                                                                      $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $sumPart                   = "{$quote}currencyvalue{$quote}.{$quote}value{$quote} ";
            $sumPart                  .= "* {$quote}currencyvalue{$quote}.{$quote}ratetobase{$quote}";
            $selectQueryAdapter->addClause('customfield', 'value', 'source');
            $selectQueryAdapter->addClauseByQueryString("sum({$sumPart})", 'amount');
            $joinTablesAdapter->addFromTableAndGetAliasName('customfield', 'source_customfield_id', 'opportunity');
            $joinTablesAdapter->addFromTableAndGetAliasName('currencyvalue', 'amount_currencyvalue_id', 'opportunity');
            $groupBy                   = "{$quote}customfield{$quote}.{$quote}value{$quote}";
            $sql                       = SQLQueryUtil::makeQuery('opportunity', $selectQueryAdapter,
                                                                 $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }
    }
?>