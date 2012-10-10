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

    class ContactImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.accounts.data.*');
            $defaultDataMaker = new AccountsDefaultDataMaker();
            $defaultDataMaker->make();
            Yii::import('application.modules.contacts.data.*');
            $defaultDataMaker = new ContactsDefaultDataMaker();
            $defaultDataMaker->make();
            Currency::getAll(); //forces base currency to be created.
            ContactsModule::loadStartingData();
            $jim = UserTestHelper::createBasicUser('jim');
        }

        /**
         * Test when a normal user who can only view records he owns, tries to import records assigned to another user.
         */
        public function testImportSwitchingOwnerButShouldStillCreate()
        {
            $super = User::getByUsername('super');
            $jim   = User::getByUsername('jim');
            Yii::app()->user->userModel = $jim;

            //Confirm Jim can can only view ImportModelTestItems he owns.
            $item       = NamedSecurableItem::getByName('ContactsModule');
            $this->assertEquals(Permission::NONE, $item->getEffectivePermissions($jim));

            $testModels                        = Contact::getAll();
            $this->assertEquals(0, count($testModels));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Contacts';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::createTempTableByFileNameAndTableName('importTest.csv', $import->getTempTableName(),
                              Yii::getPathOfAlias('application.modules.contacts.tests.unit.files'));

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $ownerColumnMappingData         = array('attributeIndexOrDerivedType' => 'owner',
                                               'type'                        => 'extraColumn',
                                               'mappingRulesData'            => array(
                                                   'DefaultModelNameIdMappingRuleForm' =>
                                                   array('defaultModelId' => $super->id),
                                                   'UserValueTypeModelAttributeMappingRuleForm' =>
                                                   array('type' =>
                                                   UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)));

            $startingStateId = ContactsUtil::getStartingState()->id;
            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeStringColumnMappingData      ('firstName'),
                'column_1'  => ImportMappingUtil::makeStringColumnMappingData      ('lastName'),
                'column_2'  => $ownerColumnMappingData,
                'column_3'  => ContactImportTestHelper::makeStateColumnMappingData ($startingStateId, 'extraColumn'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Contacts');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(),
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //3 models are created, but Jim can't see them since they are assigned to someone else.
            $testModels = Contact::getAll();
            $this->assertEquals(0, count($testModels));

            //Using super, should see all 3 models created.
            Yii::app()->user->userModel = $super;
            $testModels = Contact::getAll();
            $this->assertEquals(3, count($testModels));

            foreach ($testModels as $model)
            {
                $this->assertEquals(array(Permission::NONE, Permission::NONE), $model->getExplicitActualPermissions ($jim));
            }
            //Confirm 4 rows were processed as 'created'.
            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 0 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));

            //Clear out data in table
            R::exec("delete from " . Contact::getTableName('Contact'));
        }

        /**
         * @depends testImportSwitchingOwnerButShouldStillCreate
         */
        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $account                               = AccountTestHelper::
                                                     createAccountByNameForOwner('Account',
                                                                                 Yii::app()->user->userModel);
            $accountId = $account->id;
            $contacts                              = Contact::getAll();
            $this->assertEquals(0, count($contacts));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Contacts';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.contacts.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            R::exec("update " . $import->getTempTableName() . " set column_17 = " .
                    $account->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $currency = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeStringColumnMappingData       ('firstName'),
                'column_1'  => ImportMappingUtil::makeStringColumnMappingData       ('lastName'),
                'column_2'  => ImportMappingUtil::makeStringColumnMappingData       ('jobTitle'),
                'column_3'  => ImportMappingUtil::makeStringColumnMappingData       ('officePhone'),
                'column_4'  => ImportMappingUtil::makeStringColumnMappingData       ('officeFax'),
                'column_5'  => ImportMappingUtil::makeStringColumnMappingData       ('department'),
                'column_6'  => ImportMappingUtil::makeUrlColumnMappingData          ('website'),
                'column_7'  => ImportMappingUtil::makeTextAreaColumnMappingData     ('description'),
                'column_8'  => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__city'),
                'column_9'  => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__country'),
                'column_10' => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__postalCode'),
                'column_11' => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__state'),
                'column_12' => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__street1'),
                'column_13' => ImportMappingUtil::makeStringColumnMappingData       ('primaryAddress__street2'),
                'column_14' => ImportMappingUtil::makeEmailColumnMappingData        ('primaryEmail__emailAddress'),
                'column_15' => ImportMappingUtil::makeBooleanColumnMappingData      ('primaryEmail__isInvalid'),
                'column_16' => ImportMappingUtil::makeBooleanColumnMappingData      ('primaryEmail__optOut'),
                'column_17' => ImportMappingUtil::makeHasOneColumnMappingData       ('account'),
                'column_18' => ImportMappingUtil::makeDropDownColumnMappingData     ('source'),
                'column_19' => ContactImportTestHelper::makeStateColumnMappingData  (),
                'column_20' => ImportMappingUtil::makeDropDownColumnMappingData     ('industry'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Contacts');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(),
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $contacts = Contact::getAll();
            $this->assertEquals(3, count($contacts));

            $contacts = Contact::getByName('contact1 contact1son');
            $this->assertEquals(1,                         count($contacts[0]));
            $this->assertEquals('contact1',                $contacts[0]->firstName);
            $this->assertEquals('contact1son',             $contacts[0]->lastName);
            $this->assertEquals('president',               $contacts[0]->jobTitle);
            $this->assertEquals(123456,                    $contacts[0]->officePhone);
            $this->assertEquals(555,                       $contacts[0]->officeFax);
            $this->assertEquals('executive',               $contacts[0]->department);
            $this->assertEquals('http://www.contact1.com', $contacts[0]->website);
            $this->assertEquals('desc1',                   $contacts[0]->description);
            $this->assertEquals('city1',                   $contacts[0]->primaryAddress->city);
            $this->assertEquals('country1',                $contacts[0]->primaryAddress->country);
            $this->assertEquals('postal1',                 $contacts[0]->primaryAddress->postalCode);
            $this->assertEquals('state1',                  $contacts[0]->primaryAddress->state);
            $this->assertEquals('street11',                $contacts[0]->primaryAddress->street1);
            $this->assertEquals('street21',                $contacts[0]->primaryAddress->street2);
            $this->assertEquals('a@a.com',                 $contacts[0]->primaryEmail->emailAddress);
            $this->assertEquals(null,                      $contacts[0]->primaryEmail->isInvalid);
            $this->assertEquals(null,                      $contacts[0]->primaryEmail->optOut);
            $this->assertTrue($contacts[0]->account->isSame($account));
            $this->assertEquals('Self-Generated',          $contacts[0]->source->value);
            $this->assertEquals('Qualified',               $contacts[0]->state->name);
            $this->assertEquals('Automotive',              $contacts[0]->industry->value);

            $contacts = Contact::getByName('contact2 contact2son');
            $this->assertEquals(1,                         count($contacts[0]));
            $this->assertEquals('contact2',                $contacts[0]->firstName);
            $this->assertEquals('contact2son',             $contacts[0]->lastName);
            $this->assertEquals('president2',               $contacts[0]->jobTitle);
            $this->assertEquals(223456,                    $contacts[0]->officePhone);
            $this->assertEquals(655,                       $contacts[0]->officeFax);
            $this->assertEquals('executive2',               $contacts[0]->department);
            $this->assertEquals('http://www.contact2.com', $contacts[0]->website);
            $this->assertEquals('desc2',                   $contacts[0]->description);
            $this->assertEquals('city2',                   $contacts[0]->primaryAddress->city);
            $this->assertEquals('country2',                $contacts[0]->primaryAddress->country);
            $this->assertEquals('postal2',                 $contacts[0]->primaryAddress->postalCode);
            $this->assertEquals('state2',                  $contacts[0]->primaryAddress->state);
            $this->assertEquals('street12',                $contacts[0]->primaryAddress->street1);
            $this->assertEquals('street22',                $contacts[0]->primaryAddress->street2);
            $this->assertEquals('b@b.com',                 $contacts[0]->primaryEmail->emailAddress);
            $this->assertEquals(null,                      $contacts[0]->primaryEmail->isInvalid);
            $this->assertEquals(null,                      $contacts[0]->primaryEmail->optOut);
            $this->assertTrue($contacts[0]->account->isSame($account));
            $this->assertEquals('Tradeshow',               $contacts[0]->source->value);
            $this->assertEquals('Customer',                $contacts[0]->state->name);
            $this->assertEquals('Banking',                 $contacts[0]->industry->value);

            $contacts = Contact::getByName('contact3 contact3son');
            $this->assertEquals(1,                         count($contacts[0]));
            $this->assertEquals('contact3',                $contacts[0]->firstName);
            $this->assertEquals('contact3son',             $contacts[0]->lastName);
            $this->assertEquals('president3',              $contacts[0]->jobTitle);
            $this->assertEquals(323456,                    $contacts[0]->officePhone);
            $this->assertEquals(755,                       $contacts[0]->officeFax);
            $this->assertEquals('executive3',              $contacts[0]->department);
            $this->assertEquals('http://www.contact3.com', $contacts[0]->website);
            $this->assertEquals('desc3',                   $contacts[0]->description);
            $this->assertEquals('city3',                   $contacts[0]->primaryAddress->city);
            $this->assertEquals('country3',                $contacts[0]->primaryAddress->country);
            $this->assertEquals('postal3',                 $contacts[0]->primaryAddress->postalCode);
            $this->assertEquals('state3',                  $contacts[0]->primaryAddress->state);
            $this->assertEquals('street13',                $contacts[0]->primaryAddress->street1);
            $this->assertEquals('street23',                $contacts[0]->primaryAddress->street2);
            $this->assertEquals('c@c.com',                 $contacts[0]->primaryEmail->emailAddress);
            $this->assertEquals('1',                       $contacts[0]->primaryEmail->isInvalid);
            $this->assertEquals('1',                       $contacts[0]->primaryEmail->optOut);
            $this->assertTrue($contacts[0]->account->isSame($account));
            $this->assertEquals('Inbound Call',            $contacts[0]->source->value);
            $this->assertEquals('Qualified',               $contacts[0]->state->name);
            $this->assertEquals('Energy',                  $contacts[0]->industry->value);

            //Confirm 3 rows were processed as 'created'.
            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));

            //test the account has 3 contacts
            $account->forget();
            $account = Account::getById($accountId);
            $this->assertEquals(3, $account->contacts->count());
        }
    }
?>