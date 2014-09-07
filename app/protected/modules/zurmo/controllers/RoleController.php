<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ZurmoRoleController extends ZurmoModuleController
    {
        public function resolveModuleClassNameForFilters()
        {
            return 'RolesModule';
        }

        public function resolveAndGetModuleId()
        {
            return 'roles';
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $title           = Zurmo::t('ZurmoModule', 'Roles');
            $breadCrumbLinks = array(
                 $title,
            );
            $introView = new SecurityIntroView('ZurmoModule');
            $actionBarAndTreeView = new RolesActionBarAndTreeListView(
                $this->getId(),
                $this->getModule()->getId(),
                Role::getAll('name'),
                $introView
            );
            $view = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $actionBarAndTreeView, $breadCrumbLinks, 'RoleBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $this->actionEdit($id);
        }

        public function actionCreate()
        {
            $title           = Zurmo::t('ZurmoModule', 'Create Role');
            $breadCrumbLinks = array($title);
            $editView = new RoleEditAndDetailsView('Edit',
                                                   $this->getId(),
                                                   $this->getModule()->getId(),
                                                   $this->attemptToSaveModelFromPost(new Role()));
            $editView->setCssClasses(array('AdministrativeArea'));
            $view     = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                          makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadCrumbLinks, 'RoleBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $role            = Role::getById(intval($id));
            $title           = Zurmo::t('Core', 'Edit');
            $breadCrumbLinks = array(strval($role) => array('role/edit',  'id' => $id), $title);
            $editView = new RoleEditAndDetailsView('Edit',
                                                   $this->getId(),
                                                   $this->getModule()->getId(),
                                                   $this->attemptToSaveModelFromPost($role));
            $editView->setCssClasses(array('AdministrativeArea'));
            $view     = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                          makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadCrumbLinks, 'RoleBreadCrumbView'));
            echo $view->render();
        }

        /**
         * Override to ensure the permissions cache is forgotten since if it is not, other users logged in will not
         * get the effective changes until the cache is cleared across the application.
         * (non-PHPdoc)
         * @see ZurmoBaseController::actionAfterSuccessfulModelSave()
         */
        protected function actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams = null)
        {
            PermissionsCache::forgetAll();
            RightsCache::forgetAll();
            PoliciesCache::forgetAll();
            AllPermissionsOptimizationCache::forgetAll();
            parent::actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
        }

        //selecting
        public function actionModalParentList()
        {
            echo $this->renderModalList(
                'SelectParentRoleModalTreeListView', Zurmo::t('ZurmoModule', 'Select a Parent Role'));
        }

        public function actionModalList()
        {
            echo $this->renderModalList(
                'RolesModalTreeListView', Zurmo::t('ZurmoModule', 'Select a Role'));
        }

        protected function renderModalList($modalViewName, $pageTitle)
        {
            $rolesModalTreeView = new $modalViewName(
                $this->getId(),
                $this->getModule()->getId(),
                isset($_GET['modalTransferInformation']['sourceModelId']) ? $_GET['modalTransferInformation']['sourceModelId'] : null,
                Role::getAll('name'),
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId'],
                $_GET['modalTransferInformation']['modalId']
            );
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, $rolesModalTreeView);
            return $view->render();
        }

        public function actionDelete($id)
        {
            $role = Role::GetById(intval($id));
            $role->delete();
            unset($role);
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionAutoComplete($term, $autoCompleteOptions = null)
        {
            echo $this->renderAutoCompleteResults(RolesModule::getPrimaryModelName(), $term, $autoCompleteOptions);
        }

        /**
         * There is no details action, so redirect to list.
         */
        protected function redirectAfterSaveModel($modelId, $redirectUrlParams = null)
        {
            if ($redirectUrlParams == null)
            {
                $redirectUrlParams = array($this->getId() . '/list', 'id' => $modelId);
            }
            $this->redirect($redirectUrlParams);
        }

        public function actionUsersInRoleModalList($id)
        {
            $model = Role::getById((int)$id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            $searchAttributeData = UsersByModelModalListControllerUtil::makeModalSearchAttributeDataByModel($model, 'role');
            $dataProvider = UsersByModelModalListControllerUtil::makeDataProviderBySearchAttributeData($searchAttributeData);
            Yii::app()->getClientScript()->setToAjaxMode();
            echo UsersByModelModalListControllerUtil::renderList($this, $dataProvider, 'usersInRoleModalList');
        }
    }
?>