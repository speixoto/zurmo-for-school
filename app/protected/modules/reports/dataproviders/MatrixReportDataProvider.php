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

    class MatrixReportDataProvider extends ReportDataProvider
    {
        /**
         * Resolved to include the groupBys as query only display attributes
         * @var null | array of DisplayAttributesForReportForms
         */
        private $resolvedDisplayAttributes;

        /**
         * Resolved groupBys in order of y-axis groupBys then x-axis groupBys
         * @var null | array of GroupBysForReportForms
         */
        private $resolvedGroupBys;

        private $xAxisGroupByDataValues;

        private $yAxisGroupByDataValues;

        public static function resolveColumnAliasName($index)
        {
            assert('is_int($index)');
            return DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX . $index;
        }

        public function calculateTotalItemCount()
        {
            //todo: somewhere check size of x and y to make sure not to big to make a matrix report
            //todo: total count is wrong because we dont use every row. not sure how to do deal with that.
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
            $rows                   = R::getAll($sql);
            return count($rows);
        }

        public function resolveDisplayAttributes()
        {
            if($this->resolvedDisplayAttributes == null)
            {
                $this->resolvedDisplayAttributes = array();
                foreach($this->report->getDisplayAttributes() as $displayAttribute)
                {
                    $this->resolvedDisplayAttributes[] = $displayAttribute;
                }
                foreach($this->resolveGroupBys() as $groupBy)
                {
                    $displayAttribute                                 = new DisplayAttributeForReportForm(
                                                                        $groupBy->getModuleClassName(),
                                                                        $groupBy->getModelClassName(),
                                                                        $this->report->getType());
                    $displayAttribute->attributeIndexOrDerivedType    = $groupBy->attributeIndexOrDerivedType;
                    $displayAttribute->queryOnly                      = true;
                    $displayAttribute->madeViaSelectInsteadOfViaModel = true;
                    $this->resolvedDisplayAttributes[]                = $displayAttribute;
                }
            }
            return $this->resolvedDisplayAttributes;
        }

        public function resolveGroupBys()
        {
            if($this->resolvedGroupBys != null)
            {
                return $this->resolvedGroupBys;
            }
            $this->resolvedGroupBys = array();
            foreach($this->report->getGroupBys() as $groupBy)
            {
                if($groupBy->axis == 'y')
                {
                    $this->resolvedGroupBys[] = $groupBy;
                }
            }
            foreach($this->report->getGroupBys() as $groupBy)
            {
                if($groupBy->axis == 'x')
                {
                    $this->resolvedGroupBys[] = $groupBy;
                }
            }
            return $this->resolvedGroupBys;
        }

        public function getXAxisGroupByDataValuesCount()
        {
            $count = 1;
            foreach($this->getXAxisGroupByDataValues() as $groupByValues)
            {
                $count = $count * count($groupByValues);
            }
            return $count;

        }

        public function getYAxisGroupByDataValuesCount()
        {
            return count($this->getYAxisGroupByDataValues());

        }

        /**
         * Public for testing purposes
         * @return array
         */
        public function makeXAxisGroupingsForColumnNamesData()
        {
            $data                        = array();
            $xAxisGroupByDataValues      = $this->getXAxisGroupByDataValues();
            $xAxisGroupByDataValuesCount = count($xAxisGroupByDataValues);
            $attributeKey                = 0;
            $startingGroupBysIndex       = 0;
            $this->resolveXAxisGroupingsForColumnNames($data, array_values($xAxisGroupByDataValues), $attributeKey,
                                                       $xAxisGroupByDataValuesCount, $startingGroupBysIndex);
            return $data;
        }

        protected function resolveXAxisGroupingsForColumnNames(& $data, $indexedXAxisGroupByDataValues, & $attributeKey,
                                                               $xAxisGroupBysCount, $startingIndex)
        {
            assert('is_array($data)');
            assert('is_array($indexedXAxisGroupByDataValues)');
            assert('is_int($attributeKey)');
            assert('is_int($xAxisGroupBysCount)');
            assert('is_int($startingIndex)');
            foreach($indexedXAxisGroupByDataValues[$startingIndex] as $value)
            {
                $data[$value] = array();
                if(($startingIndex + 1) == $xAxisGroupBysCount)
                {
                    foreach($this->resolveDisplayAttributes() as $displayAttribute)
                    {
                        if($displayAttribute->queryOnly != true)
                        {
                            $data[$value][$displayAttribute->attributeIndexOrDerivedType] =
                                static::resolveColumnAliasName($attributeKey);
                            $attributeKey ++;
                        }
                    }
                }
                else
                {
                    $this->resolveXAxisGroupingsForColumnNames($data[$value], $indexedXAxisGroupByDataValues,
                                                               $attributeKey, $xAxisGroupBysCount, $startingIndex + 1);
                }
            }
        }

        protected function runQueryAndGetResolveResultsData($offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $selectQueryAdapter                        = new RedBeanModelSelectQueryAdapter();
            $sql                                       = $this->makeSqlQueryForFetchingData($selectQueryAdapter,
                                                         $offset, $limit);
            $rows                                      = $this->getRowsData($sql);
            $resultsData                               = array();
            $idByOffset                                = 0;
            $calculationsCount                         = $this->getDisplayCalculationsCount();
            $xAxisGroupByDataValuesCount               = $this->getXAxisGroupByDataValuesCount() * $calculationsCount;
            $xAxisGroupingsColumnNamesData             = $this->makeXAxisGroupingsForColumnNamesData();
            $displayAttributesThatAreYAxisGroupBys     = $this->getDisplayAttributesThatAreYAxisGroupBys();
            $previousYAxisDisplayAttributesUniqueIndex = $this->resolveYAxisDisplayAttributesUniqueIndex(
                                                         $rows[0], $displayAttributesThatAreYAxisGroupBys);
            $resultsData[$idByOffset]                  = new ReportResultsRowData($this->resolveDisplayAttributes(), 0);
            $this->addDefaultColumnNamesAndValuesToReportResultsRowData($resultsData[$idByOffset],
                                                                        $xAxisGroupByDataValuesCount);
            foreach ($rows as $row)
            {
                $currentYAxisDisplayAttributesUniqueIndex = $this->resolveYAxisDisplayAttributesUniqueIndex(
                                                            $row, $displayAttributesThatAreYAxisGroupBys);
                if($previousYAxisDisplayAttributesUniqueIndex != $currentYAxisDisplayAttributesUniqueIndex)
                {
                    $idByOffset ++;
                    $resultsData[$idByOffset] = new ReportResultsRowData($this->resolveDisplayAttributes(), $idByOffset);
                    $this->addDefaultColumnNamesAndValuesToReportResultsRowData($resultsData[$idByOffset],
                                                                                $xAxisGroupByDataValuesCount);
                }
                $tempData = $xAxisGroupingsColumnNamesData;
                foreach($this->resolveDisplayAttributes() as $displayAttribute)
                {
                    if($this->isDisplayAttributeAnXAxisGroupBy($displayAttribute))
                    {
                        $value = $row[$displayAttribute->columnAliasName];
                        $tempData = $tempData[$value];
                    }
                }
                //At this point $tempData is at the final level, where the actual display calculations are located
                foreach($this->resolveDisplayAttributes() as $displayAttribute)
                {
                    if(!$displayAttribute->queryOnly)
                    {
                        $value = $row[$displayAttribute->columnAliasName];
                        $columnAliasName = $tempData[$displayAttribute->attributeIndexOrDerivedType];
                        $resultsData[$idByOffset]->addSelectedColumnNameAndValue($columnAliasName, $value);
                    }
                }
                $previousYAxisDisplayAttributesUniqueIndex = $currentYAxisDisplayAttributesUniqueIndex;
            }
            return $resultsData;
        }

        protected function getDisplayCalculationsCount()
        {
            $count           = 0;
            foreach($this->resolveDisplayAttributes() as $displayAttribute)
            {
                if(!$displayAttribute->queryOnly)
                {
                    $count ++;
                }
            }
            return $count;
        }

        protected function getXAxisGroupByDataValues()
        {
            if($this->xAxisGroupByDataValues == null)
            {
                $selectQueryAdapter = new RedBeanModelSelectQueryAdapter();
                $sql                = $this->makeSqlQueryForFetchingData($selectQueryAdapter, null, null);
                $rows               = $this->getRowsData($sql);
                foreach($rows as $row)
                {
                    foreach($this->getDisplayAttributesThatAreXAxisGroupBys() as $displayAttribute)
                    {
                        if(!isset($this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]) ||
                            !in_array($row[$displayAttribute->columnAliasName],
                                $this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]))
                        {
                            $this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType][] =
                                $row[$displayAttribute->columnAliasName];
                        }
                    }
                }
            }
            return $this->xAxisGroupByDataValues;
        }

        protected function getYAxisGroupByDataValues()
        {
            if($this->yAxisGroupByDataValues == null)
            {
                $selectQueryAdapter = new RedBeanModelSelectQueryAdapter();
                $sql                = $this->makeSqlQueryForFetchingData($selectQueryAdapter, null, null);
                $rows               = $this->getRowsData($sql);
                foreach($rows as $row)
                {
                    foreach($this->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
                    {
                        if(!isset($this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]) ||
                            in_array($row[$displayAttribute->columnAliasName],
                                     $this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]))
                        {
                            $this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType][] =
                                $row[$displayAttribute->columnAliasName];
                        }
                    }
                }
            }
            return $this->yAxisGroupByDataValues;
        }

        protected function isReportValidType()
        {
            if($this->report->getType() != Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
        }

        protected function getXAxisGroupBys()
        {
            $xAxisGroupBys = array();
            foreach($this->report->getGroupBys() as $groupBy)
            {
                if($groupBy->axis == 'x')
                {
                    $xAxisGroupBys[] = $groupBy;
                }
            }
            return $xAxisGroupBys;
        }

        protected function getDisplayAttributesThatAreXAxisGroupBys()
        {
            $displayAttributes = array();
            foreach($this->resolveDisplayAttributes() as $displayAttribute)
            {
                foreach($this->getXAxisGroupBys() as $xAxisGroupBy)
                {
                    if($displayAttribute->attributeIndexOrDerivedType ==
                        $xAxisGroupBy->attributeIndexOrDerivedType)
                    {
                        $displayAttributes[] = $displayAttribute;
                        break;
                    }
                }
            }
            return $displayAttributes;
        }

        protected function getYAxisGroupBys()
        {
            $yAxisGroupBys = array();
            foreach($this->report->getGroupBys() as $groupBy)
            {
                if($groupBy->axis == 'y')
                {
                    $yAxisGroupBys[] = $groupBy;
                }
            }
            return $yAxisGroupBys;
        }
//todo: we might not need this method
        protected function getLastYAxisGroupBy()
        {
            $yAxisGroupBys = $this->getYAxisGroupBys();
            return end($yAxisGroupBys);
        }

        protected function getDisplayAttributesThatAreYAxisGroupBys()
        {
            $displayAttributes = array();
            foreach($this->resolveDisplayAttributes() as $displayAttribute)
            {
                foreach($this->getYAxisGroupBys() as $groupBy)
                {
                    if($displayAttribute->attributeIndexOrDerivedType ==
                       $groupBy->attributeIndexOrDerivedType)
                    {
                        $displayAttributes[] = $displayAttribute;
                        break;
                    }
                }
            }
            return $displayAttributes;
        }

        protected function isDisplayAttributeAnXAxisGroupBy($displayAttribute)
        {
            foreach($this->getXAxisGroupBys() as $groupBy)
            {
                if($displayAttribute->attributeIndexOrDerivedType ==
                    $groupBy->attributeIndexOrDerivedType)
                {
                    return true;
                }
            }
            return false;
        }

        protected function addDefaultColumnNamesAndValuesToReportResultsRowData(ReportResultsRowData $reportResultsRowData, $totalCount)
        {
            for ($i = 0; $i < $totalCount; $i++)
            {
                $columnAliasName = DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX . $i;
                $value = 0;
                $reportResultsRowData->addSelectedColumnNameAndValue($columnAliasName, $value);
            }
        }

        protected function resolveYAxisDisplayAttributesUniqueIndex($rowData, $displayAttributesThatAreYAxisGroupBys)
        {
            $uniqueIndex = null;
            foreach($displayAttributesThatAreYAxisGroupBys as $displayAttribute)
            {
                if($uniqueIndex != null)
                {
                    $uniqueIndex .= FormModelUtil::DELIMITER;
                }
                $uniqueIndex .= $rowData[$displayAttribute->columnAliasName];
            }
            return $uniqueIndex;
        }
    }
?>