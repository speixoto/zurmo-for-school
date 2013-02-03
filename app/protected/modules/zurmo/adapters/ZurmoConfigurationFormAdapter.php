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
     * Class to adapt global configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class ZurmoConfigurationFormAdapter
    {
        /**
         * @return ZurmoConfigurationForm
         */
        public static function makeFormFromGlobalConfiguration()
        {
            $form                                        = new ZurmoConfigurationForm();
            $form->applicationName                       = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $form->timeZone                              = Yii::app()->timeZoneHelper->getGlobalValue();
            $form->listPageSize                          = Yii::app()->pagination->getGlobalValueByType('listPageSize');
            $form->subListPageSize                       = Yii::app()->pagination->getGlobalValueByType('subListPageSize');
            $form->modalListPageSize                     = Yii::app()->pagination->getGlobalValueByType('modalListPageSize');
            $form->dashboardListPageSize                 = Yii::app()->pagination->getGlobalValueByType('dashboardListPageSize');
            $form->gamificationModalNotificationsEnabled = Yii::app()->gameHelper->modalNotificationsEnabled;
            return $form;
        }

        /**
         * Given a ZurmoConfigurationForm, save the configuration global values.
         */
        public static function setConfigurationFromForm(ZurmoConfigurationForm $form)
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', $form->applicationName);
            Yii::app()->timeZoneHelper  ->setGlobalValue(                         (string)$form->timeZone);
            Yii::app()->pagination->setGlobalValueByType('listPageSize',          (int)   $form->listPageSize);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',       (int)   $form->subListPageSize);
            Yii::app()->pagination->setGlobalValueByType('modalListPageSize',     (int)   $form->modalListPageSize);
            Yii::app()->pagination->setGlobalValueByType('dashboardListPageSize', (int)   $form->dashboardListPageSize);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'gamificationModalNotificationsEnabled',
                                                    (boolean) $form->gamificationModalNotificationsEnabled);
       }
    }
?>