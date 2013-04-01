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
     * Given a model and its attribute, find the appropriate operator type.  Override to handle boolean which has
     * an operator for workflow but does not for reporting for example.
     */
    class ModelAttributeToWorkflowOperatorTypeUtil extends ModelAttributeToOperatorTypeUtil
    {
        const AVAILABLE_OPERATORS_TYPE_BOOLEAN        = 'Boolean';

        const AVAILABLE_OPERATORS_TYPE_CURRENCY_VALUE = 'CurrencyValue';

        /**
         * @param array $data
         * @param string $type
         */
        public static function resolveOperatorsToIncludeByType(& $data, $type)
        {
            assert('is_array($data)');
            assert('is_string($type)');
            if($type == self::AVAILABLE_OPERATORS_TYPE_BOOLEAN)
            {
                $data[OperatorRules::TYPE_EQUALS] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_EQUALS);
                return;
            }
            if($type == self::AVAILABLE_OPERATORS_TYPE_CURRENCY_VALUE)
            {
                $data[OperatorRules::TYPE_EQUALS] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_EQUALS);
                $data[OperatorRules::TYPE_DOES_NOT_EQUAL] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_DOES_NOT_EQUAL);
                $data[OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO);
                $data[OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO);
                $data[OperatorRules::TYPE_GREATER_THAN] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_GREATER_THAN);
                $data[OperatorRules::TYPE_LESS_THAN] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_LESS_THAN);
                $data[OperatorRules::TYPE_BETWEEN] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_BETWEEN);
                return;
            }
            parent::resolveOperatorsToIncludeByType($data, $type);
        }

        /**
         * @param array $data
         * @param string $type
         */
        protected static function resolveIsNullAndIsNotNullOperatorsToInclude(& $data, $type)
        {
            assert('is_array($data)');
            assert('is_string($type)');
            if($type != self::AVAILABLE_OPERATORS_TYPE_DROPDOWN && $type != self::AVAILABLE_OPERATORS_TYPE_HAS_ONE)
            {
                $data[OperatorRules::TYPE_IS_NULL] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NULL);
                $data[OperatorRules::TYPE_IS_NOT_NULL] =
                    OperatorRules::getTranslatedTypeLabel(OperatorRules::TYPE_IS_NOT_NULL);
            }
        }

        /**
         * @param $model
         * @param string $attributeName
         * @return string
         */
        public static function getAvailableOperatorsType($model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($model->$attributeName instanceof CurrencyValue)
            {
                return self::AVAILABLE_OPERATORS_TYPE_CURRENCY_VALUE;
            }
            return parent::getAvailableOperatorsType($model, $attributeName);
        }

        /**
         * @return string
         */
        protected static function getAvailableOperatorsTypeForBoolean()
        {
            return static::AVAILABLE_OPERATORS_TYPE_BOOLEAN;
        }
    }
?>