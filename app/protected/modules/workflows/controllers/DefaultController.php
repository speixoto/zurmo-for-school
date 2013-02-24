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
     * Default controller for all workflow actions
      */
    class WorkflowsDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                   array(
                        self::getRightsFilterPath() . ' + selectType',
                        'moduleClassName' => 'WorkflowsModule',
                        'rightName' => WorkflowsModule::RIGHT_CREATE_WORKFLOWS,
                   )
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        protected function resolveMetadataBeforeMakingDataProvider(& $metadata)
        {
            $metadata = SavedWorkflowUtil::resolveSearchAttributeDataByModuleClassNames($metadata,
                        Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo());
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $savedWorkflow                    = new SavedWorkflow(false);
            $searchForm                     = new WorkflowsSearchForm($savedWorkflow);
            $dataProvider                   = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'WorkflowsSearchView'
            );
            $title           = Zurmo::t('WorkflowsModule', 'Workflows');
            $breadcrumbLinks = array(
                 $title,
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new WorkflowsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView');
                $view = new WorkflowsPageView(ZurmoDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                            $this, $mixedView, $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }
/**
        public function actionDetails($id)
        {
            $savedReport = static::getModelAndCatchNotFoundAndDisplayError('SavedReport', intval($id));
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($savedReport), 'ReportsModule'), $savedReport);
            $breadcrumbLinks         = array(strval($savedReport));
            $breadCrumbView          = new ReportBreadCrumbView($this->getId(), $this->getModule()->getId(), $breadcrumbLinks);
            $detailsAndRelationsView = $this->makeReportDetailsAndRelationsView($savedReport, Yii::app()->request->getRequestUri(),
                                                                                $breadCrumbView);
            $view = new ReportsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }
**/
        public function actionSelectType()
        {
            $breadcrumbLinks  = array(Zurmo::t('WorkflowsModule', 'Select Workflow Type'));
            $view             = new WorkflowsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    new WorkflowWizardTypesGridView(),
                                                    $breadcrumbLinks,
                                                    'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate($type = null)
        {
            if($type == null)
            {
                $this->actionSelectType();
                Yii::app()->end(0, false);
            }
            $breadcrumbLinks = array(Zurmo::t('WorkflowsModule', 'Create'));
            assert('is_string($type)');
            $wizard           = new Workflow();
            $wizard->setType($type);
            $wizardWizardView = WorkflowWizardViewFactory::makeViewFromWorkflow($wizard);
            $view             = new WorkflowsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $wizardWizardView,
                                                    $breadcrumbLinks,
                                                    'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $savedWorkflow      = SavedWorkflow::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            $breadcrumbLinks  = array(strval($savedWorkflow));
            $wizard           = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            $wizardWizardView = WorkflowWizardViewFactory::makeViewFromWorkflow($wizard);
            $view             = new WorkflowsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $wizardWizardView,
                                                    $breadcrumbLinks,
                                                    'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionSave($type, $id = null)
        {
            $postData                  = PostUtil::getData();
            $savedWorkflow             = null;
            $wizard                    = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $wizard, $type, $id);

            $workflowToWizardFormAdapter = new WorkflowToWizardFormAdapter($wizard);
            $model                     =  $workflowToWizardFormAdapter->makeFormByType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($wizard, $savedWorkflow);
            if($savedWorkflow->id > 0)
            {
                ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            }
            if($savedWorkflow->save())
            {
                echo CJSON::encode(array('id'             => $savedWorkflow->id,
                                         'redirectToList' => false));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $postData      = PostUtil::getData();
            $savedWorkflow = null;
            $wizard        = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $wizard, $type, $id);
            if($nodeId != null)
            {
                $wizardToTreeAdapter = new WorkflowRelationsAndAttributesToTreeAdapter($wizard, $treeType);
                echo ZurmoTreeView::saveDataAsJson($wizardToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new WorkflowRelationsAndAttributesTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber, $trackableStructurePosition = false, $id = null)
        {
            $postData                           = PostUtil::getData();
            $savedWorkflow                      = null;
            $wizard                             = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $wizard, $type, $id);
            $nodeIdWithoutTreeType              = WorkflowRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $wizard->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WorkflowActiveForm();
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = WorkflowToWizardFormAdapter::getFormClassNameByType($wizard->getType());
            $model                              = ComponentForWorkflowFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $wizard->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new WorkflowAttributeToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForWorkflowComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content               = $view->render();
            $view->renderAddAttributeErrorSettingsScript($form, $wizardFormClassName, get_class($model), $inputPrefixData);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionDelete($id)
        {
            $savedWorkflow = SavedWorkflow::GetById(intval($id));
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedWorkflow);
            $savedWorkflow->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        protected function resolveCanCurrentUserAccessWorkflows()
        {
            if(!RightsUtil::doesUserHaveAllowByRightName('WorkflowsModule',
                                                            WorkflowsModule::RIGHT_CREATE_WORKFLOWS,
                                                            Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            return true;
        }

        protected function resolveSavedWorkflowAndWorkflowByPostData(Array $postData, & $savedWorkflow, & $wizard, $type, $id = null)
        {
            if($id == null)
            {
                $this->resolveCanCurrentUserAccessWorkflows();
                $savedWorkflow               = new SavedWorkflow();
                $wizard                    = new Workflow();
                $wizard->setType($type);
            }
            else
            {
                $savedWorkflow              = SavedWorkflow::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedWorkflow);
                $wizard                     = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            }
            DataToWorkflowUtil::resolveWorkflowByWizardPostData($wizard, $postData,
                                    WorkflowToWizardFormAdapter::getFormClassNameByType($type));
        }

        protected function actionValidate($postData, WorkflowWizardForm $model)
        {
            if(isset($postData['validationScenario']) && $postData['validationScenario'] != null)
            {
                $model->setScenario($postData['validationScenario']);
            }
            else
            {
                throw new NotSupportedException();
            }
            $model->validate();
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        protected function makeReportDetailsAndRelationsView(SavedReport $savedReport, $redirectUrl,
                                                             ReportBreadCrumbView $breadCrumbView)
        {
            $wizardDetailsAndRelationsView = ReportDetailsAndResultsViewFactory::makeView($savedReport, $this->getId(),
                                                                                          $this->getModule()->getId(),
                                                                                          $redirectUrl);
            $gridView = new GridView(2, 1);
            $gridView->setView($breadCrumbView, 0, 0);
            $gridView->setView($wizardDetailsAndRelationsView, 1, 0);
            return $gridView;
        }
    }
?>
