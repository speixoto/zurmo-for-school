<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
            $time                                       = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 week'));
            $autoresponderItem                          = new AutoresponderItem();
            $autoresponderItem->processed               = AutoresponderItem::NOT_PROCESSED;
            $autoresponderItem->processDateTime         = $time;
            $this->assertTrue($autoresponderItem->unrestrictedSave());
            $id = $autoresponderItem->id;
            unset($autoresponderItem);
            $autoresponderItem = AutoresponderItem::getById($id);
            $this->assertEquals(AutoresponderItem::NOT_PROCESSED,   $autoresponderItem->processed);
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

            $time                                       = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 week'));
            $autoresponderItem                          = new AutoresponderItem();
            $autoresponderItem->processed               = AutoresponderItem::PROCESSED;
            $autoresponderItem->processDateTime         = $time;
            $this->assertTrue($autoresponderItem->unrestrictedSave());
            $id = $autoresponderItem->id;
            unset($autoresponderItem);
            $autoresponderItem = AutoresponderItem::getById($id);
            $this->assertEquals(AutoresponderItem::PROCESSED,       $autoresponderItem->processed);
            $this->assertEquals($time,                              $autoresponderItem->processDateTime);
        }

        /**
         * @depends testCreateAndGetAutoresponderItemById
         */
        public function testGetByProcessed()
        {
            for($i = 0; $i < 5; $i++)
            {
                $time                               = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 day'));
                $processed                          = AutoresponderItem::NOT_PROCESSED;
                if ($i % 2)
                {
                    $processed      = AutoresponderItem::PROCESSED;
                }
                $autoresponderItem                  = new AutoresponderItem();
                $autoresponderItem->processed       = $processed;
                $autoresponderItem->processDateTime = $time;
                $this->assertTrue($autoresponderItem->unrestrictedSave());
            }
            $autoresponderItems         =   AutoresponderItem::getAll();
            $this->assertCount(7, $autoresponderItems);
            $processedItems             =   AutoresponderItem::getByProcessed(AutoresponderItem::PROCESSED);
            $this->assertCount(3, $processedItems);
            $notProcessedItems          =   AutoresponderItem::getByProcessed(AutoresponderItem::NOT_PROCESSED);
            $this->assertCount(4, $notProcessedItems);
        }


        /**
         * @depends testGetByProcessed
         */
        public function testGetByProcessedAndAutoresponderId()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $this->assertNotNull($marketingList);
            $autoresponder1     = AutoresponderTestHelper::createAutoresponder('autoresponder 01', 'subject 01', 'text 01',
                                                    'html 01', 10, Autoresponder::OPERATION_UNSUBSCRIBE,  $marketingList);
            $this->assertNotNull($autoresponder1);
            $autoresponder2     = AutoresponderTestHelper::createAutoresponder('autoresponder 02', 'subject 02', 'text 02',
                                                    'html 02', 20, Autoresponder::OPERATION_SUBSCRIBE,  $marketingList);
            $this->assertNotNull($autoresponder2);
            for($i = 0; $i < 10; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('contact 0' . $i, Yii::app()->user->userModel);
                $this->assertNotNull($contact);
                $time                               = DateTimeUtil::convertTimestampToDbFormatDateTime(strtotime('+1 day'));
                if ($i % 3)
                {
                    $processed      = AutoresponderItem::PROCESSED;
                }
                else
                {
                    $processed      = AutoresponderItem::NOT_PROCESSED;
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
            $this->assertCount(17, $autoresponderItems);
            $autoresponder1Processed  = AutoresponderItem::getByProcessedAndAutoresponderId(AutoresponderItem::PROCESSED,
                                                                                            $autoresponder1->id);
            $this->assertNotEmpty($autoresponder1Processed);
            $this->assertCount(3, $autoresponder1Processed);
            $autoresponder1NotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(AutoresponderItem::NOT_PROCESSED,
                                                                                                $autoresponder1->id);
            $this->assertNotEmpty($autoresponder1NotProcessed);
            $this->assertCount(2, $autoresponder1NotProcessed);
            $autoresponder2Processed  = AutoresponderItem::getByProcessedAndAutoresponderId(AutoresponderItem::PROCESSED,
                                                                                            $autoresponder2->id);
            $this->assertNotEmpty($autoresponder2Processed);
            $this->assertCount(3, $autoresponder2Processed);
            $autoresponder2NotProcessed  = AutoresponderItem::getByProcessedAndAutoresponderId(AutoresponderItem::NOT_PROCESSED,
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
            $autoresponder3     = AutoresponderTestHelper::createAutoresponder('autoresponder 03', 'subject 03', 'text 03',
                                                'html 03', 10, Autoresponder::OPERATION_UNSUBSCRIBE,  $marketingList[0]);
            $this->assertNotNull($autoresponder3);
            $autoresponder4     = AutoresponderTestHelper::createAutoresponder('autoresponder 04', 'subject 04', 'text 04',
                                                    'html 04', 20, Autoresponder::OPERATION_SUBSCRIBE,  $marketingList[0]);
            $this->assertNotNull($autoresponder4);
            for($i = 0; $i < 10; $i++)
            {
                if ($i % 3)
                {
                    $pastOrFuture   = "-";
                    $processed      = AutoresponderItem::PROCESSED;
                }
                else
                {
                    $pastOrFuture   = "+";
                    $processed      = AutoresponderItem::NOT_PROCESSED;
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
                $timestamp          = strtotime($pastOrFuture . ($i+1) . ' ' . $interval);
                $time               = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
                $autoresponderItem  = AutoresponderItemTestHelper::createAutoresponderItem($processed, $time, $autoresponder);
                $this->assertNotNull($autoresponderItem);
            }

            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(27, $autoresponderItems);
            $autoresponder3ProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                            AutoresponderItem::PROCESSED,
                                                                                            $autoresponder3->id);
            $this->assertNotEmpty($autoresponder3ProcessedBeforeNow);
            $this->assertCount(3, $autoresponder3ProcessedBeforeNow);
            $autoresponder3ProcessedFiveDaysAgo   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                        AutoresponderItem::PROCESSED,
                                                                                        $autoresponder3->id,
                                                                                        strtotime("-5 day"));
            $this->assertNotEmpty($autoresponder3ProcessedFiveDaysAgo);
            $this->assertCount(2, $autoresponder3ProcessedFiveDaysAgo);
            $autoresponder3NotProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                    AutoresponderItem::NOT_PROCESSED,
                                                                                    $autoresponder3->id);
            $this->assertEmpty($autoresponder3NotProcessedBeforeNow);
            $autoresponder3NotProcessedFiveDaysFromNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                    AutoresponderItem::NOT_PROCESSED,
                                                                                    $autoresponder3->id,
                                                                                    strtotime("+5 day"));
            $this->assertNotEmpty($autoresponder3NotProcessedFiveDaysFromNow);
            $this->assertCount(1, $autoresponder3NotProcessedFiveDaysFromNow);
            $autoresponder4ProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                            AutoresponderItem::PROCESSED,
                                                                                            $autoresponder4->id);
            $this->assertNotEmpty($autoresponder4ProcessedBeforeNow);
            $this->assertCount(3, $autoresponder4ProcessedBeforeNow);
            $autoresponder4ProcessedFiveDaysAgo   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                            AutoresponderItem::PROCESSED,
                                                                                            $autoresponder4->id,
                                                                                            strtotime("-5 day"));
            $this->assertEmpty($autoresponder4ProcessedFiveDaysAgo);
            $autoresponder4NotProcessedBeforeNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponder4->id);
            $this->assertEmpty($autoresponder4NotProcessedBeforeNow);
            $autoresponder4NotProcessedFiveDaysFromNow   = AutoresponderItem::getByProcessedAndAutoresponderIdAndTime(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponder4->id,
                                                                                        strtotime("+5 day"));
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
            $this->assertCount(27, $autoresponderItems);
            $autoresponderItems[0]->delete();
            $autoresponderItems = AutoresponderItem::getAll();
            $this->assertEquals(26, count($autoresponderItems));
        }
    }
?>