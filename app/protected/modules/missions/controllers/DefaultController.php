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
            $this->actionList(ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
        }

        public function actionList($type = null)
        {
            $pageSize         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                'listPageSize', get_class($this->getModule()));
            $conversation     = new Conversation(false);
            if ($type == null)
            {
                $type = ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED;
            }
            if ($type == ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED)
            {
                $activeActionElementType = 'ConversationsCreatedLink';
            }
            elseif ($type == ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT)
            {
                $activeActionElementType = 'ConversationsParticipantLink';
            }
            else
            {
                throw new NotSupportedException();
            }
            $searchAttributes = array();
            $metadataAdapter  = new ConversationsSearchDataProviderMetadataAdapter(
                $conversation,
                Yii::app()->user->userModel->id,
                $searchAttributes,
                $type
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter,
                'Conversation',
                'RedBeanModelDataProvider',
                'latestDateTime',
                true,
                $pageSize
            );
            $actionBarAndListView = new ActionBarAndListView(
                $this->getId(),
                $this->getModule()->getId(),
                $conversation,
                'Conversations',
                $dataProvider,
                array(),
                'ConversationsActionBarForListView',
                $activeActionElementType
            );
            $view = new ConversationsPageView(ZurmoDefaultViewUtil::
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
            $mission->status = Mission::STATUS_NEW;
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
            $uniquePageId  = 'CommentInlineEditForMissionView';
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }
    }
?>
