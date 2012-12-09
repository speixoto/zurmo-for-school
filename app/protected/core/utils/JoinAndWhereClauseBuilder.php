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

    class JoinAndWhereClauseBuilder
    {
        protected $modelAttributeToDataProviderAdapter;

        protected $joinTablesAdapter;

        public function __construct(RedBeanModelAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter)
        {
            $this->modelAttributeToDataProviderAdapter = $modelAttributeToDataProviderAdapter;
            $this->joinTablesAdapter                   = $joinTablesAdapter;
        }

        /**
         * For both non related and related attributes, this method resolves whether a from join is needed.  This occurs
         * for example if a model attribute is castedUp. And that attribute is a relation that needs to be joined in
         * order to search.  Since that attribute is castedUp, the castedUp model needs to be from joined first.  This
         * also applies if the attribute is not a relation and just a member on the castedUp model. In that scenario,
         * the castedUp model also needs to be joined.
         *
         * This method assumes if the attribute is not on the base model, that it is casted up not down from it.
         */
        public function resolveShouldAddFromTableAndGetAliasName()
        {
            //If the attribute table is the same as the model table then there is nothing to add.
            if (!$this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel())
            {
                return $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            }
            $modelClassName     = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $tableAliasName     = $attributeTableName;
            //If attribute is casted up
            if(!$this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                $castedDownModelClassName   = $modelClassName;
                while (get_parent_class($modelClassName) !=
                    $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName())
                {
                    $castedDownFurtherModelClassName = $castedDownModelClassName;
                    $castedDownModelClassName        = $modelClassName;
                    $modelClassName                  = get_parent_class($modelClassName);
                    if ($modelClassName::getCanHaveBean())
                    {
                        $castedUpAttributeTableName = $modelClassName::getTableName($modelClassName);
                        if (!$this->joinTablesAdapter->isTableInFromTables($castedUpAttributeTableName))
                        {
                            if ($castedDownModelClassName::getCanHaveBean())
                            {
                                $resolvedTableJoinIdName = $castedDownModelClassName::getTableName($castedDownModelClassName);
                            }
                            elseif ($castedDownFurtherModelClassName::getCanHaveBean())
                            {
                                $resolvedTableJoinIdName = $castedDownModelClassName::getTableName($castedDownFurtherModelClassName);
                            }
                            else
                            {
                                throw new NotSupportedException();
                            }
                            $this->joinTablesAdapter->addFromTableAndGetAliasName(
                                $castedUpAttributeTableName,
                                self::resolveForeignKey($castedUpAttributeTableName),
                                $resolvedTableJoinIdName);
                        }
                    }
                }
            }
            //Add from table if it is not already added
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                if (!$modelClassName::getCanHaveBean())
                {
                    if (!$castedDownModelClassName::getCanHaveBean())
                    {
                        throw new NotSupportedException();
                    }
                    $modelClassName = $castedDownModelClassName;
                }
                $tableAliasName             = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                    $attributeTableName,
                    self::resolveForeignKey($attributeTableName),
                    $modelClassName::getTableName($modelClassName));
            }
            return $tableAliasName;
        }

        public function resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName()
        {
            $onTableAliasName = $this->resolveShouldAddFromTableAndGetAliasName();
            if($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY  ||
                $this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO ||
                $this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_ONE_BELONGS_TO)
            {
                $onTableJoinIdName  = 'id';
                $tableJoinIdName    = self::resolveForeignKey($onTableAliasName);
                //HAS_MANY have the potential to produce more than one row per model, so we need
                //to signal the query to be distinct.
                if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY)
                {
                    $this->joinTablesAdapter->setSelectDistinctToTrue();
                }
            }
            else
            {
                $tableJoinIdName    = 'id';
                $onTableJoinIdName  = $this->modelAttributeToDataProviderAdapter->getColumnName();
            }
            if (!$this->modelAttributeToDataProviderAdapter->canRelationHaveTable())
            {
                $relationTableAliasName          = $onTableAliasName;
            }
            else
            {
                $relationTableAliasName          = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                    $this->modelAttributeToDataProviderAdapter->getRelationTableName(),
                    $onTableJoinIdName,
                    $onTableAliasName,
                    $tableJoinIdName);
            }
            $relationAttributeTableAliasName = $relationTableAliasName;
            //the second left join check being performed is if you
            //are in a contact filtering on related account email as an example.
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName() !=
                $this->modelAttributeToDataProviderAdapter->getRelationModelClassName())
            {
                $relationAttributeTableName  = $this->modelAttributeToDataProviderAdapter->getRelatedAttributeTableName();
                //Handling special scenario for casted down Person.  Todo: Automatically determine a
                //casted down scenario instead of specifically looking for Person.
                if ($this->modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName() == 'Person')
                {
                    $onTableJoinIdName = self::resolveForeignKey($relationAttributeTableName);
                }
                //An example of this if if you are searching on an account's industry value.  Industry is related from
                //account, but the value is actually on the parent class of OwnedCustomField which is CustomField.
                //Therefore the JoinId is going to be structured like this.
                elseif (get_parent_class($this->modelAttributeToDataProviderAdapter->getRelationModelClassName()) ==
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName())
                {
                    $onTableJoinIdName = $this->modelAttributeToDataProviderAdapter->getColumnName();
                }
                else
                {
                    $onTableJoinIdName = "{$this->modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName()}" .
                        "_" . self::resolveForeignKey($relationAttributeTableName);
                }
                $relationAttributeTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                    $relationAttributeTableName,
                    $onTableJoinIdName,
                    $relationTableAliasName);
            }
            return $relationAttributeTableAliasName;
        }

        /**
         * Given a non-related attribute on a model, build the join and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        public function buildJoinAndWhereForNonRelatedAttribute(
                                                                          $operatorType,
                                                                          $value,
                                                                          $whereKey,
                                                                          & $where)
        {
            assert('is_string($operatorType)');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            $tableAliasName = $this->resolveShouldAddFromTableAndGetAliasName();
            self::addWherePartByClauseInformation($operatorType, $value,
                $where, $whereKey, $tableAliasName,
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

        public function resolveRelatedAttributeJoinAndGetRelationModelClassName()
        {
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                $this->resolveJoinForManyToManyRelatedAttribute();
            }
            else
            {
                $this->resolveJoinsForRelatedAttribute();
            }
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                   $this->modelAttributeToDataProviderAdapter->getModelClassName(),
                                                   $this->modelAttributeToDataProviderAdapter->getAttribute());
            return $modelAttributeToDataProviderAdapter->getRelationModelClassName();
        }

        protected function resolveJoinForManyToManyRelatedAttribute()
        {
            assert('$this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
            $relationTableName               = $this->modelAttributeToDataProviderAdapter->getRelationTableName();
            $onTableAliasName                = $this->resolveShouldAddFromTableAndGetAliasName();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $this->modelAttributeToDataProviderAdapter->getManyToManyTableName(),
                "id",
                $this->resolveShouldAddFromTableAndGetAliasName(),
                self::resolveForeignKey($this->modelAttributeToDataProviderAdapter->getAttributeTableName()));
            //if this is not the id column, then add an additional left join.
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                $this->joinTablesAdapter->setSelectDistinctToTrue();
                $relationTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                    $relationTableName,
                    self::resolveForeignKey($relationTableName),
                    $relationJoiningTableAliasName,
                    'id');
            }
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                return $relationTableAliasName;
            }
            else
            {
                return $relationJoiningTableAliasName;
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
                $tableAliasName = $this->resolveShouldAddFromTableAndGetAliasName();
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
            $relationAttributeTableAliasName    = $this->resolveJoinForManyToManyRelatedAttribute();
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

        public static function makeColumnNameWithTableAlias($tableAliasName, $columnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            $quote = DatabaseCompatibilityUtil::getQuote();
            return $quote . $tableAliasName . $quote . '.' . $quote . $columnName. $quote;
        }

        protected static function resolveForeignKey($idName)
        {
            assert('is_string($idName)');
            return $idName . '_id';
        }
    }
?>