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

    class AutoresponderDefaultControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $marketingListId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Yii::app()->user->userModel = UserTestHelper::createBasicUser('nobody');

            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT, 'Subject 01', 'Contact',
                                                            'EmailTemplate 01', 'Html Content 01', 'Text Content 01');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT, 'Subject 02', 'Contact',
                                                            'EmailTemplate 02', 'Html Content 02', 'Text Content 03');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT, 'Subject 03', 'Contact',
                                                            'EmailTemplate 03', 'Html Content 03', 'Text Content 03');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT, 'Subject 04', 'Contact',
                                                            'EmailTemplate 04', 'Html Content 04', 'Text Content 04');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_WORKFLOW, 'Subject 05', 'Contact',
                                                            'EmailTemplate 05', 'Html Content 05', 'Text Content 05');

            $marketingList = MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                                                        'MarketingList Description');
            static::$marketingListId = $marketingList->id;
            AutoresponderTestHelper::createAutoresponder('Autoresponder 01', 'Subject 01', 'This is text Content 01',
                            'This is html Content 01', 10, Autoresponder::OPERATION_SUBSCRIBE, true, $marketingList);
            AutoresponderTestHelper::createAutoresponder('Autoresponder 02', 'Subject 02', 'This is text Content 02',
                        'This is html Content 02', 5, Autoresponder::OPERATION_UNSUBSCRIBE, false, $marketingList);
            AutoresponderTestHelper::createAutoresponder('Autoresponder 03', 'Subject 03', 'This is text Content 03',
                        'This is html Content 03', 1, Autoresponder::OPERATION_REMOVE, false, $marketingList);
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testSuperUserCreateActionWithoutParameters()
        {
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserCreateActionWithoutParameters
         */
        public function testSuperUserCreateActionWithoutRedirectUrl()
        {
            $this->setGetArray(array('marketingListId' => static::$marketingListId ));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
        }

        /**
         * @depends testSuperUserCreateActionWithoutRedirectUrl
         */
        public function testSuperUserCreateActionWithParameters()
        {
            // test create page
            $redirectUrl    = 'http://www.zurmo.com/';
            $this->setGetArray(array('marketingListId' => static::$marketingListId , 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertTrue(strpos($content, 'emailTemplates/default/index">Marketing</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/list">Lists</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/details?id=' . static::$marketingListId .
                                                '">MarketingListName</a> &#47; <span>Create</span></div>') !== false);
            $this->assertTrue(strpos($content, 'Create Autoresponder') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_operationType_value" class="required">' .
                                                    'Type <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_secondsFromOperation_value" class="required">' .
                                                    'When to send? <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_name" class="required">Name ' .
                                                    '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_subject" class="required">Subject ' .
                                                '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_enableTracking">Enable Tracking' .
                                                '</label>') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[operationType]" ' .
                                                'id="Autoresponder_operationType_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="1">Subscription</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="2">Unsubscription</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="3">Removal</option>') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[secondsFromOperation]" ' .
                                                'id="Autoresponder_secondsFromOperation_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="3600">1 Hour</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="21600">6 Hours</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="43200">12 Hours</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="86400">1 day</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="259200">3 days</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="604800">1 week</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="1209600">2 weeks</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="2592000">1 month</option>') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_name" name="Autoresponder[name]" '.
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_subject" name="Autoresponder[subject]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[contactEmailTemplateNames]" ' .
                                                'id="Autoresponder_contactEmailTemplateNames_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="">Select a template</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 01</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 02</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 03</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 04</option>') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="mergetag-guide" class="simple-link" ' .
                                                'href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="Autoresponder_textContent" ' .
                                                'name="Autoresponder[textContent]" rows="6" cols="50"') !== false);
            $this->assertTrue(strpos($content, "<textarea id='Autoresponder_htmlContent' " .
                                                "name='Autoresponder[htmlContent]'") !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Cancel</span>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);

            // test all required fields
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => '',
                                                            'secondsFromOperation'      => '',
                                                            'name'                      => '',
                                                            'subject'                   => '',
                                                            'enableTracking'            => '',
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => '',
                                                            'htmlContent'               => '',
                                                            )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertTrue(strpos($content, 'Please fix the following input errors:') !== false);
            $this->assertTrue(strpos($content, 'Name cannot be blank.') !== false);
            $this->assertTrue(strpos($content, 'Subject cannot be blank.') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            $this->assertTrue(strpos($content, 'When to send? cannot be blank.') !== false);
            $this->assertTrue(strpos($content, 'Type cannot be blank.') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_name" name="Autoresponder[name]" type="text" maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_subject" name="Autoresponder[subject]" type="text" maxlength="64" value="" class="error"') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[operationType]" ' .
                                                'id="Autoresponder_operationType_value" class="error">') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[secondsFromOperation]" ' .
                                                'id="Autoresponder_secondsFromOperation_value" class="error">') !== false);

            // try with invalid merge tags
            $this->setPostArray(array('Autoresponder' => array(
                                                                'operationType'             => 2,
                                                                'secondsFromOperation'      => 86400,
                                                                'name'                      => 'Autoresponder 04',
                                                                'subject'                   => 'Subject 04',
                                                                'enableTracking'            => 0,
                                                                'contactEmailTemplateNames' => '',
                                                                'textContent'               => '[[TEXT^CONTENT]] 04',
                                                                'htmlContent'               => '[[HTML^CONTENT]] 04',
                                                            )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertTrue(strpos($content, 'Please fix the following input errors:') !== false);
            $this->assertTrue(strpos($content, 'Text Content: Invalid MergeTag(TEXT^CONTENT) used.') !== false);
            $this->assertTrue(strpos($content, 'Html Content: Invalid MergeTag(HTML^CONTENT) used.') !== false);

            // try saving with valid data.
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => 2,
                                                            'secondsFromOperation'      => 2592000,
                                                            'name'                      => 'Autoresponder 04',
                                                            'subject'                   => 'Subject 04',
                                                            'enableTracking'            => 0,
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => 'Text Content 04',
                                                            'htmlContent'               => 'Html Content 04',
                                                        )));

            $resolvedRedirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/create');
            $autoresponders  = Autoresponder::getByName('Autoresponder 04');
            $this->assertEquals(1, count($autoresponders));
            $this->assertTrue  ($autoresponders[0]->id > 0);
            $this->assertEquals(2, $autoresponders[0]->operationType);
            $this->assertEquals(2592000, $autoresponders[0]->secondsFromOperation);
            $this->assertEquals('Subject 04', $autoresponders[0]->subject);
            $this->assertEquals(0, $autoresponders[0]->enableTracking);
            $this->assertEquals('Text Content 04', $autoresponders[0]->textContent);
            $this->assertEquals('Html Content 04', $autoresponders[0]->htmlContent);
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(4, count($autoresponders));
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserDetailsActionWithoutParameters()
        {
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserDetailsActionWithoutParameters
         */
        public function testSuperUserDetailsActionWithoutRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Autoresponder 04');
            $this->setGetArray(array('id' => $autoresponderId));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
        }

        /**
         * @depends testSuperUserDetailsActionWithoutRedirectUrl
         */
        public function testSuperUserDetailsActionWithRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Autoresponder 04');
            $redirectUrl     = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
            $this->assertTrue(strpos($content, '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/index">Marketing</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/list">Lists</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/details?id=' . static::$marketingListId .
                                                '">MarketingListName</a> &#47; <span>Autoresponder 04</span></div>') !== false);
            $this->assertTrue(strpos($content, 'Autoresponder 04') !== false);
            $this->assertEquals(3, substr_count($content, 'Autoresponder 04'));
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">Autoresponder 04</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'autoresponders/default/edit?id=' . $autoresponderId) !== false);
            $this->assertTrue(strpos($content, 'autoresponders/default/delete?id=' . $autoresponderId) !== false);
            $this->assertTrue(strpos($content, '<th>Type</th><td colspan="1">Unsubscription</td>') !== false);
            $this->assertTrue(strpos($content, '<th>When to send?</th><td colspan="1">1 month</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">Autoresponder 04</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">Subject 04</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Enable Tracking</th>') !== false);
            $this->assertTrue(strpos($content, '<input id="ytAutoresponder_enableTracking" type="hidden" value="0" '.
                                                'name="Autoresponder[enableTracking]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox disabled">' .
                                                '<input id="Autoresponder_enableTracking" ' .
                                                'name="Autoresponder[enableTracking]" disabled="disabled" value="1" ' .
                                                'type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, 'Text Content 04') !== false);
            $this->assertTrue(strpos($content, 'Html Content 04') !== false);
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserEditActionWithoutParameters()
        {
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserEditActionWithoutParameters
         */
        public function testSuperUserEditActionWithoutRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Autoresponder 04');
            $this->setGetArray(array('id' => $autoresponderId));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
        }

        /**
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserEditAction()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Autoresponder 04');
            $redirectUrl     = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
            $this->assertTrue(strpos($content, '<div class="breadcrumbs">') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/index">Marketing</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/list">Lists</a> &#47; <a href=') !== false);
            $this->assertTrue(strpos($content, 'marketingLists/default/details?id=' . static::$marketingListId .
                                                '">MarketingListName</a> &#47; <span>Autoresponder 04</span></div>') !== false);
            $this->assertTrue(strpos($content, 'Autoresponder 04') !== false);
            $this->assertEquals(3, substr_count($content, 'Autoresponder 04'));
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">Autoresponder 04</span>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_operationType_value" class="required">' .
                                                'Type <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_secondsFromOperation_value" class="required">' .
                                                'When to send? <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_name" class="required">Name ' .
                                                '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_subject" class="required">Subject ' .
                                                '<span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<label for="Autoresponder_enableTracking">Enable Tracking' .
                                                '</label>') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[operationType]" ' .
                                                'id="Autoresponder_operationType_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="1">Subscription</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="2" selected="selected">Unsubscription</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="3">Removal</option>') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[secondsFromOperation]" ' .
                                                'id="Autoresponder_secondsFromOperation_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="3600">1 Hour</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="21600">6 Hours</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="43200">12 Hours</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="86400">1 day</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="259200">3 days</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="604800">1 week</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="1209600">2 weeks</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="2592000" selected="selected">1 month</option>') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_name" name="Autoresponder[name]" type="text" '.
                                                'maxlength="64" value="Autoresponder 04" ') !== false);
            $this->assertTrue(strpos($content, '<input id="Autoresponder_subject" name="Autoresponder[subject]" ' .
                                                'type="text" maxlength="64" value="Subject 04"') !== false);
            $this->assertTrue(strpos($content, '<select name="Autoresponder[contactEmailTemplateNames]" ' .
                                                'id="Autoresponder_contactEmailTemplateNames_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="">Select a template</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 01</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 02</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 03</option>') !== false);
            $this->assertTrue(strpos($content, '>EmailTemplate 04</option>') !== false);
            $this->assertTrue(strpos($content, '<a class="active-tab" href="#tab1">Text Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">Html Content</a>') !== false);
            $this->assertTrue(strpos($content, '<a id="mergetag-guide" class="simple-link" ' .
                                                'href="#">MergeTag Guide</a>') !== false);
            $this->assertTrue(strpos($content, '<textarea id="Autoresponder_textContent" ' .
                                                'name="Autoresponder[textContent]" rows="6" cols="50"') !== false);
            $this->assertTrue(strpos($content, "<textarea id='Autoresponder_htmlContent' " .
                                                "name='Autoresponder[htmlContent]'") !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Cancel</span>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Delete</span>') !== false);

            // modify everything:
            $this->setPostArray(array('Autoresponder' => array(
                                                        'operationType'             => 1,
                                                        'secondsFromOperation'      => 259200,
                                                        'name'                      => 'Autoresponder 040',
                                                        'subject'                   => 'Subject 040',
                                                        'enableTracking'            => 1,
                                                        'contactEmailTemplateNames' => '',
                                                        'textContent'               => 'Text Content 040',
                                                        'htmlContent'               => 'Html Content 040',
                                                    )));
            $resolvedRedirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/edit');
            $autoresponders  = Autoresponder::getByName('Autoresponder 040');
            $this->assertEquals(1, count($autoresponders));
            $this->assertTrue  ($autoresponders[0]->id > 0);
            $this->assertEquals(1, $autoresponders[0]->operationType);
            $this->assertEquals(259200, $autoresponders[0]->secondsFromOperation);
            $this->assertEquals('Subject 040', $autoresponders[0]->subject);
            $this->assertEquals(1, $autoresponders[0]->enableTracking);
            $this->assertEquals('Text Content 040', $autoresponders[0]->textContent);
            $this->assertEquals('Html Content 040', $autoresponders[0]->htmlContent);
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(4, count($autoresponders));
        }

        /**
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserDeleteAction()
        {
            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(4, $autoresponders);
            $autoresponderId = $autoresponders[0]->id;
            $this->setGetArray(array('id' => $autoresponderId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/delete', true);
            $this->assertEmpty($content);

            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(3, $autoresponders);
            $autoresponderId = $autoresponders[0]->id;
            $redirectUrl = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $resolvedRedirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/delete');
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(2, $autoresponders);
        }
    }
?>