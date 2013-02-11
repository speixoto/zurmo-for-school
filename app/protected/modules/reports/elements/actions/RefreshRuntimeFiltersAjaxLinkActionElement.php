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
     * A class used to render a refresh ajax link for the report detail view
     */
    class RefreshRuntimeFiltersAjaxLinkActionElement extends AjaxLinkActionElement
    {
        /**
         * @param $controllerId
         * @param $moduleId
         * @param $modelId
         * @param array $params
         */
        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            $params['htmlOptions'] = array('id' => 'reset-runtime-filters', 'class'  => 'attachLoading z-button white-button');
            parent::__construct($controllerId, $moduleId, $modelId, $params);
        }

        /**
         * @return null
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Reset');
        }

        /**
         * @return mixed
         */
        protected function getDefaultRoute()
        {
            return Yii::app()->createUrl('reports/default/resetRuntimeFilters/',
                                         array('id' => $this->modelId )
            );
        }

        /**
         * @return string
         */
        protected function getLabel()
        {
            $content  = ZurmoHtml::tag('span', array('class' => 'z-spinner'), null);
            $content .= ZurmoHtml::tag('span', array('class' => 'z-icon'), null);
            $content .= ZurmoHtml::tag('span', array('class' => 'z-label'), $this->getDefaultLabel());
            return $content;
        }

        /**
         * @return array
         */
        protected function getAjaxOptions()
        {
            return array(
                    'beforeSend' => 'js:function(){
                                        attachLoadingSpinner("reset-runtime-filters", true);
                                        $("#reset-runtime-filters").addClass("attachLoadingTarget");
                                        $("#reset-runtime-filters").addClass("loading");
                                        $("#reset-runtime-filters").addClass("loading-ajax-submit");
                                    } ',
                    'success'    => 'js:function(){
                                        $("#RuntimeFiltersForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportResultsGridForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportChartForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportSQLForPortletView").find(".refreshPortletLink").click();
                                    }');
        }
    }
?>