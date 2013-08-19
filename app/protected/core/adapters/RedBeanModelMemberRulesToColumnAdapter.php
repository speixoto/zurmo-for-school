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
     * Adapter class to generate column definition when provided with rules, modelClassName
     */
    abstract class RedBeanModelMemberRulesToColumnAdapter
    {
        /**
         * Should we assume all int types signed?
         */
        const ASSUME_SIGNED = true;

        /**
         * this the default model class thats passed to validator in case we can't find a suitable model class.
         * this class must allow having beans.
         */
        const DEFAULT_MODEL_CLASS = 'Item';

        /**
         * Should be force using the default model class defined above for validators or try to find suitable one?
         */
        const FORCE_DEFAULT_MODEL_CLASS = true;

        /**
         * Key to store unique indexes for unique validators against models
         */
        const CACHE_KEY = 'RedBeanModelMemberRulesToColumnAdapter_uniqueIndexes';

        /**
         * returns unique indexes for a modelClass if there are any unique validators for its members
         * @param string $modelClassName
         * @return array|null
         */
        public static function resolveUniqueIndexesFromValidator($modelClassName)
        {
            $uniqueIndexes  = GeneralCache::getEntry(static::CACHE_KEY, array());
            if (isset($uniqueIndexes[$modelClassName]))
            {
                return $uniqueIndexes[$modelClassName];
            }
            return null;
        }

        /**
         * Provided modelClassName and rules for a member we resolve a column in table.
         * @param string $modelClassName
         * @param array $rules
         * @param $messageLogger
         * @return array|bool
         */
        public static function resolve($modelClassName, array $rules, & $messageLogger)
        {
            if (empty($rules))
            {
                return false;
            }
            $column                         = array();
            $member                         = $rules[0][0];
            assert('strpos($member, " ") === false');
            $column['name']                 = RedBeanModelMemberToColumnNameUtil::resolve($member);
            $column['type']                 = null;
            $column['unsigned']             = null;
            $column['notNull']              = 'NULL'; // TODO: @Shoaibi/@Jason: Medium: We will handle this later.
            $column['collation']            = null;
            $column['default']              = 'DEFAULT NULL'; // TODO: @Shoaibi/@Jason: Medium: We will handle this later.
            $column['length']               = null;
            static::resolveColumnTypeAndLengthFromRules($modelClassName, $member, $rules, $column, $messageLogger);
            if (!isset($column['type']))
            {
                return false;
            }
            $column['type']                 = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType(
                                                                                                    $column['type'],
                                                                                                    $column['length']);
            unset($column['length']);
            return $column;
        }

        protected static function resolveColumnTypeAndLengthFromRules($modelClassName, $member, array $rules,
                                                                                            & $column, & $messageLogger)
        {
            $suitableModelClassName = static::findSuitableModelClassName($modelClassName);
            if (!$suitableModelClassName)
            {
                $messageLogger->addErrorMessage(Zurmo::t('Core', 'Unable to find a suitable non-abstract class for' .
                                                ' validators against {{model}}', array('{{model}}' => $modelClassName)));
                return;
            }
            $model                              = $suitableModelClassName::model();
            $yiiValidators                      = CValidator::$builtInValidators;
            $yiiValidatorsToRedBeanValidators   = RedBeanModel::getYiiValidatorsToRedBeanValidators();
            $intMaxValuesAllows = DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType(static::ASSUME_SIGNED);
            foreach ($rules as $validatorMetadata)
            {
                assert('isset($validatorMetadata[0])');
                assert('isset($validatorMetadata[1])');
                $validatorName       = $validatorMetadata[1];
                $validatorParameters = array_slice($validatorMetadata, 2);
                if (isset($yiiValidators[$validatorName]))
                {
                    $validatorName = $yiiValidators[$validatorName];
                }
                if (isset($yiiValidatorsToRedBeanValidators[$validatorName]))
                {
                    $validatorName = $yiiValidatorsToRedBeanValidators[$validatorName];
                }
                if (!@class_exists($validatorName))
                {
                    continue;
                }
                $validator = CValidator::createValidator($validatorName, $model, $member, $validatorParameters);

                switch ($validatorName)
                {
                    case 'RedBeanModelTypeValidator':
                    case 'TypeValidator':
                    case 'CTypeValidator':
                        if (in_array($validator->type, array('blob', 'boolean', 'date', 'datetime', 'longblob',
                                                        'string', 'float', 'integer', 'time', 'text', 'longtext')))
                        {
                            if (!isset($column['type']) || $validator->type == 'float') // another validator such as CNumberValidator(integer) might have set type to more precise one.
                            {
                                $column['type'] = $validator->type;
                            }
                        }
                        break;
                    case 'CBooleanValidator':
                        $column['type'] = 'boolean';
                        break;
                    case 'CStringValidator':
                        if ((!isset($column['type']) || $column['type'] == 'string'))
                        {
                            $column['type'] = 'text';
                            if (isset($validator->max) && $validator->max > 0)
                            {
                                if ($validator->max > 65535)
                                {
                                    $column['type'] = 'longtext';
                                }
                                elseif ($validator->max <= 255)
                                {
                                    $column['type']     = 'string';
                                    $column['length']   = $validator->max;
                                }
                            }
                        }
                        break;
                    case 'CUrlValidator':
                        $column['type'] = 'string';
                        if (!isset($column['length']))
                        {
                            $column['length'] = 255;
                        }
                        break;
                    case 'CEmailValidator':
                        $column['type'] = 'string';
                        if (!isset($column['length']))
                        {
                            $column['length'] = 255;
                        }
                        break;
                    case 'RedBeanModelNumberValidator':
                    case 'CNumberValidator':
                        if ((!isset($column['type']) || $column['type'] == 'integer') && !isset($validator->precision))
                        {
                            $column['type'] = 'integer';
                            if (isset($validator->max))
                            {
                                foreach ($intMaxValuesAllows as $type => $valueLimit)
                                {
                                    $maxAllowedValue = $valueLimit;
                                    $minAllowedValue = 0;
                                    if (static::ASSUME_SIGNED)
                                    {
                                        $minAllowedValue = -1 * $valueLimit;
                                    }
                                    if ((!isset($validator->min) || $validator->min >= $minAllowedValue) &&
                                                                                    $validator->max < $maxAllowedValue)
                                    {
                                        $column['type'] = $type;
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    case 'RedBeanModelDefaultValueValidator':
                    case 'CDefaultValueValidator':
                        // TODO: @Shoaibi/@Jason: Medium: Left here for future use if we want to set defaults on db level too.
                        //$column['default']              = 'DEFAULT ' . $validator->value;
                        break;
                    case 'RedBeanModelRequiredValidator':
                    case 'CRequiredValidator':
                        //$column['notNull'] = 'NOT NULL';
                        // TODO: @Shoaibi/@Jason: Medium: Left here for future use if we want to set required on db level too.
                        break;
                    case 'RedBeanModelUniqueValidator':
                    case 'CUniqueValidator':
                        static::registerUniqueIndexByMemberName($member, $modelClassName);
                        break;
                }
            }
            // we have a string and we don't know anything else about it, better to set it as text.
            if ($column['type'] == 'string' && !isset($column['length']))
            {
                $column['type'] = 'text';
            }
            $column['collation'] = DatabaseCompatibilityUtil::resolveCollationByHintType($column['type']);
            $column['unsigned'] = DatabaseCompatibilityUtil::resolveUnsignedByHintType($column['type'],
                                                                                            static::ASSUME_SIGNED);
        }

        protected static function findSuitableModelClassName($modelClassName)
        {
            if (!static::FORCE_DEFAULT_MODEL_CLASS)
            {
                $suitableModelClassName = static::findFirstNonAbstractModelInHierarchy($modelClassName);
                if ($suitableModelClassName)
                {
                    return $suitableModelClassName;
                }
            }
            if (static::DEFAULT_MODEL_CLASS)
            {
                return static::DEFAULT_MODEL_CLASS;
            }
            return false;
        }

        protected static function findFirstNonAbstractModelInHierarchy($modelClassName)
        {
            if (!$modelClassName || $modelClassName == 'RedBeanModel')
            {
                return null;
            }
            $model              = new ReflectionClass($modelClassName);
            if ($model->isAbstract())
            {
                return static::findFirstNonAbstractModelInHierarchy(get_parent_class($modelClassName));
            }
            else
            {
                return $modelClassName;
            }
        }

        protected static function registerUniqueIndexByMemberName($member, $modelClassName)
        {
            $indexName  = RedBeanModelMemberIndexMetadataAdapter::resolveRandomIndexName($member, true);
            $uniqueIndexes  = GeneralCache::getEntry(static::CACHE_KEY, array());
            $uniqueIndexes[$modelClassName][$indexName] = array('members' => array($member), 'unique' => true);
            GeneralCache::cacheEntry(static::CACHE_KEY, $uniqueIndexes);
        }
    }
?>