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

    class DisplayAttributeForReportFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + float';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetDisplayLabelForCalculations()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'float__Summation';
            $this->assertEquals('Float -(Sum)',    $displayAttribute->label);
            $this->assertEquals('Float -(Sum)',    $displayAttribute->getDisplayLabel());
        }

        public function testSetAndGetDisplayAttribute()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('String',    $displayAttribute->label);
            $displayAttribute->label                       = 'someLabel';
            $this->assertEquals('string',    $displayAttribute->attributeAndRelationData);
            $this->assertEquals('string',    $displayAttribute->attributeIndexOrDerivedType);
            $this->assertEquals('string',    $displayAttribute->getResolvedAttribute());
            $this->assertEquals('String',    $displayAttribute->getDisplayLabel());
            $this->assertEquals('someLabel', $displayAttribute->label);
            $validated = $displayAttribute->validate();
            $this->assertTrue($validated);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $displayAttribute->label             = null;
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $displayAttribute->label             = '';
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $displayAttribute->label             = 'test';
            $displayAttribute->setAttributes(array('label' => ''));
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
        }

        public function testGetDisplayElementType()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'boolean';
            $this->assertEquals('CheckBox',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $this->assertEquals('Date',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime';
            $this->assertEquals('DateTime',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'float';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'integer';
            $this->assertEquals('Integer',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $this->assertEquals('Phone',                     $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'textArea';
            $this->assertEquals('TextArea',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'url';
            $this->assertEquals('Url',                       $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'currencyValue';
            $this->assertEquals('CurrencyValue',             $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dropDown';
            $this->assertEquals('DropDown',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'radioDropDown';
            $this->assertEquals('RadioDropDown',             $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'multiDropDown';
            $this->assertEquals('MultiSelectDropDown',       $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'tagCloud';
            $this->assertEquals('TagCloud',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $this->assertEquals('Email',                     $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'FullName';
            $this->assertEquals('FullName',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'likeContactState';
            $this->assertEquals('ContactState',              $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'calculated';
            $this->assertEquals('CalculatedNumber',          $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'createdByUser__User';
            $this->assertEquals('User',                      $displayAttribute->getDisplayElementType());
        }

        public function testGetDisplayElementTypeDisplayCalculationsAndGroupModifiers()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Minimum';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'float__Minimum';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'date__Minimum';
            $this->assertEquals('Date',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Minimum';
            $this->assertEquals('DateTime',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'currencyValue__Minimum';
            $this->assertEquals('CalculatedCurrencyValue',   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Day';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Week';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Month';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Quarter';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Year';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
        }

        public function testResolveValueAsLabelForHeaderCell()
        {
            //todO:
            //$this->fail();
        }

        public function testGetDisplayLabel()
        {
            //todo: test owned like primary address -> street1.  normal, and various types of related ones.
            //$this->fail();
        }
    }
?>