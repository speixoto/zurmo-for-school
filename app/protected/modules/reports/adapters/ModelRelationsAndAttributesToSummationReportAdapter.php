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

    class ModelRelationsAndAttributesToSummationReportAdapter extends ModelRelationsAndAttributesToSummableReportAdapter
    {
        public function getAttributesForOrderBys($existingGroupBys = array(), $existingDisplayAttributes = array(),
                                                 RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            assert('is_array($existingGroupBys)');
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            if(empty($existingGroupBys) && empty($existingDisplayAttributes))
            {
                return array();
            }
            $attributes = array();
            foreach($existingGroupBys as $groupBy)
            {
                $addAttribute = false;
                //is there is preceding model/relation info
                if($precedingModel != null && $precedingRelation != null)
                {
                    if($groupBy->hasRelatedData() &&
                       $groupBy->getPenultimateModelClassName() == get_class($precedingModel) &&
                       $groupBy->getPenultimateRelation() == $precedingRelation &&
                       $groupBy->getResolvedAttributeModelClassName() == get_class($this->model))
                    {
                        $addAttribute = true;
                    }
                }
                else
                {
                    //is there is no preceding model/relation info
                    //if the groupBy attribute is part of a related data chain, ignore,
                    //since must be at the wrong spot in the chain.
                    if(!$groupBy->hasRelatedData() &&
                       $groupBy->getResolvedAttributeModelClassName() == get_class($this->model))
                    {
                        $addAttribute = true;
                    }
                }
                if($addAttribute)
                {
                    $resolvedAttribute = $groupBy->getResolvedAttribute();
                    $attributes[$groupBy->attributeIndexOrDerivedType] =
                        array('label' => $this->model->getAttributeLabel($resolvedAttribute));
                }
            }
            foreach($existingDisplayAttributes as $displayAttribute)
            {
                $resolvedAttribute    = $displayAttribute->getResolvedAttribute();
                if($this->isAttributeACalculationOrModifier($resolvedAttribute))
                {
                    //We don't have to check penultimate information like GroupBys, because all display calculations are
                    //valid
                    //if the displayAttribute is part of a related data chain, ignore,
                    //since must be at the wrong spot in the chain.
                    if(!$displayAttribute->hasRelatedData() &&
                        $displayAttribute->getResolvedAttributeModelClassName() == get_class($this->model))
                    {
                        $realAttributeName = $this->resolveRealAttributeName($resolvedAttribute);
                        $attributes[$resolvedAttribute] = array('label' =>
                        $this->resolveDisplayCalculationLabel($realAttributeName,
                                $this->getCalculationOrModifierType($resolvedAttribute)));
                    }
                }
            }
            return $attributes;
        }

        public function getForDrillDownAttributes()
        {
            $attributes = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes = array_merge($attributes, $this->getDerivedAttributesData());
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        public function getAttributesForChartSeries($existingGroupBys = array(), $existingDisplayAttributes = array())
        {
            $attributes = array();
            foreach($existingDisplayAttributes as $displayAttribute)
            {
                foreach($existingGroupBys as $groupBy)
                {
                    if($groupBy->attributeIndexOrDerivedType == $displayAttribute->attributeIndexOrDerivedType)
                    {
                        $attributes[$displayAttribute->attributeIndexOrDerivedType] =
                            array('label' => $displayAttribute->getDisplayLabel());
                    }
                }
            }
            return $attributes;
        }

        public function getAttributesForChartRange ($existingDisplayAttributes = array())
        {
            $attributes = array();
            foreach($existingDisplayAttributes as $displayAttribute)
            {
                if(static::
                   isAttributeIndexOrDerivedTypeADisplayCalculation($displayAttribute->attributeIndexOrDerivedType))
                {
                    $attributes[$displayAttribute->attributeIndexOrDerivedType] =
                        array('label' => $displayAttribute->getDisplayLabel());
                }
            }
            return $attributes;
        }
    }
?>