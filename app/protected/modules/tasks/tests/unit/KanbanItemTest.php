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

    class KanbanItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testKanbanItemSave()
        {
            $accounts = Account::getByName('anAccount');

            $user                   = UserTestHelper::createBasicUser('Billy');
            $task                   = new Task();
            $task->name             = 'MyTask';
            $task->owner            = $user;
            $task->requestedByUser  = $user;
            $task->description      = 'my test description';
            $taskCheckListItem      = new TaskCheckListItem();
            $taskCheckListItem->name = 'Test Check List Item';
            $task->checkListItems->add($taskCheckListItem);
            $task->activityItems->add($accounts[0]);
            $task->status           = Task::TASK_STATUS_IN_PROGRESS;
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);

            //Create KanbanItem here
            $kanbanItem                     = new KanbanItem();
            $kanbanItem->type               = TasksUtil::resolveKanbanItemTypeForTaskStatus($task->status);
            $kanbanItem->task               = $task;
            $kanbanItem->kanbanRelatedItem  = $task->activityItems->offsetGet(0);
            $sortOrder = KanbanItem::getMaximumSortOrderByType($kanbanItem->type);
            $kanbanItem->sortOrder          = $sortOrder;
            $saved = $kanbanItem->save();
            $kanbanItemId = $kanbanItem->id;
            $this->assertTrue($saved);

            $kanbanItem                     = KanbanItem::getById($kanbanItemId);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem->type);

            $this->assertEquals(1, count(KanbanItem::getAll()));
        }

        public function testGetKanbanItemByTask()
        {
            $tasks = Task::getByName('MyTask');
            $kanbanItem = KanbanItem::getByTask($tasks[0]->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem->type);
            $this->assertEquals($kanbanItem->kanbanRelatedItem, $tasks[0]->activityItems->offsetGet(0));
        }

        public function testGetMaximumSortOrderByType()
        {
            $tasks = Task::getByName('MyTask');
            $task  = $tasks[0];
            $kanbanItem = KanbanItem::getByTask($task->id);
            $sortOrder = KanbanItem::getMaximumSortOrderByType($kanbanItem->type);
            $this->assertEquals(2, $sortOrder);
        }
    }
?>
