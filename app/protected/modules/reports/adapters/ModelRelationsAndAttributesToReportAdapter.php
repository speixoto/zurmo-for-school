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
        const DYNAMIC_ATTRIBUTE_USER = 'User';

        const DYNAMIC_RELATION_INFERRED = 'Inferred';

        protected $model;

        protected $rules;

        protected $report;

        public function getModel()
        {
            return $this->model;
        }

        public function __construct(RedBeanModel $model, ReportRules $rules, Report $report)
        {
            $this->model  = $model;
            $this->rules  = $rules;
            $this->report = $report;
        }

        /**
         * @return model class name.  Resolves for inferred and derived relations
         */
        public function getRelationModelClassName($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if(count($relationAndInferredData) == 2)
            {
                list($modelClassName, $notUsed) = $relationAndInferredData;
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
                            $attributes[$modelClassName  . FormModelUtil::DELIMITER . self::DYNAMIC_RELATION_INFERRED] =
                                array('label' => $modelClassName::getModelLabelByTypeAndLanguage('Plural'));
                        }
                    }
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
                $attributes[$attribute->name] = array('label' => $attribute->getLabelByLanguage(Yii::app()->language));
            }
            return array_merge($attributes, $this->rules->getDerivedAttributeTypesData($this->model));
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
    }
?>