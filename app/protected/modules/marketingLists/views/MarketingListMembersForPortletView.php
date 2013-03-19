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

    class MarketingListMembersForPortletView extends ConfigurableMetadataView
                                                                  implements PortletViewInterface
    {
        // TODO: @Shoaibi: Low: refactor this and LatestActivitiesForPortletView, create a parent PortletView Class
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

        protected $marketingListMembersListView;

        protected static $persistantUserPortletConfigs = array(
                'filteredBySubscriptionType',
            );

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = $params['relationModuleId'];
            $this->model          = $params['relationModel'];
            $this->controllerId   = $params['controllerId'];
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'SelectContactAndReportLink',
                                'htmlOptions' => array('class' => 'icon-details'),
                                'listViewGridId' => 'eval:$this->getMarketingListMembersListGridId()'),
                            // TODO: @Shoaibi: High: Change it with https://bitbucket.org/shoaibi/zurmo/src/2bb67d4e4bd2/app/protected/core/elements/actions/MassEditLinkActionElement.php?at=Mobile
                            // TODO: @Shoaibi: High: Break this into 2 buttons
                            array('type'  => 'MarketingListsUpdateLink',
                                'htmlOptions' => array('class' => 'icon-edit'),
                                'listViewGridId' => 'eval:$this->getMarketingListMembersListGridId()'),
                            // TODO: @Shoaibi: High: We need mass delete button here too: https://bitbucket.org/shoaibi/zurmo/src/2bb67d4e4bd2/app/protected/core/elements/actions/MassDeleteLinkActionElement.php?at=Mobile
                            //array('type'  => 'MarketingListsDeleteMembersLink',
                            //    'htmlOptions' => array('class' => 'icon-edit'),
                            //    'listViewGridId' => 'eval:$this->getMarketingListMembersListGridId()'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        // TODO: @Shoaibi/@Amit: Low: Hide the title bar
        public function getTitle()
        {
            return null;
        }

        public function renderContent()
        {
            $actionElementBar       = $this->renderActionElementBar(false);
            $memberSearchAndList    = $this->renderMembersSearchFormAndListContent();
            return ZurmoHtml::tag('div', array('class' => MarketingListDetailsAndRelationsView::MEMBERS_PORTLET_CLASS),
                                                                            $actionElementBar . $memberSearchAndList);
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
                                                array_merge($_GET, array( 'portletId' => $this->params['portletId'],
                                                                            'uniqueLayoutId' => $this->uniqueLayoutId)));
        }

        /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         */
        protected function getNonAjaxRedirectUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/' . $this->controllerId . '/details',
                                                                                    array( 'id' => $this->model->id));
        }

        protected function getMembersSearchUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/' . $this->controllerId . '/membersSearchList');
        }

        protected function renderMembersSearchFormAndListContent()
        {
            $marketingListMembersListContent = $this->getMarketingListMembersListView()->render();
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-members-list'), $marketingListMembersListContent);
        }

        protected function makeMarketingListMembersListView()
        {
            $uniquePageId  = get_called_class();
            $marketingListMembersConfigurationForm = $this->makeMarketingListMembersConfigurationForm();
            $this->resolveMarketingListMembersConfigFormFromRequest($marketingListMembersConfigurationForm);
            $marketingListMembersListViewClassName = $this->getMarketingListMembersListViewClassName();
            $dataProvider = $this->getDataProvider($uniquePageId, $marketingListMembersConfigurationForm);
            return new $marketingListMembersListViewClassName(
                                                            $dataProvider,
                                                            $marketingListMembersConfigurationForm,
                                                            $this->controllerId,
                                                            $this->moduleId,
                                                            $this->getPortletDetailsUrl(),
                                                            $this->getNonAjaxRedirectUrl(),
                                                            $uniquePageId,
                                                            $this->params,
                                                            get_class(Yii::app()->findModule($this->moduleId))
                                                        );
        }

        protected function getMarketingListMembersListView()
        {
            if ($this->marketingListMembersListView === null)
            {
                $this->marketingListMembersListView = $this->makeMarketingListMembersListView();
            }
            return $this->marketingListMembersListView;
        }

        protected function resolveMarketingListMembersConfigFormFromRequest(&$marketingListMembersConfigurationForm)
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($marketingListMembersConfigurationForm)]))
            {
                $marketingListMembersConfigurationForm->setAttributes($_GET[get_class($marketingListMembersConfigurationForm)]);
                $excludeFromRestore = $this->saveUserSettingsFromConfigForm($marketingListMembersConfigurationForm);
            }
            $this->restoreUserSettingsToConfigFrom($marketingListMembersConfigurationForm, $excludeFromRestore);
        }

        protected function saveUserSettingsFromConfigForm(&$marketingListMembersConfigurationForm)
        {
            $savedConfigs = array();
            foreach (static::$persistantUserPortletConfigs as $persistantUserConfigItem)
            {
                if ($marketingListMembersConfigurationForm->$persistantUserConfigItem !==
                    MarketingListMembersPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                            $this->params['portletId'],
                                                                                            $persistantUserConfigItem)
                                                                                        )
                {
                    MarketingListMembersPortletPersistentConfigUtil::setForCurrentUserByPortletIdAndKey(
                                                        $this->params['portletId'],
                                                        $persistantUserConfigItem,
                                                        $marketingListMembersConfigurationForm->$persistantUserConfigItem
                                                        );
                    $savedConfigs[] = $persistantUserConfigItem;
                }
            }
            return $savedConfigs;
        }

        protected function restoreUserSettingsToConfigFrom(&$marketingListMembersConfigurationForm, $excludeFromRestore)
        {
            foreach (static::$persistantUserPortletConfigs as $persistantUserConfigItem)
            {
                if (in_array($persistantUserConfigItem, $excludeFromRestore))
                {
                    continue;
                }
                else
                {
                    $persistantUserConfigItemValue = MarketingListMembersPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                            $this->params['portletId'],
                                                                                            $persistantUserConfigItem
                                                                                            );
                    if(isset($persistantUserConfigItemValue))
                    {
                        $marketingListMembersConfigurationForm->$persistantUserConfigItem = $persistantUserConfigItemValue;
                    }
                }
            }
            return $marketingListMembersConfigurationForm;
        }

        protected function makeMarketingListMembersConfigurationForm()
        {
            return new MarketingListMembersConfigurationForm();
        }

        protected function getMarketingListMembersListViewClassName()
        {
            return 'MarketingListMembersListView';
        }

        protected function getMarketingListMembersListGridId()
        {
            return $this->getMarketingListMembersListView()->getGridViewId();
        }

        protected function getDataProvider($uniquePageId, $form)
        {
            assert('is_string($uniquePageId)');
            assert('$form instanceOf MarketingListMembersConfigurationForm');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            $searchAttributes   = MarketingListMembersUtil::makeSearchAttributeData($this->model->id,
                                                                                $form->filteredBySubscriptionType,
                                                                                $form->filteredBySearchTerm);
            $sortAttributes     = MarketingListMembersUtil::makeSortAttributeData();
            return new RedBeanModelsDataProvider($uniquePageId,
                                                    $sortAttributes,
                                                    true,
                                                    $searchAttributes,
                                                    array('pagination' => array('pageSize' => $pageSize))
                                                );
        }
    }
?>