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
     * Removes module permission data that is not applicable
     * for the administrative group module permissions user
     * interface.  Some modules, while they do have permissions
     * are not administered through the user interface.
     */
    class GroupModulePermissionsDataToEditViewAdapater
    {
        /**
         * @param array $data
         * @return array
         */
        public static function resolveData($data)
        {
            assert('is_array($data)');
            $resolvedData = array();
            foreach ($data as $moduleClassName => $modulePermissions)
            {
                if (in_array($moduleClassName, GroupModulePermissionsDataToEditViewAdapater::getAdministrableModuleClassNames()))
                {
                    $resolvedData[$moduleClassName] = $modulePermissions;
                }
            }
            return $resolvedData;
        }

        /**
         * @return array of module class names that are available for
         * module permissions administration through the user interface
         */
        public static function getAdministrableModuleClassNames()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $classToEvaluate     = new ReflectionClass($module);
                if (is_subclass_of($module, 'SecurableModule') && !$classToEvaluate->isAbstract() &&
                    $module::hasPermissions())
                {
                    $moduleClassNames[] = get_class($module);
                }
            }
            return $moduleClassNames;
        }
    }
?>