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

    class ReportAttributeToElementAdapter
    {
        protected $inputPrefixData;

        protected $model;

        protected $form;

        //protected $attribute;

        protected $treeType;

        protected $showAvailableRuntimeFilter = true;

        public function __construct(Array $inputPrefixData, $model, $form, $treeType)
        {
            assert('count($inputPrefixData) > 1');
            assert('$model instanceof ComponentForReportForm');
            assert('$form instanceof ReportActiveForm');
            assert('is_string($treeType)');
            $this->inputPrefixData      = $inputPrefixData;
            $this->model                = $model;
            $this->form                 = $form;
            $this->treeType             = $treeType;
        }

        public function getContent()
        {
            $this->form->setInputPrefixData($this->inputPrefixData);
            if($this->treeType == ComponentForReportForm::TYPE_FILTERS)
            {
                $content = $this->getContentForFilter();
            }
            elseif($this->treeType == ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES)
            {
                $content = $this->getContentForDisplayAttribute();
            }
            elseif($this->treeType == ComponentForReportForm::TYPE_ORDER_BYS)
            {
                $content = $this->getContentForOrderBy();
            }
            elseif($this->treeType == ComponentForReportForm::TYPE_GROUP_BYS)
            {
                $content = $this->getContentForGroupBy();
            }
            elseif($this->treeType == ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES)
            {
                $content = $this->getContentForDrillDownDisplayAttribute();
            }
            else
            {
                throw new NotSupportedException();
            }
            $this->form->clearInputPrefixData();
            return $content;
        }

        protected function renderAttributeIndexOrDerivedType()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                            array_merge($this->inputPrefixData, array('attributeIndexOrDerivedType')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->getAttributeIndexOrDerivedType(),
                                          $idInputHtmlOptions);
        }

        protected function getContentForFilter()
        {
            $params                                 = array('inputPrefix' => $this->inputPrefixData);
            if($this->model->hasAvailableOperatorsType())
            {
                $operatorElement                    = new OperatorStaticDropDownElement($this->model, 'operator', $this->form, $params);
                $operatorElement->editableTemplate  = '{content}{error}';
                $operatorContent                    = $operatorElement->render();
            }
            else
            {
                $operatorContent                    = null;
            }
            $valueElementType                       = $this->model->getValueElementType();
            if($valueElementType != null)
            {
                $valueElementClassName              = $valueElementType . 'Element';
                $valueElement                       = new $valueElementClassName($this->model, 'value', $this->form, $params);
                if($valueElement instanceof NameIdElement)
                {
                    $valueElement->setIdAttributeId('value');
                    $valueElement->setNameAttributeName('stringifiedModelForValue');
                }
                if($valueElement instanceof MixedNumberTypesElement)
                {
                    $valueElement->editableTemplate = '{content}{error}';
                }
                else
                {
                    $valueElement->editableTemplate = '<div class="first-value-area">{content}{error}</div>';
                }
                $valueContent                   = $valueElement->render();
            }
            else
            {
                throw new NotSupportedException();
            }
            $content                                = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-attribute-label');
            self::resolveDivWrapperForContent($operatorContent,                $content, 'dynamic-attribute-operator');
            self::resolveDivWrapperForContent($valueContent,                   $content, 'value-data');
            if($this->showAvailableRuntimeFilter)
            {
                $runTimeElement                         = new CheckBoxElement($this->model, 'availableAtRunTime',
                                                                    $this->form, $params);
                $runTimeElement->editableTemplate       = '{label}{content}{error}';
                $runTimeContent                         = $runTimeElement->render();
                self::resolveDivWrapperForContent($runTimeContent, $content, 'report-runtime-availability');
            }
            return $content;
        }

        protected function getContentForGroupBy()
        {
            if($this->model->getReportType() == Report::TYPE_ROWS_AND_COLUMNS)
            {
                throw new NotSupportedException();
            }
            elseif($this->model->getReportType() == Report::TYPE_MATRIX)
            {
                $params                               = array('inputPrefix' => $this->inputPrefixData);
                $groupByAxisElement                   = new GroupByAxisStaticDropDownElement($this->model, 'axis',
                                                                                             $this->form, $params);
                $groupByAxisElement->editableTemplate = '{content}{error}';
                $groupByAxisElement                   = $groupByAxisElement->render();
            }
            else
            {
                $groupByAxisElement                   = null;
            }
            $content                                  = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content);
            self::resolveDivWrapperForContent($groupByAxisElement,             $content);
            return $content;
        }

        protected function getContentForOrderBy()
        {
            if($this->model->getReportType() == Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
            $params                             = array('inputPrefix' => $this->inputPrefixData);
            $directionElement                   = new OrderByStaticDropDownElement($this->model, 'order', $this->form,
                                                                                   $params);
            $directionElement->editableTemplate = '{content}{error}';
            $directionElement                   = $directionElement->render();
            $content                            = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-attribute-label');
            self::resolveDivWrapperForContent($directionElement,               $content, 'dynamic-attribute-field');
            return $content;
        }

        protected function getContentForDisplayAttribute()
        {
            $params                                = array('inputPrefix' => $this->inputPrefixData);
            $displayLabelElement                   = new TextElement($this->model, 'label', $this->form, $params);
            $displayLabelElement->editableTemplate = '{content}{error}';
            $displayLabelElement                   = $displayLabelElement->render();
            $content                               = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-attribute-label');
            self::resolveDivWrapperForContent($displayLabelElement,            $content, 'dynamic-attribute-field');
            return $content;
        }

        protected function getContentForDrillDownDisplayAttribute()
        {
            if($this->model->getReportType() == Report::TYPE_ROWS_AND_COLUMNS ||
               $this->model->getReportType() == Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
            return $this->getContentForDisplayAttribute();
        }

        protected static function resolveDivWrapperForContent($innerContent, & $content, $class = null)
        {
            if($class != null)
            {
                $htmlOptions = array('class' => $class);
            }
            else
            {
                $htmlOptions = array();
            }
            if($innerContent != null)
            {
                $content .= ZurmoHtml::tag('div', $htmlOptions, $innerContent);
            }
        }
    }
?>