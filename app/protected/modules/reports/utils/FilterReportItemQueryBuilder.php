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

    class FilterReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        protected $filtersStructure;

        public function resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                          $modelClassName,
                                                          $realAttributeName)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            if($modelToReportAdapter->isAttributeReadOptimization($realAttributeName))
            {
                $hintAdapter        = new ReadOptimizationDerivedAttributeToDataProviderAdapter(
                                      $modelToReportAdapter->getModelClassName(), null);
                $hintModelClassName = $hintAdapter->getAttributeModelClassName();
                $modelAttributeToDataProviderAdapter->setCastingHintModelClassNameForAttribute($hintModelClassName);
            }
            else
            {
                return parent::resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                              $modelClassName, $realAttributeName);
            }
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            //todo: split if /else into 2 sub methods
            if($modelAttributeToDataProviderAdapter instanceof ReadOptimizationDerivedAttributeToDataProviderAdapter)
            {
                $builder        = new ReadOptimizationModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
                $clausePosition = 1;
                $where          = null;
                $builder->resolveJoinsAndBuildWhere(null, null, $clausePosition, $where, $onTableAliasName);
                return $where[1];
            }
            else
            {
                $modelClassName  = $modelAttributeToDataProviderAdapter->getResolvedModelClassName();
                $metadataAdapter = new FilterForReportFormToDataProviderMetadataAdapter($this->componentForm);
                $attributeData   = $metadataAdapter->getAdaptedMetadata();
                //todO: we need a way to not setDistinct from here. right now that just defaults to true in ModelDataProviderUtil::makeWhere, so maybe we need
                //todo: override util that we then dont set it or something. also need test, that requires data  provider tests to work right. or we can test distinct is still false on all existing builder tests..
                return ModelDataProviderUtil::makeWhere($modelClassName, $attributeData, $this->joinTablesAdapter,
                                                        $onTableAliasName);
            }
        }

        protected function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)'); //todo: why is this also using a sortAttribute from the rule. seems strange
            $sortAttribute = $modelToReportAdapter->getRules()->getSortAttributeForRelationReportedAsAttribute(
                             $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $sortAttribute);
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter->isAttributeReadOptimization($attribute))
            {
                return new ReadOptimizationDerivedAttributeToDataProviderAdapter(
                           $modelToReportAdapter->getModelClassName(), null);
            }
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                //todO: document that we dont have to do like displayAttributeBuilder where it resolves for related attribute, since really this can only be date/datetime coluumns. at least for now
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute));
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }

        //todo: test multi because multi is sub-select so in fact we do use sub-select for multiple dropdown.. multiples need to be sub-query
    }
?>