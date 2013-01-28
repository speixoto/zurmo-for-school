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

    class CampaignsModule extends SecurableModule
    {
        const RIGHT_CREATE_CAMPAIGNS = 'Create Campaigns';
        const RIGHT_DELETE_CAMPAIGNS = 'Delete Campaigns';
        const RIGHT_ACCESS_CAMPAIGNS = 'Access Campaigns';

        public function getDependencies()
        {
            return array(
                'activities',
            );
        }

        public function getRootModelNames()
        {
            return array('Campaign');
        }

        public static function getUntranslatedRightsLabels()
        {
            $labels                           = array();
            $labels[self::RIGHT_CREATE_CAMPAIGNS] = 'Create CampaignsModulePluralLabel';
            $labels[self::RIGHT_DELETE_CAMPAIGNS] = 'Delete CampaignsModulePluralLabel';
            $labels[self::RIGHT_ACCESS_CAMPAIGNS] = 'Access CampaignsModulePluralLabel';
            return $labels;
        }

        public static function getPrimaryModelName()
        {
            return 'Campaign';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CAMPAIGNS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CAMPAIGNS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_CAMPAIGNS;
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'CampaignsSearchForm';
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'globalSearchAttributeNames' => array(
                    'name',
                ),
            );
            return $metadata;
        }
    }
?>
