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
     * Popuplate the RedBeanModelSelectQueryAdapter with the necessary columns or calculations to select
     */
    class DisplayAttributesReportQueryBuilder extends ReportQueryBuilder
    {
        protected $selectQueryAdapter;

        public function __construct(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            parent::__construct($joinTablesAdapter);
            $this->selectQueryAdapter = $selectQueryAdapter;
        }

        public function makeQueryContent(Array $components)
        {
            $content = null;
            foreach($components as $displayAttribute)
            {
                $this->resolveComponentAttributeStringContent($displayAttribute);
            }
            return $this->selectQueryAdapter->getSelect();
        }

        protected function resolveComponentAttributeStringContent(ComponentForReportForm $componentForm)
        {
            assert('$componentForm instanceof DisplayAttributeForReportForm');
            return parent::resolveComponentAttributeStringContent($componentForm);
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter,
                                               ComponentForReportForm $componentForm, $onTableAliasName = null)
        {
            $this->resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter, $componentForm, $onTableAliasName);
        }

        protected function resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter,
                                                             DisplayAttributeForReportForm $componentForm,
                                                             $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder        = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            if($this->shouldPrematurelyStopBuildingJoinsForAttribute($componentForm, $modelAttributeToDataProviderAdapter))
            {
                $this->resolveDisplayAttributeForPrematurelyStoppingJoins($builder,
                                                                          $modelAttributeToDataProviderAdapter,
                                                                          $onTableAliasName);
            }
            else
            {
                $this->resolveDisplayAttributeForProcessingAllJoins(      $builder,
                                                                          $modelAttributeToDataProviderAdapter,
                                                                          $componentForm,
                                                                          $onTableAliasName);
            }
        }

        protected function resolveDisplayAttributeForPrematurelyStoppingJoins(ModelJoinBuilder $builder,
                                                                              $modelAttributeToDataProviderAdapter,
                                                                              $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $resolvedModelClassName     = $this->resolvedModelClassName($modelAttributeToDataProviderAdapter);
            if($onTableAliasName == null)
            {
                $onTableAliasName     = $modelAttributeToDataProviderAdapter->getModelTableName();
            }
            $this->selectQueryAdapter->resolveIdClause($resolvedModelClassName, $onTableAliasName);
        }

        protected function resolveDisplayAttributeForProcessingAllJoins(ModelJoinBuilder $builder,
                                                                        $modelAttributeToDataProviderAdapter,
                                                                        $componentForm,
                                                                        $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('$componentForm instanceof DisplayAttributeForReportForm');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $modelToReportAdapter           = ModelRelationsAndAttributesToReportAdapter::make(
                                              $componentForm->getModuleClassName(),
                                              $componentForm->getModelClassName(),
                                              $componentForm->getReportType());
            $tableAliasName                 = $builder->resolveJoins($onTableAliasName,
                                              ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            if(static::isDisplayAttributeMadeViaSelect($componentForm))
            {
                if(!$modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter)
                {
                    throw new NotSupportedException();
                }
                $modelToReportAdapter->resolveDisplayAttributeTypeAndAddSelectClause(
                                  $this->selectQueryAdapter,
                                  $componentForm->getResolvedAttribute(),
                                  $tableAliasName,
                                  $this->resolveColumnName($modelAttributeToDataProviderAdapter),
                                  $componentForm->columnAliasName);
            }
            else
            {
                $this->selectQueryAdapter->resolveIdClause(
                    $this->resolvedModelClassName($modelAttributeToDataProviderAdapter),
                    $this->resolvedTableAliasName($modelAttributeToDataProviderAdapter, $builder));
            }
        }

        protected function resolveColumnName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getColumnName();
            }
        }

        protected function resolvedModelClassName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelationModelClassName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getModelClassName();
            }
        }

        protected function resolvedTableAliasName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter,
                                                  ModelJoinBuilder $builder)
        {
            if($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $builder->getTableAliasNameForRelatedModel();
            }
            else
            {
                return $builder->getTableAliasNameForBaseModel();
            }
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
               $modelToReportAdapter->isDisplayAttributeACalculationOrModifier($attribute))
            {
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute));
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }

        protected function shouldPrematurelyStopBuildingJoinsForAttribute(ComponentForReportForm $componentForm,
                                                                          $modelAttributeToDataProviderAdapter)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            if(static::isDisplayAttributeMadeViaSelect($componentForm))
            {
                return false;
            }
            if($modelAttributeToDataProviderAdapter instanceof
               DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            elseif($modelAttributeToDataProviderAdapter instanceof
                   InferredRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            //If casted up non-relation
            elseif($modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() &&
               !$modelAttributeToDataProviderAdapter->isRelation())
            {
                return true;
            }
            //Owned relations such as Address or Email
            elseif($modelAttributeToDataProviderAdapter->isOwnedRelation())
            {
                return true;
            }
            //if a User relation
            elseif($modelAttributeToDataProviderAdapter->isRelation() &&
                   $modelAttributeToDataProviderAdapter->getRelationModelClassName() == 'User')
            {
                return true;
            }
            return parent::shouldPrematurelyStopBuildingJoinsForAttribute($componentForm,
                                                                          $modelAttributeToDataProviderAdapter);
        }

        protected static function isDisplayAttributeMadeViaSelect(ComponentForReportForm $componentForm)
        {
            $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::make(
                                    $componentForm->getModuleClassName(),
                                    $componentForm->getModelClassName(),
                                    $componentForm->getReportType());
            if($modelToReportAdapter->isDisplayAttributeMadeViaSelect($componentForm->getResolvedAttribute()))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        protected function resolveCastingHintForAttribute(ComponentForReportForm  $componentForm,
                                                          $modelAttributeToDataProviderAdapter, $modelClassName,
                                                          $realAttributeName)
        {
            if(static::isDisplayAttributeMadeViaSelect($componentForm))
            {
                return parent::resolveCastingHintForAttribute($componentForm, $modelAttributeToDataProviderAdapter,
                                                              $modelClassName, $realAttributeName);
            }
        }
    }
?>