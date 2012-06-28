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

    class ZurmoRoleController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH,
                    'moduleClassName' => 'RolesModule',
               ),
            );
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
            $title           = Yii::t('Default', 'Roles');
            $breadcrumbLinks = array(
                 $title,
            );
            $actionBarAndTreeView = new RolesActionBarAndTreeListView(
                $this->getId(),
                $this->getModule()->getId(),
                Role::getAll('name')
            );
            $view = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $actionBarAndTreeView, $breadcrumbLinks, 'RoleBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $this->actionEdit($id);
        }

        public function actionCreate()
        {
            $title           = Yii::t('Default', 'Create Role');
            $breadcrumbLinks = array($title);
            $editView = new RoleEditAndDetailsView('Edit',
                                                   $this->getId(),
                                                   $this->getModule()->getId(),
                                                   $this->attemptToSaveModelFromPost(new Role()));
            $editView->setCssClasses(array('AdministrativeArea'));
            $view     = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                          makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadcrumbLinks, 'RoleBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $role            = Role::getById(intval($id));
            $title           = Yii::t('Default', 'Edit');
            $breadcrumbLinks = array(strval($role) => array('role/edit',  'id' => $id), $title);
            $editView = new RoleEditAndDetailsView('Edit',
                                                   $this->getId(),
                                                   $this->getModule()->getId(),
                                                   $this->attemptToSaveModelFromPost($role));
            $editView->setCssClasses(array('AdministrativeArea'));
            $view     = new RolesPageView(ZurmoDefaultAdminViewUtil::
                                          makeViewWithBreadcrumbsForCurrentUser($this, $editView, $breadcrumbLinks, 'RoleBreadCrumbView'));
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
            parent::actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
        }

        //selecting
        public function actionModalParentList()
        {
            echo $this->renderModalList(
                'SelectParentRoleModalTreeListView', Yii::t('Default', 'Select a Parent Role'));
        }

        public function actionModalList()
        {
            echo $this->renderModalList(
                'RolesModalTreeListView', Yii::t('Default', 'Select a Role'));
        }

        protected function renderModalList($modalViewName, $pageTitle)
        {
            $rolesModalTreeView = new $modalViewName(
                $this->getId(),
                $this->getModule()->getId(),
                $_GET['modalTransferInformation']['sourceModelId'],
                Role::getAll('name'),
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, $rolesModalTreeView);
            return $view->render();
        }

        public function actionDelete($id)
        {
            $role = Role::GetById(intval($id));
            $role->users->removeAll();
            $role->roles->removeAll();
            $role->save();
            $role->delete();
            unset($role);
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionAutoComplete($term)
        {
            $modelClassName = RolesModule::getPrimaryModelName();
            echo $this->renderAutoCompleteResults($modelClassName, $term);
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
    }
?>