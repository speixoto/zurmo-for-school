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
     * Create the query string part for the SQL order by
     */
    class OrderBysReportQueryBuilder extends ReportQueryBuilder
    {
        public function makeQueryContent(Array $components)
        {
            $content = null;
            foreach($components as $orderBy)
            {
                $orderByContent = $this->resolveComponentAttributeStringContent($orderBy);
                if($content != null)
                {
                    $content .= ', ';
                }
                $content     .= $orderByContent;
            }
            return $content;
        }

        protected static function resolveSortColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                        $modelAttributeToDataProviderAdapter)
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

        protected static function resolveOrderByString(OrderByForReportForm $componentForm, $tableAliasName,
                                                       $resolvedSortColumnName, $queryStringExtraPart)
        {
            $modelToReportAdapter = static::makeModelToReportAdapterByComponentForm($componentForm);
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculationOrModifier($componentForm->getResolvedAttribute()))
            {
                return $modelToReportAdapter->resolveOrderByStringForCalculationOrModifier(
                    $componentForm->getResolvedAttribute(), $tableAliasName, $resolvedSortColumnName, $queryStringExtraPart);
            }
            else
            {
                return ModelDataProviderUtil::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
            }
        }

        protected function resolveComponentAttributeStringContent(ComponentForReportForm $componentForm)
        {
            assert('$componentForm instanceof OrderByForReportForm');
            return parent::resolveComponentAttributeStringContent($componentForm);
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter,
                                               ComponentForReportForm $componentForm,
                                               $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $content = $this->resolveSortAttributeContent($componentForm, $modelAttributeToDataProviderAdapter, $onTableAliasName);
            return $content . ' ' . $componentForm->order;
        }

        protected function resolveSortAttributeContent(ComponentForReportForm $componentForm,
                                                    RedBeanModelAttributeToDataProviderAdapter
                                                    $modelAttributeToDataProviderAdapter,
                                                    $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder                = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            $tableAliasName         = $builder->resolveJoins($onTableAliasName, ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            $resolvedSortColumnName = self::resolveSortColumnName($modelAttributeToDataProviderAdapter);
            $queryStringExtraPart   = $this->getAttributeClauseQueryStringExtraPart($componentForm, $tableAliasName);
            return self::resolveOrderByString($componentForm, $tableAliasName, $resolvedSortColumnName, $queryStringExtraPart);
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute,
                                                                   ComponentForReportForm $componentForm)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculationOrModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute), $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute, $componentForm);
        }

        protected static function makeModelAttributeToDataProviderAdapterForDynamicallyDerivedAttribute(
            $modelToReportAdapter, $attribute)
        {
            return new RedBeanModelAttributeToDataProviderAdapter(
                $modelToReportAdapter->getModelClassName(),
                $modelToReportAdapter->resolveRealAttributeName($attribute), 'lastName');
        }

        protected static function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
            $modelToReportAdapter, $attribute, ComponentForReportForm $componentForm)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            $sortAttribute = $modelToReportAdapter->getRules()->
                getSortAttributeForRelationReportedAsAttribute(
                $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $sortAttribute);
        }
    }
?>