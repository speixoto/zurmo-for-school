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

    class MeetingsModule extends SecurableModule
    {
        const RIGHT_CREATE_MEETINGS = 'Create Meetings';
        const RIGHT_DELETE_MEETINGS = 'Delete Meetings';
        const RIGHT_ACCESS_MEETINGS = 'Access Meetings';

        public function getDependencies()
        {
            return array(
                'activities',
            );
        }

        public function getRootModelNames()
        {
            return array('Meeting');
        }

        public static function getTranslatedRightsLabels()
        {
            $params                              = LabelUtil::getTranslationParamsForAllModules();
            $labels                              = array();
            $labels[self::RIGHT_CREATE_MEETINGS] = Zurmo::t('MeetingsModule', 'Create MeetingsModulePluralLabel', $params);
            $labels[self::RIGHT_DELETE_MEETINGS] = Zurmo::t('MeetingsModule', 'Delete MeetingsModulePluralLabel', $params);
            $labels[self::RIGHT_ACCESS_MEETINGS] = Zurmo::t('MeetingsModule', 'Access MeetingsModulePluralLabel', $params);
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
                    'showMenusLink' => false,
                ),
                'globalSearchAttributeNames' => array(
                    'name',
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Meeting';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_MEETINGS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_MEETINGS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_MEETINGS;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'MeetingsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('MeetingsDemoDataMaker');
        }

        public static function hasPermissions()
        {
            return true;
        }

        /**
         * Even though meetings are never globally searched, the search form can still be used by a specific
         * search view for a module.  Either this module or a related module.  This is why a class is returned.
         * @see modelsAreNeverGloballySearched controls it not being searchable though in the global search.
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'MeetingsSearchForm';
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        public static function isReportable()
        {
            return true;
        }

        public static function canHaveWorkflow()
        {
            return true;
        }

        public static function canHaveContentTemplates()
        {
            return true;
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('MeetingsModule', 'Meeting', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('MeetingsModule', 'Meetings', array(), null, $language);
        }

        public static function canShowOnCalendar()
        {
            return true;
        }
    }
?>
