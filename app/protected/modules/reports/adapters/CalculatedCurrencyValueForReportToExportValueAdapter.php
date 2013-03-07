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

    class CalculatedCurrencyValueForReportToExportValueAdapter extends ForReportToExportValueAdapter
    {
        public function resolveData(& $data)
        {
            assert('$this->model->{$this->attribute} instanceof CurrencyValue');
            if($this->getCurrencyValueConversionType() == Report::CURRENCY_CONVERSION_TYPE_ACTUAL)
            {
                $data[] = Yii::app()->numberFormatter->formatDecimal($this->model->{$this->attribute});
                $data[] = Zurmo::t('ReportsModule', 'Mixed Currency');
            }
            elseif($this->getCurrencyValueConversionType() == Report::CURRENCY_CONVERSION_TYPE_BASE)
            {
                //Assumes base conversion is done using sql math
                $data[] = Yii::app()->numberFormatter->formatCurrency($this->model->{$this->attribute},
                                                                      Yii::app()->currencyHelper->getBaseCode());
                $data[] = Yii::app()->currencyHelper->getBaseCode();
            }
            elseif($this->getCurrencyValueConversionType() == Report::CURRENCY_CONVERSION_TYPE_SPOT)
            {
                //Assumes base conversion is done using sql math
                $data[] = Yii::app()->numberFormatter->formatCurrency($this->model->{$this->attribute} *
                          $this->getFromBaseToSpotRate(), $this->getSpotConversionCurrencyCode());
                $data[] = $this->getSpotConversionCurrencyCode();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Resolve the header data for the attribute.
         * @param array $headerData
         */
        public function resolveHeaderData(& $headerData)
        {
            $headerData[] = $this->model->getAttributeLabel($this->attribute);
            $headerData[] = $this->model->getAttributeLabel($this->attribute) . ' ' . Zurmo::t('ZurmoModule', 'Currency');
        }
    }
?>