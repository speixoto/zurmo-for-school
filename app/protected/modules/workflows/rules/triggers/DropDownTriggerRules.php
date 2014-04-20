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
     * Class to help evaluate CustomField triggers against model values.
     */
    class DropDownTriggerRules extends TriggerRules
    {
        /**
         * @param RedBeanModel $model
         * @param string $attribute
         * @return bool
         * @throws NotSupportedException
         */
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            switch($this->trigger->getOperator())
            {
                case OperatorRules::TYPE_EQUALS:
                    if (static::sanitize($model->{$attribute}->value) === static::sanitize($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_EQUAL:
                    if (static::sanitize($model->{$attribute}->value) !== static::sanitize($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_ONE_OF:
                    if (!is_array(static::sanitize($this->trigger->value)))
                    {
                        return false;
                    }
                    if (in_array(static::sanitize($model->{$attribute}->value), static::sanitize($this->trigger->value)))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_CHANGES:
                    if (array_key_exists('value', $model->{$attribute}->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_CHANGE:
                    if (!array_key_exists('value', $model->{$attribute}->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES:
                    if (array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        static::sanitize($model->{$attribute}->value) === static::sanitize($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS:
                    if (array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        static::sanitize($model->{$attribute}->originalAttributeValues['value']) ===
                        static::sanitize($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES_ONE_OF:
                    if (!is_array(static::sanitize($this->trigger->value)))
                    {
                        return false;
                    }
                    if (array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        in_array(static::sanitize($model->{$attribute}->value), static::sanitize($this->trigger->value)))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS_ONE_OF:
                    if (!is_array(static::sanitize($this->trigger->value)))
                    {
                        return false;
                    }
                    if (array_key_exists('value', $model->{$attribute}->originalAttributeValues) &&
                        in_array(static::sanitize($model->{$attribute}->originalAttributeValues['value']),
                            static::sanitize($this->trigger->value)))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_EMPTY:
                    if (empty($model->{$attribute}->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NOT_EMPTY:
                    if (!empty($model->{$attribute}->value))
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
         * @param string $attribute
         * @param boolean $changeRequiredToProcess - if a change in value is required to confirm the time trigger is true
         * @return bool
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute, $changeRequiredToProcess = true)
        {
            assert('is_string($attribute)');
            assert('is_bool($changeRequiredToProcess)');
            if (array_key_exists('value', $model->{$attribute}->originalAttributeValues) || !$changeRequiredToProcess)
            {
                if ($this->trigger->getOperator() == OperatorRules::TYPE_DOES_NOT_CHANGE)
                {
                    return true;
                }
                return $this->evaluateBeforeSave($model, $attribute);
            }
            return false;
        }
    }
?>