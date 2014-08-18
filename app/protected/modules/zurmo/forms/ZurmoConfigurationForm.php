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
     * Form to all editing and viewing of global configuration values in the user interface.
     */
    class ZurmoConfigurationForm extends ConfigurationForm
    {
        public $applicationName;
        public $timeZone;
        public $listPageSize;
        public $subListPageSize;
        public $modalListPageSize;
        public $dashboardListPageSize;
        public $defaultFromEmailAddress;
        public $defaultTestToEmailAddress;
        public $gamificationModalNotificationsEnabled;
        public $gamificationModalCollectionsEnabled;
        public $gamificationModalCoinsEnabled;
        public $realtimeUpdatesEnabled;
        public $reCaptchaPrivateKey;
        public $reCaptchaPublicKey;

        public function rules()
        {
            return array(
                array('applicationName',                        'type',    'type' => 'string'),
                array('applicationName',                        'length',  'max' => 64),
                array('timeZone',                               'required'),
                array('listPageSize',                           'required'),
                array('listPageSize',                           'type',      'type' => 'integer'),
                array('listPageSize',                           'numerical', 'min' => 1, 'max' => ZurmoSystemConfigurationUtil::getBatchSize()),
                array('subListPageSize',                        'required'),
                array('subListPageSize',                        'type',      'type' => 'integer'),
                array('subListPageSize',                        'numerical', 'min' => 1, 'max' => ZurmoSystemConfigurationUtil::getBatchSize()),
                array('modalListPageSize',                      'required'),
                array('modalListPageSize',                      'type',      'type' => 'integer'),
                array('modalListPageSize',                      'numerical', 'min' => 1, 'max' => ZurmoSystemConfigurationUtil::getBatchSize()),
                array('dashboardListPageSize',                  'required'),
                array('dashboardListPageSize',                  'type',      'type' => 'integer'),
                array('dashboardListPageSize',                  'numerical', 'min' => 1, 'max' => ZurmoSystemConfigurationUtil::getBatchSize()),
                array('defaultFromEmailAddress',                'email'),
                array('defaultFromEmailAddress',                'required'),
                array('defaultTestToEmailAddress',              'email'),
                array('defaultTestToEmailAddress',              'required'),
                array('gamificationModalNotificationsEnabled',  'boolean'),
                array('gamificationModalCollectionsEnabled',    'boolean'),
                array('gamificationModalCoinsEnabled',          'boolean'),
                array('realtimeUpdatesEnabled',                 'boolean'),
                array('subListPageSize',                        'type',      'type' => 'integer'),
                array('reCaptchaPrivateKey',                    'type',      'type' => 'string'),
                array('reCaptchaPublicKey',                     'type',      'type' => 'string'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'applicationName'                        => Zurmo::t('ZurmoModule', 'Application Name'),
                'timeZone'                               => Zurmo::t('ZurmoModule', 'Time Zone'),
                'listPageSize'                           => Zurmo::t('ZurmoModule', 'List page size'),
                'subListPageSize'                        => Zurmo::t('ZurmoModule', 'Sublist page size'),
                'modalListPageSize'                      => Zurmo::t('ZurmoModule', 'Popup list page size'),
                'dashboardListPageSize'                  => Zurmo::t('ZurmoModule', 'Dashboard portlet list page size'),
                'defaultFromEmailAddress'                => Zurmo::t('ZurmoModule', 'Default From Email Address'),
                'defaultTestToEmailAddress'              => Zurmo::t('ZurmoModule', 'Default Test To Email Address'),
                'gamificationModalNotificationsEnabled'  => Zurmo::t('ZurmoModule', 'Enable game notification popup'),
                'gamificationModalCollectionsEnabled'    => Zurmo::t('ZurmoModule', 'Enable game collection popup'),
                'gamificationModalCoinsEnabled'          => Zurmo::t('ZurmoModule', 'Enable game coin popup'),
                'realtimeUpdatesEnabled'                 => Zurmo::t('ZurmoModule', 'Enable real-time updates'),
                'reCaptchaPrivateKey'                    => Zurmo::t('ZurmoModule', 'ReCaptcha Private Key'),
                'reCaptchaPublicKey'                     => Zurmo::t('ZurmoModule', 'ReCaptcha Public Key'),
            );
        }
    }
?>