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
     * The DefaultValueModelAttributeMappingRuleForm needs to get its rules from the associated model based on
     * an attribute.  This utility provides that information.
     */
    class ModelAttributeRulesToWorkflowActionAttributeUtil
    {
        /**
         * @param string $modelClassName
         * @param string $attributeName
         * @param string $ruleAttributeName
         * @return array
         */
        public static function getApplicableRulesByModelClassNameAndAttributeName($modelClassName, $attributeName,
                                                                                  $ruleAttributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('is_string($ruleAttributeName)');
            $model    = new $modelClassName(false);
            assert('$model->isAttribute($attributeName)');
            $metadata = $modelClassName::getMetadata();
            assert('isset($metadata[$modelClassName])');
            $applicableRules = array();
            if ($attributeName == 'id')
            {
                return $applicableRules;
            }

            $modelAttributeClassName = $model->getAttributeModelClassName($attributeName);
            if (isset($metadata[$modelAttributeClassName]['rules']))
            {
                $i = 0;
                while ($i < count($metadata[$modelAttributeClassName]['rules']))
                {
                    $rule = $metadata[$modelAttributeClassName]['rules'][$i];
                    if ($rule[0] == $attributeName)
                    {
                        switch ($rule[1])
                        {
                            case 'numerical':
                                if (isset($rule['precision']))
                                {
                                    $rule[1] = 'RedBeanModelNumberValidator';
                                }
                                $rule[0] = $ruleAttributeName;
                                $applicableRules[] = $rule;
                                continue;
                            case 'length':
                                $rule[0] = $ruleAttributeName;
                                $applicableRules[] = $rule;
                                continue;
                        }
                    }
                    $i++;
                }
            }
            return $applicableRules;
        }
    }
?>