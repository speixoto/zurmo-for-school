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

    class CalendarModule extends SecurableModule
    {
        const RIGHT_CREATE_CALENDAR = 'Create Calendar';
        const RIGHT_DELETE_CALENDAR = 'Delete Calendar';
        const RIGHT_ACCESS_CALENDAR = 'Access Calandar Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('SavedCalendar');
        }

        public static function getTranslatedRightsLabels()
        {
            $params                              = LabelUtil::getTranslationParamsForAllModules();
            $labels                              = array();
            $labels[self::RIGHT_CREATE_CALENDAR] = Zurmo::t('CalendarModule', 'Create CalendarModulePluralLabel',     $params);
            $labels[self::RIGHT_DELETE_CALENDAR] = Zurmo::t('CalendarModule', 'Delete CalendarModulePluralLabel',     $params);
            $labels[self::RIGHT_ACCESS_CALENDAR] = Zurmo::t('CalendarModule', 'Access CalendarModulePluralLabel Tab', $params);
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'designerMenuItems' => array(
                    'showFieldsLink' => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink' => true,
                ),
                'globalSearchAttributeNames' => array(
                    'quantity',
                    'name'
                ),
                'tabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('CalendarModule', 'CalendarModulePluralLabel', \$translationParams)",
                        'url'   => array('/calendar/default'),
                        'right' => self::RIGHT_ACCESS_CALENDAR,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'SavedCalendar';
        }

        public static function getSingularCamelCasedName()
        {
            return 'Calendar';
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('CalendarModule', 'Calendar', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('CalendarModule', 'Calendars', array(), null, $language);
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CALENDAR;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CALENDAR;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_CALENDAR;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'CalendarDefaultDataMaker';
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('CalendarDemoDataMaker');
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'CalendarSearchForm';
        }

        public static function hasPermissions()
        {
            return true;
        }

        public static function isReportable()
        {
            return true;
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        public static function canHaveWorkflow()
        {
            return false;
        }
    }
?>