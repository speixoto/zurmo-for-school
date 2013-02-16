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
     * Class that builds demo product catalogs.
     */
    class ProductCatalogsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('users');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $productCatalogs = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $productCatalog = new ProductCatalog();
                $productCatalog->productCategories->add($demoDataHelper->getRandomByModelName('ProductCategory'));
                $this->populateModel($productCatalog);
                $saved = $productCatalog->save();
                assert('$saved');
                $productCatalogs[] = $productCatalog->id;
            }
            $demoDataHelper->setRangeByModelName('ProductCatalog', $productCatalogs[0], $productCatalogs[count($productCatalogs)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof ProductCatalog');
            parent::populateModel($model);
            $productCatalogRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('ProductTemplatesModule', 'ProductCatalog');
            $name    = RandomDataUtil::getRandomValueFromArray($productCatalogRandomData['names']);

            $model->name            = $name;
        }
    }
?>