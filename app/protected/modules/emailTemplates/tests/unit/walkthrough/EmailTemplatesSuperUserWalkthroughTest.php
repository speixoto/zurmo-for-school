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

    /**
     * Accounts Module Super User Walkthrough.
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

            //Setup test data owned by the super user.
            EmailTemplateTestHelper::createEmailTemplateByName(1, 'Test Subject', 'Test Name', 'Test HtmlContent', 'Test TextContent');
            EmailTemplateTestHelper::createEmailTemplateByName(1, 'Test Subject1', 'Test Name1', 'Test HtmlContent1', 'Test TextContent1');
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
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
        }

        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/list');
            $this->assertTrue(strpos($content, 'Email Templates</title></head>') !== false);
            $this->assertTrue(substr_count($content, '2 result(s)') !== false);
            $this->assertEquals(substr_count($content, 'Test Name'), 2);
            $this->assertEquals(substr_count($content, 'Clark Kent'), 2);
            $this->assertFalse(strpos($content, 'anyMixedAttributes') !== false);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
        }

        public function testSuperUserCreateAction()
        {

            // Create a new emailTemplate and test validator.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'        => 1,
                'name'        => 'New Test EmailTemplate',
                'subject'     => 'New Test Subject')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertTrue(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="1" selected="selected">Workflow</option>') !== false);
            $this->assertTrue(strpos($content, '<option value="2">Contact</option>') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            // Create a new emailTemplate and save it.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'        => 1,
                'name'        => 'New Test EmailTemplate',
                'subject'     => 'New Test Subject',
                'textContent' => 'New Text Content')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplateId = $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertTrue  ($emailTemplate->id > 0);
            $this->assertEquals('New Test Subject', $emailTemplate->subject);
            $this->assertEquals('New Text Content', $emailTemplate->textContent);
            $this->assertTrue  ($emailTemplate->owner == $this->super);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplate->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
        }

        public function testSuperUserEditAction()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'Test Name');
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
                                    'name' => 'New Name',
                                    'subject' => 'New Subject',
                                    'type' => 2,
                                    'htmlContent' => 'New HTML Content',
                                    'textContent' => 'New Text Content')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject', $emailTemplate->subject);
            $this->assertEquals('New Name', $emailTemplate->name);
            $this->assertEquals(2, $emailTemplate->type);
            $this->assertEquals('New Text Content', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content', $emailTemplate->htmlContent);
        }

        public function testSuperUserDetailsAction()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Name');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $types = EmailTemplate::getTypeDropDownArray();
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Type</th><td colspan="1">' . $types[(int)$emailTemplate->type] . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-textcontent">'. $emailTemplate->textContent . '</div>') !== false);
            $this->assertTrue(strpos($content, '<div class="email-template-htmlcontent">' . $emailTemplate->htmlContent . '</div>') !== false);
        }

        public function testSuperUserDeleteAction()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Name');
            //Delete an emailTemplate.
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $content = $this->runControllerWithRedirectExceptionAndGetContent('emailTemplates/default/delete');
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
        }
    }
?>
