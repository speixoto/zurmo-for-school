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
     * Builder for creating where clauses and making sure the appropriate joins are added to facilitate these clauses.
      */
    class ModelWhereAndJoinBuilder extends ModelJoinBuilder
    {
        /**
         * @param $operatorType
         * @param $value
         * @param $clausePosition
         * @param $where
         */
        public function resolveJoinsAndBuildWhere($operatorType, $value, & $clausePosition, & $where,
                                                  $onTableAliasName = null)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if(!$this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $tableAliasName = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                self::addWherePartByClauseInformation($operatorType, $value, $where, $clausePosition, $tableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->getColumnName());
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelationTypeAHasOneVariant() &&
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttribute() == 'id')
            {
                self::addWherePartByClauseInformation($operatorType, $value, $where, $clausePosition,
                                                      $this->resolveJoinsForRelatedId($onTableAliasName),
                                                      $this->modelAttributeToDataProviderAdapter->getColumnName());
            }
            else
            {
                $this->buildJoinAndWhereForRelatedAttribute($operatorType, $value, $clausePosition, $where,
                                                            $onTableAliasName);
            }
        }

        /**
         * Given a related attribute on a model, build the join and where sql string information.
         * @param $operatorType
         * @param $value
         * @param $whereKey
         * @param $where
         */
        protected function buildJoinAndWhereForRelatedAttribute($operatorType, $value, $whereKey, &$where,
                                                                $onTableAliasName = null)
        {
            assert('is_string($operatorType)');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $relationWhere                          = array();
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                $relationAttributeTableAliasName    = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                self::addWherePartByClauseInformation($operatorType, $value, $relationWhere, 1,
                                                      $relationAttributeTableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->resolveManyToManyColumnName());
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelatedAttributeRelation() &&
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationType() == RedBeanModel::HAS_MANY)
            {
                $relationAttributeTableAliasName    = $this->resolveOnlyAttributeJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                $this->buildWhereForRelatedAttributeThatIsItselfAHasManyRelation(
                                                      $relationAttributeTableAliasName,
                                                      $operatorType,
                                                      $value,
                                                      $relationWhere,
                                                      1);
            }
            else
            {
                $relationAttributeTableAliasName    = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                self::addWherePartByClauseInformation($operatorType,
                                                      $value,
                                                      $relationWhere,
                                                      1,
                                                      $relationAttributeTableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName());
            }
            $where[$whereKey] = strtr('1', $relationWhere);
        }

        /**
         * Given a related attribute on a model and the related attribute is a has_many relation,
         * build the join and where sql string information.
         * @param $onTableAliasName
         * @param $operatorType
         * @param $value
         * @param $where
         * @param $whereKey
         * @throws NotSupportedException
         */
        protected function buildWhereForRelatedAttributeThatIsItselfAHasManyRelation($onTableAliasName,
                                                                                     $operatorType,
                                                                                     $value,
                                                                                     & $where,
                                                                                     $whereKey
        )
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($operatorType)');
            assert('is_array($value) && count($value) > 0');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            $relationAttributeModelClassName = $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationModelClassName();
            if ($relationAttributeModelClassName != 'CustomFieldValue' || $operatorType != 'oneOf')
            {
                //Until we can add a third parameter to the search adapter metadata, we have to assume we are only doing
                //this for CustomFieldValue searches. Below we have $joinColumnName, since we don't have any other way
                //of ascertaining this information for now.

                //only support oneOf for the moment.  Once we add allOf, need to have an alternative sub-query
                //below that uses if/else logic to compare count against how many possibles. then return 1 or 0.
                throw new NotSupportedException();
            }
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $tableAliasName                  = $relationAttributeTableName;
            $joinColumnName                  = 'value';
            $relationColumnName              = self::resolveForeignKey(RedBeanModel::getTableName(
                                               $this->modelAttributeToDataProviderAdapter->
                                                   getRelatedAttributeModelClassName()));
            $quote                           = DatabaseCompatibilityUtil::getQuote();
            $where[$whereKey]                = "(1 = (select 1 from $quote$relationAttributeTableName$quote $tableAliasName " . // Not Coding Standard
                                               "where $quote$tableAliasName$quote.$quote$relationColumnName$quote = " . // Not Coding Standard
                                               "$quote$onTableAliasName$quote.id " . // Not Coding Standard
                                               "and $quote$tableAliasName$quote.$quote$joinColumnName$quote " . // Not Coding Standard
                                               DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . " limit 1))";
        }

        protected function resolveJoinsForRelatedId($onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() && $onTableAliasName == null)
            {
                return $this->addFromJoinsForAttributeThatIsCastedUp();
            }
            elseif($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() &&
                   $onTableAliasName != null)
            {
                return $this->addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName);
            }
            else
            {
                return $this->resolveOnTableAliasName($onTableAliasName);
                //return $this->modelAttributeToDataProviderAdapter->getModelTableName();
            }
        }

        /**
         * Add a sql string to the where array. How the sql string is built depends on if the value is a string or not.
         * @param $operatorType
         * @param $value
         * @param $where
         * @param $whereKey
         * @param $tableAliasName
         * @param $columnName
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

        //todo: test because i am not sure we need this always. needed this for owner part of Conversation query where there was no related attribute, otherwise it didn't work right.
        //todo: need an actual test just on owner for example. and run more search tests to make sure this is ok.
        //todo: the way we have this reporting can't use this builder override since you dont always have a related attribute but that doesn't necessarily mean anything
        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveLeftJoinsForARelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
            }
            return $onTableAliasName;
        }
    }
?>