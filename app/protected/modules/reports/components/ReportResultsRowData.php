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

    class ReportResultsRowData extends CComponent
    {
        const ATTRIBUTE_NAME_PREFIX = 'attribute';

        const DRILL_DOWN_GROUP_BY_VALUE_PREFIX = 'groupByRowValue';

        protected $id;

        protected $displayAttributes;

        protected $modelsByAliases  = array();

        protected $selectedColumnNamesAndValues   = array();

        protected $selectedColumnNamesAndRowSpans = array();

        protected $selectedColumnNamesAndLabels   = array();

        public static function resolveAttributeNameByKey($key)
        {
            assert('is_numeric($key) || is_string($key)');
            return self::ATTRIBUTE_NAME_PREFIX . $key;
        }

        public function __construct(array $displayAttributes, $id)
        {
            assert('is_int($id)');
            $this->displayAttributes = $displayAttributes;
            $this->id                = $id;
        }

        public function __get($name)
        {
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $name);
            if(count($parts) == 2 && $parts[1] != null)
            {
                return $this->resolveValueFromModel($parts[1]);
            }
            if(isset($this->selectedColumnNamesAndValues[$name]))
            {
                return $this->selectedColumnNamesAndValues[$name];
            }
            return parent::__get($name);
        }

        public function addModelAndAlias(RedBeanModel $model, $alias)
        {
            assert('is_string($alias)');
            if(isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            $this->modelsByAliases[$alias] = $model;
        }

        public function addSelectedColumnNameAndValue($columnName, $value)
        {
            $this->selectedColumnNamesAndValues[$columnName] = $value;
        }

        public function addSelectedColumnNameAndLabel($columnName, $label)
        {
            assert('is_string($label)');
            $this->selectedColumnNamesAndLabels[$columnName] = $label;
        }

        public function getLabel($columnName)
        {
            assert('is_string($label)');
            return $this->selectedColumnNamesAndLabels[$columnName];
        }

        public function addSelectedColumnNameAndRowSpan($columnName, $value)
        {
            assert('is_int($value)');
            $this->selectedColumnNamesAndRowSpans[$columnName] = $value;
        }

        public function getSelectedColumnRowSpan($columnName)
        {
            assert('is_string($columnName)');
            return $this->selectedColumnNamesAndRowSpans[$columnName];
        }


        public function getModel($attribute)
        {
            list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attribute);
            if($displayAttributeKey != null)
            {
                return $this->resolveModel($displayAttributeKey);
            }
            throw new NotSupportedException();
        }

        public function getId()
        {
            return $this->id;
        }

        public function getDataParamsForDrillDownAjaxCall()
        {
            $dataParams = array();
            foreach($this->displayAttributes as $key => $displayAttribute)
            {
                if($displayAttribute->valueUsedAsDrillDownFilter)
                {
                    $attributeAlias = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                    if($this->shouldResolveValueFromModel($attributeAlias))
                    {
                        list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attributeAlias);
                        $model = $this->resolveModel($displayAttributeKey);
                        if($model == null)
                        {
                            $value = null;
                        }
                        else
                        {
                            $value = $this->resolveRawValueByModel($displayAttribute, $model);
                        }
                    }
                    else
                    {
                        $value = $this->selectedColumnNamesAndValues[$attributeAlias];
                    }

                    $dataParams[self::resolveDataParamKeyForDrillDown($displayAttribute->attributeIndexOrDerivedType)] = $value;
                }
            }
            return $dataParams;
        }

        public static function resolveDataParamKeyForDrillDown($attributeIndexOrDerivedType)
        {
            return self::DRILL_DOWN_GROUP_BY_VALUE_PREFIX . $attributeIndexOrDerivedType;
        }

        public function resolveRawValueByDisplayAttributeKey($displayAttributeKey)
        {
            assert('is_int($displayAttributeKey)');
            $model = $this->resolveModel($displayAttributeKey);
            return $this->resolveRawValueByModel($this->displayAttributes[$displayAttributeKey], $model);
        }

        protected function shouldResolveValueFromModel($attributeAlias)
        {
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $attributeAlias);
            if(count($parts) == 2 && $parts[1] != null)
            {
                return true;
            }
            return false;
        }

        protected function resolveModel($displayAttributeKey)
        {
            if(!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            if(!isset($this->modelsByAliases[$modelAlias]))
            {
                return null;
            }
            return $this->getModelByAlias($modelAlias);
        }

        protected function resolveValueFromModel($displayAttributeKey)
        {
            if(!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            $attribute        = $displayAttribute->getResolvedAttributeRealAttributeName();
            if(!isset($this->modelsByAliases[$modelAlias]))
            {
                $defaultModelClassName = $displayAttribute->getResolvedAttributeModelClassName();
                $model = new $defaultModelClassName(false);
            }
            else
            {
                $model = $this->getModelByAlias($modelAlias);
            }

            return $this->resolveModelAttributeValueForPenultimateRelation($model, $attribute, $displayAttribute);
        }

        protected function resolveModelAttributeValueForPenultimateRelation(RedBeanModel $model, $attribute,
                                                                            DisplayAttributeForReportForm $displayAttribute)
        {
            if($model->isAttribute($attribute))
            {
                return $model->$attribute;
            }
            $penultimateRelation = $displayAttribute->getPenultimateRelation();
            if(!$model->isAttribute($penultimateRelation))
            {
                throw new NotSupportedException();
            }
            return $model->$penultimateRelation->$attribute;
        }

        protected function resolveRawValueByModel(DisplayAttributeForReportForm $displayAttribute, RedBeanModel $model)
        {
            $type                 = $displayAttribute->getDisplayElementType();
            $attribute            = $displayAttribute->getResolvedAttribute();
            if($type == 'CurrencyValue')
            {
                return $model->{$attribute}->value;
            }
            elseif($type == 'User')
            {
                $realAttributeName = $displayAttribute->getResolvedAttributeRealAttributeName();
                return $model->{$realAttributeName}->id;
            }
            elseif($type == 'DropDown')
            {
                return $model->{$attribute}->value;
            }
            elseif(null != $rawValueRelatedAttribute = $displayAttribute->getRawValueRelatedAttribute())
            {
                return $model->{$attribute}->{$rawValueRelatedAttribute};
            }
            else
            {
                return $this->resolveModelAttributeValueForPenultimateRelation($model, $attribute, $displayAttribute);
            }
        }

        protected function getModelByAlias($alias)
        {
            if(!isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            return $this->modelsByAliases[$alias];
        }
    }
?>