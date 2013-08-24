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

    class WorkflowDocumentationTest extends WorkflowBaseTest
    {
        protected static $jimmy;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist();
            ContactsModule::loadStartingData();
            $jimmy  = UserTestHelper::createBasicUserWithEmailAddress('jimmy');
            self::$jimmy  = $jimmy;
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        /**
         * A simple workflow that is only triggered on a new account being created.  If the owner is jimmy it means
         * that the description will be updated with some text.
         */
        public function testUpdateDescriptionWhenAccountIsCreatedAndOwnerIsJim()
        {
            $super = User::getByUsername('super');
            $contactStates = ContactState::getAll();
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (1);
            $workflow->setModuleClassName('AccountsModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add Trigger
            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'owner';
            $trigger->value                       = self::$jimmy->id;
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('Opportunity', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array(  'description' => array('shouldSetValue'    => '1',
                                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'  => 'my new description')
            );
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $account = AccountTestHelper::createAccountByNameForOwner('my account', self::$jimmy);
            $this->assertTrue($account->id > 0);
            $this->assertEquals('my new description', $account->description);
        }

        public function testCreateARelatedContactOnAnOpportunityWhenOpportunityBecomesClosedWon()
        {
            $super = User::getByUsername('super');
            $contactStates = ContactState::getAll();
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (1);
            $workflow->setModuleClassName('OpportunitiesModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add Trigger
            $trigger     = new TriggerForWorkflowForm('OpportunitiesModule', 'Opportunity', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'stage';
            $trigger->value                       = 'Prospecting';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('Opportunity', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'contacts';
            $attributes                   = array(  'lastName' => array('shouldSetValue'    => '1',
                                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'  => 'smith'),
                                                    'firstName' => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => 'john'),
                                                    'owner__User'     => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => Yii::app()->user->userModel->id),
                                                    'state'       => array('shouldSetValue'    => '1',
                                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'  => $contactStates[0]->id),
            );
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('some opp', $super);
            $this->assertTrue($opportunity->id > 0);
            $this->assertEquals(0, $opportunity->contacts->count());
            //Change opportunity to  Prospecting
            $opportunity->stage->value = 'Prospecting';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $opportunity));
            $saved = $opportunity->save();
            $this->assertTrue($saved);
            $this->assertEquals(1,       $opportunity->contacts->count());
            $this->assertEquals('smith', $opportunity->contacts[0]->lastName);
        }

        public function testAWorkflowProcess()
        {
            //todo: write a full set of tests to document workflow
        }

        public function testUpdateRelatedCatalogItemOnAProductBySellPriceCriteria()
        {
            $super = User::getByUsername('super');
            $contactStates = ContactState::getAll();
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (1);
            $workflow->setModuleClassName('ProductsModule');
            $workflow->setName           ('myFirstProductWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add Trigger
            $trigger     = new TriggerForWorkflowForm('ProductsModule', 'Product', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'sellPrice';
            $trigger->value                       = 600;
            $trigger->operator                    = 'greaterThanOrEqualTo';
            $workflow->addTrigger($trigger);
            //Add action
            $currencies                   = Currency::getAll();
            $action                       = new ActionForWorkflowForm('Product', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'productTemplate';
            $attributes                   = array(  'description'    => array('shouldSetValue'    => '1',
                                                        'type'       => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'      => 'Set Price'),
                                                    'priceFrequency' => array('shouldSetValue'    => '1',
                                                        'type'       => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'      => 2),
                                                    'listPrice'      => array('shouldSetValue'    => '1',
                                                        'type'       => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'      => 800,
                                                        'currencyId' => $currencies[0]->id),
                                                    'cost'           => array('shouldSetValue'    => '1',
                                                        'type'       => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'      => 700,
                                                        'currencyId' => $currencies[0]->id),
                                                );
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $productTemplate  = ProductTemplateTestHelper::createProductTemplateByName('superProductTemplate');
            $productTemplates = ProductTemplate::getByName('superProductTemplate');
            $product = ProductTestHelper::createProductByNameForOwner('Test Product', $super);
            $this->assertTrue($product->id > 0);
            $product->productTemplate = $productTemplates[0];

            //Change product sell price
            $product->sellPrice->value = 650;
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $product));
            $saved = $product->save();
            $this->assertTrue($saved);

            $productId = $product->id;
            $product->forget();

            $product = Product::getById($productId);
            $this->assertEquals('Set Price', $product->productTemplate->description);
            $this->assertEquals(2, $product->productTemplate->priceFrequency);
            $this->assertEquals(700, $product->productTemplate->cost->value);
            $this->assertEquals(800, $product->productTemplate->listPrice->value);
        }

        /**
         * Tests that a bug involving createdByUser is resolved. The issue was that createdByUser is not set until
         * after the workflow observer is called beforeSave. This behavior was changed and now this test passes
         */
        public function testProcessBeforeSaveOnCreatedByUserEquals()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('AccountsModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add trigger
            $trigger = new TriggerForWorkflowForm('AccountsTestModule', 'Account', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'createdByUser';
            $trigger->value                       = Yii::app()->user->userModel->id;
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('Account', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Confirm that the workflow processes and the attribute gets updated
            $model = new Account();
            $model->name   = 'aValue';
            $this->assertTrue($model->save());
            $this->assertEquals('jason', $model->name);
        }

        /**
         * A test to show that the modifiedByUser works ok as a trigger with 'equals' on a newly created model.
         * @see testProcessBeforeSaveOnCreatedByUserEquals
         */
        public function testProcessBeforeSaveOnModifiedByUserEquals()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('AccountsModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add trigger
            $trigger = new TriggerForWorkflowForm('AccountsTestModule', 'Account', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'modifiedByUser';
            $trigger->value                       = Yii::app()->user->userModel->id;
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('Account', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Confirm that the workflow processes and the attribute gets updated
            $model = new Account();
            $model->name   = 'aValue';
            $this->assertTrue($model->save());
            $this->assertEquals('jason', $model->name);
        }
    }
?>