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
     * Base class for handling multi-select dropdowns.
     */
    class MultipleValuesCustomField extends BaseCustomField
    {
        public function __toString()
        {
            if ($this->values->count() == 0)
            {
                return Yii::t('Default', '(None)');
            }
            $s = null;

            $dataAndLabels = CustomFieldDataUtil::
                             getDataIndexedByDataAndTranslatedLabelsByLanguage($this->data, Yii::app()->language);
            $s             = null;
            foreach ($this->values as $customFieldValue)
            {
                if ($s != null)
                {
                    $s .= ', ';
                }
                $s .= ArrayUtil::getArrayValue($dataAndLabels, strval($customFieldValue));
            }
            return $s;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                ),
                'relations' => array(
                    'values' => array(RedBeanModel::HAS_MANY, 'CustomFieldValue', RedBeanModel::OWNED),
                ),
                'rules' => array(
                ),
            );
            return $metadata;
        }

        public static function updateValueByDataIdAndOldValueAndNewValue($customFieldDataId, $oldValue, $newValue)
        {
            $quote                         = DatabaseCompatibilityUtil::getQuote();
            $customFieldTableName          = RedBeanModel::getTableName('MultipleValuesCustomField');
            $baseCustomFieldTableName      = RedBeanModel::getTableName('BaseCustomField');
            $customFieldValueTableName     = RedBeanModel::getTableName('CustomFieldValue');
            $baseCustomFieldJoinColumnName = $baseCustomFieldTableName . '_id';
            $valueAttributeColumnName      = 'value';
            $dataAttributeColumnName       = RedBeanModel::getForeignKeyName('BaseCustomField', 'data');
            $sql  = "update {$quote}{$customFieldValueTableName}{$quote}, {$quote}{$customFieldTableName}{$quote}, ";
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote} ";
            $sql .= "set {$quote}{$customFieldValueTableName}{$quote}.{$valueAttributeColumnName} = '{$newValue}' ";
            $sql .= "where {$quote}{$customFieldTableName}{$quote}.$baseCustomFieldJoinColumnName = "; // Not Coding Standard
            $sql .= "{$quote}{$baseCustomFieldTableName}{$quote}.id ";
            $sql .= "AND {$quote}{$dataAttributeColumnName}{$quote} = $customFieldDataId ";
            $sql .= "AND {$quote}{$customFieldTableName}{$quote}.id = {$quote}{$customFieldValueTableName}{$quote}.{$customFieldTableName}_id ";
            $sql .= "AND {$quote}{$customFieldValueTableName}{$quote}.{$valueAttributeColumnName} = '{$oldValue}'";
            R::exec($sql);
        }

        public function setValues($values)
        {
            $customFieldValueObject = array();
            if (count($values) == 0)
            {
                $this->values->removeAll();
            }
            else
            {
                if ($this->values->count() > 0)
                {
                    foreach ($this->values as $customFieldValue)
                    {
                        if (!in_array($customFieldValue->value, $values))
                        {
                            $customFieldValueObject[] = $customFieldValue;
                        }
                        else
                        {
                            $key = array_search($customFieldValue->value, $values);
                            unset($values[$key]);
                        }
                    }
                    foreach ($customFieldValueObject as $customFieldValue)
                    {
                        $this->values->remove($customFieldValue);
                    }
                }

                foreach ($values as $value)
                {
                    $customFieldValue = new CustomFieldValue();
                    $customFieldValue->value = $value;
                    $this->values->add($customFieldValue);
                }
            }
        }

        /**
         * Given an array of data, create stringified content.  Method is extended to provide support for translating
         * the data into the correct language.
         * (non-PHPdoc)
         * @see RedBeanModel::stringifyOneToManyRelatedModelsValues()
         */
        public function stringifyOneToManyRelatedModelsValues($values)
        {
            assert('is_array($values)');
            $dataAndLabels = CustomFieldDataUtil::
                             getDataIndexedByDataAndTranslatedLabelsByLanguage($this->data, Yii::app()->language);
            foreach ($values as $key => $value)
            {
                if (ArrayUtil::getArrayValue($dataAndLabels, $value) != null)
                {
                    $values[$key] = ArrayUtil::getArrayValue($dataAndLabels, $value);
                }
            }
            return ArrayUtil::stringify($values);
        }
    }
?>
