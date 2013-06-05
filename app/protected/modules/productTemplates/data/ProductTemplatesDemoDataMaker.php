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
     * Class that builds demo product templates.
     */
    class ProductTemplatesDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array();
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $currencies = Currency::getAll('id');
            $productTemplates = array();
            for ($i = 0; $i < 5; $i++)
            {
                $productTemplate = new ProductTemplate();
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[array_rand($currencies)];
                $productTemplate->cost           = $currencyValue;
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[array_rand($currencies)];
                $productTemplate->listPrice      = $currencyValue;
                $currencyValue                   = new CurrencyValue();
                $currencyValue->currency         = $currencies[array_rand($currencies)];
                $productTemplate->sellPrice      = $currencyValue;
                $this->populateModelData($productTemplate, $i);
                $saved               = $productTemplate->save();
                assert('$saved');
                $productTemplates[]      = $productTemplate->id;
            }
            $demoDataHelper->setRangeByModelName('ProductTemplate', $productTemplates[0], $productTemplates[count($productTemplates)-1]);
        }

        public function populateModelData(& $model, $counter)
        {
            assert('$model instanceof ProductTemplate');
            parent::populateModel($model);
            $productTemplateRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                            'ProductTemplatesModule', 'ProductTemplate');
            $name                      = $productTemplateRandomData['names'][$counter];
            $productCategoryName       = self::getProductCategoryForTemplate($name);
            $allCats = ProductCategory::getAll();
            foreach ($allCats as $category)
            {
                if ($category->name == $productCategoryName)
                {
                    $categoryId = $category->id;
                }
            }
            $productCategory           = ProductCategory::getById($categoryId);
            $model->name               = $name;
            $model->productCategories->add($productCategory);
            $model->priceFrequency     = 2;
            $model->cost->value        = 200;
            $model->listPrice->value   = 200;
            $model->sellPrice->value   = 200;
            $model->status             = ProductTemplate::STATUS_ACTIVE;
            $model->type               = ProductTemplate::TYPE_PRODUCT;
            $sellPriceFormula          = new SellPriceFormula();
            $sellPriceFormula->type    = SellPriceFormula::TYPE_EDITABLE;
            $model->sellPriceFormula   = $sellPriceFormula;
        }

        private static function getProductCategoryForTemplate($template)
        {
            $templateCategoryMapping = array(
                                                'Amazing Kid'           => 'CD-DVD',
                                                'You Can Do Anything'   => 'CD-DVD',
                                                'A Bend in the River'   => 'Books',
                                                'A Gift of Monotheists' => 'Books',
                                                'Once in a Lifetime'    => 'Music'
                                            );

            return $templateCategoryMapping[$template];
        }
    }
?>