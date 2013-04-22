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

    abstract class ProductsRelatedListView extends SecuredRelatedListView
    {
        /**
         * Form that has the information for how to display the latest products view.
         * @var object LatestActivitiesConfigurationForm
         */
        protected $configurationForm;

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

        /**
         * True to show the owned by filter option.
         * @var boolean
         */
        protected $showView = true;

        protected $params;

        protected static $persistantUserPortletConfigs = array(
            'view',
        );

        public static function getDefaultMetadata()
        {
	    $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('ProductsModule', 'ProductsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()'),
                        ),
                    ),
                    'rowMenu' => array(
                        'elements' => array(
                            array('type'                      => 'RelatedUnlink',
                                  'relationModelClassName'    => 'eval:get_class($this->params["relationModel"])',
                                  'relationModelId'           => 'eval:$this->params["relationModel"]->id',
                                  'relationModelRelationName' => 'products',
                                  'userHasRelatedModelAccess' => 'eval:ActionSecurityUtil::canCurrentUserPerformAction( "Edit", $this->params["relationModel"])')
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'FullName',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_NORMAL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
				array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'quantity', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
				array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'sellPrice', 'type' => 'CurrencyValue'),
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

        public static function getModuleClassName()
        {
            return 'ProductsModule';
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}" . $preloader;
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

        protected function renderConfigurationForm()
        {
            $formName   = 'product-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id'	    => $formName,
		    'htmlOptions'   => array('style' => 'display:none')
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        protected function renderConfigurationFormLayout($form)
        {
            $this->uniquePageId = 'OpportunityProductsForPortletView';

            $mashableModelClassNamesAndDisplayLabels = LatestActivitiesUtil::getMashableModelDataForCurrentUser(false);

            $this->configurationForm = new ProductsConfigurationForm();
            $this->configurationForm->mashableModelClassNamesAndDisplayLabels = $mashableModelClassNamesAndDisplayLabels;

            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $innerContent = null;
	    $content = $this->renderAddProductContent($form);
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
	    Yii::app()->clientScript->registerScript('addProductPortletAction', "
            $('#ProductsConfigurationForm_view_2').click(function()
                {
                   $('#ProductsConfigurationForm_name').show();
                }
            );

            ");
        }
    }
?>