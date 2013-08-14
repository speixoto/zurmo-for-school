<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * The base View for a module's related list view.
     */
    abstract class RelatedListView extends ListView implements PortletViewInterface, RelatedPortletViewInterface
    {
        protected $params;
        protected $viewData;
        protected $uniqueLayoutId;

        /**
         * Override so viewToolbar renders during renderPortletHeadContent instead of renderContent
         * @var bool
         */
        protected $renderViewToolBarDuringRenderContent = false;

        /**
         * Signal to use ExtendedGridView
         * @var integer
         */
        const GRID_VIEW_TYPE_NORMAL  = 1;

        /**
         * Signal to use StackedExtendedGridView
         * @var integer
         */
        const GRID_VIEW_TYPE_STACKED = 2;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('$params["relationModel"] instanceof RedBeanModel || $params["relationModel"] instanceof ModelForm');
            assert('isset($params["portletId"])');
            assert('isset($params["redirectUrl"])');
            assert('$this->getRelationAttributeName() != null');
            $this->modelClassName    = $this->getModelClassName();
            $this->viewData          = $viewData;
            $this->params            = $params;
            $this->uniqueLayoutId    = $uniqueLayoutId;
            $this->gridIdSuffix      = $uniqueLayoutId;
            $this->rowsAreSelectable = false;
            $this->gridId            = 'list-view';
            $this->controllerId      = $this->resolveControllerId();
            $this->moduleId          = $this->resolveModuleId();
        }

        public function getPortletParams()
        {
            return array();
        }

        protected function getShowTableOnEmpty()
        {
            return false;
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}" . $preloader;
        }

        protected function getEmptyText()
        {
            $moduleClassName = static::getModuleClassName();
            $moduleLabel     = $moduleClassName::getModuleLabelByTypeAndLanguage('PluralLowerCase');
            return Zurmo::t('Core', 'No {moduleLabelPluralLowerCase} found', array('{moduleLabelPluralLowerCase}' => $moduleLabel));
        }

        protected function getGridViewWidgetPath()
        {
            $resolvedMetadata = $this->getResolvedMetadata();
            if (isset($resolvedMetadata['global']['gridViewType']) &&
                     $resolvedMetadata['global']['gridViewType'] == RelatedListView::GRID_VIEW_TYPE_STACKED)
             {
                 return 'application.core.widgets.StackedExtendedGridView';
             }

            return parent::getGridViewWidgetPath();
        }

        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => $this->getRelationAttributeName(),
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->id,
                )
            );
            $searchAttributeData['structure'] = '1';
            return $searchAttributeData;
        }

        protected function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( $this->modelClassName, null, false,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }

        public function renderPortletHeadContent()
        {
            return $this->renderViewToolBar();
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function getCreateLinkRouteParameters()
        {
            return array(
                'relationAttributeName' => $this->getRelationAttributeName(),
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'firstPageLabel' => '<span>first</span>',
                    'prevPageLabel'  => '<span>previous</span>',
                    'nextPageLabel'  => '<span>next</span>',
                    'lastPageLabel'  => '<span>last</span>',
                    'class'          => 'SimpleListLinkPager',
                    'paginationParams' => array_merge(GetUtil::getData(),
                                            array('portletId'   => $this->params['portletId'])),
                    'route'         => 'defaultPortlet/details',
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        public function getTitle()
        {
            if (!empty($this->viewData['title']))
            {
                return $this->viewData['title'];
            }
            else
            {
                return static::getDefaultTitle();
            }
        }

        public static function getDefaultTitle()
        {
            $metadata = self::getMetadata();
            $title    = $metadata['perUser']['title'];
            MetadataUtil::resolveEvaluateSubString($title);
            return $title;
        }

        public static function canUserConfigure()
        {
            return false;
        }

        public static function getDesignerRulesType()
        {
            return 'RelatedListView';
        }

        /**
         * Override to add a display description.  An example would be 'Contacts for Account'.  This display description
         * can then be used by external classes interfacing with the view in order to display information to the user in
         * the user interface.
         */
        public static function getDisplayDescription()
        {
            return null;
        }

        public function getModelClassName()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getPrimaryModelName();
        }

        /**
         * What kind of PortletRules this view follows.
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'RelatedList';
        }

        /**
         * Controller Id for the link to models from rows in the grid view.
         */
        protected function resolveControllerId()
        {
            return 'default';
        }

        /**
         * Module Id for the link to models from rows in the grid view.
         */
        private function resolveModuleId()
        {
            $moduleClassName = $this->getActionModuleClassName();
            return $moduleClassName::getDirectoryName();
        }

        /**
         * Module class name for models linked from rows in the grid view.
         */
        protected function getActionModuleClassName()
        {
            $calledClass = get_called_class();
            return $calledClass::getModuleClassName();
        }

        protected function getDataProvider()
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProviderBySearchAttributeData($this->makeSearchAttributeData());
            }
            return $this->dataProvider;
        }

        abstract protected function getRelationAttributeName();

        public static function getAllowedOnPortletViewClassNames()
        {
            return array();
        }

        public static function allowMultiplePlacement()
        {
            return false;
        }
    }
?>
