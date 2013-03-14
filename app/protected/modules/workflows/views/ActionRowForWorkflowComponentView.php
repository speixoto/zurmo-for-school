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
     * View for displaying a row of action information for a component
     */
    class ActionRowForWorkflowComponentView extends View
    {
        const REQUIRED_ATTRIBUTES_INDEX     = 'Required';

        const NON_REQUIRED_ATTRIBUTES_INDEX = 'NonRequired';

        protected $model;

        protected $rowNumber;

        protected $inputPrefixData;

        protected $form;

        public static function getFormId()
        {
            return WizardView::getFormId();
        }

        public function __construct(ActionForWorkflowForm $model, $rowNumber, $inputPrefixData, WizardActiveForm $form)
        {
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            $this->model            = $model;
            $this->rowNumber        = $rowNumber;
            $this->inputPrefixData  = $inputPrefixData;
            $this->form             = $form;
        }

        public function render()
        {
            return $this->renderContent();
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = '<div>';
            $content .= $this->renderActionRowNumberLabel();
            $toggleLink = ZurmoHtml::tag('span', array('class' => 'toggle-row'), '            _expand/collpase row link (todo)_'); //todo: make it work
            $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-row-label'), $this->model->type . $toggleLink); //todo: convert to label
            $content .= $this->renderTypeHiddenInputContent();
            $content .= $this->renderRelationHiddenInputContent();
            $content .= $this->renderRelatedModelRelationHiddenInputContent();
            $content .= '</div>';
            $content .= ZurmoHtml::link('—', '#', array('class' => 'remove-dynamic-row-link'));
            $content .= '<div>';
            $content .= $this->renderAttributesRowsContent($this->makeAttributeRows());
            $content .= '</div>';
            $content .= '<div>';
            $content .= $this->renderSaveActionElementsContent();
            $content .= '</div>';
            //todo: call correctly as action, fix theme? need to maybe refcator
            $content  =  ZurmoHtml::tag('div', array('class' => 'dynamic-row'), $content);
            return ZurmoHtml::tag('li', array(), $content);
        }

        /**
         * @return string
         */
        protected function renderActionRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'dynamic-row-number-label'),
                ($this->rowNumber + 1) . '.');
        }

        protected function renderTypeHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                                   array_merge($this->inputPrefixData, array('type')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                                   array_merge($this->inputPrefixData, array('type')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->type, $idInputHtmlOptions);
        }

        protected function renderRelationHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                array_merge($this->inputPrefixData, array('relation')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                array_merge($this->inputPrefixData, array('relation')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->relation, $idInputHtmlOptions);
        }

        protected function renderRelatedModelRelationHiddenInputContent()
        {
            $hiddenInputName     = Element::resolveInputNamePrefixIntoString(
                array_merge($this->inputPrefixData, array('relatedModelRelation')));
            $hiddenInputId       = Element::resolveInputIdPrefixIntoString(
                array_merge($this->inputPrefixData, array('relatedModelRelation')));
            $idInputHtmlOptions  = array('id' => $hiddenInputId);
            return ZurmoHtml::hiddenField($hiddenInputName, $this->model->relatedModelRelation, $idInputHtmlOptions);
        }

        protected function makeAttributeRows()
        {
            $attributeRows     = array(self::REQUIRED_ATTRIBUTES_INDEX     => array(),
                                       self::NON_REQUIRED_ATTRIBUTES_INDEX => array());
            $inputPrefixData   = $this->inputPrefixData;
            $inputPrefixData[] = ActionForWorkflowForm::ACTION_ATTRIBUTES;
            foreach($this->model->resolveAllRequiredActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttributeForm)
            {
                $elementAdapter  = new WorkflowActionAttributeToElementAdapter($actionAttributeForm, $this->form,
                                   $this->model->type, array_merge($inputPrefixData, array($attribute)), true);
                $attributeRows[self::REQUIRED_ATTRIBUTES_INDEX][] = $elementAdapter->getContent();
            }
            foreach($this->model->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort() as $attribute => $actionAttributeForm)
            {
                $elementAdapter  = new WorkflowActionAttributeToElementAdapter($actionAttributeForm, $this->form,
                                   $this->model->type, array_merge($inputPrefixData, array($attribute)), false);
                $attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX][] = $elementAdapter->getContent();
            }
            return $attributeRows;
        }

        protected function renderAttributesRowsContent($attributeRows)
        {
            assert('is_array($attributeRows)');
            $content = ZurmoHtml::tag('h3', array(), Zurmo::t('WorkflowModule', 'Required Fields'));
            foreach($attributeRows[self::REQUIRED_ATTRIBUTES_INDEX] as $attributeContent)
            {
                $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-sub-row'), $attributeContent);
            }
            $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('WorkflowModule', 'Other Fields'));
            foreach($attributeRows[self::NON_REQUIRED_ATTRIBUTES_INDEX] as $attributeContent)
            {
                $content .= ZurmoHtml::tag('div', array('class' => 'dynamic-sub-row'), $attributeContent);
            }
            return $content;
        }

        protected function renderSaveActionElementsContent()
        {
            $params                = array();
            $params['label']       = Zurmo::t('Core', 'Save');
            $params['htmlOptions'] = array('id' => 'saveAction'. $this->rowNumber,
                                     'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }
    }
?>