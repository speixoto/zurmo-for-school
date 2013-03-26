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
     * Form to work with multi-select drop downs
     */
    class MultiSelectDropDownWorkflowActionAttributeForm extends CustomFieldWorkflowActionAttributeForm
    {
        public function getValueElementType()
        {
            return 'MultiSelectStaticDropDownForWorkflow';
        }

        /**
         * Override to make sure the value attribute is set as an array.
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(array('value', 'type', 'type' => 'array')));
        }

        protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            $data                           = array();
            $data[static::TYPE_STATIC]      = Zurmo::t('WorkflowModule', 'As');
            return $data;
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
                $adapter->getModel()->{$attribute}->values->removeAll();
                $value        = new CustomFieldValue();
                $value->value = $this->value;
                $adapter->getModel()->{$attribute}->values->add($value);
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>