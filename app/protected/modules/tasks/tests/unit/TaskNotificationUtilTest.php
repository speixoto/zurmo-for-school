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

    class TaskNotificationUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        /**
         * @covers makeAndSubmitNewTaskNotificationMessage
         */
        public function testTaskNotifications()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $count = Notification::getCount();
            $this->assertEquals(0, $count);
            $task                       = new Task();
            $task->name                 = 'My Task';
            $task->owner                = Yii::app()->user->userModel;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertTrue($task->save());

            $newCount = Notification::getCount();
            $this->assertEquals(1, $newCount);
            $user                       = UserTestHelper::createBasicUser('Billy');
            $task->owner                = $user;
            $this->assertTrue($task->save());
            $newCount = Notification::getCount();
            $this->assertEquals(2, $newCount);

            $dueDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $task->dueDateTime = $dueDateTime;
            $this->assertTrue($task->save());
            $newCount = Notification::getCount();
            //As there are two default subscribers owner and requested by user
            $this->assertEquals(4, $newCount);

            $comment                = new Comment();
            $comment->description   = 'My Description';
            $task->comments->add($comment);
            $this->assertTrue($task->save());
            TasksNotificationUtil::submitTaskNotificationMessage($task,
                                                                    TasksNotificationUtil::TASK_ADD_COMMENT_NOTIFY_ACTION,
                                                                    $task->comments[0]->createdByUser);
            $newCount = Notification::getCount();
            $this->assertEquals(5, $newCount);

            $task->completed         = true;
            $task->status            = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $newCount = Notification::getCount();
            $this->assertEquals(7, $newCount);
            $this->assertTrue(strtotime($task->completedDateTime) > strtotime(date('Y-m-d')));
        }
    }
?>