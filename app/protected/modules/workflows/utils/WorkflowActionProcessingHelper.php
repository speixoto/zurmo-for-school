<?php
/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class WorkflowActionProcessingHelper
    {
        protected $action;

        protected $triggeredModel;

        protected $triggeredUser;

        public function __construct(ActionForWorkflowForm $action, RedBeanModel $triggeredModel, User $triggeredUser)
        {
            $this->action         = $action;
            $this->triggeredModel = $triggeredModel;
            $this->triggeredUser  = $triggeredUser;
        }

        public function processUpdateSelectAction()
        {
            if($this->action->type == ActionForWorkflowForm::TYPE_UPDATE_SELF)
            {
                self::processActionAttributesForAction($this->action, $this->triggeredModel,
                                                       $this->triggeredUser, $this->triggeredModel);
            }
        }

        public function processNonUpdateSelfAction()
        {
            if($this->action->type == ActionForWorkflowForm::TYPE_UPDATE_RELATED)
            {
                self::processUpdateRelatedAction();
            }
            elseif($this->action->type == ActionForWorkflowForm::TYPE_CREATE)
            {
                self::processCreateAction();
            }
            elseif($this->action->type == ActionForWorkflowForm::TYPE_CREATE_RELATED)
            {
                self::processCreateRelatedAction();
            }
            else
            {
                throw new NotSupportedException('Invalid action type: ' . $this->action->type);
            }
        }

        protected static function processActionAttributesForAction(ActionForWorkflowForm $action,
                                                                   RedBeanModel $model,
                                                                   User $triggeredUser,
                                                                   RedBeanModel $triggeredModel)
        {
            foreach($action->getActionAttributes() as $attribute => $actionAttribute)
            {
                if($actionAttribute->shouldSetValue)
                {
                    if(null == $relation = ActionForWorkflowForm::resolveFirstRelationName($attribute))
                    {
                        $resolvedModel     = $model;
                        $resolvedAttribute = $attribute;
                    }
                    else
                    {
                        $resolvedModel     = $model->{$relation};
                        $resolvedAttribute = ActionForWorkflowForm::resolveRealAttributeName($attribute);
                    }
                    $adapter = new WorkflowActionProcessingModelAdapter($resolvedModel, $triggeredUser, $triggeredModel);
                    $actionAttribute->resolveValueAndSetToModel($adapter, $resolvedAttribute);
                }
            }
        }

        protected function processUpdateRelatedAction()
        {
            if($this->action->relationFilter != ActionForWorkflowForm::RELATION_FILTER_ALL)
            {
                throw new NotSupportedException();
            }
            $adapter = new RedBeanModelAttributeToDataProviderAdapter(get_class($this->triggeredModel), $this->action->relation);

            if($this->triggeredModel->isADerivedRelationViaCastedUpModel($this->action->relation) &&
               $this->triggeredModel->getDerivedRelationType($this->action->relation) == RedBeanModel::MANY_MANY)
            {
                foreach($this->resolveDerivedModels($this->triggeredModel, $this->action->relation) as $relatedModel)
                {
                    self::processActionAttributesForAction($this->action, $relatedModel, $this->triggeredUser, $this->triggeredModel);
                    $saved = $relatedModel->save();
                    if(!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            elseif($this->triggeredModel->{$this->action->relation} instanceof RedBeanMutableRelatedModels)
            {
                foreach($this->triggeredModel->{$this->action->relation} as $relatedModel)
                {
                    self::processActionAttributesForAction($this->action, $relatedModel, $this->triggeredUser, $this->triggeredModel);
                    $saved = $relatedModel->save();
                    if(!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            elseif($adapter->isRelationTypeAHasOneVariant() && !$adapter->isOwnedRelation())
            {
                $relatedModel = $this->triggeredModel->{$this->action->relation};
                self::processActionAttributesForAction($this->action, $relatedModel, $this->triggeredUser, $this->triggeredModel);
                $saved = $relatedModel->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function processCreateAction()
        {
            if($this->resolveCreateModel($this->triggeredModel, $this->action->relation))
            {
                //todo: when calling save, we need to not trigger workflows on this model since we already are running them..
                //todo: and test this
                $saved = $this->triggeredModel->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
        }

        /**
         * @param RedBeanModel $model
         * @param $relation
         * @return bool true if the $model passed in needs to be saved again. Otherwise false if not.
         * @throws NotSupportedException
         * @throws FailedToSaveModelException
         */
        protected function resolveCreateModel(RedBeanModel $model, $relation)

        {
            assert('is_string($relation)');
            $adapter = new RedBeanModelAttributeToDataProviderAdapter(get_class($model), $relation);
            if($model->isADerivedRelationViaCastedUpModel($relation) &&
                $model->getDerivedRelationType($relation) == RedBeanModel::MANY_MANY)
            {
                $relationModelClassName = $model->getDerivedRelationModelClassName($relation);
                $inferredRelationName   = $model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation);
                $newModel               = new $relationModelClassName();
                self::processActionAttributesForAction($this->action, $newModel, $this->triggeredUser, $this->triggeredModel);
                $newModel->{$inferredRelationName}->add($model);
                $saved = $newModel->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                return false;
            }
            elseif($model->$relation instanceof RedBeanMutableRelatedModels)
            {
                $relationModelClassName = $model->getRelationModelClassName($relation);
                $newModel               = new $relationModelClassName();
                self::processActionAttributesForAction($this->action, $newModel, $this->triggeredUser, $this->triggeredModel);
                $saved = $newModel->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                $model->{$relation}->add($newModel);
                return true;
            }
            elseif($adapter->isRelationTypeAHasOneVariant() && !$adapter->isOwnedRelation())
            {
                $relatedModel = $model->{$relation};
                if($relatedModel->id > 0)
                {
                    return;
                }
                self::processActionAttributesForAction($this->action, $relatedModel, $this->triggeredUser, $this->triggeredModel);
                if(!$relatedModel->save())
                {
                    throw new FailedToSaveModelException();
                }
                return true;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function processCreateRelatedAction()
        {
            if($this->action->relationFilter != ActionForWorkflowForm::RELATION_FILTER_ALL)
            {
                throw new NotSupportedException();
            }
            $adapter = new RedBeanModelAttributeToDataProviderAdapter(get_class($this->triggeredModel), $this->action->relation);
            if($this->triggeredModel->isADerivedRelationViaCastedUpModel($this->action->relation) &&
                $this->triggeredModel->getDerivedRelationType($this->action->relation) == RedBeanModel::MANY_MANY)
            {
                foreach($this->resolveDerivedModels($this->triggeredModel, $this->action->relation) as $relatedModel)
                {
                    if($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                    {
                        $saved = $relatedModel->save();
                        if(!$saved)
                        {
                            throw new FailedToSaveModelException();
                        }
                    }
                }
            }
            elseif($this->triggeredModel->{$this->action->relation} instanceof RedBeanMutableRelatedModels)
            {
                foreach($this->triggeredModel->{$this->action->relation} as $relatedModel)
                {
                    if($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                    {
                        $saved = $relatedModel->save();
                        if(!$saved)
                        {
                            throw new FailedToSaveModelException();
                        }
                    }
                }
            }
            elseif($adapter->isRelationTypeAHasOneVariant() && !$adapter->isOwnedRelation())
            {
                $relatedModel = $this->triggeredModel->{$this->action->relation};
                if($this->resolveCreateModel($relatedModel, $this->action->relatedModelRelation))
                {
                    $saved = $relatedModel->save();
                    if(!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveDerivedModels(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName       = $model->getDerivedRelationModelClassName($relation);
            $inferredRelationName = $model->getDerivedRelationViaCastedUpModelOpposingRelationName($relation);
            return                  WorkflowUtil::getModelsFilteredByInferredModel($modelClassName, $inferredRelationName,
                                    $model->getClassId('Item'));
        }
    }
?>