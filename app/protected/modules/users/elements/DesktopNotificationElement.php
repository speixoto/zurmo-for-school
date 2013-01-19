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
     * Display a button to activate the browser's desktop notifications
     */
    class DesktopNotificationElement extends Element
    {
        /**
         * Renders the button.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $this->renderScript();
            $content                 = $this->renderRequestBrowserAutorizationButton();
            $content                .= $this->renderEnableDesktopNotificationsCheckBox();
            return ZurmoHtml::tag('div', array('id' => 'enableDesktopNotifications'), $content);
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function renderRequestBrowserAutorizationButton()
        {
            $content  = ZurmoHtml::link(Yii::t('Default', 'Activate Desktop Notifications on Browser'),
                                              '',
                                              array('onClick' => 'js:desktopNotifications.requestAutorization(); return false;'));
            $content .= $this->renderTooltipContent();
            return ZurmoHtml::tag('span', array('style' => 'display:none'), $content);
        }

        protected function renderEnableDesktopNotificationsCheckBox()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $content                 = $this->form->checkBox($this->model, $this->attribute, $htmlOptions);
            return ZurmoHtml::tag('span', array('style' => 'display:none'), $content);
        }

        protected static function renderTooltipContent()
        {
            $title       = Yii::t('Default', 'Desktop Notifications is a pop-up to warn you when new events occurs in Zurmo. </br>' .
                                             'The pop-up will appear as a Desktop Notification but you need to use a browser' .
                                             ' that can show this notifications, like Chrome.');
            $content     = '<span id="user-desktop-notifications-tooltip" class="tooltip"  title="' . $title . '">';
            $content    .= '?</span>';
            $qtip        = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'))));
            $qtip->addQTip("#user-desktop-notifications-tooltip");
            return $content;
        }

        private function renderScript()
        {
            $errorNoBrowserSupport  = Yii::t('Default', 'Sorry! Your browser does not support desktop notifications.');
            $errorDenied            = Yii::t('Default', 'You have denied desktop notifications. Check your browser settings to change it.');
            $script = "
                    if (desktopNotifications.isSupported)
                    {
                        if (window.webkitNotifications.checkPermission() == 1)
                        {
                            $('#enableDesktopNotifications span:nth-child(1)').toggle();
                        }
                        else if (window.webkitNotifications.checkPermission() == 2)
                        {
                            $('#enableDesktopNotifications').html('${errorDenied}');
                        }
                        else
                        {
                            $('#enableDesktopNotifications span:nth-child(2)').toggle();
                        }
                    }
                    else
                    {
                        $('#enableDesktopNotifications').html('${errorNoBrowserSupport}');
                    }
                ";
            Yii::app()->clientScript->registerScript('EnableDesktopNotifications', $script, CClientScript::POS_READY);
        }
    }
?>