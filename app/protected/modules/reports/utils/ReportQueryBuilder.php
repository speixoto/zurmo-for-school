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
     * Base class for working with report components to build the necessary query parts to make a full report query
     */
    abstract class ReportQueryBuilder
    {
        /**
         * @var RedBeanModelJoinTablesQueryAdapter
         */
        protected $joinTablesAdapter;

        public function __construct(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $this->joinTablesAdapter = $joinTablesAdapter;
        }

        abstract public function makeQueryContent(Array $components);

        abstract protected function resolveFinalContent($content, ComponentForReportForm $componentForm);

        protected function resolveComponentAttributeStringContent(ComponentForReportForm $componentForm)
        {
            $attributeAndRelationData = $componentForm->getAttributeAndRelationData();
            if(!is_array($attributeAndRelationData))
            {
                return $this->resolveComponentAttributeStringContentForNonNestedAttribute($componentForm);
            }
            elseif(count($attributeAndRelationData) > 1)
            {
                return $this->resolveComponentAttributeStringContentForNestedAttribute($componentForm);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveComponentAttributeStringContentForNonNestedAttribute(ComponentForReportForm $componentForm)
        {
            $attribute                           = $componentForm->getAttributeAndRelationData();
            $modelToReportAdapter                = ModelRelationsAndAttributesToReportAdapter::make(
                                                   $componentForm->getModuleClassName(),
                                                   $componentForm->getModelClassName(), $componentForm->getReportType());
            $modelAttributeToDataProviderAdapter = $this->makeModelAttributeToDataProviderAdapter(
                                                   $modelToReportAdapter, $attribute);
            return $this->resolveFinalContent($modelAttributeToDataProviderAdapter, $componentForm);
        }

        protected function resolveComponentAttributeStringContentForNestedAttribute(ComponentForReportForm $componentForm)
        {
            $attributeAndRelationData = $componentForm->getAttributeAndRelationData();
            $count                    = 0;
            $moduleClassName          = $componentForm->getModuleClassName();
            $modelClassName           = $componentForm->getModelClassName();
            $onTableAliasName         = null;
            $startingModelClassName   = null;
            foreach($attributeAndRelationData as $key => $relationOrAttribute)
            {
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($moduleClassName, $modelClassName, $componentForm->getReportType());
                $modelAttributeToDataProviderAdapter  = $this->makeModelAttributeToDataProviderAdapter(
                                        $modelToReportAdapter, $relationOrAttribute);
                if($this->shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter, $componentForm,
                                                                         $modelAttributeToDataProviderAdapter))
                {
                    $attribute                            = 'id';
                    $modelAttributeToDataProviderAdapter  = $this->makeModelAttributeToDataProviderAdapter(
                                                            $modelToReportAdapter, $attribute);
                    break;
                }
                elseif($modelToReportAdapter->isReportedOnAsARelation($relationOrAttribute))
                {
                    $modelClassName   = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                    $moduleClassName  = $modelToReportAdapter->getRelationModuleClassName($relationOrAttribute);
                    if($modelToReportAdapter->isInferredRelation($relationOrAttribute) ||
                       $modelToReportAdapter->isDerivedRelationsViaCastedUpModelRelation($relationOrAttribute))
                    {
                        static::resolveCastingHintForAttribute($componentForm,
                                                               $modelAttributeToDataProviderAdapter,
                                                               $modelClassName,
                                                               $modelToReportAdapter->resolveRealAttributeName(
                                                               $attributeAndRelationData[$key + 1]));
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
                    if($count + 1 != count($attributeAndRelationData))
                    {
                        throw new NotSupportedException('The final element in array must be an attribute, not a relation');
                    }
                }
                $count ++;
            }
            $modelAttributeToDataProviderAdapter->setCastingHintStartingModelClassName($startingModelClassName);
            return $this->resolveFinalContent($modelAttributeToDataProviderAdapter, $componentForm, $onTableAliasName);
        }

        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if($modelToReportAdapter->isInferredRelation($attribute))
            {
                return new InferredRedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute),
                    $modelToReportAdapter->getRelationModelClassName($attribute),
                    $modelToReportAdapter->getRelationModuleClassName($attribute));
            }
            elseif($modelToReportAdapter->isDerivedRelationsViaCastedUpModelRelation($attribute))
            {
                return new DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $attribute);
            }
            //Example is full name or calculated number
            elseif($modelToReportAdapter->isDerivedAttribute($attribute))
            {
                //Derived attributes are assumed to be made via model so we only need the id of the model here.
                return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(), 'id');
            }
            //Example: createdUser__User
            elseif($modelToReportAdapter->isDynamicallyDerivedAttribute($attribute))
            {
                return new RedBeanModelAttributeToDataProviderAdapter(
                                $modelToReportAdapter->getModelClassName(),
                                $modelToReportAdapter->resolveRealAttributeName($attribute), 'lastName');
            }
            //Example: CustomField, CurrencyValue, OwnedCustomField, or likeContactState
            elseif($modelToReportAdapter->relationIsReportedAsAttribute($attribute))
            {
                return static::makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
                               $modelToReportAdapter, $attribute);
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
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $attribute);
            }
        }

        protected static function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
                                  $modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                       $attribute);
        }

        protected function resolveCastingHintForAttribute(ComponentForReportForm  $componentForm,
                                                          $modelAttributeToDataProviderAdapter,
                                                          $modelClassName,
                                                          $realAttributeName)
        {
            $hintAdapter        = new RedBeanModelAttributeToDataProviderAdapter($modelClassName, $realAttributeName);
            $hintModelClassName = $hintAdapter->getAttributeModelClassName();
            $modelAttributeToDataProviderAdapter->setCastingHintModelClassNameForAttribute($hintModelClassName);
        }

        protected function shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          ComponentForReportForm $componentForm,
                                                                          $modelAttributeToDataProviderAdapter)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            return false;
        }
    }
?>