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
     * Opportunities Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class OpportunitiesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner        ('superAccount',  $super);
            AccountTestHelper::createAccountByNameForOwner                   ('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact',  $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner        ('superContact2', $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp',      $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp2',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp3',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp4',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp5',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp6',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp7',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp8',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp9',     $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp10',    $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp11',    $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp12',    $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                                  (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $opportunities = Opportunity::getAll();
            $this->assertEquals(12, count($opportunities));
            $superOpportunityId   = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $superOpportunityId2  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp2');
            $superOpportunityId3  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp3');
            $superOpportunityId4  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp4');
            $superOpportunityId5  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp5');
            $superOpportunityId6  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp6');
            $superOpportunityId7  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp7');
            $superOpportunityId8  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp8');
            $superOpportunityId9  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp9');
            $superOpportunityId10 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp10');
            $superOpportunityId11 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp11');
            $superOpportunityId12 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp12');
            $this->setGetArray(array('id' => $superOpportunityId));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/edit');
            //Save opportunity.
            $superOpportunity = Opportunity::getById($superOpportunityId);
            $this->assertEquals(null, $superOpportunity->description);
            $this->setPostArray(array('Opportunity' => array('description' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/edit');
            $superOpportunity = Opportunity::getById($superOpportunityId);
            $this->assertEquals('456765421', $superOpportunity->description);
            //Test having a failed validation on the opportunity during save.
            $this->setGetArray (array('id'      => $superOpportunityId));
            $this->setPostArray(array('Opportunity' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superOpportunityId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8,9', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>6</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>12</strong>&#160;records selected for updating') === false);

            //save Model MassEdit for selected Ids
            //Test that the 2 contacts do not have the office phone number we are populating them with.
            $opportunity1 = Opportunity::getById($superOpportunityId);
            $opportunity2 = Opportunity::getById($superOpportunityId2);
            $opportunity3 = Opportunity::getById($superOpportunityId3);
            $opportunity4 = Opportunity::getById($superOpportunityId4);
            $this->assertNotEquals('7788', $opportunity1->description);
            $this->assertNotEquals('7788', $opportunity2->description);
            $this->assertNotEquals('7788', $opportunity3->description);
            $this->assertNotEquals('7788', $opportunity4->description);
            $this->setGetArray(array(
                'selectedIds' => $superOpportunityId . ',' . $superOpportunityId2, // Not Coding Standard
                'selectAll' => '',
                'Opportunity_page' => 1));
            $this->setPostArray(array(
                'Opportunity'  => array('description' => '7788'),
                'MassEdit' => array('description' => 1)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/massEdit');
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);
            //Test that the 2 opportunities have the new office phone number and the other contacts do not.
            $opportunity1  = Opportunity::getById($superOpportunityId);
            $opportunity2  = Opportunity::getById($superOpportunityId2);
            $opportunity3  = Opportunity::getById($superOpportunityId3);
            $opportunity4  = Opportunity::getById($superOpportunityId4);
            $opportunity5  = Opportunity::getById($superOpportunityId5);
            $opportunity6  = Opportunity::getById($superOpportunityId6);
            $opportunity7  = Opportunity::getById($superOpportunityId7);
            $opportunity8  = Opportunity::getById($superOpportunityId8);
            $opportunity9  = Opportunity::getById($superOpportunityId9);
            $opportunity10 = Opportunity::getById($superOpportunityId10);
            $opportunity11 = Opportunity::getById($superOpportunityId11);
            $opportunity12 = Opportunity::getById($superOpportunityId12);
            $this->assertEquals('7788', $opportunity1->description);
            $this->assertEquals('7788', $opportunity2->description);
            $this->assertNotEquals('7788', $opportunity3->description);
            $this->assertNotEquals('7788', $opportunity4->description);
            $this->assertNotEquals('7788', $opportunity5->description);
            $this->assertNotEquals('7788', $opportunity6->description);
            $this->assertNotEquals('7788', $opportunity7->description);
            $this->assertNotEquals('7788', $opportunity8->description);
            $this->assertNotEquals('7788', $opportunity9->description);
            $this->assertNotEquals('7788', $opportunity10->description);
            $this->assertNotEquals('7788', $opportunity11->description);
            $this->assertNotEquals('7788', $opportunity12->description);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',
                'Opportunity_page' => 1));
            $this->setPostArray(array(
                'Opportunity'  => array('description' => '6654'),
                'MassEdit' => array('description' => 1)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/massEdit');
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);
            //Test that all opportunities have the new description.
            $opportunity1 = Opportunity::getById($superOpportunityId);
            $opportunity2 = Opportunity::getById($superOpportunityId2);
            $opportunity3 = Opportunity::getById($superOpportunityId3);
            $opportunity4 = Opportunity::getById($superOpportunityId4);
            $opportunity5 = Opportunity::getById($superOpportunityId5);
            $opportunity6 = Opportunity::getById($superOpportunityId6);
            $opportunity7 = Opportunity::getById($superOpportunityId7);
            $opportunity8 = Opportunity::getById($superOpportunityId8);
            $opportunity9 = Opportunity::getById($superOpportunityId9);
            $opportunity10 = Opportunity::getById($superOpportunityId10);
            $opportunity11 = Opportunity::getById($superOpportunityId11);
            $opportunity12 = Opportunity::getById($superOpportunityId12);
            $this->assertEquals('6654', $opportunity1->description);
            $this->assertEquals('6654', $opportunity2->description);
            $this->assertEquals('6654', $opportunity3->description);
            $this->assertEquals('6654', $opportunity4->description);
            $this->assertEquals('6654', $opportunity5->description);
            $this->assertEquals('6654', $opportunity6->description);
            $this->assertEquals('6654', $opportunity7->description);
            $this->assertEquals('6654', $opportunity8->description);
            $this->assertEquals('6654', $opportunity9->description);
            $this->assertEquals('6654', $opportunity10->description);
            $this->assertEquals('6654', $opportunity11->description);
            $this->assertEquals('6654', $opportunity12->description);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('opportunities/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Opportunity_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":16') === false);
            $this->setGetArray(array('selectAll' => '1', 'Opportunity_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":25') === false);
            $this->setGetArray(array('selectAll' => '1', 'Opportunity_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":33') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //save Model MassEdit for selected Ids
            //Test that the 2 contacts do not have the closed date populating them with.
            //Test that closed dates are properly updated
            $opportunity1 = Opportunity::getById($superOpportunityId);
            $opportunity2 = Opportunity::getById($superOpportunityId2);
            $opportunity3 = Opportunity::getById($superOpportunityId3);
            $opportunity4 = Opportunity::getById($superOpportunityId4);
            $this->assertNotEquals('2012-12-05', $opportunity1->closeDate);
            $this->assertNotEquals('2012-12-05', $opportunity2->closeDate);
            $this->assertNotEquals('2012-12-05', $opportunity3->closeDate);
            $this->assertNotEquals('2012-12-05', $opportunity4->closeDate);
            $this->setGetArray(array(
                'selectedIds' => $superOpportunityId . ',' . $superOpportunityId2, // Not Coding Standard
                'selectAll' => '',
                'Opportunity_page' => 1));
            $this->setPostArray(array(
                'Opportunity'  => array('closeDate' => '12/5/12'),
                'MassEdit' => array('closeDate' => 1)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $content = $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/massEdit');

            $opportunity1 = Opportunity::getById($superOpportunityId);
            $opportunity2 = Opportunity::getById($superOpportunityId2);
            $opportunity3 = Opportunity::getById($superOpportunityId3);
            $opportunity4 = Opportunity::getById($superOpportunityId4);
            $this->assertEquals('2012-12-05', $opportunity1->closeDate);
            $this->assertEquals('2012-12-05', $opportunity2->closeDate);
            $this->assertNotEquals('2012-12-05', $opportunity3->closeDate);
            $this->assertNotEquals('2012-12-05', $opportunity4->closeDate);

            //Autocomplete for Opportunity
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/modalList');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $superOpportunityId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/auditEventsModalList');

            //Select a related Opportunity for this contact. Go to the select screen.
            $superContactId     = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $opportunity1->forget();
            $opportunity = Opportunity::getById($superOpportunityId);
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'OpportunityDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals(1, count($portlets));
            $this->assertEquals(2, count($portlets[1]));
            $contact = Contact::getById($superContactId);
            $this->assertEquals(0, $contact->opportunities->count());
            $this->assertEquals(0, $opportunity->contacts->count());
            $this->setGetArray(array('portletId'             => $portlets[1][1]->id, //Doesnt matter which portlet we are using
                                     'relationAttributeName' => 'opportunities',
                                     'relationModuleId'      => 'opportunities',
                                     'relationModelId'       => $superOpportunityId,
                                     'uniqueLayoutId'        => 'OpportunityDetailsAndRelationsViewLeftBottomView_' .
                                                                $portlets[1][1]->id)
            );

            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/SelectFromRelatedList');
            //Now add an opportunity to a contact via the select from related list action.
            $this->setGetArray(array(   'portletId'             => $portlets[1][1]->id,
                                        'modelId'               => $superContactId,
                                        'relationAttributeName' => 'opportunities',
                                        'relationModuleId'      => 'opportunities',
                                        'relationModelId'       => $superOpportunityId,
                                        'uniqueLayoutId'        => 'OpportunityDetailsAndRelationsViewLeftBottomView_' .
                                                                   $portlets[1][1]->id)
            );
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/defaultPortlet/SelectFromRelatedListSave');
            //Run forget in order to refresh the contact and opportunity showing the new relation
            $contact->forget();
            $opportunity->forget();
            $contact     = Contact::getById($superContactId);
            $opportunity = Opportunity::getById($superOpportunityId);
            $this->assertEquals(1,                $opportunity->contacts->count());
            $this->assertEquals($contact,         $opportunity->contacts[0]);
            $this->assertEquals(1,                $contact->opportunities->count());
            $this->assertEquals($opportunity->id, $contact->opportunities[0]->id);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superOpportunityId2 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp2');
            //Save a layout change. Collapse all portlets in the Opportunity Details View.
            //At this point portlets for this view should be created because we have
            //already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'OpportunityDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (2, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['OpportunityDetailsAndRelationsViewLeftBottomView_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'OpportunityDetailsAndRelationsViewLeftBottomView_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 3 portlets.
            $this->assertEquals(2, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => 'OpportunityDetailsAndRelationsViewLeftBottomView',
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed and moved to the first column.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            'OpportunityDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (2, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            foreach ($portlets as $column => $columns)
            {
                foreach ($columns as $position => $positionPortlets)
                {
                    $this->assertEquals('1', $positionPortlets->collapsed);
                }
            }
            //Load Details View again to make sure everything is ok after the layout change.
            $this->setGetArray(array('id' => $superOpportunityId2));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/details');
        }

        /**
         * @depends testSuperUserDefaultPortletControllerActions
         */
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superOpportunityId4 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp4');
            //Delete an opportunity.
            $this->setGetArray(array('id' => $superOpportunityId4));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/delete');
            $opportunities = Opportunity::getAll();
            $this->assertEquals(11, count($opportunities));
            try
            {
                Contact::getById($superOpportunityId4);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $currencies    = Currency::getAll();
            //Create a new opportunity.
            $this->resetGetArray();
            $this->setPostArray(array('Opportunity' => array(
                                            'name'        => 'myNewOpportunity',
                                            'description' => '456765421',
                                            'closeDate'   => '11/1/11',
                                            'amount' => array(  'value' => '545',
                                                                'currency' => array('id' => $currencies[0]->id)),
                                            'stage'       => array('value' => 'Negotiating'))));
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/create');
            $opportunities = Opportunity::getByName('myNewOpportunity');
            $this->assertEquals(1, count($opportunities));
            $this->assertTrue  ($opportunities[0]->id > 0);
            $this->assertTrue  ($opportunities[0]->owner == $super);
            $this->assertEquals('456765421',   $opportunities[0]->description);
            $this->assertEquals('545',         $opportunities[0]->amount->value);
            $this->assertEquals('2011-11-01',  $opportunities[0]->closeDate);
            $this->assertEquals('Negotiating', $opportunities[0]->stage->value);
            $opportunities = Opportunity::getAll();
            $this->assertEquals(12, count($opportunities));

            //todo: test save with account.
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserCreateFromRelationAction()
        {
            $super         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $currencies    = Currency::getAll();
            $opportunities      = Opportunity::getAll();
            $this->assertEquals(12, count($opportunities));
            $account       = Account::getByName('superAccount2');
            $contact       = Contact::getByName('superContact2 superContact2son');
            $this->assertEquals(1, count($contact));

            //Create a new contact from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                                        'relationModelId'       => $account[0]->id,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('Opportunity' => array(
                                        'name'        => 'myUltraNewOpportunity',
                                        'description' => '456765421',
                                        'closeDate'   => '11/1/11',
                                        'amount' => array(  'value' => '545',
                                                            'currency' => array('id' => $currencies[0]->id)),
                                        'stage'       => array('value' => 'Negotiating'))));
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/createFromRelation');
            $opportunities = Opportunity::getByName('myUltraNewOpportunity');
            $this->assertEquals(1, count($opportunities));
            $this->assertTrue($opportunities[0]->id > 0);
            $this->assertTrue($opportunities[0]->owner   == $super);
            $this->assertTrue($opportunities[0]->account == $account[0]);
            $this->assertEquals('456765421',   $opportunities[0]->description);
            $this->assertEquals('545',         $opportunities[0]->amount->value);
            $this->assertEquals('2011-11-01',  $opportunities[0]->closeDate);
            $this->assertEquals('Negotiating', $opportunities[0]->stage->value);
            $opportunities      = Opportunity::getAll();
            $this->assertEquals(13, count($opportunities));

            //Create a new contact from a related opportunity
            $this->setGetArray(array(   'relationAttributeName' => 'contacts',
                                        'relationModelId'       => $contact[0]->id,
                                        'relationModuleId'      => 'contacts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('Opportunity' => array(
                                        'name'        => 'mySuperNewOpportunity',
                                        'description' => '456765421',
                                        'closeDate'   => '11/1/11',
                                        'amount' => array(  'value' => '545',
                                                            'currency' => array('id' => $currencies[0]->id)),
                                        'stage'       => array('value' => 'Negotiating'))));
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/default/createFromRelation');
            $opportunities = Opportunity::getByName('mySuperNewOpportunity');
            $this->assertEquals(1, count($opportunities));
            $this->assertTrue(                 $opportunities[0]->id > 0);
            $this->assertTrue(                 $opportunities[0]->owner   == $super);
            $this->assertEquals(1,             $opportunities[0]->contacts->count());
            $this->assertTrue(                 $opportunities[0]->contacts[0] == $contact[0]);
            $this->assertEquals('456765421',   $opportunities[0]->description);
            $this->assertEquals('545',         $opportunities[0]->amount->value);
            $this->assertEquals('2011-11-01',  $opportunities[0]->closeDate);
            $this->assertEquals('Negotiating', $opportunities[0]->stage->value);
            $opportunities      = Opportunity::getAll();
            $this->assertEquals(14, count($opportunities));

            //todo: test save with account.
        }

        /**
         * @deletes selected leads.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $opportunities = Opportunity::getAll();
            $this->assertEquals(14, count($opportunities));
            $superOpportunityId   = self::getModelIdByModelNameAndName('Opportunity', 'mySuperNewOpportunity');
            $superOpportunityId2  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp2');
            $superOpportunityId3  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp3');
            $superOpportunityId4  = self::getModelIdByModelNameAndName('Opportunity', 'myNewOpportunity');
            $superOpportunityId5  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp5');
            $superOpportunityId6  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp6');
            $superOpportunityId7  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp7');
            $superOpportunityId8  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp8');
            $superOpportunityId9  = self::getModelIdByModelNameAndName('Opportunity', 'superOpp9');
            $superOpportunityId10 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp10');
            $superOpportunityId11 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp11');
            $superOpportunityId12 = self::getModelIdByModelNameAndName('Opportunity', 'superOpp12');
            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8,9', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;Opportunities selected for removal') === false);

            //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>14</strong>&#160;Opportunities selected for removal') === false);

            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds' => $superOpportunityId  . ',' . $superOpportunityId2 . ',' . // Not Coding Standard
                                 $superOpportunityId3 . ',' . $superOpportunityId4 . ',' . // Not Coding Standard
                                 $superOpportunityId5 . ',' . $superOpportunityId6,        // Not Coding Standard
                'selectAll'        => '',
                'massDelete'       => '',
                'Opportunity_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $this->runControllerWithExitExceptionAndGetContent('opportunities/default/massDelete');

            //MassDelete for selected Record Count
            $Opportunities = Opportunity::getAll();
            $this->assertEquals(9, count($Opportunities));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds' => $superOpportunityId . ',' . $superOpportunityId2 . ',' .  // Not Coding Standard
                                 $superOpportunityId3 . ',' . $superOpportunityId4 . ',' . // Not Coding Standard
                                 $superOpportunityId5 . ',' . $superOpportunityId6,        // Not Coding Standard
                'selectAll'        => '',
                'massDelete'       => '',
                'Opportunity_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massDeleteProgress');

           //MassDelete for selected Record Count
            $Opportunities = Opportunity::getAll();
            $this->assertEquals(8, count($Opportunities));
        }

         /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $Opportunities = Opportunity::getAll();
            $this->assertEquals(8, count($Opportunities));

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Opportunity_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 8));
            //Run Mass Delete using progress save for page1.
            $this->runControllerWithExitExceptionAndGetContent('opportunities/default/massDelete');

            //check for previous mass delete progress
            $Opportunities = Opportunity::getAll();
            $this->assertEquals(3, count($Opportunities));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Opportunity_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 8));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/massDeleteProgress');

            //calculating lead's count
            $Opportunities = Opportunity::getAll();
            $this->assertEquals(0, count($Opportunities));
        }
    }
?>