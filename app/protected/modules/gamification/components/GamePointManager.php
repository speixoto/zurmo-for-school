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
     * Manages point tabulation through a single page request. As scores are added, the point information is
     * tabulated in the GamePointManager so it can then update persistent storage in a single request at the end
     * of the page request.
     */
    class GamePointManager extends CComponent
    {
        private static $pointTypesAndValuesByUserIdToAdd = array();

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
            echo 'aaaaa';
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
        public static function processDeferredPoints()
        {
            foreach(self::$pointTypesAndValuesByUserIdToAdd as $userId => $typeAndValues)
            {
                if($typeAndValues != null)
                {
                    foreach($typeAndValues as $type => $value)
                    {
                        $gamePoint      = GamePoint::
                                            resolveToGetByTypeAndPersonId($type, User::getById($userId));
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
    }
?>