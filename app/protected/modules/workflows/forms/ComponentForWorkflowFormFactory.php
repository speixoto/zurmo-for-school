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
     * Helper class to create ComponentForWorkflowForm based objects
     */
    class ComponentForWorkflowFormFactory
    {
        /**
         * Make and return the correct ComponentForWorkflowForm based object
         * @param $moduleClassName string
         * @param $modelClassName string
         * @param $type string
         * @param $componentType string
         * @return TimeTriggerForWorkflowForm|TriggerForWorkflowForm|ActionForWorkflowForm
         * @throws NotSupportedException
         */
        public static function makeByComponentType($moduleClassName, $modelClassName, $type, $componentType)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($type)');
            assert('is_string($componentType)');
            if($componentType == ComponentForWorkflowForm::TYPE_TIME_TRIGGER)
            {
                return new TimeTriggerForWorkflowForm($moduleClassName, $modelClassName, $type);
            }
            elseif($componentType == ComponentForWorkflowForm::TYPE_TRIGGERS)
            {
                return new TriggerForWorkflowForm($moduleClassName, $modelClassName, $type);
            }
            elseif($componentType == ComponentForWorkflowForm::TYPE_ACTION)
            {
                return new ActionForWorkflowForm($moduleClassName, $modelClassName, $type);
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>