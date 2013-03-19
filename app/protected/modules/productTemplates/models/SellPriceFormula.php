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

    class SellPriceFormula extends OwnedModel
    {
        const TYPE_EDITABLE           = 1;
        const TYPE_PROFIT_MARGIN      = 2;
        const TYPE_MARKUP_OVER_COST   = 3;
        const TYPE_DISCOUNT_FROM_LIST = 4;
        const TYPE_SAME_AS_LIST       = 5;

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('ProductTemplatesModule', '(None)');
            }
            return $this->name;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'type',
                    'discountOrMarkupPercentage',
                ),
                'relations' => array(
                    'productTemplate' => array(RedBeanModel::HAS_ONE, 'ProductTemplate'),
                ),
                'rules' => array(
                    array('name',                        'required'),
                    array('name',                        'type',    'type' => 'string'),
                    //array('name',                        'length',  'min'  => 3,  'max' => 64),
                    array('discountOrMarkupPercentage',  'type',    'type' => 'float'),
                ),
//                'elements' => array(
//                    'type'                => 'SellPriceFormulaTypeDropDown',
//                ),
                'defaultSortAttribute' => 'name',
                'customFields' => array(
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array of sellpriceformula values and labels
         */
        public static function getNameDropDownArray()
        {
            return array(
                null                                       => Yii::t('Default', '--'),
                SellPriceFormula::TYPE_EDITABLE            => Yii::t('Default', 'Editable'),
                SellPriceFormula::TYPE_DISCOUNT_FROM_LIST  => Yii::t('Default', 'Discount From List'),
                SellPriceFormula::TYPE_MARKUP_OVER_COST    => Yii::t('Default', 'Markup Over Cost'),
                SellPriceFormula::TYPE_PROFIT_MARGIN       => Yii::t('Default', 'Profit Margin'),
                SellPriceFormula::TYPE_SAME_AS_LIST        => Yii::t('Default', 'Same As List'),
            );
        }
    }
?>