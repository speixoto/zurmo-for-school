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

    class CalendarDefaultController extends ZurmoModuleController
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
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $account                        = new Account(false);
            $searchForm                     = new AccountsSearchForm($account);
            $listAttributesSelector         = new ListAttributesSelector('AccountsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'AccountsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new AccountsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                                                                    'SecuredActionBarForAccountsSearchAndListView');
                $view = new AccountsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $account = static::getModelAndCatchNotFoundAndDisplayError('Account', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($account), 'AccountsModule'), $account);
            if (KanbanUtil::isKanbanRequest() === false)
            {
                $breadCrumbView          = StickySearchUtil::resolveBreadCrumbViewForDetailsControllerAction($this, 'AccountsSearchView', $account);
                $detailsAndRelationsView = $this->makeDetailsAndRelationsView($account, 'AccountsModule',
                                                                              'AccountDetailsAndRelationsView',
                                                                              Yii::app()->request->getRequestUri(),
                                                                              $breadCrumbView);
                $view                    = new AccountsPageView(ZurmoDefaultViewUtil::
                                                                    makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            }
            else
            {
                $view = TasksUtil::resolveTaskKanbanViewForRelation($account, $this->getModule()->getId(), $this,
                                                                        'TasksForAccountKanbanView', 'AccountsPageView');
            }
            echo $view->render();
        }

        public function actionCreate()
        {
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new SavedCalendar()), 'Edit');
            $view               = new CalendarPageView(ZurmoDefaultViewUtil::
                                                        makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
            $this->processEdit($savedCalendar, $redirectUrl);
        }

        public function actionCopy($id)
        {
            $copyToAccount  = new Account();
            $postVariableName   = get_class($copyToAccount);
            if (!isset($_POST[$postVariableName]))
            {
                $account        = Account::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account);
                ZurmoCopyModelUtil::copy($account, $copyToAccount);
            }
            $this->processEdit($copyToAccount);
        }

        protected function processEdit(SavedCalendar $calendar, $redirectUrl = null)
        {
            $view = new CalendarPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($calendar, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $account = Account::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($account);
            $account->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        protected static function getSearchFormClassName()
        {
            return 'AccountsSearchForm';
        }

        public function actionExport()
        {
            $this->export('AccountsSearchView');
        }

        /**
         * Modal create for account
         */
        public function actionModalCreate()
        {
            $account = new Account();
            $this->validateCreateModalPostData();
            if (isset($_POST['Account']) && Yii::app()->request->isAjaxRequest)
            {
                $account = $this->attemptToSaveModelFromPost($account, null, false);
                if ($account->id > 0)
                {
                    echo CJSON::encode(array('id' => $account->id, 'name' => $account->name));
                    Yii::app()->end(0, false);
                }
                else
                {
                    throw new FailedToSaveModelException();
                }
            }
            echo ModalEditAndDetailsControllerUtil::setAjaxModeAndRenderModalEditAndDetailsView($this,
                                                                                      'AccountModalCreateView',
                                                                                      $account, 'Edit');
        }
    }
?>