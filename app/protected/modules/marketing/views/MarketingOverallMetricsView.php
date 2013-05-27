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
     * Base class used for displaying the overall performance metrics for the marketing dashboard
     */
    class MarketingOverallMetricsView extends ConfigurableMetadataView implements PortletViewInterface
    {
        /**
         * Portlet parameters passed in from the portlet.
         * @var array
         */
        protected $params;

        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $uniqueLayoutId;

        protected $viewData;

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = 'home';
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public function renderPortletHeadContent()
        {
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('MarketingModule', 'Overall Metrics')",
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            $title  = Zurmo::t('MarketingModule', 'Marketing Dashboard');
            return $title;
        }

        public function renderContent()
        {
            $content  = ZurmoHtml::tag('h3', array(), Zurmo::t('MarketingModule', 'What is going on with Marketing?'));
            $content .= $this->renderConfigureElementsContent();
            $content .= $this->renderMetricsWrapperContent();
            return $content;
        }

        protected function renderConfigureElementsContent()
        {
            return 'date picker, and grouping picker';
        }

        protected function renderMetricsWrapperContent()
        {
            $content  = ZurmoHtml::tag('div', array(), $this->renderOverallListPerformanceContent());
            $content .= ZurmoHtml::tag('div', array(), $this->renderEmailsInThisListContent());
            $content .= ZurmoHtml::tag('div', array(), $this->renderListGrowthContent());
            return $content;
        }

        protected function renderOverallListPerformanceContent()
        {
            $content  = ZurmoHtml::tag('h3', array(), Zurmo::t('MarketingModule', 'Overall List Performance'));
            $content .= 'todo chart';
            return $content;
        }

        protected function renderEmailsInThisListContent()
        {
            $content  = ZurmoHtml::tag('h3', array(), Zurmo::t('MarketingModule', 'Emails in this list'));
            $content .= 'todo chart';
            return $content;
        }

        protected function renderListGrowthContent()
        {
            $content  = ZurmoHtml::tag('h3', array(), Zurmo::t('MarketingModule', 'List Growth'));
            $content .= 'todo chart';
            return $content;
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/details',
                array_merge($_GET, array( 'portletId' =>
                $this->params['portletId'],
                    'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

        /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         */
        protected function getNonAjaxRedirectUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/default/details',
                array( 'id' => $this->params['relationModel']->id));
        }

        public static function canUserConfigure()
        {
            return false;
        }

        public static function canUserRemove()
        {
            return false;
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'ModelDetails';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'MarketingModule';
        }
    }
?>