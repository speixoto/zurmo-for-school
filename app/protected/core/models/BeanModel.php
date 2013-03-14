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
     * Base class for working with models. Handles mapping and caching of metadata and attribute information.
     */
    abstract class BeanModel extends ObservableComponent
    {
        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1 and that the class on the side of the relationship where this is not a column in that
         * model's table.  Example: model X HAS_ONE Y.  There will be a y_id on the x table.  But in Y you would have
         * HAS_ONE_BELONGS_TO X and there would be no column in the y table.
         */
        const HAS_ONE_BELONGS_TO = 0;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class on the M side of the
         * relation.
         * Note: Currently if you have a relation that is set to HAS_MANY_BELONGS_TO, then that relation name
         * must be the strtolower() same as the related model class name.  This is the current support for this
         * relation type.  If something different is set, an exception will be thrown.
         */
        const HAS_MANY_BELONGS_TO = 1;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:1.
         */
        const HAS_ONE    = 2;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is 1:M and that the class is on the 1 side of the
         * relation.
         */
        const HAS_MANY   = 3;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a relation is M:N and that the class on the either side of the
         * relation.
         */
        const MANY_MANY  = 4;

        /**
         * Used in an extending class's getDefaultMetadata() method to specify
         * that a 1:1 or 1:M relation is one in which the left side of the relation
         * owns the model or models on the right side, meaning that if the model
         * is deleted it owns the related models and they are deleted along with it.
         * If not specified the related model is independent and is not deleted.
         */
        const OWNED     = true;

        /**
         * @see const OWNED for more information.
         * @var boolean
         */
        const NOT_OWNED = false;

        const CACHE_IDENTIFIER = 'BeanModelMapping';

        /**
         * Utilize an assumptive link when a model (X) has a relationship to another model (Y) and this is the only
         * relationship between the 2 models.  In this scenario it 'assumes' the link_name is simple.  If X HAS_MANY Y
         * then on the Y model, the column name will be just x_id.  There is no need for any link information to prefix
         * the column name.  It 'assumes' it is not needed.
         * @var integer
         */
        const LINK_TYPE_ASSUMPTIVE   = 0;

        /**
         * Utilize a specific link when a model (X) has 2 relationships to model (Y). Now the link information is needed.
         * If you specific LINK_TYPE_SPECIFIC, then the 5th parameter in the relation array must also be defined. If you
         * have X HAS_MANY Y link name = y1 and X HAS_MANY Y link name = y2, then on the y model you will have
         * the following two columns y1_x_id and y2_x_id.
         * @var integer
         */
        const LINK_TYPE_SPECIFIC     = 1;

        /**
         * Utilize for a polymorphic relationship.  Similar to LINK_TYPE_SPECIFIC, you must define the 5th parameter
         * of the relation array.  An example is if Y has a parent relationship, but the parent model can be more than
         * one type of model.
         * @var integer
         */
        const LINK_TYPE_POLYMORPHIC  = 2;

        /**
         * @see RedBeanModel::$lastClassInBeanHeirarchy
         */
        protected static $lastClassInBeanHeirarchy = 'BeanModel';

        /**
         * @var array
         */
        private static   $attributeNamesToClassNames;

        /**
         * @vara array
         */
        private static   $relationNameToRelationTypeModelClassNameAndOwns;

        /**
         * @var array
         */
        private static   $attributeNamesNotBelongsToOrManyMany;

        /**
         * @var array
         */
        private static $derivedRelationNameToTypeModelClassNameAndOppposingRelation;

        /**
         * Can the class have a bean.  Some classes do not have beans as they are just used for modeling purposes
         * and do not need to store persistant data.
         * @var boolean
         */
        private static $canHaveBean = true;

        /**
         * @returns boolean
         */
        public static function getCanHaveBean()
        {
            return self::$canHaveBean;
        }

        /**
         * Implement in children classes
         * @throws NotImplementedException
         */
        public static function getMetadata()
        {
            throw new NotImplementedException();
        }

        /**
         * Static alternative to using isAttribute which is a concrete method.
         * @param $attributeName
         * @return bool
         */
        public static function isAnAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            return $attributeName == 'id' || array_key_exists($attributeName, self::getAttributeNamesToClassNamesForModel());
        }

        /**
         * This method is needed to interpret when the attributeName is 'id'.  Since id is not an attribute
         * on the model, we manaully check for this and return the appropriate class name.
         * @param string $attributeName
         * @return the model class name for the attribute.  This could be a casted up model class name.
         */
        public static function resolveAttributeModelClassName($attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_called_class();
            }
            return self::getAttributeModelClassName($attributeName);
        }

        /**
         * Returns the model class name for an
         * attribute name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public static function getAttributeModelClassName($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            $attributeNamesToClassNames = self::getAttributeNamesToClassNamesForModel();
            return $attributeNamesToClassNames[$attributeName];
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method.
         */
        public static function isRelation($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            return array_key_exists($attributeName, static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel());
        }

        /**
         * Returns true if the named attribute is one of the
         * relation names defined by the extending
         * class's getMetadata() method, and specifies RedBeanModel::OWNED.
         */
        public static function isOwnedRelation($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return array_key_exists($attributeName, $relationAndOwns) &&
                $relationAndOwns[$attributeName][2];
        }

        /**
         * Returns the relation type
         * relation name defined by the extending class's getMetadata() method.
         */
        public static function getRelationType($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][0];
        }

        /**
         * Returns the model class name for a
         * relation name defined by the extending class's getMetadata() method.
         * For use by RedBeanModelDataProvider. Is unlikely to be of any
         * use to an application.
         */
        public static function getRelationModelClassName($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][1];
        }

        /**
         * Returns the link type for a
         * relation name defined by the extending class's getMetadata() method.
         */
        public function getRelationLinkType($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][3];
        }

        /**
         * Returns the link name for a
         * relation name defined by the extending class's getMetadata() method.
         */
        public function getRelationLinkName($relationName)
        {
            assert('self::isRelation($relationName, get_called_class())');
            $relationAndOwns = static::getRelationNameToRelationTypeModelClassNameAndOwnsForModel();
            return $relationAndOwns[$relationName][4];
        }

        /**
         * Returns the opposing relation name of a derived relation
         * defined by the extending class's getMetadata() method.
         */
        public static function isADerivedRelationViaCastedUpModel($relationName)
        {
            $derivedRelations = static::getDerivedRelationNameToTypeModelClassNameAndOppposingRelationForModel();
            if(array_key_exists($relationName, $derivedRelations))
            {
                return true;
            }
            return false;
        }

        /**
         * Returns the relation type of a derived relation
         * defined by the extending class's getMetadata() method.
         */
        public function getDerivedRelationType($relationName)
        {
            assert("\self::isADerivedRelationViaCastedUpModel('$relationName')");
            $derivedRelations = static::getDerivedRelationNameToTypeModelClassNameAndOppposingRelationForModel();
            return $derivedRelations[$relationName][0];
        }

        /**
         * Returns the relation model class name of a derived relation
         * defined by the extending class's getMetadata() method.
         */
        public function getDerivedRelationModelClassName($relationName)
        {
            assert("\self::isADerivedRelationViaCastedUpModel('$relationName')");
            $derivedRelations = static::getDerivedRelationNameToTypeModelClassNameAndOppposingRelationForModel();
            return $derivedRelations[$relationName][1];
        }

        /**
         * Returns the opposing relation name of a derived relation
         * defined by the extending class's getMetadata() method.
         */
        public function getDerivedRelationViaCastedUpModelOpposingRelationName($relationName)
        {
            assert("\self::isADerivedRelationViaCastedUpModel('$relationName')");
            $derivedRelations = static::getDerivedRelationNameToTypeModelClassNameAndOppposingRelationForModel();
            return $derivedRelations[$relationName][2];
        }

        /**
         * Given an attribute return the column name.
         * @param string $attributeName
         * @return string
         */
        public static function getColumnNameByAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            if (self::isRelation($attributeName))
            {
                $modelClassName = get_called_class();
                $columnName = $modelClassName::getForeignKeyName($modelClassName, $attributeName);
            }
            else
            {
                $columnName = strtolower($attributeName);
            }
            return $columnName;
        }

        /**
         * Static implementation of attributeNames()
         */
        public static function getAttributeNames()
        {
            return array_keys(static::getAttributeNamesToClassNamesForModel());
        }

        /**
         * Static implementation of generateAttributeLabel()
         */
        public static function generateAnAttributeLabel($attributeName)
        {
            assert('self::isAnAttribute($attributeName, get_called_class())');
            return ucfirst(preg_replace('/([A-Z0-9])/', ' \1', $attributeName));
        }

        public static function getAbbreviatedAttributeLabel($attributeName)
        {
            return static::getAbbreviatedAttributeLabelByLanguage($attributeName, Yii::app()->language);
        }

        /**
         * Public for message checker only.
         */
        public static function getUntranslatedAbbreviatedAttributeLabels()
        {
            return static::untranslatedAbbreviatedAttributeLabels();
        }

        /**
         * Array of untranslated abbreviated attribute labels.
         */
        protected static function untranslatedAbbreviatedAttributeLabels()
        {
            return array();
        }

        protected static function untranslatedAttributeLabels()
        {
            return array();
        }

        /**
         * Given an attributeName and a language, retrieve the translated attribute label. Attempts to find a customized
         * label in the metadata first, before falling back on the standard attribute label for the specified attribute.
         * @param string $attributeName
         * @param string $language
         * @return string - translated attribute label
         */
        protected static function getAbbreviatedAttributeLabelByLanguage($attributeName, $language)
        {
            assert('is_string($attributeName)');
            assert('is_string($language)');
            $labels = static::untranslatedAbbreviatedAttributeLabels();
            if (isset($labels[$attributeName]))
            {
                return ZurmoHtml::tag('span', array('title' => static::generateAnAttributeLabel($attributeName)),
                    Zurmo::t('Default', $labels[$attributeName],
                        LabelUtil::getTranslationParamsForAllModules(), null, $language));
            }
            else
            {
                return null;
            }
        }

        /**
         * @return array
         */
        protected static function getMixedInModelClassNames()
        {
            return array();
        }

        /**
         * @return array
         */
        protected static function getAttributeNamesToClassNamesForModel()
        {
            if(!PHP_CACHING_ON || !isset(self::$attributeNamesToClassNames[get_called_class()]))
            {
                self::resolveCacheAndMapMetadataForAllClassesInHeirarchy();
            }
            return self::$attributeNamesToClassNames[get_called_class()];
        }

        /**
         * @return array
         */
        protected static function getAttributeNamesNotBelongsToOrManyManyForModel()
        {
            if(!PHP_CACHING_ON || !isset(self::$attributeNamesNotBelongsToOrManyMany[get_called_class()]))
            {
                self::resolveCacheAndMapMetadataForAllClassesInHeirarchy();
            }
            return self::$attributeNamesNotBelongsToOrManyMany[get_called_class()];
        }

        /**
         * @return array
         */
        protected static function getRelationNameToRelationTypeModelClassNameAndOwnsForModel()
        {
            if(!PHP_CACHING_ON || !isset(self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()]))
            {
                self::resolveCacheAndMapMetadataForAllClassesInHeirarchy();
            }
            return self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()];
        }

        /**
         * @return array
         */
        protected static function getDerivedRelationNameToTypeModelClassNameAndOppposingRelationForModel()
        {
            if(!PHP_CACHING_ON || !isset(self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()]))
            {
                self::resolveCacheAndMapMetadataForAllClassesInHeirarchy();
            }
            return self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()];
        }

        /**
         * @param string $modelClassName
         */
        protected static function forgetBeanModel($modelClassName)
        {
            if(isset(self::$attributeNamesToClassNames[$modelClassName]))
            {
                unset(self::$attributeNamesToClassNames[$modelClassName]);
            }
            if(isset(self::$relationNameToRelationTypeModelClassNameAndOwns[$modelClassName]))
            {
                unset(self::$relationNameToRelationTypeModelClassNameAndOwns[$modelClassName]);
            }
            if(isset(self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[$modelClassName]))
            {
                unset(self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[$modelClassName]);
            }
            if(isset(self::$attributeNamesNotBelongsToOrManyMany[$modelClassName]))
            {
                unset(self::$attributeNamesNotBelongsToOrManyMany[$modelClassName]);
            }
            BeanModelCache::forgetEntry(self::CACHE_IDENTIFIER . get_called_class());
        }

        protected static function forgetAllBeanModels()
        {
            self::$attributeNamesToClassNames                                  = null;
            self::$relationNameToRelationTypeModelClassNameAndOwns             = null;
            self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation = null;
            self::$attributeNamesNotBelongsToOrManyMany                        = null;
            BeanModelCache::forgetAll();
        }

        protected static function resolveCacheAndMapMetadataForAllClassesInHeirarchy()
        {
            try
            {
                $cachedData = BeanModelCache::getEntry(self::CACHE_IDENTIFIER . get_called_class());
                self::$attributeNamesToClassNames[get_called_class()]                              =
                    $cachedData['attributeNamesToClassNames'][get_called_class()];
                self::$attributeNamesNotBelongsToOrManyMany[get_called_class()]                    =
                    $cachedData['attributeNamesNotBelongsToOrManyMany'][get_called_class()];
                self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()]         =
                    $cachedData['relationNameToRelationTypeModelClassNameAndOwns'][get_called_class()];
                self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()]         =
                    $cachedData['derivedRelationNameToTypeModelClassNameAndOppposingRelation'][get_called_class()];
            }
            catch(NotFoundException $e)
            {
                self::mapMetadataForAllClassesInHeirarchy();
                $cachedData = array();
                $cachedData['attributeNamesToClassNames'][get_called_class()]                      =
                    self::$attributeNamesToClassNames[get_called_class()];
                $cachedData['attributeNamesNotBelongsToOrManyMany'][get_called_class()]            =
                    self::$attributeNamesNotBelongsToOrManyMany[get_called_class()];
                $cachedData['relationNameToRelationTypeModelClassNameAndOwns'][get_called_class()] =
                    self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()];
                $cachedData['derivedRelationNameToTypeModelClassNameAndOppposingRelation'][get_called_class()] =
                    self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()];
                BeanModelCache::cacheEntry(self::CACHE_IDENTIFIER . get_called_class(), $cachedData);
            }
        }


        /**
         * Maps metadata for the class and all of the classes in the heirarchy up to the BeanModel
         */
        private static function mapMetadataForAllClassesInHeirarchy()
        {
            self::$attributeNamesToClassNames[get_called_class()]                                  = array();
            self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()]             = array();
            self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()] = array();
            self::$attributeNamesNotBelongsToOrManyMany[get_called_class()]                        = array();
            foreach (array_reverse(RuntimeUtil::getClassHierarchy(get_called_class(), static::$lastClassInBeanHeirarchy)) as $modelClassName)
            {
                if ($modelClassName::getCanHaveBean())
                {
                    self::mapMetadataByModelClassName($modelClassName);
                }
            }
            foreach(static::getMixedInModelClassNames() as $modelClassName)
            {
                if ($modelClassName::getCanHaveBean())
                {
                    self::mapMetadataByModelClassName($modelClassName);
                }
            }
        }

        /**
         * @param $modelClassName
         * @throws NotSupportedException
         */
        private static function mapMetadataByModelClassName($modelClassName)
        {
            assert('is_string($modelClassName)');
            assert('$modelClassName != ""');
            $metadata = static::getMetadata();
            if (isset($metadata[$modelClassName]))
            {
                if (isset($metadata[$modelClassName]['members']))
                {
                    foreach ($metadata[$modelClassName]['members'] as $memberName)
                    {

                        self::$attributeNamesToClassNames[get_called_class()][$memberName] = $modelClassName;
                        self::$attributeNamesNotBelongsToOrManyMany[get_called_class()][]  = $memberName;
                    }
                }
            }
            if (isset($metadata[$modelClassName]['relations']))
            {
                foreach ($metadata[$modelClassName]['relations'] as $relationName => $relationTypeModelClassNameAndOwns)
                {
                    assert('in_array(count($relationTypeModelClassNameAndOwns), array(2, 3, 4, 5))');

                    $relationType           = $relationTypeModelClassNameAndOwns[0];
                    $relationModelClassName = $relationTypeModelClassNameAndOwns[1];
                    if ($relationType == self::HAS_MANY_BELONGS_TO &&
                        strtolower($relationName) != strtolower($relationModelClassName))
                    {
                        $label = 'Relations of type HAS_MANY_BELONGS_TO must have the relation name ' .
                            'the same as the related model class name. Relation: {relationName} ' .
                            'Relation model class name: {relationModelClassName}';
                        throw new NotSupportedException(Zurmo::t('Core', $label,
                            array('{relationName}' => $relationName,
                                '{relationModelClassName}' => $relationModelClassName)));
                    }
                    if (count($relationTypeModelClassNameAndOwns) >= 3 &&
                        $relationTypeModelClassNameAndOwns[2] == self::OWNED)
                    {
                        $owns = true;
                    }
                    else
                    {
                        $owns = false;
                    }
/**
                    if (count($relationTypeModelClassNameAndOwns) == 4 && $relationType != self::HAS_MANY)
                    {
                        throw new NotSupportedException();
                    }
                    if (count($relationTypeModelClassNameAndOwns) == 4)
                    {
                        $relationPolyOneToManyName = $relationTypeModelClassNameAndOwns[3];
                    }
                    else
                    {
                        $relationPolyOneToManyName = null;
                    }
 * */
                    $linkType          = null;
                    $relationLinkName  = null;
                    self::resolveLinkTypeAndRelationLinkName($relationTypeModelClassNameAndOwns, $linkType,
                          $relationLinkName);
                    assert('in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, ' .
                        'self::HAS_ONE, self::HAS_MANY, self::MANY_MANY))');
                    self::$attributeNamesToClassNames[get_called_class()][$relationName] = $modelClassName;
                    self::$relationNameToRelationTypeModelClassNameAndOwns[get_called_class()][$relationName] =
                        array($relationType,
                              $relationModelClassName,
                              $owns,
                              $linkType,
                              $relationLinkName);
                    if (!in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, self::MANY_MANY)))
                    {
                        self::$attributeNamesNotBelongsToOrManyMany[get_called_class()][] = $relationName;
                    }
                }
            }
            if (isset($metadata[$modelClassName]['derivedRelationsViaCastedUpModel']))
            {
                foreach ($metadata[$modelClassName]['derivedRelationsViaCastedUpModel'] as $relationName =>
                         $relationTypeModelClassNameAndOpposingRelation)
                {
                    self::$derivedRelationNameToTypeModelClassNameAndOppposingRelation[get_called_class()][$relationName] =
                         $relationTypeModelClassNameAndOpposingRelation;
                }
            }
        }

        protected static function resolveLinkTypeAndRelationLinkName($relationTypeModelClassNameAndOwns, & $linkType,
                                                                     & $relationLinkName)
        {
            if (count($relationTypeModelClassNameAndOwns) == 4 &&
                $relationTypeModelClassNameAndOwns[3] != self::LINK_TYPE_ASSUMPTIVE)
            {
                throw new NotSupportedException();
            }
            if (count($relationTypeModelClassNameAndOwns) == 5)
            {
                $linkType          = $relationTypeModelClassNameAndOwns[3];
                $relationLinkName  = $relationTypeModelClassNameAndOwns[4];
            }
            else
            {
                $linkType          = self::LINK_TYPE_ASSUMPTIVE;
                $relationLinkName  = null;
            }
        }
    }
?>