<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ProductsSuperUserSaveWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Make sure everyone group is created
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group->save();

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpportunity', $super, $account);
            ProductTestHelper::createProductStagesIfDoesNotExist();
            ProductTestHelper::createProductByNameForOwner("My Product 1", $super);
            //Setup test data owned by the super user.
            ProductTemplateTestHelper::createProductTemplateByName('My Product Template');
        }

        public function testSuperUserEditControllerAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $contacts           = Contact::getByName('superContact superContactson');
            $products           = Product::getAll();
            $this->assertEquals(1, count($contacts));
            $this->assertEquals(1, count($products));
            $superProductId     = self::getModelIdByModelNameAndName('Product', 'My Product 1');
            $this->setGetArray(array('id' => $superProductId));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');
            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->setPostArray(
                                array(
                                        'Product' => array(
                                                            'contact' => array('id' => $contacts[0]->id),
                                                            'opportunity' => array('id' => ''),
                                                            'owner' => array('id' => $super->id),
                                                            'explicitReadWriteModelPermissions' => array(
                                                                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP,
                                                                    'nonEveryoneGroup' => ''
                                                                )
                                                          )
                                      )
                                );

            //Test having a failed validation on the product during save.
            $this->setGetArray (array('id'      => $superProductId));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('products/default/edit');
        }

        public function testSuperUserEditProductPortletControllerAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $products           = Product::getAll();
            $this->assertEquals(1, count($products));
            $superProductId     = self::getModelIdByModelNameAndName('Product', 'My Product 1');
            $this->setGetArray(array('attribute' => 'sellPrice', 'item' => $superProductId, 'value' => '300.54'));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/update', true);

            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->assertEquals(300.54, $superProduct->sellPrice->value);

            $this->setGetArray(array('attribute' => 'sellPrice', 'item' => $superProductId, 'value' => '3000.54'));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/update', true);

            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->assertEquals(3000.54, $superProduct->sellPrice->value);

            $this->setGetArray(array('attribute' => 'quantity', 'item' => $superProductId, 'value' => '10'));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/update', true);
            //Save product.
            $superProduct       = Product::getById($superProductId);
            $this->assertEquals(10, $superProduct->quantity);
        }

        public function testSuperUserCreateProductFromProductTemplateControllerAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $products           = Product::getAll();
            $this->assertEquals(1, count($products));

            $superProductTemplateId  = self::getModelIdByModelNameAndName('ProductTemplate', 'My Product Template');
            $productTemplate    = ProductTemplate::getById($superProductTemplateId);
            $productCategory    = ProductCategoryTestHelper::createProductCategoryByName("Test Category");
            $productCategoryII  = ProductCategoryTestHelper::createProductCategoryByName("Test CategoryII");
            $productTemplate->productCategories->add($productCategory);
            $productTemplate->productCategories->add($productCategoryII);
            $productTemplate->save();
            $superProductTemplateId   = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);

            $accountId = self::getModelIdByModelNameAndName('Account', 'superAccount');
            $this->setGetArray(array('relationModuleId'         => 'accounts',
                                      'portletId'               => '1',
                                      'uniqueLayoutId'          => 'AccountDetailsAndRelationsView_1',
                                      'id'                      => $superProductTemplateId,
                                      'relationModelId'         => $accountId,
                                      'relationAttributeName'   => 'account',
                                      'relationModelClassName'  => 'Account',
                                      'redirect'                => '0'
                                    )
                              );
            $this->runControllerWithNoExceptionsAndGetContent('products/default/createProductFromProductTemplate', true);
            $products           = Product::getAll();
            $this->assertEquals(2, count($products));

            $latestProduct = $products[1];
            $productSavedCategory   = $latestProduct->productCategories[0];
            $productSavedCategoryII = $latestProduct->productCategories[1];
            $this->assertEquals('Test Category',   $productSavedCategory->name);
            $this->assertEquals('Test CategoryII', $productSavedCategoryII->name);
            $this->assertEquals('My Product Template', $latestProduct->name);
            $this->assertEquals(500.54, $latestProduct->sellPrice->value);

            //When created from contact should copy the contact account to product
            $contactId = self::getModelIdByModelNameAndName('Contact', 'superContact superContactson');
            $this->setGetArray(array('relationModuleId'         => 'accounts',
                                     'portletId'               => '1',
                                     'uniqueLayoutId'          => 'ContactDetailsAndRelationsView_1',
                                     'id'                      => $superProductTemplateId,
                                     'relationModelId'         => $contactId,
                                     'relationAttributeName'   => 'contact',
                                     'relationModelClassName'  => 'Contact',
                                     'redirect'                => '0'
                )
            );
            $this->runControllerWithNoExceptionsAndGetContent('products/default/createProductFromProductTemplate', true);
            $products           = Product::getAll();
            $this->assertEquals(3, count($products));

            $latestProduct = $products[2];
            $this->assertEquals($accountId, $latestProduct->account->id);
        }

        public function testSuperUserCreateFromRelationAction()
        {
            $super         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $products      = Product::getAll();
            $productsCount = count($products);

            $accountId     = self::getModelIdByModelNameAndName('Account',     'superAccount');
            $contactId     = self::getModelIdByModelNameAndName('Contact',     'superContact superContactson');
            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpportunity');

            //Create a new product from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                                        'relationModelId'       => $accountId,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));

            $currency                                   = new Currency();
            $currency->code                             = 'USD';
            $currency->rateToBase                       = 1;
            $currency->save();
            $currencyRec                                = Currency::getByCode('USD');
            $currencyValue1Array                        = array('currency' => array('id' => $currencyRec->id), 'value' => 500.54);

            $product['name']                            = 'myUltraNewProduct';
            $product['quantity']                        = 50;
            $product['priceFrequency']                  = ProductTemplate::PRICE_FREQUENCY_ONE_TIME;
            $product['sellPrice']                       = $currencyValue1Array;
            $product['type']                            = ProductTemplate::TYPE_PRODUCT;
            $product['stage']['value']                  = Product::OPEN_STAGE;
            $this->setPostArray(array('Product' => $product));

            $this->runControllerWithRedirectExceptionAndGetContent('products/default/createFromRelation');
            $products = Product::getByName('myUltraNewProduct');
            $this->assertEquals(1, count($products));
            $this->assertTrue($products[0]->id > 0);
            $this->assertTrue($products[0]->owner == $super);
            $this->assertEquals($accountId,                                 $products[0]->account->id);
            $this->assertEquals('50',                                       $products[0]->quantity);
            $this->assertEquals('500.54',                                   $products[0]->sellPrice->value);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ONE_TIME,  $products[0]->priceFrequency);
            $this->assertEquals(ProductTemplate::TYPE_PRODUCT,              $products[0]->type);
            $this->assertEquals(Product::OPEN_STAGE,                        $products[0]->stage->value);
            $products = Product::getAll();
            $this->assertEquals(++$productsCount, count($products));

            //Create a new product from a related contact.
            $this->setGetArray(array(   'relationAttributeName' => 'contact',
                                        'relationModelId'       => $contactId,
                                        'relationModuleId'      => 'contacts',
                                        'redirectUrl'           => 'someRedirect'));

            $product['name']                            = 'myUltraNewProduct2';
            $product['quantity']                        = 51;
            $product['priceFrequency']                  = ProductTemplate::PRICE_FREQUENCY_ANNUALLY;
            $product['sellPrice']                       = $currencyValue1Array;
            $product['type']                            = ProductTemplate::TYPE_SERVICE;
            $product['stage']['value']                  = Product::OPEN_STAGE;
            $this->setPostArray(array('Product' => $product));

            $this->runControllerWithRedirectExceptionAndGetContent('products/default/createFromRelation');
            $products = Product::getByName('myUltraNewProduct2');
            $this->assertEquals(1, count($products));
            $this->assertTrue($products[0]->id > 0);
            $this->assertTrue($products[0]->owner == $super);
            $this->assertEquals($accountId,                                 $products[0]->account->id);
            $this->assertEquals($contactId,                                 $products[0]->contact->id);
            $this->assertEquals('51',                                       $products[0]->quantity);
            $this->assertEquals('500.54',                                   $products[0]->sellPrice->value);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ANNUALLY,  $products[0]->priceFrequency);
            $this->assertEquals(ProductTemplate::TYPE_SERVICE,              $products[0]->type);
            $this->assertEquals(Product::OPEN_STAGE,                        $products[0]->stage->value);
            $products = Product::getAll();
            $this->assertEquals(++$productsCount, count($products));

            //Create a new product from a related opportunity.
            $this->setGetArray(array(   'relationAttributeName' => 'opportunity',
                                        'relationModelId'       => $opportunityId,
                                        'relationModuleId'      => 'opportunities',
                                        'redirectUrl'           => 'someRedirect'));

            $product['name']                            = 'myUltraNewProduct3';
            $product['quantity']                        = 51;
            $product['priceFrequency']                  = ProductTemplate::PRICE_FREQUENCY_ANNUALLY;
            $product['sellPrice']                       = $currencyValue1Array;
            $product['type']                            = ProductTemplate::TYPE_SERVICE;
            $product['stage']['value']                  = Product::OPEN_STAGE;
            $this->setPostArray(array('Product' => $product));

            $this->runControllerWithRedirectExceptionAndGetContent('products/default/createFromRelation');
            $products = Product::getByName('myUltraNewProduct3');
            $this->assertEquals(1, count($products));
            $this->assertTrue($products[0]->id > 0);
            $this->assertTrue($products[0]->owner == $super);
            $this->assertEquals($accountId,                                 $products[0]->account->id);
            $this->assertEquals($opportunityId,                             $products[0]->opportunity->id);
            $this->assertEquals('51',                                       $products[0]->quantity);
            $this->assertEquals('500.54',                                   $products[0]->sellPrice->value);
            $this->assertEquals(ProductTemplate::PRICE_FREQUENCY_ANNUALLY,  $products[0]->priceFrequency);
            $this->assertEquals(ProductTemplate::TYPE_SERVICE,              $products[0]->type);
            $this->assertEquals(Product::OPEN_STAGE,                        $products[0]->stage->value);
            $products = Product::getAll();
            $this->assertEquals(++$productsCount, count($products));
        }
    }
?>