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
     * Helper class for working with advanced search panel.
     */
    class DynamicSearchUtil
    {
        public static function getSearchableAttributesAndLabels($viewClassName, $modelClassName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );
            $attributeIndexOrDerivedTypeAndLabels = array();
            foreach($attributesLayoutAdapter->getPlaceableLayoutAttributes() as $attributeIndexOrDerivedType => $data)
            {
                //Special exception since anyMixedAttributes should not be available for dynamic search.
                //Eventually refactor and decouple at some point if needed.
                if($attributeIndexOrDerivedType != 'anyMixedAttributes' &&
                   $attributeIndexOrDerivedType != 'dynamicStructure' &&
                   $attributeIndexOrDerivedType != 'dynamicClauses')
                {
                    $attributeIndexOrDerivedTypeAndLabels[$attributeIndexOrDerivedType] = $data['attributeLabel'];
                }
            }
            return $attributeIndexOrDerivedTypeAndLabels;
        }

        public static function getCellElement($viewClassName, $modelClassName, $elementName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            assert('is_string($elementName)');
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );

            $derivedAttributes         = $attributesLayoutAdapter->getAvailableDerivedAttributeTypes();
            $placeableLayoutAttributes = $attributesLayoutAdapter->getPlaceableLayoutAttributes();
            if (in_array($elementName, $derivedAttributes))
            {
                $element = array('attributeName' => 'null', 'type' => $elementName); // Not Coding Standard
            }
            elseif (isset($placeableLayoutAttributes[$elementName]) &&
                   $placeableLayoutAttributes[$elementName]['elementType'] == 'DropDownDependency')
            {
                throw new NotSupportedException();
            }
            elseif (isset($placeableLayoutAttributes[$elementName]))
            {
                $element = array(
                    'attributeName' => $elementName,
                    'type'          => $placeableLayoutAttributes[$elementName]['elementType']
                );
            }
            else
            {
                throw new NotSupportedException();
            }
            return $designerRules->formatSavableElement($element, $viewClassName);
        }
    }
?>