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
     * Adapter class to generate column definition when provided with relationMetadata
     */
    abstract class RedBeanModelRelationToColumnAdapter
    {
        protected static $polymorphicLinkColumns = array();
        // TODO: @Shoaibi: Critical: Add some documentation for this.
        // TODO: @Shoaibi: Critical: Tests

        public static function resolvePolymorphicColumnsByTableName($tableName)
        {
            return static::$polymorphicLinkColumns[$tableName];
        }

        public static function resolve($modelClassName, $relationName, array $relationMetadata, & $messageLogger)
        {
            $column = null;
            $relationType           = $relationMetadata[0];
            $linkType               = $relationMetadata[3];
            $relatedModelClass      = $relationMetadata[1];
            assert('in_array($relationType, array(self::HAS_ONE_BELONGS_TO, self::HAS_MANY_BELONGS_TO, ' .
                                                                'self::HAS_ONE, self::HAS_MANY, self::MANY_MANY))');
            if ($relationType == RedBeanModel::MANY_MANY)
            {
                RedBeanModelToJoinTableAdapter::resolve($modelClassName, $relationMetadata, $messageLogger);
                return null;
            }
            else if (in_array($relationType, array(RedBeanModel::HAS_ONE, RedBeanModel::HAS_MANY_BELONGS_TO)))
            {
                $linkName               = null;
                if ($linkType == RedBeanModel::LINK_TYPE_ASSUMPTIVE &&
                                                        strtolower($relatedModelClass) != strtolower($relationName))
                {
                    $linkName   = strtolower($relationName) . '_';
                }
                $name   = $linkName . RedBeanModel::getForeignKeyName($modelClassName, $relationName);
                $column = RedBeanModelMemberToColumnNameUtil::resolveForeignKeyColumnMetadata($name);
            }
            else if ($relationType == RedBeanModel::HAS_MANY && $linkType == RedBeanModel::LINK_TYPE_POLYMORPHIC)
            {
                static::setColumnsForPolymorphicLink($relatedModelClass, $relationMetadata[4]);
            }
            // ignore HAS_MANY and HAS_ONE_BELONGS_TO as we are dealing with HAS_ONE and HAS_MANY_BELONGS e.g.
            // we are ignore the sides which shouldn't have columns.
            return $column;
        }

        protected static function setColumnsForPolymorphicLink($relatedModelClassName, $linkName)
        {
            $columns        = array();
            $columns[]      = RedBeanModelMemberToColumnNameUtil::resolveForeignKeyColumnMetadata(
                                                RedBeanModelMemberToColumnNameUtil::resolve($linkName). '_id');
            $columns[]      = static::resolvePolymorphicTypeColumnByLinkName($linkName);
            $tableName      = RedBeanModel::getTableName($relatedModelClassName);
            static::$polymorphicLinkColumns[$tableName] = $columns;
        }

        protected static function resolvePolymorphicTypeColumnByLinkName($linkName)
        {
            $column['name']         = RedBeanModelMemberToColumnNameUtil::resolve($linkName) . '_type';
            $column['type']         = 'string';
            $column['length']       = 255;
            $column['unsigned']     = DatabaseCompatibilityUtil::resolveUnsignedByHintType($column['type'], false);
            $column['notNull']      = 'NULL';
            $column['collation']    = DatabaseCompatibilityUtil::resolveCollationByHintType($column['type']);
            $column['default']      = 'DEFAULT NULL';
            $column['type']         = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType($column['type'],
                                                                                                    $column['length']);
            unset($column['length']);
            return $column;
        }
    }
?>