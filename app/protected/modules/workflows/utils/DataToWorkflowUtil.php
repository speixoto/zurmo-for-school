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

    class DataToWorkflowUtil
    {
        public static function resolveWorkflowByWizardPostData(Workflow $workflow, $postData, $wizardFormClassName)
        {
            assert('is_array($postData)');
            $data = ArrayUtil::getArrayValue($postData, $wizardFormClassName);
            if(isset($data['description']))
            {
                $workflow->setDescription($data['description']);
            }
            if(isset($data['moduleClassName']))
            {
                $workflow->setModuleClassName($data['moduleClassName']);
            }
            if(isset($data['name']))
            {
                $workflow->setName($data['name']);
            }
            if(isset($data['triggersStructure']))
            {
                $workflow->setFiltersStructure($data['triggersStructure']);
            }
            self::resolveTriggers                   ($data, $workflow);
            self::resolveActions                    ($data, $workflow);
            self::resolveTimeTrigger                ($data, $workflow);
        }

        public static function resolveTriggers($data, Workflow $workflow)
        {
            $report->removeAllFilters();
            $moduleClassName = $report->getModuleClassName();
            if(count($triggersData = ArrayUtil::getArrayValue($data, ComponentForWorkflowForm::TYPE_TRIGGERS)) > 0)
            {
                $sanitizedTriggersData = self::sanitizeTriggersData($moduleClassName, $report->getType(), $triggersData);
                foreach($sanitizedTriggersData as $triggerData)
                {
                    $trigger = new TriggerForWorkflowForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                          $workflow->getType());
                    $trigger->setAttributes($filterData);
                    $workflow->addTrigger($trigger);
                }
            }
            else
            {
                $report->removeAllFilters();
            }
        }

        public static function sanitizeTriggersData($moduleClassName, $reportType, array $filtersData)
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

        protected static function sanitizeTriggerData($moduleClassName, $modelClassName, $reportType, $filterData)
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

        protected static function resolveActions($data, Report $report)
        {
            //todo: we need to sanitize action data too since we can be populating things like static dates..
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

        protected static function resolveTimeTrigger($data, Report $report)
        {
            //todo: need to re-work this method for time trigger.
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