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
     * Helper class to provider SQL operators and validate
     * accurate usage of operator types
     */
    class SQLOperatorUtil
    {
        /**
         * Confirms usage of operator type is valid.
         * @return boolean;
         */
        public static function isValidOperatorTypeByValue($operatorType, $value)
        {
            if (is_string($value))
            {
                return in_array($operatorType, array('startsWith', 'endsWith', 'equals', 'doesNotEqual', 'contains',
                                                     'lessThan', 'greaterThan', 'greaterThanOrEqualTo',
                                                     'lessThanOrEqualTo'));
            }
            elseif (is_array($value))
            {
                return in_array($operatorType, array('oneOf'));
            }
            elseif ($value !== null)
            {
                return in_array($operatorType, array('greaterThan', 'lessThan', 'equals', 'doesNotEqual',
                                                     'greaterThanOrEqualTo', 'lessThanOrEqualTo'));
            }
            elseif ($value === null)
            {
                return in_array($operatorType, array('isNull', 'isNotNull', 'isEmpty', 'isNotEmpty'));
            }
            return false;
        }

        /**
         * Input an operator type and it returns an
         * equivalent SQL operator.
         * @return string
         */
        public static function getOperatorByType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator)
            {
                switch ($operatorType)
                {
                    case 'startsWith' :
                        return 'like';

                    case 'endsWith' :
                        return 'like';

                    case 'contains' :
                        return 'like';

                    case 'equals' :
                        return '=';

                    case 'doesNotEqual' :
                        return '!=';

                    case 'greaterThan' :
                        return '>';

                    case 'lessThan' :
                        return '<';

                    case 'greaterThanOrEqualTo' :
                        return '>=';

                    case 'lessThanOrEqualTo' :
                        return '<=';

                    default :
                        throw new NotSupportedException('Unsupported operator type: ' . $operatorType);
                }
            }
        }

        /**
         * @return string
         */
        public static function resolveValueLeftSideLikePartByOperatorType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator &&  in_array($operatorType, array('endsWith', 'contains')))
            {
                return '%';
            }
        }

        /**
         * @return string
         */
        public static function resolveValueRightSideLikePartByOperatorType($operatorType)
        {
            assert('is_string($operatorType)');
            $validOperator = true;
            if (YII_DEBUG)
            {
                $validOperator = SQLOperatorUtil::isValidOperatorType($operatorType);
            }
            if ($validOperator && in_array($operatorType, array('startsWith', 'contains')))
            {
                return '%';
            }
        }

        public static function resolveOperatorAndValueForOneOf($operatorType, $values, $ignoreStringToLower = false)
        {
            assert('$operatorType == "oneOf"');
            assert('is_array($values) && count($values) > 0');
            $inPart = null;
            foreach ($values as $theValue)
            {
                if ($inPart != null)
                {
                    $inPart .= ','; // Not Coding Standard
                }
                if (is_string($theValue))
                {
                    if ($ignoreStringToLower)
                    {
                        $inPart .= "'" . DatabaseCompatibilityUtil::escape($theValue) . "'";
                    }
                    else
                    {
                        $inPart .= "'" . DatabaseCompatibilityUtil::escape($theValue) . "'";
                    }
                }
                elseif (is_numeric($theValue))
                {
                    $inPart .= $theValue;
                }
                elseif (is_bool($theValue))
                {
                    if (!$theValue)
                    {
                        $theValue = 0;
                    }
                    $inPart .= $theValue;
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return 'IN(' . $inPart . ')';
        }

        public static function resolveOperatorAndValueForNullOrEmpty($operatorType)
        {
            assert('in_array($operatorType, array("isNull", "isNotNull", "isEmpty", "isNotEmpty"))');
            if ($operatorType == 'isNull')
            {
                return 'IS NULL'; // Not Coding Standard
            }
            elseif ($operatorType == 'isNotNull')
            {
                return 'IS NOT NULL'; // Not Coding Standard
            }
            elseif ($operatorType == 'isEmpty')
            {
                return "= ''";
            }
            else
            {
                return "!= ''";
            }
        }

        /**
         * @return boolean
         */
        protected static function isValidOperatorType($type)
        {
            if (in_array($type, array(
                'startsWith',
                'endsWith',
                'contains',
                'equals',
                'doesNotEqual',
                'greaterThanOrEqualTo',
                'lessThanOrEqualTo',
                'greaterThan',
                'lessThan',
                'oneOf',
                'isNull',
                'isNotNull',
                'isEmpty',
                'isNotEmpty')))
            {
                return true;
            }
            return false;
        }

        public static function doesOperatorTypeAllowNullValues($type)
        {
            assert('is_string($type)');
            if (in_array($type, array(
                'isNull',
                'isNotNull',
                'isEmpty',
                'isNotEmpty')))
            {
                return true;
            }
            return false;
        }
    }
?>