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
     * Task module walkthrough tests.
     */
    class TaskAjaxSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            $testUser = UserTestHelper::createBasicUser('myuser');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testInlineCreateCommentFromAjax()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $task = new Task();
            $task->name = 'aTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $this->setGetArray(array('id' => $task->id, 'uniquePageId' => 'CommentInlineEditForModelView'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/inlineCreateCommentFromAjax');
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testUpdateUserViaAjax()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $user   = UserTestHelper::createBasicUser('test');
            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('id' => $task->id, 'attribute' => 'owner', 'userId' => $user->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateRelatedUsersViaAjax');
            $task   = Task::getById($taskId);
            $this->assertEquals($user->id, $task->owner->id);

            $this->setGetArray(array('id' => $task->id, 'attribute' => 'requestedByUser', 'userId' => $user->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateRelatedUsersViaAjax');
            $task   = Task::getById($taskId);

            $this->assertEquals($user->id, $task->requestedByUser->id);
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testUpdateDueDateTimeViaAjax()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('id' => $task->id, 'dateTime' => '7/23/13 12:00 am'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateDueDateTimeViaAjax', true);
            $task   = Task::getById($taskId);
            $displayDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($task->dueDateTime);
            $this->assertEquals('7/23/13 12:00 AM', $displayDateTime);
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testAddAndRemoveSubscriberViaAjax()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addSubscriber', false);
            $this->assertTrue(strpos($content, 'gravatar') > 0);
            $task   = Task::getById($taskId);
            $this->assertEquals(1, $task->notificationSubscribers->count());
            $notificationSubscribers = $task->notificationSubscribers;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            $user = $notificationSubscribers[0]->person->castDown(array($modelDerivationPathToItem));
            $this->assertEquals($user->id, $super->id);

            //Remove subscriber
            $this->setGetArray(array('id' => $task->id));
            $this->assertEquals(1, $task->notificationSubscribers->count());
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeSubscriber', true);
            $task   = Task::getById($taskId);
            $this->assertEquals(0, $task->notificationSubscribers->count());
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testSuperUserModalAllDefaultFromRelationAction()
        {
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $accountId          = self::getModelIdByModelNameAndName('Account', 'superAccount');
            $this->setGetArray(array(
                                      'relationAttributeName'   => 'Account',
                                      'relationModelId'         => $accountId,
                                      'relationModuleId'        => 'accounts',
                                      'modalId'                 => 'relatedModalContainer-tasks',
                                      'portletId'               => '12',
                                      'uniqueLayoutId'          => 'AccountDetailsAndRelationsView_12'
                                    ));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCreateFromRelation');
            $tasks              = Task::getAll();
            $this->assertEquals(1, count($tasks));
            $this->setGetArray(array(
                                      'relationAttributeName'   => 'Account',
                                      'relationModelId'         => $accountId,
                                      'relationModuleId'        => 'accounts',
                                      'portletId'               => '12',
                                      'uniqueLayoutId'          => 'AccountDetailsAndRelationsView_12'
                                    ));
            $this->setPostArray(array(
                                       'Task'   => array('name'              => 'Task for test cases'),
                                       'ActivityItemForm' => array('Account' => array('id' => $accountId))
            ));

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalSaveFromRelation');
            $this->assertTrue(strpos($content, 'Task for test cases') > 0);
            $tasks              = Task::getAll();
            $this->assertEquals(2, count($tasks));

            $this->setGetArray(array(
                                    'id'                       => $tasks[1]->id,
                                    'modalTransferInformation' =>array('modalId' => 'relatedModalContainer-tasks')
                                    )
                              );
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalDetails');
            $this->assertTrue(strpos($content, 'Task for test cases') > 0);

            $this->setGetArray(array(
                                    'id'  => $tasks[1]->id
                              ));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalEdit');

            $this->setGetArray(array(
                                    'id'  => $tasks[1]->id
                              ));
            unset($_POST['Task']);
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCopy');
            $this->assertTrue(strpos($content, 'Task for test cases') > 0);
        }

        public function testAddAndRemoveKanbanSubscriberViaAjax()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task     = new Task();
            $task->name = 'newTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());
            unset($task);
            $tasks  = Task::getByName('newTest');
            $task   = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('id' => $task->id));
            $this->assertEquals(0, $task->notificationSubscribers->count());
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addKanbanSubscriber', true);
            $task   = Task::getById($taskId);
            $this->assertEquals(1, $task->notificationSubscribers->count());
            $notificationSubscribers = $task->notificationSubscribers;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            $user = $notificationSubscribers[0]->person->castDown(array($modelDerivationPathToItem));
            $this->assertEquals($user->id, $super->id);

            //Remove kanban subscriber
            $this->setGetArray(array('id' => $task->id));
            $this->assertEquals(1, $task->notificationSubscribers->count());
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeKanbanSubscriber', true);
            $task   = Task::getById($taskId);
            $this->assertEquals(0, $task->notificationSubscribers->count());
        }


        public function testUpdateStatusOnDragInKanbanView()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $project = ProjectTestHelper::createProjectByNameForOwner('a new project', $super);
            $task = TaskTestHelper::createTaskByNameForOwner('My Kanban Task', Yii::app()->user->userModel);
            $task->project = $project;
            TasksUtil::setDefaultValuesForTask($task);
            $task->status = Task::STATUS_IN_PROGRESS;
            $taskId = $task->id;
            $this->assertTrue($task->save());

            $task1 = TaskTestHelper::createTaskByNameForOwner('My Kanban Task 1', Yii::app()->user->userModel);
            $task1->project = $project;
            TasksUtil::setDefaultValuesForTask($task1);
            $task1->status = Task::STATUS_NEW;
            $this->assertTrue($task1->save());
            $task1Id = $task1->id;
            $taskArray = array($task, $task1);

            foreach ($taskArray as $row => $data)
            {
                $kanbanItem  = KanbanItem::getByTask($data->id);
                if($kanbanItem == null)
                {
                    //Create KanbanItem here
                    $kanbanItem = TasksUtil::createKanbanItemFromTask($data);
                }
                $kanbanItemsArray[] = $kanbanItem;
            }
            $this->assertEquals(KanbanItem::TYPE_TODO, $kanbanItemsArray[1]->type);
            $this->assertEquals(1, $kanbanItemsArray[1]->sortOrder);
            $this->assertEquals(1, $kanbanItemsArray[0]->sortOrder);

            $this->setGetArray(array('items' => array($task1->id, $task->id), 'type' => KanbanItem::TYPE_IN_PROGRESS));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateStatusOnDragInKanbanView', false);
            $contentArray = CJSON::decode($content);
            $this->assertTrue(strpos($contentArray['button'], 'Finish') > 0);
            $task1 = Task::getById($task1Id);
            $this->assertEquals(Task::STATUS_IN_PROGRESS, $task1->status);
            $kanbanItem = KanbanItem::getByTask($task1Id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem->type);

            $kanbanItem = KanbanItem::getByTask($taskId);
            $this->assertEquals(2, $kanbanItem->sortOrder);
        }

        /**
         * @depends testUpdateStatusOnDragInKanbanView
         */
        public function testUpdateStatusInKanbanView()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks = Task::getByName('My Kanban Task');
            $task  = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('targetStatus' => Task::STATUS_AWAITING_ACCEPTANCE, 'taskId' => $task->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateStatusInKanbanView', true);
            $task = Task::getById($taskId);
            $this->assertEquals(Task::STATUS_AWAITING_ACCEPTANCE, $task->status);
        }
   }
?>