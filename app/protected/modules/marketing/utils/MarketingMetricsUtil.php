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

    class MarketingMetricsUtil
    {
        /**
         * @param MarketingListPerformanceChartDataProvider $chartDataProvider
         * @param string $uniqueId
         * @return string
         */
        public static function renderOverallListPerformanceChartContent(MarketingListPerformanceChartDataProvider $chartDataProvider, $uniqueId)
        {
            assert('is_string($uniqueId)');
            $chartData = $chartDataProvider->getChartData();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data = $chartData;
            $amChart->id =  $uniqueId;
            $amChart->type = ChartRules::TYPE_LINE;
            $amChart->addSerialGraph('uniqueClickThroughRate', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Unique CTR') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'CTR') . ": [[value]]%'"));
            $amChart->addSerialGraph('uniqueOpenRate', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Unique Open Rate') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Open Rate') . ": [[value]]%'"));
            $amChart->xAxisName        = $chartDataProvider->getXAxisName();
            $amChart->yAxisName        = $chartDataProvider->getYAxisName();
            $javascript = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . $uniqueId, $javascript);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $uniqueId));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }

        /**
         * @param ChartDataProvider $chartDataProvider
         * @param string $uniqueId
         * @return string
         */
        public static function renderEmailsInThisListChartContent(ChartDataProvider $chartDataProvider, $uniqueId)
        {
            assert('is_string($uniqueId)');
            $chartData = $chartDataProvider->getChartData();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data = $chartData;
            $amChart->id =  $uniqueId;
            $amChart->type = ChartRules::TYPE_STACKED_COLUMN_2D;
            $amChart->addSerialGraph('queued', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Queued') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Queued') . ": [[value]]'"));
            $amChart->addSerialGraph('sent', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Sent') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Sent') . ": [[value]]'"));
            $amChart->addSerialGraph('opened', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Opened') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Opened') . ": [[value]]'"));
            $amChart->addSerialGraph('clicked', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Clicked') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Clicked') . ": [[value]]'"));
            $amChart->addSerialGraph('bounced', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Bounced') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Bounced') . ": [[value]]'"));
            $amChart->addSerialGraph('optedOut', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Opted Out') . "'",
                                           'balloonText' => "'" . Zurmo::t('MarketingModule', 'Opted Out') . ": [[value]]'"));

            $amChart->xAxisName        = $chartDataProvider->getXAxisName();
            $amChart->yAxisName        = $chartDataProvider->getYAxisName();
            $javascript = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . $uniqueId, $javascript);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $uniqueId));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }

        /**
         * @param MarketingListGrowthChartDataProvider $chartDataProvider
         * @param string $uniqueId
         * @return string
         */
        public static function renderListGrowthChartContent(MarketingListGrowthChartDataProvider $chartDataProvider, $uniqueId)
        {
            assert('is_string($uniqueId)');
            $chartData = $chartDataProvider->getChartData();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data = $chartData;
            $amChart->id =  $uniqueId;
            $amChart->type = ChartRules::TYPE_STACKED_COLUMN_2D;
            $amChart->addSerialGraph('existingSubscribers', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'Existing Subscribers') . "'",
                                           'balloonText' => "'[[dateBalloonLabel]]<br>" . Zurmo::t('MarketingModule', 'Existing Subscribers') . ": [[value]]'"));
            $amChart->addSerialGraph('newSubscribers', 'column',
                                     array('title'       => "'" . Zurmo::t('MarketingModule', 'New Subscribers') . "'",
                                           'balloonText' => "'[[dateBalloonLabel]]<br>" . Zurmo::t('MarketingModule', 'New Subscribers') . ": [[value]]'"));
            $amChart->xAxisName        = $chartDataProvider->getXAxisName();
            $amChart->yAxisName        = $chartDataProvider->getYAxisName();
            $javascript = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . $uniqueId, $javascript);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $uniqueId));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }
    }
?>