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
     * Social items list view.
     */
    class SocialItemsListView extends ListView
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * Ajax url to use after actions are completed from the user interface for a portlet.
         * @var string
         */
        protected $portletDetailsUrl;

        /**
         * The url to use as the redirect url when going to another action. This will return the user
         * to the correct page upon canceling or completing an action.
         * @var string
         */
        public $redirectUrl;

        /**
         * Unique identifier used to identify this view on the page.
         * @var string
         */
        protected $uniquePageId;

        protected $params;

        /**
         * Associated moduleClassName of the containing view.
         * @var string
         */
        protected $containerModuleClassName;

        public function __construct(RedBeanModelDataProvider $dataProvider,
                                    $controllerId,
                                    $moduleId,
                                    $portletDetailsUrl,
                                    $redirectUrl,
                                    $uniquePageId,
                                    $params,
                                    $containerModuleClassName)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($portletDetailsUrl)');
            assert('is_string($redirectUrl)');
            assert('is_string($uniquePageId)');
            assert('is_array($params)');
            assert('is_string($containerModuleClassName)');
            $this->dataProvider             = $dataProvider;
            $this->controllerId             = $controllerId;
            $this->moduleId                 = $moduleId;
            $this->portletDetailsUrl        = $portletDetailsUrl;
            $this->redirectUrl              = $redirectUrl;
            $this->uniquePageId             = $uniquePageId;
            $this->gridIdSuffix             = $uniquePageId;
            $this->gridId                   = 'list-view';
            $this->params                   = $params;
            $this->containerModuleClassName = $containerModuleClassName;
        }

        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ListView'] . "\n";
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}" . $preloader;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'SocialItemAndComments'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),

            );
            return $metadata;
        }

        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('hideHeader' => true));
        }

        protected function getCGridViewLastColumn()
        {
            return array();
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'class'            => 'SimpleListLinkPager',
                    'paginationParams' => array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId'])),
                    'route'            => 'defaultPortlet/details',
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

        public function isUniqueToAPage()
        {
            return false;
        }

        public function getContainerModuleClassName()
        {
            return $this->containerModuleClassName;
        }
    }
?>