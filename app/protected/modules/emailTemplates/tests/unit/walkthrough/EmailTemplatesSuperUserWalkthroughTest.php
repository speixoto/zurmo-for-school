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

    /**
     * EmailTemplates Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class EmailTemplatesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $maker  = new EmailTemplatesDefaultDataMaker();
            $maker->make();
            $demoDataHelper = new DemoDataHelper();
            $demoDataHelper->setRangeByModelName('User', 1, 10);
            $groupsDemoDataMaker = new GroupsDemoDataMaker();
            $groupsDemoDataMaker->makeAll($demoDataHelper);
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername($this->getTestUsername());
        }

        public function testAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            // This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');

            // Setup test data owned by the super user.
            EmailTemplateTestHelper::create('Test Name', 'Test Subject', 'Contact', 'Text HtmlContent',
                                            'Test TextContent', EmailTemplate::TYPE_WORKFLOW);
            EmailTemplateTestHelper::create('Test Name1', 'Test Subject1', 'Contact', 'Text HtmlContent1',
                                            'Test TextContent1', EmailTemplate::TYPE_CONTACT);

            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT,
                                     'builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
        }

        /**
         * @depends testAllDefaultControllerActions
         */
        public function testRelationsAndAttributesTreeForMergeTags()
        {
            //Test without a node id
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/relationsAndAttributesTreeForMergeTags');

            //Test with a node id
            $this->setGetArray (array('uniqueId' => 'EmailTemplate', 'nodeId' => 'EmailTemplate_secondaryAddress'));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/relationsAndAttributesTreeForMergeTags');
        }

        /**
         * @depends testRelationsAndAttributesTreeForMergeTags
         */
        public function testListForMarketingAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name1'), 1);
            $this->assertEquals (substr_count($content, strval($this->user)), 2);
            $this->assertEquals (substr_count($content, '<td>HTML</td>'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $this->assertEquals (1, count($emailTemplates));
        }

        /**
         * @depends testListForMarketingAction
         */
        public function testListForWorkflowAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name'), 1);
            $this->assertEquals (substr_count($content, strval($this->user)), 2);
            $this->assertEquals (substr_count($content, '<td>HTML</td>'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_WORKFLOW);
            $this->assertEquals (1, count($emailTemplates));
        }

        public function testSelectBuiltTypeAction()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/selectBuiltType');
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">'.
                                                'Email Template Wizard</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<ul class="configuration-list creation-list">') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Plain Text</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=1">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>HTML</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=2">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Template Builder</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=3">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li></ul>') !== false);
        }

        /**
         * @depends testSelectBuiltTypeAction
         */
        public function testCreateWithoutBuiltTypeAction()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">'.
                                                'Email Template Wizard</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<ul class="configuration-list creation-list">') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Plain Text</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=1">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>HTML</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=2">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Template Builder</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=3">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li></ul>') !== false);
        }

        public function testMergeTagGuideAction()
        {
            $content    = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/mergeTagGuide');
            $this->assertTrue(strpos($content, '<div id="ModalView"><div id="MergeTagGuideView">') !== false);
            $this->assertTrue(strpos($content, '<div id="mergetag-guide-modal-content" class="mergetag-guide-modal">') !== false);
            $this->assertTrue(strpos($content, 'Merge tags are a quick way to introduce reader-specific dynamic '.
                                                'information into emails.') !== false);
            $this->assertTrue(strpos($content, '<div id="mergetag-syntax"><div id="mergetag-syntax-head">'.
                                                '<h4>Syntax</h4></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="mergetag-syntax-body"><ul>') !== false);
            $this->assertTrue(strpos($content, '<li>A merge tag starts with: [[ and ends with ]].</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Between starting and closing tags it can have field names. These ' .
                                                'names are written in all caps regardless of actual field name' .
                                                ' case.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>Fields that contain more than one word are named using camel case' .
                                                ' in the system and to address that in merge tags, use the prefix ^ ' .
                                                'before the letter that should be capitalize when ' .
                                                'converted.</li>') !== false);
            $this->assertTrue(strpos($content, '<li>To access a related field, use the following prefix:' .
                                                ' __</li>') !== false);
            $this->assertTrue(strpos($content, '<li>To access a previous value of a field (only supported in workflow' .
                                                ' type templates) prefix the field name with: WAS%. If there is no ' .
                                                'previous value, the current value will be used. If the attached ' .
                                                'module does not support storing previous values an error will be ' .
                                                'thrown when saving the template.</li>') !== false);
            $this->assertTrue(strpos($content, '</ul></div></div><div id="mergetag-examples"><div id="mergetag-' .
                                                'examples-head">') !== false);
            $this->assertTrue(strpos($content, '<h4>Examples</h4></div><div id="mergetag-examples-body">') !== false);
            $this->assertTrue(strpos($content, '<ul><li>Adding a contact\'s First Name (firstName): <strong>' .
                                                '[[FIRST^NAME]]</strong></li>') !== false);
            $this->assertTrue(strpos($content, '<li>Adding a contact\'s city (primaryAddress->city): <strong>' .
                                                '[[PRIMARY^ADDRESS__CITY]]</strong></li>') !== false);
            $this->assertTrue(strpos($content, '<li>Adding a user\'s previous primary email address: <strong>' .
                                                '[[WAS%PRIMARY^EMAIL__EMAIL^ADDRESS]]</strong></li>') !== false);
            $this->assertTrue(strpos($content, '</ul></div></div><div id="mergetag-special-tags"><div id="mergetag' .
                                                '-special-tags-head">') !== false);
            $this->assertTrue(strpos($content, '<h4>Special Tags</h4></div><div id="mergetag-special-tags-body">') !== false);
            $this->assertTrue(strpos($content, '<ul><li><strong>[[MODEL^URL]]</strong> : prints absolute url to the ' .
                                                'current model attached to template.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[BASE^URL]]</strong> : prints absolute url to the current' .
                                                ' install without trailing slash.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[APPLICATION^NAME]]</strong> : prints application name' .
                                                ' as set in global settings > application name.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[CURRENT^YEAR]]</strong> : prints current year.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[LAST^YEAR]]</strong> : prints last year.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[OWNERS^AVATAR^SMALL]]</strong> : prints the owner\'s ' .
                                                'small avatar image (32x32).</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[OWNERS^AVATAR^MEDIUM ]]</strong> : prints the owner\'s ' .
                                                'medium avatar image (64x64).</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[OWNERS^AVATAR^LARGE]]</strong> : prints the owner\'s ' .
                                                'large avatar image (128x128).</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[OWNERS^EMAIL^SIGNATURE]]</strong> : prints the owner\'s' .
                                                ' email signature.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[GLOBAL^MARKETING^FOOTER^PLAIN^TEXT]]</strong> : prints ' .
                                                'the Global Marketing Footer(Plain Text).</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>[[GLOBAL^MARKETING^FOOTER^HTML]]</strong> : prints the ' .
                                                'Global Marketing Footer(Rich Text).</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>' . GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . '</strong> : prints unsubscribe' .
                                                ' url.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>' . GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag() . '</strong> : prints manage' .
                                                ' subscriptions url.</li>') !== false);
        }

        public function testGetHtmlContentActionForPredefined()
        {
            $emailTemplateId    = 2;
            $this->setGetArray(array('id' => $emailTemplateId, 'className' => 'EmailTemplate'));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent', true);
        }

        /**
         * @depends testGetHtmlContentActionForPredefined
         */
        public function testGetHtmlContentActionForPlainText()
        {
            // create a plain text template, returned content should be empty
            $emailTemplate  = EmailTemplateTestHelper::create('plainText 01', 'plainText 01', 'Contact', null, 'text',
                                                                            EmailTemplate::TYPE_CONTACT, 0,
                                                                            EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY);
            $this->setGetArray(array('id' => $emailTemplate->id, 'className' => get_class($emailTemplate)));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent', true);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         */
        public function testGetHtmlContentActionForHtml()
        {
            // create html template, we should get same content in return
            $emailTemplate  = EmailTemplateTestHelper::create('html 01', 'html 01', 'Contact', 'html', null,
                                                                                EmailTemplate::TYPE_CONTACT, 0,
                                                                                EmailTemplate::BUILT_TYPE_PASTED_HTML);
            $this->setGetArray(array('id' => $emailTemplate->id, 'className' => get_class($emailTemplate)));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent');
            $this->assertEquals('html', $content);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         */
        public function testGetHtmlContentActionForBuilder()
        {
            // create a builder template, returned content should have some basic string patterns.
            $emailTemplateId        = 2;
            $predefinedTemplate     = EmailTemplate::getById($emailTemplateId);
            $unserializedData       = CJSON::decode($predefinedTemplate->serializedData);
            $unserializedData['baseTemplateId']   = $predefinedTemplate->id;
            $expectedHtmlContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByUnserializedData($unserializedData);
            $serializedData         = CJSON::encode($unserializedData);
            $emailTemplate          = EmailTemplateTestHelper::create('builder 01', 'builder 01', 'Contact', null, null,
                                                                                EmailTemplate::TYPE_CONTACT, 0,
                                                                                EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE,
                                                                                $serializedData);
            $this->setGetArray(array('id' => $emailTemplate->id, 'className' => get_class($emailTemplate)));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent');
            $this->assertEquals($expectedHtmlContent, $content);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         * @depends testGetHtmlContentActionForBuilder
         */
        public function testGetSerializedToHtmlContentForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent', true);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         * @depends testGetSerializedToHtmlContentForPlainText
         */
        public function testGetSerializedToHtmlContentForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent', true);
        }

        /**
         * @depends testGetHtmlContentActionForBuilder
         * @depends testGetSerializedToHtmlContentForHtml
         */
        public function testGetSerializedToHtmlContentForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testGetSerializedToHtmlContentForBuilder
         */
        public function testGetSerializedToHtmlContentForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent');
            $this->assertEquals($expectedContent, $content);
        }

        public function testRenderCanvasWithoutId()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         * @depends testRenderCanvasWithoutId
         */
        public function testRenderCanvasForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         * @depends testRenderCanvasForPlainText
         */
        public function testRenderCanvasForForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testGetHtmlContentActionForBuilder
         * @depends testRenderCanvasForForHtml
         */
        public function testRenderCanvasForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId, true);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderCanvasForBuilder
         */
        public function testRenderCanvasForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId, true);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas');
            $this->assertEquals($expectedContent, $content);
        }

        public function testRenderPreviewWithoutId()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         * @depends testRenderPreviewWithoutId
         */
        public function testRenderPreviewForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         * @depends testRenderPreviewForPlainText
         */
        public function testRenderPreviewForForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testGetHtmlContentActionForBuilder
         * @depends testRenderPreviewForForHtml
         */
        public function testRenderPreviewForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderPreviewForBuilder
         */
        public function testRenderPreviewForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderPreviewForPredefined
         */
        public function testRenderPreviewWithPost()
        {
            $emailTemplate      = EmailTemplate::getById(2);
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate);
            $this->setPostArray(array('serializedData' => $emailTemplate->serializedData));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderPreview');
            $this->assertEquals($expectedContent, $content);
        }

        public function testConvertEmailWithoutConverter()
        {
            $emailTemplate      = EmailTemplate::getById(2);
            $expectedContent    = ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate);
            $this->setGetArray(array('id' => $emailTemplate->id));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/convertEmail');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         * @depends testConvertEmailWithoutConverter
         */
        public function testConvertEmailForPlainText()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            // @ to avoid file_get_contents(): Filename cannot be empty
            $expectedContent    = @ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate, 'cssin');
            $this->setGetArray(array('id' => $emailTemplate->id, 'converter' => 'cssin'));
            // @ to avoid file_get_contents(): Filename cannot be empty
            $content            = @$this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/convertEmail');
            // these won't be empty due to an html comment we append to converted output.
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         * @depends testConvertEmailForPlainText
         */
        public function testConvertEmailForForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $expectedContent    = ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate, 'cssin');
            $this->setGetArray(array('id' => $emailTemplate->id, 'converter' => 'cssin'));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/convertEmail');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testGetHtmlContentActionForBuilder
         * @depends testConvertEmailForForHtml
         */
        public function testConvertEmailForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $expectedContent    = ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate, 'cssin');
            $this->setGetArray(array('id' => $emailTemplate->id, 'converter' => 'cssin'));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/convertEmail');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testConvertEmailForBuilder
         */
        public function testConvertEmailForPredefined()
        {
            $emailTemplateId    = 2;
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $expectedContent    = ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate, 'cssin');
            $this->setGetArray(array('id' => $emailTemplate->id, 'converter' => 'cssin'));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/convertEmail');
            $this->assertEquals($expectedContent, $content);
        }

        public function testRenderElementNonEditableWithGet()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderElementNonEditable', true);
        }

        /**
         * @depends testRenderElementNonEditableWithGet
         */
        public function testRenderElementNonEditableWithoutClassName()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $this->setPostArray(array($formClassName => array()));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderElementNonEditable', true);
        }

        /**
         * @depends testRenderElementNonEditableWithoutClassName
         */
        public function testRenderElementNonEditableWithClassName()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = null;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                            $wrapElementInRow, $id,
                                                                            $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas,
                                        'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                'emailTemplates/default/renderElementNonEditable');
            // because we don't send id we would have different ids in both content, lets get rid of those.
            static::sanitizeStringOfIdAttribute($content);
            static::sanitizeStringOfIdAttribute($expectedContent);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementNonEditableWithClassName
         */
        public function testRenderElementNonEditableWithClassNameAndIdForCanvasWithoutRowWrapper()
        {
            // we have to send id so at both times element is init using same id.
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                                    $wrapElementInRow, $id,
                                                                                    $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                    'renderForCanvas'   => $renderForCanvas,
                                    'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'emailTemplates/default/renderElementNonEditable');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementNonEditableWithClassNameAndIdForCanvasWithoutRowWrapper
         */
        public function testRenderElementNonEditableWithClassNameAndIdForCanvasWithNormalRowWrapper()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::WRAP_IN_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                                $wrapElementInRow, $id,
                                                                                $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas,
                                        'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'emailTemplates/default/renderElementNonEditable');
            // because we can't send id for wrapping row and column we would have different
            // ids in both content, lets get rid of those.
            static::sanitizeStringOfIdAttribute($content);
            static::sanitizeStringOfIdAttribute($expectedContent);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementNonEditableWithClassNameAndIdForCanvasWithNormalRowWrapper
         */
        public function testRenderElementNonEditableWithClassNameAndIdForCanvasWithHeaderRowWrapper()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::WRAP_IN_HEADER_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                                $wrapElementInRow, $id,
                                                                                $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                    'renderForCanvas'   => $renderForCanvas,
                                    'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'emailTemplates/default/renderElementNonEditable');
            // we need following because header row has 1:2 configuration and
            // we don't have the option to supply columnId for second column.
            static::sanitizeStringOfIdAttribute($content);
            static::sanitizeStringOfIdAttribute($expectedContent);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementNonEditableWithClassNameAndIdForCanvasWithHeaderRowWrapper
         */
        public function testRenderElementNonEditableWithClassNameAndIdAndContentForCanvas()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $content            = array('text' => 'dummyContent');
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                                $wrapElementInRow, $id,
                                                                                $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                    'renderForCanvas'   => $renderForCanvas,
                                    'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'emailTemplates/default/renderElementNonEditable');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementNonEditableWithClassNameAndIdAndContentForCanvas
         */
        public function testRenderElementNonEditableWithClassNameAndIdAndContentAndPropertiesForCanvas()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $content            = array('text' => 'dummyContent');
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = array(
                                                'frontend'      => array('inlineStyles'  => array('color' => '#cccccc')),
                                                'backend'       => array('headingLevel'  => 'h3'));
            $params             = null;
            $wrapElementInRow   = BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW;
            $expectedContent    = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas,
                                                                                $wrapElementInRow, $id,
                                                                                $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                    'renderForCanvas'   => $renderForCanvas,
                                    'wrapElementInRow'  => $wrapElementInRow));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                    'emailTemplates/default/renderElementNonEditable');
            $this->assertEquals($expectedContent, $content);
        }

        public function testRenderElementEditableWithGet()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderElementEditable', true);
        }

        /**
         * @depends testRenderElementEditableWithGet
         */
        public function testRenderElementEditableWithoutClassName()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $this->setPostArray(array($formClassName => array()));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderElementEditable', true);
        }

        /**
         * @depends testRenderElementEditableWithoutClassName
         */
        public function testRenderElementEditableWithClassName()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = null;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $expectedContent    = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id,
                                                                            $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                        'emailTemplates/default/renderElementEditable');
            // we don't set id so we would have to get rid of it from contents
            static::sanitizeStringOfIdAttribute($content);
            static::sanitizeStringOfIdAttribute($expectedContent);
            // need to get rid of script from the content controller returned as we don't get that when using util
            static::sanitizeStringOfScript($content);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementEditableWithClassName
         */
        public function testRenderElementEditableWithClassNameAndIdForCanvas()
        {
            // we have to send id so at both times element is init using same id.
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $content            = null;
            $params             = null;
            $expectedContent    = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id,
                                                                            $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                                'content'           => $content,
                                                                                'properties'        => $properties,
                                                                                'params'            => $params,
                                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                        'emailTemplates/default/renderElementEditable');
            // need to get rid of script from the content controller returned as we don't get that when using util
            static::sanitizeStringOfScript($content);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementEditableWithClassNameAndIdForCanvas
         */
        public function testRenderElementEditableWithClassNameAndIdAndContentForCanvas()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $content            = array('text' => 'dummyContent');
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = null;
            $params             = null;
            $expectedContent    = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id,
                                                                            $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                        'emailTemplates/default/renderElementEditable');
            // need to get rid of script from the content controller returned as we don't get that when using util
            static::sanitizeStringOfScript($content);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testRenderElementEditableWithClassNameAndIdAndContentForCanvas
         */
        public function testRenderElementEditableWithClassNameAndIdAndContentAndPropertiesForCanvas()
        {
            $formClassName      = BaseBuilderElement::getModelClassName();
            $className          = 'BuilderTitleElement';
            $content            = array('text' => 'dummyContent');
            $id                 = __FUNCTION__ . __LINE__;
            $renderForCanvas    = true;
            $properties         = array(
                'frontend'      => array('inlineStyles'  => array('color' => '#cccccc')),
                'backend'       => array('headingLevel'  => 'h3'));
            $params             = null;
            $expectedContent    = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id,
                                                                            $properties, $content, $params);
            $this->setPostArray(array($formClassName => array(  'className'         => $className,
                                                                'content'           => $content,
                                                                'properties'        => $properties,
                                                                'params'            => $params,
                                                                'id'                => $id),
                                        'renderForCanvas'   => $renderForCanvas));
            $content            = $this->runControllerWithNoExceptionsAndGetContent(
                                                                        'emailTemplates/default/renderElementEditable');
            // need to get rid of script from the content controller returned as we don't get that when using util
            static::sanitizeStringOfScript($content);
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testGetHtmlContentActionForPlainText
         */
        public function testDetailsJsonActionForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);
            $this->setGetArray(array('id' => $emailTemplateId, 'renderJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
        }

        /**
         * @depends testGetHtmlContentActionForHtml
         */
        public function testDetailsJsonActionForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);
            $this->setGetArray(array('id' => $emailTemplateId, 'renderJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
        }

        /**
         * @depends testGetHtmlContentActionForBuilder
         */
        public function testDetailsJsonActionForBuilder()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);
            unset($emailTemplateDetailsArray['serializedData']);
            $this->setGetArray(array('id' => $emailTemplateId, 'renderJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
        }

        /**
         * @depends testDetailsJsonActionForPlainText
         */
        public function testDetailsJsonActionForMarketing()
        {
            $emailTemplate  = EmailTemplateTestHelper::create('marketing 01', 'marketing 01', 'Contact', 'html', 'text');
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);
            $this->setGetArray(array('id' => $emailTemplate->id, 'renderJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
        }

        /**
         * @depends testDetailsJsonActionForMarketing
         */
        public function testDetailsJsonActionForMarketingWithFiles()
        {
            $emailTemplateId        = self::getModelIdByModelNameAndName ('EmailTemplate', 'marketing 01');
            $emailTemplate          = EmailTemplate::getById($emailTemplateId);
            // attach some files
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            foreach ($fileNames as $fileName)
            {
                $emailTemplate->files->add(ZurmoTestHelper::createFileModel($fileName));
            }
            $emailTemplate->save();
            $emailTemplate->forgetAll();
            unset($emailTemplate);
            $emailTemplate          = EmailTemplate::getById($emailTemplateId);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);

            $this->setGetArray(array('id' => $emailTemplateId, 'renderJson' => true, 'includeFilesInJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $emailTemplateDetailsResolvedArrayWithoutFiles = $emailTemplateDetailsResolvedArray;
            unset($emailTemplateDetailsResolvedArrayWithoutFiles['filesIds']);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertNotEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArrayWithoutFiles);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray['filesIds']);
            $this->assertEquals($emailTemplate->files->count(), count($emailTemplateDetailsResolvedArray['filesIds']));
            foreach ($emailTemplate->files as $index => $file)
            {
                $this->assertEquals($file->id, $emailTemplateDetailsResolvedArray['filesIds'][$index]);
            }
        }

        /**
         * @depends testDetailsJsonActionForMarketing
         */
        public function testDetailsActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'marketing 01');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab2">') !== false);
        }

        /**
         * @depends testDetailsJsonActionForPlainText
         */
        public function testDetailsJsonActionForWorkflow()
        {
            $emailTemplate  = EmailTemplateTestHelper::create('workflow 01', 'workflow 01', 'Note', 'html',
                                                                    'text', EmailTemplate::TYPE_WORKFLOW);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);
            $this->setGetArray(array('id' => $emailTemplate->id, 'renderJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
        }

        /**
         * @depends testDetailsJsonActionForWorkflow
         */
        public function testDetailsJsonActionForWorkflowWithFiles()
        {
            $emailTemplateId        = self::getModelIdByModelNameAndName ('EmailTemplate', 'workflow 01');
            $emailTemplate          = EmailTemplate::getById($emailTemplateId);
            // attach some files
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            foreach ($fileNames as $fileName)
            {
                $emailTemplate->files->add(ZurmoTestHelper::createFileModel($fileName));
            }
            $emailTemplate->save();
            $emailTemplate->forgetAll();
            unset($emailTemplate);
            $emailTemplate          = EmailTemplate::getById($emailTemplateId);
            $emailTemplateDataUtil = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateDetailsArray = $emailTemplateDataUtil->getData();
            $this->assertNotEmpty($emailTemplateDetailsArray);

            $this->setGetArray(array('id' => $emailTemplateId, 'renderJson' => true, 'includeFilesInJson' => true));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $emailTemplateDetailsResolvedArrayWithoutFiles = $emailTemplateDetailsResolvedArray;
            unset($emailTemplateDetailsResolvedArrayWithoutFiles['filesIds']);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertNotEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArray);
            $this->assertEquals($emailTemplateDetailsArray, $emailTemplateDetailsResolvedArrayWithoutFiles);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray['filesIds']);
            $this->assertEquals($emailTemplate->files->count(), count($emailTemplateDetailsResolvedArray['filesIds']));
            foreach ($emailTemplate->files as $index => $file)
            {
                $this->assertEquals($file->id, $emailTemplateDetailsResolvedArray['filesIds'][$index]);
            }
        }

        /**
         * @depends testDetailsJsonActionForWorkflow
         */
        public function testDetailsActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'workflow 01');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab2">') !== false);
        }

        /**
         * @depends testDetailsJsonActionForWorkflow
         */
        public function testDetailsJsonActionWithMergeTagResolution()
        {
            $contact         = ContactTestHelper::createContactByNameForOwner('test', $this->user);
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'marketing 01');
            $emailTemplate   = EmailTemplate::getById($emailTemplateId);
            $unsubscribePlaceholder         = GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag();
            $manageSubscriptionsPlaceholder = GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag();
            $emailTemplate->textContent = "Test text content with contact tag: [[FIRST^NAME]] {$unsubscribePlaceholder}";
            $emailTemplate->htmlContent = "Test html content with contact tag: [[FIRST^NAME]] {$manageSubscriptionsPlaceholder}";
            $this->assertTrue($emailTemplate->save());
            $this->setGetArray(array('id'                 => $emailTemplateId,
                'renderJson'         => true,
                'includeFilesInJson' => false,
                'contactId'          => $contact->id));
            // @ to avoid headers already sent error.
            $content = @$this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/detailsJson');
            $emailTemplateDetailsResolvedArray = CJSON::decode($content);
            $this->assertNotEmpty($emailTemplateDetailsResolvedArray);
            $this->assertEquals('Test text content with contact tag: test ', $emailTemplateDetailsResolvedArray['textContent']);
            $this->assertEquals('Test html content with contact tag: test ', $emailTemplateDetailsResolvedArray['htmlContent']);
        }

        public function testCreateActionForPlainTextAndMarketing()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT,
                                        'builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, '<div id="MarketingBreadCrumbView" class="BreadCrumbView">' .
                                                '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/marketing/default/index">Marketing</a>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/listForMarketing">Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<span>Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ClassicEmailTemplateStepsAndProgressBarForWizardView" ' .
                                                'class="StepsAndProgressBarForWizardView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="progress"><div class="progress-back"><div class="progress' .
                                                '-bar" style="width:50%; margin-left:0%"></div></div>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:50%" class="current-step">General</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:50%">Content</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ClassicEmailTemplateWizardView" class="' .
                                                'EmailTemplateWizardView WizardView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                                'Email Template Wizard - Plain Text</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/save?builtType=1" method="post">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<input id="componentType" type="hidden" value=' .
                                                '"ValidateForGeneralData" name="validationScenario"') !== false);
            $this->assertTrue(strpos($content, '<div class="GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="GeneralDataForEmailTemplateWizardView" class="ComponentFor' .
                                                'EmailTemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix">') !== false);
            $this->assertTrue(strpos($content, '<h3>General</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="edit-form_es_" class="errorSummary" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<p>Please fix the following input errors:</p>') !== false);
            $this->assertTrue(strpos($content, '<ul><li>dummy</li></ul></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">' .
                                                '<table class="form-fields">') !== false);
            $this->assertTrue(strpos($content, '<colgroup><col class="col-0"><col class="col-1"></colgroup>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="ClassicEmailTemplateWizardForm_name">' .
                                                'Name</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="ClassicEmailTemplateWizardForm_name"' .
                                                ' name="ClassicEmailTemplateWizardForm[name]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="ClassicEmailTemplateWizardForm_subject">' .
                                                'Subject</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="ClassicEmailTemplateWizardForm_' .
                                                'subject" name="ClassicEmailTemplateWizardForm[subject]"' .
                                                ' type="text" maxlength="255"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="dropzoneClassicEmailTemplateWizardForm"></div>' .
                                                '<div id="fileUploadClassicEmailTemplateWizardForm">') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-buttonbar clearfix"><div ' .
                                                'class="addfileinput-button">') !== false);
            $this->assertTrue(strpos($content, '<span>Y</span><strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_files" multiple="multiple" type="file"' .
                                                ' name="ClassicEmailTemplateWizardForm_files"') !== false);
            $this->assertTrue(strpos($content, '<span class="max-upload-size">') !== false);
            $this->assertTrue(strpos($content, '</div><div class="fileupload-content"><table class="files">') !== false);
            $this->assertTrue(strpos($content, '<tr><td colspan="2"><input id="ClassicEmailTemplateWizardForm_type"' .
                                                ' type="hidden" value="2" name="ClassicEmailTemplateWizard' .
                                                'Form[type]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_builtType" type="hidden"' .
                                                ' value="1" name="ClassicEmailTemplateWizardForm[builtType]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_isDraft" type="hidden" ' .
                                                'value="0" name="ClassicEmailTemplateWizardForm[isDraft]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_language" type="hidden" ' .
                                                'name="ClassicEmailTemplateWizardForm[language]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_hiddenId" type="hidden" ' .
                                                'value="0" name="ClassicEmailTemplateWizardForm[hiddenId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="modelClassNameForMergeTagsViewId" type="hidden" ' .
                                                'name="modelClassNameForMergeTagsViewId"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_modelClassName" ' .
                                                'type="hidden" value="Contact" name="ClassicEmailTemplate' .
                                                'WizardForm[modelClassName]"') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-side-edit-view-panel">') !== false);
            $this->assertTrue(strpos($content, '<h3>Rights and Permissions</h3><div id="owner-box">') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_ownerName"'.
                                                '>Owner Name</label>') !== false);
            $this->assertTrue(strpos($content, '<input name="ClassicEmailTemplateWizardForm[ownerId]" ' .
                                                'id="ClassicEmailTemplateWizardForm_ownerId" value="' .
                                                $this->user->id .'" type="hidden"') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<a id="ClassicEmailTemplateWizardForm_users_Select' .
                                                'Link" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="model-select-icon"></span><span ' .
                                                'class="z-spinner"></span></a>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizard' .
                                                'Form_ownerId_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<label>Who can read and write</label><div ' .
                                                'class="radio-input">') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0" value="" type="radio" name="ClassicEmailTemplate' .
                                                'WizardForm[explicitReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0">Owner</label>') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWrite' .
                                                'ModelPermissions_type_1" value="') !== false);
            $this->assertTrue(strpos($content, '" type="radio" name="ClassicEmailTemplateWizardForm[explicit' .
                                                'ReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel'.
                                                'Permissions_type_1">Owner and users in</label>') !== false);
            $this->assertTrue(strpos($content, '<select id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_nonEveryoneGroup" onclick="document.getElementById(' .
                                                '&quot;ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_1&quot;).checked=&quot;checked&quot;;" name="' . // Not Coding Standard
                                                'ClassicEmailTemplateWizardForm[explicitReadWriteModelPermissions]' .
                                                '[nonEveryoneGroup]"') !== false);
            $this->assertContentHasDemoGroupNameOptionTags($content);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2" value="') !== false);
            $this->assertTrue(strpos($content, 'type="radio" name="ClassicEmailTemplateWizardForm[explicitReadWrite' .
                                                'ModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2">Everyone</label>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertEquals(2, substr_count($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                            'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Cancel</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataNextLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span><span ' .
                                                'class="z-label">Next</span></a></div></div></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ContentForEmailTemplateWizardView" class="ComponentForEmail' .
                                                'TemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix strong-right">' .
                                                '<h3>Content</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><h3>Merge Tags</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="MergeTagsView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MergeTagsTreeAreaEmailTemplate" class="hasTree' .
                                                ' loading"><span class="big-spinner"></span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="MergeTagGuideAjaxLinkActionElement-') !== false);
            $this->assertTrue(strpos($content, '" class="simple-link" href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class="active-tab tab email-template-' .
                                                'textContent">') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_textContent">' .
                                                'Text Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="ClassicEmailTemplateWizardForm_textContent" ' .
                                                'name="ClassicEmailTemplateWizardForm[textContent]"' .
                                                ' rows="6" cols="50"></textarea>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizardForm_' .
                                                'textContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizardForm_' .
                                                'htmlContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentFinishLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                'jQuery.yii.submitForm(this, &#039;&#039;, {&#039;save&#039;:&#039;' .
                                                'save&#039;}); return false;" href="#"><span class="z-spinner">' .
                                                '</span><span class="z-icon"></span><span class="z-label">Save</span>' .
                                                '</a></div></div></div></div></div></form>') !== false);
        }

        /**
         * @depends testCreateActionForPlainTextAndMarketing
         */
        public function testCreateActionForHtmlAndWorkflow()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_WORKFLOW,
                                    'builtType' => EmailTemplate::BUILT_TYPE_PASTED_HTML));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, '<div id="WorkflowBreadCrumbView" class="SettingsBreadCrumbView ' .
                                                'BreadCrumbView"><div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/workflows/default/index">Workflows</a>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/listForWorkflow">Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<span>Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ClassicEmailTemplateStepsAndProgressBarForWizardView" ' .
                                                'class="StepsAndProgressBarForWizardView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="progress"><div class="progress-back"><div class="progress' .
                                                '-bar" style="width:50%; margin-left:0%"></div></div>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:50%" class="current-step">General</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:50%">Content</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ClassicEmailTemplateWizardView" class="' .
                                                'EmailTemplateWizardView WizardView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                                'Email Template Wizard - HTML</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/save?builtType=2" method="post">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<input id="componentType" type="hidden" value=' .
                                                '"ValidateForGeneralData" name="validationScenario"') !== false);
            $this->assertTrue(strpos($content, '<div class="GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="GeneralDataForEmailTemplateWizardView" class="ComponentFor' .
                                                'EmailTemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix">') !== false);
            $this->assertTrue(strpos($content, '<h3>General</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="edit-form_es_" class="errorSummary" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<p>Please fix the following input errors:</p>') !== false);
            $this->assertTrue(strpos($content, '<ul><li>dummy</li></ul></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">' .
                                                '<table class="form-fields">') !== false);
            $this->assertTrue(strpos($content, '<colgroup><col class="col-0"><col class="col-1"></colgroup>') !== false);
            $this->assertTrue(strpos($content, '<tr><th>Module<span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<select name="ClassicEmailTemplateWizardForm[modelClassName]" ' .
                                                'id="ClassicEmailTemplateWizardForm_modelClassName_value">') !== false);
            $this->assertContentHasAllowedModuleOptionTags($content);
            $this->assertTrue(strpos($content, '<tr><th><label for="ClassicEmailTemplateWizardForm_name">' .
                                                'Name</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="ClassicEmailTemplateWizardForm_name"' .
                                                ' name="ClassicEmailTemplateWizardForm[name]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="ClassicEmailTemplateWizardForm_subject">' .
                                                'Subject</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="ClassicEmailTemplateWizardForm_' .
                                                'subject" name="ClassicEmailTemplateWizardForm[subject]"' .
                                                ' type="text" maxlength="255"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="dropzoneClassicEmailTemplateWizardForm"></div>' .
                                                '<div id="fileUploadClassicEmailTemplateWizardForm">') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-buttonbar clearfix"><div ' .
                                                'class="addfileinput-button">') !== false);
            $this->assertTrue(strpos($content, '<span>Y</span><strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_files" multiple="multiple" type="file"' .
                                                ' name="ClassicEmailTemplateWizardForm_files"') !== false);
            $this->assertTrue(strpos($content, '<span class="max-upload-size">') !== false);
            $this->assertTrue(strpos($content, '</div><div class="fileupload-content"><table class="files">') !== false);
            $this->assertTrue(strpos($content, '<tr><td colspan="2"><input id="ClassicEmailTemplateWizardForm_type"' .
                                                ' type="hidden" value="1" name="ClassicEmailTemplateWizard' .
                                                'Form[type]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_builtType" type="hidden"' .
                                                ' value="2" name="ClassicEmailTemplateWizardForm[builtType]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_isDraft" type="hidden" ' .
                                                'value="0" name="ClassicEmailTemplateWizardForm[isDraft]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_language" type="hidden" ' .
                                                'name="ClassicEmailTemplateWizardForm[language]"') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_hiddenId" type="hidden" ' .
                                                'value="0" name="ClassicEmailTemplateWizardForm[hiddenId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="modelClassNameForMergeTagsViewId" type="hidden" ' .
                                                'value="Account" name="modelClassNameForMergeTagsViewId"') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-side-edit-view-panel">') !== false);
            $this->assertTrue(strpos($content, '<h3>Rights and Permissions</h3><div id="owner-box">') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_ownerName"'.
                                                '>Owner Name</label>') !== false);
            $this->assertTrue(strpos($content, '<input name="ClassicEmailTemplateWizardForm[ownerId]" ' .
                                                'id="ClassicEmailTemplateWizardForm_ownerId" value="' .
                                                $this->user->id .'" type="hidden"') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<a id="ClassicEmailTemplateWizardForm_users_Select' .
                                                'Link" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="model-select-icon"></span><span ' .
                                                'class="z-spinner"></span></a>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizard' .
                                                'Form_ownerId_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<label>Who can read and write</label><div ' .
                                                'class="radio-input">') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0" value="" type="radio" name="ClassicEmailTemplate' .
                                                'WizardForm[explicitReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0">Owner</label>') !== false);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWrite' .
                                                'ModelPermissions_type_1" value="') !== false);
            $this->assertTrue(strpos($content, '" type="radio" name="ClassicEmailTemplateWizardForm[explicit' .
                                                'ReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel'.
                                                'Permissions_type_1">Owner and users in</label>') !== false);
            $this->assertTrue(strpos($content, '<select id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_nonEveryoneGroup" onclick="document.getElementById(' .
                                                '&quot;ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_1&quot;).checked=&quot;checked&quot;;" name="' . // Not Coding Standard
                                                'ClassicEmailTemplateWizardForm[explicitReadWriteModelPermissions]' .
                                                '[nonEveryoneGroup]"') !== false);
            $this->assertContentHasDemoGroupNameOptionTags($content);
            $this->assertTrue(strpos($content, '<input id="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2" value="') !== false);
            $this->assertTrue(strpos($content, 'type="radio" name="ClassicEmailTemplateWizardForm[explicitReadWrite' .
                                                'ModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2">Everyone</label>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertEquals(2, substr_count($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                            'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Cancel</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataNextLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span><span ' .
                                                'class="z-label">Next</span></a></div></div></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="ContentForEmailTemplateWizardView" class="ComponentForEmail' .
                                                'TemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix strong-right">' .
                                                '<h3>Content</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><h3>Merge Tags</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="MergeTagsView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MergeTagsTreeAreaEmailTemplate" class="hasTree' .
                                                ' loading"><span class="big-spinner"></span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="MergeTagGuideAjaxLinkActionElement-') !== false);
            $this->assertTrue(strpos($content, '" class="simple-link" href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class=" tab email-template-' .
                                                'textContent">') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_textContent">' .
                                                'Text Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="ClassicEmailTemplateWizardForm_textContent" ' .
                                                'name="ClassicEmailTemplateWizardForm[textContent]"' .
                                                ' rows="6" cols="50"></textarea>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab2" class="active-tab tab email-template-htmlContent">') !== false);
            $this->assertTrue(strpos($content, '<label for="ClassicEmailTemplateWizardForm_htmlContent">Html Content' .
                                                '</label><textarea id="ClassicEmailTemplateWizardForm_htmlContent" name' .
                                                '="ClassicEmailTemplateWizardForm[htmlContent]"></textarea>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizardForm_' .
                                                'textContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="ClassicEmailTemplateWizardForm_' .
                                                'htmlContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentFinishLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                'jQuery.yii.submitForm(this, &#039;&#039;, {&#039;save&#039;:&#039;' .
                                                'save&#039;}); return false;" href="#"><span class="z-spinner">' .
                                                '</span><span class="z-icon"></span><span class="z-label">Save</span>' .
                                                '</a></div></div></div></div></div></form>') !== false);
        }

        /**
         * @depends testCreateActionForHtmlAndWorkflow
         */
        public function testCreateActionForBuilderAndWorkflow()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_WORKFLOW,
                                    'builtType' => EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE));
            // we access csrf here in BuilderCanvasWizardView:282, which is not set so CHttpRequest tries to set it
            // in cookie but cookies can't be set after writing headers and we get the notorious
            // headers already sent, hence the "@".
            $content = @$this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, '<div id="WorkflowBreadCrumbView" class="SettingsBreadCrumbView ' .
                                                'BreadCrumbView"><div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/workflows/default/index">Workflows</a>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/listForWorkflow">Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<span>Create</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderEmailTemplateStepsAndProgressBarForWizardView" ' .
                                                'class="StepsAndProgressBarForWizardView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="progress"><div class="progress-back"><div class="progress' .
                                                '-bar" style="width:25%; margin-left:0%"></div></div>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%" class="current-step">General</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Layout</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Designer</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Content</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderEmailTemplateWizardView" class="' .
                                                'EmailTemplateWizardView WizardView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                                'Email Template Wizard - Template Builder</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/save?builtType=3" method="post">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<input id="componentType" type="hidden" value=' .
                                                '"ValidateForGeneralData" name="validationScenario"') !== false);
            $this->assertTrue(strpos($content, '<div class="GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="GeneralDataForEmailTemplateWizardView" class="ComponentFor' .
                                                'EmailTemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix">') !== false);
            $this->assertTrue(strpos($content, '<h3>General</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="edit-form_es_" class="errorSummary" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<p>Please fix the following input errors:</p>') !== false);
            $this->assertTrue(strpos($content, '<ul><li>dummy</li></ul></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">' .
                                                '<table class="form-fields">') !== false);
            $this->assertTrue(strpos($content, '<colgroup><col class="col-0"><col class="col-1"></colgroup>') !== false);
            $this->assertTrue(strpos($content, '<tr><th>Module<span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<select name="BuilderEmailTemplateWizardForm[modelClassName]" ' .
                                                'id="BuilderEmailTemplateWizardForm_modelClassName_value">') !== false);
            $this->assertContentHasAllowedModuleOptionTags($content);
            $this->assertTrue(strpos($content, '<tr><th><label for="BuilderEmailTemplateWizardForm_name">' .
                                                'Name</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="BuilderEmailTemplateWizardForm_name"' .
                                                ' name="BuilderEmailTemplateWizardForm[name]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="BuilderEmailTemplateWizardForm_subject">' .
                                                'Subject</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="BuilderEmailTemplateWizardForm_' .
                                                'subject" name="BuilderEmailTemplateWizardForm[subject]"' .
                                                ' type="text" maxlength="255"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="dropzoneBuilderEmailTemplateWizardForm"></div>' .
                                                '<div id="fileUploadBuilderEmailTemplateWizardForm">') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-buttonbar clearfix"><div ' .
                                                'class="addfileinput-button">') !== false);
            $this->assertTrue(strpos($content, '<span>Y</span><strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_files" multiple="multiple" type="file"' .
                                                ' name="BuilderEmailTemplateWizardForm_files"') !== false);
            $this->assertTrue(strpos($content, '<span class="max-upload-size">') !== false);
            $this->assertTrue(strpos($content, '</div><div class="fileupload-content"><table class="files">') !== false);
            $this->assertTrue(strpos($content, '<tr><td colspan="2"><input id="BuilderEmailTemplateWizardForm_type"' .
                                                ' type="hidden" value="1" name="BuilderEmailTemplateWizard' .
                                                'Form[type]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_builtType" type="hidden"' .
                                                ' value="3" name="BuilderEmailTemplateWizardForm[builtType]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_isDraft" type="hidden" ' .
                                                'value="1" name="BuilderEmailTemplateWizardForm[isDraft]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_language" type="hidden" ' .
                                                'name="BuilderEmailTemplateWizardForm[language]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_hiddenId" type="hidden" ' .
                                                'value="0" name="BuilderEmailTemplateWizardForm[hiddenId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="modelClassNameForMergeTagsViewId" type="hidden" ' .
                                                'value="Account" name="modelClassNameForMergeTagsViewId"') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-side-edit-view-panel">') !== false);
            $this->assertTrue(strpos($content, '<h3>Rights and Permissions</h3><div id="owner-box">') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_ownerName"'.
                                                '>Owner Name</label>') !== false);
            $this->assertTrue(strpos($content, '<input name="BuilderEmailTemplateWizardForm[ownerId]" ' .
                                                'id="BuilderEmailTemplateWizardForm_ownerId" value="' .
                                                $this->user->id .'" type="hidden"') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<a id="BuilderEmailTemplateWizardForm_users_Select' .
                                                'Link" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="model-select-icon"></span><span ' .
                                                'class="z-spinner"></span></a>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizard' .
                                                'Form_ownerId_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<label>Who can read and write</label><div ' .
                                                'class="radio-input">') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0" value="" type="radio" name="BuilderEmailTemplate' .
                                                'WizardForm[explicitReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0">Owner</label>') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWrite' .
                                                'ModelPermissions_type_1" value="') !== false);
            $this->assertTrue(strpos($content, '" type="radio" name="BuilderEmailTemplateWizardForm[explicit' .
                                                'ReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel'.
                                                'Permissions_type_1">Owner and users in</label>') !== false);
            $this->assertTrue(strpos($content, '<select id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_nonEveryoneGroup" onclick="document.getElementById(' .
                                                '&quot;BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_1&quot;).checked=&quot;checked&quot;;" name="' . // Not Coding Standard
                                                'BuilderEmailTemplateWizardForm[explicitReadWriteModelPermissions]' .
                                                '[nonEveryoneGroup]"') !== false);
            $this->assertContentHasDemoGroupNameOptionTags($content);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2" value="') !== false);
            $this->assertTrue(strpos($content, 'type="radio" name="BuilderEmailTemplateWizardForm[explicitReadWrite' .
                                                'ModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2">Everyone</label>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertEquals(4, substr_count($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Cancel</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataNextLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                '$(this).addClass(&quot;loading&quot;);$(this).makeOrRemoveLoading' .
                                                'Spinner(true);jQuery.yii.submitForm(this, &#039;&#039;, {&#039;' .
                                                'save&#039;:&#039;save&#039;}); return false;" href="#"><span class="' .
                                                'z-spinner"></span><span class="z-icon"></span><span class="' .
                                                'z-label">Next</span></a></div></div></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="SelectBaseTemplateForEmailTemplateWizardView" class="' .
                                                'ComponentForEmailTemplateWizardView ComponentForWizardModelView ' .
                                                'MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div id="chosen-layout" class="clearfix" ' .
                                                'style="display: block;">') !== false);
            $this->assertTrue(strpos($content, '<span><i class="icon-user-template"></i></span>') !== false);
            $this->assertTrue(strpos($content, '<a id="chooser-overlay" class="secondary-button" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Select a different layout</span>') !== false);
            $this->assertTrue(strpos($content, '<div id="templates" style="display: none;">' .
                                                '<div class="mini-pillbox">') !== false);
            $this->assertTrue(strpos($content, '<div class="pills">') !== false);
            $this->assertTrue(strpos($content, '<a href="#" class="filter-link active" ' .
                                                'data-filter="1">Layouts</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#" id="saved-templates-link" class="filter-link" data-filter="2">' .
                                                'Saved Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<a class="simple-link closeme" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span><i class="icon-x"></i></span>cancel</a></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="templates-chooser-list clearfix" id="BuilderEmail' .
                                                'TemplateWizardForm_baseTemplateId_list_view">') !== false);
            $this->assertTrue(strpos($content, '<div class="summary">Displaying 1-6 of 6 results.</div>') !== false);
            $this->assertTrue(strpos($content, '<ul class="template-list clearfix">') !== false);
            $this->assertTrue(strpos($content, '<li class="base-template-selection" data-value="') !== false);
            $this->assertEquals(6, substr_count($content, '<li class="base-template-selection" data-value="'));
            $this->assertTrue(strpos($content, 'data-name="Blank" data-icon="icon-template-0" ' .
                                                'data-subject="Blank">') !== false);
            $this->assertTrue(strpos($content, '<label><span><i class="icon-template') !== false);
            $this->assertEquals(6, substr_count($content, '<label><span><i class="icon-template-'));
            $this->assertTrue(strpos($content, '</i></span><h4 class="name">') !== false);
            $this->assertEquals(6, substr_count($content, '</i></span><h4 class="name">'));
            $this->assertTrue(strpos($content, 'Blank</h4></label>') !== false);
            $this->assertTrue(strpos($content, '<a class="z-button use-template" href="#"><span class="z-label' .
                                                '">Use</span></a>') !== false);
            $this->assertEquals(6, substr_count($content, '<a class="z-button use-template" href="#">' .
                                                '<span class="z-label">Use</span></a>'));
            $this->assertTrue(strpos($content, '<a class="secondary-button preview-template" href="#"><span class=' .
                                                '"z-label">Preview</span></a></li>') !== false);
            $this->assertEquals(6, substr_count($content, '<a class="secondary-button preview-template" href="#">' .
                                                '<span class="z-label">Preview</span></a></li>'));
            $this->assertTrue(strpos($content, 'data-name="1 Column" data-icon="icon-template-5" ' .
                                                'data-subject="1 Column">') !== false);
            $this->assertTrue(strpos($content, '1 Column</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="2 Columns" data-icon="icon-template-2" ' .
                                                'data-subject="2 Columns">') !== false);
            $this->assertTrue(strpos($content, '2 Columns</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="2 Columns with strong right" data-icon="icon-template-3" ' .
                                                'data-subject="2 Columns with strong right">') !== false);
            $this->assertTrue(strpos($content, '2 Columns with strong right</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="3 Columns" data-icon="icon-template-4" ' .
                                                'data-subject="3 Columns">') !== false);
            $this->assertTrue(strpos($content, '3 Columns</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="3 Columns with Hero" data-icon="icon-template-1" ' .
                                                'data-subject="3 Columns with Hero">') !== false);
            $this->assertTrue(strpos($content, '3 Columns with Hero</h4></label>') !== false);
            $this->assertTrue(strpos($content, '<div class="list-preloader">') !== false);
            $this->assertTrue(strpos($content, '<div class="keys" style="display:none" title="') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_serializedData_' .
                                                'baseTemplateId" type="hidden" name="BuilderEmailTemplateWizard' .
                                                'Form[serializedData][baseTemplateId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_originalBaseTemplateId" ' .
                                                'type="hidden" name="BuilderEmailTemplateWizardForm[original' .
                                                'BaseTemplateId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_serializedData_dom" ' .
                                                'type="hidden" name="BuilderEmailTemplateWizardForm' .
                                                '[serializedData][dom]"') !== false);
            $this->assertTrue(strpos($content, '<a id="selectBaseTemplatePreviousLink" class="cancel-button" href="#"' .
                                                '><span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="selectBaseTemplateNextLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);$(this).addClass(&quot;loading&quot;);$(this).makeOrRemove' .
                                                'LoadingSpinner(true);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#"><span ' .
                                                'class="z-spinner"></span><span class="z-icon"></span><span class="' .
                                                'z-label">Next</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderCanvasWizardView" class="ComponentForEmailTemplate' .
                                                'WizardView ComponentForWizardModelView MetadataView" ' .
                                                'style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix"><h3>Canvas</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="builder" class="strong-right clearfix">') !== false);
            $this->assertTrue(strpos($content, '<div id="iframe-overlay" class="ui-overlay-block">') !== false);
            $this->assertTrue(strpos($content, '<span class="big-spinner"') !== false);
            $this->assertTrue(strpos($content, '<div id="preview-iframe-container" title="Preview" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<a id="preview-iframe-container-close-link" class="default-btn" ' .
                                                'href="#"><span class="z-label">Close</span></a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="preview-iframe" src="about:blank" width="100%" ' .
                                                'height="100%" seamless="seamless" frameborder="0"></iframe>') !== false);
            $this->assertTrue(strpos($content, '<nav class="pillbox clearfix"><div id="builder-elements-menu-button" ' .
                                                'class="active default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-elements"></i>' .
                                                '<span class="button-label">Elements</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="builder-canvas-configuration-menu-button" ' .
                                                'class="default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-configuration"></i>' .
                                                '<span class="button-label">Canvas Configuration</span></a>') !== false);
            $this->assertTrue(strpos($content, '<nav class="pillbox clearfix"><div id="builder-preview-menu-button" ' .
                                                'class="default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-preview"></i><span ' .
                                                'class="button-label">Preview</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="droppable-element-sidebar"><ul id="building-blocks" class="' .
                                                'clearfix builder-elements builder-elements-droppable">') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderButtonElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-button"></i><span>Button</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderDividerElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-divider"></i><span>Divider</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderExpanderElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-expander"></i><span>Expander</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderFancyDividerElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-fancydivider"></i><span>Fancy Divider</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderFooterElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-footer"></i><span>Footer</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderHeaderImageTextElement" class="builder-element' .
                                                ' builder-element-droppable" data-wrap="0">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-header"></i><span>Header</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderImageElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-image"></i><span>Image</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderPlainTextElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-plaintext"></i><span>Plain Text</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderSocialElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-social"></i><span>Social</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderTextElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-text"></i><span>Rich Text</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderTitleElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-title"></i><span>Title</span>') !== false);
            $this->assertTrue(strpos($content, '<a id="refresh-canvas-from-saved-template" style="display:none" ' .
                                                'href="#">Reload Canvas</a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="canvas-iframe" src="about:blank" width="100%" height=' .
                                                '"100%" frameborder="0"></iframe>') !== false);
            $this->assertTrue(strpos($content, '<a id="builderCanvasPreviousLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="builderCanvasSaveLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);$(this).addClass(&quot;loading&quot;);$(this).makeOrRemove' .
                                                'LoadingSpinner(true);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span><span ' .
                                                'class="z-label">Next</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="ContentForEmailTemplateWizardView" class="ComponentForEmail' .
                                                'TemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix strong-right">' .
                                                '<h3>Content</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><h3>Merge Tags</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="MergeTagsView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MergeTagsTreeAreaEmailTemplate" class="hasTree' .
                                                ' loading"><span class="big-spinner"></span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="MergeTagGuideAjaxLinkActionElement-') !== false);
            $this->assertTrue(strpos($content, '" class="simple-link" href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class="active-tab tab email-template-' .
                                                'textContent">') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_textContent">' .
                                                'Text Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="BuilderEmailTemplateWizardForm_textContent" ' .
                                                'name="BuilderEmailTemplateWizardForm[textContent]"' .
                                                ' rows="6" cols="50"></textarea>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizardForm_' .
                                                'textContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizardForm_' .
                                                'htmlContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentFinishLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                '$(this).addClass(&quot;loading&quot;);$(this).makeOrRemoveLoading' .
                                                'Spinner(true);jQuery.yii.submitForm(this, &#039;&#039;, {&#039;' .
                                                'save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span>' .
                                                '<span class="z-label">Save</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="preview-iframe-container" title="Preview" '.
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<a id="preview-iframe-container-close-link" class="default-btn"' .
                                                ' href="#"><span class="z-label">Close</span></a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="preview-iframe" src="about:blank" width="100%" ' .
                                                'height="100%" seamless="seamless" frameborder="0">' .
                                                '</iframe></div></form>') !== false);
        }

        /**
         * @depends testCreateActionForBuilderAndWorkflow
         * @depends testGetHtmlContentActionForBuilder
         */
        public function testEditActionForBuilderAndMarketing()
        {
            $emailTemplateId        = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            // we access csrf here in BuilderCanvasWizardView:282, which is not set so CHttpRequest tries to set it
            // in cookie but cookies can't be set after writing headers and we get the notorious
            // headers already sent, hence the "@".
            $content = @$this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, '<div id="MarketingBreadCrumbView" class="BreadCrumbView">' .
                                                '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, '/marketing/default/index">Marketing</a>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/listForMarketing">Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<span>builder 01</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderEmailTemplateStepsAndProgressBarForWizardView" ' .
                                                'class="StepsAndProgressBarForWizardView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="progress"><div class="progress-back"><div class="progress' .
                                                '-bar" style="width:25%; margin-left:0%"></div></div>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%" class="current-step">General</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Layout</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Designer</span>') !== false);
            $this->assertTrue(strpos($content, '<span style="width:25%">Content</span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderEmailTemplateWizardView" class="' .
                                                'EmailTemplateWizardView WizardView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">' .
                                                'Email Template Wizard - Template Builder</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/save?builtType=3" method="post">') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<input id="componentType" type="hidden" value=' .
                                                '"ValidateForGeneralData" name="validationScenario"') !== false);
            $this->assertTrue(strpos($content, '<div class="GridView">') !== false);
            $this->assertTrue(strpos($content, '<div id="GeneralDataForEmailTemplateWizardView" class="ComponentFor' .
                                                'EmailTemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix">') !== false);
            $this->assertTrue(strpos($content, '<h3>General</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="edit-form_es_" class="errorSummary" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<p>Please fix the following input errors:</p>') !== false);
            $this->assertTrue(strpos($content, '<ul><li>dummy</li></ul></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><div class="panel">' .
                                                '<table class="form-fields">') !== false);
            $this->assertTrue(strpos($content, '<colgroup><col class="col-0"><col class="col-1"></colgroup>') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="BuilderEmailTemplateWizardForm_name">' .
                                                'Name</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="BuilderEmailTemplateWizardForm_name"' .
                                                ' name="BuilderEmailTemplateWizardForm[name]" ' .
                                                'type="text" maxlength="64" value="builder 01"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label for="BuilderEmailTemplateWizardForm_subject">' .
                                                'Subject</label><span class="required">*</span></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div><input id="BuilderEmailTemplateWizardForm_' .
                                                'subject" name="BuilderEmailTemplateWizardForm[subject]"' .
                                                ' type="text" maxlength="255" value="builder 01"') !== false);
            $this->assertTrue(strpos($content, '<tr><th><label>Attachments</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><div id="dropzoneBuilderEmailTemplateWizardForm"></div>' .
                                                '<div id="fileUploadBuilderEmailTemplateWizardForm">') !== false);
            $this->assertTrue(strpos($content, '<div class="fileupload-buttonbar clearfix"><div ' .
                                                'class="addfileinput-button">') !== false);
            $this->assertTrue(strpos($content, '<span>Y</span><strong class="add-label">Add Files</strong>') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_files" multiple="multiple" type="file"' .
                                                ' name="BuilderEmailTemplateWizardForm_files"') !== false);
            $this->assertTrue(strpos($content, '<span class="max-upload-size">') !== false);
            $this->assertTrue(strpos($content, '</div><div class="fileupload-content"><table class="files">') !== false);
            $this->assertTrue(strpos($content, '<tr><td colspan="2"><input id="BuilderEmailTemplateWizardForm_type"' .
                                                ' type="hidden" value="2" name="BuilderEmailTemplateWizard' .
                                                'Form[type]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_builtType" type="hidden"' .
                                                ' value="3" name="BuilderEmailTemplateWizardForm[builtType]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_isDraft" type="hidden" ' .
                                                'value="0" name="BuilderEmailTemplateWizardForm[isDraft]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_language" type="hidden" ' .
                                                'value="en" name="BuilderEmailTemplateWizardForm[language]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_hiddenId" type="hidden" ' .
                                                'value="' . $emailTemplateId . '" name="BuilderEmailTemplate' .
                                                'WizardForm[hiddenId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="modelClassNameForMergeTagsViewId" type="hidden" ' .
                                                'value="Contact" name="modelClassNameFor' .
                                                'MergeTagsViewId"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_modelClassName" ' .
                                                'type="hidden" value="Contact" name="BuilderEmailTemplate' .
                                                'WizardForm[modelClassName]"') !== false);
            $this->assertTrue(strpos($content, '<div class="right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="right-side-edit-view-panel">') !== false);
            $this->assertTrue(strpos($content, '<h3>Rights and Permissions</h3><div id="owner-box">') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_ownerName"'.
                                                '>Owner Name</label>') !== false);
            $this->assertTrue(strpos($content, '<input name="BuilderEmailTemplateWizardForm[ownerId]" ' .
                                                'id="BuilderEmailTemplateWizardForm_ownerId" value="' .
                                                $this->user->id .'" type="hidden"') !== false); // Not Coding Standard
            $this->assertTrue(strpos($content, '<a id="BuilderEmailTemplateWizardForm_users_Select' .
                                                'Link" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="model-select-icon"></span><span ' .
                                                'class="z-spinner"></span></a>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizard' .
                                                'Form_ownerId_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<label>Who can read and write</label><div ' .
                                                'class="radio-input">') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0" value="" type="radio" name="BuilderEmailTemplate' .
                                                'WizardForm[explicitReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_0">Owner</label>') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWrite' .
                                                'ModelPermissions_type_1" value="') !== false);
            $this->assertTrue(strpos($content, '" type="radio" name="BuilderEmailTemplateWizardForm[explicit' .
                                                'ReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel'.
                                                'Permissions_type_1">Owner and users in</label>') !== false);
            $this->assertTrue(strpos($content, '<select id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_nonEveryoneGroup" onclick="document.getElementById(' .
                                                '&quot;BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_1&quot;).checked=&quot;checked&quot;;" name="' . // Not Coding Standard
                                                'BuilderEmailTemplateWizardForm[explicitReadWriteModelPermissions]' .
                                                '[nonEveryoneGroup]"') !== false);
            $this->assertContentHasDemoGroupNameOptionTags($content);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2" value="') !== false);
            $this->assertTrue(strpos($content, 'checked="checked" type="radio" name="BuilderEmailTemplateWizardForm' .
                                                '[explicitReadWriteModelPermissions][type]"') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_explicitReadWriteModel' .
                                                'Permissions_type_2">Everyone</label>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertEquals(4, substr_count($content, '<div class="float-bar"><div class="view-toolbar-container ' .
                                                'clearfix dock"><div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Cancel</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="generalDataNextLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                '$(this).addClass(&quot;loading&quot;);$(this).makeOrRemoveLoading' .
                                                'Spinner(true);jQuery.yii.submitForm(this, &#039;&#039;, {&#039;' .
                                                'save&#039;:&#039;save&#039;}); return false;" href="#"><span class="' .
                                                'z-spinner"></span><span class="z-icon"></span><span class="' .
                                                'z-label">Next</span></a></div></div></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div id="SelectBaseTemplateForEmailTemplateWizardView" class="' .
                                                'ComponentForEmailTemplateWizardView ComponentForWizardModelView ' .
                                                'MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div id="chosen-layout" class="clearfix" ' .
                                                'style="display: block;">') !== false);
            $this->assertTrue(strpos($content, 'span><i class="icon-template-5"></i></span><h3>builder 01</h3>') !== false);
            $this->assertTrue(strpos($content, '<a id="chooser-overlay" class="secondary-button" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Select a different layout</span>') !== false);
            $this->assertTrue(strpos($content, '<div id="templates" style="display: none;">' .
                                                '<div class="mini-pillbox">') !== false);
            $this->assertTrue(strpos($content, '<div class="pills">') !== false);
            $this->assertTrue(strpos($content, '<a href="#" class="filter-link active" ' .
                                                'data-filter="1">Layouts</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#" id="saved-templates-link" class="filter-link" data-filter="2">' .
                                               'Saved Templates</a>') !== false);
            $this->assertTrue(strpos($content, '<a class="simple-link closeme" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span><i class="icon-x"></i></span>cancel</a></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="templates-chooser-list clearfix" id="BuilderEmail' .
                                                'TemplateWizardForm_baseTemplateId_list_view">') !== false);
            $this->assertTrue(strpos($content, '<div class="summary">Displaying 1-6 of 6 results.</div>') !== false);
            $this->assertTrue(strpos($content, '<ul class="template-list clearfix">') !== false);
            $this->assertTrue(strpos($content, '<li class="base-template-selection" data-value="') !== false);
            $this->assertEquals(6, substr_count($content, '<li class="base-template-selection" data-value="'));
            $this->assertTrue(strpos($content, 'data-name="Blank" data-icon="icon-template-0" ' .
                                                'data-subject="Blank">') !== false);
            $this->assertTrue(strpos($content, '<label><span><i class="icon-template') !== false);
            $this->assertEquals(6, substr_count($content, '<label><span><i class="icon-template-'));
            $this->assertTrue(strpos($content, '</i></span><h4 class="name">') !== false);
            $this->assertEquals(6, substr_count($content, '</i></span><h4 class="name">'));
            $this->assertTrue(strpos($content, 'Blank</h4></label>') !== false);
            $this->assertTrue(strpos($content, '<a class="z-button use-template" href="#"><span class="z-label' .
                                                '">Use</span></a>') !== false);
            $this->assertEquals(6, substr_count($content, '<a class="z-button use-template" href="#">' .
                                                '<span class="z-label">Use</span></a>'));
            $this->assertTrue(strpos($content, '<a class="secondary-button preview-template" href="#"><span class=' .
                                                '"z-label">Preview</span></a></li>') !== false);
            $this->assertEquals(6, substr_count($content, '<a class="secondary-button preview-template" href="#">' .
                                                '<span class="z-label">Preview</span></a></li>'));
            $this->assertTrue(strpos($content, 'data-name="1 Column" data-icon="icon-template-5" ' .
                                                'data-subject="1 Column">') !== false);
            $this->assertTrue(strpos($content, '1 Column</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="2 Columns" data-icon="icon-template-2" ' .
                                                'data-subject="2 Columns">') !== false);
            $this->assertTrue(strpos($content, '2 Columns</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="2 Columns with strong right" data-icon="icon-template-3" ' .
                                                'data-subject="2 Columns with strong right">') !== false);
            $this->assertTrue(strpos($content, '2 Columns with strong right</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="3 Columns" data-icon="icon-template-4" ' .
                                                'data-subject="3 Columns">') !== false);
            $this->assertTrue(strpos($content, '3 Columns</h4></label>') !== false);
            $this->assertTrue(strpos($content, 'data-name="3 Columns with Hero" data-icon="icon-template-1" ' .
                                                'data-subject="3 Columns with Hero">') !== false);
            $this->assertTrue(strpos($content, '3 Columns with Hero</h4></label>') !== false);
            $this->assertTrue(strpos($content, '<div class="list-preloader">') !== false);
            $this->assertTrue(strpos($content, '<div class="keys" style="display:none" title="') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_serializedData_' .
                                                'baseTemplateId" type="hidden" value="2" name="BuilderEmailTemplate' .
                                                'WizardForm[serializedData][baseTemplateId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_originalBaseTemplateId" ' .
                                                'type="hidden" value="2" name="BuilderEmailTemplate' .
                                                'WizardForm[originalBaseTemplateId]"') !== false);
            $this->assertTrue(strpos($content, '<input id="BuilderEmailTemplateWizardForm_serializedData_dom" ' .
                                                'type="hidden" name="BuilderEmailTemplateWizardForm' .
                                                '[serializedData][dom]"') !== false);
            $this->assertTrue(strpos($content, '<a id="selectBaseTemplatePreviousLink" class="cancel-button" href="#"' .
                                                '><span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="selectBaseTemplateNextLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);$(this).addClass(&quot;loading&quot;);$(this).makeOrRemove' .
                                                'LoadingSpinner(true);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#"><span ' .
                                                'class="z-spinner"></span><span class="z-icon"></span><span class="' .
                                                'z-label">Next</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="BuilderCanvasWizardView" class="ComponentForEmailTemplate' .
                                                'WizardView ComponentForWizardModelView MetadataView" ' .
                                                'style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix"><h3>Canvas</h3>') !== false);
            $this->assertTrue(strpos($content, '<div id="builder" class="strong-right clearfix">') !== false);
            $this->assertTrue(strpos($content, '<div id="iframe-overlay" class="ui-overlay-block">') !== false);
            $this->assertTrue(strpos($content, '<span class="big-spinner"') !== false);
            $this->assertTrue(strpos($content, '<div id="preview-iframe-container" title="Preview" ' .
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<a id="preview-iframe-container-close-link" class="default-btn" ' .
                                                'href="#"><span class="z-label">Close</span></a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="preview-iframe" src="about:blank" width="100%" ' .
                                                'height="100%" seamless="seamless" frameborder="0"></iframe>') !== false);
            $this->assertTrue(strpos($content, '<nav class="pillbox clearfix"><div id="builder-elements-menu-button" ' .
                                                'class="active default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-elements"></i>' .
                                                '<span class="button-label">Elements</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="builder-canvas-configuration-menu-button" ' .
                                                'class="default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-configuration"></i>' .
                                                '<span class="button-label">Canvas Configuration</span></a>') !== false);
            $this->assertTrue(strpos($content, '<nav class="pillbox clearfix"><div id="builder-preview-menu-button" ' .
                                                'class="default-button">') !== false);
            $this->assertTrue(strpos($content, '<a class="button-action" href="#"><i class="icon-preview"></i><span ' .
                                                'class="button-label">Preview</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="droppable-element-sidebar"><ul id="building-blocks" class="' .
                                                'clearfix builder-elements builder-elements-droppable">') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderButtonElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-button"></i><span>Button</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderDividerElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-divider"></i><span>Divider</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderExpanderElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-expander"></i><span>Expander</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderFancyDividerElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-fancydivider"></i><span>Fancy Divider</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderFooterElement" class="builder-element builder' .
                                                '-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-footer"></i><span>Footer</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderHeaderImageTextElement" class="builder-element' .
                                                ' builder-element-droppable" data-wrap="0">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-header"></i><span>Header</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderImageElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-image"></i><span>Image</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderPlainTextElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-plaintext"></i><span>Plain Text</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderSocialElement" class="builder-element ' .
                                                'builder-element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-social"></i><span>Social</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderTextElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-text"></i><span>Rich Text</span>') !== false);
            $this->assertTrue(strpos($content, '<li data-class="BuilderTitleElement" class="builder-element builder-' .
                                                'element-droppable builder-element-cell-droppable">') !== false);
            $this->assertTrue(strpos($content, '<i class="icon-title"></i><span>Title</span>') !== false);
            $this->assertTrue(strpos($content, '<a id="refresh-canvas-from-saved-template" style="display:none" ' .
                                                'href="#">Reload Canvas</a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="canvas-iframe" src="about:blank" width="100%" height=' .
                                                '"100%" frameborder="0"></iframe>') !== false);
            $this->assertTrue(strpos($content, '<a id="builderCanvasPreviousLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="builderCanvasSaveLink" name="save" class="attachLoading ' .
                                                'z-button" onclick="js:$(this).addClass(&quot;attachLoadingTarget' .
                                                '&quot;);$(this).addClass(&quot;loading&quot;);$(this).makeOrRemove' .
                                                'LoadingSpinner(true);jQuery.yii.submitForm(this, &#039;&#039;, ' .
                                                '{&#039;save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span><span ' .
                                                'class="z-label">Next</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="ContentForEmailTemplateWizardView" class="ComponentForEmail' .
                                                'TemplateWizardView ComponentForWizardModelView' .
                                                ' MetadataView" style="display:none;">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width clearfix strong-right">' .
                                                '<h3>Content</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column"><h3>Merge Tags</h3>') !== false);
            $this->assertTrue(strpos($content, '<div class="MergeTagsView">') !== false);
            $this->assertTrue(strpos($content, '<div id="MergeTagsTreeAreaEmailTemplate" class="hasTree' .
                                                ' loading"><span class="big-spinner"></span></div></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-combined-content right-column">') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-content">') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav">') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="MergeTagGuideAjaxLinkActionElement-') !== false);
            $this->assertTrue(strpos($content, '" class="simple-link" href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<div id="tab1" class="active-tab tab email-template-' .
                                                'textContent">') !== false);
            $this->assertTrue(strpos($content, '<label for="BuilderEmailTemplateWizardForm_textContent">' .
                                                'Text Content</label>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="BuilderEmailTemplateWizardForm_textContent" ' .
                                                'name="BuilderEmailTemplateWizardForm[textContent]"' .
                                                ' rows="6" cols="50"></textarea>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizardForm_' .
                                                'textContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<div class="errorMessage" id="BuilderEmailTemplateWizardForm_' .
                                                'htmlContent_em_" style="display:none"></div>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentCancelLink" class="cancel-button" href="#">' .
                                                '<span class="z-label">Previous</span></a>') !== false);
            $this->assertTrue(strpos($content, '<a id="contentFinishLink" name="save" class="attachLoading z-button"' .
                                                ' onclick="js:$(this).addClass(&quot;attachLoadingTarget&quot;);' .
                                                '$(this).addClass(&quot;loading&quot;);$(this).makeOrRemoveLoading' .
                                                'Spinner(true);jQuery.yii.submitForm(this, &#039;&#039;, {&#039;' .
                                                'save&#039;:&#039;save&#039;}); return false;" href="#">' .
                                                '<span class="z-spinner"></span><span class="z-icon"></span>' .
                                                '<span class="z-label">Save</span></a>') !== false);
            $this->assertTrue(strpos($content, '<div id="preview-iframe-container" title="Preview" '.
                                                'style="display:none">') !== false);
            $this->assertTrue(strpos($content, '<a id="preview-iframe-container-close-link" class="default-btn"' .
                                                ' href="#"><span class="z-label">Close</span></a>') !== false);
            $this->assertTrue(strpos($content, '<iframe id="preview-iframe" src="about:blank" width="100%" ' .
                                                'height="100%" seamless="seamless" frameborder="0">' .
                                                '</iframe></div></form>') !== false);
        }

        public function testSaveWithGet()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save', true);
        }

        /**
         * @depends testSaveWithGet
         */
        public function testSaveWithIrrelevantPost()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array());
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save', true);
        }

        /**
         * @depends testSaveWithGet
         */
        public function testSaveInvalidDataWithoutValidationScenario()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array(
                        'ClassicEmailTemplateWizardForm' => array('name' => ''),
                        'ajax' => 'edit-form',
            ));
            $this->runControllerWithNotSupportedExceptionAndGetContent('emailTemplates/default/save');
        }

        /**
         * @depends testSaveInvalidDataWithoutValidationScenario
         */
        public function testSaveValidDataWithoutValidationScenario()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array(
                'ClassicEmailTemplateWizardForm' => array(
                                                'name'                                  => 'plainText 02',
                                                'subject'                               => 'plainText 02',
                                                'type'                                  => 2,
                                                'builtType'                             => 1,
                                                'isDraft'                               => 0,
                                                'language'                              => '',
                                                'hiddenId'                              => 0,
                                                'modelClassName'                        => 'Contact',
                                                'ownerId'                               => 1,
                                                'ownerName'                             => 'Super User',
                                                'textContent'                           => '',
                                                'explicitReadWriteModelPermissions'     => array(
                                        'nonEveryoneGroup'          => 3,
                                        'type'                      => 1,
                                        ),
                    ),
                'ajax' => 'edit-form',
            ));
            $this->runControllerWithNotSupportedExceptionAndGetContent('emailTemplates/default/save');
        }

        /**
         * @depends testSaveWithGet
         * @expectedException FailedToSaveModelException
         */
        public function testSaveWithInvalidDataWithoutValidation()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array(
                'ClassicEmailTemplateWizardForm' => array(
                                                'name'                                  => '',
                                                'subject'                               => '',
                ),
            ));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/save');
        }

        /**
         * @depends testSaveWithInvalidDataWithoutValidation
         */
        public function testSaveWithInvalidDataWithValidation()
        {
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array(
                'ClassicEmailTemplateWizardForm' => array(
                                                'name'                                  => '',
                                                'subject'                               => '',
                ),
                'ajax' => 'edit-form',
                'validationScenario' => 'ValidateForGeneralData',
            ));
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertCount(4, $jsonDecodedContent);
            $this->assertArrayHasKey('ClassicEmailTemplateWizardForm_type', $jsonDecodedContent);
            $this->assertCount(1, $jsonDecodedContent['ClassicEmailTemplateWizardForm_type']);
            $this->assertEquals($jsonDecodedContent['ClassicEmailTemplateWizardForm_type'][0], 'Type cannot be blank.');
            $this->assertArrayHasKey('ClassicEmailTemplateWizardForm_modelClassName', $jsonDecodedContent);
            $this->assertCount(1, $jsonDecodedContent['ClassicEmailTemplateWizardForm_modelClassName']);
            $this->assertEquals($jsonDecodedContent['ClassicEmailTemplateWizardForm_modelClassName'][0],
                                                                                    'Model Class Name cannot be blank.');
            $this->assertArrayHasKey('ClassicEmailTemplateWizardForm_name', $jsonDecodedContent);
            $this->assertCount(1, $jsonDecodedContent['ClassicEmailTemplateWizardForm_name']);
            $this->assertEquals($jsonDecodedContent['ClassicEmailTemplateWizardForm_name'][0],
                                                                                            'Name cannot be blank.');
            $this->assertArrayHasKey('ClassicEmailTemplateWizardForm_subject', $jsonDecodedContent);
            $this->assertCount(1, $jsonDecodedContent['ClassicEmailTemplateWizardForm_subject']);
            $this->assertEquals($jsonDecodedContent['ClassicEmailTemplateWizardForm_subject'][0],
                                                                                            'Subject cannot be blank.');
        }

        /**
         * @depends testSaveWithInvalidDataWithValidation
         */
        public function testSaveWithValidDataWithValidation()
        {
            $predefinedTemplate                         = EmailTemplate::getById(2);
            $expectedUnserializedData                   = CJSON::decode($predefinedTemplate->serializedData);
            unset($expectedUnserializedData['icon']);
            $expectedUnserializedData['baseTemplateId'] = $predefinedTemplate->id;
            $expectedHtmlContent                        = EmailTemplateSerializedDataToHtmlUtil::
                                                                resolveHtmlByUnserializedData($expectedUnserializedData);
            $serializedData                     = CJSON::encode($expectedUnserializedData);
            $post                   = array(
                'BuilderEmailTemplateWizardForm' => array(
                                                'name'                              => 'builder 02',
                                                'subject'                           => 'builder 02',
                                                'type'                              => 2,
                                                'builtType'                         => 3,
                                                'isDraft'                           => 0,
                                                'language'                          => '',
                                                'hiddenId'                          => 0,
                                                'modelClassName'                    => 'Contact',
                                                'ownerId'                           => 1,
                                                'ownerName'                         => 'Super User',
                                                'explicitReadWriteModelPermissions' => array(
                                            'nonEveryoneGroup'  => 3,
                                            'type'              => 1,
                    ),
                                                'baseTemplateId' => $predefinedTemplate->id,
                                                'serializedData' => array(
                                            'baseTemplateId'    => $predefinedTemplate->id,
                                            'dom'               => '',
                    ),
                                                'originalBaseTemplateId' => '',
                                                'textContent' => 'some text',
                ),
                'validationScenario' => BuilderEmailTemplateWizardForm::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO,
                'ajax' => 'edit-form',
            );
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE));
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertEmpty($jsonDecodedContent);

            // now send the actual save request
            unset($post['ajax']);
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertNotEmpty($jsonDecodedContent);
            $this->assertCount(3, $jsonDecodedContent);
            $this->assertArrayHasKey('id', $jsonDecodedContent);
            $this->assertArrayHasKey('redirectToList', $jsonDecodedContent);
            $this->assertFalse($jsonDecodedContent['redirectToList']);
            $this->assertArrayHasKey('moduleClassName', $jsonDecodedContent);
            $this->assertEquals('ContactsModule', $jsonDecodedContent['moduleClassName']);

            // ensure htmlContent was generated.
            $emailTemplate      = EmailTemplate::getById((int)$jsonDecodedContent['id']);
            $unserializedData   = CJSON::decode($emailTemplate->serializedData);
            $this->assertEquals($expectedHtmlContent, $emailTemplate->htmlContent);
            $this->assertEquals($expectedUnserializedData, $unserializedData);
        }

        /**
         * @depends testSaveWithValidDataWithValidation
         */
        public function testSaveWithFiles()
        {
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $filesIds               = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $filesIds[]                 = $file->id;
            }
            $post               = array(
                'ClassicEmailTemplateWizardForm' => array(
                                                'name'                                  => 'plainText 03',
                                                'subject'                               => 'plainText 03',
                                                'type'                                  => 2,
                                                'builtType'                             => 1,
                                                'isDraft'                               => 0,
                                                'language'                              => '',
                                                'hiddenId'                              => 0,
                                                'modelClassName'                        => 'Contact',
                                                'ownerId'                               => 1,
                                                'ownerName'                             => 'Super User',
                                                'textContent'                           => 'some text',
                                                'explicitReadWriteModelPermissions'     => array(
                        'nonEveryoneGroup'          => 3,
                        'type'                      => 1,
                    ),
                ),
                'filesIds'              => $filesIds,
                'validationScenario'    => BuilderEmailTemplateWizardForm::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO,
                'ajax'                  => 'edit-form',
            );
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertEmpty($jsonDecodedContent);

            // now send the actual save request
            unset($post['ajax']);
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertNotEmpty($jsonDecodedContent);
            $this->assertCount(3, $jsonDecodedContent);
            $this->assertArrayHasKey('id', $jsonDecodedContent);
            $this->assertArrayHasKey('redirectToList', $jsonDecodedContent);
            $this->assertFalse($jsonDecodedContent['redirectToList']);
            $this->assertArrayHasKey('moduleClassName', $jsonDecodedContent);
            $this->assertEquals('ContactsModule', $jsonDecodedContent['moduleClassName']);

            $emailTemplate  = EmailTemplate::getById((int)$jsonDecodedContent['id']);
            $this->assertCount(3, $emailTemplate->files);
        }

        /**
         * @depends testSaveWithValidDataWithValidation
         * @depends testGetHtmlContentActionForBuilder
         */
        public function testSaveWithBaseTemplateIdUpdate()
        {
            $emailTemplateId        = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 02');
            $emailTemplate          = EmailTemplate::getById($emailTemplateId);
            $oldUnserializedData    = CJSON::decode($emailTemplate->serializedData);
            $oldBaseTemplateId      = ArrayUtil::getArrayValue($oldUnserializedData, 'baseTemplateId');

            $baseTemplateId                       = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $baseTemplate                         = EmailTemplate::getById($baseTemplateId);
            $expectedUnserializedData             = CJSON::decode($baseTemplate->serializedData);
            unset($expectedUnserializedData['icon']);
            $expectedUnserializedData['baseTemplateId'] = $baseTemplate->id;
            $expectedHtmlContent                  = EmailTemplateSerializedDataToHtmlUtil::
                                                                resolveHtmlByUnserializedData($expectedUnserializedData);
            $post                   = array(
                'BuilderEmailTemplateWizardForm' => array(
                                                'name'                              => 'builder 02',
                                                'subject'                           => 'builder 02',
                                                'type'                              => 2,
                                                'builtType'                         => 3,
                                                'isDraft'                           => 0,
                                                'language'                          => '',
                                                'hiddenId'                          => $emailTemplateId,
                                                'modelClassName'                    => 'Contact',
                                                'ownerId'                           => 1,
                                                'ownerName'                         => 'Super User',
                                                'explicitReadWriteModelPermissions' => array(
                        'nonEveryoneGroup'  => 3,
                        'type'              => 1,
                    ),
                                                'baseTemplateId' => $baseTemplateId,
                                                'serializedData' => array(
                        'baseTemplateId'    => $baseTemplateId,
                        'dom'               => CJSON::encode($oldUnserializedData['dom']),
                    ),
                                                'originalBaseTemplateId' => $oldBaseTemplateId,
                                                'textContent' => 'some text changed',
                ),
                'validationScenario' => BuilderEmailTemplateWizardForm::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO,
                'ajax' => 'edit-form',
            );
            $this->setGetArray(array('builtType' => EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE));
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertEmpty($jsonDecodedContent);

            // now send the actual save request
            unset($post['ajax']);
            $this->setPostArray($post);
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/save');
            $jsonDecodedContent = CJSON::decode($content);
            $this->assertNotEmpty($jsonDecodedContent);
            $this->assertCount(3, $jsonDecodedContent);
            $this->assertArrayHasKey('id', $jsonDecodedContent);
            $this->assertEquals($emailTemplateId, $jsonDecodedContent['id']);
            $this->assertArrayHasKey('redirectToList', $jsonDecodedContent);
            $this->assertFalse($jsonDecodedContent['redirectToList']);
            $this->assertArrayHasKey('moduleClassName', $jsonDecodedContent);
            $this->assertEquals('ContactsModule', $jsonDecodedContent['moduleClassName']);

            // ensure htmlContent was generated.
            $emailTemplate->forgetAll();
            unset($emailTemplate);
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $unserializedData   = CJSON::decode($emailTemplate->serializedData);
            $this->assertEquals($expectedUnserializedData, $unserializedData);
            $this->assertEquals('some text changed', $emailTemplate->textContent);
            $this->assertEquals($expectedHtmlContent, $emailTemplate->htmlContent);
        }

        /**
         * @depends testListForMarketingAction
         */
        public function testStickySearchActions()
        {
            StickySearchUtil::clearDataByKey('EmailTemplatesSearchView');
            $value = StickySearchUtil::getDataByKey('EmailTemplatesSearchView');
            $this->assertNull($value);

            $this->setGetArray(array(
                        'EmailTemplatesSearchForm' => array(
                            'anyMixedAttributes'    => 'xyz'
                        )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->assertTrue(strpos($content, 'No results found') !== false);
            $data = StickySearchUtil::getDataByKey('EmailTemplatesSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                'dynamicStructure'                      => null,
                'anyMixedAttributes'                    => 'xyz',
                'anyMixedAttributesScope'               => null,
                'selectedListAttributes'                => null
            );
            $this->assertEquals($compareData, $data);

            $this->setGetArray(array(
                'EmailTemplatesSearchForm' => array(
                                                'anyMixedAttributes'    => 'Test'
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->assertTrue(strpos($content, '1 result(s)') !== false);
            $data = StickySearchUtil::getDataByKey('EmailTemplatesSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                'dynamicStructure'                      => null,
                'anyMixedAttributes'                    => 'Test',
                'anyMixedAttributesScope'               => null,
                'selectedListAttributes'                => null,
                'savedSearchId'                         => null
            );
            $this->assertEquals($compareData, $data);

            $this->setGetArray(array('clearingSearch' => true));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $data = StickySearchUtil::getDataByKey('EmailTemplatesSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                'dynamicStructure'                      => null,
                'anyMixedAttributesScope'               => null,
                'selectedListAttributes'                => null
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testDetailsActionForMarketing
         * @depends testDetailsActionForWorkflow
         */
        public function testDeleteAction()
        {
            $initialCount   = EmailTemplate::getCount();
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'marketing 01');
            // Delete an emailTemplate.
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/listForMarketing');
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals($initialCount - 1 , EmailTemplate::getCount());
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'workflow 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/listForWorkflow');
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals($initialCount - 2, EmailTemplate::getCount());
        }

        protected static function sanitizeStringOfIdAttribute(& $string)
        {
            // remove id from all tags
            $string = preg_replace('#\s\[?id\]?="[^"]+"#', '', $string); // Not Coding Standard
            // remove hidden input which has a name ending with id
            $string = preg_replace('#<input(.*?)type="hidden(.*?) name="(.*?)\[id\]"(.*?)#is', '', $string);
        }

        protected static function sanitizeStringOfScript(& $string)
        {
            $string = trim(preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string));
        }

        protected function assertContentHasAllowedModuleOptionTags($content)
        {
            $availableModules   = EmailTemplateModelClassNameElement::getAvailableModelNamesArray();
            foreach ($availableModules as $key => $name)
            {
                $this->assertTrue(strpos($content, '<option value="' . $key) !== false); // Not Coding Standard
            }
        }

        protected function assertContentHasDemoGroupNameOptionTags($content)
        {
            $this->assertTrue(strpos($content, '">East</option>') !== false);
            $this->assertTrue(strpos($content, '">East Channel Sales</option>') !== false);
            $this->assertTrue(strpos($content, '">East Direct Sales</option>') !== false);
            $this->assertTrue(strpos($content, '">West</option>') !== false);
            $this->assertTrue(strpos($content, '">West Channel Sales</option>') !== false);
            $this->assertTrue(strpos($content, '">West Direct Sales</option>') !== false);
        }

        protected function getTestUserName()
        {
            return 'super';
        }
    }
?>