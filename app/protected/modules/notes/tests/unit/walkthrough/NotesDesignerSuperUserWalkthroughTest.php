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
    * Designer Module Walkthrough of notes.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also tests the creation of the customfileds, addition of custom fields to all the layouts.
    * This also tests creation, edit and delete of the notes based on the custom fields.
    */
    class NotesDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create a opportunity for testing.
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);

            //Create a two contacts for testing.
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact1', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);

            //Create a note for testing.
            NoteTestHelper::createNoteWithOwnerAndRelatedAccount('superNote', $super, $account);
        }

        public function testSuperUserNoteDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Load AttributesList for Note module.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Note module.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'NotesModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'NotesModuleForm' => $this->createModuleEditGoodValidationPostData('note new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));
            $this->setPostArray(array('save' => 'Save',
                'NotesModuleForm' => $this->createModuleEditGoodValidationPostData('note new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Note New Name',  NotesModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Note New Names', NotesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('note new name',  NotesModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('note new names', NotesModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteInlineEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserNoteDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'NotesModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('NotesModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('NotesModule', 'currency');
            $this->createDateCustomFieldByModule                ('NotesModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('NotesModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('NotesModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('NotesModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('NotesModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('NotesModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('NotesModule', 'citylist');
            $this->createIntegerCustomFieldByModule             ('NotesModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('NotesModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('NotesModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('NotesModule', 'calcnumber');
            $this->createDropDownDependencyCustomFieldByModule  ('NotesModule', 'dropdowndep');
            $this->createPhoneCustomFieldByModule               ('NotesModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('NotesModule', 'radio');
            $this->createTextCustomFieldByModule                ('NotesModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('NotesModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('NotesModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForNotesModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to NoteEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteEditAndDetailsView'));
            $layout = NotesDesignerWalkthroughHelperUtil::getNoteEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add custom fields to NoteInlineEditView.
            $this->setGetArray(array('moduleClassName' => 'NotesModule',
                                     'viewClassName'   => 'NoteInlineEditView'));
            $layout = NotesDesignerWalkthroughHelperUtil::getNoteInlineEditViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForNotesModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superNoteId = self::getModelIdByModelNameAndName ('Note', 'superNote');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superNoteId));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/createFromRelation');
            //todo: more permutations from different relations.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForNotesModule
         */
        public function testCreateAnNoteAfterTheCustomFieldsArePlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId     = self::getModelIdByModelNameAndName('Contact', 'superContact1 superContact1son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new note based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Note' => array(
                                            'occurredOnDateTime'                => $datetime,
                                            'description'                       => 'Note Description',
                                            'explicitReadWriteModelPermissions' => array('type' => null),
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
                                            'urlCstm'                           => 'http://wwww.abc.com'),
                                      'ActivityItemForm' => array(
                                            'Account'    => array('id'  => $superAccount[0]->id),
                                            'Contact'    => array('id'  => $superContactId),
                                            'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('notes/default/inlineCreateSave');

            //Check the details if they are saved properly for the custom fields.
            $note = Note::getByName('Note Description');

            //Retrieve the permission of the note.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Note::getById($note[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($note[0]->description                      , 'Note Description');
            $this->assertEquals($note[0]->occurredOnDateTime               , $datetimeAssert);
            $this->assertEquals($note[0]->owner->id                        , $superUserId);
            $this->assertEquals($note[0]->activityItems->count()           , 3);
            $this->assertEquals(0                                          , count($readWritePermitables));
            $this->assertEquals(0                                          , count($readOnlyPermitables));
            $this->assertEquals($note[0]->checkboxCstm                     , '1');
            $this->assertEquals($note[0]->currencyCstm->value              , 45);
            $this->assertEquals($note[0]->currencyCstm->currency->id       , $baseCurrency->id);
            $this->assertEquals($note[0]->dateCstm                         , $dateAssert);
            $this->assertEquals($note[0]->datetimeCstm                     , $datetimeAssert);
            $this->assertEquals($note[0]->decimalCstm                      , '123');
            $this->assertEquals($note[0]->picklistCstm->value              , 'a');
            $this->assertEquals($note[0]->integerCstm                      , 12);
            $this->assertEquals($note[0]->phoneCstm                        , '259-784-2169');
            $this->assertEquals($note[0]->radioCstm->value                 , 'd');
            $this->assertEquals($note[0]->textCstm                         , 'This is a test Text');
            $this->assertEquals($note[0]->textareaCstm                     , 'This is a test TextArea');
            $this->assertEquals($note[0]->urlCstm                          , 'http://wwww.abc.com');
            $this->assertEquals($note[0]->countrylistCstm->value           , 'bbbb');
            $this->assertEquals($note[0]->statelistCstm->value             , 'bbb1');
            $this->assertEquals($note[0]->citylistCstm->value              , 'bb1');
            $this->assertContains('ff'                                     , $note[0]->multiselectCstm->values);
            $this->assertContains('rr'                                     , $note[0]->multiselectCstm->values);
            $this->assertContains('writing'                                , $note[0]->tagcloudCstm->values);
            $this->assertContains('gardening'                              , $note[0]->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Note');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $note[0]);
            $this->assertEquals(135                                        , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnNoteAfterTheCustomFieldsArePlacedForNotesModule
         */
        public function testEditOfTheNoteForTheTagCloudFieldAfterRemovingAllTagsPlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId     = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Retrieve the note Id based on the created note.
            $note = Note::getByName('Note Description');
            $this->assertEquals(2, $note[0]->tagcloudCstm->values->count());

            //Edit a note based on the custom fields.
            $this->setGetArray(array('id' => $note[0]->id));
            $this->setPostArray(array('Note' => array(
                                'occurredOnDateTime'                => $datetime,
                                'description'                       => 'Note Edit Description',
                                'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                                'owner'                             => array('id' => $superUserId),
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
                          'ActivityItemForm'  => array(
                                'Account'     => array('id'  => $superAccount[0]->id),
                                'Contact'     => array('id'  => $superContactId),
                                'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('notes/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $note = Note::getByName('Note Edit Description');

            //Retrieve the permission of the note.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Note::getById($note[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($note[0]->description                      , 'Note Edit Description');
            $this->assertEquals($note[0]->occurredOnDateTime               , $datetimeAssert);
            $this->assertEquals($note[0]->owner->id                        , $superUserId);
            $this->assertEquals($note[0]->activityItems->count()           , 3);
            $this->assertEquals(1                                          , count($readWritePermitables));
            $this->assertEquals(0                                          , count($readOnlyPermitables));
            $this->assertEquals($note[0]->checkboxCstm                     , '0');
            $this->assertEquals($note[0]->currencyCstm->value              , 40);
            $this->assertEquals($note[0]->currencyCstm->currency->id       , $baseCurrency->id);
            $this->assertEquals($note[0]->dateCstm                         , $dateAssert);
            $this->assertEquals($note[0]->datetimeCstm                     , $datetimeAssert);
            $this->assertEquals($note[0]->decimalCstm                      , '12');
            $this->assertEquals($note[0]->picklistCstm->value              , 'b');
            $this->assertEquals($note[0]->integerCstm                      , 11);
            $this->assertEquals($note[0]->phoneCstm                        , '259-784-2069');
            $this->assertEquals($note[0]->radioCstm->value                 , 'e');
            $this->assertEquals($note[0]->textCstm                         , 'This is a test Edit Text');
            $this->assertEquals($note[0]->textareaCstm                     , 'This is a test Edit TextArea');
            $this->assertEquals($note[0]->urlCstm                          , 'http://wwww.abc-edit.com');
            $this->assertEquals($note[0]->countrylistCstm->value           , 'aaaa');
            $this->assertEquals($note[0]->statelistCstm->value             , 'aaa1');
            $this->assertEquals($note[0]->citylistCstm->value              , 'ab1');
            $this->assertContains('gg'                                     , $note[0]->multiselectCstm->values);
            $this->assertContains('hh'                                     , $note[0]->multiselectCstm->values);
            $this->assertEquals(0                                          , $note[0]->tagcloudCstm->values->count());
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Note');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $note[0]);
            $this->assertEquals(23                                         , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheNoteForTheTagCloudFieldAfterRemovingAllTagsPlacedForNotesModule
         */
        public function testEditOfTheNoteForTheCustomFieldsPlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId     = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Retrieve the note Id based on the created note.
            $note = Note::getByName('Note Edit Description');

            //Edit a note based on the custom fields.
            $this->setGetArray(array('id' => $note[0]->id));
            $this->setPostArray(array('Note' => array(
                                'occurredOnDateTime'                => $datetime,
                                'description'                       => 'Note Edit Description',
                                'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                                'owner'                             => array('id' => $superUserId),
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
                          'ActivityItemForm'  => array(
                                'Account'     => array('id'  => $superAccount[0]->id),
                                'Contact'     => array('id'  => $superContactId),
                                'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('notes/default/edit');

            //Check the details if they are saved properly for the custom fields.
            $note = Note::getByName('Note Edit Description');

            //Retrieve the permission of the note.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Note::getById($note[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($note[0]->description                      , 'Note Edit Description');
            $this->assertEquals($note[0]->occurredOnDateTime               , $datetimeAssert);
            $this->assertEquals($note[0]->owner->id                        , $superUserId);
            $this->assertEquals($note[0]->activityItems->count()           , 3);
            $this->assertEquals(1                                          , count($readWritePermitables));
            $this->assertEquals(0                                          , count($readOnlyPermitables));
            $this->assertEquals($note[0]->checkboxCstm                     , '0');
            $this->assertEquals($note[0]->currencyCstm->value              , 40);
            $this->assertEquals($note[0]->currencyCstm->currency->id       , $baseCurrency->id);
            $this->assertEquals($note[0]->dateCstm                         , $dateAssert);
            $this->assertEquals($note[0]->datetimeCstm                     , $datetimeAssert);
            $this->assertEquals($note[0]->decimalCstm                      , '12');
            $this->assertEquals($note[0]->picklistCstm->value              , 'b');
            $this->assertEquals($note[0]->integerCstm                      , 11);
            $this->assertEquals($note[0]->phoneCstm                        , '259-784-2069');
            $this->assertEquals($note[0]->radioCstm->value                 , 'e');
            $this->assertEquals($note[0]->textCstm                         , 'This is a test Edit Text');
            $this->assertEquals($note[0]->textareaCstm                     , 'This is a test Edit TextArea');
            $this->assertEquals($note[0]->urlCstm                          , 'http://wwww.abc-edit.com');
            $this->assertEquals($note[0]->countrylistCstm->value           , 'aaaa');
            $this->assertEquals($note[0]->statelistCstm->value             , 'aaa1');
            $this->assertEquals($note[0]->citylistCstm->value              , 'ab1');
            $this->assertContains('gg'                                     , $note[0]->multiselectCstm->values);
            $this->assertContains('hh'                                     , $note[0]->multiselectCstm->values);
            $this->assertContains('reading'                                , $note[0]->tagcloudCstm->values);
            $this->assertContains('surfing'                                , $note[0]->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Note');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $note[0]);
            $this->assertEquals(23                                         , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheNoteForTheCustomFieldsPlacedForNotesModule
         */
        public function testDeleteOfTheNoteForTheCustomFieldsPlacedForNotesModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the note Id based on the created note.
            $note = Note::getByName('Note Edit Description');

            //Set the note id so as to delete the note.
            $this->setGetArray(array('id' => $note[0]->id));
            $this->runControllerWithRedirectExceptionAndGetUrl('notes/default/delete');

            //Check to confirm that the note is deleted.
            $note = Note::getByName('Note Edit Description');
            $this->assertEquals(0, count($note));
        }

        /**
         * @depends testDeleteOfTheNoteForTheCustomFieldsPlacedForNotesModule
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForNotesModule()
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
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForNotesModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForNotesModule()
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