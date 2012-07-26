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

    class MissionsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        public function actionIndex()
        {
            $this->actionList(MissionsSearchDataProviderMetadataAdapter::LIST_TYPE_OPEN);
        }

        public function actionList($type = null)
        {
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $mission          = new Mission(false);
            if ($type == null)
            {
                $type = MissionsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED;
            }
            if ($type == MissionsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED)
            {
                $activeActionElementType = 'MissionsCreatedLink';
            }
            elseif ($type == MissionsSearchDataProviderMetadataAdapter::LIST_TYPE_OPEN)
            {
                $activeActionElementType = 'MissionsOpenLink';
            }
            else
            {
                throw new NotSupportedException();
            }
            $searchAttributes = array();
            $metadataAdapter  = new MissionsSearchDataProviderMetadataAdapter(
                $mission,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $type
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                'Mission',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $mission,
                'Missions',
                $dataProvider,
                array(),
                'MissionsActionBarForListView',
                $activeActionElementType
            );
            $view = new MissionsPageView(ZurmoDefaultViewUtil::
                                              makeStandardViewForCurrentUser($this, $actionBarAndListView));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $mission = static::getModelAndCatchNotFoundAndDisplayError('Mission', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($mission);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($mission), 'MissionsModule'), $mission);
            MissionsUtil::markUserHasReadLatest($mission, Yii::app()->user->userModel);
            $detailsView              = new MissionDetailsView($this->getId(), $this->getModule()->getId(), $mission);
            $view                     = new MissionsPageView(ZurmoDefaultViewUtil::
                                                makeStandardViewForCurrentUser($this, $detailsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $mission         = new Mission();
            $mission->status = Mission::STATUS_OPEN;
            $mission->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE);
            $editView = new MissionEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($mission),
                                                 Yii::t('Default', 'Create Mission'));
            $view     = new MissionsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $mission  = Mission::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($mission);
            $editView = new MissionEditView($this->getId(), $this->getModule()->getId(),
                                                 $this->attemptToSaveModelFromPost($mission),
                                                 strval($mission));
            $view     = new MissionsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $mission = Mission::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($mission);
            $mission->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/missions/default/inlineCreateCommentFromAjax',
                                                    array('id'		     => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => (int)$id,
                                   'relatedModelClassName' 	  => 'Mission',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForModelView';
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }

        public function ajaxChangeStatus($status, $id)
        {
            $mission         = Mission::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($mission);
            if($status == Mission::STATUS_TAKEN)
            {
                if($mission->takenByUser->id > 0)
                {
                    throw new NotSupportedException();
                }
                $mission->takenByUser = Yii::app()->user->userModel;
            }

            $mission->status = $status;
            $saved           = $mission->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
            $statusText        = MissionStatusElement::renderStatusTextContent($mission);
            $statusAction      = MissionStatusElement::renderStatusActionContent($mission, $statusChangeDivId);
            $content = $statusText;
            if($statusAction != null)
            {
                $content . ' ' . $statusAction;
            }
            echo $content;
        }
    }
?>
