<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ProjectsDefaultController extends ZurmoModuleController
    {
        public static function getListBreadcrumbLinks()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('ProjectsModule', 'ProjectsModulePluralLabel', $params);
            return array($title);
        }

        public function filters()
        {
            $modelClassName             = $this->getModule()->getPrimaryModelName();
            $viewClassName              = $modelClassName . 'EditAndDetailsView';
            $zeroModelsYetViewClassName = 'ProjectsZeroModelsYetView';
            $pageViewClassName          = 'ProjectsPageView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller'                 => $this,
                        'zeroModelsYetViewClassName' => $zeroModelsYetViewClassName,
                        'modelClassName'             => $modelClassName,
                        'pageViewClassName'          => $pageViewClassName
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $project                        = new Project(false);
            $searchForm                     = new ProjectsSearchForm($project);
            $listAttributesSelector         = new ListAttributesSelector('ProjectsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider                   = $this->resolveSearchDataProvider(
                                                    $searchForm,
                                                    $pageSize,
                                                    null,
                                                    'ProjectsSearchView'
                                                );
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView  = $this->makeListView(
                            $searchForm,
                            $dataProvider
                        );
                $view       = new ProjectsPageView($mixedView);
            }
            else
            {
                $mixedView        = $this->makeActionBarSearchAndListView($searchForm, $dataProvider);
                $view             = new ProjectsPageView(ProjectDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                        $this, $mixedView, $breadcrumbLinks, 'ProjectBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $project            = static::getModelAndCatchNotFoundAndDisplayError('Project', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($project);
            $breadcrumbLinks = array(StringUtil::getChoppedStringContent(strval($project), 25));
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($project), 'ProjectsModule'), $project);
            $getData                 = GetUtil::getData();
            $isKanbanBoardInRequest  = ArrayUtil::getArrayValue($getData, 'kanbanBoard');
            if ($isKanbanBoardInRequest == 0 || $isKanbanBoardInRequest == null || Yii::app()->userInterface->isMobile() === true)
            {
                $detailsView        = new ProjectEditAndDetailsView('Details', $this->getId(), $this->getModule()->getId(), $project);
                $view               = new ProjectsPageView(ProjectDefaultViewUtil::
                                                             makeViewWithBreadcrumbsForCurrentUser(
                                                                $this, $detailsView, $breadcrumbLinks, 'ProjectBreadCrumbView'));
            }
            else
            {
                $kanbanItem   = new KanbanItem();
                $kanbanBoard  = new TaskKanbanBoard($kanbanItem, 'type', $project, get_class($project));
                $kanbanBoard->setIsActive();
                $params['relationModel']    = $project;
                $params['relationModuleId'] = $this->getModule()->getId();
                $params['redirectUrl']      = null;
                $listView     = new TasksForProjectKanbanView($this->getId(),
                                                                  'tasks', 'Task', null, $params, null, array(), $kanbanBoard);
                $view         = new ProjectsPageView(ZurmoDefaultViewUtil::
                                                            makeStandardViewForCurrentUser($this, $listView));
            }
            echo $view->render();
        }

        public function actionCreate()
        {
            $params                 = LabelUtil::getTranslationParamsForAllModules();
            $title                  = Zurmo::t('ProjectsModule', 'Create ProjectsModuleSingularLabel', $params);
            $breadcrumbLinks        = array($title);
            $editAndDetailsView     = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new Project()), 'Edit');
            $view                   = new ProjectsPageView(ProjectDefaultViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser(
                                                    $this, $editAndDetailsView, $breadcrumbLinks, 'ProjectBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $project         = Project::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($project);
            $breadcrumbLinks = array(StringUtil::getChoppedStringContent(strval($project), 25));
            $view            = new ProjectsPageView(ProjectDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this,
                                                            $this->makeEditAndDetailsView(
                                                                $this->attemptToSaveModelFromPost(
                                                                    $project, $redirectUrl), 'Edit'), $breadcrumbLinks, 'ProjectBreadCrumbView'                                                   ));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $project = Project::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($project);
            $project->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        protected static function getSearchFormClassName()
        {
            return 'ProjectsSearchForm';
        }

        public function actionExport()
        {
            $this->export('ProjectsSearchView');
        }

        /**
         * Copies the project
         * @param int $id
         */
        public function actionCopy($id)
        {
            $copyToProject      = new Project();
            $postVariableName   = get_class($copyToProject);
            if (!isset($_POST[$postVariableName]))
            {
                $project        = Project::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($project);
                ProjectZurmoCopyModelUtil::copy($project, $copyToProject);
            }
            $this->processEdit($copyToProject);
        }

        /**
         * Process the editing of project
         * @param Project $project
         * @param string $redirectUrl
         */
        protected function processEdit(Project $project, $redirectUrl = null)
        {
            $view = new ProjectsPageView(ProjectDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($project, $redirectUrl), 'Edit')));
            echo $view->render();
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
            $project = new Project(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $project = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProjectsPageView',
                $project,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massDeleteView = $this->makeMassDeleteView(
                $project,
                $activeAttributes,
                $selectedRecordCount,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProjectsPageView(ProjectDefaultViewUtil::
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
            $project = new Project(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView'
            );
            $this->processMassDeleteProgress(
                'Project',
                $pageSize,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        /**
         * Project Modal List Field
         */
        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId'],
                                            $_GET['modalTransferInformation']['modalId']
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider);
        }

        /**
         * Render autocomplete options of accounts for projects
         * @param string $term
         */
        public function actionAutoCompleteAllAccountsForMultiSelectAutoComplete($term)
        {
            $this->processAutoCompleteOptionsForRelations('Account', $term);
        }

        /**
         * Render autocomplete options of opportunities for projects
         * @param string $term
         */
        public function actionAutoCompleteAllOpportunitiesForMultiSelectAutoComplete($term)
        {
            $this->processAutoCompleteOptionsForRelations('Opportunity', $term);
        }

        /**
         * Process auto complete options for relations
         * @param string $relatedModelClassName
         * @param string $term
         */
        protected function processAutoCompleteOptionsForRelations($relatedModelClassName, $term)
        {
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = null;
            $projectRelations      = self::getProjectRelationsByPartialName($relatedModelClassName, $term, $pageSize, $adapterName);
            $autoCompleteResults    = array();
            foreach ($projectRelations as $projectRelation)
            {
                $autoCompleteResults[] = array(
                    'id'   => $projectRelation->id,
                    'name' => self::renderHtmlContentLabelFromRelationAndKeyword($projectRelation, $term)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        /**
         * @param string $partialName
         * @param int $pageSize
         * @param null|string $stateMetadataAdapterClassName
         */
        public static function getProjectRelationsByPartialName($className, $partialName, $pageSize, $stateMetadataAdapterClassName = null)
        {
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $joinTablesAdapter  = new RedBeanModelJoinTablesQueryAdapter($className);
            $metadata           = array('clauses' => array(), 'structure' => '');
            if ($stateMetadataAdapterClassName != null)
            {
                $stateMetadataAdapter   = new $stateMetadataAdapterClassName($metadata);
                $metadata               = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                $metadata['structure']  = '(' . $metadata['structure'] . ')';
            }
            $where  = RedBeanModelDataProvider::makeWhere($className, $metadata, $joinTablesAdapter);
            if ($where != null)
            {
                $where .= 'and';
            }
            $where .= self::getWherePartForPartialNameSearchByPartialName(lcfirst($className), $partialName);
            return $className::getSubset($joinTablesAdapter, null, $pageSize, $where, lcfirst($className) . ".name");
        }

        /**
         * @param string $partialName
         * @return string
         */
        protected static function getWherePartForPartialNameSearchByPartialName($tableName, $partialName)
        {
            assert('is_string($partialName)');
            return "   ($tableName.name  like '$partialName%') ";
        }

        /**
         * @param RelatedModel Account, Contact or Opportunity
         * @param string $keyword
         * @return string
         */
        public static function renderHtmlContentLabelFromRelationAndKeyword($relatedModel, $keyword)
        {
            assert('($relatedModel instanceof Account || $relatedModel instanceof Opportunity) && $relatedModel->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if ($relatedModel->name != null)
            {
                return strval($relatedModel) . '&#160&#160<b>'. '</b>';
            }
            else
            {
                return strval($relatedModel);
            }
        }

        /**
         * @return ProjectZurmoControllerUtil
         */
        protected static function getZurmoControllerUtil()
        {
            return new ProjectZurmoControllerUtil('projectItems', 'ProjectItemForm');
        }
    }
?>