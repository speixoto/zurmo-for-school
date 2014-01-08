<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Sanitizer that is for attributes that are user models.
     */
    class UserValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        public static function getLinkedMappingRuleType()
        {
            return 'UserValueTypeModelAttribute';
        }

        public static function getUsernames()
        {
            $sql = 'select username from ' . User::getTableName();
            return ZurmoRedBean::getCol($sql);
        }

        public static function getUserIds()
        {
            $sql = 'select id from ' . User::getTableName();
            return ZurmoRedBean::getCol($sql);
        }

        public static function getUserExternalSystemIds()
        {
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            $userTableName = User::getTableName();
            ExternalSystemIdUtil::addExternalIdColumnIfMissing($userTableName);
            $sql = 'select ' . $columnName . ' from ' . $userTableName;
            return ZurmoRedBean::getCol($sql);
        }

        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if ($this->mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)
            {
                $compareValue = TextUtil::strToLowerWithDefaultEncoding($rowBean->{$this->columnName});
            }
            else
            {
                $compareValue = $rowBean->{$this->columnName};
            }
            if ($rowBean->{$this->columnName} != null && !in_array($compareValue, $this->getAcceptableValues()))
            {
                $label = Zurmo::t('ImportModule', 'Is an invalid user value. This value will be skipped during import.');
                $this->analysisMessages[] = $label;
            }
        }

        /**
         * Given a value that is either a zurmo user id, a username, or an external system user id, resolve that the
         * value is valid.  If the value is not valid then an InvalidValueToSanitizeException is thrown.
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            if ($value == null)
            {
                return $value;
            }
            if ($this->mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)
            {
                try
                {
                    if ((int)$value <= 0)
                    {
                        throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'User Id specified did not match any existing records.'));
                    }
                    return User::getById((int)$value);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'User Id specified did not match any existing records.'));
                }
            }
            elseif ($this->mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, 'User');
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'Other User Id specified did not match any existing records.'));
                }
            }
            else
            {
                try
                {
                    return User::getByUsername(strtolower($value));
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', 'Username specified did not match any existing records.'));
                }
            }
        }

        protected function getAcceptableValues()
        {
            if ($this->mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)
            {
                return UserValueTypeSanitizerUtil::getUserIds();
            }
            elseif ($this->mappingRuleData['type'] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)
            {
                return UserValueTypeSanitizerUtil::getUserExternalSystemIds();
            }
            else
            {
                $acceptableValues = UserValueTypeSanitizerUtil::getUsernames();
                return ArrayUtil::resolveArrayToLowerCase($acceptableValues);
            }
        }

        protected function assertMappingRuleDataIsValid()
        {
            assert('!isset($this->mappingRuleData["type"]) ||
                    $this->mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID ||
                    $this->mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID ||
                    $this->mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME');
        }
    }
?>