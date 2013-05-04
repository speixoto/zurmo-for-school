<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
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
            $this->assertEquals('Test Description', $superTemplate->description);
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'		=> $superTemplateId));
            $this->setPostArray(array('ProductTemplate' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superTemplateId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/details');

            //Autocomplete for Product Template
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/autoCompleteAllProductCategoriesForMultiSelectAutoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/modalList');
        }

        public function testSuperUserCreateAction()
        {
            $super					= $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel			= $super;
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
	    $compareRedirectUrl				= Yii::app()->createUrl('productTemplates/default/details', array('id' => $productTemplates[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

	public function testSuperUserDeleteAction()
        {
	    $super			= $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $productTemplate		= ProductTemplateTestHelper::createProductTemplateByName("My New Catalog Item");

            //Delete a product template
            $this->setGetArray(array('id' => $productTemplate->id));
            $this->resetPostArray();
	    $productTemplates		= ProductTemplate::getAll();
	    $this->assertEquals(14, count($productTemplates));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/delete');
            $productTemplates		= ProductTemplate::getAll();
            $this->assertEquals(13, count($productTemplates));
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

	public function testSuperUserModalListForProductPortletAction()
        {
            $super							    = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel					    = $super;
            $this->resetGetArray();
	    $account							    = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
	    OpportunityTestHelper::createOpportunityStagesIfDoesNotExist();
            $opportunity						    = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);

	    $_GET['modalTransferInformation']['sourceIdFieldId']	    = 'product_opportunity_id';
	    $_GET['modalTransferInformation']['sourceNameFieldId']	    = 'product_opportunity_name';
	    $_GET['modalTransferInformation']['sourceModelId']		    = -148;
	    $_GET['modalTransferInformation']['relationModelId']	    = $opportunity->id;
	    $_GET['modalTransferInformation']['relationAttributeName']	    = 'opportunity';
	    $_GET['modalTransferInformation']['portletId']		    = 27;
	    $_GET['modalTransferInformation']['uniqueLayoutId']		    = 'OpportunityDetailsAndRelationsViewRightBottomView_27';
	    $_GET['modalTransferInformation']['relationModuleId']	    = 'opportunities';

	    $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/modalListForProductPortlet');
        }

	/**
         * @deletes selected product templates.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super		= $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $productTemplates	= ProductTemplate::getAll();
            $this->assertEquals(13, count($productTemplates));
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
            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8,9', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massDelete');
	    print $content;
	    exit;
            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;ProductTemplates selected for removal') === false);

             //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>13</strong>&#160;productTemplates selected for removal') === false);

            //MassDelete for selected Record Count
            $productTemplates = ProductTemplate::getAll();
            $this->assertEquals(13, count($productTemplates));

            //MassDelete for selected ids for paged scenario
            $superTemplate1 = ProductTemplate::getById($superTemplateId);
            $superTemplate2 = ProductTemplate::getById($superTemplateId2);
            $superTemplate3 = ProductTemplate::getById($superTemplateId3);
            $superTemplate4 = ProductTemplate::getById($superTemplateId4);
            $superTemplate5 = ProductTemplate::getById($superTemplateId5);
            $superTemplate6 = ProductTemplate::getById($superTemplateId6);
            $superTemplate7 = ProductTemplate::getById($superTemplateId7);

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds'  => $superTemplateId . ',' . $superTemplateId2 . ',' .  // Not Coding Standard
                                  $superTemplateId3 . ',' . $superTemplateId4 . ',' . // Not Coding Standard
                                  $superTemplateId5 . ',' . $superTemplateId6 . ',' . // Not Coding Standard
                                  $superTemplateId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'ProductTemplate_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithExitExceptionAndGetContent('productTemplates/default/massDelete');

            //MassDelete for selected Record Count
            $productTemplates = ProductTemplate::getAll();
            $this->assertEquals(8, count($productTemplates));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds'  => $superTemplateId . ',' . $superTemplateId2 . ',' .  // Not Coding Standard
                                  $superTemplateId3 . ',' . $superTemplateId4 . ',' . // Not Coding Standard
                                  $superTemplateId5 . ',' . $superTemplateId6 . ',' . // Not Coding Standard
                                  $superTemplateId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'ProductTemplate_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/massDeleteProgress');

            //MassDelete for selected Record Count
            $productTemplates = ProductTemplate::getAll();
            $this->assertEquals(7, count($productTemplates));
        }
    }
?>