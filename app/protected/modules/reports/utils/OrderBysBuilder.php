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
     * Helper functionality for finding the filter value element
     * associated with a model's attribute
     */
    class OrderBysBuilder
    {
        /**
         * @var RedBeanModelJoinTablesQueryAdapter
         */
        protected $joinTablesAdapter;

        public function __construct(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $this->joinTablesAdapter = $joinTablesAdapter;
        }

        public function makeQueryContent(Array $orderBys)
        {
            $content = null;
            foreach($orderBys as $orderBy)
            {
                $orderByContent = $this->resolveSortAttributeColumnNameAndOrder($orderBy);
                if($content != null)
                {
                    $content .= ', ';
                }
                $content       .= $orderByContent;
            }
            return $content;
        }

        protected function resolveSortAttributeColumnNameAndOrder(OrderByForReportForm $orderBy)
        {
            $attributeAndRelationData = $orderBy->getAttributeAndRelationData();
            if(!is_array($attributeAndRelationData))
            {
                return $this->resolveSortAttributeColumnNameAndOrderForNonNestedAttribute($orderBy);
            }
            elseif(count($attributeAndRelationData) > 1)
            {
                return $this->resolveSortAttributeColumnNameAndOrderForNestedAttribute($orderBy);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveSortAttributeColumnNameAndOrderForNonNestedAttribute(OrderByForReportForm $orderBy)
        {
            $attribute                           = $orderBy->getAttributeAndRelationData();
            $modelToReportAdapter                = ModelRelationsAndAttributesToReportAdapter::make(
                                                   $orderBy->getModuleClassName(),
                                                   $orderBy->getModelClassName(), $orderBy->getReportType());
            $modelAttributeToDataProviderAdapter = $this->makeModelAttributeToDataProviderAdapter(
                                                   $modelToReportAdapter, $attribute);
            $content                             = ModelDataProviderUtil::resolveSortAttributeColumnName(
                                                   $modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            return $content . ' ' . $orderBy->order;
        }

        protected function resolveSortAttributeColumnNameAndOrderForNestedAttribute(OrderByForReportForm $orderBy)
        {
            $attributeAndRelationData = $orderBy->getAttributeAndRelationData();
            $count                    = 0;
            $moduleClassName          = $orderBy->getModuleClassName();
            $modelClassName           = $orderBy->getModelClassName();
            $onTableAliasName         = null;
            $startingModelClassName   = null;
            foreach($attributeAndRelationData as $key => $relationOrAttribute)
            {
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($moduleClassName, $modelClassName, $orderBy->getReportType());
                if($modelToReportAdapter->isReportedOnAsARelation($relationOrAttribute))
                {
                    $modelClassName   = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                    $moduleClassName  = $modelToReportAdapter->getRelationModuleClassName($relationOrAttribute);
                    if($modelToReportAdapter->isInferredRelation($relationOrAttribute))
                    {
                        $modelAttributeToDataProviderAdapter = new InferredRedBeanModelAttributeToDataProviderAdapter(
                                        $modelToReportAdapter->getModelClassName(),
                                        $modelToReportAdapter->resolveRealAttributeName($relationOrAttribute),
                                        $modelToReportAdapter->getRelationModelClassName($relationOrAttribute),
                                        $modelToReportAdapter->getRelationModuleClassName($relationOrAttribute));
                        static::resolveCastingHintForAttribute($modelAttributeToDataProviderAdapter, $modelClassName,
                                                               $modelToReportAdapter->resolveRealAttributeName(
                                                               $attributeAndRelationData[$key + 1]));
                    }
                    elseif($modelToReportAdapter->isDerivedRelationsViaCastedUpModelRelation($relationOrAttribute))
                    {
                        $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                               $modelToReportAdapter->getModelClassName(),
                                                               $relationOrAttribute);
                        static::resolveCastingHintForAttribute($modelAttributeToDataProviderAdapter, $modelClassName,
                                                               $modelToReportAdapter->resolveRealAttributeName(
                                                               $attributeAndRelationData[$key + 1]));
                    }
                    else
                    {
                        $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                               $modelToReportAdapter->getModelClassName(),
                                                               $relationOrAttribute);
                    }
                    $modelAttributeToDataProviderAdapter->setCastingHintStartingModelClassName($startingModelClassName);
                    $builder                = new ModelJoinBuilder($modelAttributeToDataProviderAdapter,
                                              $this->joinTablesAdapter);
                    $onTableAliasName       = $builder->resolveJoins($onTableAliasName,
                                              ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                    $startingModelClassName = $modelAttributeToDataProviderAdapter->getCastingHintModelClassNameForAttribute();
                }
                else
                {
                    $attribute = $relationOrAttribute;
                    if($count + 1 != count($attributeAndRelationData))
                    {
                        throw new NotSupportedException('The final element in array must be an attribute, not a relation');
                    }
                }
                $count ++;
            }
            $modelToReportAdapter                 = ModelRelationsAndAttributesToReportAdapter::
                                                    make($moduleClassName, $modelClassName, $orderBy->getReportType());
            $modelAttributeToDataProviderAdapter  = $this->makeModelAttributeToDataProviderAdapter(
                                                    $modelToReportAdapter, $attribute);
            $modelAttributeToDataProviderAdapter->setCastingHintStartingModelClassName($startingModelClassName);
            $content                              = ModelDataProviderUtil::resolveSortAttributeColumnName(
                                                    $modelAttributeToDataProviderAdapter, $this->joinTablesAdapter,
                                                    $onTableAliasName);
            return $content . ' ' . $orderBy->order;
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            //Example: createdUser__User
            if($modelToReportAdapter->isDynamicallyDerivedAttribute($attribute))
            {
                return new RedBeanModelAttributeToDataProviderAdapter(
                                $modelToReportAdapter->getModelClassName(),
                                $modelToReportAdapter->resolveRealAttributeName($attribute), 'lastName');
            }
            //Example: CustomField, CurrencyValue, OwnedCustomField, or likeContactState
            elseif($modelToReportAdapter->relationIsReportedAsAttribute($attribute))
            {

                $sortAttribute = $modelToReportAdapter->getRules()->
                                 getSortAttributeForRelationReportedAsAttribute(
                                 $modelToReportAdapter->getModel(), $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                                                                      $attribute, $sortAttribute);
            }
            //Example: name or phone
            elseif(!$modelToReportAdapter->isReportedOnAsARelation($attribute) &&
                   !$modelToReportAdapter->relationIsReportedAsAttribute($attribute))
            {
                return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                                                                      $attribute);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveCastingHintForAttribute($modelAttributeToDataProviderAdapter, $modelClassName,
                                                          $realAttributeName)
        {
            $hintAdapter        = new RedBeanModelAttributeToDataProviderAdapter($modelClassName, $realAttributeName);
            $hintModelClassName = $hintAdapter->getAttributeModelClassName();
            $modelAttributeToDataProviderAdapter->setCastingHintModelClassNameForAttribute($hintModelClassName);
        }
    }
?>