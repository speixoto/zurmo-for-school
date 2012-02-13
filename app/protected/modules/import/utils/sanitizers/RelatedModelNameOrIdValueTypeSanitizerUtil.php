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
     * Sanitizer to handle attribute values that are possible a model name not just a model id.
     */
    class RelatedModelNameOrIdValueTypeSanitizerUtil extends ExternalSystemIdSuppportedSanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'RelatedModelNameOrIdValueType';
        }

        public static function getLinkedMappingRuleType()
        {
            return 'RelatedModelValueType';
        }

        /**
         * Given a value that is either a zurmo id or an external system id, resolve that the
         * value is valid.  The value presented can also be a 'name' value.  If the name is not found as a model
         * in the system, then a new related model will be created using this name.
         * NOTE - If the related model has other required attributes that have no default values,
         * then there will be a problem saving this new model. This is too be resolved at some point.
         * If the value is not valid then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName) && $attributeName != "id"');
            assert('$mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID ||
                    $mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME');
            if ($value == null)
            {
                return $value;
            }
            $model                   = new $modelClassName(false);
            $relationModelClassName = $model->getRelationModelClassName($attributeName);
            if ($mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)
            {
                try
                {
                    if ((int)$value <= 0)
                    {
                        throw new NotFoundException();
                    }
                    return $relationModelClassName::getById((int)$value);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The id specified did not match any existing records.'));
                }
            }
            elseif ($mappingRuleData["type"] == RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, $relationModelClassName);
                }
                catch (NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The other id specified did not match any existing records.'));
                }
            }
            else
            {
                if (!method_exists($relationModelClassName, 'getByName'))
                {
                    throw new NotSupportedException();
                }
                $modelsFound = $relationModelClassName::getByName($value);
                if (count($modelsFound) == 0)
                {
                    $newRelatedModel       = new $relationModelClassName();
                    $newRelatedModel->name = $value;
                    $saved = $newRelatedModel->save();
                    //Todo: need to handle this more gracefully. The use case where a related model is needed to be made
                    //but there are some required attributes that do not have defaults. As a result, since those extra
                    //defaults cannot be specified at this time, an error must be thrown.
                    if (!$saved)
                    {
                        throw new InvalidValueToSanitizeException(Yii::t('Default',
                        'A new related model could not be created because there are unspecified required attributes on that related model.'));
                    }
                    return $newRelatedModel;
                }
                else
                {
                    return $modelsFound[0];
                }
            }
        }
    }
?>