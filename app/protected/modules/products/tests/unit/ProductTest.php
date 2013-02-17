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

    class ProductTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('superOpportunity', $super);
            $productTemplate = ProductTemplateTestHelper::createProductTemplateByName('superProductTemplate');
        }

        public function testCreateAndGetProductById()
        {
            $accounts         = Account::getByName('superAccount');
            $opportunities    = Opportunity::getByName('superOpportunity');
            $productTemplates = ProductTemplate::getByName('superProductTemplate');
            $account          = $accounts[0];
            $user             = $account->owner;

            $product                  = new Product();
            $product->name            = 'Product 1';
            $product->owner           = $user;
            $product->description     = 'Description';
            $product->quantity        = 2;
            $product->stage->value    = 'Open';
            $product->account         = $accounts[0];
            $product->opportunity     = $opportunities[0];
            $product->productTemplate = $productTemplates[0];
            $this->assertTrue($product->save());
            $id                       = $product->id;
            $product->forget();
            unset($product);
            $product                  = Product::getById($id);
            $this->assertEquals('Product 1', $product->name);
            $this->assertEquals(2, $product->quantity);
            $this->assertEquals('Description', $product->description);
            $this->assertEquals('Open', $product->stage->value);
            $this->assertEquals($user->id, $product->owner->id);
            $this->assertTrue($product->account->isSame($accounts[0]));
            $this->assertTrue($product->opportunity->isSame($opportunities[0]));
            $this->assertTrue($product->productTemplate->isSame($productTemplates[0]));
        }

    }
?>