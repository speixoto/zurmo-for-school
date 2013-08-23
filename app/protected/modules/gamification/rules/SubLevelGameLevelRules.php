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
     * Base class for sub-level rules
     */
    abstract class SubLevelGameLevelRules extends GameLevelRules
    {
        public static function hasBonusPointsOnLevelChange()
        {
            return true;
        }

        public static function getLevelBonusPointType()
        {
            return GamePoint::TYPE_USER_ADOPTION;
        }

        /**
         * @param int $level
         * @return int|void
         */
        public static function getLevelBonusPointValue($level)
        {
            assert('is_int($level)');
            if ($level == 1)
            {
                return 100;
            }
            elseif ($level == 1)
            {
                return 110;
            }
            elseif ($level == 2)
            {
                return 110;
            }
            elseif ($level == 3)
            {
                return 130;
            }
            elseif ($level == 4)
            {
                return 140;
            }
            elseif ($level == 5)
            {
                return 150;
            }
            elseif ($level == 6)
            {
                return 160;
            }
            elseif ($level == 7)
            {
                return 170;
            }
        }
    }
?>