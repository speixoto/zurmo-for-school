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
     * Helper class used by product template elements.
     */
    class ProductTemplateElementUtil
    {
        /**
         * Script used by some policy elements to control the helper
         * dropdown and toggling disable and enable on the text field
         * @see PolicyIntegerAndStaticDropDownElement
         */
        public static function getShowHideDiscountOrMarkupPercentageTextFieldScript()
        {
            return "
                var typeProfitMargin = " . SellPriceFormula::TYPE_PROFIT_MARGIN . ";
                var typeMarkOverCost = " . SellPriceFormula::TYPE_MARKUP_OVER_COST . ";
                var typeDiscountFromList = " . SellPriceFormula::TYPE_DISCOUNT_FROM_LIST . ";
                function showHideDiscountOrMarkupPercentageTextField(helperValue, textFieldId)
                {
                    if (helperValue == typeProfitMargin || helperValue == typeMarkOverCost || helperValue == typeDiscountFromList)
                    {
                        $('#' + textFieldId).show();
                    }
                    else
                    {
                        $('#' + textFieldId).hide();
                    }
                }
            ";
        }

        public static function getEnableDisableSellPriceElementBySellPriceFormulaScript()
        {
            return "
                var typeEditable = " . SellPriceFormula::TYPE_EDITABLE . ";
                function enableDisableSellPriceElementBySellPriceFormula(helperValue, elementId, attribute)
                {
                    if (helperValue != typeEditable)
                    {
                        $('#' + elementId).attr('readonly', true);
                        $('#ProductTemplate_' + attribute + '_currency_id').attr('readonly', 'true');
                        $('#ProductTemplate_' + attribute + '_currency_id').addClass('disabled');
                        $('#' + elementId).addClass('disabled');
                    }
                    else
                    {
                        $('#' + elementId).removeAttr('readonly');
                        $('#ProductTemplate_' + attribute + '_currency_id').removeAttr('readonly');
                        $('#ProductTemplate_' + attribute + '_currency_id').removeClass('disabled');
                        $('#' + elementId).removeClass('disabled');
                    }
                }
            ";
        }

        public static function getCalculatedSellPriceBySellPriceFormulaScript()
        {
            return "
                var typeEditable = " . SellPriceFormula::TYPE_EDITABLE . ";
                var typeProfitMargin = " . SellPriceFormula::TYPE_PROFIT_MARGIN . ";
                var typeMarkOverCost = " . SellPriceFormula::TYPE_MARKUP_OVER_COST . ";
                var typeDiscountFromList = " . SellPriceFormula::TYPE_DISCOUNT_FROM_LIST . ";
                var typeSameAsList = " . SellPriceFormula::TYPE_SAME_AS_LIST . ";
                function calculateSellPriceBySellPriceFormula()
                {
                    var helperValue = $('#ProductTemplate_sellPriceFormula_type').val();
                    var calculatedSellPrice = 0;
                    var discountOrMarkupPercentage = $('#ProductTemplate_sellPriceFormula_discountOrMarkupPercentage').val();
                    if(discountOrMarkupPercentage == '')
                    {
                        discountOrMarkupPercentage = 0;
                    }
                    else
                    {
                        discountOrMarkupPercentage = parseFloat(discountOrMarkupPercentage)/100;
                    }
                    if (helperValue == typeProfitMargin)
                    {
                        var cost = parseFloat($('#ProductTemplate_cost_value').val());
                        calculatedSellPrice = parseFloat(cost/(100-discountOrMarkupPercentage));
                        modCalculatesSellPrice = (Math.round(calculatedSellPrice * 100)/100).toFixed(2);
                        $('#ProductTemplate_sellPrice_value').val(modCalculatesSellPrice);
                    }

                    if (helperValue == typeMarkOverCost)
                    {
                        var cost = parseFloat($('#ProductTemplate_cost_value').val());
                        calculatedSellPrice = (discountOrMarkupPercentage*cost)+cost;
                        $('#ProductTemplate_sellPrice_value').val(calculatedSellPrice);
                    }

                    if (helperValue == typeDiscountFromList)
                    {
                        var listPrice = parseFloat($('#ProductTemplate_listPrice_value').val());
                        calculatedSellPrice = listPrice - (listPrice * discountOrMarkupPercentage);
                        $('#ProductTemplate_sellPrice_value').val(calculatedSellPrice);
                    }

                    if (helperValue == typeSameAsList)
                    {
                        var listPrice = parseFloat($('#ProductTemplate_listPrice_value').val());
                        $('#ProductTemplate_sellPrice_value').val(listPrice);
                    }
                }
            ";
        }

        public static function bindActionsWithFormFieldsForSellPrice()
        {
            return "
                $(document).ready(function()
                {
                   $('#ProductTemplate_cost_value').bind('keyup',function()
                       {
                            calculateSellPriceBySellPriceFormula();
                       }
                   );
                   $('#ProductTemplate_listPrice_value').bind('keyup',function()
                       {
                            calculateSellPriceBySellPriceFormula();
                       }
                   );
                });
            ";
        }

        public static function getProductTemplateStatusDropdownArray()
        {
            return array(
                ProductTemplate::STATUS_ACTIVE       => Yii::t('Default', 'Active'),
                ProductTemplate::STATUS_INACTIVE     => Yii::t('Default', 'Inactive'),
            );
        }

        public static function getProductTemplateTypeDropdownArray()
        {
            return array(
                ProductTemplate::TYPE_PRODUCT       => Yii::t('Default', 'Product'),
                ProductTemplate::TYPE_SERVICE       => Yii::t('Default', 'Service'),
                ProductTemplate::TYPE_SUBSCRIPTION  => Yii::t('Default', 'Subscription'),
            );
        }

        public static function getProductTemplatePriceFrequencyDropdownArray()
        {
            return array(
                ProductTemplate::PRICE_FREQUENCY_ONE_TIME  => Yii::t('Default', 'One Time'),
                ProductTemplate::PRICE_FREQUENCY_MONTHLY   => Yii::t('Default', 'Monthly'),
                ProductTemplate::PRICE_FREQUENCY_ANNUALLY  => Yii::t('Default', 'Annually'),
            );
        }

        public static function getProductTemplateTypeDisplayedGridValue($data, $row)
        {
            $typeDropdownData = self::getProductTemplateTypeDropdownArray();
            if(isset($typeDropdownData[$data->type]))
            {
                return $typeDropdownData[$data->type];
            }
            else
            {
                return null;
            }
        }

        public static function getProductTemplateStatusDisplayedGridValue($data, $row)
        {
            $statusDropdownData = self::getProductTemplateStatusDropdownArray();
            if(isset($statusDropdownData[$data->status]))
            {
                return $statusDropdownData[$data->status];
            }
            else
            {
                return null;
            }
        }

        public static function getProductTemplatePriceFrequencyDisplayedGridValue($data, $row)
        {
            $frequencyDropdownData = self::getProductTemplatePriceFrequencyDropdownArray();
            if(isset($frequencyDropdownData[$data->$attribute]))
            {
                return $frequencyDropdownData[$data->$attribute];
            }
            else
            {
                return null;
            }
        }

        public static function getSellPriceFormulaDisplayedGridValue($data, $row)
        {
            $sellPriceFormulaModel = $data->sellPriceFormula;
            $type = $sellPriceFormulaModel->type;
            $discountOrMarkupPercentage = $sellPriceFormulaModel->discountOrMarkupPercentage;
            $displayedSellPriceFormulaList = SellPriceFormula::getDisplayedSellPriceFormulaArray();
            $content = '';
            if($type != null)
            {
                $content = $displayedSellPriceFormulaList[$type];

                if($type != SellPriceFormula::TYPE_EDITABLE)
                {
                    $content = str_replace('{discount}', $discountOrMarkupPercentage/100, $content);
                }
            }

            return $content;
        }
    }
?>