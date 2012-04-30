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
     * Class defining rules for Contact gamification behavior.
     */
    class ContactGamificationRules extends GamificationRules
    {
        const SCORE_CATEGORY_CREATE_LEAD          = 'CreateLead';

        const SCORE_CATEGORY_UPDATE_LEAD          = 'UpdateLead';

        const SCORE_CATEGORY_CONVERT_LEAD         = 'ConvertLead';

        public function scoreOnSaveModel(CEvent $event)
        {
            if(!LeadsUtil::isStateALead &&
                array_key_exists('name', $event->sender->state->originalAttributeValues) &&
                LeadsUtil::isStateALeadByStateName($event->sender->state->originalAttributeValues['name']))
            {
                $this->scoreOnSaveWhereLeadIsConverted($event);
            }
            elseif (LeadsUtil::isStateALead($event->sender->state))
            {
                $this->scoreOnSaveWhereStateIsLead($event);
            }
            else
            {
                parent::scoreOnSaveModel($event);
            }
        }

        protected function scoreOnSaveWhereLeadIsConverted(CEvent $event)
        {
            $scoreType = 'ConvertLead';
            $category  = static::SCORE_CATEGORY_CONVERT_LEAD;
            $gameScore = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved = $gameScore->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByGameScore($gameScore->type, Yii::app()->user->userModel,
                           getPointTypeAndValueDataByScoreTypeAndCategory($gameScore->type, $category));
        }

        protected function scoreOnSaveWhereStateIsLead(CEvent $event)
        {
            $model                   = $event->sender;
            assert('$model instanceof Item');
            if($model->getIsNewModel())
            {
                $scoreType           = 'CreateLead';
                $category            = static::SCORE_CATEGORY_CREATE_LEAD;
                $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            }
            else
            {
                $scoreType           = 'UpdateLead';
                $category            = static::SCORE_CATEGORY_UPDATE_LEAD;
                $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            }
            $gameScore->addValue();
            $saved = $gameScore->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
                GamePointUtil::addPointsByGameScore($gameScore->type, Yii::app()->user->userModel,
                               static::getPointTypeAndValueDataByScoreTypeAndCategory($gameScore->type, $category));
        }

        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_ACCOUNT_MANAGEMENT => 10);
        }

        public static function getPointTypesAndValuesForUpdateModel()
        {
            return array(GamePoint::TYPE_ACCOUNT_MANAGEMENT => 10);
        }

        public static function getPointTypesAndValuesForConvertLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 25);
        }

        public static function getPointTypesAndValuesForCreateLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 10);
        }

        public static function getPointTypesAndValuesForUpdateLead()
        {
            return array(GamePoint::TYPE_NEW_BUSINESS => 10);
        }
    }
?>