<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Class to help evaluate date triggers against model values.
     */
    class DateTriggerRules extends TriggerRules
    {
        /**
         * @param RedBeanModel $model
         * @param $attribute
         * @return bool
         * @throws NotSupportedException
         */
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            switch($this->trigger->valueType)
            {
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER:
                    if (static::sanitize($model->$attribute) > static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE:
                    if (static::sanitize($model->$attribute) < static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON:
                    if (static::sanitize($model->$attribute) === static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BETWEEN:
                    if (static::sanitize($model->$attribute) > static::sanitizeTriggerValue($this->trigger->value) &&
                       static::sanitize($model->$attribute) < static::sanitizeTriggerValue($this->trigger->secondValue))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_IS_EMPTY:
                    if (DateTimeUtil::isDateValueNull($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_IS_NOT_EMPTY:
                    if (!DateTimeUtil::isDateValueNull($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_CHANGES:
                    if ($this->resolveAttributeValueIsChanged($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_DOES_NOT_CHANGE:
                    if (!$this->resolveAttributeValueIsChanged($model, $attribute))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_BECOMES_ON:
                    if ($this->resolveAttributeValueIsChanged($model, $attribute) &&
                        static::sanitize($model->$attribute) === static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesSearchFormAttributeMappingRules::TYPE_WAS_ON:
                    if ($this->resolveAttributeValueIsChanged($model, $attribute) &&
                        static::sanitize($model->originalAttributeValues[$attribute]) ===
                            static::sanitizeTriggerValue($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesTriggerForWorkflowFormAttributeMappingRules::TYPE_AT_LEAST_X_AFTER_TRIGGERED_DATE:
                    if (!DateTimeUtil::isDateValueNull($model, $attribute) &&
                         static::sanitize($model->$attribute, true) >= static::sanitizeAndResolveThirdValue())
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesTriggerForWorkflowFormAttributeMappingRules::TYPE_AT_LEAST_X_BEFORE_TRIGGERED_DATE:
                    if (!DateTimeUtil::isDateValueNull($model, $attribute) &&
                        static::sanitize($model->$attribute, true) <= static::sanitizeAndResolveThirdValue())
                    {
                        return true;
                    }
                    break;
                case MixedDateTypesTriggerForWorkflowFormAttributeMappingRules::TYPE_LESS_THAN_X_AFTER_TRIGGERED_DATE:
                    if (!DateTimeUtil::isDateValueNull($model, $attribute) &&
                        static::sanitize($model->$attribute, true) < static::sanitizeAndResolveThirdValue())
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
         * @param $changeRequiredToProcess - if a change in value is required to confirm the time trigger is true
         * @return bool
         * @throws NotSupportedException
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute, $changeRequiredToProcess = true)
        {
            assert('is_string($attribute)');
            assert('is_bool($changeRequiredToProcess)');
            if ($this->trigger->valueType != MixedDateTypesSearchFormAttributeMappingRules::TYPE_IS_TIME_FOR)
            {
                throw new NotSupportedException();
            }
            return (array_key_exists($attribute, $model->originalAttributeValues)  || !$changeRequiredToProcess) &&
                   (DateTimeUtil::isDateValueNull($model, $attribute) === false);
        }

        /**
         * @param $value
         * @param bool $adjustForTimezone
         * @return int|mixed
         */
        protected function sanitize($value, $adjustForTimezone = false)
        {
            if ($adjustForTimezone)
            {
                $timeZone = date_default_timezone_get();
                date_default_timezone_set('GMT');
            }
            $timeStamp = strtotime($value);
            if ($adjustForTimezone)
            {
                date_default_timezone_set($timeZone);
            }
            return $timeStamp;
        }

        /**
         * @param $value
         * @return int
         */
        protected function sanitizeTriggerValue($value)
        {
            return strtotime($value);
        }

        /**
         * @param RedBeanModel $model
         * @param $attribute
         * @return bool
         */
        protected function resolveAttributeValueIsChanged(RedBeanModel $model, $attribute)
        {
            return array_key_exists($attribute, $model->originalAttributeValues);
        }

        protected function sanitizeAndResolveThirdValue()
        {
            if ($this->trigger->valueEvaluationType == 'Date')
            {
                $todayDate = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(), time());
                $todayDateTime = DateTimeUtil::resolveDateAsDateTime($todayDate);
                return $this->trigger->resolveNewTimeStampForThirdValueDuration(strtotime($todayDateTime));
            }
            elseif ($this->trigger->valueEvaluationType == 'DateTime')
            {
                $timeZone = date_default_timezone_get();
                date_default_timezone_set('GMT');
                $timeStamp = $this->trigger->resolveNewTimeStampForThirdValueDuration(time());
                date_default_timezone_set($timeZone);
                return $timeStamp;
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>