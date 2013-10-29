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
     * Helper class for working with tasks notification
     */
    class TasksNotificationUtil
    {
        const NEW_TASK_NOTIFY_ACTION = 1;

        const CLOSE_TASK_NOTIFY_ACTION = 2;

        const CHANGE_TASK_OWNER_NOTIFY_ACTION = 3;

        const CHANGE_TASK_DUE_DATE_NOTIFY_ACTION = 4;

        const TASK_ADD_COMMENT_NOTIFY_ACTION = 5;

        /**
         * @param Task $task
         */
        public static function makeAndSubmitNewTaskNotificationMessage(Task $task)
        {
            if ($task->owner != $task->requestedByUser)
            {
                $message                      = new NotificationMessage();
                $message->htmlContent         = self::getEmailMessage($task, NEW_TASK_NOTIFY_ACTION);
                $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                    array('id' => $task->id));
                $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
                $rules = new CreateTaskNotificationRules();
                $rules->addUser($task->owner);
                NotificationsUtil::submit($message, $rules);
            }
        }

        /**
         * @param Task $task
         */
        public static function makeAndSubmitCloseTaskNotificationMessage(Task $task)
        {
            $peopleToSendNotification     = TasksUtil::resolvePeopleSubscribedForTask($task);
            $message                      = new NotificationMessage();
            $message->htmlContent         = self::getEmailMessage($task, CLOSE_TASK_NOTIFY_ACTION);
            $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                array('id' => $task->id));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            $rules = new CloseTaskNotificationRules();
            foreach ($peopleToSendNotification as $person)
            {
                $rules->addUser($person);
            }
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * @param Task $task
         * @param User $previousOwner
         */
        public static function makeAndSubmitOwnerChangedNotificationMessage(Task $task, User $previousOwner)
        {
            $message                      = new NotificationMessage();
            $message->htmlContent         = self::getEmailMessage($task, CHANGE_TASK_OWNER_NOTIFY_ACTION);
            $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                array('id' => $task->id));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            $rules = new TaskOwnerChangeNotificationRules();
            $rules->addUser($task->owner);
            $rules->addUser($previousOwner);
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * @param Task $task
         */
        public static function makeAndSubmitDueDateChangedNotificationMessage(Task $task)
        {
            $peopleToSendNotification     = TasksUtil::resolvePeopleSubscribedForTask($task);
            $message                      = new NotificationMessage();
            $message->htmlContent         = self::getEmailMessage($task, CHANGE_TASK_DUE_DATE_NOTIFY_ACTION);
            $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                array('id' => $task->id));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            $rules = new TaskDueDateChangeNotificationRules();
            foreach ($peopleToSendNotification as $person)
            {
                $rules->addUser($person);
            }
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * @param Task $task
         */
        public static function makeAndSubmitNewCommentNotificationMessage(Task $task)
        {
            $message                      = new NotificationMessage();
            $message->htmlContent         = self::getEmailMessage($task, TASK_ADD_COMMENT_NOTIFY_ACTION);
            $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                array('id' => $task->id));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            $rules = new TaskCommentAdditionNotificationRules();
            $rules->addUser($task->owner);
            NotificationsUtil::submit($message, $rules);
        }

        /**
         * Gets email subject for the notification
         * @param Task $model
         * @return string
         */
        public static function getEmailSubject($model, $action)
        {
            assert('$model instanceof Task');
            if($action == NEW_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW TASK {task}',
                                    array('{task}'   => $model->name));
            }
            elseif($action == CLOSE_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'COMPLETED TASK {task}',
                                    array('{task}'   => $model->name));
            }
            elseif($action == CHANGE_TASK_OWNER_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW OWNER {task}',
                                    array('{task}'   => $model->name));
            }
            elseif($action == CHANGE_TASK_DUE_DATE_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW DUE DATE {task}',
                                    array('{task}'   => $model->name));
            }
            elseif($action == TASK_ADD_COMMENT_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW COMMENT {task}',
                                    array('{task}'   => $model->name));
            }
        }

        /**
         * Gets email message for the notification
         * @param Task $model
         * @param string $action
         * @return string
         */
        public static function getEmailMessage($model, $action)
        {
            assert('$model instanceof Task');
            if($action == NEW_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'A new task {task} has been assigned to you',
                                    array('{task}'   => $model->name));
            }
            elseif($action == CLOSE_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The Task {task} has been completed.',
                                    array('{task}'   => $model->name));
            }
            elseif($action == CHANGE_TASK_OWNER_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The Task {task} has been assigned to {owner}.',
                                    array('{task}'   => $model->name,
                                          '{owner}' => $model->owner->getFullName()));
            }
            elseif($action == CHANGE_TASK_DUE_DATE_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The due date for task {task} has been updated to {duedate}',
                                    array('{task}' => $model->name, '{duedate}' =>
                                        DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($model->dueDateTime)));
            }
            elseif($action == TASK_ADD_COMMENT_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', '{userfullname} has commented on the task {task}',
                                    array('{task}'   => $model->name));
            }
        }
    }
?>