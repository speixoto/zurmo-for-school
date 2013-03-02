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
     * Class that defines the actions used for a workflow
     */
    class ActionForWorkflowForm extends ConfigurableMetadataModel
    {
        /**
         * This action is if you trigger an account and then update attributes in that same account for example.
         */
        const TYPE_UPDATE_SELF      = 'Update';

        /**
         * This action is if you trigger an account and then update attributes in the related contacts
         */
        const TYPE_UPDATE_RELATED   = 'UpdateRelated';

        /**
         * This action is if you trigger an account and then create a related task
         */
        const TYPE_CREATE           = 'Create';

        /**
         * This action is if you trigger an account and then create a task against a related contact
         */
        const TYPE_CREATE_RELATED   = 'CreateRelated';

        /**
         * When performing actions on related models, if there are MANY related models RELATION_FILTER_ALL means the
         * action will be performed on all related models
         */
        const RELATION_FILTER_ALL   = 'RelationFilterAll';

        /**
         * Utilized by arrays to define the element that is for the actionAttributes
         */
        const ACTION_ATTRIBUTES     = 'ActionAttributes';
        /**
         * Type of action
         * @var string
         */
        public $type;

        /**
         * If the type is TYPE_UPDATE_RELATED TYPE_CREATE, or TYPE_CREATE_RELATED, the relation is required. It defines the model's relation
         * name to be used.
         * @var string
         */
        public $relation;

        /**
         * self::RELATION_FILTER_ALL is the only supported value.  Eventually additional support will be added to filter
         * related models.  An example is if you are creating a workflow on Account.  And you want to modify related opportunities.
         * Currently you can only modify all opportunities.
         * @var relationFilter
         */
        public $relationFilter;

        /**
         * If the type is TYPE_CREATE_RELATED, the relationModelRelation is required. An example is Create a contact's related
         * account's opportunity.  So the relation is accounts and the relatedModelRelation is opportunity
         * @var relationModelRelation
         */
        public $relatedModelRelation;

        /**
         * @var string
         */
        private $_workflowType;

        /**
         * @var array of WorkflowActionAttributeForms indexed by attributeNames
         */
        private $_actionAttributes = array();

        /**
         * @var string string references the modelClassName of the workflow itself
         */
        private $_modelClassName;

        /**
         * @return array
         */
        public static function getTypeDataAndLabels()
        {
            return array(
                self::TYPE_UPDATE_SELF    => Zurmo::t('WorkflowsModule', 'Update'),
                self::TYPE_UPDATE_RELATED => Zurmo::t('WorkflowsModule', 'Update Related'),
                self::TYPE_CREATE         => Zurmo::t('WorkflowsModule', 'Create'),
                self::TYPE_CREATE_RELATED => Zurmo::t('WorkflowsModule', 'Create Related'),
            );
        }

        public static function getTypeRelationDataAndLabels($moduleClassName, $modelClassName, $workflowType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $adapter        = ModelRelationsAndAttributesToWorkflowAdapter::make($moduleClassName,
                              $modelClassName, $workflowType);
            $relationsData  = $adapter->getSelectableRelationsDataForActionTypeRelation();
            $dataAndLabels  = array();
            foreach($relationsData as $relation => $data)
            {
                $dataAndLabels[$relation] = $data['label'];
            }
            return $dataAndLabels;
        }

        public static function getTypeRelatedModelRelationDataAndLabels($moduleClassName, $modelClassName, $workflowType, $relation)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            assert('is_string($relation)');
            $adapter        = ModelRelationsAndAttributesToWorkflowAdapter::make($moduleClassName,
                              $modelClassName, $workflowType);
            $relatedadapter = ModelRelationsAndAttributesToWorkflowAdapter::make(
                              $adapter->getRelationModuleClassName($relation),
                              $adapter->getRelationModelClassName($relation), $workflowType);
            $relationsData  = $relatedadapter->getSelectableRelationsDataForActionTypeRelation();
            $dataAndLabels  = array();
            foreach($relationsData as $relation => $data)
            {
                $dataAndLabels[$relation] = $data['label'];
            }
            return $dataAndLabels;
        }

        public function resolveAllRequiredActionAttributeFormsAndLabelsAndSort()
        {
            return $this->resolveActionAttributeFormsAndLabelsAndSortByMethod('getRequiredAttributesForActions');
        }

        public function resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort()
        {
            return $this->resolveActionAttributeFormsAndLabelsAndSortByMethod('getNonRequiredAttributesForActions');
        }

        /**
         * @param string $modelClassName
         */
        public function __construct($modelClassName, $workflowType)
        {
            assert('is_string($modelClassName)');
            assert('is_string($workflowType)');
            $this->_modelClassName = $modelClassName;
            $this->_workflowType   = $workflowType;
        }

        /**
         * @return int
         */
        public function getActionAttributeFormsCount()
        {
            return count($this->_actionAttributes);
        }

        /**
         * @param $attribute
         * @return bool
         */
        public function hasActionAttributeFormByName($attribute)
        {
            assert('is_string($attribute)');
            if(!isset($this->_actionAttributes[$attribute]))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        /**
         * @param $attribute
         * @return mixed
         * @throws NotFoundException if the attribute does not exist
         */
        public function getActionAttributeFormByName($attribute)
        {
            assert('is_string($attribute)');
            if(!isset($this->_actionAttributes[$attribute]))
            {
                throw new NotFoundException();
            }
            else
            {
                return $this->_actionAttributes[$attribute];
            }
        }

        public function getActionAttributesAttributeFormType($attribute)
        {
            assert('is_string($attribute)');
            $resolvedAttributeName  = static::resolveRealAttributeName($attribute);
            $resolvedModelClassName = $this->resolveRealModelClassName($attribute);
            return WorkflowActionAttributeFormFactory::getType($resolvedModelClassName, $resolvedAttributeName);
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('type',                    'required'),
                array('type',                    'type', 'type' => 'string'),
                array('type',                    'validateType'),
                array('relation',  	 		     'type', 'type' => 'string'),
                array('relation',                'validateRelation'),
                array('relationFilter',  	 	 'type', 'type' => 'string'),
                array('relationFilter',          'validateRelationFilter'),
                array('relatedModelRelation',    'type', 'type' => 'string'),
                array('relatedModelRelation',    'validateRelatedModelRelation'),
            ));
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array();
        }

        /**
         * Process all attributes except 'attributes' first since the 'attributes' requires the 'type' to be set
         * @param $values
         * @param bool $safeOnly
         */
        public function setAttributes($values, $safeOnly = true)
        {
            $valuesAttributes = null;
            if(isset($values[self::ACTION_ATTRIBUTES]))
            {
                $valuesAttributes = $values[self::ACTION_ATTRIBUTES];
                unset($values[self::ACTION_ATTRIBUTES]);
                $this->_actionAttributes = array();
            }
            parent::setAttributes($values, $safeOnly);
            if($valuesAttributes != null)
            {
                foreach($valuesAttributes as $attribute => $attributeData)
                {
                    $resolvedAttributeName  = static::resolveRealAttributeName($attribute);
                    $resolvedModelClassName = static::resolveRealModelClassName($attribute, $this->_modelClassName,
                                              $this->type, $this->relation, $this->relatedModelRelation);
                    $form = WorkflowActionAttributeFormFactory::make($resolvedModelClassName, $resolvedAttributeName);
                    $form->setAttributes($attributeData);
                    if($form->shouldSetValue)
                    {
                        $this->_actionAttributes[$attribute] = $form;
                    }
                }
            }
        }

        /**
         * @return bool
         */
        public function validateType()
        {
            if($this->type == self::TYPE_UPDATE_SELF || $this->type == self::TYPE_CREATE ||
               $this->type == self::TYPE_UPDATE_RELATED || $this->type == self::TYPE_CREATE_RELATED)
            {
                return true;
            }
            $this->addError('type', Zurmo::t('WorkflowsModule', 'Invalid Type'));
            return false;
        }

        /**
         * @return bool
         */
        public function validateRelation()
        {
            if($this->type == self::TYPE_CREATE || $this->type == self::TYPE_UPDATE_RELATED ||
               $this->type == self::TYPE_CREATE_RELATED)
            {
                if(!empty($this->relation))
                {
                    return true;
                }
                $this->addError('relation', Zurmo::t('WorkflowsModule', 'Relation cannot be blank.'));
                return false;
            }
            return true;
        }

        /**
         * @return bool
         */
        public function validateRelationFilter()
        {
            if($this->type == self::TYPE_UPDATE_RELATED || $this->type == self::TYPE_CREATE_RELATED)
            {
                if($this->relationFilter == self::RELATION_FILTER_ALL)
                {
                    return true;
                }
                $this->addError('relationFilter', Zurmo::t('WorkflowsModule', 'Invalid Relation Filter'));
                return false;
            }
            return true;
        }

        /**
         * @return bool
         */
        public function validateRelatedModelRelation()
        {
            if($this->type == self::TYPE_CREATE_RELATED)
            {
                if(!empty($this->relatedModelRelation))
                {
                    return true;
                }
                $this->addError('relatedModelRelation', Zurmo::t('WorkflowsModule', 'Related Model Relation cannot be blank.'));
                return false;
            }
            return true;
        }

        /**
         * @return bool
         */
        public function beforeValidate()
        {
            if(!$this->validateAttributes())
            {
                return false;
            }
            return parent::beforeValidate();
        }

        /**
         * @return bool
         */
        public function validateAttributes()
        {
            $passedValidation = true;
            foreach($this->_actionAttributes as $attributeName => $workflowActionAttributeForm)
            {
                if(!$workflowActionAttributeForm->validate())
                {
                    foreach($workflowActionAttributeForm->getErrors() as $attribute => $errorArray)
                    {
                        assert('is_array($errorArray)');
                        $attributePrefix = static::resolveErrorAttributePrefix($attributeName);
                        $this->addError( $attributePrefix . $attribute, $errorArray[0]);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        protected function makeActionAttributeFormByAttribute($attribute)
        {
            assert('is_string($attribute)');
            $resolvedAttributeName  = static::resolveRealAttributeName($attribute);
            $resolvedModelClassName = static::resolveRealModelClassName($attribute, $this->_modelClassName,
                                      $this->type, $this->relation, $this->relatedModelRelation);
            return WorkflowActionAttributeFormFactory::make($resolvedModelClassName, $resolvedAttributeName);
        }

        /**
         * @param $attributeName string
         * @return string
         */
        protected static function resolveErrorAttributePrefix($attributeName)
        {
            assert('is_string($attributeName)');
            return self::ACTION_ATTRIBUTES . '_' .  $attributeName . '_';
        }

        /**
         * Resolves the real attribute for dynamic attributes such as owner__User or primaryAddress___street2
         * @param string attribute
         * @return real model attribute name.  Parses for primaryAddress___street1 for example
         * @throws NotSupportedException() if invalid $attribute string
         */
        protected static function resolveRealAttributeName($attribute)
        {
            assert('is_string($attribute)');
            $delimiter                  = FormModelUtil::RELATION_DELIMITER;
            $attributeAndRelationData   = explode($delimiter, $attribute);
            if(count($attributeAndRelationData) == 2)
            {
                list($notUsed, $attribute) =  $attributeAndRelationData;
                return $attribute;
            }
            elseif(count( $attributeAndRelationData) == 1)
            {
                //resolve for owner__User for example.
                $delimiter                  = FormModelUtil::DELIMITER;
                $attributeAndDynamicData    = explode($delimiter, $attribute);
                if(count($attributeAndDynamicData) == 2)
                {
                    list($attribute, $notUsed) =  $attributeAndDynamicData;
                    return $attribute;
                }
                else
                {
                    return $attribute;
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param string attribute
         * @return real model class name.  Parses for primaryAddress___street1 for example
         * @throws NotSupportedException() if invalid $attribute string
         */
        protected function resolveRealModelClassName($attribute)
        {
            assert('is_string($attribute)');
            $delimiter                  = FormModelUtil::RELATION_DELIMITER;
            $attributeAndRelationData   = explode($delimiter, $attribute);
            $model                      = $this->makeModelAndResolveForRelations();
            if(count($attributeAndRelationData) == 2)
            {
                list($relation, $notUsed) =  $attributeAndRelationData;
                return $model->getRelationModelClassName($relation);
            }
            elseif(count( $attributeAndRelationData) == 1)
            {
                return get_class($model);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function makeModelAndResolveForRelations()
        {
            $modelClassName = $this->getModelClassNameAndResolveForRelations();
            return new $modelClassName(false);
        }

        protected function getModelClassNameAndResolveForRelations()
        {
            //todo: once performance3 is done, don't need to instantiate this
            $modelClassName = $this->_modelClassName;
            $model = new $modelClassName(false);
            if($this->type == self::TYPE_UPDATE_SELF)
            {
                return $modelClassName;
            }
            elseif($this->type == self::TYPE_UPDATE_RELATED || $this->type == self::TYPE_CREATE)
            {
                return $model->getRelationModelClassName($this->relation);
            }
            elseif($this->type == self::TYPE_CREATE_RELATED)
            {
                $relationModelClassName = $model->getRelationModelClassName($this->relation);
                $relationModel          = new $relationModelClassName(false);
                return $relationModel->getRelationModelClassName($this->relatedModelRelation);
            }
            throw new NotSupportedException();
        }

        protected function resolveActionAttributeFormsAndLabelsAndSortByMethod($methodToCall)
        {
            assert('$methodToCall == "getNonRequiredAttributesForActions" || $methodToCall == "getRequiredAttributesForActions"');
            $modelClassName                   = $this->getModelClassNameAndResolveForRelations();
            $attributeFormsIndexedByAttribute = array();
            $adapter = ModelRelationsAndAttributesToWorkflowAdapter::make($modelClassName::getModuleClassName(),
                                                                          $modelClassName, $this->_workflowType);
            foreach($adapter->$methodToCall() as $attribute => $data)
            {
                if($this->hasActionAttributeFormByName($attribute))
                {
                    $attributeFormsIndexedByAttribute[$attribute] = $this->getActionAttributeFormByName($attribute);
                }
                else
                {
                    $attributeFormsIndexedByAttribute[$attribute] = $this->makeActionAttributeFormByAttribute($attribute);
                }
                if($methodToCall == 'getRequiredAttributesForActions')
                {
                    $attributeFormsIndexedByAttribute[$attribute]->shouldSetValue = true;
                }
                $attributeFormsIndexedByAttribute[$attribute]->setDisplayLabel($data['label']);
            }
            return $attributeFormsIndexedByAttribute;
        }
    }
?>