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

    class TaskTestHelper
    {
        public static function createTaskByNameForOwner($name, $owner)
        {
            $dueStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 10000);
            $completedStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 9000);
            $task = new Task();
            $task->name             = $name;
            $task->owner            = $owner;
            $task->dueDateTime       = $dueStamp;
            $task->completedDateTime = $completedStamp;
            $task->description      = 'my test description';
            $saved = $task->save();
            assert('$saved');
            return $task;
        }

        public static function createTaskWithOwnerAndRelatedAccount($name, $owner, $account, $status = Task::STATUS_IN_PROGRESS)
        {
            $dueStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 10000);
            $completedStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 9000);
            $task = new Task();
            $task->name             = $name;
            $task->owner            = $owner;
            $task->requestedByUser  = $owner;
            $task->dueDateTime       = $dueStamp;
            $task->completedDateTime = $completedStamp;
            $task->description      = 'my test description';
            $task->activityItems->add($account);
            $task->status = $status;
            $saved = $task->save();
            assert('$saved');
            return $task;
        }

        public static function createTaskWithOwnerAndRelatedItem($name, $owner, $item, $status = Task::STATUS_IN_PROGRESS)
        {
            $dueStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 10000);
            $completedStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 9000);
            $task = new Task();
            $task->name             = $name;
            $task->owner            = $owner;
            $task->requestedByUser  = $owner;
            $task->dueDateTime       = $dueStamp;
            $task->completedDateTime = $completedStamp;
            $task->description      = 'my test description';
            $task->activityItems->add($item);
            $task->status = $status;
            $saved = $task->save();
            assert('$saved');
            return $task;
        }

        public static function createTaskByNameWithProjectAndStatus($name, $owner, $project, $status)
        {
            $dueStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 10000);
            $completedStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 9000);
            $task = new Task();
            $task->name             = $name;
            $task->owner            = $owner;
            $task->dueDateTime      = $dueStamp;
            $task->status           = $status;
            $task->project          = $project;
            $saved                  = $task->save();
            assert('$saved');
            return $task;
        }

        public static function createKanbanItemForTask($task)
        {
            $id = $task->id;
            $kanbanItem  = KanbanItem::getByTask($id);
            assert('$kanbanItem === null');
            $kanbanItem = TasksUtil::createKanbanItemFromTask($task);
            assert('$kanbanItem !== null');
            return $kanbanItem;
        }
    }
?>
