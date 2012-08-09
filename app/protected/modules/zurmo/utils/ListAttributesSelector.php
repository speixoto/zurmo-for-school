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

    class ListAttributesSelector
    {
        private $designerLayoutAttributes;

        private $selectedValues;

        private $layoutMetadataAdapter;

        private $viewClassName;

        public function __construct($viewClassName, $moduleClassName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($moduleClassName)');
            $modelClassName           = $moduleClassName::getPrimaryModelName();
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
            $this->layoutMetadataAdapter = new LayoutMetadataAdapter(
                $viewClassName,
                $moduleClassName,
                $editableMetadata,
                $designerRules,
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->designerLayoutAttributes = $attributesLayoutAdapter->makeDesignerLayoutAttributes();
            $this->viewClassName            = $viewClassName;
        }

        public function getUnselectedListAttributesNamesAndLabelsAndAll()
        {
            $selectedValues = $this->getSelected();
            $attributeNames = array();
            foreach($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if(!in_array($attributeName, $selectedValues))
                {
                    $attributeNames[$attributeName] = $data['attributeLabel'];
                }
            }
            return $attributeNames;
        }

        public function getSelectedListAttributesNamesAndLabelsAndAll()
        {
            $selectedValues = $this->getSelected();
            $attributeNames = array();
            foreach($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if(in_array($attributeName, $selectedValues))
                {
                    $attributeNames[$attributeName] = $data['attributeLabel'];
                }
            }
            return $attributeNames;
        }

        public function getSelected()
        {
            if($this->selectedValues != null)
            {
                return $this->selectedValues;
            }
            $attributeNames = array();
            foreach($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if(!$data['availableToSelect'])
                {
                    $attributeNames[] = $attributeName;
                }
            }
            return $attributeNames;
        }

        public function getMetadataDefinedListAttributeNames()
        {
            $attributeNames = array();
            foreach($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if(!$data['availableToSelect'])
                {
                    $attributeNames[] = $attributeName;
                }
            }
            return $attributeNames;
        }

        public function setSelected($values)
        {
            $this->selectedValues = $values;
        }

        public function getResolvedMetadata()
        {
            return $this->layoutMetadataAdapter->resolveMetadataFromSelectedListAttributes($this->viewClassName,
                                                                                           $this->getSelected());
        }
    }
?>