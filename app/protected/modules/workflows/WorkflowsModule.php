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

    class WorkflowsModule extends SecurableModule
    {
        const RIGHT_CREATE_WORKFLOWS = 'Create Workflows';
        const RIGHT_DELETE_WORKFLOWS = 'Delete Workflows';
        const RIGHT_ACCESS_WORKFLOWS = 'Access Workflows Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('SavedWorkflow', 'ByTimeWorkflowInQueue');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'globalSearchAttributeNames' => array(
                    'name',
                ),
                'adminTabMenuItems' => array(
                        array(
                            'label' => 'Workflows',
                            'url'   => array('/workflows/default'),
                            'right' => self::RIGHT_ACCESS_WORKFLOWS,
                        ),
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('UsersModule', 'Workflows')",
                        'descriptionLabel' => "eval:Zurmo::t('UsersModule', 'Manage Workflows')",
                        'route'            => '/workflows/default',
                        'right'            => self::RIGHT_CREATE_WORKFLOWS,
                    ),
                ),
                'headerMenuItems' => array(
                    array(
                        'label' => 'Workflows',
                        'url'   => array('/workflows/default'),
                        'right' => self::RIGHT_ACCESS_WORKFLOWS,
                        'order' => 8,
                    ),
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label' => 'Workflow',
                        'url'   => array('/workflows/default/selectType'),
                        'right' => self::RIGHT_CREATE_WORKFLOWS,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'SavedWorkflow';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_WORKFLOWS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_WORKFLOWS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_WORKFLOWS;
        }

        public static function getDefaultDataMakerClassName()
        {
          //  return 'WorkflowsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassName()
        {
          //  return 'WorkflowsDemoDataMaker';
        }

        /**
         * Even though workflows are never globally searched, the search form can still be used by a specific
         * search view for a module.  Either this module or a related module.  This is why a class is returned.
         * @see modelsAreNeverGloballySearched controls it not being searchable though in the global search.
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'WorkflowsSearchForm';
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        public static function hasPermissions()
        {
            return false;
        }
    }
?>
