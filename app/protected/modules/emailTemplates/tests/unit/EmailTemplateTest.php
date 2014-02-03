<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
    class EmailTemplateTest extends ZurmoBaseTest
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

        public function testCreateAndGetEmailTemplateById()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Test Email Template';
            $emailTemplate->htmlContent     = 'Test html Content';
            $emailTemplate->textContent     = 'Test text Content';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $this->assertTrue($emailTemplate->save());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,    $emailTemplate->type);
            $this->assertEquals('Test subject',                 $emailTemplate->subject);
            $this->assertEquals('Test Email Template',          $emailTemplate->name);
            $this->assertEquals('Test html Content',            $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content',            $emailTemplate->textContent);
            $this->assertEquals(1, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDefaultLanguageGetsPopulated()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Test subject For Language';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Test Email Template For Language';
            $emailTemplate->htmlContent     = 'Test html Content For Language';
            $emailTemplate->textContent     = 'Test text Content For Language';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->language        = "";
            $this->assertTrue($emailTemplate->save());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,                $emailTemplate->type);
            $this->assertEquals('Test subject For Language',                $emailTemplate->subject);
            $this->assertEquals('Test Email Template For Language',         $emailTemplate->name);
            $this->assertEquals('Test html Content For Language',           $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content For Language',           $emailTemplate->textContent);
            $this->assertEquals(Yii::app()->user->userModel->language,      $emailTemplate->language);
            $this->assertEquals(2, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testAtLeastOneContentFieldIsRequired()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testModelClassNameExists()
        {
            // test against a class name that doesn't exist
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'RaNdOmTeXt';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name does not exist.', $errorMessages['modelClassName'][0]);
            // test against a class name thats not a model
            $emailTemplate->modelClassName  = 'TestSuite';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name is not a valid Model class.', $errorMessages['modelClassName'][0]);
            // test against a model that is indeed a class
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(3, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testValidationErrorForInaccessibleModule()
        {
            // test against a user who doesn't have access for provided model's modulename
            $nobody                        = UserTestHelper::createBasicUser('nobody');
            Yii::app()->user->userModel     = $nobody;
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_WORKFLOW;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name access is prohibited.', $errorMessages['modelClassName'][0]);

            // grant him access, now save should work
            $nobody->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue($nobody->save());
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(1, EmailTemplate::getCount()); // this is his only template
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testMergeTagsValidation()
        {
            // test against a invalid merge tags
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content [[TEXT__INVALID^MERGE^TAG]]';
            $emailTemplate->htmlContent     = 'Html Content [[HTMLINVALIDMERGETAG]]';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(2, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertTrue(array_key_exists('htmlContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals(1, count($errorMessages['htmlContent']));
            $this->assertTrue(strpos($errorMessages['textContent'][0], 'TEXT__INVALID^MERGE^TAG') !== false);
            $this->assertTrue(strpos($errorMessages['htmlContent'][0], 'HTMLINVALIDMERGETAG') !== false);
            // test with no merge tags
            $emailTemplate->textContent    = 'Text Content without tags';
            $emailTemplate->htmlContent    = 'Html Content without tags';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(5, EmailTemplate::getCount());
            // test with valid merge tags
            $emailTemplate->textContent    = 'Name : [[FIRST^NAME]] [[LAST^NAME]]';
            $emailTemplate->htmlContent    = '<b>Name : [[FIRST^NAME]] [[LAST^NAME]]</b>';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(5, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDummyHtmlContentThrowsValidationErrorWhenTextContentIsEmpty()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = '';
            $emailTemplate->htmlContent     = "<html>\n<head>\n</head>\n<body>\n</body>\n</html>";
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);

            $emailTemplate->textContent         = 'Text Content';
            $this->assertTrue($emailTemplate->save());
            $this->assertEquals(6, EmailTemplate::getCount());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,    $emailTemplate->type);
            $this->assertEquals('Another Test subject',                 $emailTemplate->subject);
            $this->assertEquals('Another Test Email Template',          $emailTemplate->name);
            $this->assertEquals(null,            $emailTemplate->htmlContent);
            $this->assertEquals('Text Content',            $emailTemplate->textContent);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testHtmlContentGetsSavedCorrectly()
        {
            $randomData                     = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('EmailTemplatesModule',
                                                                                                        'EmailTemplate');
            $htmlContent                    = $randomData['htmlContent'][count($randomData['htmlContent']) -1];
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->htmlContent     = $htmlContent;
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertTrue($emailTemplate->save());
            $emailTemplateId = $emailTemplate->id;
            $emailTemplate->forgetAll();
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals($htmlContent, $emailTemplate->htmlContent);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetEmailTemplateByName()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Test Email Template', $emailTemplate[0]->name);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetLabel()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Email Template',  $emailTemplate[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Email Templates', $emailTemplate[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /*
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDeleteEmailTemplate()
        {
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(7, count($emailTemplates));
            $emailTemplates[0]->delete();
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(6, count($emailTemplates));
        }
    }
?>