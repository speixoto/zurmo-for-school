<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Sanitizer for attributes that are models, and handling the values that represent the ids of those models.
     * If you are importing a related account on a contact, this would be used for the account id, not the contact id.
     * To sanitize for the contact id in this example, you would use  @see SelfIdValueTypeSanitizerUtil
     *
     * Data analyzer for columns mapped to attributes that are either ids or relation ids.  For importing ids, there
     * are several approved value types including a zurmo model id as well as an external system id that can be used to
     * maintain key integerity during the entirety of a data import.
     */
    abstract class IdValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        /**
         * Identifies the type of value provided. IdValueTypeMappingRuleForm::ZURMO_MODEL_ID or
         * IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID
         * @var integer
         */
        protected $type;

        /**
         * The attribute is expected to be a relation. This is the model class name for that relation.
         * @var string
         */
        protected $attributeModelClassName;

        /**
         * Max allowed length of a value when the type of value is IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID
         * @var integer
         */
        protected $externalSystemIdMaxLength = 40;

        public static function getLinkedMappingRuleType()
        {
            return 'IdValueType';
        }

        /**
         * If a model id value is invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if ($this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                $found = $this->resolveFoundIdByValue($rowBean->{$this->columnName});
            }
            else
            {
                $found = $this->resolveFoundExternalSystemIdByValue($rowBean->{$this->columnName});
            }
            if ($found)
            {
                $this->resolveForFoundModel();
            }
            else
            {
                $this->resolveForUnfoundModel();
            }
            if ($this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                if (strlen($rowBean->{$this->columnName}) > $this->externalSystemIdMaxLength)
                {
                    $label = Zurmo::t('ImportModule', '{columnName} is  too long.',
                                      array('{columnName}' => $this->columnName));
                    $this->shouldSkipRow      = true;
                    $this->analysisMessages[] = $label;
                }
            }
        }

        protected function resolveForFoundModel()
        {
            $label = Zurmo::t('ImportModule', '{columnName} is an existing record and will be updated.',
                              array('{columnName}' => $this->columnName));
            $this->analysisMessages[] = $label;
        }

        protected function resolveForUnfoundModel()
        {
            $label = Zurmo::t('ImportModule', '{columnName} was not found and this row will be skipped during import.',
                              array('{columnName}' => $this->columnName));
            $this->shouldSkipRow      = true;
            $this->analysisMessages[] = $label;
        }

        /**
         * Ensure the type is an accepted type.
         * @param unknown_type integer
         */
        protected function ensureTypeValueIsValid($type)
        {
            assert('$type == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $type == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
        }

        /**
         * Given a model and an attribute, return the model class name for the attribute.
         * @param RedBeanModel $model
         * @param string $attributeName
         * @return string $attributeModelClassName
         */
        protected function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getRelationModelClassName($attributeName);
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         * @return boolean
         */
        protected function resolveFoundIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            elseif (is_int($value))
            {
                $sqlReadyString = $value;
            }
            else
            {
                $sqlReadyString = '\'' . $value . '\'';
            }
            $modelClassName = $this->attributeModelClassName;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
                ' where id = ' . $sqlReadyString . ' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        /**
         * Tries to find the value in the system. If found, returns true, otherwise false.
         * @param string $value
         * @return boolean
         */
        protected function resolveFoundExternalSystemIdByValue($value)
        {
            assert('is_int($value) || is_string($value) || $value == null');
            if ($value == null)
            {
                return false;
            }
            $modelClassName = $this->attributeModelClassName;
            $columnName     = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            $sql = 'select id from ' . $modelClassName::getTableName($modelClassName) .
                ' where ' . $columnName . ' = \'' . $value . '\' limit 1';
            $ids =  R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                return false;
            }
            return true;
        }

        protected function assertMappingRuleDataIsValid()
        {
            assert('$this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
        }

        protected function init()
        {
            parent::init();
            $modelClassName                = $this->modelClassName;
            $model                         = new $modelClassName(false);
            $this->attributeModelClassName = $this->resolveAttributeModelClassName($model, $this->attributeName);
            $this->ensureTypeValueIsValid($this->mappingRuleData["type"]);
            if ($this->mappingRuleData["type"] == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                $modelClassName  = $this->attributeModelClassName;
                $tableColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
                RedBeanColumnTypeOptimizer::externalIdColumn($modelClassName::getTableName($modelClassName),
                                                             $tableColumnName,
                                                             $this->externalSystemIdMaxLength);
            }
        }
    }
?>