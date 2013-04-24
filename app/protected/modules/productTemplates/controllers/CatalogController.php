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

    class ProductTemplatesCatalogController extends ZurmoModuleController
    {
        public function filters()
        {
	    $modelClassName		= 'ProductCatalog';
            $viewClassName		= $modelClassName . 'EditAndDetailsView';
	    $pageViewClassName		= 'ProductCatalogsPageView';
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
			'modelClassName'	     => $modelClassName,
			'pageViewClassName'	     => $pageViewClassName
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $productCatalog                 = new ProductCatalog(false);
            $searchForm                     = new ProductCatalogsSearchForm($productCatalog);
            $listAttributesSelector         = new ListAttributesSelector('ProductCatalogsListView', get_class($this->getModule()), 'ProductCatalog');
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ProductCatalogsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view	   = new ProductCatalogsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider, 'CatalogsActionBarForSearchAndListView');
                $view	   = new ProductCatalogsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $productCatalog	   = static::getModelAndCatchNotFoundAndDisplayError('ProductCatalog', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($productCatalog);
            $detailsView           = new ProductCatalogDetailsView($this->getId(), $this->getModule()->getId(), $productCatalog);
            $view                  = new ProductCatalogsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new ProductCatalog()), 'Edit');
            $view		= new ProductCatalogsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $productCatalog = ProductCatalog::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($productCatalog);
            $view = new ProductCatalogsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,
                                             $this->makeEditAndDetailsView(
                                                 $this->attemptToSaveModelFromPost($productCatalog, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $productCatalog = ProductCatalog::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($productCatalog);
            if($productCatalog->delete())
            {
                $this->redirect(array($this->getId() . '/index'));
            }
            else
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule', 'The product catalog is associated to categories in the system hence could not be deleted'));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
        }

        protected static function getSearchFormClassName()
        {
            return 'ProductCatalogsSearchForm';
        }

    }
?>