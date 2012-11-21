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
     * A chart view for displaying a chart showing all opportunities by source.
     *
     */
    class OpportunitiesBySourceChartView extends ChartView implements PortletViewInterface
    {
        public function renderContent()
        {
            $accessContent = $this->resolveContentIfCurrentUserCanAccessChartByModule(
                                        'OpportunitiesModule', 'OpportunitiesModulePluralLabel');
            if ($accessContent != null)
            {
                return $accessContent;
            }
            $chartDataProviderType = $this->getChartDataProviderType();
            $chartDataProvider     = ChartDataProviderFactory::createByType($chartDataProviderType);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule(
                                        $chartDataProvider->getModel()->getModuleClassName(), true);
            $chartData = $chartDataProvider->getChartData();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data = $chartData;
            $amChart->id =  $this->uniqueLayoutId;
            $amChart->type = $this->resolveViewAndMetadataValueByName('type');
            $amChart->addSerialGraph('value', 'column');
            $amChart->xAxisName = $chartDataProvider->getXAxisName();
            $amChart->yAxisName = $chartDataProvider->getYAxisName();
            $javascript = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->uniqueLayoutId, $javascript);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('application.core.widgets.AmChart', array(
                    'id'        => $this->uniqueLayoutId,
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }

        public static function getDefaultMetadata()
        {
            return array(
                'perUser' => array(
                    'title' => "eval:Yii::t('Default', 'Opportunities By Lead Source', LabelUtil::getTranslationParamsForAllModules())",
                    'type'  => 'Pie3D',
                ),
                'global' => array(
                ),
            );
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'Chart';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'OpportunitiesModule';
        }

        public function getChartDataProviderType()
        {
            return 'OpportunitiesBySource';
        }
    }
?>