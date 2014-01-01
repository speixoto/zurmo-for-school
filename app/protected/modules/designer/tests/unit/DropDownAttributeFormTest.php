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

    class DropDownAttributeFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testValidateCustomFieldDataData()
        {
            //First test a clean array with no errors.
            $form = new DropDownAttributeForm();
            $form->customFieldDataData   = array('a', 'b', 'c');
            $form->customFieldDataLabels = array('fr' => array('Afr', 'Bfr', 'Cfr'), 'de' => array('Afr', 'Bfr', 'Cfr'));
            $form->validateCustomFieldDataData('customFieldDataData', null);
            $errors = $form->getErrors();
            $this->assertEquals(0, count($errors));

            //First test a duplicate value that is of a different case and one of the same case
            $form = new DropDownAttributeForm();
            $form->customFieldDataData = array('a', 'b', 'c', 'C', 'b');
            $form->validateCustomFieldDataData('customFieldDataData', null);
            $errors = $form->getErrors();
            $this->assertEquals(1, count($errors));
            $compareData = array(0 => 'Each item must be uniquely named and the following are not: C, b');
            $this->assertEquals($compareData, $errors['customFieldDataData']);

           //Test the blank values for the blank value
            $form = new DropDownAttributeForm();
            $form->customFieldDataData = array('a', '', 'c');
            $form->validateCustomFieldDataData('customFieldDataData', null);
            $errors = $form->getErrors();
            $this->assertEquals(1, count($errors));
            $compareData = array(0 => 'Value cannot be blank.');
            $this->assertEquals($compareData, $errors['customFieldDataData']);
        }

        public function testValidateAttributeNameDoesNotExists()
        {
            Yii::app()->user->userModel             = User::getByUsername('super');
            $compareAttributeLabels                 = array(
                                                            'de' => 'sameattribute de',
                                                            'en' => 'sameattribute en',
                                                        );
            $customFieldDataData                    = array('a', 'b', 'c');
            $customFieldDataLabels                  = array('fr' => array('Afr', 'Bfr', 'Cfr'), 'de' => array('Ade', 'Bde', 'Cde'));
            $attributeForm                          = new DropDownAttributeForm();
            $attributeForm->attributeName           = 'same';
            $attributeForm->attributeLabels         = $compareAttributeLabels;
            $attributeForm->customFieldDataData     = $customFieldDataData;
            $attributeForm->customFieldDataLabels   = $customFieldDataLabels;

            $modelAttributesAdapterClassName        = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                                = new $modelAttributesAdapterClassName(new Account());
            $adapter->setAttributeMetadataFromForm($attributeForm);

            $attributeForm                          = new DropDownAttributeForm();
            $attributeForm->attributeName           = 'same';
            $attributeForm->attributeLabels         = $compareAttributeLabels;
            $attributeForm->customFieldDataData     = $customFieldDataData;
            $attributeForm->customFieldDataLabels   = $customFieldDataLabels;
            $attributeForm->modelClassName          = 'Contact';
            $attributeForm->setScenario('createAttribute');
            $attributeForm->validate();
            $this->assertContains('A field with this name and data is already used in another module',
                                  $attributeForm->getError('attributeName'));
        }
    }
?>