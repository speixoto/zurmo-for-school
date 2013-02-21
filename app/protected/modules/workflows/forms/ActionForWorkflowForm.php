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
         * Type of chart
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
         * @var array of WorkflowActionAttributeForms indexed by attributeNames
         */
        private $_attributes;

        /**
         * @var string string references the modelClassName of the workflow itself
         */
        private $_modelClassName;

        /**
         * @param string $modelClassName
         */
        public function __construct($modelClassName)
        {
            assert('is_string($modelClassName)');
            $this->_modelClassName = $modelClassName;
        }

        /**
         * @return int
         */
        public function getAttributeFormsCount()
        {
            return count($this->attributes);
        }

        /**
         * @param $attribute
         * @return mixed
         * @throws NotFoundException if the attribute does not exist
         */
        public function getAttributeFormByName($attribute)
        {
            assert('is_string($attribute)');
            if(!isset($this->attributes[$attribute]))
            {
                throw new NotFoundException();
            }
            else
            {
                return $this->attributes[$attribute];
            }
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
                array('relation',                    'validateType'),
                array('relationFilter',  	 	 'type', 'type' => 'string'),
                array('relationModelRelation',   'type', 'type' => 'string'),
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
        public function setAttributes($values, $safeOnly=true)
        {
            $valuesAttributes = null;
            if(isset($values['attributes']))
            {
                $valuesAttributes = $values['attributes'];
                unset($values['attributes']);
            }
            else
            {
                $this->_attributes = array();
            }
            parent::setAttributes($values, $safeOnly);
            if($valuesAttributes != null)
            {
                foreach($valuesAttributes as $attribute => $attributeData)
                {
                    $resolvedAttributeName  = $this->resolveRealAttributeName($attribute);
                    $resolvedModelClassName = $this->resolveRealModelClassName($attribute);
                    $form = WorkflowActionAttributeFormFactory::make($resolvedModelClassName, $resolvedAttributeName);
                    $form->setAttributes($attributeData);
                    $this->_attributes[$attribute] = $form;
                }
            }
        }

        /**
         * @return bool
         */
        public function validateType()
        {
            if($this->type == self::TYPE_UPDATE || $this->type == self::TYPE_CREATE ||
               $this->type == self::TYPE_UPDATE_RELATED || $this->type == self::TYPE_CREATE_RELATED)
            {
                return true;
            }
            $this->addError('type', Zurmo::t('WorkflowModule', 'Invalid Type'));
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
                $this->addError('relation', Zurmo::t('WorkflowModule', 'Relation cannot be blank.'));
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
                $this->addError('relationFilter', Zurmo::t('WorkflowModule', 'Invalid Relation Filter'));
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
                $this->addError('relatedModelRelation', Zurmo::t('WorkflowModule', 'Related Model Relation cannot be blank.'));
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
            $count            = 0;
            foreach($this->_attributes as $attributeName => $workflowActionAttributeForm)
            {
                if(!$workflowActionAttributeForm->validate())
                {
                    foreach($workflowActionAttributeForm->getErrors() as $attribute => $error)
                    {
                        $attributePrefix = static::resolveErrorAttributePrefix($attributeName, $count);
                        $this->addError( $attributePrefix . $attribute, $error);
                    }
                    $passedValidation = false;
                }
                $count ++;
            }
            return $passedValidation;
        }

        /**
         * @param $attributeName string
         * @param $count integer
         * @return string
         */
        protected static function resolveErrorAttributePrefix($attributeName, $count)
        {
            assert('is_string($attributeName)');
            assert('is_int($count)');
            return $attributeName . '_' . $count . '_';
        }

        /**
         * @param string attribute
         * @return real model attribute name.  Parses for primaryAddress___street1 for example
         * @throws NotSupportedException() if invalid $attribute string
         */
        protected function resolveRealAttributeName($attribute)
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
                return $attribute;
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
            //todo: once performance3 is done, don't need to instantiate this
            $modelClassName = $this->_modelClassName;
            $model = new $modelClassName(false);
            if($this->type == self::TYPE_UPDATE_SELF)
            {
                return $model;
            }
            elseif($this->type == self::TYPE_UPDATE_RELATED || $this->type == self::TYPE_UPDATE_RELATED)
            {
                $relationModelClassName = $model->getRelationModelClassName($this->relation);
                return new $relationModelClassName(false);
            }
            elseif($this->type == self::TYPE_CREATE_RELATED)
            {
                $relationModelClassName = $model->getRelationModelClassName($this->relation);
                $relationModel          = new $relationModelClassName(false);
                $relationModelRelatedModelClassName = $relationModel->getRelationModelClassName($this->relatedModelRelation);
                return new $relationModelRelatedModelClassName(false);
            }
            throw new NotSupportedException();
        }
    }
?>