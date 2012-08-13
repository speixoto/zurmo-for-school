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
     * Attaches events to the WebUser as needed for gamification.
     */
    class WebUserAfterLoginGamificationBehavior extends CBehavior
    {
        public function attach($owner)
        {
            $owner->attachEventHandler('onAfterLogin', array($this, 'handleScoreLogin'));
        }

        /**
         * The login of a user is a scored game event.  This method processes this.
         * @param CEvent $event
         */
        public function handleScoreLogin($event)
        {
            if (Yii::app()->gamificationObserver->enabled)
            {
                $scoreType           = 'LoginUser';
                $category            = GamificationRules::SCORE_CATEGORY_LOGIN_USER;
                $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
                $gameScore->addValue();
                self::resolveSaveGameScoreAndAddPointsByCategory($gameScore, $category);
                if (Yii::app()->timeZoneHelper->isCurrentUsersTimeZoneConfirmed())
                {
                    $hour = date('G');
                    if ($hour >= 22 || $hour < 4)
                    {
                        $scoreType           = 'NightOwl';
                        $category            = GamificationRules::SCORE_CATEGORY_LOGIN_USER;
                        $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
                        $gameScore->addValue();
                        self::resolveSaveGameScoreAndAddPointsByCategory($gameScore, $category);
                    }
                    elseif ($hour >= 4 && $hour < 8)
                    {
                        $scoreType           = 'EarlyBird';
                        $category            = GamificationRules::SCORE_CATEGORY_LOGIN_USER;
                        $gameScore           = GameScore::resolveToGetByTypeAndPerson($scoreType, Yii::app()->user->userModel);
                        $gameScore->addValue();
                        self::resolveSaveGameScoreAndAddPointsByCategory($gameScore, $category);
                    }
                }
            }
        }

        public static function resolveSaveGameScoreAndAddPointsByCategory($gameScore, $category)
        {
            assert('is_string($category)');
            $saved = $gameScore->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
                GamePointUtil::addPointsByPointData(Yii::app()->user->userModel,
                               GamificationRules::getPointTypeAndValueDataByCategory($category));
        }
     }
?>
