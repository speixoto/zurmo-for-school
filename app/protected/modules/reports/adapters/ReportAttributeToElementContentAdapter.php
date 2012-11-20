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

    class ReportAttributeToElementContentAdapter
    {
        protected $modelToReportAdapter;

        protected $inputPrefixData;

        protected $model;

        protected $form;

        protected $attribute;

        protected $label;

        public function __construct($modelToReportAdapter, Array $inputPrefixData, $model, $form, $attribute, $label)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('$model instanceof ReportWizardForm');
            assert('$form instanceof ZurmoActiveForm');
            assert('is_string($attribute)');
            assert('is_string($label)');
            $this->modelToReportAdapter = $modelToReportAdapter;
            $this->inputPrefixData      = $inputPrefixData;
            $this->model                = $model;
            $this->form                 = $form;
            $this->attribute            = $attribute;
            $this->label                = $label;
        }
/**
        public function getContent()
        {
            if($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_FILTERS)
            {
                return $modelToReportAdapter->getAttributesForFilters($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DISPLAY_ATTRIBUTES)
            {
                return $modelToReportAdapter->getAttributesForDisplayAttributes($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_ORDER_BYS)
            {
                return $modelToReportAdapter->getAttributesForOrderBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_GROUP_BYS)
            {
                return $modelToReportAdapter->getAttributesForGroupBys($precedingModel, $precedingRelation);
            }
            elseif($this->treeType == ReportRelationsAndAttributesTreeView::TREE_TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)
            {
                return $modelToReportAdapter->getForDrillDownAttributes($precedingModel, $precedingRelation);
            }
            else
            {
                throw new NotSupportedException();
            }
        }
**/
        public function getContent()
        {
            $params                    = array('inputPrefix' => $this->inputPrefixData);
            //$labelElement              = new Something($this->model, 'operator');
            $operatorElement           = $this->modelToReportAdapter->getOperatorElement($this->model, $this->form, $attribute);
            if($operatorElement != null)
            {
                $operatorElement->editableTemplate  = '{content}{error}';
                $operatorContent       = $operatorElement->render();
            }
            else
            {
                $operatorContent       = null;
            }
            $attributeElement = $this->modelToReportAdapter->getAttributeElement($this->model, $this->form, $attribute);
            if($attributeElement != null)
            {
                $attributeElement->editableTemplate = '{content}{error}';
                $attributeContent      = $attributeElement->render();
            }
            else
            {
                $attributeContent      = null;
            }
            $runTimeElement   = $this->modelToReportAdapter->getRunTimeElement($this->model, $this->form, $attribute);
            if($runTimeElement != null)
            {
                $runTimeElement->editableTemplate = '{content}{error}';
                $runTimeContent        = $runTimeElement->render();
            }
            else
            {
                $runTimeContent        = null;
            }
            $content                   = $this->renderAttributeIndexOrDerivedType();
            $content                   = ZurmoHtml::tag('div', array(), $content  . $this->label);
            //into some sort of label
            if($operatorContent != null)
            {
                $content              .= ZurmoHtml::tag('div', array(), $operatorContent);
            }
            if($attributeContent != null)
            {
                $content              .= ZurmoHtml::tag('div', array(), $attributeContent);
            }
            if($runTimeContent != null)
            {
                $content              .= ZurmoHtml::tag('div', array(), $runTimeContent);
            }
            return $content;
        }

        protected function renderAttributeIndexOrDerivedType()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->attribute, $idInputHtmlOptions);
        }

        /**
        protected function getContentForGroupBy()
        {

        }

        protected function getContentForOrderBy()
        {

        }

        protected function getContentForDisplayAttribute()
        {

        }

        protected function getContentForDrillDownAttribute()
        {

        }
        **/
    }
?>