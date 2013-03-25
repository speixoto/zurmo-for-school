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
     * Class to help evaluate CurrencyValue triggers against model values.
     */
    class CurrencyValueTriggerRules extends NumberTriggerRules
    {
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            switch($this->trigger->getOperator())
            {

                case OperatorRules::TYPE_EQUALS:
                    if(static::sanitize($model->{$attribute}->value) === static::sanitize($this->trigger->value) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_EQUAL:
                    if(static::sanitize($model->{$attribute}->value) !== static::sanitize($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_CHANGES:
                    if(array_key_exists('value', $model->{$attribute}->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_CHANGE:
                    if(!array_key_exists('value', $model->{$attribute}->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES:
                    if(array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        static::sanitize($model->{$attribute}->value) === static::sanitize($this->trigger->value) &&
                        $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS:
                    if(array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        static::sanitize($model->{$attribute}->originalAttributeValues['value']) ===
                        static::sanitize($this->trigger->value)&&
                        $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_GREATER_THAN:
                    if(static::sanitize($model->{$attribute}->value) > static::sanitize($this->trigger->value) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_LESS_THAN:
                    if(static::sanitize($model->{$attribute}->value) < static::sanitize($this->trigger->value) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO:
                    if(static::sanitize($model->{$attribute}->value) >= static::sanitize($this->trigger->value) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO:
                    if(static::sanitize($model->{$attribute}->value) <= static::sanitize($this->trigger->value) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BETWEEN:
                    if(static::sanitize($model->{$attribute}->value) > static::sanitize($this->trigger->value) &&
                       static::sanitize($model->{$attribute}->value) < static::sanitize($this->trigger->secondValue) &&
                       $this->resolveCurrency($model->{$attribute}->currency))
                    {
                        return true;
                    }
                    break;
                default:
                    throw new NotSupportedException();
            }
            return false;
        }

        /**
         * @see parent::evaluateTimeTriggerBeforeSave for explanation of method
         * @param RedBeanModel $model
         * @param $attribute
         * @return bool
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute)
        {
            if(array_key_exists('value', $model->{$attribute}->originalAttributeValues))
            {
                if($this->trigger->getOperator() == OperatorRules::TYPE_DOES_NOT_CHANGE)
                {
                    return true;
                }
                return $this->evaluateBeforeSave($model, $attribute);
            }
            return false;
        }

        /**
         * @param $value
         * @return mixed
         */
        protected function sanitize($value)
        {
            return floatval($value);
        }

        protected  function resolveCurrency(Currency $currency)
        {
           if($this->trigger->currencyIdForValue == null)
           {
               return true;
           }
           elseif($currency->id == $this->trigger->currencyIdForValue)
           {
               return true;
           }
           else
           {
               return false;
           }
        }
    }
?>