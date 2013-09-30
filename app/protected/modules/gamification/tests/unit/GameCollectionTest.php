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

    class GameCollectionTest extends ZurmoBaseTest
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

        public function testCreateAndGetGameCollectionById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameCollection      = new GameCollection();
            $gameCollection->person    = $user;
            $gameCollection->type      = 'Butterflies';
            $gameCollection->serializedData = serialize(array('something'));
            $this->assertTrue($gameCollection->save());
            $id = $gameCollection->id;
            unset($gameCollection);
            $gameCollection = GameCollection::getById($id);
            $this->assertEquals('Butterflies', $gameCollection->type);
            $this->assertEquals($user,         $gameCollection->person);
            $this->assertEquals(array('something'), unserialize($gameCollection->serializedData));
        }

        /**
         * @depends testCreateAndGetGameCollectionById
         */
        public function testResolveByTypeAndPerson()
        {
            Yii::app()->user->userModel      = User::getByUsername('steven');
            $gameCollection                  = GameCollection::resolveByTypeAndPerson('Butterflies',  Yii::app()->user->userModel);
            $this->assertEquals('Butterflies',                  $gameCollection->type);
            $this->assertEquals(Yii::app()->user->userModel,    $gameCollection->person);
            $this->assertEquals(array('something'),             unserialize($gameCollection->serializedData));

            $gameCollection = GameCollection::resolveByTypeAndPerson('Frogs',  Yii::app()->user->userModel);
            $this->assertTrue($gameCollection->id < 0);
            $compareData = array('RedemptionItem' => 0,
                                 'Items' => array(
                                    'Goliath'           => 0,
                                    'NorthernLeopard'   => 0,
                                    'OrnateHorned'      => 0,
                                    'Tree'              => 0,
                                    'Wood'              => 0));
            $this->assertEquals($compareData, unserialize($gameCollection->serializedData));
        }

        /**
         * @depends testResolveByTypeAndPerson
         */
        public function testResolvePersonAndAvailableTypes()
        {
            Yii::app()->user->userModel      = User::getByUsername('steven');
            $collections = GameCollection::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, array('Butterflies'));
            $this->assertEquals(1, count($collections));
            $this->assertEquals('Butterflies', $collections['Butterflies']->type);
            $this->assertTrue($collections['Butterflies']->id > 0);
            //Try a collection that is not created yet for the user
            $collections = GameCollection::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, array('Frogs'));
            $this->assertEquals(1, count($collections));
            $this->assertEquals('Frogs', $collections['Frogs']->type);
            $this->assertTrue($collections['Frogs']->id > 0);
            $compareData = array('RedemptionItem' => 0,
                                 'Items' => array(
                                    'Goliath'           => 0,
                                    'NorthernLeopard'   => 0,
                                    'OrnateHorned'      => 0,
                                    'Tree'              => 0,
                                    'Wood'              => 0));
            $this->assertEquals($compareData, unserialize($collections['Frogs']->serializedData));
        }

        /**
         * @depends testResolvePersonAndAvailableTypes
         */
        public function testGetAvailableTypes()
        {
           $types = GameCollection::getAvailableTypes();
            $this->assertTrue(count($types) > 0);
        }

        /**
         * @depends testGetAvailableTypes
         */
        public function testGetItemsData()
        {
            Yii::app()->user->userModel      = User::getByUsername('steven');
            $collections = GameCollection::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, array('Frogs'));
            $this->assertEquals(1, count($collections));
            $itemsData = $collections['Frogs']->getItemsData();
            $compareData = array('Goliath'           => 0,
                                 'NorthernLeopard'   => 0,
                                 'OrnateHorned'      => 0,
                                 'Tree'              => 0,
                                 'Wood'              => 0);
            $this->assertEquals($compareData, $itemsData);
        }

        /**
         * @depends testGetItemsData
         */
        public function testGetRedemptionCount()
        {
            Yii::app()->user->userModel      = User::getByUsername('steven');
            $collections = GameCollection::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, array('Frogs'));
            $this->assertEquals(1, count($collections));
            $redemptionCount = $collections['Frogs']->getRedemptionCount();
            $this->assertEquals(0, $redemptionCount);
        }

        /**
         * @depends testGetRedemptionCount
         */
        public function testRedeem()
        {
            //todO;
            $this->fail();
        }

        /**
         * @depends testRedeem
         */
        public function shouldReceiveCollectionItem()
        {
            //todO;
            $this->fail();
        }

        /**
         * @depends shouldReceiveCollectionItem
         */
        public function processRandomReceivingCollectionItemByUser()
        {
            //todO;
            $this->fail();
        }
    }
?>
