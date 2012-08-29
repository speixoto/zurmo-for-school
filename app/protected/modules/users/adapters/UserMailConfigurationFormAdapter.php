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
     * Class to adapt user's mail configuration values into a configuration form.
     */
    class UserMailConfigurationFormAdapter
    {

        /**
         * Contains array of the UserMailConfigurationForm that will be saved or show
         * @var array
         */
        private static $formFieldsToSave = array(
            'fromName',
            'fromAddress',
            'replyToName',
            'replyToAddress',
            'outboundType',
            'outboundHost',
            'outboundPort',
            'outboundUsername',
            'outboundPassword',
            'outboundSecurity'
        );

        /**
         * @return UserMailConfigurationForm
         */
        public static function makeFormFromUserMailConfiguration(User $user)
        {
            $form = new UserMailConfigurationForm($user);
            foreach (static::$formFieldsToSave as $keyName)
            {
                $form->$keyName = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'EmailMessagesModule', $keyName);
                if (!isset($form->$keyName))
                {
                    if ($keyName == 'fromName' || $keyName == 'replyToName')
                    {
                        $form->$keyName = $user->getFullName();
                    }
                    if ($keyName == 'fromAddress' || $keyName == 'replyToAddress')
                    {
                        $form->$keyName = $user->primaryEmail;
                    }
                }
            }
            return $form;
        }

        /**
         * Given a UserMailConfigurationForm, save the values in the user's configuration.
         * @param UserMailConfigurationForm $form
         * @param User $user
         */
        public static function setUserMailConfigurationFromForm(UserMailConfigurationForm $form, User $user)
        {
            foreach (static::$formFieldsToSave as $keyName)
            {
                if ($form->outboundType == UserMailConfigurationForm::EMAIL_OUTBOUND_CUSTOM_SETTINGS &&
                   ($keyName != 'outboundHost'     || $keyName != 'outboundPort'     ||
                    $keyName != 'outboundUsername' || $keyName != 'outboundPassword' ||
                    $keyName != 'outboundSecurity'))
                {
                    ZurmoConfigurationUtil::setByUserAndModuleName($user, 'EmailMessagesModule', $keyName, $form->$keyName);
                }
                elseif (!($form->outboundType == UserMailConfigurationForm::EMAIL_OUTBOUND_CUSTOM_SETTINGS))
                {
                    ZurmoConfigurationUtil::setByUserAndModuleName($user, 'EmailMessagesModule', $keyName, $form->$keyName);
                }
            }

       }
    }
?>