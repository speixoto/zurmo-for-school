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

    class ModelWhereAndJoinBuilder extends ModelJoinBuilder
    {
        /**
         * Given a non-related attribute on a model, build the join and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        public function buildJoinAndWhereForNonRelatedAttribute($operatorType, $value, $whereKey, & $where)
        {
            assert('is_string($operatorType)');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            $tableAliasName = $this->resolveShouldAddFromTable();
            self::addWherePartByClauseInformation($operatorType, $value, $where, $whereKey, $tableAliasName,
                                                  $this->modelAttributeToDataProviderAdapter->getColumnName());
        }

        public function resolveWhenIdAndBuildJoinAndWhereForRelatedAttribute($operatorType, $value, & $clausePosition, & $where)
        {
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() == 'id')
            {
                $this->buildJoinAndWhereForRelatedId(
                    $operatorType,
                    $value,
                    $clausePosition,
                    $where);
            }
            else
            {
                self::buildJoinAndWhereForRelatedAttribute(
                    $operatorType,
                    $value,
                    $clausePosition,
                    $where);
            }
        }

        /**
         * Given a related attribute on a model, build the jion and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected function buildJoinAndWhereForRelatedAttribute($operatorType, $value, $whereKey, &$where)
        {
            assert('is_string($operatorType)');
            assert('$this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                $this->buildJoinAndWhereForManyToManyRelatedAttribute(
                    $operatorType,
                    $value,
                    $whereKey,

                    $where);
            }
            else
            {
                $relationAttributeTableAliasName     = $this->resolveJoinsForRelatedAttribute();
                $relationWhere                       = array();
                if ($this->modelAttributeToDataProviderAdapter->isRelatedAttributeRelation() &&
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationType() == RedBeanModel::HAS_MANY)
                {
                    $this->buildWhereForRelatedAttributeThatIsItselfAHasManyRelation(
                        $relationAttributeTableAliasName,
                        $operatorType,
                        $value,
                        $relationWhere,
                        1);
                }
                else
                {
                    self::addWherePartByClauseInformation($operatorType,
                        $value,
                        $relationWhere,
                        1,
                        $relationAttributeTableAliasName,
                        $this->modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName());
                }
                $where[$whereKey] = strtr('1', $relationWhere);
            }
        }

        protected function buildWhereForRelatedAttributeThatIsItselfAHasManyRelation(
                                                                                            $relationAttributeTableAliasName,
                                                                                            $operatorType,
                                                                                            $value,
                                                                                            & $where,
                                                                                            $whereKey
        )
        {
            assert('is_string($relationAttributeTableAliasName)');
            assert('is_string($operatorType)');
            assert('is_array($value) && count($value) > 0');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            $relationAttributeName           = $this->modelAttributeToDataProviderAdapter->getRelatedAttribute();
            $relationAttributeModelClassName = $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationModelClassName();
            if ($relationAttributeModelClassName != 'CustomFieldValue')
            {
                //Until we can add a third parameter to the search adapter metadata, we have to assume we are only doing
                //this for CustomFieldValue searches. Below we have $joinColumnName, since we don't have any other way
                //of ascertaining this information for now.
                throw new NotSupportedException();
            }
            if ($operatorType != 'oneOf')
            {
                //only support oneOf for the moment.  Once we add allOf, need to have an alternative sub-query
                //below that uses if/else logic to compare count against how many possibles. then return 1 or 0.
            }
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $tableAliasName                  = $relationAttributeTableName;
            $joinColumnName                  = 'value';
            $relationColumnName              = self::resolveForeignKey(RedBeanModel::getTableName($this->modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName()));
            $quote                           = DatabaseCompatibilityUtil::getQuote();
            $where[$whereKey]   = "(1 = (select 1 from $quote$relationAttributeTableName$quote $tableAliasName " . // Not Coding Standard
                "where $quote$tableAliasName$quote.$quote$relationColumnName$quote = " . // Not Coding Standard
                "$quote$relationAttributeTableAliasName$quote.id " . // Not Coding Standard
                "and $quote$tableAliasName$quote.$quote$joinColumnName$quote " . // Not Coding Standard
                DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . " limit 1))";
        }

        /**
         * When the attributeName is 'id', this method determines if we need to join any tables or we can just
         * add where clauses on the column in the base table that corresponds to the id.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         *
         */
        protected function buildJoinAndWhereForRelatedId($operatorType,
                                                                $value,
                                                                $whereKey,
                                                                &$where)
        {
            assert('is_string($operatorType)');
            assert('$this->modelAttributeToDataProviderAdapter->getRelatedAttribute() == "id"');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            //Is the relation type HAS_ONE or HAS_MANY_BELONGS_TO
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_ONE ||
                $this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                $tableAliasName = $this->resolveShouldAddFromTable();
                self::addWherePartByClauseInformation(  $operatorType,
                    $value,
                    $where, $whereKey, $tableAliasName,
                    $this->modelAttributeToDataProviderAdapter->getColumnName());
            }
            elseif ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                $this->buildJoinAndWhereForManyToManyRelatedAttribute( $operatorType, $value,
                    $whereKey, $where);
            }
            else
            {
                $this->buildJoinAndWhereForRelatedAttribute($operatorType, $value,
                    $whereKey, $where);
            }
        }

        /**
         * Given a RedBeanModel::MANY_MANY related attribute on a model, build the join and where sql string information.
         * In this scenario with a many-to-many relation, you only need to join the joining table, since this method
         * currently only supports where the relatedAttributeName = 'id'.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected function buildJoinAndWhereForManyToManyRelatedAttribute($operatorType, $value, $whereKey, & $where)
        {
            assert('is_string($operatorType)');
            assert('$this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            assert('$this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY');
            $relationAttributeTableAliasName    = $this->resolveJoinsForRelatedAttribute();
            $relationWhere                      = array();
            self::addWherePartByClauseInformation($operatorType, $value, $relationWhere, 1,
                  $relationAttributeTableAliasName,
                  $this->modelAttributeToDataProviderAdapter->resolveManyToManyColumnName());
            $where[$whereKey] = strtr('1', $relationWhere);
        }

        /**
         * Add a sql string to the where array base on the $operatorType, $value, $tableAliasName, and $columnName
         * parameters.  How the sql string is built depends on if the value is a string or not.
         */
        protected static function addWherePartByClauseInformation(  $operatorType, $value, &$where,
                                                                    $whereKey, $tableAliasName, $columnName)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            if (is_string($value) || (is_array($value) && count($value) > 0) || $value !== null  ||
                ($value === null && SQLOperatorUtil::doesOperatorTypeAllowNullValues($operatorType)))
            {
                $where[$whereKey] = "(" . self::makeColumnNameWithTableAlias($tableAliasName, $columnName) . " " .
                                    DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType,
                                    $value) . ")";
            }
        }
    }
?>