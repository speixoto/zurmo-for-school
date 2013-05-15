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
    class CampaignTest extends ZurmoBaseTest
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

        public function testCreateAndGetCampaignListById()
        {
            $campaign                   = new Campaign();
            $campaign->name             = 'Test Campaign Name';
            $campaign->type             = Campaign::TYPE_MARKETING_LIST;
            $campaign->supportsRichText = Campaign::SUPPORTS_RICH_TEXT;
            $campaign->status           = Campaign::STATUS_PAUSED;
            $campaign->fromName         = 'Test From Name';
            $campaign->fromAddress      = 'from@zurmo.com';
            $campaign->subject          = 'Test Subject';
            $campaign->htmlContent      = 'Test Html Content';
            $campaign->textContent      = 'Test Text Content';
            $campaign->fromName         = 'From Name';
            $campaign->fromAddress      = 'from@zurmo.com';
            $campaign->sendNow          = Campaign::SEND_NOW;
            $this->assertTrue($campaign->save());
            $id                         = $campaign->id;
            unset($campaign);
            $campaign                   = Campaign::getById($id);
            $this->assertEquals('Test Campaign Name',                       $campaign->name);
            $this->assertEquals(Campaign::TYPE_MARKETING_LIST,              $campaign->type);
            $this->assertEquals(Campaign::SUPPORTS_RICH_TEXT,               $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaign->status);
            $this->assertEquals('From Name',                           $campaign->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaign->fromAddress);
            $this->assertEquals('Test Subject',                             $campaign->subject);
            $this->assertEquals('Test Html Content',                        $campaign->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaign->textContent);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testRequiredAttributes()
        {
            $campaign                   = new Campaign();
            $this->assertFalse($campaign->save());
            $errors                     = $campaign->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(8, $errors);
            $this->assertArrayHasKey('name', $errors);
            $this->assertEquals('Name cannot be blank.', $errors['name'][0]);
            $this->assertArrayHasKey('status', $errors);
            $this->assertEquals('Status cannot be blank.', $errors['status'][0]);
            $this->assertArrayHasKey('supportsRichText', $errors);
            $this->assertEquals('Supports Rich Text(HTML) outgoing emails cannot be blank.', $errors['supportsRichText'][0]);
            $this->assertArrayHasKey('subject', $errors);
            $this->assertEquals('Subject cannot be blank.', $errors['subject'][0]);
            $this->assertArrayHasKey('fromName', $errors);
            $this->assertEquals('From Name cannot be blank.', $errors['fromName'][0]);
            $this->assertArrayHasKey('fromAddress', $errors);
            $this->assertEquals('From Address cannot be blank.', $errors['fromAddress'][0]);
            $this->assertArrayHasKey('textContent', $errors);
            $this->assertEquals('Please provide at least one of the contents field.', $errors['textContent'][0]);
            $this->assertArrayHasKey('sendNow', $errors);
            $this->assertEquals('Send Now? cannot be blank.', $errors['sendNow'][0]);

            $campaign->sendNow          = Campaign::SEND_DELAYED;
            $this->assertFalse($campaign->save());
            $errors = $campaign->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(8, $errors);
            $this->assertArrayHasKey('name', $errors);
            $this->assertEquals('Name cannot be blank.', $errors['name'][0]);
            $this->assertArrayHasKey('status', $errors);
            $this->assertEquals('Status cannot be blank.', $errors['status'][0]);
            $this->assertArrayHasKey('supportsRichText', $errors);
            $this->assertEquals('Supports Rich Text(HTML) outgoing emails cannot be blank.', $errors['supportsRichText'][0]);
            $this->assertArrayHasKey('subject', $errors);
            $this->assertEquals('Subject cannot be blank.', $errors['subject'][0]);
            $this->assertArrayHasKey('fromName', $errors);
            $this->assertEquals('From Name cannot be blank.', $errors['fromName'][0]);
            $this->assertArrayHasKey('fromAddress', $errors);
            $this->assertEquals('From Address cannot be blank.', $errors['fromAddress'][0]);
            $this->assertArrayHasKey('textContent', $errors);
            $this->assertEquals('Please provide at least one of the contents field.', $errors['textContent'][0]);
            $this->assertArrayHasKey('sendingDateTime', $errors);
            $this->assertEquals('Send On cannot be blank.', $errors['sendingDateTime'][0]);

            $campaign->name             = 'Test Campaign Name2';
            $campaign->type             = Campaign::TYPE_MARKETING_LIST;
            $campaign->supportsRichText = Campaign::SUPPORTS_PLAIN_TEXT_ONLY;
            $campaign->status           = Campaign::STATUS_ACTIVE;
            $campaign->fromName         = 'From Name2';
            $campaign->fromAddress      = 'from2@zurmo.com';
            $campaign->subject          = 'Test Subject2';
            $campaign->htmlContent      = 'Test Html Content2';
            $campaign->textContent      = 'Test Text Content2';
            $campaign->sendNow          = Campaign::SEND_NOW;
            $campaign->fromName         = 'From Name2';
            $campaign->fromAddress      = 'from2@zurmo.com';
            $this->assertTrue($campaign->save());
            $id                         = $campaign->id;
            unset($campaign);
            $campaign                   = Campaign::getById($id);

            $this->assertEquals('Test Campaign Name2',                      $campaign->name);
            $this->assertEquals(Campaign::TYPE_MARKETING_LIST,              $campaign->type);
            $this->assertEquals(Campaign::SUPPORTS_PLAIN_TEXT_ONLY,         $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_ACTIVE,                    $campaign->status);
            $this->assertEquals('From Name2',                               $campaign->fromName);
            $this->assertEquals('from2@zurmo.com',                          $campaign->fromAddress);
            $this->assertEquals('Test Subject2',                            $campaign->subject);
            $this->assertEquals('Test Html Content2',                       $campaign->htmlContent);
            $this->assertEquals('Test Text Content2',                       $campaign->textContent);
            $this->assertEquals(Campaign::SEND_NOW,                         $campaign->sendNow);
            $this->assertTrue((time() + 15) > DateTimeUtil::convertDbFormatDateTimeToTimestamp($campaign->sendingDateTime));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testGetCampaignByName()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Test Campaign Name', $campaigns[0]->name);
            $this->assertEquals(Campaign::TYPE_MARKETING_LIST,              $campaigns[0]->type);
            $this->assertEquals(Campaign::SUPPORTS_RICH_TEXT,               $campaigns[0]->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaigns[0]->status);
            $this->assertEquals('From Name',                                $campaigns[0]->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaigns[0]->fromAddress);
            $this->assertEquals('Test Subject',                             $campaigns[0]->subject);
            $this->assertEquals('Test Html Content',                        $campaigns[0]->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaigns[0]->textContent);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testGetLabel()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Campaign',  $campaigns[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Campaigns', $campaigns[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testToString()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Test Campaign Name', strval($campaigns[0]));
        }

        /**
         * @depends testRequiredAttributes
         */
        public function testDeleteCampaign()
        {
            $campaigns = Campaign::getAll();
            $this->assertEquals(2, count($campaigns));
            $campaigns[0]->delete();
            $campaigns = Campaign::getAll();
            $this->assertEquals(1, count($campaigns));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testBeforeSaveCampaignItemRegister()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 04');
            $marketingListId    = $marketingList->id;
            $contact1           = ContactTestHelper::createContactByNameForOwner('campaignContact 01',
                                                                                        Yii::app()->user->userModel);
            $contact2           = ContactTestHelper::createContactByNameForOwner('campaignContact 02',
                                                                                        Yii::app()->user->userModel);
            $contact3           = ContactTestHelper::createContactByNameForOwner('campaignContact 03',
                                                                                        Yii::app()->user->userModel);
            $contact4           = ContactTestHelper::createContactByNameForOwner('campaignContact 04',
                                                                                        Yii::app()->user->userModel);
            $contact5           = ContactTestHelper::createContactByNameForOwner('campaignContact 05',
                                                                                        Yii::app()->user->userModel);
            $processed          = CampaignItem::NOT_PROCESSED;
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact2);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact3);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact4);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact5);
            $marketingList->forgetAll();
            $marketingList      = MarketingList::getById($marketingListId);

            $campaign           = CampaignTestHelper::createCampaign('campaign 04',
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
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId($processed, $campaign->id);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(5, $campaignItems);

            // TODO: @Shoaibi: Medium: Add tests for the other campaign type.
        }
    }
?>