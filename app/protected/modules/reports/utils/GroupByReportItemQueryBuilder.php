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
     * Create the query string part for the SQL group by
     */
    class GroupByReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null |string $onTableAliasName
         * @return string
         */
        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            $columnContent = ModelDataProviderUtil::resolveGroupByAttributeColumnName(
                             $modelAttributeToDataProviderAdapter, $this->joinTablesAdapter, $onTableAliasName);
            return $this->resolveColumnContentForCalculatedModifier($columnContent);
        }

        /**
         * @param string $columnContent
         * @return string
         */
        protected function resolveColumnContentForCalculatedModifier($columnContent)
        {
            assert('is_string($columnContent)');
            $resolvedAttribute              = $this->componentForm->getResolvedAttribute();
            if($this->modelToReportAdapter->isAttributeACalculatedGroupByModifier($resolvedAttribute) &&
               $this->modelToReportAdapter->getCalculationOrModifierType($resolvedAttribute))
            {
                $sqlReadyType              = strtolower($this->modelToReportAdapter->
                                             getCalculationOrModifierType($resolvedAttribute));
                $timeZoneAdjustmentContent = $this->resolveTimeZoneAdjustmentForACalculatedDateTimeModifier($resolvedAttribute);
                return $sqlReadyType . '(' . $columnContent . $timeZoneAdjustmentContent . ')';
            }
            return $columnContent;
        }

        /**
         * @param string $attribute
         * @return string
         */
        protected function resolveTimeZoneAdjustmentForACalculatedDateTimeModifier($attribute)
        {
            $resolvedAttribute = ModelRelationsAndAttributesToReportAdapter::resolveRealAttributeName($attribute);
            if($this->modelToReportAdapter->getRealModelAttributeType($resolvedAttribute) == 'DateTime')
            {
                return DatabaseCompatibilityUtil::makeTimeZoneAdjustmentContent();
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter |
         * RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    ModelRelationsAndAttributesToReportAdapter::resolveRealAttributeName($attribute), $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
                           $modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            $groupByAttribute = $modelToReportAdapter->getRules()->
                getGroupByRelatedAttributeForRelationReportedAsAttribute(
                $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $groupByAttribute);
        }
    }
?>