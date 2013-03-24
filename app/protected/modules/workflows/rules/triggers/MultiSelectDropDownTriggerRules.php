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
     * Class to help evaluate MultipleValuesCustomField triggers against model values.
     */
    class MultiSelectDropDownTriggerRules extends TriggerRules
    {
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            switch($this->trigger->getOperator())
            {

                case OperatorRules::TYPE_EQUALS:
                    return $this->isSetIdenticalToTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_DOES_NOT_EQUAL:
                    return !$this->isSetIdenticalToTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_ONE_OF:
                    return $this->doesSetContainAtLeastOneOfTheTriggerValues($model->{$attribute}->values);
                    break;
                case OperatorRules::TYPE_CHANGES:
                    if($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_CHANGE:
                    if(!($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES:
                    if($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null &&
                       $this->isSetIdenticalToTriggerValues($model->{$attribute}->values))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS:
                    if($model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData() !== null &&
                       $this->isDataIdenticalToTriggerValues(
                           $model->{$attribute}->resolveOriginalCustomFieldValuesDataForNewData()))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_EMPTY:
                    if($model->{$attribute}->values->count() == 0)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NOT_EMPTY:
                    if($model->{$attribute}->values->count() != 0)
                    {
                        return true;
                    }
                    break;
                default:
                    throw new NotSupportedException();
            }
            return false;
        }

        protected function isDataIdenticalToTriggerValues(Array $values)
        {
            if(count($values) != count($this->trigger->value))
            {
                return false;
            }
            foreach($values as $value)
            {
                if(!in_array($value, $this->trigger->value))
                {
                    return false;
                }
            }
            return true;
        }

        protected function isSetIdenticalToTriggerValues(RedBeanOneToManyRelatedModels $multipleCustomFieldValues)
        {
            if($multipleCustomFieldValues->count() != count($this->trigger->value))
            {
                return false;
            }
            foreach($multipleCustomFieldValues as $customFieldValue)
            {
                if(!in_array($customFieldValue->value, $this->trigger->value))
                {
                    return false;
                }
            }
            return true;
        }

        protected function doesDataContainAtLeastOneOfTheTriggerValues(Array $values)
        {
            if(!is_array($this->trigger->value)) //it should always be an array
            {
                return false;
            }
            foreach($values as $value)
            {
                if(in_array($value, $this->trigger->value))
                {
                    return true;
                }
            }
            return false;
        }

        protected function doesSetContainAtLeastOneOfTheTriggerValues(RedBeanOneToManyRelatedModels $multipleCustomFieldValues)
        {
            if(!is_array($this->trigger->value)) //it should always be an array
            {
                return false;
            }
            foreach($multipleCustomFieldValues as $customFieldValue)
            {
                if(in_array($customFieldValue->value, $this->trigger->value))
                {
                    return true;
                }
            }
            return false;
        }
    }
?>