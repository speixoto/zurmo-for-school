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

        public function getReport()
        {
            return $this->report;
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
                $offset = 0;
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
            echo $sql . "<BR>";
            $rows         = R::getAll($sql);
            $resultsData  = array();
            $idByOffset   = $offset;
            foreach ($rows as $key => $row)
            {
                $reportResultsRowData = new ReportResultsRowData($this->resolveDisplayAttributesToUse(), $idByOffset);
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

        protected function resolveDisplayAttributesToUse()
        {
            return $this->report->getDisplayAttributes();
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, true);
            echo $sql . "<BR>";
            $count = R::getCell($sql);
            if ($count === null || empty($count))
            {
                $count = 0;
            }
            echo 'the count ' . $count . "<BR>";
            return $count;
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
            $groupBy                = $this->makeGroupBysContent($joinTablesAdapter, $selectQueryAdapter);
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
            $builder->makeQueryContent($this->report->getDisplayAttributes());
        }

        protected function makeFiltersContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $builder = new FiltersReportQueryBuilder($joinTablesAdapter, $this->report->getFiltersStructure());
            return $builder->makeQueryContent($this->report->getFilters());
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
            return $builder->makeQueryContent($this->report->getGroupBys());
        }
    }
?>