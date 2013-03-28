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
     * Form to work with date attributes
     */
    class DateWorkflowActionAttributeForm extends WorkflowActionAttributeForm
    {
        const TYPE_DYNAMIC_FROM_TRIGGERED_DATE = 'DynamicFromTriggeredDate';

        const TYPE_DYNAMIC_FROM_EXISTING_DATE = 'DynamicFromExistingDate';

        public function getDynamicTypeValueDropDownArray()
        {
            $data       = array();
            WorkflowUtil::resolveNegativeDurationAsDistanceFromPointData($data, false);
            $data[0]    = Zurmo::t('WorkflowsModule', '0 days');
            WorkflowUtil::resolvePositiveDurationAsDistanceFromPointData($data, false);
            return $data;
        }

        public function getValueElementType()
        {
            return 'MixedDateTypesForWorkflowActionAttribute';
        }

        /**
         * Value can either be date or if dynamic, then it is an integer
         * @return bool
         */
        public function validateValue()
        {
            if(parent::validateValue())
            {
                if($this->type == self::TYPE_STATIC)
                {
                    $validator = CValidator::createValidator('TypeValidator', $this, 'value', array('type' => 'date'));
                    $validator->validate($this);
                    return !$this->hasErrors();
                }
                else
                {
                    $validator             = CValidator::createValidator('TypeValidator', $this, 'alternateValue', array('type' => 'integer'));
                    $validator->allowEmpty = false;
                    $validator->validate($this);
                    return !$this->hasErrors();
                }
            }
            return false;
        }

        /**
         * Utilized to create or update model attribute values after a workflow's triggers are fired as true.
         * @param WorkflowActionProcessingModelAdapter $adapter
         * @param $attribute
         * @throws NotSupportedException
         */
        public function resolveValueAndSetToModel(WorkflowActionProcessingModelAdapter $adapter, $attribute)
        {
            assert('is_string($attribute)');
            if($this->type == static::TYPE_STATIC)
            {
                $adapter->getModel()->{$attribute} = $this->value;
            }
            elseif($this->type == self::TYPE_DYNAMIC_FROM_TRIGGERED_DATE)
            {
                $adapter->getModel()->{$attribute} = DateTimeUtil::convertTimestampToDbFormatDate(time() + $this->value);
            }
            elseif($this->type == self::TYPE_DYNAMIC_FROM_EXISTING_DATE)
            {
                if(!DateTimeUtil::isDateStringNull($adapter->getModel()->{$attribute}))
                {
                    $existingTimeStamp = DateTimeUtil::convertDbFormatDateTimeToTimestamp(
                                            DateTimeUtil::resolveDateAsDateTime($adapter->getModel()->{$attribute}));
                    $newDate           = DateTimeUtil::convertTimestampToDbFormatDate($existingTimeStamp + $this->value);
                    $adapter->getModel()->{$attribute} = $newDate;
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            $data                                            = array();
            $data[static::TYPE_STATIC]                       = Zurmo::t('WorkflowsModule', 'Specifically on');
            $data[self::TYPE_DYNAMIC_FROM_TRIGGERED_DATE]    = Zurmo::t('WorkflowsModule', 'Dynamically From Triggered Date');
            if(!$isCreatingNewModel)
            {
                $data[self::TYPE_DYNAMIC_FROM_EXISTING_DATE] = Zurmo::t('WorkflowsModule', 'Dynamically From Existing Date');
            }
            return $data;
        }
    }
?>