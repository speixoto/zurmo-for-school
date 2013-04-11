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
    class ContactMergeTagsUtilTest extends ZurmoBaseTest
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
                                                    EmailTemplate::TYPE_CONTACT, 'Subject 01', 'Contact', 'Name 01',
                                                    '<b>You: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        'Duplicate You: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        ' Owner: [[__OWNER__ID]]/[[__OWNER__USERNAME]] => [[__OWNER__TIME^ZONE]]</b>',
                                                    'You: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        ' Duplicate You: [[ID]]/[[FIRST^NAME]] [[LAST^NAME]] => [[INDUSTRY]].'. PHP_EOL .
                                                        ' Owner: [[__OWNER__ID]]/[[__OWNER__USERNAME]] => [[__OWNER__TIME^ZONE]]');
            self::$contact = ContactTestHelper::createContactByNameForOwner('James', self::$super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = self::$super;
            $this->contactTextMergeTagsUtil = MergeTagsUtilFactory::make(self::$emailTemplateWithMergeTags->type,
                                                                        self::$emailTemplateWithMergeTags->language,
                                                                        self::$emailTemplateWithMergeTags->textContent);
            $this->contactHtmlMergeTagsUtil = MergeTagsUtilFactory::make(self::$emailTemplateWithMergeTags->type,
                                                                        self::$emailTemplateWithMergeTags->language,
                                                                        self::$emailTemplateWithMergeTags->htmlContent);
            $this->invalidTags = array();
        }

        public function testCanInstantiateContactMergeTags()
        {
            $this->assertTrue($this->contactTextMergeTagsUtil instanceof MergeTagsUtil);
            $this->assertTrue($this->contactHtmlMergeTagsUtil instanceof MergeTagsUtil);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithCustomLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, 'fr');
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithNoLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testTextMergeFieldsArePopulatedCorrectlyWithDefaultLanguage()
        {
            $textContent = $this->contactTextMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags);
            $this->assertTrue($textContent !== false);
            $this->assertNotEquals($textContent, self::$emailTemplateWithMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithCustomLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, 'it');
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithNoLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testCanInstantiateContactMergeTags
         */
        public function testHtmlMergeFieldsArePopulatedCorrectlyWithDefaultLanguage()
        {
            $htmlContent = $this->contactHtmlMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags);
            $this->assertTrue($htmlContent !== false);
            $this->assertNotEquals($htmlContent, self::$emailTemplateWithMergeTags->htmlContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testHtmlMergeFieldsArePopulatedCorrectlyWithDefaultLanguage
         */
        public function testSucceedsWhenDataHasNoMergeTags()
        {
            $emailTemplateWithNoMergeTags = EmailTemplateTestHelper::createEmailTemplateByName(
                                                        EmailTemplate::TYPE_CONTACT, 'Subject 02', 'Contact', 'Name 02',
                                                        '<b>HTML Content without tags</b>',
                                                        'Text Content without tags');
            $contactMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplateWithNoMergeTags->type,
                                                        $emailTemplateWithNoMergeTags->language,
                                                        $emailTemplateWithNoMergeTags->textContent);
            $this->assertTrue($contactMergeTagsUtil instanceof MergeTagsUtil);
            $textContent = $contactMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertTrue($textContent !== false);
            $this->assertEquals($textContent, $emailTemplateWithNoMergeTags->textContent);
            $this->assertEmpty($this->invalidTags);
        }

        /**
         * @depends testSucceedsWhenDataHasNoMergeTags
         */
        public function testFailsOnInvalidMergeTags()
        {
            $emailTemplate = EmailTemplateTestHelper::fillEmailTemplateByName(
                EmailTemplate::TYPE_CONTACT, 'Subject 02', 'Contact', 'Name 02',
                '<b>HTML Content with [[INVALID^TAG]] tags</b>',
                'Text Content with [[INVALIDTAG]] tags');
            $contactMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplate->type,
                $emailTemplate->language,
                $emailTemplate->textContent);
            $this->assertTrue($contactMergeTagsUtil instanceof MergeTagsUtil);
            $textContent = $contactMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertFalse($textContent);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertEquals(1, count($this->invalidTags));
            $this->assertTrue($this->invalidTags[0] == 'INVALIDTAG');
            $this->invalidTags = array();
            $contactMergeTagsUtil = MergeTagsUtilFactory::make($emailTemplate->type,
                $emailTemplate->language,
                $emailTemplate->htmlContent);
            $this->assertTrue($contactMergeTagsUtil instanceof MergeTagsUtil);
            $htmlContent = $contactMergeTagsUtil->resolveMergeTags(self::$contact, $this->invalidTags, null);
            $this->assertFalse($htmlContent);
            $this->assertNotEmpty($this->invalidTags);
            $this->assertEquals(1, count($this->invalidTags));
            $this->assertTrue($this->invalidTags[0] == 'INVALID^TAG');
        }

        /**
         * @depends testFailsOnInvalidMergeTags
         */
        public function testTextMergeTag()
        {
            $content                = 'some subject [[STRING]] and something else';
            $model                  = new EmailTemplateModelTestItem();
            $model->string          = 'abc';
            $contactMergeTagsUtil   = MergeTagsUtilFactory::make(1, null, $content);
            $resolvedContent        = $contactMergeTagsUtil->resolveMergeTags($model, $this->invalidTags);
            $compareContent         = 'some subject abc and something else';
            $this->assertEquals($compareContent, $resolvedContent);
        }

        /**
         * @depends testTextMergeTag
         */
        public function testPhoneMergeTag()
        {
            $content                = 'some subject [[PHONE]] and something else';
            $model                  = new EmailTemplateModelTestItem();
            $model->phone           = 'myPhone';
            $contactMergeTagsUtil   = MergeTagsUtilFactory::make(1, null, $content);
            $resolvedContent        = $contactMergeTagsUtil->resolveMergeTags($model, $this->invalidTags);
            $compareContent         = 'some subject myPhone and something else';
            $this->assertEquals($compareContent, $resolvedContent);
        }

        //TODO WorkflowActionAttributeFormResolveValueTest - use setupBeforeClass from this.
    }
?>