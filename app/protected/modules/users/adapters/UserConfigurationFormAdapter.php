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
     * Class to adapt a user's configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class UserConfigurationFormAdapter
    {
        public static $user;

        /**
         * @return UserConfigurationForm
         */
        public static function makeFormFromUserConfigurationByUser(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            static::$user                    = $user;
            $form                            = new UserConfigurationForm(static::$user->id);
            $form->listPageSize              = Yii::app()->pagination->getByUserAndType(static::$user, 'listPageSize');
            $form->subListPageSize           = Yii::app()->pagination->getByUserAndType(static::$user, 'subListPageSize');
            $form->themeColor                = Yii::app()->themeManager->resolveAndGetThemeColorValue(static::$user);
            $form->backgroundTexture         = Yii::app()->themeManager->resolveAndGetBackgroundTextureValue(static::$user);
            $form->hideWelcomeView           = static::resolveAndGetHideWelcomeViewValue(static::$user);
            $form->turnOffEmailNotifications = static::resolveAndGetTurnOffEmailNotificationsValue(static::$user);
            $form->defaultPermissionSetting  = static::resolveAndGetDefaultPermissionSetting(static::$user);
            $form->defaultPermissionGroupSetting = static::resolveAndGetDefaultPermissionGroupSetting(static::$user);
            return $form;
        }

        /**
         * Given a UserConfigurationForm and user, save the configuration values for the specified user.
         */
        public static function setConfigurationFromForm(UserConfigurationForm $form, User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            self::$user = $user;
            static::setConfigurationFromFormForUser($form);
        }

        /**
         * Given a UserConfigurationForm save the configuration values for the current user
         * and load values as active.
         */
        public static function setConfigurationFromFormForCurrentUser(UserConfigurationForm $form)
        {
            self::$user = Yii::app()->user->userModel;
            static::setConfigurationFromFormForUser($form);
        }

        public static function setConfigurationFromFormForUser(UserConfigurationForm $form)
        {
            assert('self::$user instanceOf User && self::$user->id > 0');
            Yii::app()->pagination->setByUserAndType(static::$user, 'listPageSize', (int)$form->listPageSize);
            Yii::app()->pagination->setByUserAndType(static::$user, 'subListPageSize', (int)$form->subListPageSize);
            Yii::app()->themeManager->setThemeColorValue(static::$user, $form->themeColor);
            Yii::app()->themeManager->setBackgroundTextureValue(static::$user, $form->backgroundTexture);
            static::setHideWelcomeViewValue(static::$user, (bool)$form->hideWelcomeView);
            static::setTurnOffEmailNotificationsValue(static::$user, (bool)$form->turnOffEmailNotifications);
            static::setDefaultPermissionSettingValue(static::$user, (int)$form->defaultPermissionSetting);
            static::setDefaultPermissionGroupSetting(static::$user, (int)$form->defaultPermissionGroupSetting,
                (int)$form->defaultPermissionSetting);

        }

        public static function resolveAndGetHideWelcomeViewValue(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $hide = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'hideWelcomeView'))
            {
                return $hide;
            }
            else
            {
                return false;
            }
        }

        public static function setHideWelcomeViewValue(User $user, $value)
        {
            assert('is_bool($value)');
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'hideWelcomeView', $value);
        }

        public static function resolveAndGetTurnOffEmailNotificationsValue(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $turnOff = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'turnOffEmailNotifications'))
            {
                return $turnOff;
            }
            else
            {
                return false;
            }
        }

        public static function setTurnOffEmailNotificationsValue(User $user, $value)
        {
            assert('is_bool($value)');
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'turnOffEmailNotifications', $value);
        }

        public static function resolveAndGetDefaultPermissionSetting(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $defaultPermission = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule',
                'defaultPermissionSetting'))
            {
                return $defaultPermission;
            }
            else
            {
                return UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE;
            }
        }

        public static function resolveAndGetDefaultPermissionGroupSetting(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            return ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionGroupSetting');
        }

        public static function setDefaultPermissionSettingValue(User $user, $value)
        {
            assert('is_int($value)');
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionSetting', $value);
        }

        public static function setDefaultPermissionGroupSetting(User $user, $value, $defaultPermissionSetting)
        {
            assert('is_int($value)');
            assert('is_int($defaultPermissionSetting)');
            if ($defaultPermissionSetting == UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP)
            {
                ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionGroupSetting',
                    $value);
            }
            else
            {
                ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionGroupSetting',
                    null);
            }
        }
    }
?>