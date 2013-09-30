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
     * Class for displaying a user's game dashboard
     */
    class UserGameDashboardView extends View
    {
        const COLLECTION_CONTAINER_ID_PREFIX  = 'game-collection-container';

        const COMPLETE_COLLECTION_LINK_PREFIX = 'complete-collection-link';

        protected $controller;

        protected $user;

        protected $generalLevelData;

        protected $badgeData;

        protected $rankingData;

        protected $statisticsData;

        public static function renderCollectionContent(User $user, GameCollection $collection)
        {
            $gameCollectionRules  = GameCollectionRulesFactory::createByType($collection->type);

            $collectionImageUrl   = Yii::app()->themeManager->baseUrl . '/default/images/collections/' .
                                    $gameCollectionRules::makeLargeCollectionImageName();
            $collectionBadgeImage = ZurmoHtml::image($collectionImageUrl);
            $content  = ZurmoHtml::tag('div', array('class' => 'collection-badge'), $collectionBadgeImage);
            $content .= ZurmoHtml::tag('h3', array(), $gameCollectionRules->getCollectionLabel() . ' ' .
                                       Zurmo::t('GamificationModule', 'Collection'));
            $content .= static::renderCollectionItemsContent($user, $collection, $gameCollectionRules);
            $content  = ZurmoHtml::tag('div', array('class' => '_open-panel'), $content);
            return ZurmoHtml::tag('div', array('id'    => static::getCollectionContainerId($collection->id),
                                               'class' => 'gd-collection-panel clearfix'), $content);
        }

        public static function renderCoinsContent($coinValue)
        {
            $content  = ZurmoHtml::tag('span', array('id' => 'gd-z-coin'), 'z');
            $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('GamificationModule', '{n} coin|{n} coins',
                array($coinValue)));
            $content .= ZurmoHtml::link(Zurmo::t('GamificationModule', 'Redeem'), '#');
            //todo: where does this link to? coonvert to story? take to redemption listview of rewards
            return      ZurmoHtml::tag('div', array('id' => self::getGameCoinContainerId()), $content);
        }

        /**
         * @param CController $controller
         * @param User $user
         * @param array $generalLevelData
         * @param array $badgeData
         * @param array $rankingData
         * @param array $statisticsData
         * @param array $collectionData
         */
        public function __construct(CController $controller, User $user, array $generalLevelData, array $badgeData,
                                    array $rankingData, array $statisticsData, array $collectionData)
        {
            $this->controller       = $controller;
            $this->user             = $user;
            $this->generalLevelData = $generalLevelData;
            $this->badgeData        = $badgeData;
            $this->rankingData      = $rankingData;
            $this->statisticsData   = $statisticsData;
            $this->collectionData   = $collectionData;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $this->registerScripts();
            return $this->renderDashboardContent();
        }

        protected function registerScripts()
        {
            $script = "";
            Yii::app()->clientScript->registerScript('userGameDashboardScript', $script);

        }

        protected function renderDashboardContent()
        {
            $content  = $this->renderProfileContent();
            $content .= $this->renderBadgesContent();
            $content .= static::renderCoinsContent($this->getGameCoinForUser()->value);
            $content .= $this->renderLeaderboardContent();
            $content .= $this->renderStatisticsContent();
            $content .= $this->renderCollectionsContent();
            $content  = ZurmoHtml::tag('div', array('id' => 'game-dashboard', 'class' => 'clearfix'), $content);
            $content  = ZurmoHtml::tag('div', array('id' => 'game-overlay'), $content);
            return      ZurmoHtml::tag('div', array('id' => 'game-dashboard-container'), $content);
        }

        protected function renderProfileContent()
        {
            $content  = $this->user->getAvatarImage(240);
            $content .= ZurmoHtml::tag('h3', array(), strval($this->user));
            $content .= $this->renderMiniStatisticsContent();
            return      ZurmoHtml::tag('div', array('id' => 'gd-profile-card'), $content);
        }

        protected function renderMiniStatisticsContent()
        {
            $percentageToNextLabel = Zurmo::t('GamificationModule', '44% to Level 5', array());

            $levelContent  = ZurmoHtml::tag('strong', array(), $this->generalLevelData['level']);
            $levelContent .= ZurmoHtml::tag('span', array(), Zurmo::t('GamificationModule', 'Level'));
            $levelContent .= $this->renderPercentHolderContent((int)$this->generalLevelData['nextLevelPercentageComplete']);
            $levelContent .= ZurmoHtml::tag('span', array(), $percentageToNextLabel);

            $content  = ZurmoHtml::tag('div', array('id'    => 'gd-mini-stats-chart-div',
                                                    'style' => "width: 100%; height: 150px;"), '');
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-level'), $levelContent);
            $badgeLabelContent = Zurmo::t('GamificationModule', '<strong>{n}</strong> Badge|<strong>{n}</strong> Badges',
                                          array(count($this->badgeData)));
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-num-badges'), $badgeLabelContent);
            $collectionLabelContent = Zurmo::t('GamificationModule', '<strong>{n}</strong> Collection|<strong>{n}</strong> Collections',
                                               array($this->getCompletedCollectionCount()));
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-num-collections'), $collectionLabelContent);
            return      ZurmoHtml::tag('div',  array('id'    => 'gd-mini-stats-card'), $content);
        }

        protected function renderBadgesContent()
        {
            $content  = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Badges Achieved'));

            if(empty($this->badgeData))
            {
                $content .= $this->renderEmptyBadgeContent();
            }
            else
            {
                $content .= $this->renderPopulatedBadgeContent();
            }
            return      ZurmoHtml::tag('div', array('id' => 'gd-badges-list'), $content);
        }

        protected function renderEmptyBadgeContent()
        {
            $content  = ZurmoHtml::tag('tag', array('class' => 'icon-empty'), '');
            $content .= Zurmo::t('GamificationModule', 'No Achievements Found');
            return ZurmoHtml::tag('span', array('class' => 'empty'), $content);
        }

        protected function renderPopulatedBadgeContent()
        {
            $content = '<ul>' . "\n";
            foreach($this->badgeData as $badge)
            {
                $gameBadgeRulesClassName = $badge->type . 'GameBadgeRules';
                $value                   = $gameBadgeRulesClassName::getItemCountByGrade((int)$badge->grade);
                $badgeDisplayLabel       = $gameBadgeRulesClassName::getPassiveDisplayLabel($value);
                $badgeContent      = null;
                $badgeIconContent  = ZurmoHtml::tag('div',   array('class' => 'gloss'), '');
                $badgeIconContent .= ZurmoHtml::tag('strong',   array('class' => 'badge-icon',
                    'title' => $badgeDisplayLabel), '');
                $badgeIconContent .= ZurmoHtml::tag('span',   array('class' => 'badge-grade'), (int)$badge->grade);
                $badgeContent .= ZurmoHtml::tag('h3',   array('class' => 'badge ' . $badge->type), $badgeIconContent);
                $badgeContent .= ZurmoHtml::tag('h3',   array(), $badgeDisplayLabel);
                $badgeContent .= ZurmoHtml::tag('span', array(),
                    DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                        $badge->createdDateTime, 'long', null));
                $content      .= ZurmoHtml::tag('li',   array(), $badgeContent);
            }
            $content .= '</ul>' . "\n";
            return $content;
        }

        protected function renderLeaderboardContent()
        {
            $content  = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Leaderboard Rankings'));
            foreach($this->rankingData as $ranking)
            {
                $rankingContent  = ZurmoHtml::tag('strong', array(), $ranking['rank']);
                $rankingContent .= ZurmoHtml::tag('span', array(), $ranking['typeLabel']);
                $content .= ZurmoHtml::tag('div', array('class' => 'leaderboard-rank'), $rankingContent);
            }
            return      ZurmoHtml::tag('div', array('id' => 'gd-leaderboard'), $content);
        }

        protected function renderStatisticsContent()
        {
            $content = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Overall Statistics'));
            $rows = '';
            foreach($this->statisticsData as $statistics)
            {
                $statisticsContent  = ZurmoHtml::tag('h3', array(), $statistics['levelTypeLabel']);
                $statisticsContent .= ZurmoHtml::tag('span', array('class' => 'stat-level'), $statistics['level']);
                $pointsContent      = Zurmo::t('GamificationModule', '{n}<em>Point</em>|{n}<em>Points</em>', array($statistics['points']));
                $statisticsContent .= ZurmoHtml::tag('span', array('class' => 'stat-points'), $pointsContent);
                $statisticsContent .= $this->renderPercentHolderContent((int)$statistics['nextLevelPercentageComplete']);
                $rows .= ZurmoHtml::tag('div', array('class' => 'stat-row'), $statisticsContent);
            }
            $content .= ZurmoHtml::tag('div', array('id' => 'gd-stats-wrapper'), $rows);
            return      ZurmoHtml::tag('div', array('id' => 'gd-statistics'), $content);
        }

        protected function renderCollectionsContent()
        {
            $content  = ZurmoHtml::link('◀', '#', array('id' => 'nav-left', 'class' => 'nav-button'));
            $content .= $this->renderCollectionsCarouselWrapperAndContent();
            $content .= ZurmoHtml::link('▶', '#', array('id' => 'nav-right', 'class' => 'nav-button'));
            return      ZurmoHtml::tag('div', array('id' => 'gd-collections'), $content);
        }

        protected function renderCollectionsCarouselWrapperAndContent()
        {
            $collectionsListContent = null;
            foreach($this->collectionData as $collection)
            {
                $collectionsListContent .= $this->renderCollectionContent($this->user, $collection);
            }
            $content = ZurmoHtml::tag('div', array('id' => 'gd-carousel', 'style' => "width:2000px"), $collectionsListContent);
            return     ZurmoHtml::tag('div', array('id' => 'gd-carousel-wrapper'), $content);
        }

        protected static function renderCollectionItemsContent(User $user, GameCollection $collection,
                                                               GameCollectionRules $gameCollectionRules)
        {
            $itemTypesAndLabels = $gameCollectionRules->getItemTypesAndLabels();
            $content    = null;
            $canCollect = true;
            foreach($collection->getItemsData() as $itemType => $quantityCollected)
            {
                $itemLabel              = $itemTypesAndLabels[$itemType];
                $collectionItemImageUrl = Yii::app()->themeManager->baseUrl . '/default/images/collections/' .
                                          $gameCollectionRules::makeMediumCollectionItemImageName($itemType);
                $itemContent = ZurmoHtml::image($collectionItemImageUrl, $itemLabel, array('class'        => 'qtip-shadow',
                                                                                       'data-tooltip' => $itemLabel));
                $itemContent .= ZurmoHtml::tag('span', array('class' => 'num-collected'), 'x' . $quantityCollected);
                $classContent = 'gd-collection-item';
                if($quantityCollected == 0)
                {
                    $classContent .= ' missing';
                    $canCollect = false;
                }
                $content .= ZurmoHtml::tag('div', array('class' => $classContent), $itemContent);
            }
            $coinImageUrl       = Yii::app()->themeManager->baseUrl . '/default/images/coin.png';
            $itemRedeemContent  = ZurmoHtml::image($coinImageUrl);
            $itemRedeemContent .= static::renderCompleteButton($collection->id, $user->id, $canCollect);
            $content           .= ZurmoHtml::tag('div', array('class' => 'gd-collection-item-redeemed'), $itemRedeemContent);
            return ZurmoHtml::tag('div', array('class' => 'gd-collection-items clearfix'), $content);
        }

        protected static function renderCompleteButton($collectionId, $userId, $canCollect = true)
        {
            assert('is_int($collectionid)');
            assert('is_int($userId)');
            assert('is_bool($canCollect)');
            $url  = Yii::app()->createUrl('gamification/default/redeemCollection/',
                                          array('id' => $collectionId));
            $htmlOptions = array();
            if(!$canCollect)
            {
                $htmlOptions['disabled'] = 'disabled';
            }
            $id                      = static::getCompleteCollectionLinkId($collectionId);
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $id;
            $htmlOptions['class']    = 'attachLoading z-button';
            $aContent                = ZurmoHtml::wrapLink(Zurmo::t('Core', 'Complete'));
            $containerId             = static::getCollectionContainerId($collectionId);
            return ZurmoHtml::ajaxLink($aContent, $url, array(
                'type' => 'GET',
                'beforeSend' => 'function ( xhr ) {$(this).makeSmallLoadingSpinner(true, "#' . $id . '");}',
                'success' => 'js:function(data)
                    {
                        $("#' . $containerId . '").html(data);
                        ' . self::renderGameCoinRefreshAjax($userId) . '
                    }'
            ), $htmlOptions);
        }

        protected static function getCollectionContainerId($collectionId)
        {
            return self::COLLECTION_CONTAINER_ID_PREFIX . '-' . $collectionId;
        }

        protected static function getCompleteCollectionLinkId($collectionId)
        {
            return self::COMPLETE_COLLECTION_LINK_PREFIX . '-' . $collectionId;
        }

        protected function getCompletedCollectionCount()
        {
            $count = 0;
            foreach($this->collectionData as $collection)
            {
                if($collection->getRedemptionCount() > 0)
                {
                    $count ++;
                }
            }
            return $count;
        }

        protected function getGameCoinForUser()
        {
            return GameCoin::resolveByPerson($this->user);
        }

        protected function renderPercentHolderContent($percentageComplete)
        {
            assert('is_int($percentageComplete)');
            $percentCompleteContent = ZurmoHtml::tag('span',
                array('class' => 'percentComplete z_' . $percentageComplete),
                      ZurmoHtml::tag('span', array('class' => 'percent'), $percentageComplete . '%'));
            return ZurmoHtml::tag('span', array('class' => 'percentHolder'), $percentCompleteContent);
        }

        protected static function renderGameCoinRefreshAjax($userId)
        {
            assert('is_int($userId)');
            return ZurmoHtml::ajax(array(
                'type' => 'GET',
                'url'  =>  static::getGameCoinRefreshUrl($userId),
                'update' => '#' . self::getGameCoinContainerId(),
            ));
        }

        protected static function getGameCoinRefreshUrl($userId)
        {
            assert('is_int($userId)');
            return Yii::app()->createUrl('gamification/default/refreshGameDashboardCoinContainer', array('id' => $userId));
        }

        protected static function getGameCoinContainerId()
        {
            return 'gd-z-coins';
        }
    }
?>
