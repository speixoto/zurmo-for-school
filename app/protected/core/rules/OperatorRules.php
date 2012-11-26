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
     * Rules for working with operators that can be used for triggers in workflows or filters in reporting.
     */
    class OperatorRules
    {
        const TYPE_EQUALS                         = 'Equals';

        const TYPE_DOES_NOT_EQUAL                 = 'Does Not Equal';

        const TYPE_STARTS_WITH                    = 'Starts With';

        const TYPE_ENDS_WITH                      = 'Ends With';

        const TYPE_CONTAINS                       = 'Contains';

        const TYPE_GREATER_THAN_OR_EQUAL_TO       = 'Greater Than Or Equal To';

        const TYPE_LESS_THAN_OR_EQUAL_TO          = 'Less Than Or Equal To';

        const TYPE_GREATER_THAN                   = 'Greater Than';

        const TYPE_LESS_THAN                      = 'Less Than';

        const TYPE_ONE_OF                         = 'One Of';

        const TYPE_BETWEEN                        = 'Between';

        const TYPE_IS_NULL                        = 'Is Null';

        const TYPE_IS_NOT_NULL                    = 'Is Not Null';

        public static function getTranslatedTypeLabel($type)
        {
            assert('is_string($type)');
            $labels             = self::translatedTypeLabels();
            if(isset($labels[$type]))
            {
                return $labels[$type];
            }
            throw new NotSupportedException();
        }

        public static function translatedTypeLabels()
        {
            return array(OperatorRules::TYPE_EQUALS                      => Yii::t('Default', 'Equals'),
                         OperatorRules::TYPE_DOES_NOT_EQUAL              => Yii::t('Default', 'Does Not Equal'),
                         OperatorRules::TYPE_STARTS_WITH                 => Yii::t('Default', 'Starts With'),
                         OperatorRules::TYPE_ENDS_WITH                   => Yii::t('Default', 'Ends With'),
                         OperatorRules::TYPE_CONTAINS                    => Yii::t('Default', 'Contains'),
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO    => Yii::t('Default', 'Greater Than Or Equal To'),
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO       => Yii::t('Default', 'Less Than Or Equal To'),
                         OperatorRules::TYPE_GREATER_THAN                => Yii::t('Default', 'Greater Than'),
                         OperatorRules::TYPE_LESS_THAN                   => Yii::t('Default', 'Less Than'),
                         OperatorRules::TYPE_ONE_OF                      => Yii::t('Default', 'One Of'),
                         OperatorRules::TYPE_BETWEEN                     => Yii::t('Default', 'Between'),
                         OperatorRules::TYPE_IS_NULL                     => Yii::t('Default', 'Is Null'),
                         OperatorRules::TYPE_IS_NOT_NULL                 => Yii::t('Default', 'Is Not Null'),
            );
        }

        public static function availableTypes()
        {
            return array(OperatorRules::TYPE_EQUALS,
                         OperatorRules::TYPE_DOES_NOT_EQUAL,
                         OperatorRules::TYPE_STARTS_WITH,
                         OperatorRules::TYPE_ENDS_WITH,
                         OperatorRules::TYPE_CONTAINS,
                         OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                         OperatorRules::TYPE_GREATER_THAN,
                         OperatorRules::TYPE_LESS_THAN,
                         OperatorRules::TYPE_ONE_OF,
                         OperatorRules::TYPE_BETWEEN,
                         OperatorRules::TYPE_IS_NULL,
                         OperatorRules::TYPE_IS_NOT_NULL,
            );
        }
    }
?>