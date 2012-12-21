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
     * Zurmo Modules such as Accounts, Contacts, and Opportunities
     * should extend this class to provide generic functionality
     * that is applicable to all standard modules.
     */
    abstract class ZurmoModuleController extends ZurmoBaseController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH = 'application.modules.zurmo.controllers.filters.ZeroModelsCheckControllerFilter';

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionLoadSavedSearch($id, $redirectAction = 'list')
        {
            $savedSearch = SavedSearch::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedSearch);
            $getParams   = unserialize($savedSearch->serializedData);
            $getParams   = array_merge($getParams, array('savedSearchId' => $id));
            $url         = Yii::app()->createUrl($this->getModule()->getId() . '/' . $this->getId() . '/' .
                                                 $redirectAction, $getParams);
            $this->redirect($url);
        }

        /**
         * In a detailview, if you click the 'select' link from a sub view, this action is called. It will bring a modal
         * search/list view to select a model from.
         * @param string $portletId
         * @param string $uniqueLayoutId
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         * @param string $pageTitle
         */
        public function actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $stateMetadataAdapterClassName = null)
        {
            $portlet = Portlet::getById((int)$portletId);
            $modalListLinkProvider = new SelectFromRelatedListModalListLinkProvider(
                                            $relationAttributeName,
                                            (int)$relationModelId,
                                            $relationModuleId,
                                            $portlet->getUniquePortletPageId(),
                                            $uniqueLayoutId,
                                            (int)$portlet->id,
                                            $this->getModule()->getId()
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, $stateMetadataAdapterClassName);
        }

        public function actionAutoComplete($term)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            echo $this->renderAutoCompleteResults($modelClassName, $term);
        }

        protected function renderAutoCompleteResults($modelClassName, $term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ModelAutoCompleteUtil::getByPartialName($modelClassName, $term, $pageSize);
            return CJSON::encode($autoCompleteResults);
        }

        /**
         * Override to implement.
         */
        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            throw new NotImplementedException();
        }

        /**
         * @see actionCreateFromRelation. When a new model is instantiated, this method attaches a relation based
         * on the relation information specified.
         * @return $model;
         */
        protected function resolveNewModelByRelationInformation(    $model, $relationAttributeName,
                                                                    $relationModelId, $relationModuleId)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($relationAttributeName)');
            assert('is_int($relationModelId)');
            assert('is_string($relationModuleId)');
            $relationType = $model->getRelationType($relationAttributeName);
            if ($relationType == RedBeanModel::HAS_ONE || RedBeanModel::HAS_ONE_BELONGS_TO)
            {
                $relationModel                   = $model->$relationAttributeName;
                $model->$relationAttributeName = $relationModel::getById((int)$relationModelId);
            }
            else
            {
                $relationModelClassName          = Yii::app()->getModule($relationModuleId)->getPrimaryModelName();
                $relatedModel                    = $relationModelClassName::getById($relationModelId);
                $model->$relationAttributeName->add($relatedModel);
            }
            return $model;
        }

        public function actionAuditEventsModalList($id)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            $model = $modelClassName::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            $searchAttributeData = AuditEventsListControllerUtil::makeSearchAttributeDataByAuditedModel($model);
            $dataProvider = AuditEventsListControllerUtil::makeDataProviderBySearchAttributeData($searchAttributeData);
            Yii::app()->getClientScript()->setToAjaxMode();
            echo AuditEventsListControllerUtil::renderList($this, $dataProvider);
        }

        protected function getModelName()
        {
            return $this->getModule()->getPrimaryModelName();
        }

        protected static function getSearchFormClassName()
        {
            return null;
        }

        protected function export($stickySearchKey = null)
        {
            assert('$stickySearchKey == null || is_string($stickySearchKey)');
            $modelClassName        = $this->getModelName();
            $searchFormClassName   = static::getSearchFormClassName();
            // Set $pageSize to unlimited, because we don't want pagination
            $pageSize = Yii::app()->pagination->getGlobalValueByType('unlimitedPageSize');
            $model = new $modelClassName(false);

            if ($searchFormClassName != null)
            {
                $searchForm = new $searchFormClassName($model);
            }
            else
            {
                throw new NotSupportedException();
            }
            $stateMetadataAdapterClassName = $this->getModule()->getStateMetadataAdapterClassName();

            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                $searchForm,
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                $stickySearchKey
            );

            if (!$dataProvider)
            {
                $idsToExport = array_filter(explode(",", trim($_GET['selectedIds'], " ,"))); // Not Coding Standard
            }
            $totalItems = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider, false);

            $data = array();
            if ($totalItems > 0)
            {
                if ($totalItems <= ExportModule::$asynchronusTreshold)
                {
                    // Output csv file directly to user browser
                    if ($dataProvider)
                    {
                        $modelsToExport = $dataProvider->getData();
                        foreach ($modelsToExport as $model)
                        {
                            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ))
                            {
                                $modelToExportAdapter  = new ModelToExportAdapter($model);
                                $data[] = $modelToExportAdapter->getData();
                            }
                        }
                    }
                    else
                    {
                        foreach ($idsToExport as $idToExport)
                        {
                            $model = $modelClassName::getById(intval($idToExport));
                            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($model, Permission::READ))
                            {
                                $modelToExportAdapter  = new ModelToExportAdapter($model);
                                $data[] = $modelToExportAdapter->getData();
                            }
                        }
                    }
                    // Output data
                    if (count($data))
                    {
                        $fileName = $this->getModule()->getName() . ".csv";
                        $output = ExportItemToCsvFileUtil::export($data, $fileName, true);
                    }
                    else
                    {
                        Yii::app()->user->setFlash('notification',
                            Yii::t('Default', 'There is no data to export.')
                        );
                    }
                }
                else
                {
                    if ($dataProvider)
                    {
                        $serializedData = serialize($dataProvider);
                    }
                    else
                    {
                        $serializedData = serialize($idsToExport);
                    }

                    // Create background job
                    $exportItem = new ExportItem();
                    $exportItem->isCompleted     = 0;
                    $exportItem->exportFileType  = 'csv';
                    $exportItem->exportFileName  = $this->getModule()->getName();
                    $exportItem->modelClassName = $modelClassName;
                    $exportItem->serializedData  = $serializedData;
                    $exportItem->save();
                    $exportItem->forget();
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'A large amount of data has been requested for export.  You will receive ' .
                        'a notification with the download link when the export is complete.')
                    );
                }
            }
            else
            {
                Yii::app()->user->setFlash('notification',
                    Yii::t('Default', 'There is no data to export.')
                );
            }
            $this->redirect(array($this->getId() . '/index'));
        }

        protected static function getModelAndCatchNotFoundAndDisplayError($modelClassName, $id)
        {
            assert('is_string($modelClassName)');
            assert('is_int($id)');
            try
            {
                return $modelClassName::getById($id);
            }
            catch (NotFoundException $e)
            {
                $messageContent  = Yii::t('Default', 'The record you are trying to access does not exist.');
                $messageView     = new ModelNotFoundView($messageContent);
                $view            = new ModelNotFoundPageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
        }

        public function actionRenderStickyListBreadCrumbContent($stickyOffset, $stickyKey, $stickyModelId)
        {
            if ($stickyOffset == null)
            {
                Yii::app()->end(0, false);
            }
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $modelClassName                 = $this->getModule()->getPrimaryModelName();
            $searchFormClassName            = static::getSearchFormClassName();
            $model                          = new $modelClassName(false);
            $searchForm                     = new $searchFormClassName($model);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                $stickyKey,
                false
            );
            $totalCount  = $dataProvider->calculateTotalItemCount();
            $finalOffset = StickySearchUtil::resolveFinalOffsetForStickyList((int)$stickyOffset, (int)$pageSize, (int)$totalCount);
            $dataProvider->setOffset($finalOffset);
            $dataList   = $dataProvider->getData();
            if (count($dataList) > 0)
            {
                $menuItems = array('label' => '÷'); //char code is &#247;
                foreach ($dataList as $row => $data)
                {
                    $url = Yii::app()->createUrl($this->getModule()->getId() . '/' . $this->getId() . '/details',
                                                  array('id' => $data->id, 'stickyOffset'  => $row + $finalOffset));
                    if ($data->id == $stickyModelId)
                    {
                        $menuItems['items'][] = array(  'label'       => strval($data),
                                                        'url'         => $url,
                                                        'itemOptions' => array('class' => 'strong'));
                    }
                    else
                    {
                        $menuItems['items'][] = array('label' => strval($data), 'url'   => $url);
                    }
                }
                $cClipWidget     = new CClipWidget();
                $cClipWidget->beginClip("StickyList");
                $cClipWidget->widget('application.core.widgets.MbMenu', array(
                    'htmlOptions' => array('id' => 'StickyListMenu'),
                    'items'                   => array($menuItems),
                ));
                $cClipWidget->endClip();
                echo $cClipWidget->getController()->clips['StickyList'];
            }
        }

        public function actionUnlink($id)
        {
            $relationModelClassName    = ArrayUtil::getArrayValue(GetUtil::getData(), 'relationModelClassName');
            $relationModelId           = ArrayUtil::getArrayValue(GetUtil::getData(), 'relationModelId');
            $relationModelRelationName = ArrayUtil::getArrayValue(GetUtil::getData(), 'relationModelRelationName');
            if ($relationModelClassName == null || $relationModelId == null || $relationModelRelationName == null)
            {
                throw new NotSupportedException();
            }
            $relationModel  = $relationModelClassName::GetById(intval($relationModelId));
            if ($relationModel->getRelationType($relationModelRelationName) != RedBeanModel::HAS_MANY &&
                       $relationModel->getRelationType($relationModelRelationName) != RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            $modelClassName = $relationModel->getRelationModelClassName($relationModelRelationName);
            $model          = $modelClassName::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($relationModel);
            $relationModel->$relationModelRelationName->remove($model);
            $saved          = $relationModel->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }
    }
?>