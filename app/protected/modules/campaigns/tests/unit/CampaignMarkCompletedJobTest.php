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
    class CampaignMarkCompletedJobTest extends ZurmoBaseTest
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
        }

        public function testGetDisplayName()
        {
            $displayName                = CampaignMarkCompletedJob::getDisplayName();
            $this->assertEquals('Mark campaigns as completed', $displayName);
        }

        public function testGetType()
        {
            $type                       = CampaignMarkCompletedJob::getType();
            $this->assertEquals('CampaignMarkCompleted', $type);
        }

        public function testGetRecommendedRunFrequencyContent()
        {
            $recommendedRunFrequency    = CampaignMarkCompletedJob::getRecommendedRunFrequencyContent();
            $this->assertEquals('Every hour', $recommendedRunFrequency);
        }

        public function testRunWithoutAnyCampaigns()
        {
            $campaigns              = Campaign::getAll();
            $this->assertEmpty($campaigns);
            $job                    = new CampaignMarkCompletedJob();
            $this->assertTrue($job->run());
        }

        /**
         * @depends testRunWithoutAnyCampaigns
         */
        public function testRunWithNoCampaignWithProcessingStatus()
        {
            $marketingList          = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaignActive         = CampaignTestHelper::createCampaign('Active',
                                                                            'subject',
                                                                            'text Content',
                                                                            'Html Content',
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            Campaign::TYPE_MARKETING_LIST,
                                                                            Campaign::STATUS_ACTIVE,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            $marketingList);
            $campaignActiveId       = $campaignActive->id;
            $this->assertNotNull($campaignActive);
            $campaignIncomplete     = CampaignTestHelper::createCampaign('Incomplete',
                                                                            'subject',
                                                                            'text Content',
                                                                            'Html Content',
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            Campaign::TYPE_MARKETING_LIST,
                                                                            Campaign::STATUS_INCOMPLETE,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            $marketingList);
            $campaignIncompleteId   = $campaignIncomplete->id;
            $this->assertNotNull($campaignIncomplete);
            $campaignPaused         = CampaignTestHelper::createCampaign('Paused',
                                                                            'subject',
                                                                            'text Content',
                                                                            'Html Content',
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            Campaign::TYPE_MARKETING_LIST,
                                                                            Campaign::STATUS_PAUSED,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            $marketingList);
            $campaignPausedId       = $campaignPaused->id;
            $this->assertNotNull($campaignPaused);
            $campaignActive->forgetAll();
            $campaignPaused->forgetAll();
            $campaignIncomplete->forgetAll();
            $job                    = new CampaignMarkCompletedJob();
            $this->assertTrue($job->run());
            $campaignActive         = Campaign::getById($campaignActiveId);
            $this->assertNotNull($campaignActive);
            $this->assertEquals(Campaign::STATUS_ACTIVE, $campaignActive->status);
            $campaignIncomplete         = Campaign::getById($campaignIncompleteId);
            $this->assertNotNull($campaignIncomplete);
            $this->assertEquals(Campaign::STATUS_INCOMPLETE, $campaignIncomplete->status);
            $campaignPaused         = Campaign::getById($campaignPausedId);
            $this->assertNotNull($campaignPaused);
            $this->assertEquals(Campaign::STATUS_PAUSED, $campaignPaused->status);
        }

        /**
         * @depends testRunWithNoCampaignWithProcessingStatus
         */
        public function testRunWithCampaignWithProcessingStatusButNoProcessedItems()
        {
            $contact                = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList          = MarketingListTestHelper::populateMarketingListByName('marketingList 02');
            $campaignProcessing     = CampaignTestHelper::createCampaign('Processing But No Processed Items',
                                                                                'subject',
                                                                                'text Content',
                                                                                'Html Content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::TYPE_MARKETING_LIST,
                                                                                Campaign::STATUS_PROCESSING,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $campaignProcessingId    = $campaignProcessing->id;
            $this->assertNotNull($campaignProcessing);
            CampaignItemTestHelper::createCampaignItem(0, $campaignProcessing, $contact);
            $campaignProcessing->forgetAll();
            $job                    = new CampaignMarkCompletedJob();
            $this->assertTrue($job->run());
            $campaignProcessing         = Campaign::getById($campaignProcessingId);
            $this->assertNotNull($campaignProcessing);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaignProcessing->status);
        }

        /**
         * @depends testRunWithCampaignWithProcessingStatusButNoProcessedItems
         */
        public function testRunWithCampaignWithProcessingStatusAndProcessedItems()
        {
            $contact                = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList          = MarketingListTestHelper::populateMarketingListByName('marketingList 03');
            $campaignProcessing     = CampaignTestHelper::createCampaign('Processing With Processed Items',
                                                                            'subject',
                                                                            'text Content',
                                                                            'Html Content',
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            Campaign::TYPE_MARKETING_LIST,
                                                                            Campaign::STATUS_PROCESSING,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            $marketingList);
            $campaignProcessingId    = $campaignProcessing->id;
            $this->assertNotNull($campaignProcessing);
            CampaignItemTestHelper::createCampaignItem(1, $campaignProcessing, $contact);
            $campaignProcessing->forgetAll();
            $job                    = new CampaignMarkCompletedJob();
            $this->assertTrue($job->run());
            $campaignProcessing         = Campaign::getById($campaignProcessingId);
            $this->assertNotNull($campaignProcessing);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaignProcessing->status);
        }
    }
?>