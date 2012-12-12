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
     * Adapts RedBeanModel information based on a specific model attribute, and optionally a specific related attribute
     * into data provider ready information.
     */
    class RedBeanModelAttributeToDataProviderAdapter
    {
        /**
         * @var string
         */
        protected $modelClassName;

        /**
         * @var string
         */
        protected $attribute;

        /**
         * If the attribute specified is a relation on the $modelClassName, then a related attribute can be specified.
         * @var string
         */
        protected $relatedAttribute;

        /**
         * RedBeanModel of $this->modelClassName
         * @var RedBeanModel
         */
        protected $model;

        private $relatedModel;

        /**
         * @param string $modelClassName
         * @param string $attribute
         * @param string $relatedAttribute
         */
        public function __construct($modelClassName, $attribute, $relatedAttribute = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attribute)');
            assert('is_string($relatedAttribute) || $relatedAttribute == null');
            $this->modelClassName   = $modelClassName;
            $this->attribute        = $attribute;
            $this->relatedAttribute = $relatedAttribute;
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        public function getAttribute()
        {
            return $this->attribute;
        }

        public function getRelatedAttribute()
        {
            return $this->relatedAttribute;
        }

        /**
         * @returns The table name for the model class name specified in this adapter.
         */
        public function getModelTableName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getTableName($modelClassName);
        }

        /**
         * @returns The model class name for the attribute specified in this adapter.  Since the attribute might not be on
         * the same model as the model class name specified, (might be casted up) this method is needed.
         */
        public function getAttributeModelClassName()
        {
            if ($this->attribute == 'id')
            {
                return $this->modelClassName;
            }
            return $this->getModel()->getAttributeModelClassName($this->attribute);
        }

        /**
         * @returns The table name for the attribute specified in this adapter.  Since the attribute might not be on
         * the same table as the model class name specified, this method is needed.
         */
        public function getAttributeTableName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getTableName($this->getAttributeModelClassName());
        }

        public function getModel()
        {
            if ($this->model == null)
            {
                $this->model = new $this->modelClassName(false);
            }
            return $this->model;
        }

        /**
         * @return The column name for the attribute specified in this adapter.
         */
        public function getColumnName()
        {
            return $this->getModel()->getColumnNameByAttribute($this->attribute);
        }

        /**
         * @return true/false - Is the attribute a relation on the model class name.
         */
        public function isRelation()
        {
            return $this->getModel()->isRelation($this->attribute);
        }

        /**
         * @return The relation type of the attribute on the model class name.
         */
        public function getRelationType()
        {
            return $this->getModel()->getRelationType($this->attribute);
        }

        /**
         * @return bool
         */
        public function isRelationTypeAHasManyVariant()
        {
            if($this->getRelationType() == RedBeanModel::HAS_MANY  ||
               $this->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO ||
               $this->getRelationType() == RedBeanModel::HAS_ONE_BELONGS_TO)
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function isRelationTypeAHasOneVariant()
        {
            if($this->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO ||
               $this->getRelationType() == RedBeanModel::HAS_ONE)
            {
                return true;
            }
            return false;
        }

        /**
         * @return true/false if the $relatedAttribute was specified in the construcor.
         */
        public function hasRelatedAttribute()
        {
            if ($this->relatedAttribute == null)
            {
                return false;
            }
            return true;
        }

        /**
         * If the attribute is a relation, returns the RedBeanModel of that relation.
         */
        protected function getRelationModel()
        {
            if ($this->relatedModel == null)
            {
                $relationModelClassName     = $this->getRelationModelClassName();
                $this->relatedModel         = new $relationModelClassName(false);
            }
            return $this->relatedModel;
        }

        /**
         * If the attribute is a relation, returns the model class name of that relation.
         */
        public function getRelationModelClassName()
        {
            return $this->getModel()->getRelationModelClassName($this->attribute);
        }

        /**
         * If the attribute is a relation, returns the model class name of that relation's relatedAttribute. This might
         * be different than the relation's model class name if the relatedAttribute is casted up.
         */
        public function getRelatedAttributeModelClassName()
        {
            if ($this->relatedAttribute == 'id')
            {
                return $this->getRelationModelClassName();
            }
            return $this->getRelationModel()->getAttributeModelClassName($this->relatedAttribute);
        }

        /**
         * If the relation model class can have a bean.
         */
        public function canRelationHaveTable()
        {
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::getCanHaveBean();
        }

        /**
         * If the attribute is a relation, returns the relation's table name.
         */
        public function getRelationTableName()
        {
            $modelClassName = $this->getRelationModelClassName();
            if($this->canRelationHaveTable())
            {
                return $modelClassName::getTableName($modelClassName);
            }
            else
            {
                while (get_parent_class($modelClassName) != 'RedBeanModel')
                {
                    $modelClassName = get_parent_class($modelClassName);
                    if($modelClassName::getCanHaveBean())
                    {
                        return $modelClassName::getTableName($modelClassName);
                    }
                }
                throw new NotSupportedException();
            }
        }

        /**
         * If the attribute is a relation, returns the relation model class name or the next available that
         * can have a table
         */
        public function getRelationModelClassNameThatCanHaveATable()
        {
            $modelClassName = $this->getRelationModelClassName();
            if($this->canRelationHaveTable())
            {
                return $modelClassName;
            }
            else
            {
                while (get_parent_class($modelClassName) != 'RedBeanModel')
                {
                    $modelClassName = get_parent_class($modelClassName);
                    if($modelClassName::getCanHaveBean())
                    {
                        return $modelClassName;
                    }
                }
                throw new NotSupportedException();
            }
        }

        /**
         * If the attribute is a relation, returns the relation's relatedAttribute's table name.
         */
        public function getRelatedAttributeTableName()
        {
            $modelClassName = $this->getRelatedAttributeModelClassName();
            return            $modelClassName::getTableName($modelClassName);
        }

        /**
         *
         * If the attribute is a relation, returns the relation's relatedAttribute's column name.
         */
        public function getRelatedAttributeColumnName()
        {
            return $this->getRelationModel()->getColumnNameByAttribute($this->relatedAttribute);
        }

        /**
         * @return true/false - Is the attribute a relation on the model class name.
         */
        public function isRelatedAttributeRelation()
        {
            return $this->getRelationModel()->isRelation($this->relatedAttribute);
        }

        /**
         * @return The relation type of the attribute on the model class name.
         */
        public function getRelatedAttributeRelationType()
        {
            if (!$this->isRelatedAttributeRelation())
            {
                throw new NotSupportedException();
            }
            return $this->getRelationModel()->getRelationType($this->relatedAttribute);
        }

        /**
         * @return mixed
         * @throws NotSupportedException
         */
        public function getRelatedAttributeRelationModelClassName()
        {
            if (!$this->isRelatedAttributeRelation())
            {
                throw new NotSupportedException();
            }
            return $this->getRelationModel()->getRelationModelClassName($this->relatedAttribute);
        }

        /**
         * @return string
         * @throws NotSupportedException
         */
        public function getManyToManyTableName()
        {
            if($this->getRelationType() != RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            $attributeName = $this->getAttribute();
            return $this->getModel()->{$attributeName}->getTableName();
        }

        /**
         * @return bool
         */
        public function isAttributeMixedIn()
        {
            if ($this->getModelClassName() == 'User' &&
                $this->getAttributeModelClassName() == 'Person')
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function isAttributeOnDifferentModel()
        {
            if($this->getAttributeModelClassName() == $this->getModelClassName())
            {
                return false;
            }
            return true;
        }

        /**
         * Resolve which column to use when querying a many to many relation.
         * @return string
         */
        public function resolveManyToManyColumnName()
        {
            if ($this->getRelatedAttribute() != 'id')
            {
               return $this->getRelatedAttributeColumnName();
            }
            else
            {
                return $this->getRelationTableName() . '_id';
            }
        }

        public function isAttributeDerivedRelationViaCastedUpModel()
        {
            if($this->getModel()->isADerivedRelationViaCastedUpModel($this->attribute))
            {
                return true;
            }
            return false;
        }

        public function getCastedUpModelClassNameForDerivedRelation()
        {
            if(!$this->isAttributeDerivedRelationViaCastedUpModel())
            {
                throw new NotSupportedException();
            }
            $relationModelClassName = $this->getModel()->getDerivedRelationModelClassName($this->attribute);
            $opposingRelationName   = $this->getModel()->getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();
            return $relationModel->getRelationModelClassName($opposingRelationName);
        }


        public function getManyToManyTableNameForDerivedRelationViaCastedUpModel()
        {
            $relationModelClassName = $this->getModel()->getDerivedRelationModelClassName($this->attribute);
            $opposingRelationName   = $this->getModel()->getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();

            if($this->getModel()->getDerivedRelationType($this->attribute) != RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            $attributeName = $this->getAttribute();
            return $relationModel->{$opposingRelationName}->getTableName();
        }

        /**
         * In the case of account -> meeting, this method returns 'Activity' since 'Activity' is the model that the
         * opposing relation rests on.  This is different than getDerivedRelationModelClassName which would be 'Meeting'.
         * Sometimes both are the same model, it just depends if the final model class is casted down or not
         * @return mixed
         */
        public function getOpposingRelationModelClassName()
        {
            $relationModelClassName = $this->getDerivedRelationViaCastedUpModelClassName();
            $opposingRelationName   = $this->getModel()->getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();
            return $relationModel->getAttributeModelClassName($opposingRelationName);
        }

        /**
         * @return mixed
         */
        public function getDerivedRelationViaCastedUpModelClassName()
        {
            return $this->getModel()->getDerivedRelationModelClassName($this->getAttribute());
        }

        public function getOpposingRelationTableName()
        {
            $opposingRelationModelClassName  = $this->getOpposingRelationModelClassName();
            return $opposingRelationModelClassName::getTableName($opposingRelationModelClassName);
        }

        public function isDerivedRelationViaCastedUpModelDifferentThanOpposingModelClassName()
        {
            $opposingRelationModelClassName  = $this->getOpposingRelationModelClassName();
            $derivedRelationModelClassName   = $this->getDerivedRelationViaCastedUpModelClassName();
            if($opposingRelationModelClassName != $derivedRelationModelClassName)
            {
                return true;
            }
            return false;
        }
    }
?>