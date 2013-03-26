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

    class ProductTemplatesBundleItemController extends ZurmoModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $productTemplateBundleItem      = new ProductTemplateBundleItem(false);
            $searchForm                     = new ProductTemplateBundleItemSearchForm($productTemplateBundleItem);
            $listAttributesSelector         = new ListAttributesSelector('ProductTemplateBundleItemsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ProductTemplateBundleItemsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new ProductTemplateBundleItemsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider);
                $view = new ProductTemplateBundleItemsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $productTemplateBundle = static::getModelAndCatchNotFoundAndDisplayError('ProductTemplateBundle', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($productTemplateBundle);
            $breadCrumbView          = StickySearchUtil::resolveBreadCrumbViewForDetailsControllerAction($this, 'ProductTemplateBundlesSearchView', $productTemplateBundle);
            $detailsAndRelationsView = $this->makeDetailsAndRelationsView($productTemplateBundle, 'ProductTemplatesModule',
                                                                          'ProductTemplateBundleDetailsAndRelationsView',
                                                                          Yii::app()->request->getRequestUri(),
                                                                          $breadCrumbView);
            $view = new ProductTemplateBundlesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new ProductTemplateBundleItem()), 'Edit');
            $view = new ProductTemplateBundleItemsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $productTemplateBundle = ProductTemplateBundle::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($productTemplateBundle);
            $view = new ProductTemplateBundlesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,
                                             $this->makeEditAndDetailsView(
                                                 $this->attemptToSaveModelFromPost($productTemplateBundle, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        protected static function getZurmoControllerUtil()
        {
            return new ProductTemplateBundleZurmoControllerUtil('productTemplateBundleItems', 'ProductTemplateBundleItemForm',
                                                            'ProductTemplateBundleCategoriesForm');
        }

        /**
         * Action for displaying a mass edit form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to update is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassEditProgressView.
         * In the mass edit progress view, a javascript refresh will take place that will call a refresh
         * action, usually massEditProgressSave.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the updated records.
         * @see Controler->makeMassEditProgressView
         * @see Controller->processMassEdit
         * @see
         */
        public function actionMassEdit()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $productTemplate = new ProductTemplate(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductTemplatesSearchForm($productTemplate),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductTemplatesSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productTemplate = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProductTemplatesPageView',
                $productTemplate,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massEditView = $this->makeMassEditView(
                $productTemplate,
                $activeAttributes,
                $selectedRecordCount,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProductTemplatesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massEditView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass edit quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been updated and continues to be
         * called until the mass edit action is complete.  For example, if there are 20 records to update
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassEdit is called upon the initial form submission.
         */
        public function actionMassEditProgressSave()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $productTemplate = new ProductTemplate(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductTemplatesSearchForm($productTemplate),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductTemplatesSearchView'
            );
            $this->processMassEditProgressSave(
                'ProductTemplate',
                $pageSize,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        /**
         * Action for displaying a mass delete form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to delete is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassDeleteProgressView.
         * In the mass delete progress view, a javascript refresh will take place that will call a refresh
         * action, usually makeMassDeleteProgressView.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the delete records.
         * @see Controller->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $productTemplate = new ProductTemplate(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductTemplatesSearchForm($productTemplate),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductTemplatesSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productTemplate = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProductTemplatesPageView',
                $productTemplate,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massDeleteView = $this->makeMassDeleteView(
                $productTemplate,
                $activeAttributes,
                $selectedRecordCount,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProductTemplatesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been delted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $productTemplate = new ProductTemplate(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductTemplatesSearchForm($productTemplate),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductTemplatesSearchView'
            );
            $this->processMassDeleteProgress(
                'ProductTemplate',
                $pageSize,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider);
        }

        public function actionDelete($id)
        {
            $productTemplate = ProductTemplate::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($productTemplate);
            $productTemplate->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        protected static function getSearchFormClassName()
        {
            return 'ProductTemplatesSearchForm';
        }

        public function actionExport()
        {
            $this->export('ProductTemplatesSearchView');
        }

        public function actionAutoCompleteAllProductCategoriesForMultiSelectAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = self::resolveProductCategoryStateAdapterByModulesUserHasAccessTo('ProductTemplatesModule',
                                                                                        'ProductTemplatesModule',
                                                                                         Yii::app()->user->userModel);
            if ($adapterName === false)
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $productCategories = self::getProductCategoriesByPartialName($term, $pageSize, $adapterName);
            $autoCompleteResults  = array();
            foreach ($productCategories as $productCategory)
            {
                $autoCompleteResults[] = array(
                    'id'   => $productCategory->id,
                    'name' => self::renderHtmlContentLabelFromProductCategoryAndKeyword($productCategory, $term)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        public static function getProductCategoriesByPartialName($partialName, $pageSize, $stateMetadataAdapterClassName = null)
        {
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('ProductCategory');
            $metadata = array('clauses' => array(), 'structure' => '');
            if ($stateMetadataAdapterClassName != null)
            {
                $stateMetadataAdapter = new $stateMetadataAdapterClassName($metadata);
                $metadata = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                $metadata['structure'] = '(' . $metadata['structure'] . ')';
            }
            $where  = RedBeanModelDataProvider::makeWhere('ProductCategory', $metadata, $joinTablesAdapter);
            if ($where != null)
            {
                $where .= 'and';
            }
            $where .= self::getWherePartForPartialNameSearchByPartialName($partialName);
            return ProductCategory::getSubset($joinTablesAdapter, null, $pageSize, $where, "productcategory.name");
        }

        protected static function getWherePartForPartialNameSearchByPartialName($partialName)
        {
            assert('is_string($partialName)');
            return "      (productcategory.name      like '$partialName%') ";
        }

        public static function renderHtmlContentLabelFromProductCategoryAndKeyword($productCategory, $keyword)
        {
            assert('$productCategory instanceof ProductCategory && $productCategory->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if ($productCategory->name != null)
            {
                return strval($productCategory) . '&#160&#160<b>'. '</b>';
            }
            else
            {
                return strval($productCategory);
            }
        }

        public static function resolveProductCategoryStateAdapterByModulesUserHasAccessTo( $moduleClassNameFirstStates,
                                                                                    $moduleClassNameLaterStates,
                                                                                    $user)
        {
            assert('is_string($moduleClassNameFirstStates)');
            assert('is_string($moduleClassNameLaterStates)');
            assert('$user instanceof User && $user->id > 0');
            $canAccessFirstStatesModule  = RightsUtil::canUserAccessModule($moduleClassNameFirstStates, $user);
            $canAccessLaterStatesModule = RightsUtil::canUserAccessModule($moduleClassNameLaterStates, $user);
            if ($canAccessFirstStatesModule && $canAccessLaterStatesModule)
            {
                return null;
            }
            elseif (!$canAccessFirstStatesModule && $canAccessLaterStatesModule)
            {
                $prefix = substr($moduleClassNameLaterStates, 0, strlen($moduleClassNameLaterStates) - strlen('Module'));
                return $prefix . 'StateMetadataAdapter';
            }
            elseif ($canAccessFirstStatesModule && !$canAccessLaterStatesModule)
            {
                $prefix = substr($moduleClassNameFirstStates, 0, strlen($moduleClassNameFirstStates) - strlen('Module'));
                return $prefix . 'StateMetadataAdapter';
            }
            else
            {
                return false;
            }
        }
    }
?>
