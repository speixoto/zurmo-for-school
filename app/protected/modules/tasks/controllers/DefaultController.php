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

    class TasksDefaultController extends ActivityModelsDefaultController
    {
        public function actionCloseTask($id)
        {
            $task                    = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($task);
            $task->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $task->completed         = true;
            $saved                   = $task->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $modelClassName    = $this->getModule()->getPrimaryModelName();
            $activity          = static::getModelAndCatchNotFoundAndDisplayError($modelClassName, intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($activity);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($activity), get_class($this->getModule())), $activity);
            TasksUtil::markUserHasReadLatest($activity, Yii::app()->user->userModel);
            $pageViewClassName = $this->getPageViewClassName();
            $detailsView       = new TaskDetailsView('Details', $this->getId(), $this->getModule()->getId(), $activity);
            $view              = new $pageViewClassName(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,$detailsView));
            echo $view->render();
        }

        /**
         * Create comment via ajax for task
         * @param type $id
         * @param string $uniquePageId
         */
        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/tasks/default/inlineCreateCommentFromAjax',
                                                    array('id'           => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => (int)$id,
                                   'relatedModelClassName'    => 'Task',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForModelView';
            echo             ZurmoHtml::tag('h2', array(), Zurmo::t('CovnersationsModule', 'Add Comment'));
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }

        /**
         * Update owner or requested by user for task
         * @param int $id
         */
        public function actionUpdateRelatedUsersViaAjax($id, $attribute, $userId)
        {
            $task = Task::getById(intval($id));
            $user = User::getById(intval($userId));
            switch($attribute)
            {
                case 'owner':
                              $task->owner = $user;
                              $task->save();
                              TasksUtil::sendNotificationOnTaskUpdate($task, Zurmo::t('TasksModule', 'The owner for the TasksModuleSingularLowerCaseLabel #' . $task->id . ' is updated to ' . $user->getFullName()));
                              break;

                case 'requestedByUser':
                              $origRequestedByUser = $task->requestedByUser;
                              $task->requestedByUser = $user;
                              $task->save();
                              $user = $task->requestedByUser;
                              $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($task);
                              TasksUtil::resolveExplicitPermissionsForRequestedByUser($task, $origRequestedByUser, $user, $explicitReadWriteModelPermissions);
                              break;
            }
            echo $this->getPermissionContent($task);
        }

        /**
         * Update owner or requested by user for task
         * @param int $id
         */
        public function actionUpdateDueDateTimeViaAjax($id, $dateTime)
        {
            $task         = Task::getById(intval($id));
            $dateTime     = strtotime($dateTime);
            $dueDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime($dateTime);
            $task->dueDateTime = $dueDateTime;
            $task->save();
            TasksUtil::sendNotificationOnTaskUpdate($task, Zurmo::t('TasksModule', 'The due date for task #' . $task->id . ' is updated'));
        }

        /**
         * Update owner or requested by user for task
         * @param int $id
         */
        public function actionAddSubscriber($id)
        {
            $task = Task::getById(intval($id));
            $user = Yii::app()->user->userModel;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = $user;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $task->save();

            TasksUtil::sendNotificationOnTaskUpdate($task, Zurmo::t('TasksModule', $user->getFullName() . ' has subscribed for the task #' . $task->id));

            $content = TasksUtil::getTaskSubscriberData($task);
            echo $content;
        }

        /**
         * Update status via ajax
         * @param int $id
         */
        public function actionUpdateStatusViaAjax($id, $status)
        {
            $this->processStatusUpdateViaAjax($id, $status);
        }

        /**
         * Gets the permission content
         * @param RedBeanModel $model
         * @return string
         */
        protected function getPermissionContent($model)
        {
            $ownedSecurableItemDetailsContent   = OwnedSecurableItemDetailsViewUtil::renderAfterFormLayoutForDetailsContent(
                                                                                                        $model,
                                                                                                        null);
            return $ownedSecurableItemDetailsContent;
        }

        /**
         * Create task from related view
         */
        public function actionModalCreateFromRelation()
        {
            $task             = new Task();
            $task             = $this->resolveNewModelByRelationInformation( $task,
                                                                                $_GET['modalTransferInformation']['relationAttributeName'],
                                                                                (int)$_GET['modalTransferInformation']['relationModelId'],
                                                                                $_GET['modalTransferInformation']['relationModuleId']);
            $this->processTaskEdit($task);
        }

        /**
         * Create task from top menu
         */
        public function actionModalCreate()
        {
            $task             = new Task();
            $this->processTaskEdit($task);
        }

        /**
         * Saves task in the modal view
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         * @param string $portletId
         * @param string $uniqueLayoutId
         */
        public function actionModalSaveFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $portletId, $uniqueLayoutId, $id = null)
        {
            if($id == null)
            {
                $task   = new Task();
                $task   = $this->resolveNewModelByRelationInformation( new $task,
                                                                        $relationAttributeName,
                                                                        (int)$relationModelId,
                                                                        $relationModuleId);
            }
            else
            {
                $task = Task::getById(intval($id));
            }
            $task   = $this->attemptToSaveModelFromPost($task, null, false);
            $this->actionModalViewFromRelation($task->id);
        }

        /**
         * Saves task in the modal view
         */
        public function actionModalSave($id)
        {
            if($id == null)
            {
                $task = new Task();
            }
            else
            {
                $task = Task::getById(intval($id));
            }
            $task     = $this->attemptToSaveModelFromPost($task, null, false);
            $this->actionModalViewFromRelation($task->id);
        }

        /**
         * Clone task
         * @param int $id
         * @param string $redirectUrl
         */
        public function actionModalCopyFromRelation($id)
        {
            $copyToTask   = new Task();
            if (!isset($_POST['Task']))
            {
                $task = Task::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($task);
                ActivityCopyModelUtil::copy($task, $copyToTask);
            }
            $this->processTaskEdit($copyToTask);
        }

        /**
         * Loads modal view from related view
         * @param int $id
         */
        public function actionModalViewFromRelation($id)
        {
            $task = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($task);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($task), get_class($this->getModule())), $task);
            TasksUtil::markUserHasReadLatest($task, Yii::app()->user->userModel);
            echo ModalEditAndDetailsControllerUtil::setAjaxModeAndRenderModalEditAndDetailsView($this,'TaskDetailsView', $task, 'Details');
        }

        /**
         * Create task from related view
         */
        public function actionModalEditFromRelation($id)
        {
            $task             = Task::getById(intval($id));
            $this->processTaskEdit($task);
        }

        /**
         * Process Task Edit
         * @param Task $task
         */
        protected function processTaskEdit(Task $task)
        {
            if (RightsUtil::canUserAccessModule('TasksModule', Yii::app()->user->userModel))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] == 'task-modal-edit-form')
                {
                    $controllerUtil   = static::getZurmoControllerUtil();
                    $controllerUtil->validateAjaxFromPost($task, 'Task');
                    Yii::app()->getClientScript()->setToAjaxMode();
                    Yii::app()->end(0, true);
                }
                /*TODO Might have to remove RelatedModalEditAndDetailsLinkProvider*/
                else
                {
                    $cs = Yii::app()->getClientScript();
                    $cs->registerScriptFile(
                        Yii::app()->getAssetManager()->publish(
                            Yii::getPathOfAlias('application.modules.tasks.elements.assets')
                            ) . '/TaskUtils.js',
                        CClientScript::POS_END
                    );
                    echo ModalEditAndDetailsControllerUtil::setAjaxModeAndRenderModalEditAndDetailsView($this,'TaskModalEditAndDetailsView', $task, 'Edit');
                }
            }
        }

        /**
         * Should support in addition to custome field as well
         * @param string $id
         * @param string $attribute
         * @param string $value
         * @throws NotSupportedException
         * @throws FailedToSaveModelException
         */
        public function actionUpdateAttributeValue($id, $attribute, $value)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            $model          = $modelClassName::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            $model->{$attribute}->value = $value;
            $saved                      = $model->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionUpdateItemsSortInKanbanView()
        {
            $getData = GetUtil::getData();
            $counter = 1;
            foreach($getData['items'] as $itemId)
            {
                  //ToDo: Ask Jason, it shud be refereatial problem
                $kanbanItem        = KanbanItem::getKanbanItemForTask(intval($itemId));
//                $kanbanItem->order = $counter;
//                $kanbanItem->save();
                if($getData['type'] == $kanbanItem->type)
                {
                    $sql = "update kanbanitem set sortorder = '" . $counter . "' where task_id = " . $itemId;
                }
                else
                {
                    $sql = "update kanbanitem set sortorder = '" . $counter . "', type = '" . $getData['type'] . "' where task_id = " . $itemId;
                }
                R::exec($sql);
                $counter++;
            }
        }

        public function actionUpdateStatusInKanbanView($targetStatus, $taskId)
        {
           $route = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView');
           //Run update queries for update task staus and update type and sort order in kanban column
           $this->processStatusUpdateViaAjax($taskId, $targetStatus);
           $targetKanbanType = TasksUtil::resolveKanbanItemTypeForTaskStatus(intval($targetStatus));
           $sourceKanbanType = TasksUtil::resolveKanbanItemTypeForTask(intval($taskId));
           if($sourceKanbanType != $targetKanbanType)
           {
                $sortOrder = KanbanItem::getMaximumSortOrderByType($targetKanbanType);
                $sql = "update kanbanitem set sortorder = '" . $sortOrder . "', type = '" . $targetKanbanType . "' where task_id = " . $taskId;
                R::exec($sql);
           }
        }

        protected function processStatusUpdateViaAjax($id, $status)
        {
            $task         = Task::getById(intval($id));
            $task->status = intval($status);
            if(intval($status) == Task::TASK_STATUS_COMPLETED)
            {
                $task->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $task->completed         = true;
                $task->save();
                echo '<p>' . Zurmo::t('TasksModule', 'Completed On') . ': ' . DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($task->completedDateTime) . '</p>';
            }
            else
            {
                $task->completedDateTime = null;
                $task->completed         = false;
                $task->save();
            }

            TasksUtil::sendNotificationOnTaskUpdate($task, Zurmo::t('TasksModule', 'The status for the task #' . $task->id . ' has been updated to ' . Task::getStatusDisplayName(intval($status))));
        }
    }
?>
