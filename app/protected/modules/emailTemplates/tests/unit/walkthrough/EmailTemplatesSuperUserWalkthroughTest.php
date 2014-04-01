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
        protected $super;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $maker  = new EmailTemplatesDefaultDataMaker();
            $maker->make();
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
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
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserRelationsAndAttributesTreeForMergeTags()
        {
            //Test without a node id
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/relationsAndAttributesTreeForMergeTags');

            //Test with a node id
            $this->setGetArray (array('uniqueId' => 'EmailTemplate', 'nodeId' => 'EmailTemplate_secondaryAddress'));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/relationsAndAttributesTreeForMergeTags');
        }

        /**
         * @depends testSuperUserRelationsAndAttributesTreeForMergeTags
         */
        public function testSuperUserListForMarketingAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name1'), 1);
            $this->assertEquals (substr_count($content, 'Clark Kent'), 2);
            $this->assertEquals (substr_count($content, '<td>Use HTML</td>'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $this->assertEquals (1, count($emailTemplates));
        }

        /**
         * @depends testSuperUserListForMarketingAction
         */
        public function testSuperUserListForWorkflowAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name'), 1);
            $this->assertEquals (substr_count($content, 'Clark Kent'), 2);
            $this->assertEquals (substr_count($content, '<td>Use HTML</td>'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_WORKFLOW);
            $this->assertEquals (1, count($emailTemplates));
        }

        public function testSuperUserSelectBuiltTypeAction()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/selectBuiltType');
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">'.
                                                'Email Template Wizard</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<ul class="configuration-list creation-list">') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use Plain Text</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=1">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use HTML</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=2">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use Template Builder</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=3">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li></ul>') !== false);
        }

        public function testSuperUserCreateWithoutBuiltTypeAction()
        {
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $content    = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title"><span class="ellipsis-content">'.
                    'Email Template Wizard</span></span></h1>') !== false);
            $this->assertTrue(strpos($content, '<ul class="configuration-list creation-list">') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use Plain Text</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=1">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use HTML</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=2">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li>') !== false);
            $this->assertTrue(strpos($content, '<li><h4>Use Template Builder</h4><a class="white-button" href="') !== false);
            $this->assertTrue(strpos($content, '/emailTemplates/default/create?type=2&amp;builtType=3">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Create</span></a></li></ul>') !== false);
        }

        public function testSuperUserMergeTagGuideAction()
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
            $this->assertTrue(strpos($content, '<li><strong>{{UNSUBSCRIBE_URL}}</strong> : prints unsubscribe' .
                                                ' url.</li>') !== false);
            $this->assertTrue(strpos($content, '<li><strong>{{MANAGE_SUBSCRIPTIONS_URL}}</strong> : prints manage' .
                                                ' subscriptions url.</li>') !== false);
        }

        public function testSuperUserGetHtmlContentActionForPredefined()
        {
            $emailTemplateId    = 2;
            $this->setGetArray(array('id' => $emailTemplateId, 'className' => 'EmailTemplate'));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent', true);
        }

        /**
         * @depends testSuperUserGetHtmlContentActionForPredefined
         */
        public function testSuperUserGetHtmlContentActionForPlainText()
        {
            // create a plain text template, returned content should be empty
            $emailTemplate  = EmailTemplateTestHelper::create('plainText 01', 'plainText 01', 'Contact', null, 'text',
                                                                            EmailTemplate::TYPE_CONTACT, 0,
                                                                            EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY);
            $this->setGetArray(array('id' => $emailTemplate->id, 'className' => get_class($emailTemplate)));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getHtmlContent', true);
        }

        /**
         * @depends testSuperUserGetHtmlContentActionForPlainText
         */
        public function testSuperUserGetHtmlContentActionForHtml()
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
         * @depends testSuperUserGetHtmlContentActionForHtml
         */
        public function testSuperUserGetHtmlContentActionForBuilder()
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
            $this->markTestSkipped("// TODO: @Sergio: Critical: why are the ids for buildersocialbuttonelement different here?");
            $this->assertEquals($expectedHtmlContent, $content);
        }

        /**
         * @depends testSuperUserGetHtmlContentActionForBuilder
         */
        public function testSuperUserGetSerializedToHtmlContentForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent', true);
        }

        /**
         * @depends testSuperUserGetSerializedToHtmlContentForPlainText
         */
        public function testSuperUserGetSerializedToHtmlContentForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent', true);
        }

        /**
         * @depends testSuperUserGetSerializedToHtmlContentForHtml
         */
        public function testSuperUserGetSerializedToHtmlContentForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent');
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testSuperUserGetSerializedToHtmlContentForBuilder
         */
        public function testSuperUserGetSerializedToHtmlContentForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/getSerializedToHtmlContent');
            $this->assertEquals($expectedContent, $content);
        }

        public function testSuperUserRenderCanvasWithoutId()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testSuperUserRenderCanvasWithoutId
         */
        public function testSuperUserRenderCanvasForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testSuperUserRenderCanvasForPlainText
         */
        public function testSuperUserRenderCanvasForForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas', true);
        }

        /**
         * @depends testSuperUserRenderCanvasForForHtml
         */
        public function testSuperUserRenderCanvasForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId, true);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas');
            $this->markTestSkipped("// TODO: @Sergio: Critical: why are the ids for buildersocialbuttonelement different here?");
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testSuperUserRenderCanvasForBuilder
         */
        public function testSuperUserRenderCanvasForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId, true);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/renderCanvas');
            $this->assertEquals($expectedContent, $content);
        }

        public function testSuperUserRenderPreviewWithoutId()
        {
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testSuperUserRenderPreviewWithoutId
         */
        public function testSuperUserRenderPreviewForPlainText()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'plainText 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testSuperUserRenderPreviewForPlainText
         */
        public function testSuperUserRenderPreviewForForHtml()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName('EmailTemplate', 'html 01');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview', true);
        }

        /**
         * @depends testSuperUserRenderPreviewForForHtml
         */
        public function testSuperUserRenderPreviewForBuilder()
        {
            $emailTemplateId    = self::getModelIdByModelNameAndName('EmailTemplate', 'builder 01');
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview');
            $this->markTestSkipped("// TODO: @Sergio: Critical: why are the ids for buildersocialbuttonelement different here?");
            $this->assertEquals($expectedContent, $content);
        }

        /**
         * @depends testSuperUserRenderPreviewForBuilder
         */
        public function testSuperUserRenderPreviewForPredefined()
        {
            $emailTemplateId    = 2;
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content            = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview');
            $this->assertEquals($expectedContent, $content);
        }


        /**
         * @depends testSuperUserRenderPreviewForPredefined
         */
        public function testSuperUserRenderPreviewWithPost()
        {
            $emailTemplate      = EmailTemplate::getById(2);
            $expectedContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate);
            $this->setPostArray(array('serializedData' => $emailTemplate->serializeData));
            $content            = $this->runControllerWithExitExceptionAndGetContent('emailTemplates/default/renderPreview');
            $this->assertEquals($expectedContent, $content);
        }










        /**
         * @depends testSuperUserListForWorkflowAction
         *
        public function testSuperUserCreateActionForWorkflow()
        {
            // Create a new emailTemplate and test validator.
            $this->setGetArray(array('type' => EmailTemplate::TYPE_WORKFLOW,
                                     'builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'name'              => 'New Test Workflow EmailTemplate',
                'subject'           => 'New Test Subject')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, '<select name="EmailTemplate[modelClassName]" id="EmailTemplate_modelClassName_value"') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            $this->assertTrue(strpos($content, 'Module cannot be blank.') !== false);

            // Create a new emailTemplate and test merge tags validator.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'modelClassName'    => 'Meeting',
                'name'              => 'New Test Workflow EmailTemplate',
                'subject'           => 'New Test Subject',
                'textContent'       => 'This is text content [[INVALID^TAG]]',
                'htmlContent'       => 'This is Html content [[INVALIDTAG]]',
            )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, '<select name="EmailTemplate[modelClassName]" id="EmailTemplate_modelClassName_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="Meeting" selected="selected">Meetings</option>') !== false);
            $this->assertTrue(strpos($content, 'INVALID^TAG') !== false);
            $this->assertTrue(strpos($content, 'INVALIDTAG') !== false);
            $this->assertEquals(2, substr_count($content, 'INVALID^TAG'));
            $this->assertEquals(2, substr_count($content, 'INVALIDTAG'));

            // Create a new emailTemplate and save it.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'name'              => 'New Test Workflow EmailTemplate',
                'modelClassName'    => 'Contact',
                'subject'           => 'New Test Subject [[FIRST^NAME]]',
                'textContent'       => 'New Text Content [[FIRST^NAME]]')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertTrue  ($emailTemplate->id > 0);
            $this->assertEquals('New Test Subject [[FIRST^NAME]]', $emailTemplate->subject);
            $this->assertEquals('New Text Content [[FIRST^NAME]]', $emailTemplate->textContent);
            $this->assertTrue  ($emailTemplate->owner == $this->super);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplate->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
        }

        /**
         * @depends testSuperUserCreateActionForWorkflow
         *
        public function testSuperUserCreateActionForMarketing()
        {
            // Create a new emailTemplate and test validator.
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'name'              => 'New Test EmailTemplate',
                'subject'           => 'New Test Subject')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            $this->assertFalse(strpos($content, 'Model Class Name cannot be blank.') !== false);

            // Create a new emailTemplate and test merge tags validator.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'modelClassName'    => 'Contact',
                'name'              => 'New Test EmailTemplate',
                'subject'           => 'New Test Subject',
                'textContent'       => 'This is text content [[INVALID^TAG]]',
                'htmlContent'       => 'This is Html content [[INVALIDTAG]]',
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, 'INVALID^TAG') !== false);
            $this->assertTrue(strpos($content, 'INVALIDTAG') !== false);
            $this->assertEquals(2, substr_count($content, 'INVALID^TAG'));
            $this->assertEquals(2, substr_count($content, 'INVALIDTAG'));

            // Create a new emailTemplate and save it.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'name'              => 'New Test EmailTemplate',
                'modelClassName'    => 'Contact',
                'subject'           => 'New Test Subject [[FIRST^NAME]]',
                'textContent'       => 'New Text Content [[FIRST^NAME]]')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplateId    = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test EmailTemplate');
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $this->assertTrue  ($emailTemplateId > 0);
            $this->assertEquals('New Test Subject [[FIRST^NAME]]', $emailTemplate->subject);
            $this->assertEquals('New Text Content [[FIRST^NAME]]', $emailTemplate->textContent);
            $this->assertTrue  ($emailTemplate->owner == $this->super);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplateId));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(4, count($emailTemplates));
        }

        /**
         * @depends testSuperUserCreateActionForMarketing
         *
        public function testSuperUserEditActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_name" name="EmailTemplate[name]"' .
                                        ' type="text" maxlength="64" value="'. $emailTemplate->name . '" />') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_subject" name="EmailTemplate[subject]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->subject . '" />') !== false);
            $this->assertTrue(strpos($content, '<textarea id="EmailTemplate_textContent" name="EmailTemplate[textContent]"' .
                ' rows="6" cols="50">'. $emailTemplate->textContent . '</textarea>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'EmailTemplate_htmlContent\' name=\'EmailTemplate[htmlContent]\'>' .
                $emailTemplate->htmlContent . '</textarea>') !== false);

            // Test having a failed validation on the emailTemplate during save.
            $this->setGetArray (array('id' => $emailTemplateId));
            $this->setPostArray(array('EmailTemplate' => array('name' => '', 'htmlContent' => '', 'textContent' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, 'Name cannot be blank') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);

            // Send a valid post and verify saved data.
            $this->setPostArray(array('EmailTemplate' => array(
                                    'name' => 'New Test Email Template 00',
                                    'subject' => 'New Subject 00',
                                    'type' => EmailTemplate::TYPE_CONTACT,
                                    'htmlContent' => 'New HTML Content 00',
                                    'textContent' => 'New Text Content 00')));
            $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject 00', $emailTemplate->subject);
            $this->assertEquals('New Test Email Template 00', $emailTemplate->name);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT, $emailTemplate->type);
            $this->assertEquals('New Text Content 00', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content 00', $emailTemplate->htmlContent);

            // Now test same with file attachment
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                  = array();
            $filesIds               = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $filesIds[]                 = $file->id;
            }
            $this->setPostArray(array('EmailTemplate' => array(
                                            'name' => 'New Test Email Template 00',
                                            'subject' => 'New Subject 00',
                                            'type' => EmailTemplate::TYPE_CONTACT,
                                            'htmlContent' => 'New HTML Content 00',
                                            'textContent' => 'New Text Content 00'),
                                    'filesIds'      => $filesIds,
                                    ));
            $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject 00', $emailTemplate->subject);
            $this->assertEquals('New Test Email Template 00', $emailTemplate->name);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT, $emailTemplate->type);
            $this->assertEquals('New Text Content 00', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content 00', $emailTemplate->htmlContent);
            $this->assertNotEmpty($emailTemplate->files);
            $this->assertCount(count($files), $emailTemplate->files);
            foreach ($files as $index => $file)
            {
                $this->assertEquals($files[$index]['name'], $emailTemplate->files[$index]->name);
                $this->assertEquals($files[$index]['type'], $emailTemplate->files[$index]->type);
                $this->assertEquals($files[$index]['size'], $emailTemplate->files[$index]->size);
                $this->assertEquals($files[$index]['contents'], $emailTemplate->files[$index]->fileContent->content);
            }
        }

        /**
         * @depends testSuperUserCreateActionForMarketing
         *
        public function testSuperUserEditActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_name" name="EmailTemplate[name]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->name . '" />') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_subject" name="EmailTemplate[subject]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->subject . '" />') !== false);
            $this->assertTrue(strpos($content, '<textarea id="EmailTemplate_textContent" name="EmailTemplate[textContent]"' .
                ' rows="6" cols="50">'. $emailTemplate->textContent . '</textarea>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'EmailTemplate_htmlContent\' name=\'EmailTemplate[htmlContent]\'>' .
                $emailTemplate->htmlContent . '</textarea>') !== false);

            // Test having a failed validation on the emailTemplate during save.
            $this->setGetArray (array('id' => $emailTemplateId));
            $this->setPostArray(array('EmailTemplate' => array('name' => '', 'htmlContent' => '', 'textContent' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, 'Name cannot be blank') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);

            // Send a valid post and verify saved data.
            $this->setPostArray(array('EmailTemplate' => array(
                'name' => 'New Test Workflow Email Template 00',
                'subject' => 'New Subject 00',
                'type' => EmailTemplate::TYPE_WORKFLOW,
                'htmlContent' => 'New HTML Content 00',
                'textContent' => 'New Text Content 00')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject 00', $emailTemplate->subject);
            $this->assertEquals('New Test Workflow Email Template 00', $emailTemplate->name);
            $this->assertEquals(EmailTemplate::TYPE_WORKFLOW, $emailTemplate->type);
            $this->assertEquals('New Text Content 00', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content 00', $emailTemplate->htmlContent);
        }

        /**
         * @depends testSuperUserEditActionForMarketing
         *
        public function testSuperUserDetailsJsonActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Email Template 00');
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
         * @depends testSuperUserDetailsJsonActionForMarketing
         *
        public function testSuperUserDetailsActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Email Template 00');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a class="active-tab" href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">') !== false);
        }

        /**
         * @depends testSuperUserEditActionForWorkflow
         *
        public function testSuperUserDetailsJsonActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
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
         * @depends testSuperUserDetailsJsonActionForWorkflow
         */
        public function testSuperUserDetailsJsonActionForCreateEmailMessage()
        {
            $contact         = ContactTestHelper::createContactByNameForOwner('test', $this->super);
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
            $emailTemplate   = EmailTemplate::getById($emailTemplateId);
            $unsubscribePlaceholder         = GlobalMarketingFooterUtil::
                                                    UNSUBSCRIBE_URL_PLACEHOLDER;
            $manageSubscriptionsPlaceholder = GlobalMarketingFooterUtil::
                                                    MANAGE_SUBSCRIPTIONS_URL_PLACEHOLDER;
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

        /**
         * @depends testSuperUserDetailsJsonActionForCreateEmailMessage
         */
        public function testSuperUserDetailsActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a class="active-tab" href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">') !== false);
        }

        /**
         * @depends testSuperUserListForMarketingAction
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
         * @depends testSuperUserDetailsActionForMarketing
         */
        public function testSuperUserDeleteAction()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Email Template 00');
            // Delete an emailTemplate.
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/listForMarketing');
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/delete');
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/listForWorkflow');
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
        }
    }
?>