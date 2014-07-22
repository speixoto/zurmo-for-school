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

    /**
     * Missions Module User Walkthrough.
     * Walkthrough for the users of all possible controller actions.
     */
    class MissionsUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AllPermissionsOptimizationUtil::rebuild();

            //create everyone group
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();

            //Create test users
            $steven                             = UserTestHelper::createBasicUser('steven');
            $steven->primaryEmail->emailAddress = 'steven@testzurmo.com';
            //Steven has turned off notifications
            UserConfigurationFormAdapter::setValue($steven, true, 'turnOffEmailNotifications');
            $sally                              = UserTestHelper::createBasicUser('sally');
            $sally->primaryEmail->emailAddress  = 'sally@testzurmo.com';
            $mary                               = UserTestHelper::createBasicUser('mary');
            $mary->primaryEmail->emailAddress   = 'mary@testzurmo.com';

            //give 3 users access, create, delete for mission rights.
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $steven->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $sally->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $mary->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function testSuperUserAllSimpleControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('missions/default');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/create');
        }

        /**
         * @depends testSuperUserAllSimpleControllerActions
         */
        public function testSuperUserCreateMission()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');

            $missions = Mission::getAll();
            $this->assertEquals(0, count($missions));
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');

            //Confirm mission saved.
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals('TestDescription', $missions[0]->description);
            $this->assertEquals(Mission::STATUS_AVAILABLE,        $missions[0]->status);

            //Confirm everyone has read/write
            $everyoneGroup                     = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($missions[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$everyoneGroup->getClassId('Permitable')]));

            //Confirm email was sent
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAll();
            //Dont send message to super neither to steven (he has turned off)
            $recipents = array();
            $recipents[] = strval($emailMessages[0]->recipients[0]);
            $recipents[] = strval($emailMessages[1]->recipients[0]);
            $this->assertEquals     (1,         count($emailMessages[0]->recipients));
            $this->assertEquals     (1,         count($emailMessages[1]->recipients));
            $this->assertNotContains(strval($super->primaryEmail),    $recipents);
            $this->assertNotContains(strval($steven->primaryEmail),   $recipents);
            $this->assertContains   (strval($mary->primaryEmail),     $recipents);
            $this->assertContains   (strval($sally->primaryEmail),    $recipents);
            $this->assertEquals     (4,         User::getCount());
        }

        /**
         * @depends testSuperUserCreateMission
         */
        public function testAddingCommentsAndUpdatingActivityStampsOnMission()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(0, $missions[0]->comments->count());
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $oldStamp        = $missions[0]->latestDateTime;

            //Validate comment
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'comment-inline-edit-form',
                                      'Comment' => array('description' => 'a ValidComment Name')));

            $content = $this->runControllerWithExitExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //Now save that comment.
            sleep(2); //to force some time to pass.
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($id);
            $this->assertEquals(1, $mission->comments->count());

            //should update latest activity stamp
            $this->assertNotEquals($oldStamp, $missions[0]->latestDateTime);
            $newStamp = $missions[0]->latestDateTime;
            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Mary should be able to add a comment because everyone can do this on a mission
            $mary = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($id);
            $this->assertEquals(2, $mission->comments->count());
            $this->assertNotEquals($newStamp, $mission->latestDateTime);
        }

        /**
         * @depends testAddingCommentsAndUpdatingActivityStampsOnMission
         */
        public function testUsersCanReadAndWriteMissionsOkThatAreNotOwnerOrTakenByUser()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            //todo; we stll need to test that other users can get to the missions.
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(2, $missions[0]->comments->count());

            //Mary should not be able to edit the mission
            $mary           = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('id' => $missions[0]->id));
            $this->runControllerWithExitExceptionAndGetContent('missions/default/edit');

            //new test - mary can delete a comment she wrote
            $maryCommentId = $missions[0]->comments->offsetGet(1)->id;
            $this->assertEquals($missions[0]->comments->offsetGet(1)->createdByUser->id, $mary->id);
            $superCommentId = $missions[0]->comments->offsetGet(0)->id;
            $this->assertEquals($missions[0]->comments->offsetGet(0)->createdByUser->id, $super->id);
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $maryCommentId));
            $this->runControllerWithNoExceptionsAndGetContent('comments/default/deleteViaAjax', true);
            $missionId  = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($missionId);
            $this->assertEquals(1, $mission->comments->count());

            //new test - mary cannot delete a comment she did not write.
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $superCommentId));
            $this->runControllerShouldResultInAjaxAccessFailureAndGetContent('comments/default/deleteViaAjax');
            $missionId  = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($missionId);
            $this->assertEquals(1, $mission->comments->count());
            $this->assertEquals(1, $mission->comments->count());

            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertTrue($mission->owner->isSame($super));

            //new test , super can view and edit the mission
            $this->setGetArray(array('id' => $mission->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/edit');

            //new test , super can delete the mission
            $this->setGetArray(array('id' => $mission->id));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/delete');

            $missions  = Mission::getAll();
            $this->assertEquals(0, count($missions));
        }

        /**
         * @depends testUsersCanReadAndWriteMissionsOkThatAreNotOwnerOrTakenByUser
         */
        public function testListViewFiltering()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_CREATED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_AVAILABLE));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
        }

        /**
         * @depends testListViewFiltering
         */
        public function testCommentsAjaxListForRelatedModel()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $missions  = Mission::getAll();
            $this->assertEquals(0, count($missions));

            //Create a new mission
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));

            $this->setGetArray(array('relatedModelId' => $missions[0]->id, 'relatedModelClassName' => 'Mission',
                                     'relatedModelRelationName' => 'comments'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('comments/default/ajaxListForRelatedModel');
        }

        /**
         * @depends testCommentsAjaxListForRelatedModel
         */
        public function testAjaxChangeStatus()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $missions[0]->delete();

            //Create a new mission
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');

            //Confirm mission saved.
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_AVAILABLE,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->id < 0);

            //change status to taken
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_TAKEN,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_TAKEN,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));

            //Change status to complete
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_COMPLETED,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_COMPLETED,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));

            //Change status to accepted
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_ACCEPTED,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_ACCEPTED,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));
        }

        /**
         * @depends testAjaxChangeStatus
         */
        public function testSendEmailInNewComment()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $missions       = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $mission        = $missions[0];
            $this->assertEquals(0, $mission->comments->count());
            foreach (EmailMessage::getAll() as $emailMessage)
            {
                $emailMessage->delete();
            }
            $messageCount   = 0;
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Save new comment.
            $this->setGetArray(array('relatedModelId'             => $mission->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals(1, $mission->comments->count());
            $this->assertEquals($messageCount + 1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages  = EmailMessage::getAll();
            $emailMessage   = $emailMessages[$messageCount];
            $this->assertEquals(1, count($emailMessage->recipients));
            $this->assertContains('mission', $emailMessage->subject);
            $this->assertContains(strval($mission), $emailMessage->subject);
            $this->assertContains(strval($mission->comments[0]), $emailMessage->content->htmlContent);
            $this->assertContains(strval($mission->comments[0]), $emailMessage->content->textContent);
        }

        public function testMissionReadUnreadStatus()
        {
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $super          = $this->
                                 logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $mission              = new Mission();
            $mission->owner       = $steven;
            $mission->description = 'My test mission description';
            $mission->status      = Mission::STATUS_AVAILABLE;
            $this->assertTrue($mission->save());
            $missionId = $mission->id;
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($mission, $explicitReadWriteModelPermissions);
            $mission = Mission::getById($missionId);
            //Confirm users have mission marked as unread but not owner
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $sally));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $mary));

            //Super reads the mission
            $this->setGetArray(array('id' => $missionId));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/details');
            $mission = Mission::getById($missionId);
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $sally));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $mary));

            //Mary marks mission as read and post a comment
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            MissionsUtil::markUserHasReadLatest($mission, $mary);
            $this->setGetArray(array('relatedModelId'             => $missionId,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'Mary\'s new comment')));
            $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $mission = Mission::getById($missionId);
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $sally));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $mary));

            //Sally reads and takes the mission
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('sally');
            $this->setGetArray(array('id' => $missionId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/details');
            $mission = Mission::getById($missionId);
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $sally));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $mary));
            $this->setGetArray(array('status'         => Mission::STATUS_TAKEN,
                                     'id'             => $missionId));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');

            //Every user other than owner and takenby are marked as read latest
            $mission = Mission::getById($missionId);
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $sally));
            $this->assertTrue (MissionsUtil::hasUserReadMissionLatest($mission, $mary));
        }
    }
?>