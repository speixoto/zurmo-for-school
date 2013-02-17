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

    class ProductTemplateTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testDemoDataMaker()
        {
            $productTemplate = new ProductTemplate();
            $productTemplateRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                            'ProductTemplatesModule', 'ProductTemplate');
            $name    = RandomDataUtil::getRandomValueFromArray($productTemplateRandomData['names']);
            $productTemplate->name                   = $name;
            $productTemplate->priceFrequency         = 2;
            $productTemplate->cost->value            = 200;
            $productTemplate->listPrice->value       = 200;
            $productTemplate->sellPrice->value       = 200;
            $productTemplate->type                   = ProductTemplate::TYPE_PRODUCT;
            $productTemplate->status                 = ProductTemplate::STATUS_ACTIVE;
            $this->assertTrue($productTemplate->save());
            $productTemplates[] = $productTemplate->id;
        }

        public function testCreateAndGetProductTemplateById()
        {
            $user        = UserTestHelper::createBasicUser('Steven');
            $product     = ProductTestHelper::createProductByNameForOwner('Product 1', $user);

            $currencies                                 = Currency::getAll();
            $currencyValue1                             = new CurrencyValue();
            $currencyValue1->value                      = 500.54;
            $currencyValue1->currency                   = $currencies[0];
            $currencyValue2                             = new CurrencyValue();
            $currencyValue2->value                      = 400.54;
            $currencyValue2->currency                   = $currencies[0];
            $currencyValue3                             = new CurrencyValue();
            $currencyValue3->value                      = 300.54;
            $currencyValue3->currency                   = $currencies[0];

            $productTemplate                            = new ProductTemplate();
            $productTemplate->name                      = 'Red Widget';
            $productTemplate->description               = 'Description';
            $productTemplate->priceFrequency            = 2;
            $productTemplate->cost                      = $currencyValue1;
            $productTemplate->listPrice                 = $currencyValue2;
            $productTemplate->sellPrice                 = $currencyValue3;
            $productTemplate->type                      = ProductTemplate::TYPE_PRODUCT;
            $productTemplate->status                    = ProductTemplate::STATUS_ACTIVE;
            $productTemplate->products->add($product);
            $sellPriceFormula                           = new SellPriceFormula();
            $sellPriceFormula->name                     = 'Sell Price Formula';
            $productTemplate->sellPriceFormula          = $sellPriceFormula;
            $this->assertTrue($productTemplate->save());
            $id                                         = $productTemplate->id;
            $productTemplate->forget();
            unset($productTemplate);
            $productTemplate                            = ProductTemplate::getById($id);
            $this->assertEquals('Red Widget', $productTemplate->name);
            $this->assertEquals('Description', $productTemplate->description);
            $this->assertEquals(2, $productTemplate->priceFrequency);
            $this->assertEquals(500.54, $productTemplate->cost->value);
            $this->assertEquals(400.54, $productTemplate->listPrice->value);
            $this->assertEquals(300.54, $productTemplate->sellPrice->value);
            $this->assertEquals(ProductTemplate::TYPE_PRODUCT, $productTemplate->type);
            $this->assertEquals(ProductTemplate::STATUS_ACTIVE, $productTemplate->status);
            $this->assertEquals($product, $productTemplate->products[0]);
            $this->assertTrue($productTemplate->sellPriceFormula->isSame($sellPriceFormula));
        }
    }
?>