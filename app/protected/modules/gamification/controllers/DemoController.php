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

    Yii::import('application.modules.gamification.controllers.DefaultController', true);
    class GamificationDemoController extends GamificationDefaultController
    {
        /**
         * Special method to load each type of game notification.  New badge, badge grade change, and level up.
         */
        public function actionLoadGameNotificationsSampler()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            //Level up notification
            $coinsValue = GameCoinRules::getCoinsByLevel(2);
            $gameNotification           = new GameNotification();
            $gameNotification->user     = Yii::app()->user->userModel;
            $gameNotification->setLevelChangeByNextLevelValue(2, $coinsValue);
            $saved                      = $gameNotification->save();

            //New badge notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = Yii::app()->user->userModel;
            $gameNotification->setNewBadgeByType('LoginUser');
            $saved                      = $gameNotification->save();

            //Badge grade up notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = Yii::app()->user->userModel;
            $gameNotification->setBadgeGradeChangeByTypeAndNewGrade('LoginUser', 5);
            $saved                      = $gameNotification->save();

            //New collection Item
            GameCollection::processRandomReceivingCollectionItemByUser(Yii::app()->user->userModel);

            echo "Demo data has been loaded. Go back to the application.";
        }

        public function actionLoadCollectionItems()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $collectionData      = GameCollection::resolvePersonAndAvailableTypes(Yii::app()->user->userModel, GameCollection::getAvailableTypes());
            foreach ($collectionData as $collection)
            {
                $itemsData = $collection->getItemsData();
                foreach ($itemsData as $type => $quantity)
                {
                    $itemsData[$type] = $quantity + 1;
                }
                $collection->setItemsData($itemsData);
                $saved = $collection->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
        }
    }
?>
