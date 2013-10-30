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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class TasksTaskCheckItemsController extends ZurmoModuleController
    {
        /**
         * Action for saving a new task check item inline edit form.
         * @param string or array $redirectUrl
         */
        public function actionInlineCreateTaskCheckItemSave($relatedModelId, $relatedModelClassName,
                                                            $relatedModelRelationName, $redirectUrl = null)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'task-check-item-inline-edit-form')
            {
                $this->actionInlineEditValidate(new TaskCheckListItem());
            }
            $taskCheckListItem          = new TaskCheckListItem();
            $postData                   = PostUtil::getData();
            $postFormData               = ArrayUtil::getArrayValue($postData, get_class($taskCheckListItem));
            $taskCheckListItem->name    = $postFormData['name'];
            $task                       = Task::getById(intval($relatedModelId));
            $task->checkListItems->add($taskCheckListItem);
            $saved = $task->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            if($task->project->id > 0)
            {
                ProjectsUtil::logTaskCheckItemEvent($task, $taskCheckListItem);
            }
            if($redirectUrl != null)
            {
                $this->redirect($redirectUrl);
            }
        }

        /**
         * Create inline task check item using ajax
         * @param int $id
         * @param string $uniquePageId
         */
        public function actionInlineCreateTaskCheckItemFromAjax($id, $uniquePageId)
        {
            $taskCheckListItem  = new TaskCheckListItem();
            $redirectUrl        = Yii::app()->createUrl('/tasks/taskCheckItems/inlineCreateTaskCheckItemFromAjax',
                                                    array('id' => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters      = array('relatedModelId'           => $id,
                                        'relatedModelClassName'    => 'Task',
                                        'relatedModelRelationName' => 'checkListItems',
                                        'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $inlineView         = new TaskCheckItemInlineEditView($taskCheckListItem, 'taskCheckItems', 'tasks', 'inlineCreateTaskCheckItemSave', $urlParameters, $uniquePageId);
            $view               = new AjaxPageView($inlineView);
            echo $view->render();
        }

        /**
         * @param RedBeanModel $model
         */
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

        /**
         * Get check item list for the task using ajax
         * @param string $uniquePageId
         */
        public function actionAjaxCheckItemListForRelatedTaskModel($uniquePageId = null)
        {
            $getData                  = GetUtil::getData();
            $relatedModelId           = ArrayUtil::getArrayValue($getData, 'relatedModelId');
            $relatedModelClassName    = ArrayUtil::getArrayValue($getData, 'relatedModelClassName');
            $relatedModelRelationName = ArrayUtil::getArrayValue($getData, 'relatedModelRelationName');
            $taskCheckListItem        = TaskCheckListItem::getByTask((int)$relatedModelId);
            $getParams                = array('uniquePageId'             => $uniquePageId,
                                              'relatedModelId'           => $relatedModelId,
                                              'relatedModelClassName'    => $relatedModelClassName,
                                              'relatedModelRelationName' => $relatedModelRelationName);
            $relatedModel             = $relatedModelClassName::getById((int)$relatedModelId);
            $view                     = new TaskCheckListItemsForTaskView('taskCheckItems', 'tasks',
                                                                          $taskCheckListItem, $relatedModel,
                                                                          null, $getParams);
            $content                  = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        /**
         * Update status of task check item using ajax
         * @param int $id
         * @param bool $checkListItemCompleted
         */
        public function actionUpdateStatusViaAjax($id, $checkListItemCompleted)
        {
            $taskCheckListItem = TaskCheckListItem::getById(intval($id));
            $taskCheckListItem->completed = (bool)$checkListItemCompleted;
            $taskCheckListItem->unrestrictedSave();
        }

        /**
         * Update checklist item name
         */
        public function actionUpdateNameViaAjax($id, $name)
        {
            $taskCheckListItem       = TaskCheckListItem::getById(intval($id));
            $taskCheckListItem->name = $name;
            $taskCheckListItem->unrestrictedSave();
            echo $name;
        }

        /**
         * Delete checklist item
         */
        public function actionDeleteCheckListItem($id, $taskId)
        {
            $task              = Task::getById((int)$taskId);
            $taskCheckListItem = TaskCheckListItem::getById(intval($id));
            $task->checkListItems->remove($taskCheckListItem);
            $saved = $task->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $getParams                = array('uniquePageId'             => null,
                                              'relatedModelId'           => $task->id,
                                              'relatedModelClassName'    => 'Task',
                                              'relatedModelRelationName' => 'task');
            $url = Yii::app()->createUrl('tasks/taskCheckItems/ajaxCheckItemListForRelatedTaskModel', $getParams);
            $this->redirect($url);
        }
    }
?>