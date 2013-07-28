<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base helper class for managing adapting model relations and attributes into a workflow rule
     */
    class ModelRelationsAndAttributesToWorkflowAdapter
    {
        const DYNAMIC_ATTRIBUTE_USER         = 'User';

        const DYNAMIC_RELATION_INFERRED      = 'Inferred';

        /**
         * @var array for caching purposes
         */
        private static $adaptersByModelClassNameAndType;

        /**
         * @var RedBeanModel
         */
        protected $model;

        /**
         * @var WorkflowRules
         */
        protected $rules;

        /**
         * @var string
         */
        protected $workflowType;

        /**
         * @var null|string
         */
        protected $moduleClassName;

        /**
         * Caching property to improve performance
         * @var array | null
         */
        private $derivedAttributesData;

        /**
         * @param string $moduleClassName
         * @param string $modelClassName
         * @param string $workflowType
         * @return ModelRelationsAndAttributesToWorkflowAdapter
         * @throws NotSupportedException if the workflowType is invalid or null
         */
        public static function make($moduleClassName, $modelClassName, $workflowType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            if (!isset(self::$adaptersByModelClassNameAndType[$modelClassName . $workflowType]))
            {
                $rules                     = WorkflowRules::makeByModuleClassName($moduleClassName);
                $model                     = new $modelClassName(false);
                if ($workflowType == Workflow::TYPE_ON_SAVE)
                {
                    $adapter       = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules,
                                                                                             $workflowType, $moduleClassName);
                }
                elseif ($workflowType == Workflow::TYPE_BY_TIME)
                {
                    $adapter       = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules,
                                                                                             $workflowType, $moduleClassName);
                }
                else
                {
                    throw new NotSupportedException();
                }
                self::$adaptersByModelClassNameAndType[$modelClassName . $workflowType] = $adapter;
            }
            return self::$adaptersByModelClassNameAndType[$modelClassName . $workflowType];
        }

        /**
         * @return RedBeanModel
         */
        public function getModel()
        {
            return $this->model;
        }

        /**
         * @return string
         */
        public function getModelClassName()
        {
            return get_class($this->model);
        }

        /**
         * @return WorkflowRules
         */
        public function getRules()
        {
            return $this->rules;
        }

        /**
         * @param RedBeanModel $model
         * @param WorkflowRules $rules
         * @param string $workflowType
         * @param string $moduleClassName - optional for when there is a stateAdapter involved.  In the case of LeadsModule
         * it still uses the Contact model but is important to know that the originating module is Leads.  If moduleClassName
         * is not specified, then it will default to the model's moduleClassName
         */
        public function __construct(RedBeanModel $model, WorkflowRules $rules, $workflowType, $moduleClassName = null)
        {
            assert('is_string($workflowType)');
            assert('is_string($moduleClassName) || $moduleClassName == null');
            $this->model        = $model;
            $this->rules        = $rules;
            $this->workflowType = $workflowType;
            if ($moduleClassName == null)
            {
                $moduleClassName   = $model::getModuleClassName();
            }
            $this->moduleClassName = $moduleClassName;
        }

        /**
         * Enter description here ...
         * @param string $attribute
         * @throws NotSupportedException if the label is missing for the attribute
         */
        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            if ($this->isDynamicallyDerivedAttribute($attribute))
            {
                $resolvedAttribute = $attribute;
            }
            else
            {
                $resolvedAttribute = static::resolveRealAttributeName($attribute);
            }
            $attributesData    = $this->getAttributesIncludingDerivedAttributesData();
            if (!isset($attributesData[$resolvedAttribute]))
            {
                throw new NotSupportedException('Label not found for: ' . $resolvedAttribute . ' from ' . $attribute);
            }
            return $attributesData[$resolvedAttribute]['label'];
        }

        /**
         * Enter description here ...
         * @param string $relation
         * @throws NotSupportedException if the label is missing for the relation
         */
        public function getRelationLabel($relation)
        {
            assert('is_string($relation)');
            $relationsData    = $this->getSelectableRelationsData();
            if (!isset($relationsData[$relation]))
            {
                throw new NotSupportedException();
            }
            return $relationsData[$relation]['label'];
        }

        /**
         * Returns true/false if a string passed in is considered a relation from a workflow perspective. In this case
         * a dropDown is not considered a relation because it is used in workflow as a regular attribute.
         * @param string $relationOrAttribute
         * @return bool
         */
        public function isUsedAsARelation($relationOrAttribute)
        {
            assert('is_string($relationOrAttribute)');
            $relations = $this->getSelectableRelationsData();
            if (isset($relations[$relationOrAttribute]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @param string $relation
         * @return module class name.  Resolves for inferred and derived relations
         * @throws NotSupportedException if the relation string is malformed
         */
        public function getRelationModuleClassName($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if (count($relationAndInferredData) == 3)
            {
                list($modelClassName, $notUsed, $notUsed2) = $relationAndInferredData;
                return $modelClassName::getModuleClassName();
            }
            elseif (count($relationAndInferredData) == 1 && isset($derivedRelations[$relation]))
            {
                $modelClassName = $this->model->getDerivedRelationModelClassName($relation);
                return $modelClassName::getModuleClassName();
            }
            elseif (count($relationAndInferredData) == 1)
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
         * @param string $relation
         * @return model class name.  Resolves for inferred and derived relations
         * @throws NotSupportedException if the relation is malformed
         */
        public function getRelationModelClassName($relation)
        {
            assert('is_string($relation)');
            if (null !== $relationModelClassName = self::getInferredRelationModelClassName($relation))
            {
                return $relationModelClassName;
            }
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if (count($relationAndInferredData) == 1 && isset($derivedRelations[$relation]))
            {
                return $this->model->getDerivedRelationModelClassName($relation);
            }
            elseif (count($relationAndInferredData) == 1)
            {
                return $this->model->getRelationModelClassName($relation);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param $relation
         * @return mixed
         */
        public static function getInferredRelationModelClassName($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            if (count($relationAndInferredData) == 3)
            {
                list($modelClassName, $notUsed, $notUsed2) = $relationAndInferredData;
                return $modelClassName;
            }
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForTriggers()
        {
            $attributes       = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes       = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * Override and implement in children classes
         */
        public function getAttributesForTimeTrigger()
        {
            throw new NotImplementedException();
        }

        /**
         * Used by update actions since there is no difference between required and non-required.
         * @return sorted array
         */
        public function getAllAttributesForActions()
        {
            $attributes       = $this->resolveAttributesForActionsOrTimeTriggerData(true, true);
            $attributes       = array_merge($attributes,
                                $this->resolveDynamicallyDerivedAttributesForActionsOrTimeTriggerData(true, true));
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * @return sorted array
         */
        public function getRequiredAttributesForActions()
        {
            $attributes       = $this->resolveAttributesForActionsOrTimeTriggerData(true);
            $attributes       = array_merge($attributes,
                                $this->resolveDynamicallyDerivedAttributesForActionsOrTimeTriggerData(true));
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * @return sorted array
         */
        public function getNonRequiredAttributesForActions()
        {
            $attributes       = $this->resolveAttributesForActionsOrTimeTriggerData(false, true);
            $attributes       = array_merge($attributes,
                                $this->resolveDynamicallyDerivedAttributesForActionsOrTimeTriggerData(false, true));
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * @param string $attribute
         * @return null|string
         * @throws NotSupportedException if the attribute is a derived attribute
         */
        public function getAvailableOperatorsType($attribute)
        {
            assert('is_string($attribute)');
            //Currently only User is supported as a dynamically derived attributed
            if ($this->isDynamicallyDerivedAttribute($attribute))
            {
                return ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_HAS_ONE;
            }
            if ($this->isDerivedAttribute($attribute))
            {
                throw new NotSupportedException();
            }
            $resolvedAttribute = static::resolveRealAttributeName($attribute);
            if (null != $availableOperatorsTypeFromRule = $this->rules->getAvailableOperatorsTypes($this->model,
                                                                                                  $resolvedAttribute))
            {
                return $availableOperatorsTypeFromRule;
            }
            return ModelAttributeToWorkflowOperatorTypeUtil::getAvailableOperatorsType($this->model, $resolvedAttribute);
        }

        /**
         * @param string $attribute
         * @return null|string
         * @throws NotSupportedException if the attribute is dynamically derived but not a __User attribute since
         * this is the only type of dynamically derived attributes that are currently supported
         */
        public function getTriggerValueElementType($attribute)
        {
            assert('is_string($attribute)');
            if ($this->isDerivedAttribute($attribute))
            {
                return null;
            }
            if ($this->isDynamicallyDerivedAttribute($attribute))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                if ($parts[1] != 'User')
                {
                    throw new NotSupportedException();
                }
                return 'UserNameId';
            }
            $resolvedAttribute = static::resolveRealAttributeName($attribute);
            if (null != $triggerValueElementTypeFromRule = $this->rules->getTriggerValueElementType($this->model,
                                                                                                 $resolvedAttribute))
            {
                return $triggerValueElementTypeFromRule;
            }
            return ModeAttributeToWorkflowTriggerValueElementTypeUtil::getType($this->model, $resolvedAttribute);
        }

        /**
         * @param string $attribute
         * @return string
         * @throws NotSupportedException if the attribute is dynamically derived but not a __User attribute since
         * this is the only type of dynamically derived attributes that are currently supported
         */
        public function getDisplayElementType($attribute)
        {
            assert('is_string($attribute)');
            $derivedAttributes = $this->getDerivedAttributesData();
            if (isset($derivedAttributes[$attribute]))
            {
                return $derivedAttributes[$attribute]['derivedAttributeType'];
            }
            if ($this->isDynamicallyDerivedAttribute($attribute))
            {
                $parts = explode(FormModelUtil::DELIMITER, $attribute);
                if ($parts[1] != 'User')
                {
                    throw new NotSupportedException();
                }
                return 'User';
            }
            $resolvedAttribute = static::resolveRealAttributeName($attribute);
            return $this->getRealModelAttributeType($resolvedAttribute);
        }

        /**
         * @param string $attribute
         * @return string
         */
        public function getRealModelAttributeType($attribute)
        {
            assert('is_string($attribute)');
            return ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
        }

        /**
         * @return array
         */
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
         * Resolves relations to only return relations that the user has access too. always returns user relations
         * since this is ok for a user to see when creating or editing a workflow rule
         * @param User $user
         * @param array $relations
         * @return array
         * @throws NotSupportedException
         */
        public function getSelectableRelationsDataResolvedForUserAccess(User $user, Array $relations)
        {
            assert('$user->id > 0');
            foreach ($relations as $relation => $data)
            {
                if (null != $moduleClassName = $this->getRelationModuleClassName($relation))
                {
                    if ($moduleClassName != 'UsersModule' && !RightsUtil::canUserAccessModule($moduleClassName , $user))
                    {
                        unset($relations[$relation]);
                    }
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return $relations;
        }

        /**
         * Returns the array of selectable relations for creating a workflow rule.  Does not include relations that are
         * marked as cannotTrigger in the rules and also excludes relations that are marked as relations
         * usedAsAttributes by the rules.  Includes relations marked as derivedRelationsViaCastedUpModel.
         *
         * Public for testing only
         * @param RedBeanModel $precedingModel
         * @param null $precedingRelation
         * @return array
         * @throws NotSupportedException
         */
        public function getSelectableRelationsData(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if (($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    !$this->rules->relationIsUsedAsAttribute($this->model, $attribute) &&
                    $this->rules->attributeCanBeTriggered($this->model, $attribute) &&
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

        /**
         * @return array
         */
        public function getAttributesIncludingDerivedAttributesData()
        {
            $attributes = array('id' => array('label' => Zurmo::t('Core', 'Id')));
            $attributes = array_merge($attributes, $this->getAttributesNotIncludingDerivedAttributesData());
            $attributes = array_merge($attributes, $this->getDerivedAttributesData());
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        /**
         * @param string $relation
         * @return bool
         * @throws NotSupportedException if the relation string is malformed
         */
        public function isRelationASingularRelation($relation)
        {
            assert('is_string($relation)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $relationAndInferredData         = explode($delimiter, $relation);
            $derivedRelations                = $this->getDerivedRelationsViaCastedUpModelData();
            if (count($relationAndInferredData) == 3)
            {
                list($modelClassName, $relation, $notUsed) = $relationAndInferredData;
                $type = $this->model->getRelationType($relation);
            }
            elseif (count($relationAndInferredData) == 2)
            {
                list($relation, $notUsed) = $relationAndInferredData;
                $type = $this->model->getRelationType($relation);
            }
            elseif (count($relationAndInferredData) == 1 && isset($derivedRelations[$relation]))
            {
                $type = $this->model->getDerivedRelationType($relation);
            }
            elseif (count($relationAndInferredData) == 1)
            {
                $type = $this->model->getRelationType($relation);
            }
            else
            {
                throw new NotSupportedException();
            }
            if ( $type == RedBeanModel::HAS_ONE ||
                $type == RedBeanModel::HAS_ONE_BELONGS_TO ||
                $type == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                return true;
            }
            return false;
        }

        /**
         * @param RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @param null|string $onlyIncludeThisModelClassName
         * @return array
         * @throws NotSupportedException if there the preceding model and relation are not either both defined or both
         * null
         */
        public function getInferredRelationsData(RedBeanModel $precedingModel = null, $precedingRelation = null,
                                                 $onlyIncludeThisModelClassName = null)
        {
            if (($precedingModel != null && $precedingRelation == null) ||
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
                    foreach ($inferredRelationModelClassNames as $modelClassName)
                    {
                        if ($onlyIncludeThisModelClassName === null || $onlyIncludeThisModelClassName == $modelClassName)
                        {
                            if (!$this->inferredRelationLinksToPrecedingRelation($modelClassName,
                                $attribute, $precedingModel, $precedingRelation))
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

        /**
         * @param string $attribute
         * @param string $ruleAttributeName
         * @return array
         */
        public function getTriggerRulesByAttribute($attribute, $ruleAttributeName)
        {
            assert('is_string($attribute)');
            assert('is_string($ruleAttributeName)');
            $rules                        = array();
            $dynamicallyDerivedAttributes =  $this->getDynamicallyDerivedAttributesData();
            if ($this->model->isAttribute($attribute) && $this->model->{$attribute} instanceof CurrencyValue)
            {
                $rules[]    = array($ruleAttributeName, 'type', 'type' => 'float');
            }
            elseif (in_array($attribute, $dynamicallyDerivedAttributes))
            {
                $rules[]    = array($ruleAttributeName, 'type' => 'string');
            }
            elseif ($this->model->isAttribute($attribute))
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

        /**
         * @param string $relation
         * @return bool
         */
        public function relationIsUsedAsAttribute($relation)
        {
            assert('is_string($relation)');
            if ($this->model->isAttribute($relation) && $this->isUsedAsARelation($relation))
            {
                return false;
            }
            if ($this->model->isAttribute($relation) && !$this->model->isRelation($relation))
            {
                return false;
            }
            if ($this->isDerivedAttribute($relation))
            {
                return false;
            }
            return $this->rules->relationIsUsedAsAttribute($this->model, $relation);
        }

        /**
         * @param string $relation
         * @return bool
         */
        public function isDerivedRelationsViaCastedUpModelRelation($relation)
        {
            assert('is_string($relation)');
            $relationsData = $this->getDerivedRelationsViaCastedUpModelData();
            if (isset($relationsData[$relation]))
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $relation
         * @return bool
         */
        public function isInferredRelation($relation)
        {
            assert('is_string($relation)');
            $relationsData = $this->getInferredRelationsData();
            if (isset($relationsData[$relation]))
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $attribute
         * @return bool
         */
        public function isDynamicallyDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            $dynamicallyDerivedAttributes = $this->getDynamicallyDerivedAttributesData();
            if (isset($dynamicallyDerivedAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $attribute
         * @return bool
         */
        public function isAttributeReadOptimization($attribute)
        {
            assert('is_string($attribute)');
            if ($attribute == 'ReadOptimization')
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $attribute
         * @return bool
         */
        public function isDerivedAttribute($attribute)
        {
            assert('is_string($attribute)');
            $derivedAttributes = $this->getDerivedAttributesData();
            if (isset($derivedAttributes[$attribute]))
            {
                return true;
            }
            return false;
        }

        /**
         * @param string attribute
         * @return real model attribute name.  Parses for Inferred
         */
        public static function resolveRealAttributeName($attribute)
        {
            assert('is_string($attribute)');
            $delimiter                       = FormModelUtil::DELIMITER;
            $attributeAndInferredData   = explode($delimiter, $attribute);
            if (count($attributeAndInferredData) == 3)
            {
                list($modelClassName, $attribute, $notUsed) = $attributeAndInferredData;
                return $attribute;
            }
            elseif (count($attributeAndInferredData) == 2)
            {
                list($attribute, $notUsed) = $attributeAndInferredData;
                return $attribute;
            }
            else
            {
                return $attribute;
            }
        }

        /**
         * @param RedBeanModel $precedingModel
         * @param null $precedingRelation
         * @return array $sortedAttributes
         * @throws NotSupportedException
         */
        public function getSelectableRelationsDataForTriggers(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if (($precedingModel != null && $precedingRelation == null) ||
                ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $onlyIncludeOwnedRelations = false;
            if (($precedingModel != null && $precedingRelation != null))
            {
                $onlyIncludeOwnedRelations = true;
            }
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    !$this->rules->relationIsUsedAsAttribute($this->model, $attribute) &&
                    $this->rules->attributeCanBeTriggered($this->model, $attribute) &&
                    !$this->relationLinksToPrecedingRelation($attribute, $precedingModel, $precedingRelation) &&
                    (!$onlyIncludeOwnedRelations ||
                        ($onlyIncludeOwnedRelations && $this->model->isOwnedRelation($attribute)))
                )
                {
                    $this->resolveRelationToSelectableRelationData($attributes, $attribute);
                }
            }
            if (!$onlyIncludeOwnedRelations)
            {
                $attributes = array_merge($attributes, $this->getDerivedRelationsViaCastedUpModelData($precedingModel, $precedingRelation));
                $attributes = array_merge($attributes, $this->getInferredRelationsData($precedingModel, $precedingRelation));
            }
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * Exclude User relations and Owned relations.
         * Utilized by @see DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm
         * @return sorted array
         */
        public function getSelectableRelationsDataForEmailMessageRecipientModelRelation()
        {
            return $this->getSelectableRelationsDataForActionTypeRelation();
        }

        /**
         * Only includes relations that are to the 'Contact' model
         * Utilized by @see DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm
         * @return sorted array
         */
        public function getSelectableContactRelationsDataForEmailMessageRecipientModelRelation()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    !$this->rules->relationIsUsedAsAttribute($this->model, $attribute) &&
                    $this->rules->attributeCanBeTriggered($this->model, $attribute)  &&
                    !$this->model->isOwnedRelation($attribute) &&
                    $this->model->getRelationModelClassName($attribute) == 'Contact')
                {
                    $this->resolveRelationToSelectableRelationData($attributes, $attribute);
                }
            }
            $attributes = array_merge($attributes, $this->getDerivedRelationsViaCastedUpModelData(null, null, 'Contact'));
            $attributes = array_merge($attributes, $this->getInferredRelationsData(null, null, 'Contact'));
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * Exclude User relations and Owned relations.
         * @return sorted array
         */
        public function getSelectableRelationsDataForActionTypeRelation()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute) &&
                    !$this->rules->relationIsUsedAsAttribute($this->model, $attribute) &&
                    $this->rules->attributeCanBeTriggered($this->model, $attribute)  &&
                    !$this->model->isOwnedRelation($attribute) &&
                    $this->model->getRelationModelClassName($attribute) != 'User')
                {
                    $this->resolveRelationToSelectableRelationData($attributes, $attribute);
                }
            }
            $attributes = array_merge($attributes, $this->getDerivedRelationsViaCastedUpModelData());
            $attributes = array_merge($attributes, $this->getInferredRelationsData());
            $sortedAttributes = ArrayUtil::subValueSort($attributes, 'label', 'asort');
            return $sortedAttributes;
        }

        /**
         * @return array
         */
        protected function getAttributesNotIncludingDerivedAttributesData()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ((($this->model->isRelation($attribute) &&
                    $this->rules->relationIsUsedAsAttribute($this->model, $attribute)) ||
                    !$this->model->isRelation($attribute) &&
                    $this->rules->attributeCanBeTriggered($this->model, $attribute)))
                {
                    $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
                }
            }
            return $attributes;
        }

        /**
         * @param boolean $includeRequired
         * @param boolean $includeNonRequired
         * @param $includeReadOnly
         * @return array
         */
        protected function resolveAttributesForActionsOrTimeTriggerData($includeRequired = false,
                                                                        $includeNonRequired = false,
                                                                        $includeReadOnly = false)

        {
            assert('is_bool($includeRequired)');
            assert('is_bool($includeNonRequired)');
            assert('is_bool($includeReadOnly)');
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ((!$this->model->isAttributeReadOnly($attribute) || $includeReadOnly) &&
                    (($this->model->isRelation($attribute) &&
                    $this->rules->relationIsUsedAsAttribute($this->model, $attribute)) ||
                    !$this->model->isRelation($attribute) &&
                        $this->rules->attributeCanBeTriggered($this->model, $attribute)))
                {
                    $attributeIsRequired = $this->model->isAttributeRequired($attribute);
                    if (($includeNonRequired && !$attributeIsRequired) ||
                       ($includeRequired    && $attributeIsRequired))
                    {
                        $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
                    }
                }
            }
            return $attributes;
        }

        /**
         * @param string $relationModelClassName
         * @param string $opposingRelation
         * @param null|RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @return bool
         */
        protected function derivedRelationLinksToPrecedingRelation($relationModelClassName, $opposingRelation,
                                                                   RedBeanModel $precedingModel = null,
                                                                   $precedingRelation = null)
        {
            assert('is_string($relationModelClassName)');
            assert('is_string($opposingRelation)');
            if ($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            if ($relationModelClassName == get_class($precedingModel) && $opposingRelation == $precedingRelation)
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $inferredModelClassName
         * @param string $relation
         * @param null|RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @return bool
         */
        protected function inferredRelationLinksToPrecedingRelation($inferredModelClassName, $relation,
                                                                    RedBeanModel $precedingModel = null,
                                                                    $precedingRelation = null)
        {
            assert('is_string($inferredModelClassName)');
            assert('is_string($relation)');
            if ($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            if ($inferredModelClassName != get_class($precedingModel))
            {
                return false;
            }
            if ($precedingModel->isADerivedRelationViaCastedUpModel($precedingRelation) &&
               $precedingModel->getDerivedRelationViaCastedUpModelOpposingRelationName($precedingRelation) == $relation)
            {
                return true;
            }
            return false;
        }

        /**
         * @param string $relation
         * @param null|RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @return bool
         */
        protected function relationLinksToPrecedingRelation($relation, RedBeanModel $precedingModel = null,
                                                            $precedingRelation = null)
        {
            assert('is_string($relation)');
            if ($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            //Check if the relation is a derived relation in which case return false because it is handled by
            //@see self::inferredRelationLinksToPrecedingRelation
            if (!$precedingModel->isAttribute($precedingRelation))
            {
                return false;
            }
            if (get_class($precedingModel) != $this->model->getRelationmodelClassName($relation))
            {
                return false;
            }
            if ( $precedingModel->getRelationLinkType($precedingRelation) == RedBeanModel::LINK_TYPE_ASSUMPTIVE &&
                $this->model->getRelationLinkType($relation) == RedBeanModel::LINK_TYPE_ASSUMPTIVE)
            {
                return true;
            }
            //Check for LINK_TYPE_SPECIFIC
            if ( $precedingModel->getRelationLinkType($precedingRelation) == RedBeanModel::LINK_TYPE_SPECIFIC &&
                $this->model->getRelationLinkType($relation) == RedBeanModel::LINK_TYPE_SPECIFIC &&
                $precedingModel->getRelationLinkName($precedingRelation) == $this->model->getRelationLinkName($relation))
            {
                return true;
            }
            return false;
        }

        /**
         * @param RedBeanModel $precedingModel
         * @param null|string $precedingRelation
         * @param null|string $onlyIncludeThisModelClassName
         * @return array
         * @throws NotSupportedException if there the preceding model and relation are not either both defined or both
         * null
         */
        protected function getDerivedRelationsViaCastedUpModelData(RedBeanModel $precedingModel = null, $precedingRelation = null,
                                                                   $onlyIncludeThisModelClassName = null)
        {
            assert('$onlyIncludeThisModelClassName  === null || is_string($onlyIncludeThisModelClassName)');
            if (($precedingModel != null && $precedingRelation == null) ||
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
                    foreach ($metadata[$modelClassName]["derivedRelationsViaCastedUpModel"] as $relation => $derivedRelationData)
                    {
                        $relationModelClassName = $this->model->getDerivedRelationModelClassName($relation);
                        if ($onlyIncludeThisModelClassName === null || $relationModelClassName == $onlyIncludeThisModelClassName)
                        {
                            if (!$this->derivedRelationLinksToPrecedingRelation(
                                $relationModelClassName,
                                $this->model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation),
                                $precedingModel,
                                $precedingRelation))
                            {
                                $attributes[$relation] = array('label' => $this->model->getAttributeLabel($relation));
                            }
                        }
                    }
                }
            }
            return $attributes;
        }

        /**
         * @return array|null
         */
        protected function getDerivedAttributesData()
        {
            if ($this->derivedAttributesData == null)
            {
                $attributes = array();
                $calculatedAttributes = CalculatedDerivedAttributeMetadata::getAllByModelClassName(get_class($this->model));
                foreach ($calculatedAttributes as $attribute)
                {
                    $attributes[$attribute->name] = array('label' => $attribute->getLabelByLanguage(Yii::app()->language),
                                                          'derivedAttributeType' => 'CalculatedNumber');
                }
                $this->derivedAttributesData = array_merge($attributes, $this->rules->getDerivedAttributeTypesData($this->model));
            }
            return $this->derivedAttributesData;
        }

        /**
         * @return array
         */
        protected function getDynamicallyDerivedAttributesData()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if (!$this->model instanceof User &&
                     $this->model->isRelation($attribute) &&
                     $this->model->getRelationModelClassName($attribute) == 'User')
                {
                    $attributes[$attribute . FormModelUtil::DELIMITER . self::DYNAMIC_ATTRIBUTE_USER] =
                        array('label' => $this->model->getAttributeLabel($attribute));
                }
            }
            return $attributes;
        }

        /**
         * @param boolean $includeRequired
         * @param boolean $includeNonRequired
         * @param $includeReadOnly
         * @return array
         */
        protected function resolveDynamicallyDerivedAttributesForActionsOrTimeTriggerData($includeRequired = false,
                                                                                          $includeNonRequired = false,
                                                                                          $includeReadOnly = false)

        {
            assert('is_bool($includeRequired)');
            assert('is_bool($includeNonRequired)');
            assert('is_bool($includeReadOnly)');
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if (!$this->model instanceof User &&
                    $this->model->isRelation($attribute) &&
                    (!$this->model->isAttributeReadOnly($attribute) || $includeReadOnly) &&
                    $this->model->getRelationModelClassName($attribute) == 'User')
                {
                    $attributeIsRequired = $this->model->isAttributeRequired($attribute);
                    if (($includeNonRequired && !$attributeIsRequired) ||
                       ($includeRequired    && $attributeIsRequired))
                    {
                        $attributes[$attribute . FormModelUtil::DELIMITER . self::DYNAMIC_ATTRIBUTE_USER] =
                            array('label' => $this->model->getAttributeLabel($attribute));
                    }
                }
                if ($this->model->isRelation($attribute) && $this->model->isOwnedRelation($attribute) &&
                   $this->isRelationASingularRelation($attribute) &&
                    ($this->model->getRelationModelClassName($attribute) == 'Address' ||
                     $this->model->getRelationModelClassName($attribute) == 'Email'))
                {
                    $relatedModel = $this->model->$attribute;
                    //Assumes only Email or Address are possible owned models here
                    $zurmoRules   = WorkflowRules::makeByModuleClassName('ZurmoModule');
                    foreach ($relatedModel->getAttributes() as $relatedAttribute => $notUsed)
                    {
                        if (!$relatedModel->isAttributeReadOnly($relatedAttribute) &&
                            !$relatedModel->isRelation($relatedAttribute) &&
                            $zurmoRules->attributeCanBeTriggered($relatedModel, $relatedAttribute))
                        {
                            $relatedAttributeIsRequired = $relatedModel->isAttributeRequired($relatedAttribute);
                            if (($includeNonRequired  && !$relatedAttributeIsRequired) ||
                                ($includeRequired    && $relatedAttributeIsRequired))
                            {
                                $attributes[$attribute . FormModelUtil::RELATION_DELIMITER . $relatedAttribute] =
                                    array('label' => $this->model->getAttributeLabel($attribute)
                                                     . ' ' . ComponentForWorkflowForm::DISPLAY_LABEL_RELATION_DIVIDER
                                                     . ' ' . $relatedModel->getAttributeLabel($relatedAttribute));
                            }
                        }
                    }
                }
            }
            return $attributes;
        }

        /**
         * @param $relation
         * @return null|string
         */
        protected function getInferredRelationModelClassNamesForRelation($relation)
        {
            assert('is_string($relation)');
            return $this->model->getInferredRelationModelClassNamesForRelation($relation);
        }

        /**
         * @param array $attributes
         * @param string $attribute
         */
        protected function resolveRelationToSelectableRelationData(& $attributes, $attribute)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
        }
    }
?>