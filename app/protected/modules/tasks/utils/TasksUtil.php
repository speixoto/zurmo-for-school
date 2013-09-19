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

    /**
     * Helper class for working with tasks
     */
    class TasksUtil
    {
        /**
         * Given a Task and User, determine if the user is already a subscriber.
         * @param Task $model
         * @param User $user
         * @return boolean
         */
        public static function isUserSubscribedForTask(Task $model, User $user)
        {
            if ($model->notificationSubscribers->count() > 0)
            {
                foreach ($model->notificationSubscribers as $subscriber)
                {
                    if ($subscriber->person->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Get task subscriber data
         * @param Task $task
         * @return string
         */
        public static function getTaskSubscriberData(Task $task)
        {
            $content = null;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $user           = $subscriber->person->castDown(array($modelDerivationPathToItem));
                $userUrl        = Yii::app()->createUrl('/users/default/details', array('id' => $user->id));
                $stringContent  = ZurmoHtml::link($user->getAvatarImage(36), $userUrl);
                $content        .= '<p>' . $stringContent . '</p>';
            }

            return $content;
        }

        /**
         * Gets task participant
         * @param Task $task
         * @return array
         */
        public static function getTaskSubscribers(Task $task)
        {
            $subscribers = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $subscribers[] = $subscriber->person->castDown(array($modelDerivationPathToItem));
            }
            return $subscribers;
        }

        /**
         * Given a Task and the User that updates the task
         * return the people on the task to send new notification to
         * @param Task $task
         * @param User $user
         * @return Array $peopleToSendNotification
         */
        public static function resolvePeopleToSendNotificationToOnTaskUpdate(Task $task, User $user)
        {
            $peopleToSendNotification = array();
            $peopleSubscribedForTask     = self::resolvePeopleSubscribedForTask($task);
            foreach ($peopleSubscribedForTask as $people)
            {
                if (!$people->isSame($user))
                {
                    $peopleToSendNotification[] = $people;
                }
            }
            return $peopleToSendNotification;
        }

        /**
         * Resolve people on task
         * @param Task $task
         * @return array
         */
        public static function resolvePeopleSubscribedForTask(Task $task)
        {
            $people   = self::getTaskSubscribers($task);
            $people[] = $task->owner;
            $people[] = $task->requestedByUser;
            return $people;
        }

        /**
         * Send notification to user on task update
         * @param Task $task
         * @param type $senderPerson
         * @param type $peopleToSendNotification
         * @return type
         */
        public static function sendNotificationOnTaskUpdate(Task $task, $message)
        {
            $senderPerson = Yii::app()->user->userModel;
            $peopleToSendNotification = self::resolvePeopleToSendNotificationToOnTaskUpdate($task, $senderPerson);
            if (count($peopleToSendNotification) > 0)
            {
                $emailRecipients = array();
                foreach ($peopleToSendNotification as $people)
                {
                    if ($people->primaryEmail->emailAddress !== null &&
                    !UserConfigurationFormAdapter::resolveAndGetValue($people, 'turnOffEmailNotifications'))
                    {
                        $emailRecipients[] = $people;
                    }
                }
                $subject = self::getEmailSubject($task);
                $content = self::getEmailContent($task, $message, $senderPerson);
                if ($emailRecipients > 0)
                {
                    EmailNotificationUtil::resolveAndSendEmail($senderPerson, $emailRecipients, $subject, $content);
                }
                else
                {
                    return;
                }
            }
            else
            {
                return;
            }
        }

        /**
         * Get email content
         * @param RedBeanModel $model
         * @param string $message
         * @param User $user
         * @return EmailMessageContent
         */
        public static function getEmailContent(RedBeanModel $model, $message, User $user)
        {
            $emailContent  = new EmailMessageContent();
            $url           = static::getUrlToEmail($model);
            $textContent   = Zurmo::t('TasksModule', "Hello, {lineBreak} {updaterName} updates to the " .
                                             "{strongStartTag}{modelName}{strongEndTag}: {lineBreak}" .
                                             "\"{message}.\" {lineBreak}{lineBreak} {url} ",
                                    array('{lineBreak}'           => "\n",
                                          '{strongStartTag}'      => null,
                                          '{strongEndTag}'        => null,
                                          '{updaterName}'         => strval($user),
                                          '{modelName}'           => $model->getModelLabelByTypeAndLanguage(
                                                                     'SingularLowerCase'),
                                          '{message}'             => strval($message),
                                          '{url}'                 => ZurmoHtml::link($url, $url)
                                        ));
            $emailContent->textContent  = EmailNotificationUtil::
                                                resolveNotificationTextTemplate($textContent);
            $htmlContent = Zurmo::t('TasksModule', "Hello, {lineBreak} {updaterName} updates to the " .
                                             "{strongStartTag}{url}{strongEndTag}: {lineBreak}" .
                                             "\"{message}.\"",
                               array('{lineBreak}'           => "<br/>",
                                     '{strongStartTag}'      => '<strong>',
                                     '{strongEndTag}'        => '</strong>',
                                     '{updaterName}'         => strval($user),
                                     '{message}'             => strval($message),
                                     '{url}'                 => ZurmoHtml::link($model->getModelLabelByTypeAndLanguage(
                                                                'SingularLowerCase'), $url)
                                   ));
            $emailContent->htmlContent  = EmailNotificationUtil::resolveNotificationHtmlTemplate($htmlContent);
            return $emailContent;
        }

        /**
         * Gets email subject for the notification
         * @param Task $model
         * @return type
         */
        public static function getEmailSubject($model)
        {
            if ($model instanceof Task)
            {
                return Zurmo::t('TasksModule', 'New update on {modelName}: {subject}',
                                    array('{subject}'   => strval($model),
                                          '{modelName}' => $model->getModelLabelByTypeAndLanguage('SingularLowerCase')));
            }
        }

        /**
         * Gets url to task detail view
         * @param RedBeanModel $model
         * @return string
         */
        public static function getUrlToEmail($model)
        {
            return Yii::app()->createAbsoluteUrl('tasks/default/details/', array('id' => $model->id));
        }

        /**
         * Given a Task and the User that created the new comment
         * return the people on the task to send new notification to
         * @param Task $conversation
         * @param User $user
         * @return Array $peopleToSendNotification
         */
        public static function  resolvePeopleToSendNotificationToOnNewComment(Task $task, User $user)
        {
            $peopleToSendNotification    = array();
            $peopleSubscribedForTask     = self::resolvePeopleSubscribedForTask($task);
            foreach ($peopleSubscribedForTask as $people)
            {
                if (!$people->isSame($user))
                {
                    $peopleToSendNotification[] = $people;
                }
            }
            return $peopleToSendNotification;
        }

        /**
         * Resolve explicit permissions of the requested by user for the task
         * @param Task $task
         * @param Permitable $origRequestedByUser
         */
        public static function resolveExplicitPermissionsForRequestedByUser(Task $task, $origRequestedByUser, $requestedByUser, $explicitReadWriteModelPermissions)
        {
            ExplicitReadWriteModelPermissionsUtil::
                                        resolveExplicitReadWriteModelPermissions($task, $explicitReadWriteModelPermissions);
            if ($origRequestedByUser instanceof Permitable)
            {
                  if($origRequestedByUser->username != 'super')
                  {
                    $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($origRequestedByUser);
                  }
            }
            if ($requestedByUser instanceof Permitable)
            {
                  if($requestedByUser->username != 'super')
                  {
                    $explicitReadWriteModelPermissions->addReadWritePermitable($requestedByUser);
                  }
            }
        }

        /**
         * Given a task and a user, mark that the user has read or not read the latest changes as a task
         * owner, requested by user or subscriber
         * @param Task $task
         * @param User $user
         * @param Boolean $hasReadLatest
         */
        public static function markUserHasReadLatest(Task $task, User $user, $hasReadLatest = true)
        {
            assert('$task->id > 0');
            assert('$user->id > 0');
            assert('is_bool($hasReadLatest)');
            $save = false;
            foreach ($task->notificationSubscribers as $position => $subscriber)
            {
                if ($subscriber->person->getClassId('Item') == $user->getClassId('Item') && $subscriber->hasReadLatest != $hasReadLatest)
                {
                    $task->notificationSubscribers[$position]->hasReadLatest = $hasReadLatest;
                    $save                                                    = true;
                }
            }

            if ($save)
            {
                $task->save();
            }
        }

        /**
         * @return string
         */
        public static function getModalTitleForViewTask()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('TasksModule', 'View TasksModuleSingularLabel', $params);
            return $title;
        }

        /**
         * Gets modal title for create task modal window
         * @return string
         */
        public static function getModalTitleForCreateTask($renderType = "Create")
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            if($renderType == "Create")
            {
                $title = Zurmo::t('TasksModule', 'Create TasksModuleSingularLabel', $params);
            }
            elseif($renderType == "Copy")
            {
                $title = Zurmo::t('TasksModule', 'Copy TasksModuleSingularLabel', $params);
            }
            else
            {
                $title = Zurmo::t('TasksModule', 'Edit TasksModuleSingularLabel', $params);
            }
            return $title;
        }

        /**
         * Resolves ajax options for create link
         * @return array
         */
        public static function resolveAjaxOptionsForCreateMenuItem()
        {
            $title = self::getModalTitleForCreateTask("Create");
            return   ModalView::getAjaxOptionsForModalLink($title, self::getModalContainerId());
        }

        /**
         * @return string
         */
        public static function getModalContainerId()
        {
            return ModalLinkActionElement::RELATED_MODAL_CONTAINER_PREFIX . '-open-tasks';
        }

        /**
         * @return string
         */
        public static function getViewModalContainerId()
        {
            return ModalLinkActionElement::RELATED_MODAL_CONTAINER_PREFIX . '-view-task';
        }

        /**
         * Resolves view ajax options for selecting model
         * @return array
         */
        public static function resolveViewAjaxOptionsForSelectingModel()
        {
            $title = self::getModalTitleForViewTask();
            return   ModalView::getAjaxOptionsForModalLink($title, self::getViewModalContainerId());
        }

        /**
         * @return string
         */
        public static function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => self::getModalContainerId()), '');
        }

        /**
         * @return string
         */
        public static function renderViewModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => self::getViewModalContainerId()), '');
        }

        /**
         * Resolves ajax options for selecting model
         * @return array
         */
        public static function resolveAjaxOptionsForEditModel($renderType)
        {
            $title = self::getModalTitleForCreateTask($renderType);
            return   ModalView::getAjaxOptionsForModalLink($title, self::getModalContainerId());
        }

        /**
         * Get link for view task in modal mode
         * @param array $data
         * @param int $row
         * @param string $controllerId
         * @param string $moduleId
         * @param array $params
         * @param string $moduleClassName
         * @return string
         */
        public function getLinkForViewModal($data, $row, $controllerId, $moduleId, $moduleClassName)
        {
            $ajaxOptions = TasksUtil::resolveViewAjaxOptionsForSelectingModel();
            $title       = Zurmo::t('TasksModule', $data->name);
            $params      = array('label' => $title, 'routeModuleId' => 'tasks', 'ajaxOptions' => $ajaxOptions);
            $viewFromRelatedModalLinkActionElement = new ViewFromRelatedModalLinkActionElement($controllerId, $moduleId, $data->id, $params);
            $linkContent = $viewFromRelatedModalLinkActionElement->render();
            $string      = TaskActionSecurityUtil::resolveViewLinkToModelForCurrentUser($data, $moduleClassName, $linkContent);
            return $string;
        }

        /**
         * Resolve status for task
         * @param int $statusId
         * @return string
         */
        public static function resolveActionButtonForTaskByStatus($statusId, $controllerId, $moduleId, $modelId)
        {
            $type = self::resolveKanbanItemTypeForTaskStatus(intval($statusId));
            $route = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView');
            switch(intval($statusId))
            {
                case Task::TASK_STATUS_NEW :
                     $element = new TaskStartLinkActionElement($controllerId, $moduleId, $modelId,
                                                                                            array('route' => $route));
                    break;
                case Task::TASK_STATUS_IN_PROGRESS :

                     $element = new TaskFinishLinkActionElement($controllerId, $moduleId, $modelId,
                                                                                            array('route' => $route));
                    break;
                case Task::TASK_STATUS_AWAITING_ACCEPTANCE :

                     $acceptLinkElement = new TaskAcceptLinkActionElement($controllerId, $moduleId, $modelId,
                                                                                            array('route' => $route));
                     $rejectLinkElement = new TaskRejectLinkActionElement($controllerId, $moduleId, $modelId,
                                                                                            array('route' => $route));
                     return $acceptLinkElement->render() . $rejectLinkElement->render();
                case Task::TASK_STATUS_COMPLETED :
                     return null;
                default:
                     $element = new TaskStartLinkActionElement($controllerId, $moduleId, $modelId,
                                                                                            array('route' => $route));
                    break;
            }
            return $element->render();

        }

        /**
         * Maps task status to kanban item type
         * @return array
         */
        public static function getTaskStatusMappingToKanbanItemTypeArray()
        {
            return array(
                            Task::TASK_STATUS_NEW                   => KanbanItem::TYPE_TODO,
                            Task::TASK_STATUS_IN_PROGRESS           => KanbanItem::TYPE_IN_PROGRESS,
                            Task::TASK_STATUS_AWAITING_ACCEPTANCE   => KanbanItem::TYPE_IN_PROGRESS,
                            Task::TASK_STATUS_REJECTED              => KanbanItem::TYPE_TODO,
                            Task::TASK_STATUS_COMPLETED             => KanbanItem::TYPE_COMPLETED
                        );
        }

        /**
         * Resolve kanban item type for task status
         * @return int
         */
        public static function resolveKanbanItemTypeForTaskStatus($status)
        {
            if($status == null)
            {
                return KanbanItem::TYPE_TODO;
            }
            $data = self::getTaskStatusMappingToKanbanItemTypeArray();
            return $data[intval($status)];
        }

        /**
         * Resolve kanban item type for task
         * @param int $taskId
         * @return int
         */
        public static function resolveKanbanItemTypeForTask($taskId)
        {
            $task = Task::getById($taskId);
            $status = $task->status;
            if($status == null)
            {
                return KanbanItem::TYPE_TODO;
            }
            $data = self::getTaskStatusMappingToKanbanItemTypeArray();
            return $data[intval($status)];
        }

        /**
         * Resolves Subscribe Url
         * @return string
         */
        public static function resolveSubscribeUrl($taskId)
        {
            return Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $taskId));
        }

        /**
         * Resolve subscriber ajax options
         * @return array
         */
        public static function resolveSubscriberAjaxOptions()
        {
            return array(
                'type'    => 'GET',
                'dataType'=> 'html',
                'data'    => array(),
                'success' => 'function(data)
                              {
                                $("#subscribe-task-link").hide();
                                $("#subscriberList").replaceWith(data);
                              }'
            );
        }

        /**
         * Register subscription script
         */
        public static function registerSubscriptionScript($taskId = null)
        {
            $unsubscribeLink = '<strong>' . Zurmo::t('TasksModule', 'Unsubscribe') . '</strong>';
            if($taskId == null)
            {
                $url             = Yii::app()->createUrl('tasks/default/addKanbanSubscriber');
                $script      = self::getKanbanSubscriptionScript($url, 'subscribe-task-link', 'unsubscribe-task-link', $unsubscribeLink);
                Yii::app()->clientScript->registerScript('kanban-subscribe-task-link-script', $script);
            }
            else
            {
                $url             = Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $taskId));
                $script      = self::getDetailSubscriptionScript($url, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link', $unsubscribeLink, $taskId);
                Yii::app()->clientScript->registerScript('detail-subscribe-task-link-script', $script);
            }
        }

        /**
         * Register unsubscription script
         */
        public static function registerUnsubscriptionScript($taskId = null)
        {
            $subscribeLink = '<strong>' . Zurmo::t('TasksModule', 'Subscribe') . '</strong>';
            if($taskId == null)
            {
                $url           = Yii::app()->createUrl('tasks/default/removeKanbanSubscriber');
                $script    = self::getKanbanSubscriptionScript($url, 'unsubscribe-task-link', 'subscribe-task-link', $subscribeLink);
                Yii::app()->clientScript->registerScript('kanban-unsubscribe-task-link-script', $script);
            }
            else
            {
                $url             = Yii::app()->createUrl('tasks/default/removeSubscriber', array('id' => $taskId));
                $script    = self::getDetailSubscriptionScript($url, 'detail-unsubscribe-task-link', 'detail-subscribe-task-link', $subscribeLink, $taskId);
                Yii::app()->clientScript->registerScript('detail-unsubscribe-task-link-script', $script);
            }
        }

        /**
         * Get subscription script
         * @param string $url
         * @param string $sourceClass
         * @param string $targetClass
         * @param string $link
         * @return string
         */
        public static function getKanbanSubscriptionScript($url, $sourceClass, $targetClass, $link)
        {
            return "$('body').on('click', '." . $sourceClass . "', function()
                                                    {
                                                        var linkElement = $(this);
                                                        var element     = $(this).parent().parent().parent();
                                                        var id          = $(element).attr('id');
                                                        var idParts     = id.split('_');
                                                        var taskId      = parseInt(idParts[1]);
                                                        $.ajax(
                                                        {
                                                            type : 'GET',
                                                            data : {'id':taskId},
                                                            url  : '" . $url . "',
                                                            success : function(data)
                                                                      {
                                                                        $(linkElement).html('" . $link . "');
                                                                        $(linkElement).attr('class', '" . $targetClass . "');
                                                                      }
                                                        }
                                                        );
                                                    }
                                                );";
        }

        /**
         * Get subscription script
         * @param string $url
         * @param string $sourceClass
         * @param string $targetClass
         * @param string $link
         * @return string
         */
        public static function getDetailSubscriptionScript($url, $sourceClass, $targetClass, $link, $taskId)
        {
            return "$('body').on('click', '." . $sourceClass . "', function()
                                                    {
                                                        var linkElement = $(this);
                                                        $.ajax(
                                                        {
                                                            type : 'GET',
                                                            url  : '" . $url . "',
                                                            success : function(data)
                                                                      {
                                                                        $(linkElement).html('" . $link . "');
                                                                        $(linkElement).attr('class', '" . $targetClass . "');
                                                                        if(data == '')
                                                                        {
                                                                            $('#subscriberList').html('');
                                                                        }
                                                                        else
                                                                        {
                                                                            $('#subscriberList').html(data);
                                                                        }
                                                                      }
                                                        }
                                                        );
                                                    }
                                                );";
        }

        /**
         * Get subscription link for the task
         * @param Task $task
         * @param int $row
         * @return string
         */
        public static function getKanbanSubscriptionLink($task, $row)
        {
            return self::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
        }

        /**
         * Get subscription link for the task
         * @param Task $task
         * @param int $row
         * @return string
         */
        public static function getDetailSubscriptionLink($task, $row)
        {
            return self::resolveSubscriptionLink($task, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link');
        }

        /**
         * Resolve subscription link for detail and kanban view
         * @param Task $task
         * @param string $subscribeLinkClass
         * @param string $unsubscribeLinkClass
         * @return string
         */
        public static function resolveSubscriptionLink($task, $subscribeLinkClass, $unsubscribeLinkClass)
        {
            if(TasksUtil::isUserSubscribedForTask($task, Yii::app()->user->userModel) === false)
            {
                return ZurmoHtml::link('<strong>' . Zurmo::t('TasksModule', 'Subscribe') . '</strong>', '#', array('class' => $subscribeLinkClass)) ;
            }
            else
            {
                return ZurmoHtml::link('<strong>' . Zurmo::t('TasksModule', 'Unsubscribe') . '</strong>', '#', array('class' => $unsubscribeLinkClass)) ;
            }
        }

        /**
         * Gets task percentage
         * @param int $id
         */
        public static function getTaskCompletionPercentage($id)
        {
            $task = Task::getById($id);
            $checkListItemsCount = count($task->checkListItems);
            $completedItemsCount = 0;
            if($checkListItemsCount == 0)
            {
                return null;
            }
            else
            {
                $completedItemsCount = self::getTaskCompletedCheckListItems($task);
            }
            $completionPercent = ($completedItemsCount/$checkListItemsCount)*100;
            return $completionPercent;
        }

        /**
         * Maps task status to kanban item type
         * @return array
         */
        public static function getKanbanItemTypeToDefaultTaskStatusMappingArray()
        {
            return array(
                            KanbanItem::TYPE_TODO                   => Task::TASK_STATUS_NEW,
                            KanbanItem::TYPE_SOMEDAY                => Task::TASK_STATUS_NEW,
                            KanbanItem::TYPE_IN_PROGRESS            => Task::TASK_STATUS_IN_PROGRESS,
                            KanbanItem::TYPE_COMPLETED              => Task::TASK_STATUS_COMPLETED
                        );
        }

        /**
         * Gets default task status for kanban item type
         * @param int $kanbanItemType
         */
        public static function getDefaultTaskStatusForKanbanItemType($kanbanItemType)
        {
            $mappingArray = self::getKanbanItemTypeToDefaultTaskStatusMappingArray();
            return $mappingArray[$kanbanItemType];
        }

        /**
         * Set default values for task
         * @param Task $task
         */
        public static function setDefaultValuesForTask($task)
        {
            $user = Yii::app()->user->userModel;
            $task->requestedByUser = $user;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = $user;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
        }

        /**
         * Saves the kanban item from task
         * @param type array
         */
        public static function createKanbanItemFromTask($data)
        {
            $kanbanItem                     = new KanbanItem();
            $kanbanItem->type               = TasksUtil::resolveKanbanItemTypeForTaskStatus($data->status);
            $kanbanItem->task               = $data;
            $kanbanItem->kanbanRelatedItem  = $data->activityItems->offsetGet(0);
            $sortOrder = KanbanItem::getMaximumSortOrderByType($kanbanItem->type);
            $kanbanItem->sortOrder          = $sortOrder;
            $kanbanItem->save();
            return $kanbanItem;
        }

        /**
         * @return array
         */
        public static function getGroupByDataAndTranslatedLabels()
        {
            $data = array(KanbanItem::TYPE_SOMEDAY      => Zurmo::t('ProductsModule', 'Someday'),
                          KanbanItem::TYPE_TODO         => Zurmo::t('ProductsModule', 'To Do'),
                          KanbanItem::TYPE_IN_PROGRESS  => Zurmo::t('ProductsModule', 'In Progress'),
                          KanbanItem::TYPE_COMPLETED    => Zurmo::t('ProductsModule', 'Completed'));

            return $data;
        }

        /**
         * Render completion progress bar
         * @param int $id
         * @return string
         */
        public static function renderCompletionProgressBar($data)
        {
            $percentage = TasksUtil::getTaskCompletionPercentage(intval($data->id));
            if($percentage == null)
            {
                return null;
            }
            else
            {
                return Zurmo::t('TasksModule', '% Complete - ' . $percentage);
            }
        }

        /**
         * Get task completed check list items
         * @param Task $task
         * @return int
         */
        public static function getTaskCompletedCheckListItems($task)
        {
            $completedItemsCount = 0;
            foreach($task->checkListItems as $checkListItem)
            {
                    if((bool)$checkListItem->completed)
                    {
                        $completedItemsCount++;
                    }
            }
            return $completedItemsCount;
        }
    }
?>