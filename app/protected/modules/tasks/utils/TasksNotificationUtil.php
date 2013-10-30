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
    class TasksNotificationUtil extends NotificationsUtil
    {
        const NEW_TASK_NOTIFY_ACTION                = 'CreateTask';

        const CLOSE_TASK_NOTIFY_ACTION              = 'CloseTask';

        const CHANGE_TASK_OWNER_NOTIFY_ACTION       = 'TaskOwnerChange';

        const CHANGE_TASK_DUE_DATE_NOTIFY_ACTION    = 'TaskDueDateChange';

        const TASK_ADD_COMMENT_NOTIFY_ACTION        = 'TaskCommentAddition';

        /**
         * Submit task notification message
         * @param Task $task
         * @param string $action
         * @param User $relatedUser, the user associated with the task notification. In case of
         * owner change it would be previous owner, in case of comment, it would be the user
         * making the comment
         */
        public static function submitTaskNotificationMessage(Task $task, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            if ($action == self::NEW_TASK_NOTIFY_ACTION
                                    && $task->owner != $task->requestedByUser)
            {
                return;
            }
            $message = static::getNotificationMessageByAction($task, $action, $relatedUser);
            $rule = new TaskNotificationRules();
            $peopleToSendNotification = static::resolvePeopleToSendNotification($task, $action, $relatedUser);
            foreach ($peopleToSendNotification as $person)
            {
                $rule->addUser($person);
            }
            $rule->addUser($task->owner);
            $rule->setModel($task);
            $rule->setCritical(true);
            $rule->setAllowDuplicates(true);
            static::submitTaskNotification($message, $rule);
        }

        /**
         * Submits task notification
         * @param NotificationMessage $message
         * @param TaskNotificationRules $rule
         */
        public static function submitTaskNotification(NotificationMessage $message, $rule)
        {
            assert('$rule instanceof TaskNotificationRules');
            $users = $rule->getUsers();
            if (count($users) == 0)
            {
                throw new NotSupportedException();
            }
            static::processTaskNotification($message, $rule);
        }

        /**
         * Process task notification
         * @param NotificationMessage $message
         * @param TaskNotificationRules $rule
         */
        protected static function processTaskNotification(NotificationMessage $message, TaskNotificationRules $rule)
        {
            $users = $rule->getUsers();
            $notifications = static::resolveAndGetNotifications($users, $rule->getType(), $message, $rule->allowDuplicates());
            if (static::resolveShouldSendEmailIfCritical() && $rule->isCritical())
            {
                foreach ($notifications as $notification)
                {
                    static::sendTaskEmail($notification, $rule);
                }
            }
        }

        /**
         * Gets notification message by action
         * @param Task $task
         * @param string $action
         * @return NotificationMessage
         */
        protected static function getNotificationMessageByAction(Task $task, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            $message                      = new NotificationMessage();
            $message->htmlContent         = self::getEmailMessage($task, $action, $relatedUser);
            $url                          = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                                                array('id' => $task->id));
            $message->htmlContent        .= '-' . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            return $message;
        }

        /**
         * Gets notification subscribers
         * @param Task $model
         * @return string
         */
        public static function resolvePeopleToSendNotification(Task $task, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            $peopleToSendNotification = array();
            if($action == self::NEW_TASK_NOTIFY_ACTION)
            {
                $peopleToSendNotification = array($task->owner);
            }
            elseif($action == self::CLOSE_TASK_NOTIFY_ACTION)
            {
                $peopleToSendNotification = TasksUtil::resolvePeopleSubscribedForTask($task);
            }
            elseif($action == self::CHANGE_TASK_OWNER_NOTIFY_ACTION)
            {
                $peopleToSendNotification[] = $task->owner;
                if($relatedUser != null)
                {
                    $peopleToSendNotification[] = $relatedUser;
                }
            }
            elseif($action == self::CHANGE_TASK_DUE_DATE_NOTIFY_ACTION)
            {
                $peopleToSendNotification     = TasksUtil::resolvePeopleSubscribedForTask($task);
            }
            elseif($action == self::TASK_ADD_COMMENT_NOTIFY_ACTION)
            {
                $peopleToSendNotification = array($task->owner);
            }
            return $peopleToSendNotification;
        }

        /**
         * Gets email subject for the notification
         * @param Task $model
         * @param string $action
         * @return string
         */
        public static function getTaskEmailSubject($model, $action)
        {
            assert('$model instanceof Task');
            if($action == self::NEW_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW TASK {task}',
                                    array('{task}'   => $model->name));
            }
            elseif($action == self::CLOSE_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'COMPLETED TASK {task}',
                                    array('{task}'  => $model->name));
            }
            elseif($action == self::CHANGE_TASK_OWNER_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW OWNER {task}',
                                    array('{task}'  => $model->name));
            }
            elseif($action == self::CHANGE_TASK_DUE_DATE_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW DUE DATE {task}',
                                    array('{task}'  => $model->name));
            }
            elseif($action == self::TASK_ADD_COMMENT_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'NEW COMMENT {task}',
                                    array('{task}' => $model->name));
            }
        }

        /**
         * Gets email message for the notification
         * @param Task $model
         * @param string $action
         * @return string
         */
        public static function getEmailMessage(Task $model, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            if($action == self::NEW_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'A new task {task} has been assigned to you',
                                    array('{task}'   => $model->name));
            }
            elseif($action == self::CLOSE_TASK_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The task {task} has been completed.',
                                    array('{task}'   => $model->name));
            }
            elseif($action == self::CHANGE_TASK_OWNER_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The task {task} has been assigned to {owner}.',
                                    array('{task}'   => $model->name,
                                          '{owner}' => $model->owner->getFullName()));
            }
            elseif($action == self::CHANGE_TASK_DUE_DATE_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', 'The due date for task {task} has been updated to {duedate}',
                                    array('{task}' => $model->name, '{duedate}' =>
                                        DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($model->dueDateTime)));
            }
            elseif($action == self::TASK_ADD_COMMENT_NOTIFY_ACTION)
            {
                return Zurmo::t('TasksModule', '{userfullname} has commented on the task {task}',
                                    array('{task}'   => $model->name,
                                          '{userfullname}' => $relatedUser->getFullName()));
            }
        }

        /**
         * Send task email
         * @param Notification $notification
         * @param TaskNotificationRules $rule
         */
        protected static function sendTaskEmail(Notification $notification, $rule)
        {
            assert('$rule instanceof TaskNotificationRules');
            if ($notification->owner->primaryEmail->emailAddress !== null &&
                !UserConfigurationFormAdapter::resolveAndGetValue($notification->owner, 'turnOffEmailNotifications'))
            {
                $emailMessage               = static::makeEmailMessage($notification, $rule);
                $emailMessage->content      = static::makeEmailContent($notification);
                $emailMessage->sender       = static::makeSender();
                $emailMessage->recipients->add(static::makeRecipient($notification));
                $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                try
                {
                    Yii::app()->emailHelper->sendImmediately($emailMessage);
                }
                catch (CException $e)
                {
                    //Not sure what to do yet when catching an exception here. Currently ignoring gracefully.
                }
            }
        }

        /**
         * @param Notification $notification
         * @param TaskNotificationRules $rule
         * @return EmailMessage
         */
        protected static function makeEmailMessage(Notification $notification, $rule)
        {
            assert('$rule instanceof TaskNotificationRules');
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = Yii::app()->user->userModel;
            $task                       = $rule->getModel();
            $emailMessage->subject      = static::getTaskEmailSubject($task, $rule->getType());
            return $emailMessage;
        }

        /**
         * @param Notification $notification
         * @return EmailMessageContent
         */
        protected static function makeEmailContent(Notification $notification)
        {
            $emailContent               = new EmailMessageContent();
            $emailContent->textContent  = EmailNotificationUtil::
                                            resolveNotificationTextTemplate(
                                            $notification->notificationMessage->textContent);
            $emailContent->htmlContent  = EmailNotificationUtil::
                                            resolveNotificationHtmlTemplate(
                                            $notification->notificationMessage->htmlContent);
            return $emailContent;
        }

        /**
         * @return EmailMessageSender
         */
        protected static function makeSender()
        {
            $userToSendMessagesFrom     = BaseControlUserConfigUtil::getUserToRunAs();
            $sender                     = new EmailMessageSender();
            $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName           = strval($userToSendMessagesFrom);
            return $sender;
        }

        /**
         * @param Notification $notification
         * @return EmailMessageRecipient
         */
        protected static function makeRecipient(Notification $notification)
        {
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = $notification->owner->primaryEmail->emailAddress;
            $recipient->toName          = strval($notification->owner);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($notification->owner);
            return $recipient;
        }
    }
?>