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
     * Rules for the new business level type.
     */
    class NewBusinessGameLevelRules extends SubLevelGameLevelRules
    {
        /**
         * Defines the last level for the level type.
         * @var integer
         */
        protected static $lastLevel     = 7;

        /**
         * Array of data that provides the point value required to move up to each level.
         * @var array
         */
        protected static $levelPointMap = array( 1  => 100,
                                                 2  => 210,
                                                 3  => 330,
                                                 4  => 460,
                                                 5  => 600,
                                                 6  => 750,
                                                 7  => 910);

        public static function getDisplayLabel()
        {
            return Yii::t('Default', 'New Business');
        }
    }
?>