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
    class GroupBysReportQueryBuilder extends ReportQueryBuilder
    {
        public function makeQueryContent(Array $components)
        {
            $content = null;
            foreach($components as $groupBy)
            {
                $groupByContent = $this->resolveComponentAttributeStringContent($groupBy);
                if($content != null)
                {
                    $content .= ', ';
                }
                $content       .= $groupByContent;
            }
            return $content;
        }

        protected function resolveComponentAttributeStringContent(ComponentForReportForm $componentForm)
        {
            assert('$componentForm instanceof GroupByForReportForm');
            return parent::resolveComponentAttributeStringContent($componentForm);
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter,
                                               ComponentForReportForm $componentForm, $onTableAliasName = null)
        {


            $columnContent = ModelDataProviderUtil::resolveGroupByAttributeColumnName(
                             $modelAttributeToDataProviderAdapter, $this->joinTablesAdapter, $onTableAliasName);
            return $this->resolveColumnContentForCalculatedModifier($componentForm, $columnContent);
        }

        protected function resolveColumnContentForCalculatedModifier(GroupByForReportForm $componentForm, $columnContent)
        {
            assert('is_string($columnContent)');
            $resolvedAttribute              = $componentForm->getResolvedAttribute();
            $modelToReportAdapter           = ModelRelationsAndAttributesToReportAdapter::make(
                $componentForm->getResolvedAttributeModuleClassName(),
                $componentForm->getResolvedAttributeModelClassName(),
                $componentForm->getReportType());
            if($modelToReportAdapter->isAttributeACalculatedGroupByModifier($resolvedAttribute) &&
               $modelToReportAdapter->getGroupByCalculatedModifierAttributeType($resolvedAttribute))
            {
                $sqlReadyType              = strtolower($modelToReportAdapter->
                                             getGroupByCalculatedModifierAttributeType($resolvedAttribute));
                $timeZoneAdjustmentContent = $this->resolveTimeZoneAdjustmentForACalculatedDateTimeModifier(
                                             $modelToReportAdapter, $resolvedAttribute);
                return $sqlReadyType . '(' . $columnContent . $timeZoneAdjustmentContent . ')';
            }
            return $columnContent;
        }

        protected function resolveTimeZoneAdjustmentForACalculatedDateTimeModifier($modelToReportAdapter, $attribute)
        {
            $resolvedAttribute = $modelToReportAdapter->resolveRealAttributeName($attribute);
            if($modelToReportAdapter->getRealModelAttributeType($resolvedAttribute) == 'DateTime')
            {
                return DatabaseCompatibilityUtil::makeTimeZoneAdjustmentContent();
            }
        }


        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute,
                                                                   ComponentForReportForm $componentForm)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute), $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute, $componentForm);
        }

        protected static function resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter->relationIsReportedAsAttribute(
                $modelToReportAdapter->resolveRealAttributeName($attribute)))
            {
                return 'value';
            }
            else
            {
                return null;
            }
        }

        protected static function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
            $modelToReportAdapter, $attribute, ComponentForReportForm $componentForm)
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