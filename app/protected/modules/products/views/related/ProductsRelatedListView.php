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
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * Form that has the information for how to display the latest activity view.
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
                            array('type'		      => 'EditLink'),
                            array('type'                      => 'RelatedDeleteLink'),
                            array('type'                      => 'RelatedUnlink',
                                  'relationModelClassName'    => 'eval:get_class($this->params["relationModel"])',
                                  'relationModelId'           => 'eval:$this->params["relationModel"]->id',
                                  'relationModelRelationName' => 'products',
                                  'userHasRelatedModelAccess' => 'eval:ActionSecurityUtil::canCurrentUserPerformAction( "Edit", $this->params["relationModel"])'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'FullName',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
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

        protected function renderContent()
        {
            $content  = $this->renderConfigurationForm();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            return $content;
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
                    'id' => $formName,
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
            if ($this->showView)
            {
                $element                   = new ProductsViewFilterRadioElement($this->configurationForm,
                                                                                          'view',
                                                                                          $form);
                $element->editableTemplate =  '<div id="ProductsConfigurationForm_view_area">{content}</div>';
                $viewContent		   = $element->render();
                $innerContent             .= $viewContent;
            }
            if ($innerContent != null)
            {
                $content .= '<div class="horizontal-line latest-activity-toolbar">';
                $content .= $innerContent;
                $content .= '</div>' . "\n";
            }
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
//            $this->uniquePageId = 'list-viewOpportunityDetailsAndRelationsViewRightBottomView_71';
//            $productsConfigurationForm = new ProductsConfigurationForm();
//            $mashableModelClassNamesAndDisplayLabels = LatestActivitiesUtil::getMashableModelDataForCurrentUser(false);
//            $productsConfigurationForm->mashableModelClassNamesAndDisplayLabels =
//                $mashableModelClassNamesAndDisplayLabels;
//            $this->resolveProductsConfigFormFromRequest($productsConfigurationForm);
//            $dataProvider = $this->getDataProvider($this->uniquePageId, $productsConfigurationForm);
//            $this->dataProvider = $dataProvider;
//
//            $urlScript = 'js:$.param.querystring("' . $this->portletDetailsUrl . '", "' .
//                         $this->dataProvider->getPagination()->pageVar . '=1")'; // Not Coding Standard
//            $ajaxSubmitScript = ZurmoHtml::ajax(array(
//                    'type'       => 'GET',
//                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
//                    'url'        =>  $urlScript,
//                    'update'     => '#' . $this->uniquePageId,
//                    'beforeSend' => 'js:function(){makeSmallLoadingSpinner("' . $this->getGridViewId() . '"); $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
//                    'complete'   => 'js:function()
//                    {
//                                        $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
//                    }'
//            ));
//            Yii::app()->clientScript->registerScript($this->uniquePageId, "
//            $('#ProductsConfigurationForm_view_area').change(function()
//                {
//                    " . $ajaxSubmitScript . "
//                }
//            );
//            ");

	    Yii::app()->clientScript->registerScript('addProductPortletAction', "
            $('#ProductsConfigurationForm_view_2').click(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }

//        protected function resolveProductsConfigFormFromRequest(&$productsConfigurationForm)
//        {
//            $excludeFromRestore = array();
//            if (isset($_GET[get_class($productsConfigurationForm)]))
//            {
//                $productsConfigurationForm->setAttributes($_GET[get_class($productsConfigurationForm)]);
//                $excludeFromRestore = $this->saveUserSettingsFromConfigForm($productsConfigurationForm);
//            }
//            $this->restoreUserSettingsToConfigFrom($productsConfigurationForm, $excludeFromRestore);
//        }
//
//        protected function restoreUserSettingsToConfigFrom(&$productsConfigurationForm, $excludeFromRestore)
//        {
//            foreach (static::$persistantUserPortletConfigs as $persistantUserConfigItem)
//            {
//                if (in_array($persistantUserConfigItem, $excludeFromRestore))
//                {
//                    continue;
//                }
//                $persistantUserConfigItemValue = LatestActivitiesUtil::getPersistentConfigForCurrentUserByPortletIdAndKey(
//                    $this->params['portletId'],
//                    $persistantUserConfigItem);
//                if(isset($persistantUserConfigItemValue))
//                {
//                    $productsConfigurationForm->$persistantUserConfigItem = $persistantUserConfigItemValue;
//                }
//            }
//            return $productsConfigurationForm;
//        }
    }
?>