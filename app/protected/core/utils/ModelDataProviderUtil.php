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
     * A helper class for assisting the data providers in building query parts for fetching data.
     *
     */
    class ModelDataProviderUtil
    {

        /**
         * If the $onTableAliasName is used (not null):
         * Special use of sort attribute resolution. If you are resolving a sort attribute against a relation then
         * the joins must utilize a left join in the case of casting up.  Does not support when the attribute is a
         * relation itself as this expects any relation processing to be done before this is called.
         *
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @return string
         * @throws NotSupportedException
         */
        public static function resolveSortAttributeColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                              $modelAttributeToDataProviderAdapter,
                                                              RedBeanModelJoinTablesQueryAdapter
                                                              $joinTablesAdapter,
                                                              $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $tableAliasName             = $builder->resolveJoins($onTableAliasName, self::resolveCanUseFromJoins($onTableAliasName));
                $resolvedSortColumnName     = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                $tableAliasName             = $builder->resolveJoins($onTableAliasName, self::resolveCanUseFromJoins($onTableAliasName));
                $resolvedSortColumnName     = $modelAttributeToDataProviderAdapter->getColumnName();
            }
            return self::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
        }

        protected static function resolveCanUseFromJoins($onTableAliasName)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if($onTableAliasName != null)
            {
                return false;
            }
            return true;
        }

        protected static function resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($resolvedSortColumnName)');
            $sort  = DatabaseCompatibilityUtil::quoteString($tableAliasName);
            $sort .= '.';
            $sort .= DatabaseCompatibilityUtil::quoteString($resolvedSortColumnName);
            return $sort;
        }

        /**
         * Override from RedBeanModelDataProvider to support multiple
         * where clauses for the same attribute and operatorTypes
         * @param $modelClassName
         * @param array $metadata - array expected to have clauses and structure elements
         * @param $joinTablesAdapter
         * @return string
         */
        public static function makeWhere($modelClassName, array $metadata, $joinTablesAdapter)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            if (empty($metadata))
            {
                return;
            }
            $where = array();
            foreach ($metadata['clauses'] as $key => $clauseInformation)
            {
                static::processMetadataClause($modelClassName, $key, $clauseInformation, $where, $joinTablesAdapter);
            }
            if (count($where)> 0)
            {
                return strtr(strtolower($metadata["structure"]), $where);
            }
            return;
        }

        /**
         * @param string $modelClassName
         * @param integer $clausePosition
         * @param array $clauseInformation
         * @param array $where
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @throws NotSupportedException
         */
        protected static function processMetadataClause($modelClassName, $clausePosition, $clauseInformation, & $where, & $joinTablesAdapter)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_int($clausePosition)');
            if (isset($clauseInformation['relatedModelData']))
            {
                static::processMetadataContainingRelatedModelDataClause($modelClassName,
                    $clausePosition,
                    $clauseInformation,
                    $where,
                    $joinTablesAdapter);
            }
            elseif (isset($clauseInformation['concatedAttributeNames']))
            {
                if (isset($clauseInformation['relatedAttributeName']) &&
                   $clauseInformation['relatedAttributeName'] != null)
                {
                    throw new NotSupportedException();
                }
                $tableAliasAndColumnNames = self::makeTableAliasAndColumnNamesForNonRelatedConcatedAttributes(
                                            $modelClassName, $clauseInformation['concatedAttributeNames'],
                                            $joinTablesAdapter);
                self::addWherePartByClauseInformationForConcatedAttributes($clauseInformation['operatorType'],
                                            $clauseInformation['value'], $where, $clausePosition,
                                            $tableAliasAndColumnNames);
            }
            else
            {
                $modelAttributeToDataProviderAdapter =  new RedBeanModelAttributeToDataProviderAdapter(
                                                        $modelClassName,
                                                        $clauseInformation['attributeName'],
                                                        ArrayUtil::getArrayValue($clauseInformation, 'relatedAttributeName'));
                $builder = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter, true);
                $builder->resolveJoinsAndBuildWhere(    $clauseInformation['operatorType'],
                                                        $clauseInformation['value'], $clausePosition, $where);
            }
        }

        /**
         * @param string $modelClassName
         * @param integer $clausePosition
         * @param array $clauseInformation
         * @param array $where
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         */
        protected static function processMetadataContainingRelatedModelDataClause($modelClassName,
                                                                                  $clausePosition,
                                                                                  $clauseInformation,
                                                                                  & $where,
                                                                                  $joinTablesAdapter)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_int($clausePosition)');
            assert('is_array($clauseInformation["relatedModelData"]) && count($clauseInformation["relatedModelData"]) > 0');


            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                   $modelClassName,
                                                   $clauseInformation['attributeName'],
                                                   $clauseInformation['relatedModelData']['attributeName']);
            $builder                             = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter,
                                                                                $joinTablesAdapter, true);
            $builder->resolveJoins(null, false);
            $relationModelClassName = $modelAttributeToDataProviderAdapter->getRelationModelClassName();
            //if there is no more relatedModelData then we know this is the end of the nested information.
            if (isset($clauseInformation['relatedModelData']['relatedModelData']))
            {
                return static::processMetadataClause($relationModelClassName, $clausePosition,
                                                     $clauseInformation['relatedModelData'], $where, $joinTablesAdapter);
            }
            //Supporting the use of relatedAttributeName. Alternatively you can use relatedModelData to produce the same results.
            if (isset($clauseInformation['relatedModelData']['relatedAttributeName']))
            {
                //Two adapters are created, because the first adapter gives us the proper modelClassName
                //to use when using relatedAttributeName
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                           $relationModelClassName,
                                                           $clauseInformation['relatedModelData']['attributeName'],
                                                           $clauseInformation['relatedModelData']['relatedAttributeName']);
                $builder = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter, true);
            }
            $builder->resolveJoinsAndBuildWhere(
                $clauseInformation['relatedModelData']['operatorType'],
                $clauseInformation['relatedModelData']['value'], $clausePosition, $where);
        }

        /**
         * @param string $modelClassName
         * @param array $concatedAttributeNames
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return array
         */
        protected static function makeTableAliasAndColumnNamesForNonRelatedConcatedAttributes( $modelClassName,
                                                                                    $concatedAttributeNames,
                                                                                    $joinTablesAdapter)
        {
            assert('is_string($modelClassName)');
            assert('is_array($concatedAttributeNames) && count($concatedAttributeNames) == 2');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            $tableAliasAndColumnNames = array();
            foreach ($concatedAttributeNames as $attributeName)
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                       $modelClassName, $attributeName);
                $builder                             = new ModelWhereAndJoinBuilder(
                                                       $modelAttributeToDataProviderAdapter, $joinTablesAdapter, true);
                $tableAliasName                      = $builder->resolveJoins();
                $tableAliasAndColumnNames[]          = array($tableAliasName,
                                                       $modelAttributeToDataProviderAdapter->getColumnName());
            }
            return $tableAliasAndColumnNames;
        }

        /**
         * Add a sql string to the where array base on the $operatorType, $value and $tableAliasAndColumnNames concated
         * together.  How the sql string is built depends on if the value is a string or not.
         * @param string $operatorType
         * @param mixed $value
         * @param array $where
         * @param integer $whereKey
         * @param array $tableAliasAndColumnNames
         */
        protected static function addWherePartByClauseInformationForConcatedAttributes($operatorType, $value, &$where,
                                                                    $whereKey, $tableAliasAndColumnNames)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_array($tableAliasAndColumnNames) && count($tableAliasAndColumnNames) == 2');
            $quote = DatabaseCompatibilityUtil::getQuote();
            if (is_string($value) || (is_array($value) && count($value) > 0) || $value !== null)
            {
                $first            = ModelJoinBuilder::makeColumnNameWithTableAlias(
                                    $tableAliasAndColumnNames[0][0], $tableAliasAndColumnNames[0][1]);
                $second           = ModelJoinBuilder::makeColumnNameWithTableAlias(
                                    $tableAliasAndColumnNames[1][0], $tableAliasAndColumnNames[1][1]);
                $concatedSqlPart  = DatabaseCompatibilityUtil::concat(array($first, '\' \'', $second));
                $where[$whereKey] = "($concatedSqlPart " . // Not Coding Standard
                                    DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . ")";
            }
        }
    }
?>