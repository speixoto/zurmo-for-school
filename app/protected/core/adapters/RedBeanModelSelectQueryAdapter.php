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
     * Adapts select information into query parts for a particular sql query. Automatically determines count,
     * distinct, sums, columns and aliases.
     */
    class RedBeanModelSelectQueryAdapter
    {
        /**
         * Array of select clauses
         * @var array
         */
        private $clauses;

        /**
         * Set to true if the select query needs to be distinct
         * with the same id.
         * @var boolean
         */
        private $distinct = false;

        /**
         * Count of select clauses
         * @var integer
         */
        private $clausesCount = 0;

        private $countClausePresent = false;

        private $idTableAliasesAndModelClassNames = array();

        public function __construct($distinct = false)
        {
            $this->distinct = $distinct;
        }

        protected function increaseClausesCountByOne()
        {
            $this->clausesCount++;
        }

        public function isDistinct()
        {
            return $this->distinct;
        }

        public function getClausesCount()
        {
            return $this->clausesCount;
        }

        public function getClauses()
        {
            return $this->clauses;
        }

        public function getIdTableAliasesAndModelClassNames()
        {
            return $this->idTableAliasesAndModelClassNames;
        }

        public function getIdColumNameByTableAlias($tableAliasName)
        {
            assert('is_string($tableAliasName)');
            return $tableAliasName . 'id';
        }

        public function getSelect()
        {
            if ($this->getClausesCount() == 0)
            {
                throw new NotSupportedException();
            }
            $selectQuery = 'select ';
            if ($this->distinct && !$this->countClausePresent)
            {
                $selectQuery .= 'distinct ';
            }
            foreach ($this->clauses as $clauseCount => $clause)
            {
                $selectQuery .= $clause;
                if ($this->getClausesCount() > 1 && ($clauseCount + 1) < $this->getClausesCount())
                {
                    $selectQuery .= ','; // Not Coding Standard
                }
                $selectQuery .= ' ';
            }
            return $selectQuery;
        }

        public function addNonSpecificCountClause()
        {
            $this->clauses[] = "count(*)";
            $this->countClausePresent = true;
            $this->increaseClausesCountByOne();
        }

        public function addCountClause($tableName, $columnName = 'id', $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $distinctPart = null;
            if ($this->distinct)
            {
                $distinctPart = 'distinct ';
            }
            $clause = "count({$distinctPart}{$quote}$tableName{$quote}.{$quote}$columnName{$quote})";
            $this->clauses[] = self::resolveForAliasName($clause, $aliasName);
            $this->countClausePresent = true;
            $this->increaseClausesCountByOne();
        }

        public function addClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $this->clauses[] = self::resolveForAliasName("{$quote}$tableName{$quote}.{$quote}$columnName{$quote}", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addClauseByQueryString($queryString, $aliasName = null)
        {
            assert('is_string($queryString)');
            assert('is_string($aliasName) || $aliasName == null');
            $this->clauses[] = self::resolveForAliasName($queryString, $aliasName);
            $this->increaseClausesCountByOne();
        }


        public function addClauseWithColumnNameOnlyAndNoEnclosure($columnName, $aliasName = null)
        {
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $clause = "$columnName";
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            $this->clauses[] = self::resolveForAliasName("$columnName", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addSummationClause($tableName, $columnName, $aliasName = null, $queryStringExtraPart = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}" . $queryStringExtraPart;
            $this->clauses[] = self::resolveForAliasName("sum({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addAverageClause($tableName, $columnName, $aliasName = null, $queryStringExtraPart = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}". $queryStringExtraPart;
            $this->clauses[] = self::resolveForAliasName("avg({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addMinimumClause($tableName, $columnName, $aliasName = null, $queryStringExtraPart = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}". $queryStringExtraPart;
            $this->clauses[] = self::resolveForAliasName("min({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addMaximumClause($tableName, $columnName, $aliasName = null, $queryStringExtraPart = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            assert('is_string($queryStringExtraPart) || $queryStringExtraPart == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}". $queryStringExtraPart;
            $this->clauses[] = self::resolveForAliasName("max({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addDayClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            $this->clauses[] = self::resolveForAliasName("day({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addWeekClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            $this->clauses[] = self::resolveForAliasName("week({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addMonthClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            $this->clauses[] = self::resolveForAliasName("month({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addQuarterClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            $this->clauses[] = self::resolveForAliasName("quarter({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function addYearClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote           = DatabaseCompatibilityUtil::getQuote();
            $queryString     = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            $this->clauses[] = self::resolveForAliasName("year({$queryString})", $aliasName);
            $this->increaseClausesCountByOne();
        }

        public function resolveIdClause($modelClassName, $tableAliasName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($tableAliasName)');
            if(!isset($this->idTableAliasesAndModelClassNames[$tableAliasName]))
            {
                $this->idTableAliasesAndModelClassNames[$tableAliasName] = $modelClassName;
                $this->addClause($tableAliasName, 'id', $tableAliasName . 'id');
            }
        }

        public static function resolveForAliasName($clause, $aliasName = null)
        {
            assert('is_string($clause)');
            assert('is_string($aliasName) || $aliasName == null');
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            return $clause;
        }
    }
?>
