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

    class RedBeanColumnTypeOptimizer
    {
        public static $optimizedTableColumns;

       /**
        * Optimize table column types, based on hints
        * @param string  $table   name of the table
        * @param string  $columnName name of the column
        * @param string  $datatype
        */
        public static function optimize($table, $columnName, $datatype, $length = null)
        {
            try
            {
                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType($datatype, $length);

                if (isset(self::$optimizedTableColumns[$table]))
                {
                    $fields = self::$optimizedTableColumns[$table];
                    // It is possible that field is created outside optimizer, so in this case reload fields from database
                    if (!in_array($columnName, array_keys($fields)))
                    {
                        $fields = R::$writer->getColumns($table);
                    }
                }
                else
                {
                    $fields = R::$writer->getColumns($table);
                }

                if (in_array($columnName, array_keys($fields)))
                {
                    $columnType = $fields[$columnName];
                    if (strtolower($columnType) != strtolower($databaseColumnType))
                    {
                        if (strtolower($datatype) == 'string' && isset($length) && $length > 0)
                        {
                            $maxLength = R::getCell("SELECT MAX(LENGTH($columnName)) FROM $table");
                            if ($maxLength <= $length)
                            {
                                R::exec("alter table {$table} change {$columnName} {$columnName} " . $databaseColumnType);
                            }
                        }
                        else
                        {
                            R::exec("alter table {$table} change {$columnName} {$columnName} " . $databaseColumnType);
                        }
                    }
                }
                else
                {
                    R::exec("alter table {$table} add {$columnName} " . $databaseColumnType);
                }
            }
            catch (RedBean_Exception_SQL $e)
            {
                //42S02 - Table does not exist.
                if (!in_array($e->getSQLState(), array('42S02')))
                {
                    throw $e;
                }
                else
                {
                    R::$writer->createTable($table);
                    R::exec("alter table {$table} add {$columnName} " . $databaseColumnType);
                }
            }

            if (isset($fields))
            {
                self::$optimizedTableColumns[$table] = $fields;
            }
            else
            {
                self::$optimizedTableColumns[$table] = R::$writer->getColumns($table);
            }
            self::$optimizedTableColumns[$table][$columnName] = $databaseColumnType;
        }

        /**
         * Optimize fields that will accept ids from external sources - for example during imports
         * @param string $table
         * @param string $columnName
         */
        public static function externalIdColumn($table, $columnName)
        {
            self::optimize($table, $columnName, 'string');
            // To-Do: In future we can use $length to limit length of varchar type
        }
    }
?>