<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Jobs Manager user interface actions.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class JobsManagerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent     ('jobsManager/default/');
            $this->runControllerWithNoExceptionsAndGetContent     ('jobsManager/default/list');
        }

        public function testSuperUserResetStuckJobInProcess()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test when the job is not stuck
            $this->setGetArray(array('type' => 'Monitor'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('jobsManager/default/resetJob');
            $this->assertTrue(strpos($content, 'The job Monitor Job was not found to be stuck and therefore was not reset.') !== false);

            //Test when the job is stuck (Just having a jobInProcess is enough to trigger it.
            $jobInProcess = new JobInProcess();
            $jobInProcess->type = 'Monitor';
            $this->assertTrue($jobInProcess->save());
            $this->setGetArray(array('type' => 'Monitor'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('jobsManager/default/resetJob');
            $this->assertTrue(strpos($content, 'The job Monitor Job has been reset.') !== false);
        }

        public function testSuperUserModalListByType()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 'Monitor'));
            $this->runControllerWithNoExceptionsAndGetContent('jobsManager/default/jobLogsModalList');
        }
    }
?>