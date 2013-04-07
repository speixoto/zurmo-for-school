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
            $breadcrumbLinks = array();
            $actionBarAndTreeView = new CategoriesActionBarAndTreeListView(
                $this->getId(),
                $this->getModule()->getId(),
                ProductCategory::getAll('name')
            );
            $view = new ProductCategoriesPageView(ZurmoDefaultViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $actionBarAndTreeView, $breadcrumbLinks, 'ProductCategoryBreadCrumbView'));
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
	    if(!empty($productCatalog))
	    {
		$productCategory->productCatalogs->add($productCatalog[0]);
	    }
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

        //selecting
        public function actionModalParentList()
        {
            echo $this->renderModalList(
                'SelectParentCategoryModalTreeListView', Zurmo::t('ProductTemplatesModule', 'Select a Parent Category'));
        }

        public function actionModalList()
        {
            echo $this->renderModalList(
                'ProductCategoriesModalTreeListView', Zurmo::t('ProductTemplatesModule', 'Select a category'));
        }

        protected function renderModalList($modalViewName, $pageTitle)
        {
            $rolesModalTreeView = new $modalViewName(
                $this->getId(),
                $this->getModule()->getId(),
                $_GET['modalTransferInformation']['sourceModelId'],
                ProductCategory::getAll('name'),
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, $rolesModalTreeView);
            return $view->render();
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
    }
?>
