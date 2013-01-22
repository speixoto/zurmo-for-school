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
     * Base class of workflow rules that assist with workflow management.  Extend this class to make
     * a set of WorkflowtRules that is for a specific module or a combination of modules and/or models.
     */
    abstract class WorkflowRules //todo: extend some base rules, so getMetadata and setMetadata can be shared by reporting rules too.
    {
        protected $modelClassName;

        public static function makeByModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $rulesClassName = $moduleClassName::getPluralCamelCasedName() . 'WorkflowRules';
            return new $rulesClassName();
        }

        /**
         * Returns metadata for use in the rules.  Will attempt to retrieve from cache if
         * available, otherwill retrieve from database and cache.
         * @see getDefaultMetadata()
         * @param $user The current user.
         * @returns An array of metadata.
         */
        public static function getMetadata()
        {
            $className = get_called_class();
            try
            {
                return GeneralCache::getEntry($className . 'Metadata');
            }
            catch (NotFoundException $e)
            {
            }
            $metadata = MetadataUtil::getMetadata($className);
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            GeneralCache::cacheEntry($className . 'Metadata', $metadata);
            return $metadata;
        }

        /**
         * Sets new metadata.
         * @param $metadata An array of metadata.
         * @param $user The current user.
         */
        public static function setMetadata(array $metadata)
        {
            $className = get_called_class();
            if (YII_DEBUG)
            {
                $className::assertMetadataIsValid($metadata);
            }
            MetadataUtil::setMetadata($className, $metadata);
            GeneralCache::cacheEntry($className . 'Metadata', $metadata);
        }

        /**
         * Returns default metadata for use in automatically generating the rules.
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected static function assertMetadataIsValid(array $metadata)
        {
        }
//todo: everything below here needs to be thought of for how it applies to workflow and keep or remove
        public function relationIsReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
            in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                return true;
            }

            if(in_array($model->getRelationModelClassName($relation),
                        array('OwnedCustomField',
                              'CustomField',
                              'OwnedMultipleValuesCustomField',
                              'MultipleValuesCustomField',
                              'CurrencyValue')))
            {
                return true;
            }
            return false;
        }

        public function attributeIsReportable(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['nonReportable']) &&
            in_array($attribute, $metadata[$modelClassName]['nonReportable']))
            {
                return false;
            }
            return true;
        }

        public function getDerivedAttributeTypesData(RedBeanModel $model)
        {
            $derivedAttributeTypesData = array();
            $metadata = static::getMetadata();
            foreach (array_reverse(RuntimeUtil::getClassHierarchy(
                                   get_class($model), $model::getLastClassInBeanHeirarchy())) as $modelClassName)
            {
                if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['derivedAttributeTypes']))
                {
                    foreach($metadata[$modelClassName]['derivedAttributeTypes'] as $derivedAttributeType)
                    {

                        $elementClassName          = $derivedAttributeType . 'Element';
                        $derivedAttributeTypesData
                        [$derivedAttributeType]    = array('label'                => $elementClassName::getDisplayName(),
                                                           'derivedAttributeType' => $derivedAttributeType);
                    }
                }
            }
            return $derivedAttributeTypesData;
        }

        public function getAvailableOperatorsTypes(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['availableOperatorsTypes']) &&
               isset($attribute, $metadata[$modelClassName]['availableOperatorsTypes'][$attribute]))
            {
                return $metadata[$modelClassName]['availableOperatorsTypes'][$attribute];
            }
            return null;
        }

        public function getFilterValueElementType(RedBeanModel $model, $attribute)
        {
            assert('is_string($attribute)');
            $modelClassName = $model->getAttributeModelClassName($attribute);
            $metadata = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['filterValueElementTypes']) &&
               isset($attribute, $metadata[$modelClassName]['filterValueElementTypes'][$attribute]))
            {
                return $metadata[$modelClassName]['filterValueElementTypes'][$attribute];
            }
            return null;
        }

        public function getSortAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if(isset($metadata[$modelClassName]['relationsReportedAsAttributesSortAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesSortAttributes'][$relation];
                }
                else
                {
                    throw new NotSupportedException('Relations that report as attributes must also have a defined sort attribute');
                }
            }
            if(in_array($model->getRelationModelClassName($relation),
                array('OwnedCustomField',
                      'CustomField',
                      'OwnedMultipleValuesCustomField',
                      'MultipleValuesCustomField',
                      'CurrencyValue')))
            {
                return 'value';
            }
            throw new NotSupportedException();
        }

        public function getGroupByRelatedAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if(isset($metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation];
                }
                else
                {
                    return null;
                }
            }
            if(in_array($model->getRelationModelClassName($relation),
                array(  'OwnedCustomField',
                        'CustomField',
                        'OwnedMultipleValuesCustomField',
                        'MultipleValuesCustomField',
                        'CurrencyValue')))
            {
                return 'value';
            }
            throw new NotSupportedException();
        }

        public function getRawValueRelatedAttributeForRelationReportedAsAttribute(RedBeanModel $model, $relation)
        {
            assert('is_string($relation)');
            $modelClassName = $model->getAttributeModelClassName($relation);
            $metadata       = static::getMetadata();
            if(isset($metadata[$modelClassName]) && isset($metadata[$modelClassName]['relationsReportedAsAttributes']) &&
                in_array($relation, $metadata[$modelClassName]['relationsReportedAsAttributes']))
            {
                if(isset($metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation]))
                {
                    return $metadata[$modelClassName]['relationsReportedAsAttributesGroupByAttributes'][$relation];
                }
            }
        }

        public static function getVariableStateModuleLabel(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        public static function canUserAccessModuleInAVariableState(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        public static function resolveStateAdapterUserHasAccessTo(User $user)
        {
            assert('$user->id > 0');
            throw new NotImplementedException();
        }

        public static function getVariableStateValuesForUser($modelClassName, User $user)
        {
            assert('is_string($modelClassName)');
            assert('$user->id > 0');
        }
    }
?>