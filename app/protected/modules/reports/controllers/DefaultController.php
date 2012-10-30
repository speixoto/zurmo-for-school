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

    class ReportsDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH,
                        'moduleClassName' => 'ReportsModule',
                        'rightName' => ReportsModule::RIGHT_ACCESS_REPORTS,
                   ),
                   array(
                        self::getRightsFilterPath() . ' + selectType',
                        'moduleClassName' => 'ReportsModule',
                        'rightName' => ReportsModule::RIGHT_CREATE_REPORTS,
                   )
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $savedReport                    = new SavedReport(false);
            $searchForm                     = new ReportsSearchForm($savedReport);

            $searchAttributes = array(
                'moduleClassName'    => array('x','y','z'),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $savedReport,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                SavedReportUtil::resolveSearchAttributeDataByModuleClassNames($metadataAdapter->getAdaptedMetadata(),
                    Report::getReportableModulesClassNamesCurrentUserHasAccessTo()),
                'Notification',
                'RedBeanModelDataProvider',
                'createdDateTime',
                true,
                $pageSize
            );


            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ReportsSearchView'
            );
            $title           = Yii::t('Default', 'Reports');
            $breadcrumbLinks = array(
                 $title,
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new UsersPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView(
                    $searchForm,
                    $pageSize,
                    Yii::t('Default', 'Reports'),
                    $dataProvider,
                    'SecuredActionBarForReportsSearchAndListView'
                );
                $view = new ReportsPageView(ZurmoDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                            $this, $mixedView, $breadcrumbLinks, 'ReportBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $savedReport = static::getModelAndCatchNotFoundAndDisplayError('SavedReport', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($savedReport), 'ReportsModule'), $savedReport);
            $breadcrumbLinks         = array(strval($savedReport));
            $detailsAndRelationsView = $this->makeReportDetailsAndRelationsView($savedReport, 'ReportsModule',
                                                                                Yii::app()->request->getRequestUri(),
                                                                                $breadCrumbView);
            $view = new AccountsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionSelectType()
        {
            $breadcrumbLinks  = array(Yii::t('Default', 'Select Report Type'));
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    new ReportWizardTypesGridView(),
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate($type = null)
        {
            if($type == null)
            {
                $this->actionSelectType();
                Yii::app()->end(0, false);
            }
            $breadcrumbLinks = array(Yii::t('Default', 'Create'));
            assert('is_string($type)');
            $report           = new Report();
            $report->setType($type);
            $reportWizardView = ReportWizardViewFactory::makeViewFromReport($report);
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $reportWizardView,
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $savedReport      = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedReport);
            $breadcrumbLinks  = array(strval($savedReport));
            $report           = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $reportWizardView = ReportWizardViewFactory::makeViewFromReport($report);
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $reportWizardView,
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionSave($type, $id = null)
        {
            $postData                       = PostUtil::getData();
            if($id != null)
            {
                $savedReport                = SavedReport::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedReport);
                $report                     = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            }
            elseif(RightsUtil::doesUserHaveAllowByRightName('ReportsModule',
                                                            ReportsModule::RIGHT_CREATE_REPORTS,
                                                            Yii::app()->user->userModel))
            {

                $savedReport               = new SavedReport();
                $report                    = new Report();
                $report->setType($type);
            }
            else
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }

            DataToReportUtil::resolveReportByWizardPostData($report, $postData,
                                                            ReportToWizardFormAdapter::getFormClassNameByType($type));
            $reportToWizardFormAdapter = new ReportToWizardFormAdapter($report);
            $model                     =  $reportToWizardFormAdapter->makeFormByType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 resolveByPostDataAndModelThenMake($postData[get_class($model)], $savedReport);
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            if($savedReport->save())
            {
                if($explicitReadWriteModelPermissions != null)
                {
                    ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($savedReport,
                                                           $explicitReadWriteModelPermissions);
                }

                //i can do a safety check on perms, then do flash here, on the jscript we can go to list instead and this should come up...
                //make sure you add to list of things to test.

                $redirectToList = $this->resolveAfterSaveHasPermissionsProblem($savedReport,
                                                                                    $postData[get_class($model)]['name']);
                echo CJSON::encode(array('id'             => $savedReport->id,
                                         'redirectToList' => $redirectToList));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        protected function resolveAfterSaveHasPermissionsProblem(SavedReport $savedReport, $modelToStringValue)
        {
            assert('is_string($modelToStringValue)');
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($savedReport, Permission::READ))
            {
                return false;
            }
            else
            {
                $notificationContent = Yii::t(
                    'Default',
                    'You no longer have permissions to access {modelName}.',
                    array('{modelName}' => $modelToStringValue)
                );
                Yii::app()->user->setFlash('notification', $notificationContent);
                return true;
            }
        }

        protected function actionValidate($postData, ReportWizardForm $model)
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

        protected function makeReportDetailsAndRelationsView(SavedReport $savedReport, $redirectUrl, $breadCrumbView)
        {
            assert('$breadCrumbView instanceof BreadCrumbView');
            $reportDetailsAndRelationsView = ReportDetailsAndResultsViewFactory::makeView($report, $savedReport, $controller->getId(),
                                                                                          $controller->getModule()->getId(),
                                                                                          $params, $redirectUrl);
            $gridView = new GridView(2, 1);
            $gridView->setView($breadCrumbView, 0, 0);
            $gridView->setView($reportDetailsAndRelationsView, 1, 0);
            return $gridView;
        }
    }
?>
