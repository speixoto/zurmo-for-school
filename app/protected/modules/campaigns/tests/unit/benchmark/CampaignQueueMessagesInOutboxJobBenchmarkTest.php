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
    class CampaignQueueMessagesInOutboxJobBenchmarkTest extends AutoresponderOrCampaignBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
            Campaign::deleteAll();
            CampaignItem::deleteAll();
            Contact::deleteAll();
            MarketingList::deleteAll();
        }

        public function testSingleItem()
        {
            $this->testItems(1, 1);
        }

        /**
         * @depends testSingleItem
         */
        public function testFiveItems()
        {
            $this->testItems(5, 1);
        }

        /**
         * @depends testFiveItems
         */
        public function testTenItems()
        {
            $this->testItems(10, 1);
        }

        /**
         * @depends testTenItems
         */
        public function testFiftyItems()
        {
            $this->testItems(50, 1);
        }

        /**
         * @depends testFiftyItems
         */
        public function testHundredItems()
        {
            $this->testItems(100, 1);
        }

        /**
         * @depends testHundredItems
         */
        public function testTwoFiftyItems()
        {
            $this->testItems(250, 1);
        }

        /**
         * @depends testTwoFiftyItems
         */
        public function testFiveHundredItems()
        {
            $this->testItems(500, 1);
        }

        /**
         * @depends testFiveHundredItems
         */
        public function testThousandItems()
        {
            $this->testItems(1000, 1);
        }

        protected function testItems($count, $expectedRatio)
        {
            $timeSpent  = $this->generateAndProcessCampaignItems($count);
            echo PHP_EOL. $count . ' items took ' . $timeSpent . ' seconds';
            $this->assertTrue(($timeSpent/$count) <= $expectedRatio);
        }

        public function generateAndProcessCampaignItems($count)
        {
            $contacts                   = array();
            for ($i = 0; $i < $count; $i++)
            {
                $email                  = new Email();
                $email->emailAddress    = "demo$i@zurmo.com";
                $contact                = ContactTestHelper::createContactByNameForOwner('contact 0' . ($i + 8), $this->user);
                $contact->primaryEmail  = $email;
                $this->assertTrue($contact->save());
                $contacts[]             = $contact;
            }
            $content                    = <<<MTG
[[TITLE]] [[LAST^NAME]], [[FIRST^NAME]]
[[MODEL^URL]]
[[OWNERS^AVATAR^MEDIUM]]
[[DESCRIPTION]]
[[JOB^TITLE]] @ [[DEPARTMENT]] / [[COMPANY^NAME]] ( [[INDUSTRY]] )
[[WEBSITE]]
[[OFFICE^PHONE]] , [[OFFICE^FAX]]
[[MOBILE^PHONE]]
[[OWNERS^EMAIL^SIGNATURE]]

[[SOURCE]],  [[STATE]]
[[APPLICATION^NAME]] [c] [[CURRENT^YEAR]]
[[BASE^URL]]
MTG;
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList Test',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign Test',
                                                                                'subject',
                                                                                $content,
                                                                                $content,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_PROCESSING,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $fileNames                  = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                      = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $campaign->files->add($file);
            }
            $this->assertTrue($campaign->save());
            $processed                  = 0;
            foreach ($contacts as $contact)
            {
                CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            }
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize($count);
            Yii::app()->jobQueue->deleteAll();
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $startedAt      = microtime(true);
            $this->assertTrue($job->run());
            $timeTaken      = microtime(true) - $startedAt;

            ForgetAllCacheUtil::forgetAllCaches();
            $campaignItemsCountExpected = $count;
            $campaignItemsCountAfter    = CampaignItem::getCount();
            $this->assertEquals($campaignItemsCountExpected, $campaignItemsCountAfter);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(1, $campaign->id);
            $this->assertCount($count, $campaignItemsProcessed);
            foreach ($campaignItemsProcessed as $campaignItem)
            {
                $emailMessage               = $campaignItem->emailMessage;
                $this->assertNotEmpty($emailMessage->files);
                $this->assertCount(count($files), $emailMessage->files);
                foreach ($files as $index => $file)
                {
                    $this->assertEquals($files[$index]['name'], $emailMessage->files[$index]->name);
                    $this->assertEquals($files[$index]['type'], $emailMessage->files[$index]->type);
                    $this->assertEquals($files[$index]['size'], $emailMessage->files[$index]->size);
                    $this->assertEquals($files[$index]['contents'], $emailMessage->files[$index]->fileContent->content);
                }
            }
            return $timeTaken;
        }
    }
?>