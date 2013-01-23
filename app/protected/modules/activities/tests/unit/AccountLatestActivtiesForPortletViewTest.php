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
    * Test AccountLatestActivtiesForPortletView functions.
    */
    class AccountLatestActivtiesForPortletViewTest extends ZurmoBaseTest
    {
        public $freeze = false;
        protected static $accountLatestActivitiesPortletId = null;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            $portlet = new Portlet();
            $portlet->column    = 2;
            $portlet->position  = 5;
            $portlet->layoutId  = 'Test';
            $portlet->collapsed = true;
            $portlet->viewType  = 'AccountLatestActivtiesForPortlet';
            $portlet->user      = $super;
            $portlet->save();
            static::$accountLatestActivitiesPortletId = $portlet->id;
        }

        public function setUp()
        {
            parent::setUp();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testNullRollUpForNewAccountLatestActivitiesForPortletView()
        {
            $this->assertNull(LatestActivitiesUtil::getRollUpStateForCurrentUserByPortletId(static::$accountLatestActivitiesPortletId));
        }

        public function testCanSetRollUpForNewAccountLatestActivitiesForPortletView()
        {
            LatestActivitiesUtil::setRollUpForCurrentUserByPortletId(static::$accountLatestActivitiesPortletId, '');
            $this->assertTrue(LatestActivitiesUtil::getRollUpStateForCurrentUserByPortletId(static::$accountLatestActivitiesPortletId) === '');
            LatestActivitiesUtil::setRollUpForCurrentUserByPortletId(static::$accountLatestActivitiesPortletId, '1');
            $this->assertTrue(LatestActivitiesUtil::getRollUpStateForCurrentUserByPortletId(static::$accountLatestActivitiesPortletId) === '1');
        }

        public function testCanDeleteAccountLatestActivitiesForPortletView()
        {
            $this->assertTrue(Portlet::getById(static::$accountLatestActivitiesPortletId)->delete());
        }

        public function testRollUpIsNullAfterDeletingAccountLatestActivitiesForPortletView()
        {
            $this->testNullRollUpForNewAccountLatestActivitiesForPortletView();
        }
    }
?>