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
     * Form to all editing and viewing of email SMTP configuration values in the user interface.
     */
    class EmailSmtpConfigurationForm extends ConfigurationForm
    {
        public $host;
        public $port = 25;
        public $username;
        public $password;
        public $security;
        public $aTestToAddress;

        public function rules()
        {
            return array(
                array('host',                              'required'),
                array('host',                              'type',      'type' => 'string'),
                array('host',                              'length',    'min'  => 1, 'max' => 64),
                array('host',                              'smtpHostValidator'),
                array('port',                              'required'),
                array('port',                              'type',      'type' => 'integer'),
                array('port',                              'numerical', 'min'  => 1),
                array('username',                          'type',      'type' => 'string'),
                array('username',                          'length',    'min'  => 1, 'max' => 64),
                array('password',                          'type',      'type' => 'string'),
                array('password',                          'length',    'min'  => 1, 'max' => 64),
                array('security',                          'type',      'type' => 'string'),
                array('security',                          'length',    'min'  => 0, 'max' => 3),
                array('aTestToAddress',                    'email'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'host'                                 => Zurmo::t('ZurmoModule', 'Host'),
                'port'                                 => Zurmo::t('ZurmoModule', 'Port'),
                'username'                             => Zurmo::t('ZurmoModule', 'Username'),
                'password'                             => Zurmo::t('ZurmoModule', 'Password'),
                'security'                             => Zurmo::t('EmailMessagesModule', 'Extra Mail Settings'),
                'aTestToAddress'                       => Zurmo::t('EmailMessagesModule', 'Send a test email to')
            );
        }

        public function smtpHostValidator($attribute, $params)
        {
            assert('$attribute == "host"');
            if (preg_match('/gmail.com/', $this->$attribute) > 0 )
            {
                $message = Zurmo::t('EmailMessagesModule', 'Hang on there slick! Google SMTP policies prevent masking ' .
                                                           'of the Google email address, your emails will be sent from {username}',
                                                           array('{username}' => $this->username));
                Yii::app()->user->setFlash('notification', $message);
            }
        }
    }
?>