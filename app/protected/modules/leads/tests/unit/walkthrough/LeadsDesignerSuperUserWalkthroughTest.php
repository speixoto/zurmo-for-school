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

    /**
    * Designer Module Walkthrough of leads.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation, search, edit and delete of the lead based on the custom fields.
    */
    class LeadsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //create a lead here
            LeadTestHelper::createLeadbyNameForOwner('superLead',    $super);
        }

        public function testSuperUserLeadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Lead Modules Menu.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Leads module.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Lead module.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'LeadsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'LeadsModuleForm' => $this->createModuleEditGoodValidationPostData('lea new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule'));
            $this->setPostArray(array('save' => 'Save',
                'LeadsModuleForm' => $this->createModuleEditGoodValidationPostData('lea new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Lea New Name',  LeadsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Lea New Names', LeadsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('lea new name',  LeadsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('lea new names', LeadsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserLeadDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ContactsModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ContactsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ContactsModule', 'currency');
            $this->createDateCustomFieldByModule                ('ContactsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ContactsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ContactsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ContactsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('ContactsModule', 'citylist');
            $this->createIntegerCustomFieldByModule             ('ContactsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ContactsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('ContactsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('ContactsModule', 'calcnumber');
            $this->createDropDownDependencyCustomFieldByModule  ('ContactsModule', 'dropdowndep');
            $this->createPhoneCustomFieldByModule               ('ContactsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ContactsModule', 'radio');
            $this->createTextCustomFieldByModule                ('ContactsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ContactsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ContactsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForLeadsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to LeadEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadEditAndDetailsView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactEditAndDetailsViewLayoutWithAllCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsSearchView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsSearchView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsSearchViewLayoutWithAllCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsListView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsMassEditView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced(
                        'LeadStateDropDown');
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to LeadsModalListView.
            $this->setGetArray(array('moduleClassName' => 'LeadsModule',
                                     'viewClassName'   => 'LeadsModalListView'));
            $layout = ContactsDesignerWalkthroughHelperUtil::getContactsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForLeadsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superLeadId  = self::getModelIdByModelNameAndName('Contact', 'superLead superLeadson');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/create');
            $this->setGetArray(array('id' => $superLeadId));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/modalList');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEdit');
            //todo: test related list once the related list is available in a sub view.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForLeadsModule
         */
        public function testCreateAnLeadUserAfterTheCustomFieldsArePlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the the super user id.
            $superUserId = $super->id;

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('New');
            $leadStateId = $leadState[0]->id;

            //Create a new Lead based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                                    'title'                             => array('value' => 'Mr.'),
                                    'firstName'                         => 'Sarah',
                                    'lastName'                          => 'Williams',
                                    'state'                             => array('id' => $leadStateId),
                                    'jobTitle'                          => 'Sales Director',
                                    'companyName'                       => 'ABC Telecom',
                                    'industry'                          => array('value' => 'Automotive'),
                                    'website'                           => 'http://www.company.com',
                                    'department'                        => 'Sales',
                                    'officePhone'                       => '739-741-3005',
                                    'source'                            => array('value' => 'Self-Generated'),
                                    'mobilePhone'                       => '285-301-8232',
                                    'officeFax'                         => '255-455-1914',
                                    'primaryEmail'                      => array('emailAddress' => 'info@myNewLead.com',
                                                                                 'optOut' => '1',
                                                                                 'isInvalid' => '0'),
                                    'secondaryEmail'                    => array('emailAddress' => '',
                                                                                 'optOut' => '0',
                                                                                 'isInvalid' => '0'),
                                    'primaryAddress'                    => array('street1' => '16139 North Michigan Road',
                                                                                 'street2' => '',
                                                                                 'city' => 'Boston',
                                                                                 'state' => 'MA',
                                                                                 'postalCode' => '02119',
                                                                                 'country' => 'USA'),
                                    'secondaryAddress'                  => array('street1' => '26217 West Third Lane',
                                                                                 'street2' => '',
                                                                                 'city' => 'New York',
                                                                                 'state' => 'NY',
                                                                                 'postalCode' => '10169',
                                                                                 'country' => 'USA'),
                                    'owner'                             => array('id' => $superUserId),
                                    'explicitReadWriteModelPermissions' => array('type' => null),
                                    'description'                       => 'This is a Description',
                                    'checkboxCstm'                      => '1',
                                    'currencyCstm'                      => array('value'   => 45,
                                                                                 'currency' => array(
                                                                                 'id' => $baseCurrency->id)),
                                    'dateCstm'                          => $date,
                                    'datetimeCstm'                      => $datetime,
                                    'decimalCstm'                       => '123',
                                    'picklistCstm'                      => array('value'  => 'a'),
                                    'multiselectCstm'                   => array('values' => array('ff', 'rr')),
                                    'tagcloudCstm'                      => array('values' => array('writing', 'gardening')),
                                    'countrylistCstm'                   => array('value'  => 'bbbb'),
                                    'statelistCstm'                     => array('value'  => 'bbb1'),
                                    'citylistCstm'                      => array('value'  => 'bb1'),
                                    'integerCstm'                       => '12',
                                    'phoneCstm'                         => '259-784-2169',
                                    'radioCstm'                         => array('value' => 'd'),
                                    'textCstm'                          => 'This is a test Text',
                                    'textareaCstm'                      => 'This is a test TextArea',
                                    'urlCstm'                           => 'http://wwww.abc.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('leads/default/create');

            //Check the details if they are saved properly for the custom fields.
            $leadId     = self::getModelIdByModelNameAndName ('Contact', 'Sarah Williams');
            $lead       = Contact::getById($leadId);

            //Retrieve the permission of the lead.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($lead);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($lead->title->value                   , 'Mr.');
            $this->assertEquals($lead->firstName                      , 'Sarah');
            $this->assertEquals($lead->lastName                       , 'Williams');
            $this->assertEquals($lead->state->id                      , $leadStateId);
            $this->assertEquals($lead->jobTitle                       , 'Sales Director');
            $this->assertEquals($lead->companyName                    , 'ABC Telecom');
            $this->assertEquals($lead->industry->value                , 'Automotive');
            $this->assertEquals($lead->website                        , 'http://www.company.com');
            $this->assertEquals($lead->department                     , 'Sales');
            $this->assertEquals($lead->officePhone                    , '739-741-3005');
            $this->assertEquals($lead->source->value                  , 'Self-Generated');
            $this->assertEquals($lead->mobilePhone                    , '285-301-8232');
            $this->assertEquals($lead->officeFax                      , '255-455-1914');
            $this->assertEquals($lead->primaryEmail->emailAddress     , 'info@myNewLead.com');
            $this->assertEquals($lead->primaryEmail->optOut           , '1');
            $this->assertEquals($lead->primaryEmail->isInvalid        , '0');
            $this->assertEquals($lead->secondaryEmail->emailAddress   , '');
            $this->assertEquals($lead->secondaryEmail->optOut         , '0');
            $this->assertEquals($lead->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($lead->primaryAddress->street1        , '16139 North Michigan Road');
            $this->assertEquals($lead->primaryAddress->street2        , '');
            $this->assertEquals($lead->primaryAddress->city           , 'Boston');
            $this->assertEquals($lead->primaryAddress->state          , 'MA');
            $this->assertEquals($lead->primaryAddress->postalCode     , '02119');
            $this->assertEquals($lead->primaryAddress->country        , 'USA');
            $this->assertEquals($lead->secondaryAddress->street1      , '26217 West Third Lane');
            $this->assertEquals($lead->secondaryAddress->street2      , '');
            $this->assertEquals($lead->secondaryAddress->city         , 'New York');
            $this->assertEquals($lead->secondaryAddress->state        , 'NY');
            $this->assertEquals($lead->secondaryAddress->postalCode   , '10169');
            $this->assertEquals($lead->secondaryAddress->country      , 'USA');
            $this->assertEquals($lead->owner->id                      , $superUserId);
            $this->assertEquals(0                                     , count($readWritePermitables));
            $this->assertEquals(0                                     , count($readOnlyPermitables));
            $this->assertEquals($lead->description                    , 'This is a Description');
            $this->assertEquals($lead->checkboxCstm                   , '1');
            $this->assertEquals($lead->currencyCstm->value            , 45);
            $this->assertEquals($lead->currencyCstm->currency->id     , $baseCurrency->id);
            $this->assertEquals($lead->dateCstm                       , $dateAssert);
            $this->assertEquals($lead->datetimeCstm                   , $datetimeAssert);
            $this->assertEquals($lead->decimalCstm                    , '123');
            $this->assertEquals($lead->picklistCstm->value            , 'a');
            $this->assertEquals($lead->integerCstm                    , 12);
            $this->assertEquals($lead->phoneCstm                      , '259-784-2169');
            $this->assertEquals($lead->radioCstm->value               , 'd');
            $this->assertEquals($lead->textCstm                       , 'This is a test Text');
            $this->assertEquals($lead->textareaCstm                   , 'This is a test TextArea');
            $this->assertEquals($lead->urlCstm                        , 'http://wwww.abc.com');
            $this->assertEquals($lead->countrylistCstm->value         , 'bbbb');
            $this->assertEquals($lead->statelistCstm->value           , 'bbb1');
            $this->assertEquals($lead->citylistCstm->value            , 'bb1');
            $this->assertContains('ff'                                , $lead->multiselectCstm->values);
            $this->assertContains('rr'                                , $lead->multiselectCstm->values);
            $this->assertContains('writing'                           , $lead->tagcloudCstm->values);
            $this->assertContains('gardening'                         , $lead->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $lead);
            $this->assertEquals(135                                      , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnLeadUserAfterTheCustomFieldsArePlacedForLeadsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterCreatingTheLeadUser()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super user id.
            $superUserId = $super->id;

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('New');
            $leadStateId = $leadState[0]->id;

            //Search a created lead using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array('LeadsSearchForm' => array(
                                                            'fullName'           => 'Sarah Williams',
                                                            'officePhone'        => '739-741-3005',
                                                            'anyPostalCode'      => '10169',
                                                            'companyName'        => 'ABC Telecom',
                                                            'department'         => 'Sales',
                                                            'industry'           => array('value' => 'Automotive'),
                                                            'website'            => 'http://www.company.com',
                                                            'anyCountry'         => 'USA',
                                                            'anyInvalidEmail'    => array('value' => '0'),
                                                            'anyEmail'           => 'info@myNewLead.com',
                                                            'anyOptOutEmail'     => array('value' => '1'),
                                                            'ownedItemsOnly'     => '1',
                                                            'anyStreet'          => '26217 West Third Lane',
                                                            'anyCity'            => 'New York',
                                                            'anyState'           => 'NY',
                                                            'state'              => array('id' => $leadStateId),
                                                            'owner'              => array('id' => $superUserId),
                                                            'firstName'          => 'Sarah',
                                                            'lastName'           => 'Williams',
                                                            'jobTitle'           => 'Sales Director',
                                                            'officeFax'          => '255-455-1914',
                                                            'title'              => array('value' => 'Mr.'),
                                                            'source'             => array('value' => 'Self-Generated'),
                                                            'decimalCstm'        => '123',
                                                            'integerCstm'        => '12',
                                                            'phoneCstm'          => '259-784-2169',
                                                            'textCstm'           => 'This is a test Text',
                                                            'textareaCstm'       => 'This is a test TextArea',
                                                            'urlCstm'            => 'http://wwww.abc.com',
                                                            'checkboxCstm'       => array('value'  =>  '1'),
                                                            'currencyCstm'       => array('value'  =>  45),
                                                            'picklistCstm'       => array('value'  =>  'a'),
                                                            'multiselectCstm'    => array('values' => array('ff', 'rr')),
                                                            'tagcloudCstm'       => array('values' => array('writing', 'gardening')),
                                                            'countrylistCstm'    => array('value'  => 'bbbb'),
                                                            'statelistCstm'      => array('value'  => 'bbb1'),
                                                            'citylistCstm'       => array('value'  => 'bb1'),
                                                            'radioCstm'          => array('value'  =>  'd'),
                                                            'dateCstm__Date'     => array('type'   =>  'Today'),
                                                            'datetimeCstm__DateTime' => array('type'   =>  'Today')),
                                                            'ajax'               => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default');

            //Check if the lead name exits after the search is performed on the basis of the
            //custom fields added to the leads module.
            //$this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0); //removed until we show the count again in the listview.
            $this->assertTrue(strpos($content, "Sarah Williams") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterCreatingTheLeadUser
         */
        public function testEditOfTheLeadUserForTheTagCloudFieldAfterRemovingAllTagsPlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the the super user id.
            $superUserId = $super->id;

            //Retrieve the lead id.
            $lead   = Contact::getByName('Sarah Williams');
            $leadId = $lead[0]->id;
            $this->assertEquals(2, $lead[0]->tagcloudCstm->values->count());

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('In Progress');
            $leadStateId = $leadState[0]->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Edit and save the lead.
            $this->setGetArray(array('id' => $leadId));
            $this->setPostArray(array('Contact' => array(
                            'title'                             => array('value' => 'Mrs.'),
                            'firstName'                         => 'Sarah',
                            'lastName'                          => 'Williams Edit',
                            'jobTitle'                          => 'Sales Director Edit',
                            'companyName'                       => 'ABC Telecom Edit',
                            'industry'                          => array('value' => 'Banking'),
                            'website'                           => 'http://www.companyedit.com',
                            'department'                        => 'Sales Edit',
                            'officePhone'                       => '739-742-3005',
                            'source'                            => array('value' => 'Inbound Call'),
                            'mobilePhone'                       => '285-300-8232',
                            'officeFax'                         => '255-454-1914',
                            'state'                             => array('id' => $leadStateId),
                            'owner'                             => array('id' => $superUserId),
                            'primaryEmail'                      => array('emailAddress' => 'info@myNewLead.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => 'info@myNewLeadEdit.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'primaryAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'secondaryAddress'                  => array('street1' => '1652 North Cedar Court',
                                                                         'street2' => '',
                                                                         'city' => 'Phoenix',
                                                                         'state' => 'AZ',
                                                                         'postalCode' => '85003',
                                                                         'country' => 'USA'),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'description'                       => 'This is a Edit Description',
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'decimalCstm'                       => '12',
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array()),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com'),
                            'save'                              => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('leads/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $lead  = Contact::getById($leadId);
            //Retrieve the permission of the lead.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($lead);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($lead->title->value                   , 'Mrs.');
            $this->assertEquals($lead->firstName                      , 'Sarah');
            $this->assertEquals($lead->lastName                       , 'Williams Edit');
            $this->assertEquals($lead->state->id                      , $leadStateId);
            $this->assertEquals($lead->jobTitle                       , 'Sales Director Edit');
            $this->assertEquals($lead->companyName                    , 'ABC Telecom Edit');
            $this->assertEquals($lead->industry->value                , 'Banking');
            $this->assertEquals($lead->website                        , 'http://www.companyedit.com');
            $this->assertEquals($lead->department                     , 'Sales Edit');
            $this->assertEquals($lead->officePhone                    , '739-742-3005');
            $this->assertEquals($lead->source->value                  , 'Inbound Call');
            $this->assertEquals($lead->mobilePhone                    , '285-300-8232');
            $this->assertEquals($lead->officeFax                      , '255-454-1914');
            $this->assertEquals($lead->primaryEmail->emailAddress     , 'info@myNewLead.com');
            $this->assertEquals($lead->primaryEmail->optOut           , '0');
            $this->assertEquals($lead->primaryEmail->isInvalid        , '0');
            $this->assertEquals($lead->secondaryEmail->emailAddress   , 'info@myNewLeadEdit.com');
            $this->assertEquals($lead->secondaryEmail->optOut         , '0');
            $this->assertEquals($lead->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($lead->primaryAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($lead->primaryAddress->street2        , '');
            $this->assertEquals($lead->primaryAddress->city           , 'San Jose');
            $this->assertEquals($lead->primaryAddress->state          , 'CA');
            $this->assertEquals($lead->primaryAddress->postalCode     , '95131');
            $this->assertEquals($lead->primaryAddress->country        , 'USA');
            $this->assertEquals($lead->secondaryAddress->street1      , '1652 North Cedar Court');
            $this->assertEquals($lead->secondaryAddress->street2      , '');
            $this->assertEquals($lead->secondaryAddress->city         , 'Phoenix');
            $this->assertEquals($lead->secondaryAddress->state        , 'AZ');
            $this->assertEquals($lead->secondaryAddress->postalCode   , '85003');
            $this->assertEquals($lead->secondaryAddress->country      , 'USA');
            $this->assertEquals(1                                     , count($readWritePermitables));
            $this->assertEquals(0                                     , count($readOnlyPermitables));
            $this->assertEquals($lead->description                    , 'This is a Edit Description');
            $this->assertEquals($lead->checkboxCstm                   , '0');
            $this->assertEquals($lead->currencyCstm->value            ,  40);
            $this->assertEquals($lead->currencyCstm->currency->id     , $baseCurrency->id);
            $this->assertEquals($lead->dateCstm                       , $dateAssert);
            $this->assertEquals($lead->datetimeCstm                   , $datetimeAssert);
            $this->assertEquals($lead->decimalCstm                    , '12');
            $this->assertEquals($lead->picklistCstm->value            , 'b');
            $this->assertEquals($lead->integerCstm                    ,  11);
            $this->assertEquals($lead->phoneCstm                      , '259-784-2069');
            $this->assertEquals($lead->radioCstm->value               , 'e');
            $this->assertEquals($lead->textCstm                       , 'This is a test Edit Text');
            $this->assertEquals($lead->textareaCstm                   , 'This is a test Edit TextArea');
            $this->assertEquals($lead->urlCstm                        , 'http://wwww.abc-edit.com');
            $this->assertEquals($lead->countrylistCstm->value         , 'aaaa');
            $this->assertEquals($lead->statelistCstm->value           , 'aaa1');
            $this->assertEquals($lead->citylistCstm->value            , 'ab1');
            $this->assertContains('gg'                                , $lead->multiselectCstm->values);
            $this->assertContains('hh'                                , $lead->multiselectCstm->values);
            $this->assertEquals(0                                     , $lead->tagcloudCstm->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $lead);
            $this->assertEquals(23                                    , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheLeadUserForTheTagCloudFieldAfterRemovingAllTagsPlacedForLeadsModule
         */
        public function testEditOfTheLeadUserForTheCustomFieldsPlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the the super user id.
            $superUserId = $super->id;

            //Retrieve the lead id.
            $leadId     = self::getModelIdByModelNameAndName('Contact', 'Sarah Williams Edit');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('In Progress');
            $leadStateId = $leadState[0]->id;
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Edit and save the lead.
            $this->setGetArray(array('id' => $leadId));
            $this->setPostArray(array('Contact' => array(
                            'title'                             => array('value' => 'Mrs.'),
                            'firstName'                         => 'Sarah',
                            'lastName'                          => 'Williams Edit',
                            'jobTitle'                          => 'Sales Director Edit',
                            'companyName'                       => 'ABC Telecom Edit',
                            'industry'                          => array('value' => 'Banking'),
                            'website'                           => 'http://www.companyedit.com',
                            'department'                        => 'Sales Edit',
                            'officePhone'                       => '739-742-3005',
                            'source'                            => array('value' => 'Inbound Call'),
                            'mobilePhone'                       => '285-300-8232',
                            'officeFax'                         => '255-454-1914',
                            'state'                             => array('id' => $leadStateId),
                            'owner'                             => array('id' => $superUserId),
                            'primaryEmail'                      => array('emailAddress' => 'info@myNewLead.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => 'info@myNewLeadEdit.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'primaryAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'secondaryAddress'                  => array('street1' => '1652 North Cedar Court',
                                                                         'street2' => '',
                                                                         'city' => 'Phoenix',
                                                                         'state' => 'AZ',
                                                                         'postalCode' => '85003',
                                                                         'country' => 'USA'),
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'description'                       => 'This is a Edit Description',
                            'checkboxCstm'                      => '0',
                            'currencyCstm'                      => array('value'   => 40,
                                                                         'currency' => array(
                                                                         'id' => $baseCurrency->id)),
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'decimalCstm'                       => '12',
                            'picklistCstm'                      => array('value'  => 'b'),
                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
                            'tagcloudCstm'                      => array('values' =>  array('reading', 'surfing')),
                            'countrylistCstm'                   => array('value'  => 'aaaa'),
                            'statelistCstm'                     => array('value'  => 'aaa1'),
                            'citylistCstm'                      => array('value'  => 'ab1'),
                            'integerCstm'                       => '11',
                            'phoneCstm'                         => '259-784-2069',
                            'radioCstm'                         => array('value' => 'e'),
                            'textCstm'                          => 'This is a test Edit Text',
                            'textareaCstm'                      => 'This is a test Edit TextArea',
                            'urlCstm'                           => 'http://wwww.abc-edit.com'),
                            'save'                              => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('leads/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $lead  = Contact::getById($leadId);
            //Retrieve the permission of the lead.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($lead);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($lead->title->value                   , 'Mrs.');
            $this->assertEquals($lead->firstName                      , 'Sarah');
            $this->assertEquals($lead->lastName                       , 'Williams Edit');
            $this->assertEquals($lead->state->id                      , $leadStateId);
            $this->assertEquals($lead->jobTitle                       , 'Sales Director Edit');
            $this->assertEquals($lead->companyName                    , 'ABC Telecom Edit');
            $this->assertEquals($lead->industry->value                , 'Banking');
            $this->assertEquals($lead->website                        , 'http://www.companyedit.com');
            $this->assertEquals($lead->department                     , 'Sales Edit');
            $this->assertEquals($lead->officePhone                    , '739-742-3005');
            $this->assertEquals($lead->source->value                  , 'Inbound Call');
            $this->assertEquals($lead->mobilePhone                    , '285-300-8232');
            $this->assertEquals($lead->officeFax                      , '255-454-1914');
            $this->assertEquals($lead->primaryEmail->emailAddress     , 'info@myNewLead.com');
            $this->assertEquals($lead->primaryEmail->optOut           , '0');
            $this->assertEquals($lead->primaryEmail->isInvalid        , '0');
            $this->assertEquals($lead->secondaryEmail->emailAddress   , 'info@myNewLeadEdit.com');
            $this->assertEquals($lead->secondaryEmail->optOut         , '0');
            $this->assertEquals($lead->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($lead->primaryAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($lead->primaryAddress->street2        , '');
            $this->assertEquals($lead->primaryAddress->city           , 'San Jose');
            $this->assertEquals($lead->primaryAddress->state          , 'CA');
            $this->assertEquals($lead->primaryAddress->postalCode     , '95131');
            $this->assertEquals($lead->primaryAddress->country        , 'USA');
            $this->assertEquals($lead->secondaryAddress->street1      , '1652 North Cedar Court');
            $this->assertEquals($lead->secondaryAddress->street2      , '');
            $this->assertEquals($lead->secondaryAddress->city         , 'Phoenix');
            $this->assertEquals($lead->secondaryAddress->state        , 'AZ');
            $this->assertEquals($lead->secondaryAddress->postalCode   , '85003');
            $this->assertEquals($lead->secondaryAddress->country      , 'USA');
            $this->assertEquals(1                                     , count($readWritePermitables));
            $this->assertEquals(0                                     , count($readOnlyPermitables));
            $this->assertEquals($lead->description                    , 'This is a Edit Description');
            $this->assertEquals($lead->checkboxCstm                   , '0');
            $this->assertEquals($lead->currencyCstm->value            ,  40);
            $this->assertEquals($lead->currencyCstm->currency->id     , $baseCurrency->id);
            $this->assertEquals($lead->dateCstm                       , $dateAssert);
            $this->assertEquals($lead->datetimeCstm                   , $datetimeAssert);
            $this->assertEquals($lead->decimalCstm                    , '12');
            $this->assertEquals($lead->picklistCstm->value            , 'b');
            $this->assertEquals($lead->integerCstm                    ,  11);
            $this->assertEquals($lead->phoneCstm                      , '259-784-2069');
            $this->assertEquals($lead->radioCstm->value               , 'e');
            $this->assertEquals($lead->textCstm                       , 'This is a test Edit Text');
            $this->assertEquals($lead->textareaCstm                   , 'This is a test Edit TextArea');
            $this->assertEquals($lead->urlCstm                        , 'http://wwww.abc-edit.com');
            $this->assertEquals($lead->countrylistCstm->value         , 'aaaa');
            $this->assertEquals($lead->statelistCstm->value           , 'aaa1');
            $this->assertEquals($lead->citylistCstm->value            , 'ab1');
            $this->assertContains('gg'                                , $lead->multiselectCstm->values);
            $this->assertContains('hh'                                , $lead->multiselectCstm->values);
            $this->assertContains('reading'                           , $lead->tagcloudCstm->values);
            $this->assertContains('surfing'                           , $lead->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Contact');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $lead);
            $this->assertEquals(23                                    , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheLeadUserForTheCustomFieldsPlacedForLeadsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterEditingTheLeadUser()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super user id.
            $superUserId = $super->id;

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('In Progress');
            $leadStateId = $leadState[0]->id;

            //Search a created lead using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'LeadsSearchForm' => LeadsDesignerWalkthroughHelperUtil::fetchLeadsSearchFormGetData(
                                                                                   $leadStateId, $superUserId),
                        'ajax'            => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default');

            //Check if the lead name exits after the search is performed on the basis of the
            //custom fields added to the leads module.
            //$this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0); //removed until we show the count again in the listview.
            $this->assertTrue(strpos($content, "Sarah Williams Edit") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterEditingTheLeadUser
         */
        public function testDeleteOfTheLeadUserForTheCustomFieldsPlacedForLeadsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the lead id.
            $leadId = self::getModelIdByModelNameAndName('Contact', 'Sarah Williams Edit');

            //Set the lead id so as to delete the lead.
            $this->setGetArray(array('id' => $leadId));
            $this->runControllerWithRedirectExceptionAndGetUrl('leads/default/delete');

            //Check whether the lead is deleted.
            $lead     = Contact::getByName('Sarah Williams Edit');
            $this->assertEquals(0, count($lead));
        }

        /**
         * @depends testDeleteOfTheLeadUserForTheCustomFieldsPlacedForLeadsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterDeletingTheLead()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the super user id.
            $superUserId = $super->id;

            //Retrieve the Lead State (Status) Id based on the name.
            $leadState   = ContactState::getByName('In Progress');
            $leadStateId = $leadState[0]->id;

            //Search a created lead using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'LeadsSearchForm' => LeadsDesignerWalkthroughHelperUtil::fetchLeadsSearchFormGetData(
                                                                                   $leadStateId, $superUserId),
                        'ajax'            => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default');

            //Assert that the edit lead does not exits after the search.
            $this->assertTrue(strpos($content, "No results found.") > 0);
            $this->assertFalse(strpos($content, "26378 South Arlington Ave") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForLeadsModuleAfterDeletingTheLead
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForMeetingsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'rea'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "reading") > 0);
        }

        /**
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForMeetingsModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForMeetingsModule()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));

            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'surf'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "surfing fr") > 0);
        }
    }
?>