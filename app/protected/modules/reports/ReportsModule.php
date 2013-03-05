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
     * Module used to create and run reports
     */
    class ReportsModule extends SecurableModule
    {
        const RIGHT_CREATE_REPORTS = 'Create Reports';

        const RIGHT_DELETE_REPORTS = 'Delete Reports';

        const RIGHT_ACCESS_REPORTS = 'Access Reports Tab';

        /**
         * @return array
         */
        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        /**
         * @return array
         */
        public function getRootModelNames()
        {
            return array('SavedReport');
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'globalSearchAttributeNames' => array(
                    'name',
                ),
                'tabMenuItems' => array(
                    array(
                        'label' => 'Reports',
                        'url'   => array('/reports/default'),
                        'right' => self::RIGHT_ACCESS_REPORTS,
                    ),
                ),
                'headerMenuItems' => array(
                    array(
                        'label' => 'Reports',
                        'url'   => array('/reports/default'),
                        'right' => self::RIGHT_ACCESS_REPORTS,
                        'order' => 8,
                    ),
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label' => 'Report',
                        'url'   => array('/reports/default/selectType'),
                        'right' => self::RIGHT_CREATE_REPORTS,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'SavedReport';
        }

        /**
         * @return string
         */
        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_REPORTS;
        }

        /**
         * @return string
         */
        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_REPORTS;
        }

        /**
         * @return string
         */
        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_REPORTS;
        }

        public static function getDefaultDataMakerClassName()
        {
          //  return 'ReportsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassName()
        {
          //  return 'ReportsDemoDataMaker';
        }

        /**
         * Even though reports are never globally searched, the search form can still be used by a specific
         * search view for a module.  Either this module or a related module.  This is why a class is returned.
         * @see modelsAreNeverGloballySearched controls it not being searchable though in the global search.
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'ReportsSearchForm';
        }

        /**
         * @return bool
         */
        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasPermissions()
        {
            return true;
        }
    }
?>
