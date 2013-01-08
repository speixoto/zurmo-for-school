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

    abstract class ReportDataProvider extends CDataProvider
    {
        abstract protected function isReportValidType();

        protected $report;

        protected $runReport = false;

        protected $offset;

        private $_rowsData;

        public function __construct(Report $report, array $config = array())
        {
            $this->report = $report;
            $this->isReportValidType();
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }

        public function setRunReport($runReport)
        {
            assert('is_bool($runReport)');
            $this->runReport = $runReport;
        }

        public function getReport() //todo: can we avoid needing this from the outside? just have wrapper methods here in the data provider? would be cleaner
        {
            return $this->report;
        }

        public function resolveDisplayAttributes()
        {
            return $this->report->getDisplayAttributes();
        }

        public function resolveGroupBys()
        {
            return $this->report->getGroupBys();
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, true);
            $count = R::getCell($sql);
            if ($count === null || empty($count))
            {
                $count = 0;
            }
            return $count;
        }

        protected function fetchData()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $totalItemCount = $this->getTotalItemCount();
                $pagination->setItemCount($totalItemCount);
                $offset = $pagination->getOffset();
                $limit  = $pagination->getLimit();
            }
            else
            {
                $offset = null;
                $limit  = null;
            }
            if ($this->offset != null)
            {
                $offset = $this->offset;
            }
            if ($totalItemCount == 0)
            {
                return array();
            }
            return $this->runQueryAndGetResolveResultsData($offset, $limit);
        }

        protected function runQueryAndGetResolveResultsData($offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql          = $this->makeSqlQueryForFetchingData($selectQueryAdapter, $offset, $limit);
            $rows         = $this->getRowsData($sql);
            $resultsData  = array();
            $idByOffset   = self::resolveIdByOffset($offset);
            foreach ($rows as $key => $row)
            {
                $reportResultsRowData = new ReportResultsRowData($this->resolveDisplayAttributes(), $idByOffset);
                foreach($selectQueryAdapter->getIdTableAliasesAndModelClassNames() as $tableAlias => $modelClassName)
                {
                    $idColumnName = $selectQueryAdapter->getIdColumNameByTableAlias($tableAlias);
                    $id           = (int)$row[$idColumnName];
                    if($id != null)
                    {
                        $reportResultsRowData->addModelAndAlias($modelClassName::getById($id), $tableAlias);
                    }
                    unset($row[$idColumnName]);
                }
                foreach($row as $columnName => $value)
                {
                    $reportResultsRowData->addSelectedColumnNameAndValue($columnName, $value);
                }
                $resultsData[$key] = $reportResultsRowData;
                $idByOffset ++;
            }
            return $resultsData;
        }

        protected static function resolveIdByOffset($offset)
        {
            assert('is_int($offset) || $offset == null');
            if($offset == null)
            {
                return 0;
            }
            return $offset;
        }

        protected function getRowsData($sql)
        {
            assert('is_string($sql)');
            if($this->_rowsData == null)
            {
                $this->_rowsData = R::getAll($sql);
            }
            return $this->_rowsData;
        }

        /**
         * See the yii documentation.
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $data)
            {
                $keys[] = $data->getId();
            }
            return $keys;
        }

        protected function makeSqlQueryForFetchingData(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $moduleClassName        = $this->report->getModuleClassName();
            $modelClassName         = $moduleClassName::getPrimaryModelName();
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $this->makeDisplayAttributes($joinTablesAdapter, $selectQueryAdapter);
            $where                  = $this->makeFiltersContent($joinTablesAdapter);
            $orderBy                = $this->makeOrderBysContent($joinTablesAdapter, $selectQueryAdapter);
            $groupBy                = $this->makeGroupBysContent($joinTablesAdapter, $selectQueryAdapter);

            return                    SQLQueryUtil::makeQuery($modelClassName::getTableName($modelClassName),
                                      $selectQueryAdapter, $joinTablesAdapter, $offset, $limit, $where, $orderBy, $groupBy);
        }

        protected function makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, $selectJustCount = false)
        {
            $moduleClassName        = $this->report->getModuleClassName();
            $modelClassName         = $moduleClassName::getPrimaryModelName();
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $this->makeDisplayAttributes($joinTablesAdapter, $selectQueryAdapter);
            $where                  = $this->makeFiltersContent($joinTablesAdapter);
            $orderBy                = $this->makeOrderBysContent($joinTablesAdapter, $selectQueryAdapter);
            $groupBy                = $this->makeGroupBysContentForCount($joinTablesAdapter, $selectQueryAdapter);
            //Make a fresh selectQueryAdapter that only has a count clause
            if($selectJustCount)
            {
                //todo: if distinct we shouldn't actually do a NonSpecificCountClause, but this means distinct should know what table/col it is distincting on... so we need to add that
                $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter($selectQueryAdapter->isDistinct());
                $selectQueryAdapter->addNonSpecificCountClause();
            }
            return                    SQLQueryUtil::makeQuery($modelClassName::getTableName($modelClassName),
                                      $selectQueryAdapter, $joinTablesAdapter, null, null, $where, $orderBy, $groupBy);
        }

        protected function makeDisplayAttributes(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                                 RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            $builder                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $builder->makeQueryContent($this->resolveDisplayAttributes());
        }

        protected function makeFiltersContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $filters          = $this->report->getFilters();
            $filtersStructure = $this->report->getFiltersStructure();
            $resolvedFilters  = $this->resolveFiltersForVariableStates($filters, $filtersStructure);
            $resolvedFilters  = $this->resolveFiltersForReadPermissions($filters, $filtersStructure);
            $builder = new FiltersReportQueryBuilder($joinTablesAdapter, $filtersStructure);
            return $builder->makeQueryContent($resolvedFilters);
        }

        protected function makeOrderBysContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                               RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            $builder = new OrderBysReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            return $builder->makeQueryContent($this->report->getOrderBys());
        }

        protected function makeGroupBysContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                               RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            $builder = new GroupBysReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            return $builder->makeQueryContent($this->resolveGroupBys());
        }

        protected function makeGroupBysContentForCount(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                                       RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            return $this->makeGroupBysContent($joinTablesAdapter, $selectQueryAdapter);
        }

        /**
         * Public for testing purposes only
         * @param $filters
         * @param $filtersStructure
         * @return array
         */
        public function resolveFiltersForReadPermissions(array $filters, & $filtersStructure)
        {
            $attributeIndexes     = $this->makeReadPermissionsAttributeIndexes($filters);
            $existingFiltersCount = count($filters);
            $structurePosition    = $existingFiltersCount + 1;
            $readStructure        = null;
            foreach($attributeIndexes as $attributeIndexOrDerivedTypePrefix => $attributeOrDerivedAttributeTypes)
            {
                $structure = null;
                foreach($attributeOrDerivedAttributeTypes as $attributeOrDerivedAttributeType)
                {
                    if($structure != null)
                    {
                        $structure .= ' or ';
                    }
                    $structure .= $structurePosition;
                    $structurePosition ++;
                    $filters[]  = $this->resolveFilterForReadPermissionAttributeIndex($attributeIndexOrDerivedTypePrefix,
                                                                                      $attributeOrDerivedAttributeType);
                }
                if($structure != null)
                {
                    if($readStructure != null)
                    {
                        $readStructure .= ' and ';
                    }
                    $readStructure .= '(' . $structure . ')';
                }
            }
            if($readStructure != null)
            {
                if($filtersStructure != null)
                {
                    $filtersStructure .= ' and (' . $readStructure . ')';
                }
                else
                {
                    $filtersStructure .= $readStructure;
                }
            }
            return $filters;
        }

        protected function resolveFilterForReadPermissionAttributeIndex($attributeIndexOrDerivedTypePrefix, $attributeOrDerivedAttributeType)
        {
            assert('is_string($attributeIndexOrDerivedTypePrefix) || $attributeIndexOrDerivedTypePrefix == null');
            assert('is_string($attributeOrDerivedAttributeType)');
            $moduleClassName = $this->report->getModuleClassName();
            if($attributeOrDerivedAttributeType == 'ReadOptimization')
            {
                $filter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                       $this->report->getType());
                $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix . $attributeOrDerivedAttributeType;
            }
            elseif($attributeOrDerivedAttributeType == 'owner__User')
            {
                $filter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                       $this->report->getType());
                $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix. $attributeOrDerivedAttributeType;
                $filter->operator                    = OperatorRules::TYPE_EQUALS;
                $filter->value                       = Yii::app()->user->userModel->id;
            }
            else
            {
                throw new NotSupportedException();
            }
            return $filter;
        }

        protected function makeReadPermissionsAttributeIndexes(array $filters)
        {
            $moduleClassName = $this->report->getModuleClassName();
            $attributeIndexes = array();
            ReadPermissionsForReportUtil::
                resolveAttributeIndexes($moduleClassName::getPrimaryModelName(), $attributeIndexes);
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveDisplayAttributes());
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $filters);
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->report->getOrderBys());
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveGroupBys());
            return $attributeIndexes;
        }

        /**
         * Public for testing purposes only
         * @param $filters
         * @param $filtersStructure
         * @return array
         */
        public function resolveFiltersForVariableStates($filters, & $filtersStructure)
        {
            $attributeIndexes     = $this->makeVariableStatesAttributeIndexes($filters);
            $existingFiltersCount = count($filters);
            $structurePosition    = $existingFiltersCount + 1;
            $readStructure        = null;
            foreach($attributeIndexes as $attributeIndexOrDerivedTypePrefix => $variableStateData)
            {
                $structure = $structurePosition;
                $structurePosition ++;
                $filters[]  = $this->resolveFilterForVariableStateAttributeIndex($attributeIndexOrDerivedTypePrefix,
                              $variableStateData);
                if($readStructure != null)
                {
                    $readStructure .= ' and ';
                }
                $readStructure .= $structure;
            }
            if($readStructure != null)
            {
                if($filtersStructure != null)
                {
                    $filtersStructure .= ' and (' . $readStructure . ')';
                }
                else
                {
                    $filtersStructure .= $readStructure;
                }
            }
            return $filters;
        }

        protected function resolveFilterForVariableStateAttributeIndex($attributeIndexOrDerivedTypePrefix, $variableStateData)
        {
            assert('is_string($attributeIndexOrDerivedTypePrefix) || $attributeIndexOrDerivedTypePrefix == null');
            assert('is_array($variableStateData) && count($variableStateData) == 2');
            $moduleClassName                     = $this->report->getModuleClassName();
            $filter                              = new FilterForReportForm($moduleClassName,
                                                   $moduleClassName::getPrimaryModelName(),
                                                   $this->report->getType());
            $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix. $variableStateData[0];
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = $variableStateData[1];
            return $filter;
        }

        protected function makeVariableStatesAttributeIndexes(array $filters)
        {
            $moduleClassName = $this->report->getModuleClassName();
            $attributeIndexes = array();
            VariableStatesForReportUtil::
                resolveAttributeIndexes($moduleClassName::getPrimaryModelName(), $attributeIndexes);
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveDisplayAttributes());
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $filters);
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->report->getOrderBys());
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveGroupBys());
            return $attributeIndexes;
        }


        protected function getDisplayAttributeByAttribute($attribute)
        {
            foreach($this->resolveDisplayAttributes() as $displayAttribute)
            {
                if($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $displayAttribute;
                }
            }
        }

        protected function getDisplayAttributeKeyByAttribute($attribute)
        {
            foreach($this->resolveDisplayAttributes() as $key =>  $displayAttribute)
            {
                if($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $key;
                }
            }
        }
    }
?>