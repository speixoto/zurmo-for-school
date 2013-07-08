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
        protected static $processedTables = array();

        /**
         * Provide a schema definition array queries to create/update database schema are executed.
         * @param array $schemaDefinition
         * @param $messageLogger
         * @throws CException
         * @throws Exception|RedBean_Exception_SQL
         */
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
            if (in_array($tableName, static::$processedTables) && Yii::app()->params['isFreshInstall'])
            {
                // we don't skip if running under updateSchema as we might have multiple requests to update same table.
                return;
            }
            $needsCreateTable   = true;
            $existingFields     = array();
            $existingIndexes    = array();
            $messageLogger->addInfoMessage(Zurmo::t('Core', 'Creating/Updating schema for {{tableName}}',
                                                                                array('{{tableName}}' => $tableName)));
            if (!isset(Yii::app()->params['isFreshInstall']) || !Yii::app()->params['isFreshInstall'])
            {
                try
                {
                    $existingFields     = ZurmoRedBean::$writer->getColumnsWithDetails($tableName);
                    $existingIndexes    = ZurmoRedBean::$writer->getIndexes($tableName);
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
            }
            else
            {
                $query  = static::resolveAlterTableQuery($tableName,
                                                            $columnsAndIndexes,
                                                            $existingFields,
                                                            $existingIndexes);
            }
            if ($query)
            {
                ZurmoRedBean::exec($query);
            }
            if (!in_array($tableName, static::$processedTables))
            {
                static::$processedTables[] = $tableName;
            }
        }

        /**
         * Returns an array of processed tables.
         * @return array
         */
        public static function resolveProcessedTables()
        {
            // this is only used by tests
            return static::$processedTables;
        }

        protected static function validateSchemaDefinition(array $schemaDefinition)
        {
            if (count($schemaDefinition) == 1)
            {
                $columnsAndIndexes = reset($schemaDefinition);
                $tableName          = key($schemaDefinition);
                if (is_string($tableName) && count($columnsAndIndexes) == 2 &&
                                            isset($columnsAndIndexes['columns'], $columnsAndIndexes['indexes']))
                {
                    if(static::validateColumnDefinitionsFromSchema($columnsAndIndexes['columns']))
                    {
                        return static::validateIndexDefinitionsFromSchema($columnsAndIndexes['indexes'],
                                                                            $columnsAndIndexes['columns']);
                    }
                }
            }
            return false;
        }

        protected static function validateColumnDefinitionsFromSchema(array $columns)
        {
            $valid  = true;
            if (empty($columns))
            {
                return false;
            }
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

        protected static function validateIndexDefinitionsFromSchema(array $indexes, array $columns)
        {
            $valid  = true;
            $columnNames = RedBeanModelMemberToColumnNameUtil::resolveColumnNamesArrayFromColumnSchemaDefinition($columns);
            foreach ($indexes as $indexName => $index)
            {
                if (!is_string($indexName) || count($index) != 2 || !ArrayUtil::isValidArrayIndex('columns', $index) ||
                                    !ArrayUtil::isValidArrayIndex('unique', $index) || !is_array($index['columns']))
                {
                    $valid = false;
                    break;
                }
                foreach($index['columns'] as $column)
                {
                    list($column) = explode('(', $column);
                    if (!in_array($column, $columnNames))
                    {
                        $valid = false;
                        break;
                    }
                }
            }
            return $valid;
        }

        protected static function resolveAlterQueryForColumn($column)
        {
            $columnDefinition   = $column['columnDefinition'];
            $statement          = strtoupper($column['method']) . ' ';
            $isAddition         = ($column['method'] == 'add');
            $statement          .= static::resolveColumnStatementFromDefinition($columnDefinition, $isAddition);
            return $statement;
        }

        protected static function resolveColumnUpgradeQueries($columns, $existingFields)
        {
            $columnsNeedingUpgrade      = array();
            $columnUpgradeStatements    = array();
            foreach ($columns as $column)
            {
                if ($upgradeDefinition = static::resolveColumnUpgradeDefinition($column, $existingFields))
                {
                    $columnsNeedingUpgrade[] = $upgradeDefinition;
                }
            }
            foreach ($columnsNeedingUpgrade as $columnNeedingUpgrade)
            {
                $columnUpgradeStatements[] = static::resolveAlterQueryForColumn($columnNeedingUpgrade);
            }
            return $columnUpgradeStatements;
        }

        protected static function doesIndexNeedUpgrade($indexMetadata, $existingIndexes)
        {
            $needsUpgrade   = true;
            $indexColumns   = $indexMetadata['columns'];
            sort($indexColumns);
            foreach ($existingIndexes as $existingIndexMetadata)
            {
                $existingIndexColumns = $existingIndexMetadata['columns'];
                sort($existingIndexColumns);
                if ($indexMetadata['unique'] === $existingIndexMetadata['unique'] && $indexColumns === $existingIndexColumns)
                {
                    $needsUpgrade = false;
                    break;
                }
            }
            return $needsUpgrade;
        }

        protected static function resolveIndexUpgradeQueries($indexes, $existingIndexes)
        {
            $indexesNeedingUpgrade      = array();
            $indexUpgradeStatements     = array();
            foreach ($indexes as $indexName => $indexMetadata)
            {
                if (static::doesIndexNeedUpgrade($indexMetadata, $existingIndexes))
                {
                    $indexesNeedingUpgrade[$indexName] = $indexMetadata;
                }
            }
            foreach ($indexesNeedingUpgrade as $indexName => $indexMetadata)
            {
                $indexUpgradeStatements[] = static::resolveIndexStatementCreation($indexName, $indexMetadata, true);
            }
            return $indexUpgradeStatements;
        }

        protected static function resolveAlterTableQuery($tableName, $columnsAndIndexes, $existingFields, $existingIndexes)
        {
            $upgradeStatements       = array();
            $columnUpgradeStatements = static::resolveColumnUpgradeQueries($columnsAndIndexes['columns'], $existingFields);
            $indexUpgradeStatements  = static::resolveIndexUpgradeQueries($columnsAndIndexes['indexes'], $existingIndexes);
            $upgradeStatements       = CMap::mergeArray($columnUpgradeStatements, $indexUpgradeStatements);
            if (!empty($upgradeStatements))
            {
                $upgradeStatements  = join(',' . PHP_EOL, $upgradeStatements);
                $query              = "ALTER TABLE `${tableName}` " . PHP_EOL .
                                        $upgradeStatements . ";";
                return $query;
            }
            return false;
        }

        protected static function resolveColumnUpgradeDefinition($column, $existingFields)
        {
            if (!in_array($column['name'], array_keys($existingFields)))
            {
                return array('columnDefinition' => $column, 'method' => 'add');
            }
            else if (static::doesColumnNeedUpgrade($column, $existingFields[$column['name']]))
            {
                return array('columnDefinition' => $column, 'method' => 'change');
            }
            return null;
        }

        protected static function doesColumnNeedUpgrade($column, $existingField)
        {
            if (static::isColumnTypeSameAsExistingField($column, $existingField) &&
                static::isColumnNullSameAsExistingField($column, $existingField) &&
                static::isColumnDefaultValueSameAsExistingField($column, $existingField))
            {
                return false;
            }
            return true;
        }

        protected static function isColumnTypeSameAsExistingField($column, $existingField)
        {
            $resolvedType       = $column['type'];
            if ($column['unsigned'])
            {
                $resolvedType .= ' ' . $column['unsigned'];
            }
            $resolvedType       = strtolower($resolvedType);
            $existingFieldType  = strtolower($existingField['Type']);
            return ($resolvedType == $existingFieldType);
        }

        protected static function isColumnNullSameAsExistingField($column, $existingField)
        {
            $notNull = 'NOT NULL';
            if ($existingField['Null'] == 'YES')
            {
                $notNull = 'NULL';
            }
            return ($column['notNull'] == $notNull);
        }

        protected static function isColumnDefaultValueSameAsExistingField($column, $existingField)
        {
            $default = null;
            if ($column['default'] != 'DEFAULT NULL')
            {
                $default = substr($column['default'], strpos($column['default'], 'DEFAULT '));
            }
            return ($default == $existingField['Default']);
        }

        protected static function resolveCreateTableQuery($tableName, $columnsAndIndexesSchema)
        {
            $columns        = array('`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');
            $indexes        = array('PRIMARY KEY (id)');
            foreach ($columnsAndIndexesSchema['columns'] as $column)
            {
                $columns[]    = static::resolveColumnStatementFromDefinition($column, true);
            }
            foreach ($columnsAndIndexesSchema['indexes'] as $indexName => $indexMetadata)
            {
                $indexes[]    = static::resolveIndexStatementCreation($indexName, $indexMetadata, false);
            }
            // PHP_EOLs below are purely for readability, sql would work just fine without it.
            $tableMetadata  = CMap::mergeArray($columns, $indexes);
            $tableMetadata  = join(',' . PHP_EOL, $tableMetadata);
            $query          = "CREATE TABLE `${tableName}` (" . PHP_EOL .
                                $tableMetadata . PHP_EOL .
                                " ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";
            return $query;

        }

        protected static function resolveColumnStatementFromDefinition($column, $isAddition = true)
        {
            extract($column);
            if ($isAddition)
            {
                $clause = "`${name}` ${type} ${unsigned} ${notNull} ${collation} {$default}";
            }
            else
            {
                $clause = "`${name}` `${name}` ${type} ${unsigned} ${collation} ${notNull} {$default}";
            }
            return $clause;
        }

        protected static function resolveIndexStatementCreation($indexName, $indexMetadata, $alterTable = false)
        {
            $clause = "KEY ${indexName} (" . join(',', $indexMetadata['columns']) . ")";
            if ($indexMetadata['unique'])
            {
                $clause = 'UNIQUE ' . $clause;
            }
            if ($alterTable)
            {
                $clause = 'ADD ' . $clause;
            }
            return $clause;
        }
    }
?>