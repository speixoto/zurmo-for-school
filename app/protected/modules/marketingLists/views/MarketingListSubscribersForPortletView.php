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

    class MarketingListSubscribersForPortletView extends ConfigurableMetadataView
                                                                  implements PortletViewInterface
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

        // TODO: @Shoaibi: We would need a view here like LatestActivitiesListView
        // abstract protected function getMarketingListSubscribersViewClassName();

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            // TODO: @Shoaibi do we need these assertions?
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = $params['relationModuleId'];
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                // TODO: @Shoaibi: What do we send as listViewGridId? This is definitely wrong.
                // TODO: @Shoaibi: We need mass delete button here too.
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'SelectContactAndReportLink',
                                'htmlOptions' => array('class' => 'icon-details'),
                                'listViewGridId' => MarketingListDetailsView::SUBSCRIBERS_PORTLET_CLASS),
                            array('type'  => 'MarketingListsUpdateLink',
                                'htmlOptions' => array('class' => 'icon-edit'),
                                'listViewGridId' => MarketingListDetailsView::SUBSCRIBERS_PORTLET_CLASS),
                            array('type'  => 'MarketingListsDeleteSubscribersLink', // TODO: @Shoaibi: implement
                                'htmlOptions' => array('class' => 'icon-edit'),
                                'listViewGridId' => MarketingListDetailsView::SUBSCRIBERS_PORTLET_CLASS),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            return Zurmo::t('MarketingListsModule', 'Subscribers');
        }

        public function renderContent()
        {
            $content     = $this->renderActionElementBar(false);
            $content    .= $this->renderSubscriberSearchFormAndListing();
            return $content;
        }

        public static function canUserConfigure()
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
            return 'MarketingListsModule';
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

        protected function renderSubscriberSearchFormAndListing()
        {
            $listingAndSearchForm = $this->renderSubscriberSearchForm() . $this->renderSubscriberListing();
            return ZurmoHtml::tag('div', array('class' => 'marketing-listing-subscribers-content'), $listingAndSearchForm);
        }

        protected function renderSubscriberSearchForm()
        {
            // TODO: @Shoaibi: Implement
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-subscribers-search-form'), 'Subscriber Search Form goes here');
        }

        protected function renderSubscriberListing()
        {
            // TODO: @Shoaibi: Implement
            // TODO: @Shoaibi: How would we add the nice filters here?
            // TODO: @Shoaibi: This will call an external view with dataprovider and etc to actually generate the grid using a for loop
            // TODO: @Shoaibi: @see LatestActivtiesForPortletView::renderLatestActivitiesContent()
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-subscribers-list'), 'Subscriber List goes here');
        }

    }
?>