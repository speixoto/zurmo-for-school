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
     * Create the query string part for the SQL where part
     */
    class FiltersReportQueryBuilder extends ReportQueryBuilder
    {
        protected $filtersStructure;

        public function __construct(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    $filtersStructure)
        {
            assert('is_string($filtersStructure)');
            parent::__construct($joinTablesAdapter);
            $this->filtersStructure     = $filtersStructure;
        }

        public function makeQueryContent(Array $components)
        {
            $whereContent = array();
            foreach($components as $key => $filter)
            {
                $whereContent[$key + 1] = $this->resolveComponentAttributeStringContent($filter);
            }
            $content = strtr(strtolower($this->filtersStructure), $whereContent);
            if(empty($content))
            {
                return null;
            }
            return $content;
        }

        protected function resolveComponentAttributeStringContent(ComponentForReportForm $componentForm)
        {
            assert('$componentForm instanceof FilterForReportForm');
            return parent::resolveComponentAttributeStringContent($componentForm);
        }

        protected function resolveFinalContent($modelAttributeToDataProviderAdapter,
                                               ComponentForReportForm $componentForm, $onTableAliasName = null)
        {
            $modelClassName  = $modelAttributeToDataProviderAdapter->getResolvedModelClassName();
            $metadataAdapter = new FilterForReportFormToDataProviderMetadataAdapter($componentForm);
            $attributeData   = $metadataAdapter->getAdaptedMetadata();
            return ModelDataProviderUtil::makeWhere($modelClassName, $attributeData, $this->joinTablesAdapter,
                                                    $onTableAliasName);
        }

        protected static function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
            $modelToReportAdapter, $attribute, ComponentForReportForm $componentForm)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)'); //todo: why is this also using a sortAttribute from the rule. seems strange
            $sortAttribute = $modelToReportAdapter->getRules()->getSortAttributeForRelationReportedAsAttribute(
                             $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $sortAttribute);
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute,
                                                                   ComponentForReportForm $componentForm)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                //todO: document that we dont have to do like displayAttributeBuilder where it resolves for related attribute, since really this can only be date/datetime coluumns. at least for now
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute));
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute, $componentForm);
        }

        //todo: do we need to resolve casting hint for where clause too? need to test, not sure how this would work
        //todo: test multi because multi is sub-select so in fact we do use sub-select for multiple dropdown.. multiples need to be sub-query
    }
?>