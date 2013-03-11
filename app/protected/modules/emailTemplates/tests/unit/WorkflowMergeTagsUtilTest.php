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
    class WorkflowMergeTagsUtilTest extends ZurmoBaseTest
    {
        protected static $emailTemplateWithMergeTags;

        protected static $contact;

        protected static $super;

        protected $invalidTags;

        protected $contactTextMergeTagsUtil;

        protected $contactHtmlMergeTagsUtil;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            self::$super = User::getByUsername('super');
            Yii::app()->user->userModel = self::$super;
            self::$emailTemplateWithMergeTags = EmailTemplateTestHelper::createEmailTemplateByName(
                                                    EmailTemplate::TYPE_WORKFLOW, 'Subject 01', 'Contact', 'Name 01',
                                                    '<b>You/Current: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        ' You/Old: [[WAS%ID]]/[[WAS%FIRST^NAME]] [[WAS%LAST^NAME]] => [[WAS%INDUSTRY]].'. PHP_EOL .
                                                        ' Owner/Current: [[__OWNER__ID]]/[[__OWNER__USERNAME]] => [[__OWNER__TIME^ZONE]]'. PHP_EOL .
                                                        ' Owner/Old: [[WAS%__OWNER__ID]]/[[WAS%__OWNER__USERNAME]] => [[WAS%__OWNER__TIME^ZONE]]</b>',
                                                    'You/Current: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        ' You/Old: [[WAS%ID]]/[[WAS%FIRST^NAME]] [[WAS%LAST^NAME]] => [[WAS%INDUSTRY]].'. PHP_EOL .
                                                        ' Owner/Current: [[__OWNER__ID]]/[[__OWNER__USERNAME]] => [[__OWNER__TIME^ZONE]]'. PHP_EOL .
                                                        ' Owner/Old: [[WAS%__OWNER__ID]]/[[WAS%__OWNER__USERNAME]] => [[WAS%__OWNER__TIME^ZONE]]');
            self::$contact = ContactTestHelper::createContactByNameForOwner('James', self::$super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel     = self::$super;
            self::$contact->firstName       = "New First Name";
            self::$contact->lastName        = "New last Name";
            self::$contact->owner->username = "new_username";
            $this->contactTextMergeTagsUtil = MergeTagsUtilFactory::make(self::$emailTemplateWithMergeTags->type,
                                                                        self::$emailTemplateWithMergeTags->language,
                                                                        self::$emailTemplateWithMergeTags->textContent);
            $this->contactHtmlMergeTagsUtil = MergeTagsUtilFactory::make(self::$emailTemplateWithMergeTags->type,
                                                                        self::$emailTemplateWithMergeTags->language,
                                                                        self::$emailTemplateWithMergeTags->htmlContent);
            $this->invalidTags = array();
        }


        public function testCanInstantiateWorkflowMergeTags()
        {
            $this->assertTrue($this->contactTextMergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($this->contactHtmlMergeTagsUtil instanceof MergeTagsUtil);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithCustomLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, 'fr');
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithNoLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithDefaultLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags);
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithCustomLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, 'it');
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithNoLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        /*
         * @depends testCanInstantiateWorkflowMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithDefaultLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags);
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        public function testSucceedsWhenDataHasNoMergeTags()
        {
            $emailTemplateWithNoMergeTags = EmailTemplateTestHelper::createEmailTemplateByName(
                EmailTemplate::TYPE_WORKFLOW, 'Subject 02', 'Account', 'Name 02',
                '<b>HTML Content without tags</b>',
                'Text Content without tags');
            $workflowMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplateWithNoMergeTags->type,
                $emailTemplateWithNoMergeTags->language,
                $emailTemplateWithNoMergeTags->textContent);
            $this->assertTrue($workflowMergeTagsUtil instanceof MergeTagsUtil);
            $textContent = $workflowMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($textContent !== false);
            $this->assertEquals($textContent, $emailTemplateWithNoMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        public function testFailsOnInvalidMergeTags()
        {
            $emailTemplate = EmailTemplateTestHelper::createEmailTemplateByName(
                EmailTemplate::TYPE_WORKFLOW, 'Subject 02', 'Account', 'Name 02',
                '<b>HTML Content with [[INVALID^TAG]] tags</b>',
                'Text Content with [[INVALIDTAG]] tags');
            $workflowMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplate->type,
                $emailTemplate->language,
                $emailTemplate->textContent);
            $this->assertTrue($workflowMergeTagsUtil instanceof MergeTagsUtil);
            $textContent = $workflowMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertFalse($textContent);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertEquals(1, count($this->invalidTags));
            $this->assertTrue($this->invalidTags[0] == 'INVALIDTAG');
            $this->invalidTags = array();
            $workflowMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplate->type,
                $emailTemplate->language,
                $emailTemplate->htmlContent);
            $this->assertTrue($workflowMergeTagsUtil instanceof MergeTagsUtil);
            $htmlContent = $workflowMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertFalse($htmlContent);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertEquals(1, count($this->invalidTags));
            $this->assertTrue($this->invalidTags[0] == 'INVALID^TAG');
        }
    }
?>