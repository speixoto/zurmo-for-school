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

    abstract class ModelRelationsAndAttributesToSummableReportAdapter extends ModelRelationsAndAttributesToReportAdapter
    {
        const DISPLAY_CALCULATION_COUNT      = 'Count';

        const DISPLAY_CALCULATION_SUMMMATION = 'Summation';

        const DISPLAY_CALCULATION_AVERAGE    = 'Average';

        const DISPLAY_CALCULATION_MINIMUM    = 'Minimum';

        const DISPLAY_CALCULATION_MAXIMUM    = 'Maximum';

        const GROUP_BY_CALCULATION_DAY       = 'Day';

        const GROUP_BY_CALCULATION_WEEK      = 'Week';

        const GROUP_BY_CALCULATION_MONTH     = 'Month';

        const GROUP_BY_CALCULATION_QUARTER   = 'Quarter';

        const GROUP_BY_CALCULATION_YEAR      = 'Year';

        protected static function getTranslatedDisplayCalculationShortLabel($type)
        {
            assert('is_string($type)');
            $labels = static::translatedDisplayCalculationShortLabels();
            return $labels[$type];
        }

        protected static function translatedDisplayCalculationShortLabels()
        {
            return array(
                self::DISPLAY_CALCULATION_COUNT       => Yii::t('Default', 'Count'),
                self::DISPLAY_CALCULATION_SUMMMATION  => Yii::t('Default', 'Sum'),
                self::DISPLAY_CALCULATION_AVERAGE     => Yii::t('Default', 'Avg'),
                self::DISPLAY_CALCULATION_MINIMUM     => Yii::t('Default', 'Min'),
                self::DISPLAY_CALCULATION_MAXIMUM     => Yii::t('Default', 'Max'),
            );
        }

        protected static function getTranslatedGroupByCalculationShortLabel($type)
        {
            assert('is_string($type)');
            $labels = static::translatedGroupByCalculationShortLabels();
            return $labels[$type];
        }

        protected static function translatedGroupByCalculationShortLabels()
        {
            return array(
                self::GROUP_BY_CALCULATION_DAY       => Yii::t('Default', 'Day'),
                self::GROUP_BY_CALCULATION_WEEK      => Yii::t('Default', 'Week'),
                self::GROUP_BY_CALCULATION_MONTH     => Yii::t('Default', 'Month'),
                self::GROUP_BY_CALCULATION_QUARTER   => Yii::t('Default', 'Quarter'),
                self::GROUP_BY_CALCULATION_YEAR      => Yii::t('Default', 'Year'),
            );
        }

        public function getAttributesForFilters()
        {
            $attributes = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        public function getAttributesForDisplayAttributes(RedBeanModel $precedingModel = null, $precedingRelation = null)
        {
            if(($precedingModel != null && $precedingRelation == null) ||
               ($precedingModel == null && $precedingRelation != null))
            {
                throw new NotSupportedException();
            }
            $existingGroupBys = $this->report->getGroupBys();
            if(empty($existingGroupBys))
            {
                return array();
            }
            $attributes = array(self::DISPLAY_CALCULATION_COUNT => array('label' => Yii::t('Default', 'Count')));
            $this->resolveGroupByAttributesForDisplayAttributes($precedingModel, $precedingRelation, $attributes,
                                                                $existingGroupBys);
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
                if ($attributeType == 'Decimal' || $attributeType == 'Integer')
                {
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_SUMMMATION);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_AVERAGE);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
                }
                elseif($attributeType == 'Date' || $attributeType == 'DateTime')
                {
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
                }
                elseif($this->model->isRelation($attribute) &&
                       $this->model->getRelationModelClassName($attribute) == 'CurrencyValue')
                {
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_SUMMMATION);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_AVERAGE);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MINIMUM);
                    $this->resolveDisplayCalculationAttributeData($attributes, $attribute, self::DISPLAY_CALCULATION_MAXIMUM);
                }
            }
            return $attributes;
        }

        protected function resolveGroupByAttributesForDisplayAttributes(RedBeanModel $precedingModel = null,
                                                                        $precedingRelation = null,
                                                                        & $attributes,
                                                                        $existingGroupBys)
        {
            assert('is_array($attributes)');
            assert('is_array($existingGroupBys)');
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
                    $attributes[$resolvedAttribute] = array('label' => $this->model->getAttributeLabel($resolvedAttribute));
                }
            }
        }

        protected function resolveDisplayCalculationAttributeData(& $attributes, $attribute, $type)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            assert('is_string($type)');
            $attributes[$attribute . FormModelUtil::DELIMITER . $type] =
                        array('label' => $this->resolveDisplayCalculationLabel($attribute, $type));
        }

        protected function resolveDisplayCalculationLabel($attribute, $type)
        {
            assert('is_string($type)');
            return $this->model->getAttributeLabel($attribute) .
                   ' -(' . static::getTranslatedDisplayCalculationShortLabel($type) . ')';
        }

        public function getAttributesForGroupBys()
        {
            $attributes = array('id' => array('label' => Yii::t('Default', 'Id')));
            foreach ($this->getAttributesNotIncludingDerivedAttributesData() as $attribute => $data)
            {
                $attributeType = ModelAttributeToMixedTypeUtil::getType($this->model, $attribute);
                if($attributeType == 'Date' || $attributeType == 'DateTime')
                {
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_DAY);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_WEEK);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_MONTH);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_QUARTER);
                    $this->resolveGroupByCalculationAttributeData($attributes, $attribute, self::GROUP_BY_CALCULATION_YEAR);
                }
                elseif($attributeType != 'TextArea')
                {
                    $attributes[$attribute] = $data;
                }
            }
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        protected function resolveGroupByCalculationAttributeData(& $attributes, $attribute, $type)
        {
            assert('is_array($attributes)');
            assert('is_string($attribute)');
            assert('is_string($type)');
            $attributes[$attribute . FormModelUtil::DELIMITER . $type] =
                        array('label' => $this->resolveGroupByCalculationLabel($attribute, $type));
        }

        protected function resolveGroupByCalculationLabel($attribute, $type)
        {
            assert('is_string($type)');
            return $this->model->getAttributeLabel($attribute) .
                   ' -(' . static::getTranslatedGroupByCalculationShortLabel($type) . ')';
        }
    }
?>