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

    class ReadPermissionSubscriptionObserverTest extends ZurmoBaseTest
    {
        protected static $billy;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$billy = UserTestHelper::createBasicUser('Billy');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testOnCreateOwnerChangeAndDeleteModel()
        {
            // ToDo: Uncoment line below once we complete ReadPermissionSubscriptionObserver code
            return true;
            //test on a model that is not observed
            $marketingList = new MarketingList();
            $marketingList->name = 'test';
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $this->assertTrue($marketingList->save());
            $this->assertCount(0, Yii::app()->jobQueue->getAll());

            //test on a model that is observed
            $account = new Account();
            $account->name = 'an account';
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $this->assertTrue($account->save());
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('ReadPermissionSubscriptionUpdate', $jobs[5][0]);

            //test on a model that is updated, but not created
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $account->name = 'a new name';
            $this->assertTrue($account->save());
            $this->assertCount(0, Yii::app()->jobQueue->getAll());

            //now test that the owner changes
            $account->owner = self::$billy;
            $this->assertTrue($account->save());
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('ReadPermissionSubscriptionUpdate', $jobs[5][0]);

            //Now delete model
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $this->assertTrue($account->delete());
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('ReadPermissionSubscriptionUpdate', $jobs[5][0]);
        }
    }
?>