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
     * Helper class to dynamically generate
     * view metadata based on data array.
     */
    class ModulePermissionsEditViewUtil extends ModulePermissionsViewUtil
    {
        protected static function getElementInformation($moduleClassName, $permission, $permissionInformation)
        {
            if ($permissionInformation['inherited']     == Permission::DENY)
            {
                $type = 'PermissionInheritedDenyText';
            }
            elseif ($permissionInformation['inherited'] == Permission::ALLOW &&
                    $permissionInformation['explicit']  == null)
            {
                $type = 'PermissionInheritedAllowStaticDropDown';
            }
            else
            {
                $type = 'PermissionStaticDropDown';
            }
            $element = array(
                        'attributeName' =>
                            FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                $moduleClassName,
                                $permission),
                        'type'          => $type,
                    );
            return $element;
        }

        /**
         * The WRITE column in the user interface is a combination of
         * the WRITE, CHANGE_OWNER, and CHANGE_PERMISSIONS permissions.
         * WRITE controlls the other 2 permissions in the user interface.
         * @return array - resolved data
         */
        public static function resolveWritePermissionsFromArray($data)
        {
            assert('is_array($data)');
            foreach ($data as $moduleNamePermission => $value)
            {
                $nameParts                          = explode(FormModelUtil::DELIMITER, $moduleNamePermission);
                list($moduleClassName, $permission) = $nameParts;
                assert('is_numeric($permission)');
                if ($permission == Permission::WRITE)
                {
                    $index        = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                        $moduleClassName,
                                        Permission::CHANGE_OWNER);
                    $data[$index] = $value;
                    $index        = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                        $moduleClassName,
                                        Permission::CHANGE_PERMISSIONS);
                    $data[$index] = $value;
                }
            }
            return $data;
        }
    }
?>