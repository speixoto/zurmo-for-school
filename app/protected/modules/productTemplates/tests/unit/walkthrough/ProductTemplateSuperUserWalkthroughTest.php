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

            //Setup test data owned by the super user.
            //AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSaveAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Create a new account.
            $this->resetGetArray();

            $currencies                                 = Currency::getAll();
            $currencyValue1                             = new CurrencyValue();
            $currencyValue1->value                      = 500.54;
            $currencyValue1->currency                   = $currencies[0];
            $currencyValue1Array                        = CPropertyValue::ensureArray($currencyValue1);
            $currencyValue2                             = new CurrencyValue();
            $currencyValue2->value                      = 400.54;
            $currencyValue2->currency                   = $currencies[0];
            $currencyValue2Array                        = CPropertyValue::ensureArray($currencyValue2);
            $currencyValue3                             = new CurrencyValue();
            $currencyValue3->value                      = 300.54;
            $currencyValue3->currency                   = $currencies[0];
            $currencyValue3Array                        = CPropertyValue::ensureArray($currencyValue3);

            $productTemplate                            = array();
            $productTemplate['name']                      = 'Red Widget';
            $productTemplate['description']               = 'Description';
            $productTemplate['priceFrequency']            = 2;
            $productTemplate['cost']                      = array('currency' => $currencyValue1Array);
            $productTemplate['listPrice']                 = array('currency' => $currencyValue2Array);
            $productTemplate['sellPrice']                 = array('currency' => $currencyValue3Array);
            $productTemplate['type']                      = ProductTemplate::TYPE_PRODUCT;
            $productTemplate['status']                    = ProductTemplate::STATUS_ACTIVE;
            $sellPriceFormula                             = new SellPriceFormula();
            $sellPriceFormula->type                       = 1;
            $sellPriceFormula->discountOrMarkupPercentage = 10;
            $sellPriceFormulaArray                        = CPropertyValue::ensureArray($sellPriceFormula);
            $productTemplate['sellPriceFormula'] = $sellPriceFormulaArray;
            $this->setPostArray($productTemplate);
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('productTemplates/default/create');

            $productTemplates = ProductTemplate::getByName('Red Widget');
            $this->assertEquals(1, count($productTemplates));
            $this->assertTrue  ($productTemplates[0]->id > 0);
            //$compareRedirectUrl = Yii::app()->createUrl('productTemplates/default/details', array('id' => $productTemplates[0]->id));
            //$this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals(400.54, $productTemplates[0]->listPrice->value);
            $this->assertEquals(500.54, $productTemplates[0]->cost->value);
            $this->assertEquals(300.54, $productTemplates[0]->sellPrice->value);
        }
    }
?>