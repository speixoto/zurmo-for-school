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
     * Helper class for generating notifications
     */
    class ZurmoNotificationUtil
    {
        public static function renderDesktopNotificationsScript()
        {
            $script = "
            var desktopNotifications = {
                notify:function(image,title,body) {
                    if (window.webkitNotifications.checkPermission() == 0) {
                        window.webkitNotifications.createNotification(image, title, body).show();
                        return true;
                    }
                    return false;
                },
                isSupported:function() {
                    if (window.webkitNotifications != 'undefined') {
                        return true
                    } else {
                        return false
                    }
                },
                requestAutorization:function() {
                    if (this.isSupported)
                    {
                        if (window.webkitNotifications.checkPermission() == 1)
                        {
                            window.webkitNotifications.requestPermission();
                        }
                        else if (window.webkitNotifications.checkPermission() == 2)
                        {
                            alert('" . Yii::t('Default', 'You have denied desktop notifications. Check your browser settings to change it.') . "');
                        }
                        else
                        {
                            alert('" . Yii::t('Default', 'You have already enable desktop notifications.') . "');
                        }
                    }
                    else
                    {
                        alert('" . Yii::t('Default', 'Sorry! Your browser does not support desktop notifications.') . "');
                    }
                }
            };
            ";
            Yii::app()->clientScript->registerScript('AutoUpdater', $script, CClientScript::POS_HEAD);
        }
        public static function renderAutoUpdaterScript()
        {
            $script = "
                    var convPlacer = $('#MenuView').find('li.last').find('span:last'); //TODO: Make an id for this span
                    var notiPlacer = $('#notifications-link');
                    var uconv      = convPlacer.text();
                    var unoti      = notiPlacer.text();
                    var url        = '" . Yii::app()->createUrl('zurmo/default/getUpdatesForRefresh') . "';
                    if(typeof(EventSource) !== 'undefined') {
                        var source = new EventSource(url + '?uconv=' + uconv + '&unoti=' + unoti);
                        source.addEventListener('updateConversations', function(e) {
                          var data = JSON.parse(e.data);
                            if (uconv != data.unreadConversations) {
                                uconv = data.unreadConversations;
                                convPlacer.html(uconv);
                                if (desktopNotifications.isSupported()) {
                                    desktopNotifications.notify(data.imageUrl,
                                                                data.title,
                                                                data.message);
                                }
                            }
                        }, false);
                    } else {
                        //TODO: What to do when browser not support EventSource?
                    }
                ";
            Yii::app()->clientScript->registerScript('AutoUpdater', $script, CClientScript::POS_READY);
        }
    }
?>
