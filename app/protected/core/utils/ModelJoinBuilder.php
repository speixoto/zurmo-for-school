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
     * Base Builder for creating joins.
     */
    class ModelJoinBuilder
    {
        /**
         * @var RedBeanModelAttributeToDataProviderAdapter
         */
        protected $modelAttributeToDataProviderAdapter;

        /**
         * @var RedBeanModelJoinTablesQueryAdapter
         */
        protected $joinTablesAdapter;

        /**
         * @param string $tableAliasName
         * @param string $columnName
         * @return string
         */
        public static function makeColumnNameWithTableAlias($tableAliasName, $columnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            $quote = DatabaseCompatibilityUtil::getQuote();
            return $quote . $tableAliasName . $quote . '.' . $quote . $columnName. $quote;
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         */
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
         * @return string
         * @throws NotSupportedException
         */
        public function resolveShouldAddFromTable()
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

        /**
         * This method exists for scenarios where an attribute is casted up but because it is on a relation
         * that is joined with a left join, the cast up must use left joins as well since a from table, table will not
         * work properly.
         * @see resolveShouldAddFromTable
         * @return string
         * @throws NotSupportedException
         */
        public function resolveShouldAddLeftTable($onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            //If the attribute table is the same as the model table then there is nothing to add.
            if (!$this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel())
            {
                if($onTableAliasName != null)
                {
                    return $onTableAliasName;
                }
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
                        $onTableAliasName =     $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                                $castedUpAttributeTableName,
                                                self::resolveForeignKey($castedUpAttributeTableName),
                                                $onTableAliasName,
                                                $resolvedTableJoinIdName);
                    }
                }
            }
            //Add left table if it is not already added
            if (!$modelClassName::getCanHaveBean())
            {
                if (!$castedDownModelClassName::getCanHaveBean())
                {
                    throw new NotSupportedException();
                }
                $modelClassName = $castedDownModelClassName;
            }
            $tableAliasName =   $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                $attributeTableName,
                                self::resolveForeignKey($attributeTableName),
                                $onTableAliasName,
                                $modelClassName::getTableName($modelClassName));
            return $tableAliasName;
        }

        /**
         * @return null|string
         */
        public function resolveJoinsForRelatedAttribute($onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if($onTableAliasName == null)
            {
                $onTableAliasName = $this->resolveShouldAddFromTable();
            }
            if($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                return $this->resolveJoinForManyToManyRelatedAttribute();
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
            if ($this->modelAttributeToDataProviderAdapter->hasRelatedAttribute() &&
                $this->modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName() !=
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
         * @return null|string
         */
        protected function resolveJoinForManyToManyRelatedAttribute()
        {
            $relationTableName               = $this->modelAttributeToDataProviderAdapter->getRelationTableName();
            $onTableAliasName                = $this->resolveShouldAddFromTable();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $this->modelAttributeToDataProviderAdapter->getManyToManyTableName(),
                "id",
                $this->resolveShouldAddFromTable(),
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
























        public function resolveJoins($onTableAliasName = null, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            assert('is_bool($canUseFromJoins)');
            $onTableAliasName = $this->resolveOnTableAliasName($onTableAliasName);
            $onTableAliasName = $this->resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins);
            if($this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                       $this->modelAttributeToDataProviderAdapter->
                                                       getRelationModelClassNameThatCanHaveATable(),
                                                       $this->modelAttributeToDataProviderAdapter->
                                                       getRelatedAttribute());
                $builder                             = new ModelJoinBuilder($modelAttributeToDataProviderAdapter,
                                                       $this->joinTablesAdapter);
                $onTableAliasName                    = $builder->resolveJoinsForAttribute($onTableAliasName, false);
            }
            return $onTableAliasName;
        }

        protected function resolveOnTableAliasName($onTableAliasName = null)
        {
            if($onTableAliasName == null)
            {
                $onTableAliasName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            }
            return $onTableAliasName;
        }

        protected function resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel())
            {
                return $this->resolveJoinsForAttributeOnDifferentModel($onTableAliasName, $canUseFromJoins);
            }
            elseif($this->modelAttributeToDataProviderAdapter->isRelation())
            {
                return $this->resolveJoinsForAttributeOnSameModelThatIsARelation($onTableAliasName);
            }
            else
            {
                return $this->resolveJoinsForAttributeOnSameModelThatIsNotARelation($onTableAliasName);
            }
        }

        protected function resolveJoinsForAttributeOnDifferentModel($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if($this->modelAttributeToDataProviderAdapter->isRelation())
            {
                return $this->resolveJoinsForAttributeOnDifferentModelThatIsARelation($onTableAliasName, $canUseFromJoins);
            }
            else
            {
                return $this->resolveJoinsForAttributeOnDifferentModelThatIsNotARelation($onTableAliasName,
                                                                                         $canUseFromJoins);
            }
        }

        protected function resolveJoinsForAttributeOnSameModelThatIsARelation($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
        }

        protected function resolveJoinsForAttributeOnSameModelThatIsNotARelation($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            return $onTableAliasName;
        }

        protected function resolveJoinsForAttributeOnDifferentModelThatIsARelation($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            //todo: this clause can resolve into its own method. could share same internal signature as the next method, assumming hte internals are rewritten to be nammed properly.
            if($canUseFromJoins)
            {
                $onTableAliasName = $this->addFromJoinsForNonRelationAttribute($onTableAliasName); //todo: make its own method since this is relation not nonRelation
            }
            else
            {
                $onTableAliasName = $this->addLeftJoinsForNonRelationAttribute($onTableAliasName); //todo: make its own method since this is relation not nonRelation
            }
            return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
        }

        protected function
                  resolveJoinsForAttributeOnDifferentModelThatIsNotARelation($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if($canUseFromJoins)
            {
                return $this->addFromJoinsForNonRelationAttribute($onTableAliasName);
            }
            else
            {
                return $this->addLeftJoinsForNonRelationAttribute($onTableAliasName);
            }
        }

        protected function addFromJoinsForNonRelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addFromJoinsForNonRelationAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addFromJoinsForNonRelationAttributeThatIsCastedUp();
            }
        }

        protected function addLeftJoinsForNonRelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addLeftJoinsForNonRelationAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addLeftJoinsForNonRelationAttributeThatIsCastedUp($onTableAliasName);
            }
        }

        protected function addFromJoinsForNonRelationAttributeThatIsMixedIn($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $modelClassName     = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                $onTableAliasName = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                    $attributeTableName,
                    self::resolveForeignKey($attributeTableName),
                    $modelClassName::getTableName($modelClassName));
            }
            return $onTableAliasName;
        }

        protected function addFromJoinsForNonRelationAttributeThatIsCastedUp()
        {
            $modelClassName     = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $tableAliasName     = $attributeTableName;
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) !=
                   $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName() &&
                   get_parent_class($modelClassName) != 'RedBeanModel')
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
                            $onTableAliasName = $castedDownModelClassName::getTableName($castedDownModelClassName);
                        }
                        elseif ($castedDownFurtherModelClassName::getCanHaveBean())
                        {
                            $onTableAliasName = $castedDownModelClassName::getTableName($castedDownFurtherModelClassName);
                        }
                        else
                        {
                            throw new NotSupportedException();
                        }
                        $onTableAliasName = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                            $castedUpAttributeTableName,
                            self::resolveForeignKey($castedUpAttributeTableName),
                            $onTableAliasName);
                    }
                }
            }
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                $modelClassName   = static::resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName);
                $tableAliasName   = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                                    $attributeTableName,
                                    self::resolveForeignKey($attributeTableName),
                                    $modelClassName::getTableName($modelClassName));
            }
            return $tableAliasName;
        }

        protected function addLeftJoinsForNonRelationAttributeThatIsMixedIn($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $modelClassName     = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            return $this->addLeftJoinForMixedInAttribute($onTableAliasName, $attributeTableName, $modelClassName);
        }

        protected function addLeftJoinForMixedInAttribute($onTableAliasName, $attributeTableName, $modelClassName)
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($attributeTableName)');
            assert('is_string($modelClassName)');
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                $onTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                    $attributeTableName,
                    self::resolveForeignKey($attributeTableName),
                    $onTableAliasName);
            }
            return $onTableAliasName;
        }

        protected function addLeftJoinsForNonRelationAttributeThatIsCastedUp($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $modelClassName           = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeTableName       = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) !=
                   $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName() &&
                   get_parent_class($modelClassName) != 'RedBeanModel')
            {
                $castedDownFurtherModelClassName = $castedDownModelClassName;
                $castedDownModelClassName        = $modelClassName;
                $modelClassName                  = get_parent_class($modelClassName);
                if ($modelClassName::getCanHaveBean())
                {
                    $castedUpAttributeTableName = $modelClassName::getTableName($modelClassName);
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
                    $onTableAliasName =     $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                        $castedUpAttributeTableName,
                        self::resolveForeignKey($castedUpAttributeTableName),
                        $onTableAliasName,
                        $resolvedTableJoinIdName);
                }
            }
            //Add left table if it is not already added
            $modelClassName   = static::resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName);
            $onTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $attributeTableName,
                self::resolveForeignKey($attributeTableName),
                $onTableAliasName,
                $modelClassName::getTableName($modelClassName));
            return $onTableAliasName;
        }

        protected function addLeftJoinsForARelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                return $this->resolveJoinsForForARelationAttributeThatIsManyToMany($onTableAliasName);
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelationTypeAHasManyVariant())
            {
                return $this->resolveJoinsForForARelationAttributeThatIsAHasManyVariant($onTableAliasName);
                //todo: this is probably needed when not doing reporting.  need way to flag this..
                //HAS_MANY have the potential to produce more than one row per model, so we need
                //to signal the query to be distinct.
                /**
                if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY)
                {
                    $this->joinTablesAdapter->setSelectDistinctToTrue();
                }
                 * **/
            }
            elseif($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_ONE)
            {
                return $this->resolveJoinsForForARelationAttributeThatIsAHasOne($onTableAliasName);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveJoinsForForARelationAttributeThatIsManyToMany($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $relationTableName               = $this->modelAttributeToDataProviderAdapter->getRelationTableName();
            $attributeTableName              = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                               $this->modelAttributeToDataProviderAdapter->getManyToManyTableName(),
                                               "id",
                                               $this->resolveShouldAddFromTable(),
                                               self::resolveForeignKey($attributeTableName));
            //if this is not the id column, then add an additional left join.
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                //todo: $this->joinTablesAdapter->setSelectDistinctToTrue(); //needed when not using reporting... need way to flag this.
                return  $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                        $relationTableName,
                        self::resolveForeignKey($relationTableName),
                        $relationJoiningTableAliasName,
                        'id');

            }
            else
            {
                return $relationJoiningTableAliasName;
            }
        }

        protected function resolveJoinsForForARelationAttributeThatIsAHasManyVariant($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $onTableJoinIdName  = 'id';
            $tableJoinIdName    = self::resolveForeignKey($onTableAliasName);
            $onTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                  $this->modelAttributeToDataProviderAdapter->getRelationTableName(),
                                  $onTableJoinIdName,
                                  $onTableAliasName,
                                  $tableJoinIdName);
            return $onTableAliasName;
        }

        protected function resolveJoinsForForARelationAttributeThatIsAHasOne($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $tableJoinIdName    = 'id';
            $onTableJoinIdName  = $this->modelAttributeToDataProviderAdapter->getColumnName();
            $onTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                  $this->modelAttributeToDataProviderAdapter->getRelationTableName(),
                                  $onTableJoinIdName,
                                  $onTableAliasName,
                                  $tableJoinIdName);
            return $onTableAliasName;
        }

        protected static function resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            if (!$modelClassName::getCanHaveBean())
            {
                if (!$castedDownModelClassName::getCanHaveBean())
                {
                    throw new NotSupportedException();
                }
                return $castedDownModelClassName;
            }
            else
            {
                return $modelClassName;
            }
        }

        /**
         * @param string $idName
         * @return string
         */
        protected static function resolveForeignKey($idName)
        {
            assert('is_string($idName)');
            return $idName . '_id';
        }
    }
?>