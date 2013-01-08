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

    class ReportDataProviderToAmChartMakerAdapter
    {
        const FIRST_SERIES_VALUE_PREFIX     = 'FirstSeriesValue';

        const FIRST_SERIES_DISPLAY_LABEL    = 'FirstSeriesDisplayLabel';

        const FIRST_RANGE_DISPLAY_LABEL     = 'FirstRangeDisplayLabel';

        const FIRST_SERIES_FORMATTED_VALUE  = 'FirstSeriesFormattedValue';

        const SECOND_SERIES_VALUE           = 'SecondSeriesValue';

        const SECOND_SERIES_DISPLAY_LABEL   = 'SecondSeriesDisplayLabel';

        const SECOND_SERIES_FORMATTED_VALUE = 'SecondSeriesFormattedValue';

        protected $report;

        protected $data;

        protected $secondSeriesValueData     = array();

        protected $secondSeriesDisplayLabels = array();

        protected $secondSeriesValueCount;

        protected $formattedData;

        public static function resolveFirstSeriesValueName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_VALUE_PREFIX . $key;
        }

        public static function resolveFirstSeriesDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_DISPLAY_LABEL . $key;
        }

        public static function resolveFirstRangeDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::FIRST_RANGE_DISPLAY_LABEL . $key;
        }

        public static function resolveFirstSeriesFormattedValueName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_FORMATTED_VALUE . $key;
        }

        public static function resolveSecondSeriesValueName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_VALUE . $key;
        }

        public static function resolveSecondSeriesDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_DISPLAY_LABEL . $key;
        }

        public static function resolveSecondSeriesFormattedValueName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_FORMATTED_VALUE . $key;
        }

        public function __construct(Report $report, Array $data, Array $secondSeriesValueData = array(),
                                    Array $secondSeriesDisplayLabels = array(),
                                    $secondSeriesValueCount = null)
        {
            assert('is_int($secondSeriesValueCount) || $secondSeriesValueCount == null');
            $this->report                     = $report;
            $this->data                       = $data;
            $this->secondSeriesValueData      = $secondSeriesValueData;
            $this->secondSeriesDisplayLabels  = $secondSeriesDisplayLabels;
            $this->secondSeriesValueCount     = $secondSeriesValueCount;
        }

        public function getType()
        {
            return $this->report->getChart()->type;
        }

        public function getData()
        {
            if($this->formattedData == null)
            {
                $this->formattedData = $this->formatData($this->data);
            }
            return  $this->formattedData;
        }

        public function getSecondSeriesValueCount()
        {
            return $this->secondSeriesValueCount;
        }

        public function isStacked()
        {
            return ChartRules::isStacked($this->getType());
        }

        public function getSecondSeriesDisplayLabelByKey($key)
        {
            assert('is_int($key)');
            return $this->secondSeriesDisplayLabels[$key];
        }

        protected function formatData($data)
        {
            if(!$this->isStacked())
            {
                return $data;
            }
            foreach($this->secondSeriesValueData as $secondSeriesKey)
            {
                foreach($data as $firstSeriesDataKey => $firstSeriesData)
                {
                    if(isset($firstSeriesData[self::resolveFirstSeriesValueName($secondSeriesKey)]) &&
                        !isset($firstSeriesData[self::resolveFirstSeriesFormattedValueName($secondSeriesKey)]))
                    {
                        $value            = $firstSeriesData[self::resolveFirstSeriesValueName($secondSeriesKey)];
                        $displayAttribute = $this->report->getDisplayAttributeByAttribute($this->report->getChart()->firstRange);
                        $data[$firstSeriesDataKey][self::resolveFirstSeriesFormattedValueName($secondSeriesKey)] =
                            $this->formatValue($displayAttribute, $value);
                    }
                    if(isset($firstSeriesData[self::resolveSecondSeriesValueName($secondSeriesKey)]) &&
                        !isset($firstSeriesData[self::resolveSecondSeriesFormattedValueName($secondSeriesKey)]))
                    {
                        $value            = $firstSeriesData[self::resolveSecondSeriesValueName($secondSeriesKey)];
                        $displayAttribute = $this->report->getDisplayAttributeByAttribute($this->report->getChart()->secondRange);
                        $data[$firstSeriesDataKey][self::resolveSecondSeriesFormattedValueName($secondSeriesKey)] =
                            $this->formatValue($displayAttribute, $value);
                    }
                }
            }
            return $data;
        }

        protected function formatValue(DisplayAttributeForReportForm $displayAttribute, $value)
        {
            if($displayAttribute->isATypeOfCurrencyValue())
            {
                if($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_ACTUAL)
                {
                    return Yii::app()->numberFormatter->formatDecimal($value);
                }
                elseif($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_BASE)
                {
                    return Yii::app()->numberFormatter->formatCurrency($value, Yii::app()->currencyHelper->getBaseCode());
                }
                elseif($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_SPOT)
                {
                    return Yii::app()->numberFormatter->formatCurrency($value * $this->report->getFromBaseToSpotRate(),
                                                                       $this->report->getSpotConversionCurrencyCode());
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            elseif($displayAttribute->getDisplayElementType() == 'Decimal')
            {
                return Yii::app()->formatNumber($value);
            }
            elseif($displayAttribute->getDisplayElementType() == 'Integer')
            {
                return Yii::app()->numberFormatter->formatDecimal($value);
            }
            elseif($displayAttribute->getDisplayElementType()  == 'Date')
            {
                return DateTimeUtil::resolveValueForDateLocaleFormattedDisplay($value);
            }
            elseif($displayAttribute->getDisplayElementType() == 'DateTime')
            {
                return DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($value);
            }
            else
            {
                return $value;
            }
        }
    }
?>

