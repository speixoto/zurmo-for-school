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
     * User interface element for managing related model relations for categories. This class supports a HAS_MANY
     * specifically for the 'productCatalogs' relation. This is utilized by the Product Category model.
     *
     */
    class MultipleProductCatalogsForProductCategoryElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            $content  = null;
            $productCatalogs = $this->getExistingProductCatalogsRelationsIdsAndLabels();
            foreach ($productCatalogs as $productCatalogData)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $productCatalogData['name'];
            }
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof ProductCategory');
            $productCatalog =ProductCatalog::getByName('Default');
            $value = $productCatalog[0]->id;

            $idInputHtmlOptions = array(
                'id'       => $this->getIdForIdField(),
                'disabled' => $this->getDisabledValue()
            );
            $content       = ZurmoHtml::hiddenField($this->getNameForIdField(), $value, $idInputHtmlOptions);
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            //return Yii::app()->format->text(Zurmo::t('ProductTemplatesModule', 'Catalogs'));
            return '';
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ProductTemplatesModule', 'Related ProductTemplatesModulePluralLabel',
                       LabelUtil::getTranslationParamsForAllModules());
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getNameForIdField()
        {
                return 'ProductCategory[productCatalogs]';
        }

        protected function getIdForIdField()
        {
            return 'ProductCategory_ProductCatalogs_id';
        }

        protected function getExistingProductCatalogsRelationsIdsAndLabels()
        {
            $existingProductCatalogs = array();
            for ($i = 0; $i < count($this->model->productCatalogs); $i++)
            {
                $existingProductCatalogs[] = array('id' => $this->model->productCatalogs[$i]->id,
                                                     'name' => $this->model->productCatalogs[$i]->name);
            }
            return $existingProductCatalogs;
        }
    }
?>