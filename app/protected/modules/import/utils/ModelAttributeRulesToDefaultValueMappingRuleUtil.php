<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * The DefaultValueModelAttributeMappingRuleForm needs to get its rules from the associated model based on
     * an attribute.  This utility provides that information.
     */
    class ModelAttributeRulesToDefaultValueMappingRuleUtil
    {
        public static function getApplicableRulesByModelClassNameAndAttributeName($modelClassName, $attributeName,
                                                                                  $ruleAttributeName,
                                                                                  $requiredRuleIsApplicable = false,
                                                                                  $treatDateTimeAsDate = false,
                                                                                  $readOnlyRuleIsApplicable = true)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('is_string($ruleAttributeName)');
            assert('is_bool($requiredRuleIsApplicable)');
            assert('$modelClassName::isAnAttribute($attributeName)');
            $metadata = $modelClassName::getMetadata();
            assert('isset($metadata[$modelClassName])');
            $applicableRules = array();
            if ($attributeName == 'id')
            {
                return $applicableRules;
            }
            $modelAttributeClassName = $modelClassName::getAttributeModelClassName($attributeName);
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
                            case 'type':
                                if ($rule['type'] == 'date' || $rule['type'] == 'datetime')
                                {
                                    if ($treatDateTimeAsDate)
                                    {
                                        $rule['type'] = 'date';
                                    }
                                    $rule[1] = 'TypeValidator';
                                }
                                $rule[0] = $ruleAttributeName;
                                $applicableRules[] = $rule;
                                continue;
                            case 'numerical':
                                if (isset($rule['precision']))
                                {
                                    $rule[1] = 'RedBeanModelNumberValidator';
                                }
                                $rule[0] = $ruleAttributeName;
                                $applicableRules[] = $rule;
                                continue;
                            case 'default':
                            case 'safe':
                                continue;
                            case 'required':
                               if ($requiredRuleIsApplicable)
                               {
                                   $rule[0] = $ruleAttributeName;
                                   $applicableRules[] = $rule;
                               }
                               continue;
                            case 'readOnly':
                               if ($readOnlyRuleIsApplicable)
                               {
                                   $rule[0] = $ruleAttributeName;
                                   $applicableRules[] = $rule;
                               }
                               continue;
                            case 'dateTimeDefault':
                                 //Ignore dateTimeDefault validator for this as it is not applicable to import
                                 //It would map to RedBeanModelDateTimeDefaultValueValidator and is unneeded
                                continue;
                            case 'probability':
                                //Ignore probability validator for this as it is not applicable to import
                                //It would map to RedBeanModelProbabilityValidator and is unneeded
                                continue;
                            case 'RedBeanModelCompareDateTimeValidator':
                                 //Ignore dateTimeDefault validator for this as it is not applicable to import
                                 //We can't control if the user is mapping both the dates that are part of this
                                continue;
                            default:
                               $rule[0] = $ruleAttributeName;
                               $applicableRules[] = $rule;
                        }
                    }
                    $i++;
                }
            }
            return $applicableRules;
        }
    }
?>