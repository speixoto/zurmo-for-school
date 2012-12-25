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
     * Base class used for wrapping a view of a report chart
     */
    class ReportChartView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        protected $uniqueLayoutId;

        protected $maximumGroupsPerChart = 100;

        public function __construct($controllerId, $moduleId, SummationReportDataProvider $dataProvider, $uniqueLayoutId)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($uniqueLayoutId)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->dataProvider           = $dataProvider;
            $this->uniqueLayoutId         = $uniqueLayoutId;
        }

        public function renderContent()
        {
            if($this->dataProvider->calculateTotalItemCount() > $this->maximumGroupsPerChart)
            {
                return $this->renderMaximumGroupsContent();
            }
            return $this->renderChartContent();
        }

        protected function renderChartContent()
        {
            $chartData = $this->dataProvider->getChartData();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data      = $chartData;
            $amChart->id        = $this->uniqueLayoutId;
            $amChart->type      = $this->dataProvider->getReport()->getChart()->type;
            $amChart->addSerialGraph('value', 'column');
            $amChart->xAxisName = $this->dataProvider->resolveFirstSeriesLabel();
            $amChart->yAxisName = $this->dataProvider->resolveFirstRangeLabel();
            $scriptContent      = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->uniqueLayoutId, $scriptContent);
            $cClipWidget        = new CClipWidget();
            $cClipWidget->beginClip("Chart" . $this->uniqueLayoutId);
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $this->uniqueLayoutId));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart' . $this->uniqueLayoutId];
        }

        protected function renderMaximumGroupsContent()
        {
            $content  = '<div class="a-class-we-can-call-something-else">';
            $content .= Yii::t('Default', 'Your report has too many groups to plot. ' .
                                          'Please adjust the filters to reduce the number below {maximum}.',
                        array('{maximum}' => $this->maximumGroupsPerChart));
            $content .= '</div>';
            return $content;
        }
    }
?>