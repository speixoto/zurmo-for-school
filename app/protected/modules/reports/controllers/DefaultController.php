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
                $report                     = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            }
            else
            {
                $savedReport               = new SavedReport();
                $report                    = new Report();
                $report->setType($type);
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
                echo CJSON::encode(array('id' => $savedReport->id));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
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
    }
?>
