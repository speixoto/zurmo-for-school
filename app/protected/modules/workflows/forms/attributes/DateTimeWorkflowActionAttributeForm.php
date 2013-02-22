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
     * Form to work with dateTime attributes
     */
    class DateTimeWorkflowActionAttributeForm extends WorkflowActionAttributeForm
    {
        const TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME = 'DynamicFromTriggeredDateTime';

        const TYPE_DYNAMIC_FROM_EXISTING_DATETIME = 'DynamicFromExistingDateTime';

        /**
         * Value can either be dateTime or if dynamic, then it is an integer
         * @return bool
         */
        public function validateValue()
        {
            if(parent::validateValue())
            {
                if($this->type == self::TYPE_STATIC)
                {
                    $validator = CValidator::createValidator('TypeValidator', $this, 'value', array('type' => 'datetime'));
                    $validator->validate($this);
                    return !$this->hasErrors();
                }
                else
                {
                    $validator = CValidator::createValidator('type', $this, 'value', array('type' => 'integer'));
                    $validator->validate($this);
                    return !$this->hasErrors();
                }
            }
            return false;
        }
    }
?>