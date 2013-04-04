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

    class HomeModule extends SecurableModule
    {
        const RIGHT_CREATE_DASHBOARDS = 'Create Dashboards';
        const RIGHT_DELETE_DASHBOARDS = 'Delete Dashboards';
        const RIGHT_ACCESS_DASHBOARDS = 'Access Dashboards';

        public static function getTranslatedRightsLabels()
        {
            $labels                              = array();
            $labels[self::RIGHT_CREATE_DASHBOARDS] = Zurmo::t('HomeModule', 'Create Dashboards');
            $labels[self::RIGHT_DELETE_DASHBOARDS] = Zurmo::t('HomeModule', 'Delete Dashboards');
            $labels[self::RIGHT_ACCESS_DASHBOARDS] = Zurmo::t('HomeModule', 'Access Dashboards');
            return $labels;
        }

        public function getDependencies()
        {
            return array(
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Dashboard');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'adminTabMenuItems' => array(
                    array(
                        'label'       => "eval:Zurmo::t('HomeModule', 'Back to Application')",
                        'url'         => array('/home/default'),
                        'itemOptions' => array('class' => 'back-to-app-menu-item')
                    ),
                ),
                'tabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('HomeModule', 'Home')",
                        'url'   => array('/home/default'),
                    ),
                ),
            );
            return $metadata;
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('HomeModule', 'Home', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return static::getSingularModuleLabel($language);
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_DASHBOARDS;
        }
    }
?>
