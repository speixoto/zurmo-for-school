<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Class for displaying a modal window with a game notification.
     */
    class GameCoinContainerView extends View
    {
        protected $gameNotifications = array();

        /**
         * @param CController $controller
         */
        public function __construct(CController $controller)
        {
            $this->controller = $controller;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            if(GameCoin::showCoin($this->controller))
            {
                $this->registerScripts();
                return $this->renderCoinContainerContent();
            }
        }

        protected function registerScripts()
        {
            $url    = $this->makeAjaxClickUrl();
            $script = "$('.random-game-coin').click(function(){
                            " . ZurmoHtml::ajax(array('type' => 'GET', 'url' =>  $url)) . "
                            //$(this).animate( { left:1200, top:300 }, 35, 'easeOutQuart' );
                            var audio = document.getElementById('game-coin-chime');
                            audio.play();
                            $('.game-coin').animate({top:15},75).hide(0);
                            $('.smoke').show(0).animate({top:0},500).animateSprite({
                                columns: 8,
                                totalFrames: 40,
                                duration: 500,
                                loop: false,
                            });
                        });";
           Yii::app()->clientScript->registerScript('gameCoinClickScript', $script);

        }

        protected function renderCoinContainerContent()
        {
            $content = $this->renderCoinContent();
            $content .= $this->renderAudioContent();
            return ZurmoHtml::tag('div', array('class' => 'random-game-coin'), $content);
        }

        protected function renderCoinContent()
        {
            $content = ZurmoHtml::tag('div', array('class' => 'game-coin'), 'THE RANDOM COIN - TODO REMOVE');
            $content .= ZurmoHtml::tag('div', array('class' => 'smoke'), '');
            return ZurmoHtml::tag('div', array(), $content);
        }

        protected function renderAudioContent()
        {
            $publishedAssetsPath = Yii::app()->assetManager->publish(
                                        Yii::getPathOfAlias("application.modules.gamification.views.assets.audio"));
            $audioFilePath = $publishedAssetsPath . '/chime.mp3';
            $content = ZurmoHtml::tag('source', array('src' => $audioFilePath), '');
            return ZurmoHtml::tag('audio', array('id' => 'game-coin-chime'), $content);
        }

        protected function makeAjaxClickUrl()
        {
            return Yii::app()->createUrl('gamification/default/CollectRandomCoin');
        }
    }
?>
