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

class ModelJoinBuilder
{
    protected $modelAttributeToDataProviderAdapter;

    protected $joinTablesAdapter;

    public static function makeColumnNameWithTableAlias($tableAliasName, $columnName)
    {
        assert('is_string($tableAliasName)');
        assert('is_string($columnName)');
        $quote = DatabaseCompatibilityUtil::getQuote();
        return $quote . $tableAliasName . $quote . '.' . $quote . $columnName. $quote;
    }

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

    public function resolveJoinsForRelatedAttribute()
    {
        $onTableAliasName = $this->resolveShouldAddFromTable();
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

    protected function resolveJoinForManyToManyRelatedAttribute()
    {
        assert('$this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
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

    protected static function resolveForeignKey($idName)
    {
        assert('is_string($idName)');
        return $idName . '_id';
    }
}
?>