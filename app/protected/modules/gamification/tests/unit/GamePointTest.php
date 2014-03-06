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

    class GamePointTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetGamePointById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gamePoint             = new GamePoint();
            $gamePoint->person     = $user;
            $gamePoint->type       = 'SomeType';
            $gamePoint->addValue(10);
            $this->assertTrue($gamePoint->save());
            $id = $gamePoint->id;
            unset($gamePoint);
            $gamePoint = GamePoint::getById($id);
            $this->assertEquals('SomeType',  $gamePoint->type);
            $this->assertEquals(10,          $gamePoint->value);
            $this->assertEquals($user,       $gamePoint->person);

            $this->assertEquals(1, $gamePoint->transactions->count());
            $gamePoint->addValue(50, false);
            $this->assertTrue($gamePoint->save());
            GamePointTransaction::addTransactionResolvedForOptimization($gamePoint, 50);
            $this->assertEquals(60, $gamePoint->value);
            $gamePoint::forgetAll();
            $gamePoint = GamePoint::getById($id);
            $this->assertEquals(60, $gamePoint->value);
            $this->assertEquals(2, $gamePoint->transactions->count());
            $this->assertEquals(10, $gamePoint->transactions[0]->value);
            $this->assertEquals(50, $gamePoint->transactions[1]->value);
        }

        /**
         * @depends testCreateAndGetGamePointById
         */
        public function testCreateGamePointSettingValueDirectly()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gamePoint = new GamePoint();
            $gamePoint->value = 5; //Calls replaceValue
            $this->assertEquals(5, $gamePoint->value);
            $gamePoint->addValue(10);
            $this->assertEquals(15, $gamePoint->value);
        }

        /**
         * @depends testCreateGamePointSettingValueDirectly
         */
        public function testResolveToGetByTypeAndPerson()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gamePoint = GamePoint::resolveToGetByTypeAndPerson('SomeType',  Yii::app()->user->userModel);
            $this->assertEquals('SomeType',                   $gamePoint->type);
            $this->assertEquals(60,                           $gamePoint->value);
            $this->assertEquals(Yii::app()->user->userModel->getClassId('Item'),  $gamePoint->person->getClassId('Item'));

            $gamePoint = GamePoint::resolveToGetByTypeAndPerson('SomeType2',  Yii::app()->user->userModel);
            $this->assertTrue($gamePoint->id < 0);
        }

        /**
         * @depends testResolveToGetByTypeAndPerson
         */
        public function testGetAllByPersonIndexedByType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            $gamePoint             = new GamePoint();
            $gamePoint->person     = Yii::app()->user->userModel;
            $gamePoint->type       = 'SomeTypeX';
            $gamePoint->addValue(10);
            $this->assertTrue($gamePoint->save());

            $gamePoints = GamePoint::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($gamePoints));
            $this->assertTrue(isset($gamePoints['SomeType']));
            $this->assertTrue(isset($gamePoints['SomeTypeX']));
        }

        /**
         * @depends testGetAllByPersonIndexedByType
         */
        public function testDoesUserExceedPointsByLevelType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, GameLevel::TYPE_GENERAL);
            $this->assertTrue($result);
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, GameLevel::TYPE_SALES);
            $this->assertFalse($result);
        }

        /**
         * @depends testDoesUserExceedPointsByLevelType
         * @expectedException NotSupportedException
         */
        public function testDoesUserExceedPointsByInvalidLevelType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, 'SomethingInvalid');
        }
    }
?>
