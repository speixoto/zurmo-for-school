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
     * Class to help the workflow engine understand how to evaluate various triggers
     */
    abstract class TriggerRules
    {
        protected $trigger;

        public function __construct(TriggerForWorkflowForm $trigger)
        {
            $this->trigger = $trigger;
        }
        abstract public function evaluateBeforeSave(RedBeanModel $model, $attribute);

        /**
         * For a time trigger, the value must first 'change'.  If the operator is TYPE_DOES_NOT_CHANGE, then we can
         * assume true since any 'change' pushes out the time expiration.  If the value does 'change', then the
         * operator can be evaluated normally.
         * @param RedBeanModel $model
         * @param $attribute
         * @return bool
         */
        public function evaluateTimeTriggerBeforeSave(RedBeanModel $model, $attribute)
        {
            if(array_key_exists($attribute, $model->originalAttributeValues))
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
         * Override as needed to add specific sanitization routines.  Text for example, has to use strtolower
         * @param $value
         * @return mixed
         */
        protected function sanitize($value)
        {
            return $value;
        }
    }
?>