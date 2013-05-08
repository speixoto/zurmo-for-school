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
    * Designer Module Walkthrough of Products.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation search, edit and delete of the Opportunity based on the custom fields.
    */
    class ProductsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Create a Product for testing.
            ProductTestHelper::createProductByNameForOwner('superProduct', $super);
        }

         public function testSuperUserProductDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Product Modules Menu.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Opportunity module.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Opportunity module.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Product',  ProductsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Products', ProductsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('product',  ProductsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('products', ProductsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserOpportunityDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForProductsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('ProductsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('ProductsModule', 'currency');
            $this->createDateCustomFieldByModule                ('ProductsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('ProductsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('ProductsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('ProductsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductsModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductsModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('ProductsModule', 'citylist');
            $this->createIntegerCustomFieldByModule             ('ProductsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('ProductsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('ProductsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('ProductsModule', 'calcnumber');
            $this->createDropDownDependencyCustomFieldByModule  ('ProductsModule', 'dropdowndep');
            $this->createPhoneCustomFieldByModule               ('ProductsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('ProductsModule', 'radio');
            $this->createTextCustomFieldByModule                ('ProductsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('ProductsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('ProductsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForProductsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForProductsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to ProductEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductEditAndDetailsView'));
            $layout = ProductsDesignerWalkthroughHelperUtil::getProductEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesSearchView.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsSearchView'));
            $layout = ProductsDesignerWalkthroughHelperUtil::getProductsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesListView.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsListView'));
            $layout = ProductsDesignerWalkthroughHelperUtil::getProductsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpportunitiesRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'ProductsModule',
                                     'viewClassName'   => 'ProductsRelatedListView'));
            $layout = ProductsDesignerWalkthroughHelperUtil::getProductsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForProductsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForProductsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superProductId = self::getModelIdByModelNameAndName ('Product', 'superProduct');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('products/default/create');
            $this->setGetArray(array('id' => $superProductId));
            $this->runControllerWithNoExceptionsAndGetContent('products/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('products/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('products/default/list');
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForProductsModule
         */
        public function testCreateAProductAfterTheCustomFieldsArePlacedForProductsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Retrieve the account id and the super account id.
            $accountId   = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superUserId = $super->id;

            //Create a new product based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Product' => array(
                            'name'               => 'myNewProduct',
                            'owner'              => array('id' => $superUserId),
                            'type'               => 1,
                            'sellPrice'          => array ( 'currency' => array ( 'id' => 1 ), 'value' => 200 ),
                            'account'            => array('id' => $accountId),
                            'quantity'           => 10,
                            'stage'              => array('value' => 'Open'),
                            'explicitReadWriteModelPermissions' => array('nonEveryoneGroup' => 4, 'type' => 1),
                            'checkboxCstm'                      => '1',
                            'currencyCstm'                      => array('value'    => 45,
                                                                         'currency' => array('id' => $baseCurrency->id)),
                            'dateCstm'                          => $date,
                            'datetimeCstm'                      => $datetime,
                            'decimalCstm'                       => '123',
                            'picklistCstm'                      => array('value' => 'a'),
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
            $this->runControllerWithRedirectExceptionAndGetUrl('products/default/create');

            //Check the details if they are saved properly for the custom fields.
            $productId = self::getModelIdByModelNameAndName('Product', 'myNewProduct');
            $product   = Product::getById($productId);

            //Retrieve the permission of the opportunity.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($product);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($product->name                       , 'myNewProduct');
            $this->assertEquals($product->quantity                   , 10);
            $this->assertEquals($product->sellPrice->value           , 200.00);
            $this->assertEquals($product->account->id                , $accountId);
            $this->assertEquals($product->type                       , 1);
            $this->assertEquals($product->stage->value               , 'Open');
            $this->assertEquals($product->owner->id                  , $superUserId);
            $this->assertEquals(0                                        , count($readWritePermitables));
            $this->assertEquals(0                                        , count($readOnlyPermitables));
            $this->assertEquals($product->checkboxCstm               , '1');
            $this->assertEquals($product->currencyCstm->value        , 45);
            $this->assertEquals($product->currencyCstm->currency->id , $baseCurrency->id);
            $this->assertEquals($product->dateCstm                   , $dateAssert);
            $this->assertEquals($product->datetimeCstm               , $datetimeAssert);
            $this->assertEquals($product->decimalCstm                , '123');
            $this->assertEquals($product->picklistCstm->value        , 'a');
            $this->assertEquals($product->integerCstm                , 12);
            $this->assertEquals($product->phoneCstm                  , '259-784-2169');
            $this->assertEquals($product->radioCstm->value           , 'd');
            $this->assertEquals($product->textCstm                   , 'This is a test Text');
            $this->assertEquals($product->textareaCstm               , 'This is a test TextArea');
            $this->assertEquals($product->urlCstm                    , 'http://wwww.abc.com');
            $this->assertEquals($product->countrylistCstm->value     , 'bbbb');
            $this->assertEquals($product->statelistCstm->value       , 'bbb1');
            $this->assertEquals($product->citylistCstm->value        , 'bb1');
            $this->assertContains('ff'                                   , $product->multiselectCstm->values);
            $this->assertContains('rr'                                   , $product->multiselectCstm->values);
            $this->assertContains('writing'                              , $product->tagcloudCstm->values);
            $this->assertContains('gardening'                            , $product->tagcloudCstm->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calcnumber', 'Product');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $product);
            $this->assertEquals(1476                                     , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnOpportunityAfterTheCustomFieldsArePlacedForProductsModule
         */
//        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterCreatingTheOpportunity()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Retrieve the account id and the super user id.
//            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
//            $superUserId    = $super->id;
//            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
//
//            //Search a created opportunity using the customfield.
//            $this->resetPostArray();
//            $this->setGetArray(array('OpportunitiesSearchForm' => array(
//                                                'name'               => 'myNewOpportunity',
//                                                'owner'              => array('id' => $superUserId),
//                                                'ownedItemsOnly'     => '1',
//                                                'account'            => array('id' => $accountId),
//                                                'amount'             => array('value'       => '298000',
//                                                                              'relatedData' => true,
//                                                                              'currency'    => array(
//                                                                              'id' => $baseCurrency->id)),
//                                                'closeDate__Date'    => array('value' => 'Today'),
//                                                'stage'              => array('value' => 'Prospecting'),
//                                                'source'             => array('value' => 'Self-Generated'),
//                                                'decimalCstm'        => '123',
//                                                'integerCstm'        => '12',
//                                                'phoneCstm'          => '259-784-2169',
//                                                'textCstm'           => 'This is a test Text',
//                                                'textareaCstm'       => 'This is a test TextArea',
//                                                'urlCstm'            => 'http://wwww.abc.com',
//                                                'checkboxCstm'       => array('value'  =>  '1'),
//                                                'currencyCstm'       => array('value'  =>  45),
//                                                'picklistCstm'       => array('value'  =>  'a'),
//                                                'multiselectCstm'    => array('values' => array('ff', 'rr')),
//                                                'tagcloudCstm'       => array('values' => array('writing', 'gardening')),
//                                                'countrylistCstm'    => array('value'  => 'bbbb'),
//                                                'statelistCstm'      => array('value'  => 'bbb1'),
//                                                'citylistCstm'       => array('value'  => 'bb1'),
//                                                'radioCstm'          => array('value'  =>  'd'),
//                                                'dateCstm__Date'     => array('type'   =>  'Today'),
//                                                'datetimeCstm__DateTime' => array('type'   =>  'Today')),
//                                     'ajax' =>  'list-view'));
//            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');
//
//            //Check if the opportunity name exits after the search is performed on the basis of the
//            //custom fields added to the opportunities module.
//            //$this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0); //removed until we show the count again in the listview.
//            $this->assertTrue(strpos($content, "myNewOpportunity") > 0);
//        }
//
//        /**
//         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterCreatingTheOpportunity
//         */
//        public function testEditOfTheOpportunityForTheTagCloudFieldAfterRemovingAllTagsPlacedForProductsModule()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Set the date and datetime variable values here.
//            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
//            $dateAssert     = date('Y-m-d');
//            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
//            $datetimeAssert = date('Y-m-d H:i:')."00";
//            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
//
//            //Retrieve the account id, the super user id and opportunity Id.
//            $accountId                        = self::getModelIdByModelNameAndName ('Account', 'superAccount');
//            $superUserId                      = $super->id;
//            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
//            $opportunity   = Opportunity::getByName('myNewOpportunity');
//            $opportunityId = $opportunity[0]->id;
//            $this->assertEquals(2, $opportunity[0]->tagcloudCstm->values->count());
//
//            //Edit a new Opportunity based on the custom fields.
//            $this->setGetArray(array('id' => $opportunityId));
//            $this->setPostArray(array('Opportunity' => array(
//                            'name'                              => 'myEditOpportunity',
//                            'amount'                            => array('value'       => 288000,
//                                                                         'currency'    => array(
//                                                                             'id'      => $baseCurrency->id)),
//                            'account'                           => array('id' => $accountId),
//                            'closeDate'                         => $date,
//                            'stage'                             => array('value' => 'Qualification'),
//                            'source'                            => array('value' => 'Inbound Call'),
//                            'description'                       => 'This is the Edit Description',
//                            'owner'                             => array('id' => $superUserId),
//                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
//                            'checkboxCstm'                      => '0',
//                            'currencyCstm'                      => array('value'       => 40,
//                                                                         'currency'    => array(
//                                                                             'id' => $baseCurrency->id)),
//                            'decimalCstm'                       => '12',
//                            'dateCstm'                          => $date,
//                            'datetimeCstm'                      => $datetime,
//                            'picklistCstm'                      => array('value'  => 'b'),
//                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
//                            'tagcloudCstm'                      => array('values' =>  array()),
//                            'countrylistCstm'                   => array('value'  => 'aaaa'),
//                            'statelistCstm'                     => array('value'  => 'aaa1'),
//                            'citylistCstm'                      => array('value'  => 'ab1'),
//                            'integerCstm'                       => '11',
//                            'phoneCstm'                         => '259-784-2069',
//                            'radioCstm'                         => array('value' => 'e'),
//                            'textCstm'                          => 'This is a test Edit Text',
//                            'textareaCstm'                      => 'This is a test Edit TextArea',
//                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
//            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/edit');
//
//            //Check the details if they are saved properly for the custom fields.
//            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');
//            $opportunity   = Opportunity::getById($opportunityId);
//
//            //Retrieve the permission of the opportunity.
//            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
//                                                 makeBySecurableItem($opportunity);
//            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
//            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
//
//            $this->assertEquals($opportunity->name                       , 'myEditOpportunity');
//            $this->assertEquals($opportunity->amount->value              , '288000');
//            $this->assertEquals($opportunity->amount->currency->id       , $baseCurrency->id);
//            $this->assertEquals($opportunity->account->id                , $accountId);
//            $this->assertEquals($opportunity->probability                , '25');
//            $this->assertEquals($opportunity->stage->value               , 'Qualification');
//            $this->assertEquals($opportunity->source->value              , 'Inbound Call');
//            $this->assertEquals($opportunity->description                , 'This is the Edit Description');
//            $this->assertEquals($opportunity->owner->id                  , $superUserId);
//            $this->assertEquals(1                                        , count($readWritePermitables));
//            $this->assertEquals(0                                        , count($readOnlyPermitables));
//            $this->assertEquals($opportunity->checkboxCstm               , '0');
//            $this->assertEquals($opportunity->currencyCstm->value        , 40);
//            $this->assertEquals($opportunity->currencyCstm->currency->id , $baseCurrency->id);
//            $this->assertEquals($opportunity->dateCstm                   , $dateAssert);
//            $this->assertEquals($opportunity->datetimeCstm               , $datetimeAssert);
//            $this->assertEquals($opportunity->decimalCstm                , '12');
//            $this->assertEquals($opportunity->picklistCstm->value        , 'b');
//            $this->assertEquals($opportunity->integerCstm                , 11);
//            $this->assertEquals($opportunity->phoneCstm                  , '259-784-2069');
//            $this->assertEquals($opportunity->radioCstm->value           , 'e');
//            $this->assertEquals($opportunity->textCstm                   , 'This is a test Edit Text');
//            $this->assertEquals($opportunity->textareaCstm               , 'This is a test Edit TextArea');
//            $this->assertEquals($opportunity->urlCstm                    , 'http://wwww.abc-edit.com');
//            $this->assertEquals($opportunity->dateCstm                   , $dateAssert);
//            $this->assertEquals($opportunity->datetimeCstm               , $datetimeAssert);
//            $this->assertEquals($opportunity->countrylistCstm->value     , 'aaaa');
//            $this->assertEquals($opportunity->statelistCstm->value       , 'aaa1');
//            $this->assertEquals($opportunity->citylistCstm->value        , 'ab1');
//            $this->assertContains('gg'                                   , $opportunity->multiselectCstm->values);
//            $this->assertContains('hh'                                   , $opportunity->multiselectCstm->values);
//            $this->assertEquals(0                                        , $opportunity->tagcloudCstm->values->count());
//            $metadata            = CalculatedDerivedAttributeMetadata::
//                                   getByNameAndModelClassName('calcnumber', 'Opportunity');
//            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $opportunity);
//            $this->assertEquals(132                                      , $testCalculatedValue);
//        }
//
//        /**
//         * @depends testEditOfTheOpportunityForTheTagCloudFieldAfterRemovingAllTagsPlacedForProductsModule
//         */
//        public function testEditOfTheOpportunityForTheCustomFieldsPlacedForProductsModule()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Set the date and datetime variable values here.
//            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
//            $dateAssert     = date('Y-m-d');
//            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
//            $datetimeAssert = date('Y-m-d H:i:')."00";
//            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
//
//            //Retrieve the account id, the super user id and opportunity Id.
//            $accountId                        = self::getModelIdByModelNameAndName ('Account', 'superAccount');
//            $superUserId                      = $super->id;
//            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;
//            $opportunity                      = Opportunity::getByName('myEditOpportunity');
//            $opportunityId                    = $opportunity[0]->id;
//
//            //Edit a new Opportunity based on the custom fields.
//            $this->setGetArray(array('id' => $opportunityId));
//            $this->setPostArray(array('Opportunity' => array(
//                            'name'                              => 'myEditOpportunity',
//                            'amount'                            => array('value' => 288000,
//                                                                         'currency' => array(
//                                                                         'id' => $baseCurrency->id)),
//                            'account'                           => array('id' => $accountId),
//                            'closeDate'                         => $date,
//                            'stage'                             => array('value' => 'Qualification'),
//                            'source'                            => array('value' => 'Inbound Call'),
//                            'description'                       => 'This is the Edit Description',
//                            'owner'                             => array('id' => $superUserId),
//                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
//                            'checkboxCstm'                      => '0',
//                            'currencyCstm'                      => array('value'   => 40,
//                                                                         'currency' => array(
//                                                                         'id' => $baseCurrency->id)),
//                            'decimalCstm'                       => '12',
//                            'dateCstm'                          => $date,
//                            'datetimeCstm'                      => $datetime,
//                            'picklistCstm'                      => array('value'  => 'b'),
//                            'multiselectCstm'                   => array('values' =>  array('gg', 'hh')),
//                            'tagcloudCstm'                      => array('values' =>  array('reading', 'surfing')),
//                            'countrylistCstm'                   => array('value'  => 'aaaa'),
//                            'statelistCstm'                     => array('value'  => 'aaa1'),
//                            'citylistCstm'                      => array('value'  => 'ab1'),
//                            'integerCstm'                       => '11',
//                            'phoneCstm'                         => '259-784-2069',
//                            'radioCstm'                         => array('value' => 'e'),
//                            'textCstm'                          => 'This is a test Edit Text',
//                            'textareaCstm'                      => 'This is a test Edit TextArea',
//                            'urlCstm'                           => 'http://wwww.abc-edit.com')));
//            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/edit');
//
//            //Check the details if they are saved properly for the custom fields.
//            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');
//            $opportunity   = Opportunity::getById($opportunityId);
//
//            //Retrieve the permission of the opportunity.
//            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
//                                                 makeBySecurableItem($opportunity);
//            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
//            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
//
//            $this->assertEquals($opportunity->name                       , 'myEditOpportunity');
//            $this->assertEquals($opportunity->amount->value              , '288000');
//            $this->assertEquals($opportunity->amount->currency->id       , $baseCurrency->id);
//            $this->assertEquals($opportunity->account->id                , $accountId);
//            $this->assertEquals($opportunity->probability                , '25');
//            $this->assertEquals($opportunity->stage->value               , 'Qualification');
//            $this->assertEquals($opportunity->source->value              , 'Inbound Call');
//            $this->assertEquals($opportunity->description                , 'This is the Edit Description');
//            $this->assertEquals($opportunity->owner->id                  , $superUserId);
//            $this->assertEquals(1                                        , count($readWritePermitables));
//            $this->assertEquals(0                                        , count($readOnlyPermitables));
//            $this->assertEquals($opportunity->checkboxCstm               , '0');
//            $this->assertEquals($opportunity->currencyCstm->value        , 40);
//            $this->assertEquals($opportunity->currencyCstm->currency->id , $baseCurrency->id);
//            $this->assertEquals($opportunity->dateCstm                   , $dateAssert);
//            $this->assertEquals($opportunity->datetimeCstm               , $datetimeAssert);
//            $this->assertEquals($opportunity->decimalCstm                , '12');
//            $this->assertEquals($opportunity->picklistCstm->value        , 'b');
//            $this->assertEquals($opportunity->integerCstm                , 11);
//            $this->assertEquals($opportunity->phoneCstm                  , '259-784-2069');
//            $this->assertEquals($opportunity->radioCstm->value           , 'e');
//            $this->assertEquals($opportunity->textCstm                   , 'This is a test Edit Text');
//            $this->assertEquals($opportunity->textareaCstm               , 'This is a test Edit TextArea');
//            $this->assertEquals($opportunity->urlCstm                    , 'http://wwww.abc-edit.com');
//            $this->assertEquals($opportunity->dateCstm                   , $dateAssert);
//            $this->assertEquals($opportunity->datetimeCstm               , $datetimeAssert);
//            $this->assertEquals($opportunity->countrylistCstm->value     , 'aaaa');
//            $this->assertEquals($opportunity->statelistCstm->value       , 'aaa1');
//            $this->assertEquals($opportunity->citylistCstm->value        , 'ab1');
//            $this->assertContains('gg'                                   , $opportunity->multiselectCstm->values);
//            $this->assertContains('hh'                                   , $opportunity->multiselectCstm->values);
//            $this->assertContains('reading'                              , $opportunity->tagcloudCstm->values);
//            $this->assertContains('surfing'                              , $opportunity->tagcloudCstm->values);
//            $metadata            = CalculatedDerivedAttributeMetadata::
//                                   getByNameAndModelClassName('calcnumber', 'Opportunity');
//            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $opportunity);
//            $this->assertEquals(132                                      , $testCalculatedValue);
//        }
//
//        /**
//         * @depends testEditOfTheOpportunityForTheCustomFieldsPlacedForProductsModule
//         */
//        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterEditingTheOpportunity()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Retrieve the account id, the super user id and opportunity Id.
//            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
//            $superUserId    = $super->id;
//            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
//
//            //Search a created Opportunity using the customfields.
//            $this->resetPostArray();
//            $this->setGetArray(array(
//                        'OpportunitiesSearchForm' =>
//                            ProductsDesignerWalkthroughHelperUtil::fetchOpportunitiesSearchFormGetData($accountId,
//                                                                                      $superUserId, $baseCurrency->id),
//                        'ajax'                    =>  'list-view')
//            );
//            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');
//
//            //Assert that the edit Opportunity exits after the edit and is diaplayed on the search page.
//            //$this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0); //removed until we show the count again in the listview.
//            $this->assertTrue(strpos($content, "myEditOpportunity") > 0);
//        }
//
//        /**
//         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterEditingTheOpportunity
//         */
//        public function testDeleteOfTheOpportunityUserForTheCustomFieldsPlacedForProductsModule()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Get the opportunity id from the recently edited opportunity.
//            $opportunityId = self::getModelIdByModelNameAndName('Opportunity', 'myEditOpportunity');
//
//            //Set the opportunity id so as to delete the opportunity.
//            $this->setGetArray(array('id' => $opportunityId));
//            $this->runControllerWithRedirectExceptionAndGetUrl('opportunities/default/delete');
//
//            //Check wether the opportunity is deleted.
//            $opportunity = Opportunity::getByName('myEditOpportunity');
//            $this->assertEquals(0, count($opportunity));
//        }
//
//        /**
//         * @depends testDeleteOfTheOpportunityUserForTheCustomFieldsPlacedForProductsModule
//         */
//        public function testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterDeletingTheOpportunity()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Retrieve the account id, the super user id and opportunity Id.
//            $accountId      = self::getModelIdByModelNameAndName ('Account', 'superAccount');
//            $superUserId    = $super->id;
//            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
//
//            //Search a created Opportunity using the customfields.
//            $this->resetPostArray();
//            $this->setGetArray(array(
//                        'OpportunitiesSearchForm' =>
//                            ProductsDesignerWalkthroughHelperUtil::fetchOpportunitiesSearchFormGetData($accountId,
//                                                                                      $superUserId, $baseCurrency->id),
//                        'ajax'                    =>  'list-view')
//            );
//            $content = $this->runControllerWithNoExceptionsAndGetContent('opportunities/default');
//
//            //Assert that the edit Opportunity does not exits after the search.
//            $this->assertTrue(strpos($content, "No results found.") > 0);
//        }
//
//        /**
//         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForProductsModuleAfterDeletingTheOpportunity
//         */
//        public function testTypeAheadWorksForTheTagCloudFieldPlacedForProductsModule()
//        {
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Search a list item by typing in tag cloud attribute.
//            $this->resetPostArray();
//            $this->setGetArray(array('name' => 'tagcloud',
//                                     'term' => 'rea'));
//            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');
//
//            //Check if the returned content contains the expected vlaue
//            $this->assertTrue(strpos($content, "reading") > 0);
//        }
//
//        /**
//         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForProductsModule
//         */
//        public function testLabelLocalizationForTheTagCloudFieldPlacedForProductsModule()
//        {
//            Yii::app()->user->userModel =  User::getByUsername('super');
//            $languageHelper = new ZurmoLanguageHelper();
//            $languageHelper->load();
//            $this->assertEquals('en', $languageHelper->getForCurrentUser());
//            Yii::app()->user->userModel->language = 'fr';
//            $this->assertTrue(Yii::app()->user->userModel->save());
//            $languageHelper->setActive('fr');
//            $this->assertEquals('fr', Yii::app()->user->getState('language'));
//
//            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
//
//            //Search a list item by typing in tag cloud attribute.
//            $this->resetPostArray();
//            $this->setGetArray(array('name' => 'tagcloud',
//                                     'term' => 'surf'));
//            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');
//
//            //Check if the returned content contains the expected vlaue
//            $this->assertTrue(strpos($content, "surfing fr") > 0);
//        }
    }
?>