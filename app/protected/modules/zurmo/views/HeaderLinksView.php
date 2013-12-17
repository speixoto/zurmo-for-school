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

    class HeaderLinksView extends View
    {
        protected $settingsMenuItems;

        protected $userMenuItems;

        protected $notificationsUrl;

        protected $applicationName;

        const USER_MENU_ID                              = 'user-header-menu';

        const SETTINGS_MENU_ID                          = 'settings-header-menu';

        const MERGED_MENU_ID                            = 'settings-header-menu';

        const USER_GAME_DASHBOARD_WRAPPER_ID            = 'header-game-dashboard-link-wrapper';

        const USER_GAME_DASHBOARD_LINK_ID               = 'header-game-dashboard-link';

        const MODAL_CONTAINER_PREFIX                    = 'modalContainer';

        const MERGE_USER_AND_SETTINGS_MENU_IF_MOBILE    = true;

        const CLAIM_ITEM_LINK_ID                        = 'claim-item-link';

        /**
         * @param array $settingsMenuItems
         * @param array $userMenuItems
         * @param string $applicationName
         */
        public function __construct($settingsMenuItems, $userMenuItems, $applicationName)
        {
            assert('is_array($settingsMenuItems)');
            assert('is_array($userMenuItems)');
            assert('is_string($applicationName) || $applicationName == null');
            $this->settingsMenuItems     = $settingsMenuItems;
            $this->userMenuItems         = $userMenuItems;
            $this->applicationName       = $applicationName;
        }

        protected function renderContent()
        {
            $this->registerScripts();
            $homeUrl   = Yii::app()->createUrl('home/default');
            $content   = '<div class="clearfix">';
            $content  .= '<a href="#" id="nav-trigger" title="Toggle Navigation">&rsaquo;</a>';
            $content  .= '<div id="corp-logo">';
            if ($logoFileModelId = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                $logoFileModel = FileModel::getById($logoFileModelId);
                $logoFileSrc   = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias('application.runtime.uploads') .
                    DIRECTORY_SEPARATOR . $logoFileModel->name);
            }
            else
            {
                $logoFileSrc   = Yii::app()->themeManager->baseUrl . '/default/images/Zurmo_logo.png';
            }
            $logoHeight = ZurmoConfigurationFormAdapter::resolveLogoHeight();
            $logoWidth  = ZurmoConfigurationFormAdapter::resolveLogoWidth();
            if (Yii::app()->userInterface->isMobile())
            {
                $content   .= '<a href="' . $homeUrl . '"><img src="' . $logoFileSrc . '" alt="Zurmo Logo" /></a>'; //make sure width and height are NEVER defined
            }
            else
            {
                $content   .= '<a href="' . $homeUrl . '"><img src="' . $logoFileSrc . '" alt="Zurmo Logo" height="'
                    . $logoHeight .'" width="' . $logoWidth .'" /></a>';
            }
            if ($this->applicationName != null)
            {
                $content  .= ZurmoHtml::tag('span', array(), $this->applicationName);
            }
            $content  .= '</div>';
            if (!empty($this->userMenuItems) && !empty($this->settingsMenuItems))
            {
                $content  .= '<div id="user-toolbar" class="clearfix">';
                $content  .= static::renderHeaderMenus($this->userMenuItems, $this->settingsMenuItems);
                $content  .= '</div>';
            }
            $content  .= '</div>';
            return $content;
        }

        protected static function renderHeaderMenus($userMenuItems, $settingsMenuItems)
        {
            $userMenuItemsWithTopLevel     = static::resolveUserMenuItemsWithTopLevelItem($userMenuItems);
            $settingsMenuItemsWithTopLevel = static::resolveSettingsMenuItemsWithTopLevelItem($settingsMenuItems);
            $content = null;
            if (Yii::app()->userInterface->isMobile() === false)
            {
                $content .= static::renderHeaderGameDashboardContent();
            }
            $content     .= static::renderHeaderMenuContent($userMenuItemsWithTopLevel, self::USER_MENU_ID);
            $content     .= static::renderHeaderMenuContent($settingsMenuItemsWithTopLevel, self::SETTINGS_MENU_ID);
            return $content;
        }

        protected static function resolveUserMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $topLevel = static::getUserMenuTopLevelItem();
            return static::resolveMenuItemsWithTopLevelItem($topLevel, $menuItems);
        }

        protected static function resolveSettingsMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $topLevel = static::getSettingsMenuTopLevel();
            return static::resolveMenuItemsWithTopLevelItem($topLevel, $menuItems);
        }

        protected static function resolveMenuItemsWithTopLevelItem($topLevel, $menuItems)
        {
            assert('is_array($menuItems)');
            assert('is_array($topLevel)');
            $topLevel[0]['items'] = $menuItems;
            return $topLevel;
        }

        protected static function getUserMenuTopLevelItem()
        {
            return array(array('label' => Yii::app()->user->userModel->username, 'url' => null));
        }

        protected static function getSettingsMenuTopLevel()
        {
            return array(array('label' => Zurmo::t('ZurmoModule', 'Administration'), 'url' => null));
        }

        protected static function renderHeaderMenuContent($menuItems, $menuId)
        {
            assert('is_array($menuItems)');
            assert('is_string($menuId) && $menuId != null');
            if (empty($menuItems))
            {
                return;
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("headerMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'items'                   => $menuItems,
                'htmlOptions' => array('id'     => $menuId,
                    'class'  => 'user-menu-item'),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['headerMenu'];
        }

        protected static function renderHeaderGameDashboardContent()
        {
            $id      = static::USER_GAME_DASHBOARD_LINK_ID;
            $url     = Yii::app()->createUrl('users/default/gameDashboard/',
                array('id' => Yii::app()->user->userModel->id));
            $content = ZurmoHtml::ajaxLink('∂', $url, static::resolveAjaxOptionsForGameDashboardModel($id),
                array(
                    'id' => $id,
                )
            );
            $content .= static::resolveNewCollectionItemAndNotification($url);
            return ZurmoHtml::tag('div', array('id' => static::USER_GAME_DASHBOARD_WRAPPER_ID,
                'class' => 'user-menu-item'), $content);
        }

        protected static function resolveNewCollectionItemAndNotification($gameBoardUrl)
        {
            assert('is_string($gameBoardUrl)');
            $collectionAndItemKey = Yii::app()->gameHelper->resolveNewCollectionItems();
            if (null != $collectionAndItemKey)
            {
                $gameCollectionRules = GameCollectionRulesFactory::createByType($collectionAndItemKey[0]->type);
                $collectionItemTypesAndLabels = $gameCollectionRules::getItemTypesAndLabels();
                $claimRewardLink = ZurmoHtml::ajaxLink(Zurmo::t('GamificationModule', 'Get this item'), $gameBoardUrl,
                                   static::resolveAjaxOptionsForGameDashboardModel(static::CLAIM_ITEM_LINK_ID),
                                   array('id' => static::CLAIM_ITEM_LINK_ID, 'class' => 'mini-button'));
                $closeLink       = ZurmoHtml::link(Zurmo::t('Core', 'Close'), '#', array('id' => 'close-game-notification-link'));
                $collectionItemImagePath = $gameCollectionRules::makeMediumCOllectionItemImagePath($collectionAndItemKey[1]);
                $outerContent  = ZurmoHtml::tag('h5', array(), Zurmo::t('Core', 'Congratulations!'));
                $content  = ZurmoHtml::tag('span', array('class' => 'collection-item-image'), ZurmoHtml::image($collectionItemImagePath));
                $content .= Zurmo::t('GamificationModule', 'You discovered the {name}',
                            array('{name}' => $collectionItemTypesAndLabels[$collectionAndItemKey[1]]));
                $content .= '<br/>';
                $content .= Zurmo::t('GamificationModule', '{claimLink} or {closeLink}',
                            array('{claimLink}' => $claimRewardLink, '{closeLink}' => $closeLink));
                $content  = $outerContent . ZurmoHtml::tag('p', array(), $content);
                $content  = ZurmoHtml::tag('div', array('id' => 'game-notification'), $content);
                $content .= static::renderAudioContent();
                return $content;
            }
        }

        /**
         * @param $id
         * @return array
         */
        public static function resolveAjaxOptionsForGameDashboardModel($id)
        {
            $id      = static::USER_GAME_DASHBOARD_LINK_ID;
            // Begin Not Coding Standard
            return array(
                'beforeSend' => 'js:function(){
                        if($("#UserGameDashboardView").length){
                            closeGamificationDashboard();
                            return false;
                        }
                        $("body").addClass("gd-dashboard-active");
                        $("#' . $id . '").html("‰").toggleClass("highlighted");
                    }',
                'success'    => 'js:function(data){$("#FooterView").after(data);}');
            // End Not Coding Standard
        }

        protected static function renderAudioContent()
        {
            $collectionAndItemKey = Yii::app()->gameHelper->resolveNewCollectionItems();
            if (null != $collectionAndItemKey)
            {
                $publishedAssetsPath = Yii::app()->assetManager->publish(
                    Yii::getPathOfAlias("application.modules.gamification.views.assets.audio"));
                $MP3AudioFilePath = $publishedAssetsPath . '/magic.mp3';
                $OGGAudioFilePath = $publishedAssetsPath . '/magic.ogg';
                $WAVAudioFilePath = $publishedAssetsPath . '/magic.wav';
                $content  = ZurmoHtml::tag('source', array('src' => $MP3AudioFilePath, 'type' => 'audio/mpeg'), '');
                $content .= ZurmoHtml::tag('source', array('src' => $OGGAudioFilePath, 'type' => 'audio/ogg'), '');
                $content .= ZurmoHtml::tag('source', array('src' => $WAVAudioFilePath, 'type' => 'audio/wav'), '');
                return ZurmoHtml::tag('audio', array('id' => 'collection-item-claimed'), $content);
            }
        }

        protected static function getModalContainerId($id)
        {
            return self::MODAL_CONTAINER_PREFIX . '-' . $id;
        }

        protected function registerScripts()
        {
            $id     = static::USER_GAME_DASHBOARD_LINK_ID;
            $script = "$('#".static::CLAIM_ITEM_LINK_ID."').on('click', function(event){
                               event.preventDefault();
                               $(this).off('click');
                               $('#game-notification img').addClass('animate-spin');
                               var magicAudio = document.getElementById('collection-item-claimed');
                               magicAudio.play();
                               setTimeout(function(){
                                   $('#game-notification').fadeOut(300, function(){
                                       $('#game-notification').remove();
                                   });
                               }, 5000);
                               return false;
                           });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('claimItemScript', $script);

            // Begin Not Coding Standard
            $script = "$('#close-game-notification-link').click(function(event){
                               event.preventDefault();
                               $('#game-notification').fadeOut(300, function(){
                                   $('#game-notification').remove();
                               });
                           });
                           $('.gd-dashboard-active').on('click', function(){
                               if($('#UserGameDashboardView').length){
                                   closeGamificationDashboard();
                               }
                               return false;
                           });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('gameficationScripts', $script);

            // Begin Not Coding Standard
            $script = "function closeGamificationDashboard(){
                               $('#UserGameDashboardView').remove();
                               $('body').removeClass('gd-dashboard-active');
                               $('#" . $id . "').html('∂').toggleClass('highlighted');
                           }";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('closeGamificationScript', $script, CClientScript::POS_END);
        }
    }
?>
