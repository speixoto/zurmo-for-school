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

    class ProductTemplatesCategoryController extends ZurmoModuleController
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
            $productTemplate                = new ProductCategory(false);
            $searchForm                     = new ProductCategoriesSearchForm($productTemplate);
            $listAttributesSelector         = new ListAttributesSelector('ProductCategoriesListView', get_class($this->getModule()), 'ProductCategory');
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ProductCategoriesSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new ProductCategoriesPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider, 'CategoriesActionBarForSearchAndListView');
                $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $productCategory = static::getModelAndCatchNotFoundAndDisplayError('ProductCategory', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($productCategory);
            $detailsView           = new ProductCategoryDetailsView($this->getId(), $this->getModule()->getId(), $productCategory);
            $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $productCategory = new ProductCategory();
            $productCatalog = ProductCatalog::getByName(ProductCatalog::DEFAULT_NAME);
            $productCategory->productCatalogs->add($productCatalog[0]);
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($productCategory), 'Edit');
            $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $productCategory = ProductCategory::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($productCategory);
            $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,
                                             $this->makeEditAndDetailsView(
                                                 $this->attemptToSaveModelFromPost($productCategory, $redirectUrl), 'Edit')));
            echo $view->render();
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
            $productCategory = new ProductCategory(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductCategoriesSearchForm($productCategory),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductCategoriesSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productCategory = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProductCategoriesPageView',
                $productCategory,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massEditView = $this->makeMassEditView(
                $productCategory,
                $activeAttributes,
                $selectedRecordCount,
                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
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
            $productCategory = new ProductCategory(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductCategoriesSearchForm($productCategory),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductCategoriesSearchView'
            );
            $this->processMassEditProgressSave(
                'ProductCategory',
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
            $productCategory = new ProductCategory(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductCategoriesSearchForm($productCategory),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductCategoriesSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productCategory = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProductCategoriesPageView',
                $productCategory,
                //ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                'Product Categories',
                $dataProvider
            );

            if($productCategory === false)
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule', 'One of the product category selected is  associated to product templates in the system hence could not be deleted'));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
            else
            {
                $massDeleteView = $this->makeMassDeleteView(
                    $productCategory,
                    $activeAttributes,
                    $selectedRecordCount,
                    //ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural')
                    'Product Categories'
                );
                $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                             makeStandardViewForCurrentUser($this, $massDeleteView));
                echo $view->render();
            }
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
            $productCategory = new ProductCategory(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProductCategoriesSearchForm($productCategory),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProductCategoriesSearchView'
            );
            $this->processMassDeleteProgress(
                'ProductCategory',
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
            $productCategory = ProductCategory::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($productCategory);
            if($productCategory->delete())
            {
                $this->redirect(array($this->getId() . '/index'));
            }
            else
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule', 'The product category is associated to product templates in the system hence could not be deleted'));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
        }

        protected static function getSearchFormClassName()
        {
            return 'ProductCategoriesSearchForm';
        }

        public function actionExport()
        {
            $this->export('ProductCategoriesSearchView', 'ProductCategory', 'productCategories');
        }
    }
?>
