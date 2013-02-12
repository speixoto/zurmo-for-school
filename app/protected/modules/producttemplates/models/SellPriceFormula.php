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
        public function __toString()
        {
            if (trim($this->value) == '')
            {
                return Zurmo::t('ZurmoModule', '(None)');
            }
            return strval($this->value);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'rateToBase',
                    'value',
                ),
                'relations' => array(
                    'currency' => array(RedBeanModel::HAS_ONE, 'Currency'),
                ),
                'rules' => array(
                    array('currency',    'required'),
                    array('rateToBase',  'required'),
                    array('rateToBase',  'type', 'type' => 'float'),
                    array('value',       'required'),
                    array('value',       'type',    'type' => 'float'),
                    array('value',       'default', 'value' => 0),
                ),
                'defaultSortAttribute' => 'value'
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Given an id of a currency model, determine if any currency values are using this currency.
         * @return true if at least one currency value model is using this currency.
         * @param integer $currencyId
         */
        public static function isCurrencyInUseById($currencyId)
        {
            assert('is_int($currencyId)');
            $columnName = RedBeanModel::getForeignKeyName('SellPriceFormula', 'currency');
            $quote      = DatabaseCompatibilityUtil::getQuote();
            $where      = "{$quote}{$columnName}{$quote} = '{$currencyId}'";
            $count      = SellPriceFormula::getCount(null, $where);
            if ($count > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * Get the rateToBase from the currency model.
         * @return true to signal success and that validate can proceed.
         */
        public function beforeValidate()
        {
            if (!parent::beforeValidate())
            {
                return false;
            }
            if ($this->currency->rateToBase !== null &&
                    ($this->rateToBase === null                     ||
                     array_key_exists('value', $this->originalAttributeValues) ||
                     array_key_exists('currency', $this->originalAttributeValues)))
            {
                $this->rateToBase = $this->currency->rateToBase;
                assert('$this->rateToBase !== null');
            }
            return true;
        }
    }
?>