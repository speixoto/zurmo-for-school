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
     * Helper class for working with game levels.
     */
    class GameLevelUtil
    {
        /**
         * Given a level type and GameLevel, get the point value needed to 'level up' to the next level.
         * @param string $type
         * @param GameLevel $level
         * @return false if there is no next level or returns an integer of the points required to 'level up' to the
         * next level.
         */
        public static function getNextLevelPointValueByTypeAndCurrentLevel($type, GameLevel $level)
        {
            assert('is_string($type)');
            $nextLevel = self::getNextLevelByTypeAndCurrentLevel($type, $level);
            if($nextLevel !== false)
            {
                $className = GameLevel::TYPE_GENERAL . 'GameLevelRules';
                return $className::getMinimumPointsForLevel($nextLevel);
            }
            return false;
        }

        /**
         * Given a level type and GameLevel, get the next level.
         * @param string $type
         * @param GameLevel $level
         * @return Next level as integer or false if there is no next level.
         */
        public static function getNextLevelByTypeAndCurrentLevel($type, GameLevel $level)
        {
            assert('is_string($type)');
            $className = GameLevel::TYPE_GENERAL . 'GameLevelRules';
            if(!$className::isLastLevel((int)$level->value))
            {
                return $level->value + 1;
            }
            return false;
        }
    }
?>