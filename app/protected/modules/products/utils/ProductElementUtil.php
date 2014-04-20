<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class used by product elements.
     */
    class ProductElementUtil
    {
        /**
         * Product name length in portlet view
         */
        const PRODUCT_NAME_LENGTH_IN_PORTLET_VIEW = 19;

        /**
         * Gets sell price for product in portlet view
         * @param object $data
         * @param int $row
         * @return string
         */
        public static function getProductPortletSellPrice($data, $row)
        {
            assert('$data->sellPrice instanceof CurrencyValue');
            $currencyValueModel = $data->sellPrice;
            return Yii::app()->numberFormatter->formatCurrency( $currencyValueModel->value,
                                                                $currencyValueModel->currency->code);
        }

        /**
         * Gets total price for product in portlet view
         * @param object $data
         * @param int $row
         * @return string
         */
        public static function getProductPortletTotalPrice($data, $row)
        {
            assert('$data->sellPrice instanceof CurrencyValue');
            $currencyValueModel = $data->sellPrice;
            return Yii::app()->numberFormatter->formatCurrency( $currencyValueModel->value * $data->quantity,
                                                                $currencyValueModel->currency->code);
        }

        /**
         * Gets full calendar item data.
         * @return string
         */
        public function getCalendarItemData()
        {
            $name                      = $this->name;
            $quantity                  = $this->quantity;
            $priceFrequency            = ProductTemplatePriceFrequencyDropDownElement
                                                    ::renderNonEditableStringContent($this->priceFrequency);
            $currencyValueModel        = $this->sellPrice;
            $sellPrice                 = Yii::app()->numberFormatter->formatCurrency((float)$currencyValueModel->value,
                                                                $currencyValueModel->currency->code);
            $language                  = Yii::app()->languageHelper->getForCurrentUser();
            $translatedAttributeLabels = self::translatedAttributeLabels($language);
            return array(Zurmo::t('Core', 'Name',     array(), null, $language) => $name,
                         Zurmo::t('Core', 'Quantity', array(), null, $language) => $quantity,
                         $translatedAttributeLabels['priceFrequency']           => $priceFrequency,
                         $translatedAttributeLabels['sellPrice']                => $sellPrice);
        }
    }
?>