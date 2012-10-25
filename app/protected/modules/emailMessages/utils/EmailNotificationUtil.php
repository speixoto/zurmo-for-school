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

    class EmailNotificationUtil
    {
        /**
         * Based on the current theme, retrieve the email notification template for html content and replace the
         * content tags with the appropriate strings
         */
        public static function resolveNotificationHtmlTemplate($bodyContent)
        {
            assert('is_string($bodyContent)');
            $url                                = Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                  array('id' => Yii::app()->user->userModel->id));
            $htmlTemplate                       = self::getNotificationHtmlTemplate();
            $htmlContent                        = array();
            $htmlContent['{bodyContent}']       = $bodyContent;
            $htmlContent['{sourceContent}']     = Yii::t('Default', 'This message sent from Zurmo');
            $htmlContent['{preferenceContent}'] = ZurmoHtml::link(Yii::t('Default', 'Manage your email preferences'), $url);
            return strtr($htmlTemplate, $htmlContent);
        }

        protected static function getNotificationHtmlTemplate()
        {
            $theme        = Yii::app()->theme->name;
            $name         = 'NotificationEmailTemplate';
            $templateName = "themes/$theme/templates/$name.html";
            if (!file_exists($templateName))
            {
                $templateName = "themes/default/templates/$name.html";
            }
            if (file_exists($templateName))
            {
                return file_get_contents($templateName);
            }
        }

        /**
         * Based on the current theme, retrieve the email notification template for text content and replace the
         * content tags with the appropriate strings
         */
        public static function resolveNotificationTextTemplate($bodyContent)
        {
            assert('is_string($bodyContent)');
            $url                                = Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                  array('id' => Yii::app()->user->userModel->id));
            $htmlTemplate                       = self::getNotificationTextTemplate();
            $htmlContent                        = array();
            $htmlContent['{bodyContent}']       = $bodyContent;
            $htmlContent['{sourceContent}']     = Yii::t('Default', 'This message sent from Zurmo');
            $htmlContent['{preferenceContent}'] = Yii::t('Default', 'Manage your email preferences') . ZurmoHtml::link(null, $url);
            return strtr($htmlTemplate, $htmlContent);
        }

        protected static function getNotificationTextTemplate()
        {
            $theme        = Yii::app()->theme->name;
            $name         = 'NotificationEmailTemplate';
            $templateName = "themes/$theme/templates/$name.txt";
            if (!file_exists($templateName))
            {
                $templateName = "themes/default/templates/$name.txt";
            }
            if (file_exists($templateName))
            {
                return file_get_contents($templateName);
            }
        }
    }
?>