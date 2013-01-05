<?php
/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class VariableStatesForReportUtil
    {
        public static function resolveAttributeIndexesByComponents(array & $attributeIndexes, Array $componentForms)
        {
            foreach($componentForms as $componentForm)
            {
                $attributeIndexesToResolve = self::resolveAttributeIndexesByComponent($componentForm);
                self::resolveIndexesTogether($attributeIndexes, $attributeIndexesToResolve);
            }
        }

        /**
         * Public for testing purposes only
         */
        public static function resolveIndexesTogether(array & $attributeIndexes, array $attributeIndexesToResolve)
        {
            foreach($attributeIndexesToResolve as $key => $indexes)
            {
                if(!isset($attributeIndexes[$key]))
                {
                    $attributeIndexes[$key]= $indexes;
                }
            }
        }

        public static function resolveAttributeIndexes($modelClassName, & $attributeIndexes, $attributeIndexPrefix = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeIndexPrefix) || $attributeIndexPrefix == null');
            $moduleClassName = $modelClassName::getModuleClassName();
            if(null != $stateMetadataAdapterClassName = $moduleClassName::getStateMetadataAdapterClassName())
            {
                $reportRules  = ReportRules::makeByModuleClassName($moduleClassName);
                $stateAdapterClassName =  $reportRules->resolveStateAdapterUserHasAccessTo(Yii::app()->user->userModel);
                if($stateAdapterClassName !== null && $stateAdapterClassName !== false)
                {
                    $stateAttributeName = $stateAdapterClassName::getStateAttributeName();
                    $stateAdapter       = new $stateAdapterClassName(array());
                    $attributeIndexes[$attributeIndexPrefix] = array($stateAttributeName, $stateAdapter->getStateIds());
                }
                elseif($stateAdapterClassName === false)
                {
                    throw new PartialRightsForReportSecurityException();
                }
            }
        }

        /**
         * @see resolveAttributeIndexesByComponents
         * @param ComponentForReportForm $componentForm
         * @return array
         */
        protected static function resolveAttributeIndexesByComponent(ComponentForReportForm $componentForm)
        {
            $attributeIndexes                 = array();
            $modelClassName                   = $componentForm->getModelClassName();
            $moduleClassName                  = $componentForm->getModuleClassName();
            if(!$componentForm->hasRelatedData())
            {
                self::resolveAttributeIndexes($modelClassName, $attributeIndexes);
            }
            else
            {
                $attributeIndexPrefix = null;
                foreach($componentForm->attributeAndRelationData as $relationOrAttribute)
                {
                    self::resolveAttributeIndexes($modelClassName, $attributeIndexes, $attributeIndexPrefix);
                    $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                            make($moduleClassName, $modelClassName, $componentForm->getReportType());
                    if($modelToReportAdapter->isReportedOnAsARelation($relationOrAttribute))
                    {
                        $modelClassName       = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                        $moduleClassName      = $modelToReportAdapter->getRelationModuleClassName($relationOrAttribute);
                        $attributeIndexPrefix = $attributeIndexPrefix . $relationOrAttribute . FormModelUtil::RELATION_DELIMITER;
                    }
                }
            }
            return $attributeIndexes;
        }
    }
?>
