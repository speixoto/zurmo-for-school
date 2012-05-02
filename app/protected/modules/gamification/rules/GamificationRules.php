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
     * Base class defining rules for gamification behavior.
     */
    class GamificationRules
    {
        const SCORE_CATEGORY_CREATE_MODEL          = 'CreateModel';

        const SCORE_CATEGORY_UPDATE_MODEL          = 'UpdateModel';

        const SCORE_CATEGORY_LOGIN_USER            = 'LoginUser';

        const SCORE_CATEGORY_MASS_EDIT             = 'MassEdit';

        const SCORE_CATEGORY_SEARCH                = 'Search';

        const SCORE_CATEGORY_IMPORT                = 'Import';

        const SCORE_CATEGORY_TIME_SENSITIVE_ACTION = 'TimeSensitiveAction';

        public function attachScoringEventsByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            $modelClassName::model()->attachEventHandler('onAfterSave', array($this, 'scoreOnSaveModel'));
        }

        public function scoreOnSaveModel(CEvent $event)
        {
            $model                   = $event->sender;
            assert('$model instanceof Item');
            if(Yii::app()->gameHelper->isScoringModelsOnSaveMuted())
            {
                return;
            }
            if($model->getIsNewModel())
            {
                $scoreType           = static::resolveCreateScoreTypeByModel($model);
                $category            = static::SCORE_CATEGORY_CREATE_MODEL;
                $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            }
            else
            {
                $scoreType           = static::resolveUpdateScoreTypeByModel($model);
                $category            = static::SCORE_CATEGORY_UPDATE_MODEL;
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

        protected static function resolveCreateScoreTypeByModel($model)
        {
            return 'Create' . get_class($model);
        }

        protected static function resolveUpdateScoreTypeByModel($model)
        {
            return 'Update' . get_class($model);
        }

        public static function getPointTypeAndValueDataByScoreTypeAndCategory($type, $category)
        {
            assert('is_string($type)');
            assert('is_string($category)');
            $methodName = 'getPointTypesAndValuesFor' . $category;
            if(method_exists(get_called_class(), $methodName))
            {
                return static::$methodName();
            }
            else
            {
                throw new NotImplementedException();
            }
        }

        public static function getPointTypesAndValuesForCreateModel()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        public static function getPointTypesAndValuesForUpdateModel()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        public static function getPointTypesAndValuesForLoginUser()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 5);
        }

        public static function getPointTypesAndValuesForSearch()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 5);
        }

        public static function getPointTypesAndValuesForMassEdit()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 5);
        }

        public static function getPointTypesAndValuesForImport()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 10);
        }

        public static function getPointTypesAndValuesForTimeSensitiveAction()
        {
            return array(GamePoint::TYPE_USER_ADOPTION => 20);
        }

        public static function scoreOnSearchModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'Search' . $modelClassName;
            $category            = static::SCORE_CATEGORY_SEARCH;
            $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByGameScore($gameScore->type, Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByScoreTypeAndCategory($gameScore->type, $category));
        }

        public static function scoreOnMassEditModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'MassEdit' . $modelClassName;
            $category            = static::SCORE_CATEGORY_MASS_EDIT;
            $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByGameScore($gameScore->type, Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByScoreTypeAndCategory($gameScore->type, $category));
        }

        public static function scoreOnImportModels($modelClassName)
        {
            assert('is_string($modelClassName)');
            $scoreType           = 'Import' . $modelClassName;
            $category            = static::SCORE_CATEGORY_IMPORT;
            $gameScore           = GameScore::resolveToGetByTypeAndUser($scoreType, Yii::app()->user->userModel);
            $gameScore->addValue();
            $saved               = $gameScore->save();
            if(!$saved)
            {
                throw new FailedToSaveModelException();
            }
            GamePointUtil::addPointsByGameScore($gameScore->type, Yii::app()->user->userModel,
                           static::getPointTypeAndValueDataByScoreTypeAndCategory($gameScore->type, $category));
        }
    }
?>