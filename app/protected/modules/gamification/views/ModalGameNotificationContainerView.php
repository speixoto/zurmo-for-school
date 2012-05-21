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
     * Class for displaying a modal window with a game notification.
     */
    class ModalGameNotificationContainerView extends View
    {
        protected $gameNotifications = array();

        /**
         * Given an array of GameNotification models.
         * @param array $gameNotifications
         */
        public function __construct($gameNotifications)
        {
            $this->gameNotifications = $gameNotifications;
        }

        protected function renderContent()
        {
            $content           = null;
            $index             = 0;
            foreach($this->gameNotifications as $notification)
            {
                $content .= self::renderNotificationContent($notification, $index);
                $index ++;
                if(!$notification->delete())
                {
                    throw new FailedToDeleteModelException();
                }
            }
            return $content;
        }

        protected static function renderNotificationContent($notification, $index)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModalGameNotificationView");
            $cClipWidget->beginWidget('zii.widgets.jui.CJuiDialog', array(
                'id' => 'ModalGameNotification' . $index,
                'options' => array(
                    'autoOpen' => true,
                    'modal'    => true,
                    'height'   => 400,
                    'width'    => 500,
                    'open'     => 'js:function(event, ui) {$(this).parent().children(".ui-dialog-titlebar").hide();}',
                ),
            ));
            $adapter = new GameNotificationToModalContentAdapter($notification);
            echo CHtml::tag('div', array(), $adapter->getIconCssName());
            echo CHtml::tag('div', array(), Yii::t('Default', 'Congratulations'));
            echo CHtml::tag('div', array(), $adapter->getMessageContent());
            echo '<br/>';
            echo CHtml::link(Yii::t('Default', 'Continue'), '#',
                             array('onClick' => '$("#ModalGameNotification' . $index . '").dialog("close");'));
            $cClipWidget->endWidget('zii.widgets.jui.CJuiDialog');
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ModalGameNotificationView'];
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>
