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

    class DataToWorkflowUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            UserTestHelper::createBasicUser('bobby');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveOnSaveWorkflowByWizardPostData()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array('description'       => 'someDescription',
                                                      'name'              => 'someName',
                                                      'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                      'triggersStructure' => '1 AND 2',
                                                      'moduleClassName'   => 'WorkflowsTestModule');
            DataToWorkflowUtil::resolveWorkflowByWizardPostData($workflow, $data, 'OnSaveWorkflowWizardForm');
            $this->assertEquals('someDescription',         $workflow->getDescription());
            $this->assertEquals('someName',                $workflow->getName());
            $this->assertEquals(Workflow::TRIGGER_ON_NEW,  $workflow->getTriggerOn());
            $this->assertEquals('1 AND 2',                 $workflow->getTriggersStructure());
            $this->assertEquals('WorkflowsTestModule',     $workflow->getModuleClassName());
        }

        public function testResolveByTimeWorkflowByWizardPostData()
        {
         //todO: switch to byTime, and check timeTriggerAttribute... make sure that works
            /**
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array('description'       => 'someDescription',
                'name'              => 'someName',
                'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                'triggersStructure' => '1 AND 2',
                'moduleClassName'   => 'WorkflowsTestModule');
            DataToWorkflowUtil::resolveWorkflowByWizardPostData($workflow, $data, 'OnSaveWorkflowWizardForm');
            $this->assertEquals('someDescription',     $workflow->getDescription());
            $this->assertEquals('someName',            $workflow->getName());
            $this->assertEquals(Workflow::TRIGGER_ON_NEW,  $workflow->getTriggerOn());
            $this->assertEquals('1 AND 2',             $workflow->getTriggersStructure());
            $this->assertEquals('WorkflowsTestModule', $workflow->getModuleClassName());
         * **/
        }

        /**
         * @depends testResolveByTimeWorkflowByWizardPostData
         */
        public function testResolveTriggers()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS][] = array('attributeIndexOrDerivedType' => 'date',
                'operator'                    => null,
                'valueType'                   => 'Between',
                'value'                       => '2/24/12',
                'secondValue'                 => '2/28/12');
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS][] = array('attributeIndexOrDerivedType' => 'string',
                'operator'                    => OperatorRules::TYPE_EQUALS,
                'value'                       => 'something');
            DataToWorkflowUtil::resolveTriggers($data, $workflow);
            $triggers = $workflow->getTriggers();
            $this->assertCount(2, $triggers);
            $this->assertEquals('2012-02-24',                  $triggers[0]->value);
            $this->assertEquals('Between',                     $triggers[0]->valueType);
            $this->assertEquals('2012-02-28',                  $triggers[0]->secondValue);
            $this->assertEquals('something',                   $triggers[1]->value);
            $this->assertEquals(OperatorRules::TYPE_EQUALS,    $triggers[1]->operator);

            //Test removing triggers when none are specified
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $workflow->addTrigger($trigger);
            $triggers = $workflow->getTriggers();
            $this->assertCount(1, $triggers);
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS] = array();
            DataToWorkflowUtil::resolveTriggers($data, $workflow);
            $triggers = $workflow->getTriggers();
            $this->assertCount(0, $triggers);
        }

        /**
         * @depends testResolveTriggers
         */
        public function testResolveTimeTrigger()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_TIME_TRIGGER] = array('attributeIndexOrDerivedType' => 'string',
                'operator'                    => OperatorRules::TYPE_EQUALS,
                'value'                       => '514',
                'durationSeconds'             => '333');
            DataToWorkflowUtil::resolveTimeTrigger($data, $workflow);
            $trigger = $workflow->getTimeTrigger();
            $this->assertEquals('514',                      $trigger->value);
            $this->assertEquals('333',                      $trigger->durationSeconds);
            $this->assertEquals(OperatorRules::TYPE_EQUALS, $trigger->operator);
        }

        /**
         * @depends testResolveTimeTrigger
         */
        public function testResolveTriggersAndDateConvertsProperlyToDbFormat()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS][] = array('attributeIndexOrDerivedType' => 'date',
                                                                  'operator'                    => null,
                                                                  'valueType'                   => 'Between',
                                                                  'value'                       => '2/24/12',
                                                                  'secondValue'                 => '2/28/12');
            DataToWorkflowUtil::resolveTriggers($data, $workflow);
            $triggers = $workflow->getTriggers();
            $this->assertCount(1, $triggers);
            $this->assertEquals('2012-02-24', $triggers[0]->value);
            $this->assertEquals('Between',    $triggers[0]->valueType);
            $this->assertEquals('2012-02-28', $triggers[0]->secondValue);
        }

        /**
         * @depends testResolveTriggersAndDateConvertsProperlyToDbFormat
         */
        public function testSanitizeTriggersData()
        {
            //test specifically for date/dateTime conversion from local to db format.
            $triggersData         = array();
            $triggersData[0]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12');
            $triggersData[1]      = array('attributeIndexOrDerivedType' => 'dateTime', 'value' => '2/25/12');
            $triggersData[2]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12',
                                          'secondValue'                 => '2/28/12');
            $sanitizedTriggerData = DataToWorkflowUtil::sanitizeTriggersData('WorkflowsTestModule',
                                                                             Workflow::TYPE_ON_SAVE, $triggersData);
            $this->assertEquals('2012-02-24', $sanitizedTriggerData[0]['value']);
            $this->assertEquals('2012-02-25', $sanitizedTriggerData[1]['value']);
            $this->assertEquals('2012-02-24', $sanitizedTriggerData[2]['value']);
            $this->assertEquals('2012-02-28', $sanitizedTriggerData[2]['secondValue']);
        }

        /**
         * @depends testSanitizeTriggersData
         */
        public function testResolveUpdateActionWithStaticValues()
        {
            $contactStates = ContactState::getAll();
            $this->assertTrue($contactStates[0]->id > 0);
            $contactState  = $contactStates[0];
            $currency = Currency::getByCode('USD');
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type'] = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES] =
                array(
                    'boolean'       => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '1'),
                    'boolean2'      => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '0'),
                    'currencyValue' => array('shouldSetValue'    => '1',
                        'type'         => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'        => '362.24',
                        'currencyId'   => $currency->id),
                    'date'          => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '2/24/12'),
                    'dateTime'      => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '2/24/12 03:00 AM'),
                    'dropDown'      => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'Value 1'),
                    'float'         => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '54.25'),
                    'integer'       => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '32'),
                    'likeContactState' => array('shouldSetValue' => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => $contactState->id),
                    'multiDropDown' => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => array('Multi Value 1', 'Multi Value 2')),
                    'owner'         => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => $bobby->id),
                    'phone'         => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '8471112222'),
                    'primaryAddress___street1' => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => '123 Main Street'),
                    'primaryEmail___emailAddress' => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'info@zurmo.com'),
                    'radioDropDown' => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'Radio Value 1'),
                    'string'        => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'jason'),
                    'tagCloud' => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => array('Tag Value 1', 'Tag Value 2')),
                    'textArea'      => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'some description'),
                    'url'      => array('shouldSetValue'    => '1',
                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                        'value'  => 'http://www.zurmo.com'),
                );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_SELF, $actions[0]->type);
            $this->assertEquals(19,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('boolean') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('boolean')->type);
            $this->assertEquals('1', $actions[0]->getActionAttributeFormByName('boolean')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('boolean2') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('boolean2')->type);
            $this->assertEquals('0', $actions[0]->getActionAttributeFormByName('boolean2')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('currencyValue') instanceof CurrencyValueWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('currencyValue')->type);
            $this->assertEquals(362.24,      $actions[0]->getActionAttributeFormByName('currencyValue')->value);
            $this->assertEquals($currency->id,  $actions[0]->getActionAttributeFormByName('currencyValue')->currencyId);
            $this->assertEquals('Static',  $actions[0]->getActionAttributeFormByName('currencyValue')->currencyIdType);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('date')->type);
            $this->assertEquals('2012-02-24',  $actions[0]->getActionAttributeFormByName('date')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('dateTime')->type);
            $compareDateTime = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('2/24/12 03:00 AM');
            $this->assertEquals($compareDateTime,  $actions[0]->getActionAttributeFormByName('dateTime')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('dropDown')->type);
            $this->assertEquals('Value 1',  $actions[0]->getActionAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('float') instanceof DecimalWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('float')->type);
            $this->assertEquals('54.25',  $actions[0]->getActionAttributeFormByName('float')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('integer') instanceof IntegerWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('integer')->type);
            $this->assertEquals('32',  $actions[0]->getActionAttributeFormByName('integer')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('likeContactState') instanceof ContactStateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('likeContactState')->type);
            $this->assertEquals($contactState->id,  $actions[0]->getActionAttributeFormByName('likeContactState')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('multiDropDown') instanceof MultiSelectDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('multiDropDown')->type);
            $this->assertEquals(array('Multi Value 1', 'Multi Value 2'),  $actions[0]->getActionAttributeFormByName('multiDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('owner')->type);
            $this->assertEquals($bobby->id,  $actions[0]->getActionAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('phone') instanceof PhoneWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('phone')->type);
            $this->assertEquals('8471112222',  $actions[0]->getActionAttributeFormByName('phone')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('primaryAddress___street1') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('primaryAddress___street1')->type);
            $this->assertEquals('123 Main Street',  $actions[0]->getActionAttributeFormByName('primaryAddress___street1')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress') instanceof EmailWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress')->type);
            $this->assertEquals('info@zurmo.com',  $actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('radioDropDown')->type);
            $this->assertEquals('Radio Value 1',  $actions[0]->getActionAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getActionAttributeFormByName('string')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('tagCloud') instanceof TagCloudWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('tagCloud')->type);
            $this->assertEquals(array('Tag Value 1', 'Tag Value 2'),  $actions[0]->getActionAttributeFormByName('tagCloud')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('textArea') instanceof TextAreaWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('textArea')->type);
            $this->assertEquals('some description',  $actions[0]->getActionAttributeFormByName('textArea')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('url') instanceof UrlWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('url')->type);
            $this->assertEquals('http://www.zurmo.com',  $actions[0]->getActionAttributeFormByName('url')->value);
        }

        /**
         * @depends testResolveUpdateActionWithStaticValues
         */
        public function testResolveUpdateActionWithDynamicValues()
        {
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type'] = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES] =
            array(
                'date'          => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE,
                    'value'  => '-86400'),
                'date2'          => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE,
                    'value'  => '86400'),
                'date3'         => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE,
                    'value'  => '-86400'),
                'date4'         => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE,
                    'value'  => '86400'),
                'dateTime'          => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME,
                    'value'  => '-3600'),
                'dateTime2'     => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME,
                    'value'  => '3600'),
                'dateTime3'     => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME,
                    'value'  => '-7200'),
                'dateTime4'         => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME,
                    'value'  => '7200'),
                'dropDown'      => array('shouldSetValue'    => '1',
                    'type'   => DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS,
                    'value'  => '2'),
                'owner'         => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_CREATED_BY_USER),
                'radioDropDown' => array('shouldSetValue'    => '1',
                    'type'   => RadioDropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS,
                    'value'  => '-2'),
                'user'          => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_MODIFIED_BY_USER),
                'user2'         => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_TRIGGERED_BY_USER),
            );
            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_SELF, $actions[0]->type);
            $this->assertEquals(13,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getActionAttributeFormByName('date')->type);
            $this->assertEquals(-86400,  $actions[0]->getActionAttributeFormByName('date')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date2') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getActionAttributeFormByName('date2')->type);
            $this->assertEquals(86400,  $actions[0]->getActionAttributeFormByName('date2')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date3') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getActionAttributeFormByName('date3')->type);
            $this->assertEquals(-86400,  $actions[0]->getActionAttributeFormByName('date3')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date4') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getActionAttributeFormByName('date4')->type);
            $this->assertEquals(86400,  $actions[0]->getActionAttributeFormByName('date4')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getActionAttributeFormByName('dateTime')->type);
            $this->assertEquals(-3600,  $actions[0]->getActionAttributeFormByName('dateTime')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime2') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getActionAttributeFormByName('dateTime2')->type);
            $this->assertEquals(3600,  $actions[0]->getActionAttributeFormByName('dateTime2')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime3') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getActionAttributeFormByName('dateTime3')->type);
            $this->assertEquals(-7200,  $actions[0]->getActionAttributeFormByName('dateTime3')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime4') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getActionAttributeFormByName('dateTime4')->type);
            $this->assertEquals(7200,  $actions[0]->getActionAttributeFormByName('dateTime4')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getActionAttributeFormByName('dropDown')->type);
            $this->assertEquals(2, $actions[0]->getActionAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicCreatedByUser',    $actions[0]->getActionAttributeFormByName('owner')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getActionAttributeFormByName('radioDropDown')->type);
            $this->assertEquals(-2, $actions[0]->getActionAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('user') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicModifiedByUser',    $actions[0]->getActionAttributeFormByName('user')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('user')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('user2') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicTriggeredByUser',    $actions[0]->getActionAttributeFormByName('user2')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('user2')->value);
        }

        /**
         * @depends testResolveUpdateActionWithDynamicValues
         */
        public function testResolveUpdateRelatedActionWithStaticValues()
        {
            $contactStates = ContactState::getAll();
            $this->assertTrue($contactStates[0]->id > 0);
            $contactState  = $contactStates[0];
            $currency = Currency::getByCode('USD');
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']           = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']       = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES]     =
            array(
                'boolean'       => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '1'),
                'boolean2'      => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '0'),
                'currencyValue' => array('shouldSetValue'    => '1',
                    'type'         => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'        => '362.24',
                    'currencyId'   => $currency->id),
                'date'          => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '2/24/12'),
                'dateTime'      => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '2/24/12 03:00 AM'),
                'dropDown'      => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'Value 1'),
                'float'         => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '54.25'),
                'integer'       => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '32'),
                'likeContactState' => array('shouldSetValue' => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => $contactState->id),
                'multiDropDown' => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => array('Multi Value 1', 'Multi Value 2')),
                'owner'         => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => $bobby->id),
                'phone'         => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '8471112222'),
                'primaryAddress___street1' => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => '123 Main Street'),
                'primaryEmail___emailAddress' => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'info@zurmo.com'),
                'radioDropDown' => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'Radio Value 1'),
                'string'        => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'jason'),
                'tagCloud' => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => array('Tag Value 1', 'Tag Value 2')),
                'textArea'      => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'some description'),
                'url'      => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'http://www.zurmo.com'),
            );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_RELATED, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL, $actions[0]->relationFilter);

            $this->assertEquals(19,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('boolean') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('boolean')->type);
            $this->assertEquals('1', $actions[0]->getActionAttributeFormByName('boolean')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('boolean2') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('boolean2')->type);
            $this->assertEquals('0', $actions[0]->getActionAttributeFormByName('boolean2')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('currencyValue') instanceof CurrencyValueWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('currencyValue')->type);
            $this->assertEquals(362.24,      $actions[0]->getActionAttributeFormByName('currencyValue')->value);
            $this->assertEquals($currency->id,  $actions[0]->getActionAttributeFormByName('currencyValue')->currencyId);
            $this->assertEquals('Static',  $actions[0]->getActionAttributeFormByName('currencyValue')->currencyIdType);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('date')->type);
            $this->assertEquals('2012-02-24',  $actions[0]->getActionAttributeFormByName('date')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('dateTime')->type);
            $compareDateTime = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('2/24/12 03:00 AM');
            $this->assertEquals($compareDateTime,  $actions[0]->getActionAttributeFormByName('dateTime')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('dropDown')->type);
            $this->assertEquals('Value 1',  $actions[0]->getActionAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('float') instanceof DecimalWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('float')->type);
            $this->assertEquals('54.25',  $actions[0]->getActionAttributeFormByName('float')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('integer') instanceof IntegerWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('integer')->type);
            $this->assertEquals('32',  $actions[0]->getActionAttributeFormByName('integer')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('likeContactState') instanceof ContactStateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('likeContactState')->type);
            $this->assertEquals($contactState->id,  $actions[0]->getActionAttributeFormByName('likeContactState')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('multiDropDown') instanceof MultiSelectDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('multiDropDown')->type);
            $this->assertEquals(array('Multi Value 1', 'Multi Value 2'),  $actions[0]->getActionAttributeFormByName('multiDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getActionAttributeFormByName('owner')->type);
            $this->assertEquals($bobby->id,  $actions[0]->getActionAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('phone') instanceof PhoneWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('phone')->type);
            $this->assertEquals('8471112222',  $actions[0]->getActionAttributeFormByName('phone')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('primaryAddress___street1') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('primaryAddress___street1')->type);
            $this->assertEquals('123 Main Street',  $actions[0]->getActionAttributeFormByName('primaryAddress___street1')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress') instanceof EmailWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress')->type);
            $this->assertEquals('info@zurmo.com',  $actions[0]->getActionAttributeFormByName('primaryEmail___emailAddress')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('radioDropDown')->type);
            $this->assertEquals('Radio Value 1',  $actions[0]->getActionAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getActionAttributeFormByName('string')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('tagCloud') instanceof TagCloudWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('tagCloud')->type);
            $this->assertEquals(array('Tag Value 1', 'Tag Value 2'),  $actions[0]->getActionAttributeFormByName('tagCloud')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('textArea') instanceof TextAreaWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('textArea')->type);
            $this->assertEquals('some description',  $actions[0]->getActionAttributeFormByName('textArea')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('url') instanceof UrlWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('url')->type);
            $this->assertEquals('http://www.zurmo.com',  $actions[0]->getActionAttributeFormByName('url')->value);
        }

        /**
         * @depends testResolveUpdateRelatedActionWithStaticValues
         */
        public function testResolveUpdateRelatedActionWithDynamicValues()
        {
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']           = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']       = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES]     =
            array(
                'date'          => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE,
                    'value'  => '-86400'),
                'date2'          => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE,
                    'value'  => '86400'),
                'date3'         => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE,
                    'value'  => '-86400'),
                'date4'         => array('shouldSetValue'    => '1',
                    'type'   => DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE,
                    'value'  => '86400'),
                'dateTime'          => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME,
                    'value'  => '-3600'),
                'dateTime2'     => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME,
                    'value'  => '3600'),
                'dateTime3'     => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME,
                    'value'  => '-7200'),
                'dateTime4'         => array('shouldSetValue'    => '1',
                    'type'   => DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME,
                    'value'  => '7200'),
                'dropDown'      => array('shouldSetValue'    => '1',
                    'type'   => DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS,
                    'value'  => '2'),
                'owner'         => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_CREATED_BY_USER),
                'radioDropDown' => array('shouldSetValue'    => '1',
                    'type'   => RadioDropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS,
                    'value'  => '-2'),
                'user'          => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_MODIFIED_BY_USER),
                'user2'         => array('shouldSetValue'    => '1',
                    'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_TRIGGERED_BY_USER),
            );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_RELATED, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL, $actions[0]->relationFilter);
            $this->assertEquals(13,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getActionAttributeFormByName('date')->type);
            $this->assertEquals(-86400,  $actions[0]->getActionAttributeFormByName('date')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date2') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getActionAttributeFormByName('date2')->type);
            $this->assertEquals(86400,  $actions[0]->getActionAttributeFormByName('date2')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date3') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getActionAttributeFormByName('date3')->type);
            $this->assertEquals(-86400,  $actions[0]->getActionAttributeFormByName('date3')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('date4') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getActionAttributeFormByName('date4')->type);
            $this->assertEquals(86400,  $actions[0]->getActionAttributeFormByName('date4')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getActionAttributeFormByName('dateTime')->type);
            $this->assertEquals(-3600,  $actions[0]->getActionAttributeFormByName('dateTime')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime2') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getActionAttributeFormByName('dateTime2')->type);
            $this->assertEquals(3600,  $actions[0]->getActionAttributeFormByName('dateTime2')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime3') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getActionAttributeFormByName('dateTime3')->type);
            $this->assertEquals(-7200,  $actions[0]->getActionAttributeFormByName('dateTime3')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('dateTime4') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getActionAttributeFormByName('dateTime4')->type);
            $this->assertEquals(7200,  $actions[0]->getActionAttributeFormByName('dateTime4')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getActionAttributeFormByName('dropDown')->type);
            $this->assertEquals(2, $actions[0]->getActionAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicCreatedByUser',    $actions[0]->getActionAttributeFormByName('owner')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getActionAttributeFormByName('radioDropDown')->type);
            $this->assertEquals(-2, $actions[0]->getActionAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getActionAttributeFormByName('user') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicModifiedByUser',    $actions[0]->getActionAttributeFormByName('user')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('user')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('user2') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicTriggeredByUser',    $actions[0]->getActionAttributeFormByName('user2')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('user2')->value);
        }

        /**
         * @depends testResolveUpdateRelatedActionWithDynamicValues
         */
        public function testResolveUpdateRelatedActionWithDynamicValuesSpecificallyDynamicOwnerOfTriggeredModel()
        {
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']           = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']       = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES]     =
                array(
                    'user'          => array('shouldSetValue'    => '1',
                        'type'   => UserWorkflowActionAttributeForm::TYPE_DYNAMIC_OWNER_OF_TRIGGERED_MODEL),
                );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_RELATED, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL, $actions[0]->relationFilter);
            $this->assertEquals(1,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('user') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('OwnerOfTriggeredModel',    $actions[0]->getActionAttributeFormByName('user')->type);
            $this->assertNull($actions[0]->getActionAttributeFormByName('user')->value);
        }

        /**
         * Simple test that does not need to test all attributes because they are tested in the update
         * @depends testResolveUpdateRelatedActionWithDynamicValuesSpecificallyDynamicOwnerOfTriggeredModel
         */
        public function testResolveCreateActionWithValues()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']       = ActionForWorkflowForm::TYPE_CREATE;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']   = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES] =
            array(
                'string' => array('shouldSetValue'    => '1',
                                  'type'              => WorkflowActionAttributeForm::TYPE_STATIC,
                                  'value'             => 'jason'),
                'phone' => array('shouldSetValue'    => '1',
                                 'type'              => WorkflowActionAttributeForm::TYPE_STATIC_NULL,
                                 'value'             => ''),
            );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);

            $this->assertEquals(2,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getActionAttributeFormByName('string')->value);
            $this->assertTrue($actions[0]->getActionAttributeFormByName('phone') instanceof PhoneWorkflowActionAttributeForm);
            $this->assertEquals('StaticNull', $actions[0]->getActionAttributeFormByName('phone')->type);
            $this->assertEquals(null,  $actions[0]->getActionAttributeFormByName('phone')->value);
        }

        /**
         * Simple test that does not need to test all attributes because they are tested in the update related
         * @depends testResolveCreateActionWithValues
         */
        public function testResolveCreateRelatedActionWithValues()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']            = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']       = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relatedModelRelation'] = 'hasMany';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0][ActionForWorkflowForm::ACTION_ATTRIBUTES] =
            array(
                    'name'   => array('shouldSetValue'    => '1',
                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                    'value'  => 'jason'),
            );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE_RELATED, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL, $actions[0]->relationFilter);
            $this->assertEquals('hasMany', $actions[0]->relatedModelRelation);

            $this->assertEquals(1,        $actions[0]->getActionAttributeFormsCount());

            $this->assertTrue($actions[0]->getActionAttributeFormByName('name') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getActionAttributeFormByName('name')->type);
            $this->assertEquals('jason',  $actions[0]->getActionAttributeFormByName('name')->value);
        }

        /**
         *  A person assoicated with the triggered model      TYPE_DYNAMIC_TRIGGERED_MODEL_USER
         *      to,cc,bcc
         *      type of user
         *          user who created model
         *          manager of user who created model
         *          user who last modified model
         *          manager of user who last modified model
         *          user who is assigned model
         *          manager of user who is assigned model
         *
         *  A person assoicated with a related model          TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER
         *      to,cc,bcc
         *      type of user
         *          user who created model
         *          manager of user who created model
         *          user who last modified model
         *          manager of user who last modified model
         *          user who is assigned model
         *          manager of user who is assigned model
         *      relation
         *
         *  All users in a specific role  TYPE_STATIC_ROLE
         *      to,cc,bcc
         *      roleId
         *
         *  User who triggered process    TYPE_DYNAMIC_TRIGGERED_USER
         *      to,cc,bcc
         *
         *  A specific user               TYPE_STATIC_USER
         *      to,cc,bcc
         *      userId
         *
         *  A specific e-mail address     TYPE_STATIC_ADDRESS
         *      to,cc,bcc
         *      toName
         *      toAddress
         *
         *  All users in a specific group TYPE_STATIC_GROUP
         *      to,cc,bcc
         *       groupId
         *
         * @depends testResolveCreateRelatedActionWithValues
         */
        public function testEmailAlertValues()
        {
            $bobby    = User::getByUsername('bobby');
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS][0]['emailTemplateId']          = '5';
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS][0]['sendFromType']             =
                                                                    EmailAlertForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS][0]['sendAfterDurationSeconds'] = '0';
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS][0]['logEmail']                 = '1';
            $data[ComponentForWorkflowForm::TYPE_EMAIL_ALERTS][0][EmailAlertForWorkflowForm::EMAIL_ALERT_RECIPIENTS] =
            array(
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_TO,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_CREATED_BY_USER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_CC,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_CREATED_BY_USER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_BCC,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_MODIFIED_BY_USER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_TO,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_MODIFIED_BY_USER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_CC,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_OWNER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_BCC,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_OWNER),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_TO,
                      'dynamicUserType'   => DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm::DYNAMIC_USER_TYPE_CREATED_BY_USER,
                      'relation'          => 'hasOne'),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_STATIC_ROLE,
                      'recipientType'     => EmailMessageRecipient::TYPE_CC,
                      'roleId'            => '5'),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_DYNAMIC_TRIGGERED_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_BCC),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_STATIC_USER,
                      'recipientType'     => EmailMessageRecipient::TYPE_TO,
                      'userId'            => '6'),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_STATIC_ADDRESS,
                      'recipientType'     => EmailMessageRecipient::TYPE_CC,
                      'toName'            => 'somebody',
                      'toAddress'         => 'someone@zurmo.com'),
                array('type'              => WorkflowEmailAlertRecipientForm::TYPE_STATIC_GROUP,
                      'recipientType'     => EmailMessageRecipient::TYPE_BCC,
                      'groupId'           => '7'),
                    );
            DataToWorkflowUtil::resolveEmailAlerts($data, $workflow);
            $emailAlerts = $workflow->getEmailAlerts();
            $this->assertCount(1,   $emailAlerts);
            $this->assertEquals('5', $emailAlerts[0]->emailTemplateId);
            $this->assertEquals(EmailAlertForWorkflowForm::SEND_FROM_TYPE_DEFAULT, $emailAlerts[0]->sendFromType);
            $this->assertEquals(0,   $emailAlerts[0]->sendAfterDurationSeconds);
            $this->assertEquals('1', $emailAlerts[0]->logEmail);
            $this->assertEquals(12,  $emailAlerts[0]->getEmailAlertRecipientFormsCount());

            $emailAlertRecipients = $emailAlerts[0]->getEmailAlertRecipients();
            $this->assertTrue($emailAlertRecipients[0] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[0]->type);
            $this->assertEquals(1,                           $emailAlertRecipients[0]->recipientType);
            $this->assertEquals('CreatedByUser',             $emailAlertRecipients[0]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[1] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[1]->type);
            $this->assertEquals(2,                           $emailAlertRecipients[1]->recipientType);
            $this->assertEquals('ManagerOfCreatedByUser',    $emailAlertRecipients[1]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[2] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[2]->type);
            $this->assertEquals(3,                           $emailAlertRecipients[2]->recipientType);
            $this->assertEquals('ModifiedByUser',            $emailAlertRecipients[2]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[3] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[3]->type);
            $this->assertEquals(1,                           $emailAlertRecipients[3]->recipientType);
            $this->assertEquals('ManagerOfModifiedByUser',   $emailAlertRecipients[3]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[4] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[4]->type);
            $this->assertEquals(2,                           $emailAlertRecipients[4]->recipientType);
            $this->assertEquals('Owner',                     $emailAlertRecipients[4]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[5] instanceof DynamicTriggeredModelUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelUser', $emailAlertRecipients[5]->type);
            $this->assertEquals(3,                           $emailAlertRecipients[5]->recipientType);
            $this->assertEquals('ManagerOfOwner',            $emailAlertRecipients[5]->dynamicUserType);
            $this->assertTrue($emailAlertRecipients[6] instanceof DynamicTriggeredModelRelationUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredModelRelationUser', $emailAlertRecipients[6]->type);
            $this->assertEquals(1,                                   $emailAlertRecipients[6]->recipientType);
            $this->assertEquals('CreatedByUser',                     $emailAlertRecipients[6]->dynamicUserType);
            $this->assertEquals('hasOne',                            $emailAlertRecipients[6]->relation);
            $this->assertEquals('RelationFilterAll',                 $emailAlertRecipients[6]->relationFilter);
            $this->assertTrue($emailAlertRecipients[7] instanceof StaticRoleWorkflowEmailAlertRecipientForm);
            $this->assertEquals('StaticRole',                $emailAlertRecipients[7]->type);
            $this->assertEquals(2,                           $emailAlertRecipients[7]->recipientType);
            $this->assertEquals(5,                           $emailAlertRecipients[7]->roleId);
            $this->assertTrue($emailAlertRecipients[8] instanceof DynamicTriggeredUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('DynamicTriggeredUser',      $emailAlertRecipients[8]->type);
            $this->assertEquals(3,                           $emailAlertRecipients[8]->recipientType);
            $this->assertTrue($emailAlertRecipients[9] instanceof StaticUserWorkflowEmailAlertRecipientForm);
            $this->assertEquals('StaticUser',                $emailAlertRecipients[9]->type);
            $this->assertEquals(1,                           $emailAlertRecipients[9]->recipientType);
            $this->assertEquals(6,                           $emailAlertRecipients[9]->userId);
            $this->assertTrue($emailAlertRecipients[10] instanceof StaticAddressWorkflowEmailAlertRecipientForm);
            $this->assertEquals('StaticAddress',             $emailAlertRecipients[10]->type);
            $this->assertEquals(2,                           $emailAlertRecipients[10]->recipientType);
            $this->assertEquals('somebody',                  $emailAlertRecipients[10]->toName);
            $this->assertEquals('someone@zurmo.com',         $emailAlertRecipients[10]->toAddress);
            $this->assertTrue($emailAlertRecipients[11] instanceof StaticGroupWorkflowEmailAlertRecipientForm);
            $this->assertEquals('StaticGroup',               $emailAlertRecipients[11]->type);
            $this->assertEquals(3,                           $emailAlertRecipients[11]->recipientType);
            $this->assertEquals(7,                           $emailAlertRecipients[11]->groupId);
        }
    }
?>