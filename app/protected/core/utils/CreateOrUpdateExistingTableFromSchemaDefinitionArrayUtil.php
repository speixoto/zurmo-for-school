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
     * Utility to generate or update a table in database when provided with a database schema in array format
     */
    abstract class CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil
    {
        // TODO: @Shoaibi: Critical: Documentation
        // TODO: @Shoaibi: Critical: Tests
        public static function generateOrUpdateTableBySchemaDefinition(array $schemaDefinition, & $messageLogger)
        {
            $isValidSchema  = static::validateSchemaDefinition($schemaDefinition);
            if (!$isValidSchema)
            {
                $errorMessage   = Zurmo::t('Core', 'Invalid Schema definition received.');
                $messageLogger->addErrorMessage($errorMessage);
                throw new CException($errorMessage);
            }

            $columnsAndIndexes  = reset($schemaDefinition);
            $tableName          = key($schemaDefinition);
            $needsCreateTable = true;
            $existingFields     = array();
            $messageLogger->addInfoMessage(Zurmo::t('Core', 'Creating/Updating schema for {{tableName}}',
                                                                                array('{{tableName}}' => $tableName)));
            if (!isset(Yii::app()->params['isFreshInstall']) || !Yii::app()->params['isFreshInstall'])
            {
                try
                {
                    $existingFields     = R::$writer->getColumns($tableName);
                    $needsCreateTable   = false;
                }
                catch (RedBean_Exception_SQL $e)
                {
                    //42S02 - Table does not exist.
                    if (!in_array($e->getSQLState(), array('42S02')))
                    {
                        throw $e;
                    }
                }
            }

            if ($needsCreateTable)
            {
                $query = static::resolveCreateTableQuery($tableName, $columnsAndIndexes);
                R::exec($query);
                return true;
            }
            else
            {
                // TODO: @Shoaibi: Critical: implement.
                // var dump existing fields
                // use a function to loop through all fields and return mismatched fields(type, notNull, default, unsigned, collation)
                // use a function to create query against those mismatched fields
                // execute query
                // return true/false depending on query execution
            }
        }

        protected static function validateSchemaDefinition(array $schemaDefinition)
        {
            if (count($schemaDefinition) == 1)
            {

                $columnsAndIndexes = reset($schemaDefinition);
                if (count($columnsAndIndexes) == 2 &&
                                            isset($columnsAndIndexes['columns'], $columnsAndIndexes['indexes']))
                {
                    if(static::validateColumnDefinitionsFromSchema($columnsAndIndexes['columns']))
                    {
                        return static::validateIndexDefinitionsFromSchema($columnsAndIndexes['indexes']);
                    }
                }
            }
            return false;
        }

        protected static function validateColumnDefinitionsFromSchema(array $columns)
        {
            $valid  = true;
            $keys   = array('name', 'type', 'unsigned', 'notNull', 'collation', 'default');
            foreach ($columns as $column)
            {
                if (count($column) != 6)
                {
                    $valid = false;
                    break;
                }
                foreach ($keys as $key)
                {
                    if (!ArrayUtil::isValidArrayIndex($key, $column))
                    {
                        $valid = false;
                        break;
                    }
                }
            }
            return $valid;
        }

        protected static function validateIndexDefinitionsFromSchema(array $indexes)
        {
            $valid  = true;
            foreach ($indexes as $index)
            {
                if (count($index) != 2 || !ArrayUtil::isValidArrayIndex('columns', $index) ||
                                    !ArrayUtil::isValidArrayIndex('unique', $index) || !is_array($index['columns']))
                {
                    $valid = false;
                    break;
                }
            }
            return $valid;
        }

        protected static function resolveAlterQueryForColumn($column)
        {

        }

        protected static function resolveAlterQueryForIndex($index)
        {

        }

        protected static function resolveAlterTableQuery($table, $mismatchColumns)
        {
            // for each column get statement and put them in an array
            // for each index get statement and put it in same array in above
            // join on , and line break
            // prepend id and append other stuff
            // return
        }

        protected static function resolveCreateTableQuery($tableName, $columnsAndIndexesSchema)
        {
            $columns        = array('`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');
            $indexes        = array('PRIMARY KEY (id)');
            foreach ($columnsAndIndexesSchema['columns'] as $column)
            {
                $columns[]    = static::resolveColumnForTableCreation($column);
            }
            foreach ($columnsAndIndexesSchema['indexes'] as $indexName => $indexMetadata)
            {
                $indexes[]    = static::resolveIndexForTableCreation($indexName, $indexMetadata);
            }
            // PHP_EOLs below are purely for readability, sql would work just fine without it.
            $tableMetadata  = CMap::mergeArray($columns, $indexes);
            $tableMetadata  = join(',' . PHP_EOL, $tableMetadata);
            $query          = "CREATE TABLE $tableName (" . PHP_EOL .
                                $tableMetadata . PHP_EOL .
                                " ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
            return $query;

        }

        protected static function resolveColumnForTableCreation($column)
        {
            extract($column);
            $clause = "`${name}` ${type} ${unsigned} ${notNull} ${collation} {$default}";
            return $clause;
        }

        protected static function resolveIndexForTableCreation($indexName, $indexMetadata)
        {
            $clause = "KEY ${indexName} (" . join(',', $indexMetadata['columns']) . ")";
            if ($indexMetadata['unique'])
            {
                $clause = 'UNIQUE ' . $clause;
            }
            return $clause;
        }
    }
?>