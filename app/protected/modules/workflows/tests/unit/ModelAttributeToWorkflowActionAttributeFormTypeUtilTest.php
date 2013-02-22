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

    class ModelAttributeToWorkflowActionAttributeFormTypeUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function setup()
        {

            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetValidOperatorTypesForAllAttributeTypes()
        {
            $model              = new WorkflowModelTestItem();
            $this->assertEquals('CheckBox',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'boolean'));
            $this->assertEquals('CurrencyValue',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'currencyValue'));
            $this->assertEquals('Date',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'date'));
            $this->assertEquals('DateTime',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'dateTime'));
            $this->assertEquals('DropDown',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'dropDown'));
            $this->assertEquals('Decimal',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'float'));
            $this->assertEquals('Email',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model->primaryEmail, 'emailAddress'));
            $this->assertEquals('Integer',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'integer'));
            $this->assertEquals('MultiSelectDropDown',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'multiDropDown'));
            $this->assertEquals('Phone',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'phone'));
            $this->assertEquals('RadioDropDown',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'radioDropDown'));
            $this->assertEquals('Text',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'string'));
            $this->assertEquals('TagCloud',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'tagCloud'));
            $this->assertEquals('TextArea',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'textArea'));
            $this->assertEquals('Url',
                                ModelAttributeToWorkflowActionAttributeFormTypeUtil::getType($model, 'url'));
        }
    }
?>