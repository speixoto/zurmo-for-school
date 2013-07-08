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
            $activity = static::getModelAndCatchNotFoundAndDisplayError($modelClassName, intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($activity);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($activity), get_class($this->getModule())), $activity);
            $pageViewClassName = $this->getPageViewClassName();
            $detailsView       = new TaskDetailsView($this->getId(), $this->getModule()->getId(), $activity);
            $view              = new $pageViewClassName(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,$detailsView));
            echo $view->render();
        }

        /**
         * Action for saving a new comment inline edit form.
         * @param string or array $redirectUrl
         */
        public function actionInlineCreateTaskCheckItemSave($redirectUrl = null, $uniquePageId = null)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'task-check-item-inline-edit-form' . $uniquePageId)
            {
                $this->actionInlineEditValidate(new TaskCheckListItem());
            }
            $this->attemptToSaveModelFromPost(new TaskCheckListItem(), $redirectUrl);
        }

        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $taskCheckListItem  = new TaskCheckListItem();
            $redirectUrl        = Yii::app()->createUrl('/tasks/default/inlineCreateTaskCheckItemFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters      = array('relatedModelId'           => $this->model->id,
                                        'relatedModelClassName'    => 'Task',
                                        'relatedModelRelationName' => 'checkListItems',
                                        'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId       = 'TaskCheckItemInlineEditForModelView';
            $inlineView         = new TaskCheckItemInlineEditView($taskCheckListItem, 'default', 'tasks', 'inlineCreateTaskCheckItemSave',
                                                      $urlParameters, $uniquePageId);
            $view               = new AjaxPageView($inlineView);
            echo $view->render();
        }

        protected function actionInlineEditValidate($model)
        {
            $postData                      = PostUtil::getData();
            $postFormData                  = ArrayUtil::getArrayValue($postData, get_class($model));
            $sanitizedPostData             = PostUtil::
                                             sanitizePostByDesignerTypeForSavingModel($model, $postFormData);
            $model->setAttributes($sanitizedPostData);
            $model->validate();
            $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }
    }
?>
