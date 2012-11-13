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

    class ModelRelationsAndAttributesToSummationReportAdapter extends ModelRelationsAndAttributesToReportAdapter
    {
        const DISPLAY_CALCULATION_COUNT      = 'Count';

        const DISPLAY_CALCULATION_SUMMMATION = 'Summation';

        const DISPLAY_CALCULATION_AVERAGE    = 'Average';

        const DISPLAY_CALCULATION_MINIMUM    = 'Minimum';

        const DISPLAY_CALCULATION_MAXIMUM    = 'Maximum';

        public function getAttributesForFilters()
        {
            $attributes = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        public function getAttributesForDisplayAttributes()
        {
            $existingGroupBys = $this->report->getGroupBys();
            if(empty($existingGroupBys))
            {
                return array();
            }
            $attributes = array(self::DISPLAY_CALCULATION_COUNT => array('label' => Yii::t('Default', 'Count')));
            foreach($existingGroupBys as $groupBy)
            {
                //need to make sure the groupBy attribute is in fact on this model for this relation

                //is the groupBy attribute on the modelClassName that corresponds to $this->model?
                    //if not then ignore

                    //what if the attribute is on this model class name but it is not the relation.
                    //From Y array('hasOneX' => 'xxx');  //we are on a X, but our relationship to Y, there are 2 of them
                    //

                //todo:
                if($groupBy->getResolvedAttributeModelClassName() == get_class($this->model))
                {
                    $resolvedAttribute = $groupBy->getResolvedAttribute();
                    $attributes[$resolvedAttribute] = array('label' => $this->model->getAttributeLabel($resolvedAttribute));
                }
            }
            foreach ($this->model->getAttributes() as $attribute => $notUsed)
            {
                $attributeType = ModelAttributeToMixedTypeUtil::getTypeByModelUsingValidator($this->model, $attribute);
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

        public function getAttributesForOrderBys()
        {
            $attributes = $this->getAttributesNotIncludingDerivedAttributesData();
            $attributes = array_merge($attributes, $this->getDynamicallyDerivedAttributesData());
            return $attributes;
        }

        public function getAttributesForGroupBys()
        {

        }
    }
?>