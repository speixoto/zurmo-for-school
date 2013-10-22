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

    class TasksUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            TaskTestHelper::createTaskByNameForOwner('My Task', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testMarkUserHasReadLatest()
        {
            $super                     = User::getByUsername('super');
            $steven                    = UserTestHelper::createBasicUser('steven');

            $task = new Task();
            $task->name = 'MyTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $user = Yii::app()->user->userModel;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = $steven;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $this->assertTrue($task->save());

            $id = $task->id;
            $task->forget();
            unset($task);

            $task = Task::getById($id);
            $this->assertEquals(0, $task->notificationSubscribers->offsetGet(0)->hasReadLatest);
            //After running for super, nothing will change.
            TasksUtil::markUserHasReadLatest($task, $steven);

            $id = $task->id;
            $task->forget();
            unset($task);

            $task = Task::getById($id);
            $this->assertEquals(1, $task->notificationSubscribers->offsetGet(0)->hasReadLatest);
        }

        public function testIsUserSubscribedForTask()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $this->assertTrue(TasksUtil::isUserSubscribedForTask($task, $user));
        }

        public function testGetTaskSubscriberData()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $content = TasksUtil::getTaskSubscriberData($task);
            $this->assertTrue(strpos($content, 'gravatar') > 0);
        }

        public function testGetTaskSubscribers()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $subscribers = TasksUtil::getTaskSubscribers($task);
            $this->assertEquals($subscribers[0], $user);
        }

        public function testResolvePeopleToSendNotificationToOnTaskUpdate()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $task->requestedByUser = Yii::app()->user->userModel;
            $this->assertTrue($task->save());
            $people = TasksUtil::resolvePeopleToSendNotificationToOnTaskUpdate($task, Yii::app()->user->userModel);
            $this->assertEquals(1, count($people));
            $this->assertEquals($people[0], $user);
        }

        public function testResolvePeopleSubscribedForTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $task->requestedByUser = Yii::app()->user->userModel;
            $this->assertTrue($task->save());
            $people = TasksUtil::resolvePeopleSubscribedForTask($task);
            $this->assertEquals(3, count($people));
            $this->assertEquals($people[0], $user);
            $this->assertEquals($people[1], Yii::app()->user->userModel);
            $this->assertEquals($people[2], Yii::app()->user->userModel);
        }

        public function testGetEmailSubject()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $content = TasksUtil::getEmailSubject($task);
            $this->assertTrue(strpos($content, 'New update on') == 0);
        }

        public function testGetUrlToEmail()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $content = TasksUtil::getUrlToEmail($task);
            $this->assertTrue(strpos($content, 'tasks/default/details/' . $task->id) == 0);
        }

        public function testResolvePeopleToSendNotificationToOnNewComment()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $task->requestedByUser = Yii::app()->user->userModel;
            $this->assertTrue($task->save());
            $people = TasksUtil::resolvePeopleToSendNotificationToOnNewComment($task, Yii::app()->user->userModel);
            $this->assertEquals(1, count($people));
            $this->assertEquals($people[0], $user);
        }

        public function testResolveExplicitPermissionsForRequestedByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mark                       = UserTestHelper::createBasicUser('mark');
            $user                       = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $task->requestedByUser = $user;
            $this->assertTrue($task->save());
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($task);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesCount(), 0);
            TasksUtil::resolveExplicitPermissionsForRequestedByUser($task, $mark, $user, $explicitReadWriteModelPermissions);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesCount(), 1);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesToRemoveCount(), 1);
        }

        public function testGetModalDetailsTitle()
        {
            $title = TasksUtil::getModalDetailsTitle();
            $this->assertEquals('Collaborate On This Task',$title);
        }

        public function testGetModalTitleForCreateTask()
        {
            $title = TasksUtil::getModalTitleForCreateTask();
            $this->assertEquals('Create Task',$title);

            $title = TasksUtil::getModalTitleForCreateTask("Edit");
            $this->assertEquals('Edit Task',$title);

            $title = TasksUtil::getModalTitleForCreateTask("Copy");
            $this->assertEquals('Copy Task',$title);
        }

        public function testResolveKanbanItemTypeForTaskStatus()
        {
            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTaskStatus(Task::STATUS_AWAITING_ACCEPTANCE);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItemType);

            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTaskStatus(Task::STATUS_NEW);
            $this->assertEquals(KanbanItem::TYPE_SOMEDAY, $kanbanItemType);
        }

        public function testResolveKanbanItemTypeForTask()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTask($task->id);
            $this->assertEquals(KanbanItem::TYPE_TODO,$kanbanItemType);

            $task->status = Task::STATUS_AWAITING_ACCEPTANCE;
            $this->assertTrue($task->save());
            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTask($task->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS,$kanbanItemType);
        }

        public function testResolveSubscriptionLink()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = Yii::app()->user->userModel;
            $task->notificationSubscribers->add($notificationSubscriber);
            $task->save();
            $link = TasksUtil::getKanbanSubscriptionLink($task,0);
            $this->assertTrue(strpos($link, 'unsubscribe-task-link') > 0);

            foreach($task->notificationSubscribers as $notificationSubscriber)
            {
                if($notificationSubscriber->person == Yii::app()->user->userModel)
                {
                    $task->notificationSubscribers->remove($notificationSubscriber);
                }
            }
            $task->save();
            $link = TasksUtil::getKanbanSubscriptionLink($task,0);
            $this->assertTrue(strpos($link, 'subscribe-task-link') > 0);
        }

        public function testTaskCompletionPercentage()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $checkListItem = new TaskCheckListItem();
            $checkListItem->name = 'Test Item 1';
            $this->assertTrue($checkListItem->unrestrictedSave());
            $task->checkListItems->add($checkListItem);
            $task->save(false);

            $checkListItem = new TaskCheckListItem();
            $checkListItem->name = 'Test Item 2';
            $checkListItem->completed = true;
            $this->assertTrue($checkListItem->unrestrictedSave());
            $task->checkListItems->add($checkListItem);
            $task->save(false);

            $this->assertEquals(2, count($task->checkListItems));
            $percent = TasksUtil::getTaskCompletionPercentage($task);
            $this->assertEquals(50, $percent);
        }

        public function testGetDefaultTaskStatusForKanbanItemType()
        {
            $status = TasksUtil::getDefaultTaskStatusForKanbanItemType(KanbanItem::TYPE_SOMEDAY);
            $this->assertEquals(Task::STATUS_NEW, $status);
        }

        public function testSetDefaultValuesForTask()
        {
            $task = TaskTestHelper::createTaskByNameForOwner('My Default Task', Yii::app()->user->userModel);
            TasksUtil::setDefaultValuesForTask($task);
            $this->assertEquals(Yii::app()->user->userModel->id, $task->requestedByUser->id);
            $this->assertEquals(1, count($task->notificationSubscribers));
        }

        public function testCreateKanbanItemFromTask()
        {
            $task = TaskTestHelper::createTaskByNameForOwner('My Kanban Task', Yii::app()->user->userModel);
            TasksUtil::setDefaultValuesForTask($task);
            $task->status = Task::STATUS_IN_PROGRESS;
            $this->assertTrue($task->save());
            $kanbanItem = TasksUtil::createKanbanItemFromTask($task);
            $this->assertEquals($kanbanItem->type, KanbanItem::TYPE_IN_PROGRESS);
        }
    }
?>