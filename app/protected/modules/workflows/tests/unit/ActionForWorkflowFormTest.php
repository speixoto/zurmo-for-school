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

    class ActionForWorkflowFormTest extends ZurmoBaseTest
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

        public function testSetAndGetActionForUpdateAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem');
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE;
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes($attributes);

            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE, $action->type);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForUpdateAction
         */
        public function testSetAndGetActionForUpdateRelatedAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'hasMany2';
            $action->relationFilter       = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $attributes                   = array(
                                            'string'     => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes($attributes);

            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_RELATED,     $action->type);
            $this->assertEquals('hasMany2',                        $action->relation );
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL,     $action->relationFilter);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForUpdateRelatedAction
         */
        public function testSetAndGetActionForCreateAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasMany2';
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes($attributes);

            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE,     $action->type);
            $this->assertEquals('hasMany2',                $action->relation );
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForCreateAction
         */
        public function testSetAndGetActionForCreatingRelatedAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation             = 'hasMany2';
            $action->relationFilter       = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $action->relatedModelRelation = 'hasMany';
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes($attributes);

            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE_RELATED,     $action->type);
            $this->assertEquals('hasMany2',  $action->relation );
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL,     $action->relationFilter);
            $this->assertEquals('hasMany',   $action->relatedModelRelation);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForCreatingRelatedAction
         */
        public function testValidate()
        {
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem');
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('type'  => array('Type cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            //Update type does not require any related information
            $action->type                         = ActionForWorkflowForm::TYPE_UPDATE;
            $validated                           = $action->validate();
            $this->assertTrue($validated);



            //When the type is update_related, related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                        = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relation'        => array('Relation cannot be blank.'),
                                                         'relationFilter'  => array('Relation Filter cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $action->relation                    = 'hasMany2';
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated                           = $action->validate();
            $this->assertTrue($validated);


            //When the type is create, related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                        = ActionForWorkflowForm::TYPE_CREATE;
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relation'  => array('Relation cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $action->relation                    = 'hasMany2';
            $validated                           = $action->validate();
            $this->assertTrue($validated);

            //When the type is create related, additional related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2');
            $action->type                        = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation                    = 'hasMany2';
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relatedModelRelation'  => array('Related Model Relation cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $action->relatedModelRelation        = 'hasOne';
            $validated                           = $action->validate();
            $this->assertTrue($validated);
        }
    }
?>