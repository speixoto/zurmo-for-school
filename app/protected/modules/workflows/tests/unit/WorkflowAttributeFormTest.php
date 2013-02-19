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

    class WorkflowAttributeFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCheckBoxWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new CheckBoxWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = true;
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testContactStateWorkflowAttributeFormSetGetAndValidate()
        {
            $contactStates = ContactState::getAll();
            $this->assertTrue($contactStates[0]->id > 0);
            $contactState  = $contactStates[0];
            $form        = new ContactStateWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = $contactState->id;
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testCurrencyValueWorkflowAttributeFormSetGetAndValidate()
        {
            $currency             = Currency::getByCode('USD');
            $form                 = new CurrencyValueWorkflowActionAttributeForm();
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value          = 362.24;
            $form->currencyId     = $currency->id;
            $form->currencyIdType = CurrencyValueWorkflowActionAttributeForm::CURRENCY_ID_TYPE_STATIC;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        public function testDateWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new DateWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '12-02-24';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testDateTimeWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new DateTimeWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '12-02-24 03:00:00';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testDecimalWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new DecimalWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 44.12;
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new DropDownWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'Static 1';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testEmailAddressWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new EmailAddressWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'info@zurmo.com';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testIntegerWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new IntegerWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 12;
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testMultiDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new MultiDropDownWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = array('Multi Value 1', 'Multi Value 2');
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testPhoneWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new PhoneWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '1112223344';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testRadioDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new RadioDropDownWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'Radio Static 1';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testTagCloudWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new TagCloudWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = array('Tag Value 1', 'Tag Value 2');
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }


        public function testTextWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new TextWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'jason';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testUserWorkflowAttributeFormSetGetAndValidate()
        {
            $bobby       = User::getByUsername('bobby');
            $form        = new UserWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = $bobby;
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testTextAreaWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new TextAreaWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'a description';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        public function testUrlWorkflowAttributeFormSetGetAndValidate()
        {
            $form        = new UrlWorkflowActionAttributeForm();
            $form->type  = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'http://www.zurmo.com';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }
    }
?>