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

    class MissionGamificationRulesTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testScoreOnSaveModelForMissionTaken()
        {
            $super = User::getByUsername('super');
            $simpleUser = UserTestHelper::createBasicUser('simpleUser');
            $gamescore = GameScore::getAllByPersonIndexedByType($simpleUser);
            $this->assertEquals(0, count($gamescore));
            $missions  = Mission::getAll();
            $this->assertEquals(0, count($missions));
            $mission = new Mission();
            $mission->owner       = $super;
            $mission->takenByUser = $simpleUser;
            $mission->description = 'Test description';
            $mission->reward      = 'Test reward';
            $mission->status      = Mission::STATUS_AVAILABLE;
            $mission->save();
            //Confirm mission saved.
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $gamescore = GameScore::getAllByPersonIndexedByType($super);
            $this->assertEquals(1, count($gamescore));
            //Changing Status to Taken
            $mission = $missions[0];
            $mission->status = Mission::STATUS_TAKEN;
            $this->assertTrue($mission->save());
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $gamescore = GameScore::getAllByPersonIndexedByType($simpleUser);
            $this->assertEquals(1, count($gamescore));
            //Changing Status to Completed
            $mission = $missions[0];
            $mission->status = Mission::STATUS_COMPLETED;
            $mission->save();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $gamescore = GameScore::getAllByPersonIndexedByType($simpleUser);
            $this->assertEquals(2, count($gamescore));
            //Changing Status to Completed
            $mission = $missions[0];
            $mission->status = Mission::STATUS_ACCEPTED;
            $mission->save();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $gamescore = GameScore::getAllByPersonIndexedByType($simpleUser);
            $this->assertEquals(3, count($gamescore));

        }
    }
?>