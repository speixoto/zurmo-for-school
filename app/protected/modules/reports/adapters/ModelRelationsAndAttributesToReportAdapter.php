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

    class ModelRelationsAndAttributesToReportAdapter
    {
        private $model;

        private $rules;

        private $report;

        public function __construct(RedBeanModel $model, ReportRules $rules, Report $report)
        {
            $this->model  = $model;
            $this->rules  = $rules;
            $this->report = $report;
        }

        public function getAllRelationNamesAndLabels()
        {
            $attributes = array();
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                if ($this->model->isRelation($attribute))
                {
                    $attributes[$attribute] = $this->model->getAttributeLabel($attribute);
                }
            }
            return $attributes;
        }

        /**
         * Returns the array of selectable relations for creating a report.  Does not include relations that are
         * marked as nonReportable in the rules and also excludes relations that are marked as relations
         * reportedAsAttributes by the rules.  Includes relations marked as relationsViaParent
         * @return array of relation name and data including the label
         */
        public function getSelectableRelations(RedBeanModel $precedingModel = null, $precedingRelation = null)
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
                    $this->rules->relationIsReportable($this->model, $attribute) &&
                    !$this->relationLinksToPrecedingRelation($attribute, $precedingModel, $precedingRelation)
                    )
                {
                    $attributes[$attribute] = array('label' => $this->model->getAttributeLabel($attribute));
                }
            }

            return array_merge($attributes, $this->getRelationsViaParent());
        }

        protected function relationLinksToPrecedingRelation($relation, RedBeanModel $precedingModel = null,
                                                            $precedingRelation = null)
        {
            if($precedingModel == null || $precedingRelation == null)
            {
                return false;
            }
            if($precedingModel->getRelationModelClassName($precedingRelation) !=
               $this->model->getRelationmodelClassName($relation))
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

        protected function getRelationsViaParent()
        {
            $attributes = array();
            $metadata   = $this->model->getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($metadata[$modelClassName]["relationsViaParent"]))
                {
                    foreach($metadata[$modelClassName]["relationsViaParent"] as $relation => $notUsed)
                    {
                        $attributes[$relation] = array('label' => $this->model->getAttributeLabel($relation));
                    }
                }
            }
            return $attributes;
        }
    }
?>