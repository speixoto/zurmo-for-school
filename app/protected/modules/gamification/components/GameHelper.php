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
     *  Helps with game logic exuected during a page request. As scores are added, the point information is
     * tabulated in the GamePointManager so it can then update persistent storage in a single request at the end
     * of the page request.
     */
    class GameHelper extends CApplicationComponent
    {
        public $enabled = true;

        private static $pointTypesAndValuesByUserIdToAdd = array();

        public function init()
        {
            $this->initCustom();
        }

        /**
         * Override as needed to customize various aspects of gamification.  A few examples of things you can do here:
         * GeneralGameLevelRules::setLastLevel(100);
           GeneralGameLevelRules::setLevelPointMap($newLevelPointMap);
         */
        public function initCustom()
        {
        }

        /**
         * Given a user, point type, and value, store the information in the @see $pointTypesAndValuesByUserIdToAdd
         * data array to be processed later at the end of the page request by @see processDeferredPoints
         * @param User $user
         * @param String $type
         * @param Integer $value
         */
        public static function addPointsByUserDeferred(User $user, $type, $value)
        {
            assert('$user->id > 0');
            assert('is_string($type)');
            assert('is_int($value)');
            if(!isset(self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type]))
            {
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] = $value;
            }
            else
            {
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] =
                self::$pointTypesAndValuesByUserIdToAdd[$user->id][$type] + $value;
            }


        }

        /**
         * Process any points that have been added to @see $pointTypesAndValuesByUserIdToAdd throughout the page
         * request.
         */
        public function processDeferredPoints()
        {
            if(!$this->enabled)
            {
                return;
            }
            foreach(self::$pointTypesAndValuesByUserIdToAdd as $userId => $typeAndValues)
            {
                if($typeAndValues != null)
                {
                    foreach($typeAndValues as $type => $value)
                    {
                        $gamePoint      = GamePoint::
                                            resolveToGetByTypeAndPerson($type, User::getById($userId));
                        $gamePoint->addValue($value);
                        $saved          = $gamePoint->save();
                        if(!$saved)
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
        }

        public function resolveLevelChange()
        {
            if(!$this->enabled)
            {
                return;
            }
            //todo: refactor to resolve more than just GENERAL.. do the sub categories first...
            $currentGameLevel    = GameLevel::resolveByTypeAndPerson(GameLevel::TYPE_GENERAL, Yii::app()->user->userModel);
            $nextLevelPointValue = GameLevelUtil::getNextLevelPointValueByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL,
                                                                                              $currentGameLevel);
            $nextLevel           = GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL,
                                                                                    $currentGameLevel);
            if($nextLevel !== false &&
               GamePoint::doesUserExceedPoints(Yii::app()->user->userModel, $nextLevelPointValue))
            {
                $currentGameLevel->value = $nextLevel;
                $saved = $currentGameLevel->save();
                if(!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                if($currentGameLevel->value != 1)
                {
                    $message                    = new NotificationMessage();
                    $message->textContent       = Yii::t('Default', 'You have reached a new level: {level}. Congratulations.',
                                                                    array('{level}' => $nextLevel));
                    $rules                      = new GameNotificationRules();
                    $rules->addUser(Yii::app()->user->userModel);
                    NotificationsUtil::submit($message, $rules);
                }
            }
        }

        public function triggerSearchModelsEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnSearchModels($modelClassName);
            }
        }

        public function triggerMassEditEvent($modelClassName)
        {
            assert('is_string($modelClassName)');
            if (is_subclass_of($modelClassName, 'Item') && $modelClassName::getGamificationRulesType() != null)
            {
                $gamificationRulesType      = $modelClassName::getGamificationRulesType();
                $gamificationRulesClassName = $gamificationRulesType . 'Rules';
                $gamificationRulesClassName::scoreOnMassEditModels($modelClassName);
            }
        }
    }
?>