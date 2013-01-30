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
            $emailTemplate = new EmailTemplate();
            $emailTemplate->type        = 1;
            $emailTemplate->subject     = 'Test subject';
            $emailTemplate->name        = 'Test Email Template';
            $emailTemplate->htmlContent = 'Test html Content';
            $emailTemplate->textContent = 'Test text Content';
            $this->assertTrue($emailTemplate->save());
            $id = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate = EmailTemplate::getById($id);
            $this->assertEquals(1, $emailTemplate->type);
            $this->assertEquals('Test subject',   $emailTemplate->subject);
            $this->assertEquals('Test Email Template', $emailTemplate->name);
            $this->assertEquals('Test html Content', $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content',   $emailTemplate->textContent);
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
      /*  public function testGetLabel()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Email Template',  $emailTemplate[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Email Templates', $emailTemplate[0]::getModelLabelByTypeAndLanguage('Plural'));
        }*/

        /*public function testDeleteEmailTemplate()
        {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->name        = 'Test Marketing List2';
            $emailTemplate->description = 'Test Description2';
            $this->assertTrue($marketingList->save());
            $emailTemplate = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplate));
          /*  $marketingLists[0]->delete();
            $marketingLists = MarketingList::getAll();
            $this->assertEquals(1, count($marketingLists));
        }*/
    }
?>