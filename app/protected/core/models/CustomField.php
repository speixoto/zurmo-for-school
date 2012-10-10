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

    class CustomField extends BaseCustomField
    {
        public function __toString()
        {
            $s = strval($this->value);
            if ($s == '')
            {
                return Yii::t('Default', '(None)');
            }
            return $s;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'value',
                ),
                'relations' => array(
                ),
                'rules' => array(
                    array('value', 'type', 'type' => 'string'),
                ),
                'defaultSortAttribute' => 'value'
            );
            return $metadata;
        }

        public static function updateValueByDataIdAndOldValueAndNewValue($customFieldDataId, $oldValue, $newValue)
        {
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $customFieldTableName          = RedBeanModel::getTableName('CustomField');
            $baseCustomFieldTableName      = RedBeanModel::getTableName('BaseCustomField');
            $baseCustomFieldJoinColumnName = $baseCustomFieldTableName . '_id';
            $valueAttributeColumnName      = 'value';
            $dataAttributeColumnName       = RedBeanModel::getForeignKeyName('BaseCustomField', 'data');
            $sql  = "update {$quote}{$customFieldTableName}{$quote}, {$quote}{$baseCustomFieldTableName}{$quote} ";
            $sql .= "set {$quote}{$valueAttributeColumnName}{$quote} = '{$newValue}' ";
            $sql .= "where {$quote}{$customFieldTableName}{$quote}.$baseCustomFieldJoinColumnName = "; // Not Coding Standard
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id ";
            $sql .= "AND {$quote}{$dataAttributeColumnName}{$quote} = $customFieldDataId ";
            $sql .= "AND {$quote}{$valueAttributeColumnName}{$quote} = '{$oldValue}'";
            R::exec($sql);
        }
    }
?>
