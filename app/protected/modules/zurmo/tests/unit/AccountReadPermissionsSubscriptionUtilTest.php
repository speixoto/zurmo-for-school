<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class AccountReadPermissionsSubscriptionUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->readPermissionSubscriptionObserver->enabled = true;
        }

        public function setUp()
        {
            parent::setUp();
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->readPermissionSubscriptionObserver->enabled = false;
            parent::tearDownAfterClass();
        }

        /**
         * Create new account, new basic user and new group.
         * Add user to group, allow group to access new account
         * After job is completed record for new account and new user should be in account_read_subscription.
         * Test group deletion
         */
        public function testGroupChangeOrDeleteScenario1()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $johnny = UserTestHelper::createBasicUser('Johnny');
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            $account = AccountTestHelper::createAccountByNameForOwner('First Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(0, count($queuedJobs));
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            $group = new Group();
            $group->name = 'Group1';
            $this->assertTrue($group->save());

            //$group->users->add($johnny);
            //$this->assertTrue($group->save());
            // We need to add user to group using GroupUserMembershipForm, so ReadPermissionsSubscriptionUtil::userAddedToGroup(); will be triggered
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(0 => $johnny->id),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);

            // Because we save group, new queued job will be created, but read permission table should stay same
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            // Now add permissions to group
            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Test delete group
            $group = Group::getByName('Group1');
            $group->delete();
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);

        }

        /**
         * Remove user from group, and in this case user and account should still exist in table but with TYPE_DELETE
         * Also in this scenario test when user is added again to the group, after it is removed from group
         * @depends testGroupChangeOrDeleteScenario1
         */
        public function testGroupChangeOrDeleteScenario2()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            $johnny = User::getByUsername('johnny');

            $account = AccountTestHelper::createAccountByNameForOwner('Second Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $group = new Group();
            $group->name = 'Group2';
            $this->assertTrue($group->save());
            $group->users->add($johnny);
            $this->assertTrue($group->save());

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Check if everything is added correctly
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Remove user from group
            //$group->users->remove($johnny);
            //$this->assertTrue($group->save());
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);


            // Now add user to group again and test
            //$group->users->add($johnny);
            //$this->assertTrue($group->save());
            // We need to add user to group using GroupUserMembershipForm, so ReadPermissionsSubscriptionUtil::userAddedToGroup(); will be triggered
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(0 => $johnny->id),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
        }

        /**
         * Remove permissions from group to access account, and in this case user should be removed from group
         * @depends testGroupChangeOrDeleteScenario2
         */
        public function testGroupChangeOrDeleteScenario3()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();

            $johnny = User::getByUsername('johnny');
            $group = Group::getByName('Group2');
            $accounts = Account::getByName('Second Account');
            $account = $accounts[0];

            $account->removePermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $group::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);
        }

        /**
         * Test nested groups
         */
        public function testGroupChangeOrDeleteScenario4()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();

            $johnny = User::getByUsername('johnny');
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');

            $account = AccountTestHelper::createAccountByNameForOwner('Third Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $parentGroup = new Group();
            $parentGroup->name = 'Parent';
            $this->assertTrue($parentGroup->save());

            $group = new Group();
            $group->name = 'Child';
            $group->group = $parentGroup;
            $saved = $group->save();
            $this->assertTrue($saved);
            $group->users->add($johnny);
            $this->assertTrue($group->save());

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);


            // Add permissions for parentGroup to READ account
            $account->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account->save());
            $parentGroup::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Remove permissions from parentGroup to READ account
            $account->removePermissions($parentGroup, Permission::READ);
            $this->assertTrue($account->save());
            $parentGroup::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);
        }

        public function testRoleChangeOrDelete()
        {

        }

        protected function deleteAllModelsAndRecordsFromReadPermissionTable($modelClassName)
        {
            $models = $modelClassName::getAll();
            foreach ($models as $model)
            {
                $model->delete();
            }
            $tableName = ReadPermissionsSubscriptionUtil::getSubscriptionTableName($modelClassName);
            $sql = "DELETE FROM $tableName";
            ZurmoRedBean::exec($sql);
        }
    }
?>
