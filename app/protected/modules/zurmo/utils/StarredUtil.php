<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with Starred Models
     */
    class StarredUtil
    {
        public static function modelHasStarredInterface($modelClassName)
        {
            $refelectionClass = new ReflectionClass($modelClassName);
            return in_array('StarredInterface', $refelectionClass->getInterfaceNames());
        }

        public static function createStarredTables()
        {
            $modelClassNames = static::getStarredModels('StarredInterface');
            foreach ($modelClassNames as $modelClassName)
            {
                $modelStarredTableName = static::getStarredTableName($modelClassName);
                static::createTable($modelStarredTableName);
            }
        }

        protected static function getStarredModels($interfaceClassName)
        {
            assert('is_string($interfaceClassName)');
            $interfaceModelClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $modelClassNames = $module::getModelClassNames();
                foreach ($modelClassNames as $modelClassName)
                {
                    $classToEvaluate     = new ReflectionClass($modelClassName);
                    if ($classToEvaluate->implementsInterface($interfaceClassName) &&
                    !$classToEvaluate->isAbstract())
                    {
                        $interfaceModelClassNames[] = $modelClassName;
                    }
                }
            }
            return $interfaceModelClassNames;
        }

        protected static function createTable($modelStarredTableName)
        {
            assert('is_string($modelStarredTableName) && $modelStarredTableName  != ""');
            R::exec("create table if not exists {$modelStarredTableName} (
                        id int(11)         unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT ,
                        userId int(11)     unsigned NOT NULL,
                        modelId int(11)    unsigned NOT NULL
                     )");
        }

        protected static function getMainTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return RedBeanModel::getTableName($modelClassName);
        }

        public static function getStarredTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return self::getMainTableName($modelClassName) . '_starred';
        }

        public static function markModelAsStarred($model)
        {
            static::markModelAsStarredForUser(get_class($model),
                                              Yii::app()->user->userModel->id,
                                              $model->id);
        }

        protected static function markModelAsStarredForUser($modelClassName, $userId, $modelId)
        {
            if(!static::modelHasStarredInterface($modelClassName))
            {
                throw new NotSupportedException();
            }
            if(static::isModelStarredForUser($modelClassName, $userId, $modelId))
            {
                return;
            }
            $tableName = static::getStarredTableName($modelClassName);
            $sql       = "INSERT INTO {$tableName} VALUES (null, :userId, :modelId);";
            R::exec($sql, array(
                ':userId'  => $userId,
                ':modelId' => $modelId,
            ));
        }

        public static function unmarkModelAsStarred($model)
        {
            static::unmarkModelAsStarredForUser(get_class($model),
                                              Yii::app()->user->userModel->id,
                                              $model->id);
        }

        protected static function unmarkModelAsStarredForUser($modelClassName, $userId, $modelId)
        {
            if(!static::modelHasStarredInterface($modelClassName))
            {
                throw new NotSupportedException();
            }
            if(!static::isModelStarredForUser($modelClassName, $userId, $modelId))
            {
                return;
            }
            $tableName = static::getStarredTableName($modelClassName);
            $sql       = "DELETE FROM {$tableName} WHERE userId = :userId AND modelId = :modelId;";
            R::exec($sql, array(
                ':userId'  => $userId,
                ':modelId' => $modelId,
            ));
        }

        public static function isModelStarred($model)
        {
            return static::isModelStarredForUser(get_class($model),
                                                 Yii::app()->user->userModel->id,
                                                 $model->id);
        }

        protected static function isModelStarredForUser($modelClassName, $userId, $modelId)
        {
            if(!static::modelHasStarredInterface($modelClassName))
            {
                throw new NotSupportedException();
            }
            $tableName = static::getStarredTableName($modelClassName);
            $sql       = "SELECT id FROM {$tableName} WHERE userId = :userId AND modelId = :modelId;";
            $rows      = R::getAll($sql,
                                   $values=array(
                                    ':userId'    => $userId,
                                    ':modelId'   => $modelId,
                                   ));
            if (count($rows) == 0)
            {
                return false;
            }
            return true;
        }

        public static function unmarkModelAsStarredForAllUsers($model)
        {
            $modelClassName = get_class($model);
            if(!static::modelHasStarredInterface($modelClassName))
            {
                throw new NotSupportedException();
            }
            $tableName = static::getStarredTableName($modelClassName);
            $sql       = "DELETE FROM {$tableName} WHERE modelId = :modelId;";
            R::exec($sql, array(
                ':modelId' => $model->id,
            ));
        }

        public static function toggleModelStarStatus($modelClassName, $modelId)
        {
            $model = $modelClassName::getById($modelId);
            $isModelStarred = static::isModelStarred($model);
            if ($isModelStarred)
            {
                static::unmarkModelAsStarred($model);
            }
            else
            {
                static::markModelAsStarred($model);
            }
            if ($isModelStarred)
            {
                return 'unstarred';
            }
            return 'starred';
        }
    }
?>