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

    class RolesModule extends SecurableModule
    {
        const RIGHT_CREATE_ROLES = 'Create Roles';
        const RIGHT_DELETE_ROLES = 'Delete Roles';
        const RIGHT_ACCESS_ROLES = 'Access Roles Tab';

        public static function getTranslatedRightsLabels()
        {
            $labels                             = array();
            $labels[self::RIGHT_CREATE_ROLES] = Zurmo::t('ZurmoModule', 'Create Roles');
            $labels[self::RIGHT_DELETE_ROLES] = Zurmo::t('ZurmoModule', 'Delete Roles');
            $labels[self::RIGHT_ACCESS_ROLES] = Zurmo::t('ZurmoModule', 'Access Roles Tab');
            return $labels;
        }

        public function canDisable()
        {
            return false;
        }

        public function getDependencies()
        {
            return array('zurmo');
        }

        public function getRootModelNames()
        {
            return array('Role');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'adminTabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ZurmoModule', 'Roles')",
                        'url'   => array('/zurmo/role'),
                        'right' => self::RIGHT_ACCESS_ROLES,
                    ),
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Roles')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Roles')",
                        'route'            => '/zurmo/role',
                        'right'            => self::RIGHT_ACCESS_ROLES,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Role';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_ROLES;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_ROLES;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_ROLES;
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('RolesDemoDataMaker');
        }
    }
?>
