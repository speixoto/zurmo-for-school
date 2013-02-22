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

        public function testResolveWorkflowByWizardPostData()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array('description'       => 'someDescription',
                                                      'name'              => 'someName',
                                                      'triggersStructure' => '1 AND 2',
                                                      'moduleClassName'   => 'WorkflowsTestModule');
            DataToWorkflowUtil::resolveWorkflowByWizardPostData($workflow, $data, 'OnSaveWorkflowWizardForm');
            $this->assertEquals('someDescription',     $workflow->getDescription());
            $this->assertEquals('someName',            $workflow->getName());
            $this->assertEquals('1 AND 2',             $workflow->getTriggersStructure());
            $this->assertEquals('WorkflowsTestModule', $workflow->getModuleClassName());
        }

        /**
         * @depends testResolveWorkflowByWizardPostData
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
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes'] =
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
            $this->assertEquals(19,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('boolean') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('boolean')->type);
            $this->assertEquals('1', $actions[0]->getAttributeFormByName('boolean')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('boolean2') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('boolean2')->type);
            $this->assertEquals('0', $actions[0]->getAttributeFormByName('boolean2')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('currencyValue') instanceof CurrencyValueWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('currencyValue')->type);
            $this->assertEquals(362.24,      $actions[0]->getAttributeFormByName('currencyValue')->value);
            $this->assertEquals($currency->id,  $actions[0]->getAttributeFormByName('currencyValue')->currencyId);
            $this->assertEquals('Static',  $actions[0]->getAttributeFormByName('currencyValue')->currencyIdType);

            $this->assertTrue($actions[0]->getAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('date')->type);
            $this->assertEquals('2012-02-24',  $actions[0]->getAttributeFormByName('date')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('dateTime')->type);
            $compareDateTime = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('2/24/12 03:00 AM');
            $this->assertEquals($compareDateTime,  $actions[0]->getAttributeFormByName('dateTime')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('dropDown')->type);
            $this->assertEquals('Value 1',  $actions[0]->getAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('float') instanceof DecimalWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('float')->type);
            $this->assertEquals('54.25',  $actions[0]->getAttributeFormByName('float')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('integer') instanceof IntegerWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('integer')->type);
            $this->assertEquals('32',  $actions[0]->getAttributeFormByName('integer')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('likeContactState') instanceof ContactStateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('likeContactState')->type);
            $this->assertEquals($contactState->id,  $actions[0]->getAttributeFormByName('likeContactState')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('multiDropDown') instanceof MultiSelectDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('multiDropDown')->type);
            $this->assertEquals(array('Multi Value 1', 'Multi Value 2'),  $actions[0]->getAttributeFormByName('multiDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('owner')->type);
            $this->assertEquals($bobby->id,  $actions[0]->getAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('phone') instanceof PhoneWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('phone')->type);
            $this->assertEquals('8471112222',  $actions[0]->getAttributeFormByName('phone')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('primaryAddress___street1') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('primaryAddress___street1')->type);
            $this->assertEquals('123 Main Street',  $actions[0]->getAttributeFormByName('primaryAddress___street1')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('primaryEmail___emailAddress') instanceof EmailWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('primaryEmail___emailAddress')->type);
            $this->assertEquals('info@zurmo.com',  $actions[0]->getAttributeFormByName('primaryEmail___emailAddress')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('radioDropDown')->type);
            $this->assertEquals('Radio Value 1',  $actions[0]->getAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getAttributeFormByName('string')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('tagCloud') instanceof TagCloudWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('tagCloud')->type);
            $this->assertEquals(array('Tag Value 1', 'Tag Value 2'),  $actions[0]->getAttributeFormByName('tagCloud')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('textArea') instanceof TextAreaWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('textArea')->type);
            $this->assertEquals('some description',  $actions[0]->getAttributeFormByName('textArea')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('url') instanceof UrlWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('url')->type);
            $this->assertEquals('http://www.zurmo.com',  $actions[0]->getAttributeFormByName('url')->value);
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
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes'] =
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
            $this->assertEquals(13,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getAttributeFormByName('date')->type);
            $this->assertEquals(-86400,  $actions[0]->getAttributeFormByName('date')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date2') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getAttributeFormByName('date2')->type);
            $this->assertEquals(86400,  $actions[0]->getAttributeFormByName('date2')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date3') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getAttributeFormByName('date3')->type);
            $this->assertEquals(-86400,  $actions[0]->getAttributeFormByName('date3')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date4') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getAttributeFormByName('date4')->type);
            $this->assertEquals(86400,  $actions[0]->getAttributeFormByName('date4')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getAttributeFormByName('dateTime')->type);
            $this->assertEquals(-3600,  $actions[0]->getAttributeFormByName('dateTime')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime2') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getAttributeFormByName('dateTime2')->type);
            $this->assertEquals(3600,  $actions[0]->getAttributeFormByName('dateTime2')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime3') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getAttributeFormByName('dateTime3')->type);
            $this->assertEquals(-7200,  $actions[0]->getAttributeFormByName('dateTime3')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime4') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getAttributeFormByName('dateTime4')->type);
            $this->assertEquals(7200,  $actions[0]->getAttributeFormByName('dateTime4')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getAttributeFormByName('dropDown')->type);
            $this->assertEquals(2, $actions[0]->getAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicCreatedByUser',    $actions[0]->getAttributeFormByName('owner')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getAttributeFormByName('radioDropDown')->type);
            $this->assertEquals(-2, $actions[0]->getAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('user') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicModifiedByUser',    $actions[0]->getAttributeFormByName('user')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('user')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('user2') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicTriggeredByUser',    $actions[0]->getAttributeFormByName('user2')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('user2')->value);
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
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relationFilter'] = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes']     =
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

            $this->assertEquals(19,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('boolean') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('boolean')->type);
            $this->assertEquals('1', $actions[0]->getAttributeFormByName('boolean')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('boolean2') instanceof CheckBoxWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('boolean2')->type);
            $this->assertEquals('0', $actions[0]->getAttributeFormByName('boolean2')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('currencyValue') instanceof CurrencyValueWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('currencyValue')->type);
            $this->assertEquals(362.24,      $actions[0]->getAttributeFormByName('currencyValue')->value);
            $this->assertEquals($currency->id,  $actions[0]->getAttributeFormByName('currencyValue')->currencyId);
            $this->assertEquals('Static',  $actions[0]->getAttributeFormByName('currencyValue')->currencyIdType);

            $this->assertTrue($actions[0]->getAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('date')->type);
            $this->assertEquals('2012-02-24',  $actions[0]->getAttributeFormByName('date')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('dateTime')->type);
            $compareDateTime = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('2/24/12 03:00 AM');
            $this->assertEquals($compareDateTime,  $actions[0]->getAttributeFormByName('dateTime')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('dropDown')->type);
            $this->assertEquals('Value 1',  $actions[0]->getAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('float') instanceof DecimalWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('float')->type);
            $this->assertEquals('54.25',  $actions[0]->getAttributeFormByName('float')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('integer') instanceof IntegerWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('integer')->type);
            $this->assertEquals('32',  $actions[0]->getAttributeFormByName('integer')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('likeContactState') instanceof ContactStateWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('likeContactState')->type);
            $this->assertEquals($contactState->id,  $actions[0]->getAttributeFormByName('likeContactState')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('multiDropDown') instanceof MultiSelectDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('multiDropDown')->type);
            $this->assertEquals(array('Multi Value 1', 'Multi Value 2'),  $actions[0]->getAttributeFormByName('multiDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('Static',    $actions[0]->getAttributeFormByName('owner')->type);
            $this->assertEquals($bobby->id,  $actions[0]->getAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('phone') instanceof PhoneWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('phone')->type);
            $this->assertEquals('8471112222',  $actions[0]->getAttributeFormByName('phone')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('primaryAddress___street1') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('primaryAddress___street1')->type);
            $this->assertEquals('123 Main Street',  $actions[0]->getAttributeFormByName('primaryAddress___street1')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('primaryEmail___emailAddress') instanceof EmailWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('primaryEmail___emailAddress')->type);
            $this->assertEquals('info@zurmo.com',  $actions[0]->getAttributeFormByName('primaryEmail___emailAddress')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('radioDropDown')->type);
            $this->assertEquals('Radio Value 1',  $actions[0]->getAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getAttributeFormByName('string')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('tagCloud') instanceof TagCloudWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('tagCloud')->type);
            $this->assertEquals(array('Tag Value 1', 'Tag Value 2'),  $actions[0]->getAttributeFormByName('tagCloud')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('textArea') instanceof TextAreaWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('textArea')->type);
            $this->assertEquals('some description',  $actions[0]->getAttributeFormByName('textArea')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('url') instanceof UrlWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('url')->type);
            $this->assertEquals('http://www.zurmo.com',  $actions[0]->getAttributeFormByName('url')->value);
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
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relationFilter'] = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes']     =
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
            $this->assertEquals(13,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('date') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getAttributeFormByName('date')->type);
            $this->assertEquals(-86400,  $actions[0]->getAttributeFormByName('date')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date2') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDate', $actions[0]->getAttributeFormByName('date2')->type);
            $this->assertEquals(86400,  $actions[0]->getAttributeFormByName('date2')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date3') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getAttributeFormByName('date3')->type);
            $this->assertEquals(-86400,  $actions[0]->getAttributeFormByName('date3')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('date4') instanceof DateWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDate', $actions[0]->getAttributeFormByName('date4')->type);
            $this->assertEquals(86400,  $actions[0]->getAttributeFormByName('date4')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getAttributeFormByName('dateTime')->type);
            $this->assertEquals(-3600,  $actions[0]->getAttributeFormByName('dateTime')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime2') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromTriggeredDateTime', $actions[0]->getAttributeFormByName('dateTime2')->type);
            $this->assertEquals(3600,  $actions[0]->getAttributeFormByName('dateTime2')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime3') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getAttributeFormByName('dateTime3')->type);
            $this->assertEquals(-7200,  $actions[0]->getAttributeFormByName('dateTime3')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('dateTime4') instanceof DateTimeWorkflowActionAttributeForm);
            $this->assertEquals('DynamicFromExistingDateTime', $actions[0]->getAttributeFormByName('dateTime4')->type);
            $this->assertEquals(7200,  $actions[0]->getAttributeFormByName('dateTime4')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('dropDown') instanceof DropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getAttributeFormByName('dropDown')->type);
            $this->assertEquals(2, $actions[0]->getAttributeFormByName('dropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('owner') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicCreatedByUser',    $actions[0]->getAttributeFormByName('owner')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('owner')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('radioDropDown') instanceof RadioDropDownWorkflowActionAttributeForm);
            $this->assertEquals('DynamicStepForwardOrBackwards', $actions[0]->getAttributeFormByName('radioDropDown')->type);
            $this->assertEquals(-2, $actions[0]->getAttributeFormByName('radioDropDown')->value);

            $this->assertTrue($actions[0]->getAttributeFormByName('user') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicModifiedByUser',    $actions[0]->getAttributeFormByName('user')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('user')->value);
            $this->assertTrue($actions[0]->getAttributeFormByName('user2') instanceof UserWorkflowActionAttributeForm);
            $this->assertEquals('DynamicTriggeredByUser',    $actions[0]->getAttributeFormByName('user2')->type);
            $this->assertNull($actions[0]->getAttributeFormByName('user2')->value);
        }

        /**
         * Simple test that does not need to test all attributes because they are tested in the update
         * @depends testResolveUpdateRelatedActionWithDynamicValues
         */
        public function testResolveCreateActionWithValues()
        {
            $workflow = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTest2Module');
            $data   = array();
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['type']       = ActionForWorkflowForm::TYPE_CREATE;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relation']   = 'hasMany2';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes'] =
            array(
                'string' => array('shouldSetValue'    => '1',
                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                'value'  => 'jason'),
            );

            DataToWorkflowUtil::resolveActions($data, $workflow);
            $actions = $workflow->getActions();
            $this->assertCount(1, $actions);
            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE, $actions[0]->type);
            $this->assertEquals('hasMany2', $actions[0]->relation);

            $this->assertEquals(1,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $actions[0]->getAttributeFormByName('string')->value);
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
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relationFilter'] = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['relatedModelRelation'] = 'hasMany';
            $data[ComponentForWorkflowForm::TYPE_ACTIONS][0]['attributes'] =
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

            $this->assertEquals(1,        $actions[0]->getAttributeFormsCount());

            $this->assertTrue($actions[0]->getAttributeFormByName('name') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $actions[0]->getAttributeFormByName('name')->type);
            $this->assertEquals('jason',  $actions[0]->getAttributeFormByName('name')->value);
        }
    }
?>