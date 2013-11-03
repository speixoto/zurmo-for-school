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
    class AutoresponderItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetAutoresponderItemById()
        {
            $time                                       = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 week')); // Not Coding Standard
            $autoresponderItem                          = new AutoresponderItem();
            $autoresponderItem->processed               = 0;
            $autoresponderItem->processDateTime         = $time;
            $this->assertTrue($autoresponderItem->unrestrictedSave());
            $id = $autoresponderItem->id;
            unset($autoresponderItem);
            $autoresponderItem = AutoresponderItem::getById($id);
            $this->assertEquals(0,   $autoresponderItem->processed);
            $this->assertEquals($time,                              $autoresponderItem->processDateTime);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testRequiredAttributes()
        {
            $autoresponderItem                          = new AutoresponderItem();
            $this->assertFalse($autoresponderItem->unrestrictedSave());
            $errors = $autoresponderItem->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(1, $errors);
            $this->assertArrayHasKey('processDateTime', $errors);
            $this->assertEquals('Process Date Time cannot be blank.', $errors['processDateTime'][0]);

            $time                                       = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 week')); // Not Coding Standard
            $autoresponderItem                          = new AutoresponderItem();
            $autoresponderItem->processed               = 1;
            $autoresponderItem->processDateTime         = $time;
            $this->assertTrue($autoresponderItem->unrestrictedSave());
            $id = $autoresponderItem->id;
            unset($autoresponderItem);
            $autoresponderItem = AutoresponderItem::getById($id);
            $this->assertEquals(1,       $autoresponderItem->processed);
            $this->assertEquals($time,                              $autoresponderItem->processDateTime);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testGetByProcessed()
        {
            for ($i = 0; $i < 5; $i++)
            {
                $time                               = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 day')); // Not Coding Standard
                $processed                          = 0;
                if ($i % 2)
                {
                    $processed      = 1;
                }
                $autoresponderItem                  = new AutoresponderItem();
                $autoresponderItem->processed       = $processed;
                $autoresponderItem->processDateTime = $time;
                $this->assertTrue($autoresponderItem->unrestrictedSave());
            }
            $autoresponderItems         =   AutoresponderItem::getAll();
            $this->assertCount(7, $autoresponderItems);
            $processedItems             =   AutoresponderItem::getByProcessed(1);
            $this->assertCount(3, $processedItems);
            $notProcessedItems          =   AutoresponderItem::getByProcessed(0);
            $this->assertCount(4, $notProcessedItems);
        }

        /**
         * @depends testGetByProcessed
         */
        public function testGetByProcessedAndTime()
        {
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertCount(7, $autoresponderItems);
            $marketingList                  = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $this->assertNotNull($marketingList);
            $autoresponderToday             = AutoresponderTestHelper::createAutoresponder('subject Today',
                                                                                    'text Today',
                                                                                    'html Today',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList);
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertCount(7, $autoresponderItems);
            $this->assertNotNull($autoresponderToday);
            $autoresponderTenDaysFromNow    = AutoresponderTestHelper::createAutoresponder('subject Ten Days',
                                                                                    'text Ten Days',
                                                                                    'html Ten Days',
                                                                                    60*60*24*10,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    false,
                                                                                    $marketingList);
            $this->assertNotNull($autoresponderTenDaysFromNow);
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertCount(7, $autoresponderItems);
            for ($i = 0; $i < 10; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('contact ' . $i, Yii::app()->user->userModel);
                $this->assertNotNull($contact);
                if ($i % 3)
                {
                    $processed      = 1;
                }
                else
                {
                    $processed      = 0;
                }
                if ($i % 2)
                {
                    $autoresponder  = $autoresponderToday;
                }
                else
                {
                    $autoresponder  = $autoresponderTenDaysFromNow;
                }
                $timestamp          = time() - 10000000000; //forces all processed stamps to be in the past
                $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime((int)$timestamp);
                $autoresponderItem = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                        $processDateTime,
                                                                                        $autoresponder);
                $this->assertNotNull($autoresponderItem);
            }

            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertNotEmpty($autoresponderItems);
            $this->assertCount(17, $autoresponderItems);
            $autoresponderTodayProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(1,
                                                                                                $autoresponderToday->id);
            $this->assertNotEmpty($autoresponderTodayProcessed);
            $this->assertCount(3, $autoresponderTodayProcessed);
            $autoresponderTodayNotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                                $autoresponderToday->id);
            $this->assertNotEmpty($autoresponderTodayNotProcessed);
            $this->assertCount(2, $autoresponderTodayNotProcessed);
            $autoresponderTenDaysFromNowProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(1,
                                                                                                $autoresponderTenDaysFromNow->id);
            $this->assertNotEmpty($autoresponderTenDaysFromNowProcessed);
            $this->assertCount(3, $autoresponderTenDaysFromNowProcessed);
            $autoresponderTenDaysFromNowNotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                                $autoresponderTenDaysFromNow->id);
            $this->assertNotEmpty($autoresponderTenDaysFromNowNotProcessed);
            $this->assertCount(2, $autoresponderTenDaysFromNowNotProcessed);
        }

        /**
         * @depends testGetByProcessed
         */
        public function testGetByProcessedAndAutoresponderId()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $this->assertNotNull($marketingList);
            $autoresponder1     = AutoresponderTestHelper::createAutoresponder('subject 01', 'text 01', 'html 01', 10,
                                                            Autoresponder::OPERATION_UNSUBSCRIBE, true, $marketingList);
            $this->assertNotNull($autoresponder1);
            $autoresponder2     = AutoresponderTestHelper::createAutoresponder('subject 02', 'text 02', 'html 02', 20,
                                                            Autoresponder::OPERATION_SUBSCRIBE, false,  $marketingList);
            $this->assertNotNull($autoresponder2);
            for ($i = 0; $i < 10; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('contact 0' . $i, Yii::app()->user->userModel);
                $this->assertNotNull($contact);
                $time                               = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 day')); // Not Coding Standard
                if ($i % 3)
                {
                    $processed      = 1;
                }
                else
                {
                    $processed      = 0;
                }
                if ($i % 2)
                {
                    $autoresponder  = $autoresponder1;
                }
                else
                {
                    $autoresponder  = $autoresponder2;
                }
                $autoresponderItem = AutoresponderItemTestHelper::createAutoresponderItem($processed, $time, $autoresponder);
                $this->assertNotNull($autoresponderItem);
            }
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertNotEmpty($autoresponderItems);
            $this->assertCount(27, $autoresponderItems);
            $autoresponder1Processed  = AutoresponderItem::getByProcessedAndAutoresponderId(1,
                                                                                            $autoresponder1->id);
            $this->assertNotEmpty($autoresponder1Processed);
            $this->assertCount(3, $autoresponder1Processed);
            $autoresponder1NotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                                $autoresponder1->id);
            $this->assertNotEmpty($autoresponder1NotProcessed);
            $this->assertCount(2, $autoresponder1NotProcessed);
            $autoresponder2Processed  = AutoresponderItem::getByProcessedAndAutoresponderId(1,
                                                                                            $autoresponder2->id);
            $this->assertNotEmpty($autoresponder2Processed);
            $this->assertCount(3, $autoresponder2Processed);
            $autoresponder2NotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                                $autoresponder2->id);
            $this->assertNotEmpty($autoresponder2NotProcessed);
            $this->assertCount(2, $autoresponder2NotProcessed);
        }

        /**
         * @depends testGetByProcessedAndAutoresponderId
         */
        public function testGetByProcessedAndAutoresponderIdAndTime()
        {
            $intervals          = array('hour', 'day');
            $marketingList      = MarketingList::getByName('marketingList 01');
            $this->assertNotEmpty($marketingList);
            $autoresponder3     = AutoresponderTestHelper::createAutoresponder('subject 03', 'text 03', 'html 03', 10,
                                                        Autoresponder::OPERATION_UNSUBSCRIBE, true, $marketingList[0]);
            $this->assertNotNull($autoresponder3);
            $autoresponder4     = AutoresponderTestHelper::createAutoresponder('subject 04', 'text 04', 'html 04', 20,
                                                        Autoresponder::OPERATION_SUBSCRIBE, false, $marketingList[0]);
            $this->assertNotNull($autoresponder4);
            for ($i = 0; $i < 10; $i++)
            {
                if ($i % 3)
                {
                    $pastOrFuture   = "-";
                    $processed      = 1;
                }
                else
                {
                    $pastOrFuture   = "+"; // Not Coding Standard
                    $processed      = 0;
                }
                if ($i % 2)
                {
                    $autoresponder  = $autoresponder3;
                    $interval       = $intervals[1];
                }
                else
                {
                    $autoresponder  = $autoresponder4;
                    $interval       = $intervals[0];
                }
                $timestamp          = strtotime($pastOrFuture . ($i + 1) . ' ' . $interval);
                $time               = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
                $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed, $time, $autoresponder);
                $this->assertNotNull($autoresponderItem);
            }

            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(37, $autoresponderItems);
            $autoresponder3ProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                            1,
                                                                                            $autoresponder3->id);
            $this->assertNotEmpty($autoresponder3ProcessedBeforeNow);
            $this->assertCount(3, $autoresponder3ProcessedBeforeNow);
            $autoresponder3ProcessedFiveDaysAgo   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                        1,
                                                                                        $autoresponder3->id,
                                                                                        strtotime("-5 day"));
            $this->assertNotEmpty($autoresponder3ProcessedFiveDaysAgo);
            $this->assertCount(2, $autoresponder3ProcessedFiveDaysAgo);
            $autoresponder3NotProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                    0,
                                                                                    $autoresponder3->id);
            $this->assertEmpty($autoresponder3NotProcessedBeforeNow);
            $autoresponder3NotProcessedFiveDaysFromNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                    0,
                                                                                    $autoresponder3->id,
                                                                                    strtotime("+5 day")); // Not Coding Standard
            $this->assertNotEmpty($autoresponder3NotProcessedFiveDaysFromNow);
            $this->assertCount(1, $autoresponder3NotProcessedFiveDaysFromNow);
            $autoresponder4ProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                            1,
                                                                                            $autoresponder4->id);
            $this->assertNotEmpty($autoresponder4ProcessedBeforeNow);
            $this->assertCount(3, $autoresponder4ProcessedBeforeNow);
            $autoresponder4ProcessedFiveDaysAgo   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                            1,
                                                                                            $autoresponder4->id,
                                                                                            strtotime("-5 day"));
            $this->assertEmpty($autoresponder4ProcessedFiveDaysAgo);
            $autoresponder4NotProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                        0,
                                                                                        $autoresponder4->id);
            $this->assertEmpty($autoresponder4NotProcessedBeforeNow);
            $autoresponder4NotProcessedFiveDaysFromNow   = AutoresponderItem::getByProcessedAndAutoresponderIdWithProcessDateTime(
                                                                                        0,
                                                                                        $autoresponder4->id,
                                                                                        strtotime("+5 day")); // Not Coding Standard
            $this->assertNotEmpty($autoresponder4NotProcessedFiveDaysFromNow);
            $this->assertCount(2, $autoresponder4NotProcessedFiveDaysFromNow);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testGetLabel()
        {
            $autoresponderItem = RandomDataUtil::getRandomValueFromArray(AutoresponderItem::getAll());
            $this->assertNotNull($autoresponderItem);
            $this->assertEquals('Autoresponder Item',  $autoresponderItem::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Autoresponder Items', $autoresponderItem::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testDeleteAutoresponderItem()
        {
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertCount(37, $autoresponderItems);
            $autoresponderItems[0]->delete();
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertEquals(36, count($autoresponderItems));
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testAddNewItem()
        {
            $super              = User::getByUsername('super');
            $processed          = 0;
            $processDateTime    = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $contact            = ContactTestHelper::createContactByNameForOwner('autoresponderContact', Yii::app()->user->userModel);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $autoresponder      = AutoresponderTestHelper::createAutoresponder('test autoresponder 01',
                                                                                'This is text content 01',
                                                                                'This is <b>html</b> content 01',
                                                                                10,
                                                                                Autoresponder::OPERATION_SUBSCRIBE,
                                                                                true,
                                                                                $marketingList
                                                                            );
            $saved              = AutoresponderItem::addNewItem($processed, $processDateTime, $contact, $autoresponder);
            $this->assertTrue($saved);
            $autoresponderItems = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                        $autoresponder->id);
            $this->assertNotEmpty($autoresponderItems);
            $this->assertCount(1, $autoresponderItems);
        }

        /**
         * @depends testAddNewItem
         */
        public function testRegisterAutoresponderItemsByAutoresponderOperation()
        {
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04');
            $autoresponderSubscribe     = AutoresponderTestHelper::createAutoresponder('test autoresponder Subscribe',
                                                                            'This is text content Subscribe',
                                                                            'This is <b>html</b> content Subscribe',
                                                                            10,
                                                                            Autoresponder::OPERATION_SUBSCRIBE,
                                                                            true,
                                                                            $marketingList
                                                                        );
            $this->assertNotNull($autoresponderSubscribe);
            $autoresponderUnsubscribe   = AutoresponderTestHelper::createAutoresponder('test autoresponder Unsubscribe',
                                                                            'This is text content Unsubscribe',
                                                                            'This is <b>html</b> content Unsubscribe',
                                                                            20,
                                                                            Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                            true,
                                                                            $marketingList
                                                                        );
            $this->assertNotNull($autoresponderUnsubscribe);
            $contact1           = ContactTestHelper::createContactByNameForOwner('autoresponderContact 01',
                                                                                        Yii::app()->user->userModel);
            $contact2           = ContactTestHelper::createContactByNameForOwner('autoresponderContact 02',
                                                                                        Yii::app()->user->userModel);
            $contact3           = ContactTestHelper::createContactByNameForOwner('autoresponderContact 03',
                                                                                        Yii::app()->user->userModel);
            $contact4           = ContactTestHelper::createContactByNameForOwner('autoresponderContact 04',
                                                                                        Yii::app()->user->userModel);
            AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation(Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    $marketingList->id,
                                                                                    $contact1);
            AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation(Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    $marketingList->id,
                                                                                    $contact2);
            $autoresponderItemsSubscribe = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                        $autoresponderSubscribe->id);
            $this->assertNotEmpty($autoresponderItemsSubscribe);
            $this->assertCount(2, $autoresponderItemsSubscribe);

            AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation(Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                        $marketingList->id, $contact3);
            AutoresponderItem::registerAutoresponderItemsByAutoresponderOperation(Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                        $marketingList->id, $contact4);
            $autoresponderItemsUnsubscribe = AutoresponderItem::getByProcessedAndAutoresponderId(0,
                                                                                        $autoresponderUnsubscribe->id);
            $this->assertNotEmpty($autoresponderItemsUnsubscribe);
            $this->assertCount(2, $autoresponderItemsUnsubscribe);
        }
    }
?>