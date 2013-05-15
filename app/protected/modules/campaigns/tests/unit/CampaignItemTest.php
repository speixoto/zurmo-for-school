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
    class CampaignItemTest extends ZurmoBaseTest
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

        public function testCreateAndGetCampaignItemById()
        {
            $campaignItem                          = new CampaignItem();
            $campaignItem->processed               = CampaignItem::PROCESSED;
            $this->assertTrue($campaignItem->unrestrictedSave());
            $id = $campaignItem->id;
            unset($campaignItem);
            $campaignItem = CampaignItem::getById($id);
            $this->assertEquals(CampaignItem::PROCESSED,   $campaignItem->processed);
        }

        /**
         * @depends testCreateAndGetCampaignItemById
         */
        public function testRequiredAttributes()
        {
            $campaignItem                          = new CampaignItem();
            $this->assertTrue($campaignItem->unrestrictedSave());
            $id = $campaignItem->id;
            unset($campaignItem);
            $campaignItem = CampaignItem::getById($id);
            $this->assertEquals(CampaignItem::NOT_PROCESSED,   $campaignItem->processed);
        }

        /**
         * @depends testCreateAndGetCampaignItemById
         */
        public function testGetByProcessed()
        {
            for ($i = 0; $i < 5; $i++)
            {
                $processed                          = CampaignItem::NOT_PROCESSED;
                if ($i % 2)
                {
                    $processed      = CampaignItem::PROCESSED;
                }
                $campaignItem                  = new CampaignItem();
                $campaignItem->processed       = $processed;
                $this->assertTrue($campaignItem->unrestrictedSave());
            }
            $campaignItems         =   CampaignItem::getAll();
            $this->assertCount(7, $campaignItems);
            $processedItems             =   CampaignItem::getByProcessed(CampaignItem::PROCESSED);
            $this->assertCount(3, $processedItems);
            $notProcessedItems          =   CampaignItem::getByProcessed(CampaignItem::NOT_PROCESSED);
            $this->assertCount(4, $notProcessedItems);
        }

        /**
         * @depends testGetByProcessed
         */
        public function testGetByProcessedAndTime()
        {
            $marketingList                  = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $this->assertNotNull($marketingList);
            $campaignToday                  = CampaignTestHelper::createCampaign('campaign Today',
                                                                                    'subject Today',
                                                                                    'text Today',
                                                                                    'html Today',
                                                                                    null,
                                                                                    null,
                                                                                    null,
                                                                                    Campaign::TYPE_MARKETING_LIST,
                                                                                    null,
                                                                                    null,
                                                                                    null,
                                                                                    null,
                                                                                    $marketingList);
            $this->assertNotNull($campaignToday);
            $tenDaysFromNowDateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 60*60*24*10);
            $campaignTenDaysFromNow     = CampaignTestHelper::createCampaign('campaign Ten Days',
                                                                                    'subject Ten Days',
                                                                                    'text Ten Days',
                                                                                    'html Ten Days',
                                                                                    null,
                                                                                    null,
                                                                                    null,
                                                                                    Campaign::TYPE_MARKETING_LIST,
                                                                                    null,
                                                                                    null,
                                                                                    $tenDaysFromNowDateTime,
                                                                                    null,
                                                                                    $marketingList);
            $this->assertNotNull($campaignTenDaysFromNow);
            for ($i = 0; $i < 10; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('contact ' . $i, Yii::app()->user->userModel);
                $this->assertNotNull($contact);
                if ($i % 3)
                {
                    $processed      = CampaignItem::PROCESSED;
                }
                else
                {
                    $processed      = CampaignItem::NOT_PROCESSED;
                }
                if ($i % 2)
                {
                    $campaign  = $campaignToday;
                }
                else
                {
                    $campaign  = $campaignTenDaysFromNow;
                }
                $campaignItem       = CampaignItemTestHelper::createCampaignItem($processed, $campaign);
                $this->assertNotNull($campaignItem);
            }

            $campaignItems         = CampaignItem::getAll();
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(17, $campaignItems);
            $campaignTodayProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::PROCESSED,
                                                                                                $campaignToday->id);
            $this->assertNotEmpty($campaignTodayProcessed);
            $this->assertCount(3, $campaignTodayProcessed);
            $campaignTodayNotProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED,
                                                                                                $campaignToday->id);
            $this->assertNotEmpty($campaignTodayNotProcessed);
            $this->assertCount(2, $campaignTodayNotProcessed);
            $campaignTenDaysFromNowProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::PROCESSED,
                                                                                                $campaignTenDaysFromNow->id);
            $this->assertNotEmpty($campaignTenDaysFromNowProcessed);
            $this->assertCount(3, $campaignTenDaysFromNowProcessed);
            $campaignTenDaysFromNowNotProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED,
                                                                                                $campaignTenDaysFromNow->id);
            $this->assertNotEmpty($campaignTenDaysFromNowNotProcessed);
            $this->assertCount(2, $campaignTenDaysFromNowNotProcessed);
        }

        /**
         * @depends testGetByProcessed
         */
        public function testGetByProcessedAndCampaignId()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $this->assertNotNull($marketingList);
            $campaign1          = CampaignTestHelper::createCampaign('campaign 01',
                                                                        'subject 01',
                                                                        'text 01',
                                                                        'html 01',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::TYPE_MARKETING_LIST,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign1);
            $campaign2          = CampaignTestHelper::createCampaign('campaign 02',
                                                                        'subject 02',
                                                                        'text 02',
                                                                        'html 02',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::TYPE_MARKETING_LIST,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign2);
            for ($i = 0; $i < 10; $i++)
            {
                $contact = ContactTestHelper::createContactByNameForOwner('contact 0' . $i, Yii::app()->user->userModel);
                $this->assertNotNull($contact);
                if ($i % 3)
                {
                    $processed      = CampaignItem::PROCESSED;
                }
                else
                {
                    $processed      = CampaignItem::NOT_PROCESSED;
                }
                if ($i % 2)
                {
                    $campaign  = $campaign1;
                }
                else
                {
                    $campaign  = $campaign2;
                }
                $campaignItem = CampaignItemTestHelper::createCampaignItem($processed, $campaign);
                $this->assertNotNull($campaignItem);
            }
            $campaignItems         = CampaignItem::getAll();
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(27, $campaignItems);
            $campaign1Processed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::PROCESSED,
                                                                                            $campaign1->id);
            $this->assertNotEmpty($campaign1Processed);
            $this->assertCount(3, $campaign1Processed);
            $campaign1NotProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED,
                                                                                                $campaign1->id);
            $this->assertNotEmpty($campaign1NotProcessed);
            $this->assertCount(2, $campaign1NotProcessed);
            $campaign2Processed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::PROCESSED,
                                                                                            $campaign2->id);
            $this->assertNotEmpty($campaign2Processed);
            $this->assertCount(3, $campaign2Processed);
            $campaign2NotProcessed  = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED,
                                                                                                $campaign2->id);
            $this->assertNotEmpty($campaign2NotProcessed);
            $this->assertCount(2, $campaign2NotProcessed);
        }

        /**
         * @depends testGetByProcessedAndCampaignId
         */
        public function testGetLabel()
        {
            $campaignItem = RandomDataUtil::getRandomValueFromArray(CampaignItem::getAll());
            $this->assertNotNull($campaignItem);
            $this->assertEquals('Campaign Item',  $campaignItem::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Campaign Items', $campaignItem::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetCampaignItemById
         */
        public function testDeleteCampaignItem()
        {
            $campaignItems = CampaignItem::getAll();
            $this->assertCount(27, $campaignItems);
            $campaignItems[0]->delete();
            $campaignItems = CampaignItem::getAll();
            $this->assertEquals(26, count($campaignItems));
        }

        /**
         * @depends testCreateAndGetCampaignItemById
         */
        public function testAddNewItem()
        {
            $super              = User::getByUsername('super');
            $processed          = CampaignItem::NOT_PROCESSED;
            $contact            = ContactTestHelper::createContactByNameForOwner('campaignContact', Yii::app()->user->userModel);
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $campaign           = CampaignTestHelper::createCampaign('campaign 03',
                                                                        'subject 03',
                                                                        'text 03',
                                                                        'html 03',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::TYPE_MARKETING_LIST,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $saved              = CampaignItem::addNewItem($processed, $contact, $campaign);
            $this->assertTrue($saved);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED,
                                                                                        $campaign->id);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(1, $campaignItems);
        }

        /**
         * @depends testAddNewItem
         */
        public function testRegisterCampaignItemsByCampaignCreation()
        {
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 04',
                                                                                'subject 04',
                                                                                'text 04',
                                                                                'html 04',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::TYPE_MARKETING_LIST,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $this->assertNotNull($campaign);
            $contacts           = array();
            $contacts[]         = ContactTestHelper::createContactByNameForOwner('campaignContact 01',
                                                                                        Yii::app()->user->userModel);
            $contacts[]         = ContactTestHelper::createContactByNameForOwner('campaignContact 02',
                                                                                        Yii::app()->user->userModel);
            $contacts[]         = ContactTestHelper::createContactByNameForOwner('campaignContact 03',
                                                                                        Yii::app()->user->userModel);
            $contacts[]         = ContactTestHelper::createContactByNameForOwner('campaignContact 04',
                                                                                        Yii::app()->user->userModel);
            $contacts[]         = ContactTestHelper::createContactByNameForOwner('campaignContact 05',
                                                                                        Yii::app()->user->userModel);

            CampaignItem::registerCampaignItemsByCampaign($campaign, $contacts);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(CampaignItem::NOT_PROCESSED, $campaign->id);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(5, $campaignItems);
        }
    }
?>