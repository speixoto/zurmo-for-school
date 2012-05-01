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
     * Base class defining rules for game badges
     */
    abstract class GameBadgeRules
    {
        public static function getDisplayName()
        {
            throw new NotImplementedException();
        }

        /**
         * @return The type of the GameBadgeRules
         */
        public static function getType()
        {
            $name = get_called_class();
            $name = substr($name, 0, strlen($name) - strlen('GameBadgeRules'));
            return $name;
        }

        /**
         * @return array of available badge rule class names
         */
        public static function getBadgeRulesData()
        {
            $badgeRulesData       = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $rulesClassNames = $module::getAllClassNamesByPathFolder('rules.badges');
                foreach ($rulesClassNames as $ruleClassName)
                {
                    $classToEvaluate     = new ReflectionClass($ruleClassName);
                    if (!$classToEvaluate->isAbstract())
                    {
                        $badgeRulesData[] = $ruleClassName;
                    }
                }
            }
            return $badgeRulesData;
        }

        public static function badgeGradeUserShouldHaveByPointsAndScores($userPointsByType, $userScoresByType)
        {
            assert('is_array($userPointsByType)');
            assert('is_array($userScoresByType)');
            throw new NotImplementedException();
        }

        public static function hasBonusPointsOnCreation()
        {
            return false;
        }

        public static function hasBonusPointsOnGradeChange()
        {
            return false;
        }

        public static function getNewBonusPointType()
        {
            throw new NotImplementedException();
        }

        public static function getNewBonusPointValue()
        {
            throw new NotImplementedException();
        }

        public static function getGradeBonusPointType()
        {
            throw new NotImplementedException();
        }

        public static function getGradeBonusPointValue($grade)
        {
            assert('is_int($grade)');
            throw new NotImplementedException();
        }
    }
?>