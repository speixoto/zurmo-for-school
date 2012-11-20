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

    abstract class ComponentForReportForm extends ConfigurableMetadataModel
    {
        const     DISPLAY_LABEL_RELATION_DIVIDER = '>>';

        protected $modelClassName;

        protected $attributeAndRelationData;

        protected $reportType;

        private   $attribute;

        private   $_attributeIndexOrDerivedType;

        public function __set($name, $value)
        {
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->_attributeIndexOrDerivedType = $value;
                $this->resolveAttributeOrRelationAndAttributeDataByIndexType($value);
            }
            else
            {
                parent::__set($name, $value);
            }
        }

        public function rules()
        {
            return array();
        }

        public function attributeLabels()
        {
            return array();
        }

        public function __construct($modelClassName, $reportType)
        {
            assert('is_string($modelClassName)');
            assert(is_string($reportType));
            $this->modelClassName = $modelClassName;
            $this->reportType     = $reportType;
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        public function getAttributeIndexOrDerivedType()
        {
            return $this->_attributeIndexOrDerivedType;
        }

        public function getAttributeAndRelationData()
        {
            if($this->attributeAndRelationData == null)
            {
                return $this->attribute;
            }
            return $this->attributeAndRelationData;
        }

        public function hasRelatedData()
        {
            if($this->attribute != null)
            {
                return false;
            }
            return true;
        }

        public function getResolvedAttribute()
        {
            if($this->attribute != null)
            {
                return $this->attribute;
            }
            return $this->resolveAttributeFromData($this->attributeAndRelationData);
        }

        public function getResolvedAttributeModelClassName()
        {
            if($this->attribute != null)
            {
                return $this->modelClassName;
            }
            return $this->resolveAttributeModelClassNameFromData($this->attributeAndRelationData, $this->modelClassName);
        }

        public function getPenultimateModelClassName()
        {
            if($this->attribute != null)
            {
                throw new NotSupportedException();
            }
            return $this->resolvePenultimateModelClassNameFromData($this->attributeAndRelationData, $this->modelClassName);
        }

        public function getPenultimateRelation()
        {
            if($this->attribute != null)
            {
                throw new NotSupportedException();
            }
            return $this->resolvePenultimateRelationFromData($this->attributeAndRelationData);
        }

        public function getDisplayLabel()
        {
            $modelClassName       = $this->modelClassName;
            if($this->attribute != null)
            {
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($modelClassName::getModuleClassName(), $modelClassName, $this->reportType);
                return $modelToReportAdapter->getAttributeLabel($this->attribute);
            }
            else
            {
                $content = null;
                foreach($this->attributeAndRelationData as $relationOrAttribute)
                {
                    if($content != null)
                    {
                        $content .= ' ' . self::DISPLAY_LABEL_RELATION_DIVIDER . ' ';
                    }
                    $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                            make($modelClassName::getModuleClassName(), $modelClassName, $this->reportType);
                    if($modelToReportAdapter->isRelation($relationOrAttribute))
                    {
                        $modelClassName   = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                        $relationsData    = $modelToReportAdapter->getSelectableRelationsData();
                        if($modelToReportAdapter->isRelationASingularRelation($relationOrAttribute))
                        {
                            $content         .= $modelClassName::getModelLabelByTypeAndLanguage('Singular');
                        }
                        else
                        {
                            $content         .= $modelClassName::getModelLabelByTypeAndLanguage('Plural');
                        }
                    }
                    else
                    {
                        $content   .= $modelToReportAdapter->getAttributeLabel($relationOrAttribute);
                    }
                }
            }
            return $content;
        }

        /**
         * Passing in attributeIndexOrDerivedType, return an array representing the attribute and relation data or
         * if there is just a single attribute, then return a string representing the attribute
         * @param string $indexType
         * @return string or array
         */
        protected function resolveAttributeOrRelationAndAttributeDataByIndexType($indexType)
        {
            $attributeOrRelationAndAttributeData    = explode(FormModelUtil::RELATION_DELIMITER, $indexType);
            if(count($attributeOrRelationAndAttributeData) == 1)
            {
                $attributeOrRelationAndAttributeData = $attributeOrRelationAndAttributeData[0];
            }
            $this->setAttributeAndRelationData($attributeOrRelationAndAttributeData);
        }

        protected function resolveAttributeFromData(Array $attributeAndRelationData)
        {
            assert(count($attributeAndRelationData) > 0);
            return end($attributeAndRelationData);
        }

        protected function resolveAttributeModelClassNameFromData(Array $attributeAndRelationData, $modelClassName)
        {
            assert(count($attributeAndRelationData) > 0);
            foreach($attributeAndRelationData as $relationOrAttribute)
            {
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($modelClassName::getModuleClassName(), $modelClassName, $this->reportType);
                if($modelToReportAdapter->isRelation($relationOrAttribute))
                {
                    $modelClassName   = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                }
            }
            return $modelClassName;
        }

        protected function resolvePenultimateModelClassNameFromData(Array $attributeAndRelationData, $modelClassName)
        {
            assert(count($attributeAndRelationData) > 0);
            array_pop($attributeAndRelationData);
            foreach($attributeAndRelationData as $relationOrAttribute)
            {
                $lastModelClassName = $modelClassName;
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                                        make($modelClassName::getModuleClassName(), $modelClassName, $this->reportType);
                if($modelToReportAdapter->isRelation($relationOrAttribute))
                {
                    $modelClassName     = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                }
            }
            return $lastModelClassName;
        }

        protected function resolvePenultimateRelationFromData(Array $attributeAndRelationData)
        {
            assert(count($attributeAndRelationData) > 0);
            array_pop($attributeAndRelationData);
            return array_pop($attributeAndRelationData);
        }

        private function setAttributeAndRelationData($attributeOrRelationAndAttributeData)
        {
            assert('is_string($attributeOrRelationAndAttributeData) || is_array($attributeOrRelationAndAttributeData)');
            if(!is_array($attributeOrRelationAndAttributeData))
            {
                $this->attribute                = $attributeOrRelationAndAttributeData;
            }
            else
            {
                $this->attributeAndRelationData = $attributeOrRelationAndAttributeData;
            }
        }
    }
?>