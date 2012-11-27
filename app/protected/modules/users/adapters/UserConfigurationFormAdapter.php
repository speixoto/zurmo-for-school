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
        /**
         * @return UserConfigurationForm
         */
        public static function makeFormFromUserConfigurationByUser(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            $form                            = new UserConfigurationForm($user->id);
            $form->listPageSize              = Yii::app()->pagination->getByUserAndType($user, 'listPageSize');
            $form->subListPageSize           = Yii::app()->pagination->getByUserAndType($user, 'subListPageSize');
            $form->themeColor                = Yii::app()->themeManager->resolveAndGetThemeColorValue($user);
            $form->backgroundTexture         = Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($user);
            $form->hideWelcomeView           = static::resolveAndGetHideWelcomeViewValue($user);
            $form->turnOffEmailNotifications = static::resolveAndGetTurnOffEmailNotificationsValue($user);
            return $form;
        }

        /**
         * Given a UserConfigurationForm and user, save the configuration values for the specified user.
         */
        public static function setConfigurationFromForm(UserConfigurationForm $form, User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            Yii::app()->pagination    ->setByUserAndType        ($user, 'listPageSize',    (int)$form->listPageSize);
            Yii::app()->pagination    ->setByUserAndType        ($user, 'subListPageSize', (int)$form->subListPageSize);
            Yii::app()->themeManager->setThemeColorValue        ($user, $form->themeColor);
            Yii::app()->themeManager->setBackgroundTextureValue ($user, $form->backgroundTexture);
            static::setHideWelcomeViewValue                     ($user, (bool)$form->hideWelcomeView);
            static::setTurnOffEmailNotificationsValue           ($user, (bool)$form->turnOffEmailNotifications);
        }

        /**
         * Given a UserConfigurationForm save the configuration values for the current user
         * and load values as active.
         */
        public static function setConfigurationFromFormForCurrentUser(UserConfigurationForm $form)
        {
            Yii::app()->pagination    ->setForCurrentUserByType ('listPageSize',     (int)$form->listPageSize);
            Yii::app()->pagination    ->setForCurrentUserByType ('subListPageSize',  (int)$form->subListPageSize);
            Yii::app()->themeManager->setThemeColorValue        (Yii::app()->user->userModel,       $form->themeColor);
            Yii::app()->themeManager->setBackgroundTextureValue (Yii::app()->user->userModel,       $form->backgroundTexture);
            static::setHideWelcomeViewValue                     (Yii::app()->user->userModel, (bool)$form->hideWelcomeView);
            static::setTurnOffEmailNotificationsValue           (Yii::app()->user->userModel, (bool)$form->turnOffEmailNotifications);
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
    }
?>