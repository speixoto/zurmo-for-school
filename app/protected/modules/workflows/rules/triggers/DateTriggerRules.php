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
     * Class to help evaluate date triggers against model values.
     */
    class DateTriggerRules extends TriggerRules
    {
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            switch($this->trigger->valueType)
            {

                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER:
                    if(static::sanitize($model->$attribute) > static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE:
                    if(static::sanitize($model->$attribute) < static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON:
                if(static::sanitize($model->$attribute) === static::sanitizeTriggerValue($this->trigger->value))
                {
                    return true;
                }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN:
                    if(static::sanitize($model->$attribute) > static::sanitizeTriggerValue($this->trigger->value) &&
                       static::sanitize($model->$attribute) < static::sanitizeTriggerValue($this->trigger->secondValue))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_IS_EMPTY:
                    if(DateTimeUtil::isDateValueNull($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_IS_NOT_EMPTY:
                    if(!DateTimeUtil::isDateValueNull($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_CHANGES:
                    if(array_key_exists($attribute, $model->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_DOES_NOT_CHANGE:
                    if(!array_key_exists($attribute, $model->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BECOMES_ON:
                    if(array_key_exists($attribute, $model->originalAttributeValues) &&
                        static::sanitize($model->$attribute) === static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_WAS_ON:
                    if(array_key_exists($attribute, $model->originalAttributeValues) &&
                        static::sanitize($model->originalAttributeValues[$attribute]) ===
                            static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                default:
                    throw new NotSupportedException();
            }
            return false;
        }

        protected function sanitize($value)
        {
            return strtotime($value);
        }

        protected function sanitizeTriggerValue($value)
        {
            return strtotime($value);
        }
    }
?>