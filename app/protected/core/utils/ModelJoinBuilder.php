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
         * @var bool
         */
        protected $setDistinct;

        /**
         * @var string
         */
        protected $resolvedOnTableAliasName;

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
         * @param string $idName
         * @return string
         */
        protected static function resolveForeignKey($idName)
        {
            assert('is_string($idName)');
            return $idName . '_id';
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param boolean $setDistinct
         */
        public function __construct(RedBeanModelAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter,
                                    $setDistinct = false)
        {
            $this->modelAttributeToDataProviderAdapter = $modelAttributeToDataProviderAdapter;
            $this->joinTablesAdapter                   = $joinTablesAdapter;
            $this->setDistinct                         = $setDistinct;
        }

        public function resolveJoins($onTableAliasName = null, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            assert('is_bool($canUseFromJoins)');
            $onTableAliasName = $this->resolveOnTableAliasName($onTableAliasName);
            $onTableAliasName = $this->resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins);
            $this->resolvedOnTableAliasName = $onTableAliasName;
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

        public function resolveOnlyAttributeJoins($onTableAliasName = null, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            assert('is_bool($canUseFromJoins)');
            $onTableAliasName = $this->resolveOnTableAliasName($onTableAliasName);
            $onTableAliasName = $this->resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins);
            $this->resolvedOnTableAliasName = $onTableAliasName;
            return $onTableAliasName;
        }

        protected function resolveOnTableAliasName($onTableAliasName = null)
        {
            if($onTableAliasName == null)
            {
                $onTableAliasName = $this->resolveOnTableAliasNameForDerivedRelationViaCastedUpModel();
            }
            return $onTableAliasName;
        }

        private function resolveOnTableAliasNameForDerivedRelationViaCastedUpModel()
        {
            if($this->modelAttributeToDataProviderAdapter->isAttributeDerivedRelationViaCastedUpModel())
            {
                $onTableAliasName = $this->modelAttributeToDataProviderAdapter->getModelTableName();
            }
            else
            {
                $onTableAliasName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            }
            return $onTableAliasName;
        }

        protected function resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeDerivedRelationViaCastedUpModel())
            {
                return $this->resolveJoinsForDerivedRelationViaCastedUpModel($onTableAliasName, $canUseFromJoins);
            }
            elseif($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel())
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

        protected function resolveJoinsForDerivedRelationViaCastedUpModel($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            //First cast up
            $onTableAliasName        = $this->resolveJoinsForDerivedRelationViaCastedUpModelThatIsCastedUp(
                $onTableAliasName, $canUseFromJoins);
            //Second build relation across to the opposing model
            $onTableAliasName        = $this->resolveJoinsForDerivedRelationViaCastedUpModelThatIsManyToMany(
                $onTableAliasName);
            //Third cast down if necessary
            if($this->modelAttributeToDataProviderAdapter->isDerivedRelationViaCastedUpModelDifferentThanOpposingModelClassName())
            {
                $opposingRelationModelClassName  = $this->modelAttributeToDataProviderAdapter->
                    getOpposingRelationModelClassName();
                $derivedRelationModelClassName   = $this->modelAttributeToDataProviderAdapter->
                    getDerivedRelationViaCastedUpModelClassName();
                $onTableAliasName =$this->processLeftJoinsForAttributeThatIsCastedDown($opposingRelationModelClassName,
                    $derivedRelationModelClassName, $onTableAliasName);
            }
            return $onTableAliasName;
        }

        protected function resolveJoinsForDerivedRelationViaCastedUpModelThatIsCastedUp($onTableAliasName, $canUseFromJoins = true)
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->
                                       getCastedUpModelClassNameForDerivedRelation();
            if($canUseFromJoins)
            {
                return $this->processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName);
            }
            else
            {
                return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
            }
        }

        protected function resolveJoinsForDerivedRelationViaCastedUpModelThatIsManyToMany($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $opposingRelationModelClassName  = $this->modelAttributeToDataProviderAdapter->getOpposingRelationModelClassName();
            $opposingRelationTableName       = $this->modelAttributeToDataProviderAdapter->getOpposingRelationTableName();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $this->modelAttributeToDataProviderAdapter->getManyToManyTableNameForDerivedRelationViaCastedUpModel(),
                "id",
                $onTableAliasName,
                self::resolveForeignKey($opposingRelationTableName));
            $onTableAliasName                = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                               $opposingRelationTableName,
                                               self::resolveForeignKey($opposingRelationModelClassName),
                                               $relationJoiningTableAliasName,
                                               'id');
            return $onTableAliasName;
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
            if($canUseFromJoins)
            {
                $onTableAliasName = $this->addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName);
            }
            else
            {
                $onTableAliasName = $this->addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName);
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
                return $this->addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName);
            }
            else
            {
                return $this->addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName);
            }
        }

        protected function addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addFromJoinsForAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addFromJoinsForAttributeThatIsCastedUp();
            }
        }

        protected function addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addLeftJoinsForAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName);
            }
        }

        protected function addFromJoinsForAttributeThatIsMixedIn($onTableAliasName)
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

        /**
         * @return string
         */
        protected function addFromJoinsForAttributeThatIsCastedUp()
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName();
            return $this->processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName);
        }

        private function processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeModelClassName)');
            $attributeTableName = $attributeModelClassName::getTableName($attributeModelClassName);
            $tableAliasName     = $attributeTableName;
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) != $attributeModelClassName &&
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

        protected function addLeftJoinsForAttributeThatIsMixedIn($onTableAliasName)
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

        protected function addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName)
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName();
            return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
        }

        private function processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName)
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($modelClassName)');
            assert('is_string($attributeModelClassName)');
            $attributeTableName       = $attributeModelClassName::getTableName($attributeModelClassName);
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) != $attributeModelClassName &&
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
                $onTableAliasName = $this->resolveJoinsForForARelationAttributeThatIsAHasManyVariant($onTableAliasName);
                $this->resolveSettingDistinctForARelationAttributeThatIsHasMany();
                return $onTableAliasName;
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

        protected function resolveSettingDistinctForARelationAttributeThatIsHasMany()
        {
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY)
            {
                $this->resolveSetToDistinct();
            }
        }

        protected function resolveSetToDistinct()
        {
            if ($this->setDistinct)
            {
                $this->joinTablesAdapter->setSelectDistinctToTrue();
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
                                               $onTableAliasName,
                                               self::resolveForeignKey($attributeTableName));
            //if this is not the id column, then add an additional left join.
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                $this->resolveSetToDistinct();
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

        protected function processLeftJoinsForAttributeThatIsCastedDown($modelClassName, $castedDownModelClassName,
                                                                        $onTableAliasName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            assert('is_string($onTableAliasName)');
            $modelDerivationPathToItem = $this->resolveModelDerivationPathToItemForCastingDown($modelClassName, $castedDownModelClassName);
            foreach($modelDerivationPathToItem as $modelClassNameToCastDownTo)
            {
                if ($modelClassNameToCastDownTo::getCanHaveBean())
                {
                    $castedDownTableName = $modelClassNameToCastDownTo::getTableName($modelClassNameToCastDownTo);
                    $onTableAliasName    = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                           $castedDownTableName,
                                           'id',
                                           $onTableAliasName,
                                           self::resolveForeignKey($modelClassName::getTableName($modelClassName)));
                    $modelClassName      = $modelClassNameToCastDownTo;
                }
            }
            return $onTableAliasName;
        }

        protected function resolveModelDerivationPathToItemForCastingDown($modelClassName, $castedDownModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($castedDownModelClassName);
            foreach($modelDerivationPathToItem as $key => $modelClassNameToCastDown)
            {
                unset($modelDerivationPathToItem[$key]);
                if($modelClassName == $modelClassNameToCastDown)
                {
                    break;
                }
            }
            return $modelDerivationPathToItem;
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
    }
?>