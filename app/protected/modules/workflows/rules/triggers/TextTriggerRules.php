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
     * Class to help evaluate text triggers against model values
     */
    class TextTriggerRules extends TriggerRules
    {
        //todo: remove rems, check other rules too.
        //todO: some of these evals like startsWith endsWith even, equals, can but into a static method in parent TriggerEvaluationRules class?
        public function evaluateBeforeSave(RedBeanModel $model, $attribute)
        {
            switch($this->trigger->getOperator())
            {

                case OperatorRules::TYPE_EQUALS:
                    if(strtolower($model->$attribute) === strtolower($this->trigger->value))
                    {
                        // echo ' testX ' . $resolvedModel->$attribute .  ' bestX ' . $trigger->value  . "\n";
                        // echo 'returning here ' . "\n";
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_EQUAL:
                    if(strtolower($model->$attribute) !== strtolower($this->trigger->value))
                    {
                        //echo ' testY ' . $resolvedModel->$attribute .  ' bestY ' . $trigger->value  . "\n";
                        //echo 'returning here ' . "\n";
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NULL:
                    if($model->$attribute === null)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NOT_NULL:
                    if($model->$attribute !== null)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_EMPTY:
                    if(empty($model->$attribute))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_IS_NOT_EMPTY:
                    if(!empty($model->$attribute))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_STARTS_WITH:
                    if(!strncmp(strtolower($model->$attribute),
                        strtolower($this->trigger->value),
                        strlen(strtolower($this->trigger->value))))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_ENDS_WITH:
                    if(substr(strtolower($model->$attribute), - strlen(strtolower($this->trigger->value))) ===
                        strtolower($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_CONTAINS:
                    if((stripos($model->$attribute, $this->trigger->value)) !== false)
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_CHANGES:
                    if(array_key_exists($attribute, $model->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_DOES_NOT_CHANGE:
                    if(!array_key_exists($attribute, $model->originalAttributeValues))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_BECOMES:
                    if(array_key_exists($attribute, $model->originalAttributeValues) &&
                        strtolower($model->$attribute) === strtolower($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                case OperatorRules::TYPE_WAS:
                    if(array_key_exists($attribute, $model->originalAttributeValues) &&
                        strtolower($model->originalAttributeValues[$attribute]) ===
                            strtolower($this->trigger->value))
                    {
                        return true;
                    }
                    break;
                default:
                    throw new NotSupportedException();
            }
            return false;
        }
    }
?>