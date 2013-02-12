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

    class ProductTemplate extends Item
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'producttemplates' => 'Parent ProductTemplatesModuleSingularLabel',
                    'contacts'         => 'ContactsModulePluralLabel'
                )
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ProductTemplatesModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'ProductTemplatesModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'ProductTemplatesModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'ProductTemplatesModulePluralLabel';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'description',
                    'priceFrequency',
                    'cost',
                    'listPrice',
                    'sellPrice',
                ),
                'relations' => array(
                    'product'            => array(RedBeanModel::HAS_ONE,              'Product'),
                    'selfPriceFormula'   => array(RedBeanModel::HAS_ONE,              'SelfPriceFormula'),
                    'productCategories'  => array(RedBeanModel::MANY_MANY,            'ProductCategory'),
                    'type'               => array(RedBeanModel::HAS_ONE,              'OwnedCustomField', RedBeanModel::OWNED),
                    'priceFrequency'     => array(RedBeanModel::HAS_ONE,              'CurrencyValue',    RedBeanModel::OWNED),
                    'cost'               => array(RedBeanModel::HAS_ONE,              'CurrencyValue',    RedBeanModel::OWNED),
                    'listPrice'          => array(RedBeanModel::HAS_ONE,              'CurrencyValue',    RedBeanModel::OWNED),
                    'sellPrice'          => array(RedBeanModel::HAS_ONE,              'CurrencyValue',    RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('name',           'required'),
                    array('name',           'type',    'type' => 'string'),
                    array('name',           'length',  'min'  => 3, 'max' => 64),
                    array('description',    'type',    'type' => 'string'),
                    array('priceFrequency', 'required'),
                    array('cost',           'required'),
                    array('listPrice',      'required'),
                    array('sellPrice',      'required'),
                ),
                'elements' => array(
                    'product'        => 'Product',
                    'description'    => 'TextArea',
                    'cost'           => 'CurrencyValue',
                    'listPrice'      => 'CurrencyValue',
                    'sellPrice'      => 'CurrencyValue',
                ),
                'customFields' => array(
                    'type'     => 'ProductTemplateTypes',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'description',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'ProductTemplate';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            //return 'ProductTemplateGamification';
        }
    }
?>