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
     * Product Template Super User Walkthrough.
     * Walkthrough for the super user of all possible actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ProductTemplateSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 1");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 2");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 3");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 4");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 5");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 6");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 7");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 8");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 9");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 10");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 11");
	    ProductTemplateTestHelper::createProductTemplateByName("My Catalog Item 12");
        }

	public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $productTemplates	 = ProductTemplate::getAll();
            $this->assertEquals(12, count($productTemplates));
            $superTemplateId     = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 1');
            $superTemplateId2    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 2');
            $superTemplateId3    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 3');
            $superTemplateId4    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 4');
            $superTemplateId5    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 5');
            $superTemplateId6    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 6');
            $superTemplateId7    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 7');
            $superTemplateId8    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 8');
            $superTemplateId9    = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 9');
            $superTemplateId10   = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 10');
            $superTemplateId11   = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 11');
            $superTemplateId12   = self::getModelIdByModelNameAndName('ProductTemplate', 'My Catalog Item 12');
            $this->setGetArray(array('id' => $superTemplateId));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/edit');
            //Save contact.
            $superTemplate = ProductTemplate::getById($superTemplateId);
            $this->assertEquals('Description', $superTemplate->description);
            $this->setPostArray(array('ProductTemplate' => array('description' => 'Test Description')));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/edit');
            $superTemplate = ProductTemplate::getById($superTemplateId);
            $this->assertEquals('Test Description', $superContact->description);
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'		=> $superTemplateId));
            $this->setPostArray(array('ProductTemplate' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superTemplateId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8,9', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>6</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>12</strong>&#160;records selected for updating') === false);

            //save Model MassEdit for selected Ids
            //Test that the 4 contacts do not have the office phone number we are populating them with.
            $productTemplate1 = ProductTemplate::getById($superTemplateId);
            $productTemplate2 = ProductTemplate::getById($superTemplateId2);
            $productTemplate3 = ProductTemplate::getById($superTemplateId3);
            $productTemplate4 = ProductTemplate::getById($superTemplateId4);
            $this->assertNotEquals('Description 1', $productTemplate1->description);
            $this->assertNotEquals('Description 2', $productTemplate2->description);
            $this->assertNotEquals('Description 3', $productTemplate3->description);
            $this->assertNotEquals('Description 4', $productTemplate4->description);
            $this->setGetArray(array(
                'selectedIds'  => $superTemplateId . ',' . $superTemplateId2, // Not Coding Standard
                'selectAll'    => '',
                'ProductTemplate_page' => 1));
            $this->setPostArray(array(
                'ProductTemplate'	=> array('type' => 2),
                'MassEdit'		=> array('type' => 3)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/massEdit');
            //Test that the 2 contacts have the new office phone number and the other contacts do not.
            $productTemplate1  = ProductTemplate::getById($superTemplateId);
            $productTemplate2  = ProductTemplate::getById($superTemplateId2);
            $productTemplate3  = ProductTemplate::getById($superTemplateId3);
            $productTemplate4  = ProductTemplate::getById($superTemplateId4);
            $productTemplate5  = ProductTemplate::getById($superTemplateId5);
            $productTemplate6  = ProductTemplate::getById($superTemplateId6);
            $productTemplate7  = ProductTemplate::getById($superTemplateId7);
            $productTemplate8  = ProductTemplate::getById($superTemplateId8);
            $productTemplate9  = ProductTemplate::getById($superTemplateId9);
            $productTemplate10 = ProductTemplate::getById($superTemplateId10);
            $productTemplate11 = ProductTemplate::getById($superTemplateId11);
            $productTemplate12 = ProductTemplate::getById($superTemplateId12);
            $this->assertEquals   (2, $productTemplate1->type);
            $this->assertEquals   (3, $productTemplate2->type);
            $this->assertNotEquals(1, $productTemplate3->type);
            $this->assertNotEquals(1, $productTemplate4->type);
            $this->assertNotEquals(1, $productTemplate5->type);
            $this->assertNotEquals(1, $productTemplate6->type);
            $this->assertNotEquals(1, $productTemplate7->type);
            $this->assertNotEquals(1, $productTemplate8->type);
            $this->assertNotEquals(1, $productTemplate9->type);
            $this->assertNotEquals(1, $productTemplate10->type);
            $this->assertNotEquals(1, $productTemplate11->type);
            $this->assertNotEquals(1, $productTemplate12->type);
            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll'    => '1',
                'ProductTemplate_page' => 1));
            $this->setPostArray(array(
                'ProductTemplate'	=> array('type' => 2),
                'MassEdit'		=> array('type' => 3)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/massEdit');
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);
            //Test that all accounts have the new phone number.
            $productTemplate1 = ProductTemplate::getById($superTemplateId);
            $productTemplate2 = ProductTemplate::getById($superTemplateId2);
            $productTemplate3 = ProductTemplate::getById($superTemplateId3);
            $productTemplate4 = ProductTemplate::getById($superTemplateId4);
            $productTemplate5 = ProductTemplate::getById($superTemplateId5);
            $productTemplate6 = ProductTemplate::getById($superTemplateId6);
            $productTemplate7 = ProductTemplate::getById($superTemplateId7);
            $productTemplate8 = ProductTemplate::getById($superTemplateId8);
            $productTemplate9 = ProductTemplate::getById($superTemplateId9);
            $productTemplate10 = ProductTemplate::getById($superTemplateId10);
            $productTemplate11 = ProductTemplate::getById($superTemplateId11);
            $productTemplate12 = ProductTemplate::getById($superTemplateId12);
            $this->assertEquals   (2, $productTemplate1->type);
            $this->assertEquals   (2, $productTemplate2->type);
            $this->assertEquals   (2, $productTemplate3->type);
            $this->assertEquals   (2, $productTemplate4->type);
            $this->assertEquals   (2, $productTemplate5->type);
            $this->assertEquals   (2, $productTemplate6->type);
            $this->assertEquals   (2, $productTemplate7->type);
            $this->assertEquals   (2, $productTemplate8->type);
            $this->assertEquals   (2, $productTemplate9->type);
            $this->assertEquals   (2, $productTemplate10->type);
            $this->assertEquals   (2, $productTemplate11->type);
            $this->assertEquals   (2, $productTemplate12->type);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('productTemplates/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":16') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":25') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":33') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for Product Template
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/autoCompleteAllProductCategoriesForMultiSelectAutoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/modalList');

            //Select a related Opportunity for this contact. Go to the select screen.
//            $contact1->forget();
//            $contact1 = Contact::getById($superTemplateId);
//            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
//                                    'ContactDetailsAndRelationsViewLeftBottomView', $super->id, array());
//            $this->assertEquals(1, count($portlets));
//            $this->assertEquals(2, count($portlets[1]));
//            $opportunity = Opportunity::getById($superOpportunityId);
//            $this->assertEquals(0, $contact1->opportunities->count());
//            $this->assertEquals(0, $opportunity->contacts->count());
//            $this->setGetArray(array(   'portletId'             => $portlets[1][1]->id, //Doesnt matter which portlet we are using
//                                        'relationAttributeName' => 'contacts',
//                                        'relationModuleId'      => 'contacts',
//                                        'relationModelId'       => $superTemplateId,
//                                        'uniqueLayoutId'        => 'ContactDetailsAndRelationsViewLeftBottomView_' .
//                                                                    $portlets[1][1]->id)
//            );
//
//            $this->resetPostArray();
//            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/SelectFromRelatedList');
//            //Now add an opportunity to a contact via the select from related list action.
//            $this->setGetArray(array(   'portletId'             => $portlets[1][1]->id,
//                                        'modelId'               => $superOpportunityId,
//                                        'relationAttributeName' => 'contacts',
//                                        'relationModuleId'      => 'contacts',
//                                        'relationModelId'       => $superTemplateId,
//                                        'uniqueLayoutId'        => 'ContactDetailsAndRelationsViewLeftBottomView_' .
//                                                                    $portlets[1][1]->id)
//            );
//            $this->resetPostArray();
//            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/defaultPortlet/SelectFromRelatedListSave');
//            //Run forget in order to refresh the contact and opportunity showing the new relation
//            $contact1->forget();
//            $opportunity->forget();
//            $contact     = Contact::getById($superTemplateId);
//            $opportunity = Opportunity::getById($superOpportunityId);
//            $this->assertEquals(1,                $opportunity->contacts->count());
//            $this->assertEquals($contact,         $opportunity->contacts[0]);
//            $this->assertEquals(1,                $contact->opportunities->count());
//            $this->assertEquals($opportunity->id, $contact->opportunities[0]->id);
        }

        public function testCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->resetGetArray();

            $currency					= new Currency();
            $currency->code				= 'USD';
            $currency->rateToBase			= 1;
            $currency->save();

            $currencyRec				= Currency::getByCode('USD');

            $currencyValue1Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 500.54);
            $currencyValue2Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 400.54);
            $currencyValue3Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 300.54);

            $productTemplate                            = array();
            $productTemplate['name']                    = 'Red Widget';
            $productTemplate['description']             = 'Description';
            $productTemplate['priceFrequency']          = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $productTemplate['cost']                    = $currencyValue1Array;
            $productTemplate['listPrice']               = $currencyValue2Array;
            $productTemplate['sellPrice']               = $currencyValue3Array;


            $productTemplate['type']                    = ProductTemplate::TYPE_PRODUCT;
            $productTemplate['status']                  = ProductTemplate::STATUS_ACTIVE;
            $sellPriceFormulaArray                      = array('type' => SellPriceFormula::TYPE_DISCOUNT_FROM_LIST, 'discountOrMarkupPercentage' => 10 );

            $productTemplate['sellPriceFormula']	= $sellPriceFormulaArray;
            $this->setPostArray(array('ProductTemplate' => $productTemplate));
            $redirectUrl				= $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/create');

            $productTemplates				= ProductTemplate::getByName('Red Widget');
            $this->assertEquals(1, count($productTemplates));
            $this->assertTrue  ($productTemplates[0]->id > 0);
            $this->assertEquals(400.54, $productTemplates[0]->listPrice->value);
            $this->assertEquals(500.54, $productTemplates[0]->cost->value);
            $this->assertEquals(300.54, $productTemplates[0]->sellPrice->value);
	    $compareRedirectUrl = Yii::app()->createUrl('productTemplates/default/details', array('id' => $productTemplates[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

	public function testSuperUserDeleteAction()
        {
	    $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $productTemplate = ProductTemplateTestHelper::createProductTemplateByName("My New Catalog Item");

            //Delete a product template
            $this->setGetArray(array('id' => $productTemplate->id));
            $this->resetPostArray();
	    $productTemplates = ProductTemplate::getAll();
	    $this->assertEquals(2, count($productTemplates));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/delete');
            $productTemplates = ProductTemplate::getAll();
            $this->assertEquals(1, count($productTemplates));
            try
            {
                ProductTemplate::getById($productTemplate->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>