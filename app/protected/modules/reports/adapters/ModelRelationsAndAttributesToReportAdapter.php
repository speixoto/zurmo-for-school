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
     * Base class for managing adapting model relations and attributes into a report
     */
    class ModelRelationsAndAttributesToReportAdapter
    {
        const DYNAMIC_ATTRIBUTE_USER         = 'User';

        const DYNAMIC_RELATION_INFERRED      = 'Inferred';

        const RELATION_VIA_MODULE            = 'Via';

        const RELATION_VIA_MODULE_DELIMITER  = '_';

        protected $model;

        protected $rules;

        protected $reportType;

        protected $moduleClassName;

        public static function make($moduleClassName, $modelClassName, $reportType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($reportType)');
            $rules                     = ReportRules::makeByModuleClassName($moduleClassName);
            $model                     = new $modelClassName(false);
            if($reportType == Report::TYPE_ROWS_AND_COLUMNS)
            {
                $adapter       = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules,
                                                                                         $reportType, $moduleClassName);
            }
            elseif($reportType == Report::TYPE_SUMMATION)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules,
                                                                                         $reportType, $moduleClassName);
            }
            elseif($reportType == Report::TYPE_MATRIX)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules,
                                                                                         $reportType, $moduleClassName);
            }
            else
            {
                throw new NotSupportedException();
            }
            return $adapter;
        }

        public function getModel()
        {
            return $this->model;
        }

        public function getModelClassName()
        {
            return get_class($this->model);
        }

        public function getRules()
        {
            return $this->rules;
        }

        /**
         * @param RedBeanModel $model
         * @param ReportRules $rules
         * @param string $reportType
         * @param string $moduleClassName - optional for when there is a stateAdapter involved.  In the case of LeadsModule
         * it still uses the Contact model but is important to know that the originating module is Leads.  If moduleClassName
         * is not specified, then it will default to the model's moduleClassName
         */
        public function __construct(RedBeanModel $model, ReportRules $rules, $reportType, $moduleClassName = null)
        {
            assert('is_string($reportType)');
            assert('is_string($moduleClassName) || $moduleClassName == null');
            $this->model      = $model;
            $this->rules      = $rules;
            $this->reportType = $reportType;
            if($moduleClassName == null)
            {
                $moduleClassName   = $model::getModuleClassName();
            }
            $this->moduleClassName = $moduleClassName;
        }

        /**
         *
         * Enter description here ...
         * @param string $attribute
         */
        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            if($this->isDynamicallyDerivedAttribute($attribute))
            {
                $resolvedAttribute = $attribute;
            }
            else
            {
                $resolvedAttribute = $this->resolveRealAttributeName($attribute);
            }
            $attributesData    = $this->getAttributesIncludingDerivedAttributesData();
            if(!isset($attributesData[$resolvedAttribute]))
            {
                throw new NotSupportedException('Label not found for: ' . $resolvedAttribute);
            }
            return $attributesData[$resolvedAttribute]['label'];
        }

        /**
         *
         * Enter description here ...
         * @param string $attribute
         */
        public function getRelationLabel($relation)
        {
            assert('is_string($relation)');
            $relationsData    = $this->getSelectableRelationsData();
            if(!isset($relationsData[$attribute]))
            {
                throw new NotSupportedException();
            }
            return $relationsData[$attribute]['label'];
        }

        /**
         * Returns true/false if a string passed in is considered a relation from a reporting perspective. In this case
         * a dropDown is not considered a relation because it is reported on as a regular attribute.
         * @param string $relationOrAttribute
         */
        public function isReportedOnAsARelation($relationOrAttribute)
        {
            assert('is_string($relationOrAttribute)');
            $relations = $this->getSelectableRelationsData();
            if(isset($relations[$relationOrAttribute]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @return module class name.  Resolves for inferred and derived relations
         */
        public function getRelationModuleClassName($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredOrViaData    = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredOrViaData) == 4)
            {
                list($notUsed, $notUsed2, $notUsed3, $viaModule) = $relationAndInferredOrViaData;
                list($notUsed, $moduleClassName)                 = explode(self::RELATION_VIA_MODULE_DELIMITER, $viaModule);
                return $moduleClassName;
            }
            if(count($relationAndInferredOrViaData) == 3)
            {
                list($modelClassName, $notUsed, $notUsed2) = $relationAndInferredOrViaData;
                return $modelClassName::getModuleClassName();
            }
            elseif(count($relationAndInferredOrViaData) == 2)
            {
                list($notUsed, $viaModule)       = $relationAndInferredOrViaData;
                list($notUsed, $moduleClassName) = explode(self::RELATION_VIA_MODULE_DELIMITER, $viaModule);
                return $moduleClassName;
            }
            elseif(count($relationAndInferredOrViaData) == 1 && isset($derivedRelations[$relation]))
            {
                $modelClassName = $this->model->getDerivedRelationModelClassName($relation);
                return $modelClassName::getModuleClassName();
            }
            elseif(count($relationAndInferredOrViaData) == 1)
            {
                $modelClassName = $this->model->getRelationModelClassName($relation);
                return $modelClassName::getModuleClassName();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return model class name.  Resolves for inferred and derived relations
         */
        public function getRelationModelClassName($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredOrViaData    = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredOrViaData) == 4)
            {
                list($modelClassName, $notUsed, $notUsed2, $notUsed3) = $relationAndInferredOrViaData;
                return $modelClassName;
            }
            if(count($relationAndInferredOrViaData) == 3)
            {
                list($modelClassName, $notUsed, $notUsed2) = $relationAndInferredOrViaData;
                return $modelClassName;
            }
            elseif(count($relationAndInferredOrViaData) == 2)
            {
                list($relation, $notUsed) = $relationAndInferredOrViaData;
                return $this->model->getRelationModelClassName($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 1 && isset($derivedRelations[$relation]))
            {
                return $this->model->getDerivedRelationModelClassName($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 1)
            {
                return $this->model->getRelationModelClassName($relation);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForFilters()
        {
            throw new NotImplementedException();
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForDisplayAttributes()
        {
            throw new NotImplementedException();
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForOrderBys()
        {
            throw new NotImplementedException();
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForGroupBys()
        {
            throw new NotImplementedException();
        }

        public function getAvailableOperatorsType($attribute)
        {
            assert('is_string($attribute)');
            if($this->isDynamicallyDerivedAttribute($attribute))
            {
                return null;
            }
            if($this->isDerivedAttribute($attribute))
            {
                throw new NotSupportedException($message, $code, $previous);
            }
            $resolvedAttribute = $this->resolveRealAttributeName($attribute);
            if(null != $availableOperatorsTypeFromRule = $this->rules->getAvailableOperatorsTypes($this->model,
                                                                                                  $resolvedAttribute))
            {
                return $availableOperatorsTypeFromRule;
            }
            return ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($this->model, $resolvedAttribute);
        }

        public function getFilterValueElementType($attribute)
        {
            assert('is_string($attribute)');
            if($this->isDerivedAttribute($attribute))
            {
                return null;
            }
            if($this->isDynamicallyDerivedAttribute($attribute))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                if($parts[1] != 'User')
                {
                    throw new NotSupportedException();
                }
                return 'UserNameId';
            }
            $resolvedAttribute = $this->resolveRealAttributeName($attribute);
            if(null != $filterValueElementTypeFromRule = $this->rules->getFilterValueElementType($this->model,
                                                                                                 $resolvedAttribute))
            {
                return $filterValueElementTypeFromRule;
            }
            return ModeAttributeToReportFilterValueElementTypeUtil::getType($this->model, $resolvedAttribute);
        }

        public function getDisplayElementType($attribute)
        {
            assert('is_string($attribute)');
            $derivedAttributes = $this->getDerivedAttributesData();
            if(isset($derivedAttributes[$attribute]))
            {
                return $derivedAttributes[$attribute]['derivedAttributeType'];
            }
            if($this->isDynamicallyDerivedAttribute($attribute))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                if($parts[1] != 'User')
                {
                    throw new NotSupportedException();
                }
                return 'User';
            }
            $resolvedAttribute = $this->resolveRealAttributeName($attribute);
            return $this->getRealModelAttributeType($resolvedAttribute);
        }

        public function getRealModelAttributeType($attribute)
        {
            assert('is_string($attribute)');
            return ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
        }

        public function getAllRelationsData()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute))
                {
                    $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
                }
            }
            return $attributes;
        }
        /**
         * Returns the array of selectable relations for creating a report.  Does not include relations that are
         * marked as nonReportable in the rules and also excludes relations that are marked as relations
         * reportedAsAttributes by the rules.  Includes relations marked as derivedRelationsViaCastedUpModel
         * @return array of relation name and data including the label
         */
        public function getSelectableRelationsData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    !$this->rules->relationIsReportedAsAttribute($this->model, $attribute) &&
                    $this->rules->attributeIsReportable($this->model, $attribute) &&
                    !$this->relationLinksToPrecedingRelation($attribute, $precedingModel, $precedingRelation)
                    )
                {
                    $this->resolveRelationToSelectableRelationData($attributes, $attribute);
                }
            }
            $attributes       = array_merge($attributes, $this->getDerivedRelationsViaCastedUpModelData($precedingModel, $precedingRelation));
            $attributes       = array_merge($attributes, $this->getInferredRelationsData($precedingModel, $precedingRelation));
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }


        public function getAttributesIncludingDerivedAttributesData()
        {
            $attributes = array('id' => array('label' => Yii::t('Default', 'Id')));
            $attributes = array_merge($attributes, $this->getAttributesNotIncludingDerivedAttributesData());
            $attributes = array_merge($attributes, $this->getDerivedAttributesData());
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        public function isRelationASingularRelation($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredOrViaData    = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredOrViaData) == 4)
            {
                list($modelClassName, $relation, $notUsed, $notUsed) = $relationAndInferredOrViaData;
                $type = $this->model->getRelationType($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 3)
            {
                list($modelClassName, $relation, $notUsed) = $relationAndInferredOrViaData;
                $type = $this->model->getRelationType($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 2)
            {
                list($relation, $notUsed) = $relationAndInferredOrViaData;
                $type = $this->model->getRelationType($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 1 && isset($derivedRelations[$relation]))
            {
                $type = $this->model->getDerivedRelationType($relation);
            }
            elseif(count($relationAndInferredOrViaData) == 1)
            {
                $type = $this->model->getRelationType($relation);
            }
            else
            {
                throw new NotSupportedException();
            }
            if( $type == RedBeanModel::HAS_ONE ||
                $type == RedBeanModel::HAS_ONE_BELONGS_TO ||
                $type == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                return true;
            }
            return false;
        }

        public function getInferredRelationsData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                $inferredRelationModelClassNames = $this->getInferredRelationModelClassNamesForRelation($attribute);
                if ($this->model->isRelation($attribute) && $inferredRelationModelClassNames != null)
                {
                    $inferredModuleConnections = $this->rules->getInferredModuleConnections($this->model, $attribute);
                    foreach($inferredRelationModelClassNames as $modelClassName)
                    {
                        if(!$this->inferredRelationLinksToPrecedingRelation($modelClassName, $attribute, $precedingModel, $precedingRelation))
                        {
                            if(isset($inferredModuleConnections[$modelClassName]))
                            {
                                foreach($inferredModuleConnections[$modelClassName] as $moduleClassName)
                                {
                                    $typeToUse              = 'Plural';
                                    if($this->isRelationASingularRelation($attribute))
                                    {
                                        $typeToUse = 'Singular';
                                    }
                                    $attributes[$modelClassName  . FormModelUtil::DELIMITER .
                                            $attribute . FormModelUtil::DELIMITER . self::DYNAMIC_RELATION_INFERRED
                                            . FormModelUtil::DELIMITER . self::RELATION_VIA_MODULE .
                                            self::RELATION_VIA_MODULE_DELIMITER . $moduleClassName] =
                                    array('label' => $moduleClassName::getModuleLabelByTypeAndLanguage($typeToUse));
                                }
                            }
                            else
                            {
                                $attributes[$modelClassName  . FormModelUtil::DELIMITER .
                                        $attribute . FormModelUtil::DELIMITER . self::DYNAMIC_RELATION_INFERRED] =
                                array('label' => $modelClassName::getModelLabelByTypeAndLanguage('Plural'));
                            }

                        }
                    }
                }
            }
            return $attributes;
        }

        public function getFilterRulesByAttribute($attribute, $ruleAttributeName)
        {
            $rules                        = array();
            $dynamicallyDerivedAttributes =  $this->getDynamicallyDerivedAttributesData();
            if($this->model->isAttribute($attribute) && $this->model->{$attribute} instanceof CurrencyValue)
            {
                $rules[]    = array($ruleAttributeName, 'type', 'type' => 'float');
            }
            elseif(in_array($attribute, $dynamicallyDerivedAttributes))
            {
                $rules[]    = array($ruleAttributeName, 'type' => 'string');
            }
            elseif($this->model->isAttribute($attribute))
            {
                $rules      = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                              getApplicableRulesByModelClassNameAndAttributeName(
                              get_class($this->model),
                              $attribute,
                              $ruleAttributeName,
                              false,
                              true,
                              false);
            }
            return $rules;
        }

        public function relationIsReportedAsAttribute($relation)
        {
            assert('is_string($relation)');
            if($this->model->isAttribute($relation) && $this->isReportedOnAsARelation($relation))
            {
                return false;
            }
            if($this->model->isAttribute($relation) && !$this->model->isRelation($relation))
            {
                return false;
            }
            if($this->isDerivedAttribute($relation))
            {
                return false;
            }
            return $this->rules->relationIsReportedAsAttribute($this->model, $relation);
        }

        public function isDerivedRelationsViaCastedUpModelRelation($relation)
        {
            assert('is_string($relation)');
            $relationsData = $this->getDerivedRelationsViaCastedUpModelData();
            if(isset($relationsData[$relation]))
            {
                return true;
            }
            return false;
        }

        public function isInferredRelation($relation)
        {
            assert('is_string($relation)');
            $relationsData = $this->getInferredRelationsData();
            if(isset($relationsData[$relation]))
            {
                return true;
            }
            return false;
        }

        public function isDynamicallyDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            $dynamicallyDerivedAttributes = $this->getDynamicallyDerivedAttributesData();
            if(isset($dynamicallyDerivedAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        protected static function resolveAttributeNameToUseForRelationWithModuleConnection($attribute, $moduleClassName)
        {
            assert('is_string($attribute)');
            assert('is_string($moduleClassName)');
            return $attribute . FormModelUtil::DELIMITER . self::RELATION_VIA_MODULE .
                   self::RELATION_VIA_MODULE_DELIMITER . $moduleClassName;

        }

        protected function getAttributesNotIncludingDerivedAttributesData()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ((($this->model->isRelation($attribute) &&
                    $this->rules->relationIsReportedAsAttribute($this->model, $attribute)) ||
                    !$this->model->isRelation($attribute) &&
                    $this->rules->attributeIsReportable($this->model, $attribute)))
                {
                    $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
                }
            }
            return $attributes;
        }

        protected function derivedRelationLinksToPrecedingRelation($relationModelClassName, $opposingRelation, RedBeanModel $precedingModel = null,
                                                                    $precedingRelation = null)
        {
            assert('is_string($relationModelClassName)');
            assert('is_string($opposingRelation)');
            if($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            if($relationModelClassName == get_class($precedingModel) && $opposingRelation == $precedingRelation)
            {
                return true;
            }
            return false;
        }

        protected function inferredRelationLinksToPrecedingRelation($inferredModelClassName, $relation, RedBeanModel $precedingModel = null,
                                                                    $precedingRelation = null)
        {
            assert('is_string($inferredModelClassName)');
            if($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            if($inferredModelClassName != get_class($precedingModel))
            {
                return false;
            }
            if($precedingModel->isADerivedRelationViaCastedUpModel($precedingRelation) &&
               $precedingModel->getDerivedRelationViaCastedUpModelOpposingRelationName($precedingRelation) == $relation)
            {
                return true;
            }
            return false;
        }

        protected function relationLinksToPrecedingRelation($relation, RedBeanModel $precedingModel = null,
                                                            $precedingRelation = null)
        {
            if($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            //Check if the relation is a derived relation in which case return false because it is handled by
            //@see self::inferredRelationLinksToPrecedingRelation
            if(!$precedingModel->isAttribute($precedingRelation))
            {
                return false;
            }
            if(get_class($precedingModel) != $this->model->getRelationmodelClassName($relation))
            {
                return false;
            }
            if( $precedingModel->getRelationLinkType($precedingRelation) == RedBeanModel::LINK_TYPE_ASSUMPTIVE &&
                $this->model->getRelationLinkType($relation) == RedBeanModel::LINK_TYPE_ASSUMPTIVE)
            {
                return true;
            }
            //Check for LINK_TYPE_SPECIFIC
            if( $precedingModel->getRelationLinkType($precedingRelation) == RedBeanModel::LINK_TYPE_SPECIFIC &&
                $this->model->getRelationLinkType($relation) == RedBeanModel::LINK_TYPE_SPECIFIC &&
                $precedingModel->getRelationLinkName($precedingRelation) == $this->model->getRelationLinkName($relation))
            {
                return true;
            }
            return false;
        }

        protected function getDerivedRelationsViaCastedUpModelData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $attributes = array();
            $metadata   = $this->model->getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($metadata[$modelClassName]["derivedRelationsViaCastedUpModel"]))
                {
                    foreach($metadata[$modelClassName]["derivedRelationsViaCastedUpModel"] as $relation => $derivedRelationData)
                    {
                        if(!$this->derivedRelationLinksToPrecedingRelation(
                            $this->model->getDerivedRelationModelClassName($relation),
                            $this->model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation),
                            $precedingModel,
                            $precedingRelation))
                        {
                            $attributes[$relation] = array('label' => $this->model->getAttributeLabel($relation));
                        }
                    }
                }
            }
            return $attributes;
        }

        protected function getDerivedAttributesData()
        {
            $attributes = array();
            $calculatedAttributes = CalculatedDerivedAttributeMetadata::getAllByModelClassName(get_class($this->model));
            foreach ($calculatedAttributes as $attribute)
            {
                $attributes[$attribute->name] = array('label' => $attribute->getLabelByLanguage(Yii::app()->language),
                                                      'derivedAttributeType' => 'CalculatedNumber');
            }
            return array_merge($attributes, $this->rules->getDerivedAttributeTypesData($this->model));
        }

        public function isDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            $derivedAttributes = $this->getDerivedAttributesData();
            if(isset($derivedAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        protected function getDynamicallyDerivedAttributesData()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    $this->model->getRelationModelClassName($attribute) == 'User')
                {
                    $attributes[$attribute . FormModelUtil::DELIMITER . self::DYNAMIC_ATTRIBUTE_USER] =
                        array('label' => $this->model->getAttributeLabel($attribute));
                }
            }
            return $attributes;
        }

        protected function getInferredRelationModelClassNamesForRelation($relation)
        {
            assert('is_string($relation)');
            $attributes = array();
            $metadata   = $this->model->getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($metadata[$modelClassName][$relation . 'ModelClassNames']))
                {
                    return $metadata[$modelClassName][$relation . 'ModelClassNames'];
                }
            }
        }

        private function resolveRelationToSelectableRelationData(& $attributes, $attribute)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            $metadata                = $this->model->getMetadata();
            $attributeModelClassName = $this->model->getAttributeModelClassName($attribute);
            if(isset($metadata[$attributeModelClassName]['relationsModuleConnections']) &&
               isset($metadata[$attributeModelClassName]['relationsModuleConnections'][$attribute]))
            {
                foreach($metadata[$attributeModelClassName]['relationsModuleConnections'][$attribute] as $moduleClassName)
                {
                    $attributeNameToUse     = self::resolveAttributeNameToUseForRelationWithModuleConnection(
                                              $attribute, $moduleClassName);
                    $typeToUse              = 'Plural';
                    if($this->isRelationASingularRelation($attribute))
                    {
                        $typeToUse = 'Singular';
                    }
                    $attributes[$attributeNameToUse] = array('label' =>
                                                       $moduleClassName::getModuleLabelByTypeAndLanguage($typeToUse));
                }
            }
            else
            {
                $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
            }
        }

        /**
         * @return real model attribute name.  Parses for Inferred, Inferred__Via, and Via.
         */
        public function resolveRealAttributeName($attribute)
        {
            assert('is_string($attribute)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $attributeAndInferredOrViaData   = explode($delimiter, $attribute);
            if(count($attributeAndInferredOrViaData) == 4)
            {
                list($notUsed, $attribute, $notUsed2, $notUsed3) = $attributeAndInferredOrViaData;
                return $attribute;
            }
            elseif(count($attributeAndInferredOrViaData) == 3)
            {
                list($modelClassName, $attribute, $notUsed) = $attributeAndInferredOrViaData;
                return $attribute;
            }
            elseif(count($attributeAndInferredOrViaData) == 2)
            {
                list($attribute, $notUsed) = $attributeAndInferredOrViaData;
                return $attribute;
            }
            else
            {
                return $attribute;
            }
        }

        /**
         * Override when some attributes can be made via select and not via the model.
         * @param $attribute
         * @return bool
         */
        public function isDisplayAttributeMadeViaSelect($attribute)
        {
            //todo: document this more
            assert('is_string($attribute)');
            return false;
        }
    }
?>