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

    class EmailTemplatesDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH =
              'application.modules.emailTemplates.controllers.filters.EmailTemplatesForWorkflowZeroModelsCheckControllerFilter';
/**
        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('WorkflowsModule', 'Workflows');
            return array($title);
        }
**/
        public static function getListForWorkflowBreadcrumbLinks()
        {
            $title = Zurmo::t('EmailTemplatesModule', 'Email Templates');
            return array($title);
        }

        public static function getDetailsAndEditForWorkflowBreadcrumbLinks()
        {
            return array(Zurmo::t('EmailTemplatesModule', 'Email Templates') =>
                         array('default/listForWorkflow'));
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    /**
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                        'activeActionElementType' => 'WorkflowsLink',
                        'breadcrumbLinks'         => static::getListBreadcrumbLinks(),
                    ),
                     * */
                    array(
                        static::ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH . ' + listForWorkflow',
                        'controller'                    => $this,
                        'activeActionElementType'       => 'EmailTemplatesForWorkflowLink',
                        'breadcrumbLinks'               => static::getListForWorkflowBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForWorkflowStateMetadataAdapter'
                    ),

                )
            );
        }

        public function actionIndex()
        {
            //todo: watch where this goes... since we wont have actionList anymore
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $listAttributesSelector         = new ListAttributesSelector('EmailTemplatesListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'EmailTemplatesSearchView'
            );
            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $emailTemplate,
                'EmailTemplates',
                $dataProvider,
                array(),
                'EmailTemplatesActionBarForListView'
            );
            $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $actionBarAndListView));
            echo $view->render();
        }

        public function actionListForWorkflow()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'EmailTemplatesForWorkflowLink';
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                              'EmailTemplatesForWorkflowStateMetadataAdapter',
                                              'EmailTemplatesSearchView');
            $breadcrumbLinks                = static::getListForWorkflowBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new WorkflowsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', null, $activeActionElementType);
                $view      = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                             makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionCreate($type)
        {
            $type = (int)$type;
            $emailTemplate       = new EmailTemplate();
            $emailTemplate->type = $type;
            $editAndDetailsView  = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate), 'Edit');
            if($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadcrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadcrumbLinks[]  = Zurmo::t('EmailTemplatesModule', 'Create');
                $view               = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            else
            {
                $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::makeStandardViewForCurrentUser($this, $editAndDetailsView));
            }
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($emailTemplate);

            $editAndDetailsView = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate, $redirectUrl), 'Edit');
            if($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadcrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view               = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            else
            {
                $view = new EmailTemplatesPageView(ZurmoDefaultViewUtil::
                        makeStandardViewForCurrentUser($this, $editAndDetailsView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($emailTemplate),
                                        'EmailTemplatesModule'), $emailTemplate);
            $detailsView              = new EmailTemplateEditAndDetailsView('Details', $this->getId(),
                                                                            $this->getModule()->getId(), $emailTemplate);

            if($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
            $breadcrumbLinks          = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
            $breadcrumbLinks[]        = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
            $view                     = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                        makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                        $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            else
            {
                //todO: fix breadcrumbs for marketing module.
                $breadcrumbLinks[]    = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view                 = new EmailTemplatesPageView((ZurmoDefaultViewUtil::
                                        makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                        $breadcrumbLinks, 'EmailTemplateBreadCrumbView')));
            }
            echo $view->render();
        }

        protected static function getSearchFormClassName()
        {
            return 'EmailTemplatesSearchForm';
        }

        public function actionDelete($id)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($emailTemplate);
            $type          = $emailTemplate->type;
            $emailTemplate->delete();
            if($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $this->redirect(array($this->getId() . '/listForWorkflow'));
            }
            else
            {
                $this->redirect(array($this->getId() . '/index'));
            }

        }
    }
?>
