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
     * Special Builder for handling read optimization where clause when this is a sub-select clause.
     */
    class ReadOptimizationModelWhereAndJoinBuilder extends ModelWhereAndJoinBuilder
    {
        public function __construct(ReadOptimizationDerivedAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter)
        {
            parent::__construct($modelAttributeToDataProviderAdapter, $joinTablesAdapter);
        }


        public function resolveJoinsAndBuildWhere($operatorType, $value, & $clausePosition, & $where,
                                                  $onTableAliasName = null)
        {
            assert('$operatorType == null');
            assert('$value == null');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $tableAliasName = $this->resolveJoins(
                              $onTableAliasName, ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            $this->addReadOptimizationWhereClause($where, $clausePosition, $tableAliasName);
        }

        protected function addReadOptimizationWhereClause(& $where, $whereKey, $tableAliasName)
        {
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_string($tableAliasName)');
            $q                    = DatabaseCompatibilityUtil::getQuote();
            $columnWithTableAlias = self::makeColumnNameWithTableAlias($tableAliasName,
                                    $this->modelAttributeToDataProviderAdapter->getColumnName());
            $mungeTableName      = ReadPermissionsOptimizationUtil::getMungeTableName($this->modelAttributeToDataProviderAdapter->getModelClassName());
            $mungeIds            = ReadPermissionsOptimizationUtil::getMungeIdsByUser(Yii::app()->user->userModel);
            $whereContent        = $columnWithTableAlias . " " . SQLOperatorUtil::getOperatorByType('equals'). " ";
            $whereContent       .= "(select securable_id from {$q}$mungeTableName{$q} " .
                                   "where {$q}munge_id{$q} in ('" . join("', '", $mungeIds) . "') limit 1)";
            $where[$whereKey]    = $whereContent;
        }
    }
?>