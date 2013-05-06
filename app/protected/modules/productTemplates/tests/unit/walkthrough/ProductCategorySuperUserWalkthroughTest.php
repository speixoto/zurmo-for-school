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
    class ProductCategorySuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            ProductCategoryTestHelper::createProductCategoryByName("My Category 1");
            $category1 = ProductCategory::getByName("My Category 1");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 2");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 3", $category1);
            ProductCategoryTestHelper::createProductCategoryByName("My Category 4");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 5");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 6");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 7");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 8");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 9");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 10");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 11");
            ProductCategoryTestHelper::createProductCategoryByName("My Category 12");
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/index');
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $productTemplates	 = ProductCategory::getAll();
            $this->assertEquals(12, count($productTemplates));
            $superCategoryId     = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 1');
            $superCategoryId2    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 2');
            $superCategoryId3    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 3');
            $superCategoryId4    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 4');
            $superCategoryId5    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 5');
            $superCategoryId6    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 6');
            $superCategoryId7    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 7');
            $superCategoryId8    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 8');
            $superCategoryId9    = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 9');
            $superCategoryId10   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 10');
            $superCategoryId11   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 11');
            $superCategoryId12   = self::getModelIdByModelNameAndName('ProductCategory', 'My Category 12');
            $this->setGetArray(array('id' => $superCategoryId));
            $this->runControllerWithNoExceptionsAndGetContent('productTemplates/category/edit');

            $superCategory = ProductCategory::getById($superCategoryId2);
            $this->assertEquals(0, count($superCategory->productCategories));
            $this->setPostArray(array('ProductCategory' => array('productCategory' => array('id' => $superCategoryId))));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/default/edit');
            $superCategory = ProductCategory::getById($superCategoryId2);
            $this->assertEquals(1, count($superCategory->productCategories));
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'		=> $superCategoryId2));
            $this->setPostArray(array('ProductCategory' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('productTemplates/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superCategoryId));
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
            $super                                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel                 = $super;
            $this->resetGetArray();

            $currency                                   = new Currency();
            $currency->code                             = 'USD';
            $currency->rateToBase                       = 1;
            $currency->save();

            $currencyRec                                = Currency::getByCode('USD');

            $currencyValue1Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 500.54);
            $currencyValue2Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 400.54);
            $currencyValue3Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 300.54);

            $productTemplate                            = array();
            $productTemplate['name']                    = 'Red Widget';
            $productTemplate['description']             = 'Description';
            $productTemplate['priceFrequency']          = ProductCategory::PRICE_FREQUENCY_ONE_TIME;
            $productTemplate['cost']                    = $currencyValue1Array;
            $productTemplate['listPrice']               = $currencyValue2Array;
            $productTemplate['sellPrice']               = $currencyValue3Array;

            $productTemplate['type']                    = ProductCategory::TYPE_PRODUCT;
            $productTemplate['status']                  = ProductCategory::STATUS_ACTIVE;
            $sellPriceFormulaArray                      = array('type' => SellPriceFormula::TYPE_DISCOUNT_FROM_LIST, 'discountOrMarkupPercentage' => 10 );

            $productTemplate['sellPriceFormula']        = $sellPriceFormulaArray;
            $this->setPostArray(array('ProductCategory' => $productTemplate));
            $redirectUrl                                = $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/create');

            $productTemplates                           = ProductCategory::getByName('Red Widget');
            $this->assertEquals(1, count($productTemplates));
            $this->assertTrue  ($productTemplates[0]->id > 0);
            $this->assertEquals(400.54, $productTemplates[0]->listPrice->value);
            $this->assertEquals(500.54, $productTemplates[0]->cost->value);
            $this->assertEquals(300.54, $productTemplates[0]->sellPrice->value);
            $compareRedirectUrl                         = Yii::app()->createUrl('productTemplates/default/details', array('id' => $productTemplates[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $productCategory            = ProductCategoryTestHelper::createProductCategoryByName("My New Category");

            //Delete a product template
            $this->setGetArray(array('id' => $productCategory->id));
            $this->resetPostArray();
            $productTemplates		= ProductCategory::getAll();
            $this->assertEquals(14, count($productTemplates));
            $this->runControllerWithRedirectExceptionAndGetContent('productTemplates/category/delete');
            $productTemplates		= ProductCategory::getAll();
            $this->assertEquals(13, count($productTemplates));
            try
            {
                ProductCategory::getById($productCategory->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>