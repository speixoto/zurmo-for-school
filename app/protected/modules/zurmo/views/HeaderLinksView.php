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

    class HeaderLinksView extends View
    {
        protected $settingsMenuItems;

        protected $userMenuItems;

        protected $notificationsUrl;

        const USER_MENU_ID                              = 'user-header-menu';

        const SETTINGS_MENU_ID                          = 'settings-header-menu';

        const MERGED_MENU_ID                            = 'settings-header-menu';

        const USER_GAME_DASHBOARD_WRAPPER_ID            = 'header-game-dashboard-link-wrapper';

        const USER_GAME_DASHBOARD_LINK_ID               = 'header-game-dashboard-link';

        const MODAL_CONTAINER_PREFIX                    = 'modalContainer';

        const MERGE_USER_AND_SETTINGS_MENU_IF_MOBILE    = true;

        const CLAIM_ITEM_LINK_ID                        = 'claim-item-link';

        const USER_CALENDAR_WRAPPER_ID                  = 'header-calendar-link-wrapper';

        /**
         * @param array $settingsMenuItems
         * @param array $userMenuItems
         */
        public function __construct($settingsMenuItems, $userMenuItems)
        {
            assert('is_array($settingsMenuItems)');
            assert('is_array($userMenuItems)');
            $this->settingsMenuItems     = $settingsMenuItems;
            $this->userMenuItems         = $userMenuItems;
        }

        protected function renderContent()
        {
            $this->registerScripts();
            $content = null;
            if (!empty($this->userMenuItems) && !empty($this->settingsMenuItems))
            {
                $content  .= static::renderHeaderMenus($this->userMenuItems, $this->settingsMenuItems);
            }
            return $content;
        }

        protected static function renderHeaderMenus($userMenuItems, $settingsMenuItems)
        {
            $userMenuItemsWithTopLevel     = static::resolveUserMenuItemsWithTopLevelItem($userMenuItems);
            $settingsMenuItemsWithTopLevel = static::resolveSettingsMenuItemsWithTopLevelItem($settingsMenuItems);
            $content = static::renderHeaderMenuContent($settingsMenuItemsWithTopLevel, self::SETTINGS_MENU_ID);
            if (Yii::app()->userInterface->isMobile() === false)
            {
                $content .= static::renderHeaderGameDashboardContent();
                $content .= static::renderHeaderCalendarContent();
            }
            $content     .= static::renderHeaderMenuContent($userMenuItemsWithTopLevel, self::USER_MENU_ID);
	        $content     .= static::resolveUserSwitcher();
            return $content;
        }

	    protected static function resolveUserSwitcher()
	    {
		    $script = '<script>$("#user-switcher-link").click(function(){$("#user-switcher-wrapper").toggleClass("switcher-open"); })</script>';

		    //IF there's another user 'active' then the wrapper div should know about it and the class 'switched-user' should be on it.
		    $content = '<div id="user-switcher-wrapper" class="user-menu-item _switched-user">
		                    <a id="user-switcher-link" href="#">“</a>
							<div id="user-switcher">
								<h5>Use Zurmo as another User</h5>

								<!-- these first <p> should not show if there is no user selected-->

								<p class="clearfix">You are set to <a href="#">John Smith</a> <a href="#" class="reset-user"><i class="icon-x"></i>Reset to your user</a></p>

								<p class="clearfix">
									<!--This Input should be replaced with an AutoComplete component-->
									<input type="text" placeholder="Type to find user.." />
									<a id="claim-item-link" class="mini-button" href="#">Switch</a>
								</p>
							</div>
		                </div>';
		    return $content . $script;
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
            return array(array('dynamicLabelContent'  => Yii::app()->user->userModel->getAvatarImage(25),
                               'labelSpanHtmlOptions' => array('class' => 'avatar-holder'),
                               'label'                => Yii::app()->user->userModel->username,
                               'url'                  => null));
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
                'items'       => $menuItems,
                'htmlOptions' => array('id' => $menuId, 'class'  => 'user-menu-item'),
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
            $content .= static::renderGetNewCollectionItemNotification();
            return ZurmoHtml::tag('div', array('id' => static::USER_GAME_DASHBOARD_WRAPPER_ID,
                'class' => 'user-menu-item'), $content);
        }

        protected static function renderGetNewCollectionItemNotification()
        {
            $collectionAndItemKey = Yii::app()->gameHelper->resolveNewCollectionItems();
            if (null != $collectionAndItemKey)
            {
                $claimCollectionItemUrl = Yii::app()->createUrl('gamification/default/claimCollectionItem',
                                                                array('key'     => $collectionAndItemKey[1],
                                                                      'typeKey' => $collectionAndItemKey[2]));
                $gameCollectionRules = GameCollectionRulesFactory::createByType($collectionAndItemKey[0]->type);
                $collectionItemTypesAndLabels = $gameCollectionRules::getItemTypesAndLabels();
                $claimRewardLink = ZurmoHtml::ajaxLink(Zurmo::t('GamificationModule', 'Get this item'), $claimCollectionItemUrl,
                                   array(),
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
                               var magicAudio = document.getElementById('collection-item-claimed');
                               magicAudio.play();
                               $('#game-notification').fadeOut(300);
                           });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('claimItemScript', $script);

            // Begin Not Coding Standard
            $script = "$('#close-game-notification-link').click(function(event){
                               event.preventDefault();
                               $('#game-notification').fadeOut(300);
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

        /**
         * Renders header calendar content.
         *
         * @return string
         */
        protected static function renderHeaderCalendarContent()
        {
            $url     = Yii::app()->createUrl('calendars/default/details/');
            $content = ZurmoHtml::link('U', $url, array('id' => 'header-calendar-link'));
            return ZurmoHtml::tag('div', array('id' => static::USER_CALENDAR_WRAPPER_ID,
                'class' => 'user-menu-item'), $content);
        }

        protected function getContainerWrapperTag()
        {
            return null;
        }
    }
?>
