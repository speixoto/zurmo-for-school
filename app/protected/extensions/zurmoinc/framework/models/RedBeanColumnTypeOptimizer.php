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
        /**
       * @param string  $type   name of the table
       * @param string  $column name of the column
       * @param mixed $value
       * @param integer $datatype  data type for field
       *
       * @return boolean
       *
       */
        public static function dateColumn($type, $column, $value, $datatype)
        {
            if (!self::matchesDate($value))
            {
                return true;
            }
            $fields = R::$writer->getColumns($type);
            if (!in_array($column, array_keys($fields)))
            {
                return false;
            }

            $typeInField = R::$writer->code($fields[$column]);
            if ($typeInField != "date")
            {
                if (self::matchesDate($value))
                {
                    $cnt = (int)R::getCell("select count(*) as n from {$type} where ".
                              "{$column} regexp '[0-9]{4}-[0-1][0-9]-[0-3][0-9]' " .
                              "OR {$column} IS null");
                    $total = (int)R::getCell("SELECT count(*) FROM ".$type);
                    if ($total===$cnt)
                    {
                        R::$writer->widenColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_SPECIAL_DATE);
                    }
                    return false;
                }
                return true;
            }
            else {
                return false;
            }
        }

        protected static function matchesDate($value)
        {
            $pattern = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$/";
            return (boolean) (preg_match($pattern, $value));
        }

        public static function dateTimeColumn($type, $column, $value, $datatype)
        {

            if (!self::matchesDateTime($value))
            {
                return true;
            }

            $fields = R::$writer->getColumns($type);

            if (!in_array($column, array_keys($fields)))
            {
                return false;
            }

            $typeInField = R::$writer->code($fields[$column]);

            if ($typeInField!="datetime")
            {
              if (self::matchesDateTime($value))
              {
                $cnt = (int)R::getCell("select count(*) as n from $type where
                      {$column} regexp '[0-9]{4}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]'
                      OR {$column} IS NULL");
                $total = (int)R::getCell("SELECT count(*) FROM ".$type);

                if ($total===$cnt)
                {
                  R::exec("ALTER TABLE ".$type." change ".$column." ".$column." datetime ");
                }
                return false;
              }
              return true;
            }
            else {
              return false;
            }
        }

        protected static function matchesDateTime($value)
        {
            $pattern = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/";
            return (boolean) (preg_match($pattern, $value));
        }

        public static function idColumn($type, $column, $datatype) {
            try
            {
                $fields = R::$writer->getColumns($type);
                if (in_array($column,array_keys($fields)))
                {
                    $columnType = $fields[$column];
                    if (R::$writer->code($columnType) != RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32)
                    {
                        R::$writer->widenColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
                    }
                }
                else
                {
                    R::$writer->addColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
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
                    R::$writer->createTable($type);
                    R::$writer->addColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_UINT32);
                }
            }
        }

      public static function blobColumn($type, $column, $datatype)
      {
          assert('$datatype == "blob" || $datatype == "longblob"');
            try
            {
                $columnNamesToTypes = R::$writer->getColumns($type);
                if (array_key_exists($column, $columnNamesToTypes))
                {
                    $columnType = $columnNamesToTypes[$column];
                    if ($columnType != $datatype)
                    {
                        R::exec("alter table {$type} change {$column} {$column} " . $datatype);
                    }
                }
                else
                {
                    R::exec("alter table {$type} add {$column} " . $datatype);
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
                    R::$writer->createTable($type);
                    R::exec("alter table {$type} add {$column} " . $datatype);
                }
            }
        }

      public static function booleanColumn($type, $column, $datatype)
      {
          try
          {
              $fields = R::$writer->getColumns($type);
              if (in_array($column,array_keys($fields)))
              {
                  $columnType = $fields[$column];
                  if ($columnType != RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL)
                  {
                      R::$writer->widenColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL);
                  }
              }
              else
              {
                  R::$writer->addColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL);
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
                  R::$writer->createTable($type);
                  R::$writer->addColumn($type, $column, RedBean_QueryWriter_MySQL::C_DATATYPE_BOOL);
              }
          }
      }

        public static function externalIdColumn($type, $column, $length = 40)
        {
            try
            {
                $fields = R::$writer->getColumns($type);
                if (array_key_exists($column, $fields))
                {
                    $columnType = $fields[$column];
                    if ($columnType != static::getExternalIdType($length))
                    {
                        R::exec("alter table {$type} change {$column} {$column} " . static::getExternalIdType($length));
                    }
                }
                else
                {
                    R::exec("alter table {$type} add {$column} " . static::getExternalIdType($length));
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
                    R::$writer->createTable($type);
                    R::exec("alter table {$type} add {$column} " . static::getExternalIdType($length));
                }
            }
        }

        protected static function getExternalIdType($length = 40)
        {
            assert('is_int($length)');
            return "varchar(" . $length . ") null";
        }
    }
?>