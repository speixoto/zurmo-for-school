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
        const DYNAMIC_ATTRIBUTE_USER     = 'User';

        const DYNAMIC_RELATION_INFERRED  = 'Inferred';

        protected $model;

        protected $rules;

        protected $reportType;

        public function getModel()
        {
            return $this->model;
        }

        public static function make($moduleClassName, $modelClassName, $reportType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($reportType)');
            $rules                     = ReportRules::makeByModuleClassName($moduleClassName);
            $model                     = new $modelClassName(false);
            if($reportType == Report::TYPE_ROWS_AND_COLUMNS)
            {
                $adapter       = new ModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model, $rules, $reportType);
            }
            elseif($reportType == Report::TYPE_SUMMATION)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $reportType);
            }
            elseif($reportType == Report::TYPE_MATRIX)
            {
                $adapter       = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $reportType);
            }
            else
            {
                throw new NotSupportedException();
            }
            return $adapter;
        }

        public function __construct(RedBeanModel $model, ReportRules $rules, $reportType)
        {
            assert('is_string($reportType)');
            $this->model      = $model;
            $this->rules      = $rules;
            $this->reportType = $reportType;
        }

        /**
         *
         * Enter description here ...
         * @param string $attribute
         */
        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            $attributesData   = $this->getAttributesIncludingDerivedAttributesData();
            if(!isset($attributesData[$attribute]))
            {
                throw new NotSupportedException();
            }
            return $attributesData[$attribute]['label'];
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
        public function isRelation($relationOrAttribute)
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
         * @return model class name.  Resolves for inferred and derived relations
         */
        public function getRelationModelClassName($relation)
        {
            assert('is_string($relation)');
            assert('$this->isRelation($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredData) == 3)
            {
                list($modelClassName, $notUsed, $notUsed2) = $relationAndInferredData;
                return $modelClassName;
            }
            elseif(count($relationAndInferredData) == 1 && isset($derivedRelations[$relation]))
            {
                return $this->model->getDerivedRelationModelClassName($relation);
            }
            elseif(count($relationAndInferredData) == 1)
            {
                return $this->model->getRelationModelClassName($relation);
            }
            else
            {
                throw new NotSupportedException();
            }
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
                    $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
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
            assert('$this->isRelation($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredData) == 3)
            {
                list($modelClassName, $relation, $notUsed) = $relationAndInferredData;
                $type = $this->model->getRelationType($relation);
            }
            elseif(count($relationAndInferredData) == 1 && isset($derivedRelations[$relation]))
            {
                $type = $this->model->getDerivedRelationType($relation);
            }
            elseif(count($relationAndInferredData) == 1)
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
                    foreach($inferredRelationModelClassNames as $modelClassName)
                    {
                        if(!$this->inferredRelationLinksToPrecedingRelation($modelClassName, $attribute, $precedingModel, $precedingRelation))
                        {
                            $attributes[$modelClassName  . FormModelUtil::DELIMITER .
                                        $attribute . FormModelUtil::DELIMITER . self::DYNAMIC_RELATION_INFERRED] =
                                array('label' => $modelClassName::getModelLabelByTypeAndLanguage('Plural'));
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
                              true);
            }
            return $rules;
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
                $attributes[$attribute->name] = array('label' => $attribute->getLabelByLanguage(Yii::app()->language));
            }
            return array_merge($attributes, $this->rules->getDerivedAttributeTypesData($this->model));
        }

        protected function isDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            if(in_array($attribute, $this->getDerivedAttributesData()))
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

        protected function isDynamicallyDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            if(in_array($attribute, $this->getDynamicallyDerivedAttributesData()))
            {
                return true;
            }
            return false;
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

        public function getFilterValueElementType($attribute)
        {
            assert('is_string($attribute)');
            if($this->isDynamicallyDerivedAttribute($attribute))
            {
                return null;
            }
            if($this->isDerivedAttribute($attribute))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                if($parts[0] != 'User')
                {
                    throw NotSupportedException();
                }
                return 'User';
            }
            return ModeAttributeToReportFilterValueElementTypeUtil::getType($this->model, $attribute);
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
            return ModelAttributeToOperatorTypeUtil::getAvailableOperatorsType($this->model, $attribute);
        }
    }
?>