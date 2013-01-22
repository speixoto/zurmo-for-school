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

    class DataToReportUtil
    {
        public static function resolveReportByWizardPostData(Report $report, $postData, $wizardFormClassName)
        {
            assert('is_array($postData)');
            $data = ArrayUtil::getArrayValue($postData, $wizardFormClassName);
            if(isset($data['description']))
            {
                $report->setDescription($data['description']);
            }
            if(isset($data['moduleClassName']))
            {
                $report->setModuleClassName($data['moduleClassName']);
            }
            if(isset($data['name']))
            {
                $report->setName($data['name']);
            }
            if(isset($data['filtersStructure']))
            {
                $report->setFiltersStructure($data['filtersStructure']);
            }
            if(null != ArrayUtil::getArrayValue($data, 'ownerId'))
            {
                $owner = User::getById((int)$data['ownerId']);
                $report->setOwner($owner);
            }
            else
            {
                $report->setOwner(new User());
            }
            if(isset($data['currencyConversionType']))
            {
                $report->setCurrencyConversionType((int)$data['currencyConversionType']);
            }
            if(isset($data['spotConversionCurrencyCode']))
            {
                $report->setSpotConversionCurrencyCode($data['spotConversionCurrencyCode']);
            }
            self::resolveFilters                    ($data, $report);
            self::resolveOrderBys                   ($data, $report);
            self::resolveDisplayAttributes          ($data, $report);
            self::resolveDrillDownDisplayAttributes ($data, $report);
            self::resolveGroupBys                   ($data, $report);
            self::resolveChart                      ($data, $report);
        }

        public static function resolveFilters($data, Report $report)
        {
            $report->removeAllFilters();
            $moduleClassName = $report->getModuleClassName();
            if(count($filtersData = ArrayUtil::getArrayValue($data, ComponentForReportForm::TYPE_FILTERS)) > 0)
            {
                $sanitizedFiltersData = self::sanitizeFiltersData($moduleClassName, $report->getType(), $filtersData);
                foreach($sanitizedFiltersData as $filterData)
                {
                    $filter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                      $report->getType());
                    $filter->setAttributes($filterData);
                    $report->addFilter($filter);
                }
            }
            else
            {
                $report->removeAllFilters();
            }
        }

        public static function sanitizeFiltersData($moduleClassName, $reportType, array $filtersData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($reportType)');
            $sanitizedFiltersData = array();
            foreach($filtersData as $filterData)
            {
                $sanitizedFiltersData[] = static::sanitizeFilterData($moduleClassName,
                                                                     $moduleClassName::getPrimaryModelName(),
                                                                     $reportType,
                                                                     $filterData);
            }
            return $sanitizedFiltersData;
        }

        protected static function sanitizeFilterData($moduleClassName, $modelClassName, $reportType, $filterData)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($reportType)');
            assert('is_array($filterData)');
            $filterForSanitizing = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                           $reportType);

            $filterForSanitizing->setAttributes($filterData);
            $valueElementType = null;
            $valueElementType    = $filterForSanitizing->getValueElementType();
            if($valueElementType == 'MixedDateTypesForReport')
            {
                if(isset($filterData['value']) && $filterData['value'] !== null)
                {
                    $filterData['value']       = DateTimeUtil::resolveValueForDateDBFormatted($filterData['value']);
                }
                if(isset($filterData['secondValue']) && $filterData['secondValue'] !== null)
                {
                    $filterData['secondValue'] = DateTimeUtil::resolveValueForDateDBFormatted($filterData['secondValue']);
                }
            }
            return $filterData;
        }

        protected static function resolveOrderBys($data, Report $report)
        {
            $report->removeAllOrderBys();
            $moduleClassName = $report->getModuleClassName();
            if(count($orderBysData = ArrayUtil::getArrayValue($data, ComponentForReportForm::TYPE_ORDER_BYS)) > 0)
            {
                foreach($orderBysData as $orderByData)
                {
                    $orderBy = new OrderByForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                        $report->getType());
                    $orderBy->setAttributes($orderByData);
                    $report->addOrderBy($orderBy);
                }
            }
            else
            {
                $report->removeAllOrderBys();
            }
        }

        protected static function resolveDisplayAttributes($data, Report $report)
        {
            $report->removeAllDisplayAttributes();
            $moduleClassName = $report->getModuleClassName();
            if(count($displayAttributesData =
                     ArrayUtil::getArrayValue($data, ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES)) > 0)
            {
                foreach($displayAttributesData as $displayAttributeData)
                {
                    $displayAttribute = new DisplayAttributeForReportForm($moduleClassName,
                                                                          $moduleClassName::getPrimaryModelName(),
                                                                          $report->getType());
                    $displayAttribute->setAttributes($displayAttributeData);
                    $report->addDisplayAttribute($displayAttribute);
                }
            }
            else
            {
                $report->removeAllDisplayAttributes();
            }
        }

        protected static function resolveDrillDownDisplayAttributes($data, Report $report)
        {
            $report->removeAllDrillDownDisplayAttributes();
            $moduleClassName = $report->getModuleClassName();
            if(count($drillDownDisplayAttributesData =
                     ArrayUtil::getArrayValue($data, ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)) > 0)
            {
                foreach($drillDownDisplayAttributesData as $drillDownDisplayAttributeData)
                {
                    $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm($moduleClassName,
                                                                          $moduleClassName::getPrimaryModelName(),
                                                                          $report->getType());
                    $drillDownDisplayAttribute->setAttributes($drillDownDisplayAttributeData);
                    $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);
                }
            }
            else
            {
                $report->removeAllDrillDownDisplayAttributes();
            }
        }

        protected static function resolveGroupBys($data, Report $report)
        {
            $report->removeAllGroupBys();
            $moduleClassName = $report->getModuleClassName();
            if(count($groupBysData = ArrayUtil::getArrayValue($data, ComponentForReportForm::TYPE_GROUP_BYS)) > 0)
            {
                foreach($groupBysData as $groupByData)
                {
                    $groupBy = new GroupByForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                        $report->getType());
                    $groupBy->setAttributes($groupByData);
                    $report->addGroupBy($groupBy);
                }
            }
            else
            {
                $report->removeAllGroupBys();
            }
        }

        protected static function resolveChart($data, Report $report)
        {
            if($report->getType() != Report::TYPE_SUMMATION)
            {
                return;
            }
            $moduleClassName = $report->getModuleClassName();
            if($moduleClassName != null)
            {
                $modelClassName      = $moduleClassName::getPrimaryModelName();
                $adapter             = ModelRelationsAndAttributesToSummationReportAdapter::
                                       make($moduleClassName, $modelClassName, $report->getType());
                $seriesDataAndLabels = ReportUtil::makeDataAndLabelsForSeriesOrRange(
                                       $adapter->getAttributesForChartSeries($report->getGroupBys(),
                                                                             $report->getDisplayAttributes()));
                $rangeDataAndLabels  = ReportUtil::makeDataAndLabelsForSeriesOrRange(
                                       $adapter->getAttributesForChartRange($report->getDisplayAttributes()));
            }
            else
            {
                $seriesDataAndLabels = array();
                $rangeDataAndLabels  = array();
            }

            $chart           = new ChartForReportForm($seriesDataAndLabels, $rangeDataAndLabels);
            if(null != $chartData = ArrayUtil::getArrayValue($data, 'ChartForReportForm'))
            {
                $chart->setAttributes($chartData);
            }
            $report->setChart($chart);
        }
    }
?>