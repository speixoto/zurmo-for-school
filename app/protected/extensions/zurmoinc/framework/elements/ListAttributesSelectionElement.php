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
     * Element to render a set of checkboxes representing available list view attributes that can be selected when
     * running a search and viewing a list.
     */
    class ListAttributesSelectionElement extends Element
    {
        protected function renderControlEditable()
        {
            assert('$this->model instanceof SearchForm');
            assert('$this->attribute == null');
            assert('$this->model->getListAttributesSelector() != null');
            $content  = $this->renderSelectionContent();
            $content .= $this->renderApplyLinkContent();
            $content .= $this->renderApplyResetContent();
            $this->renderEditableScripts();
            return ZurmoHtml::tag('div', array('class' => 'list-view-attributes-selection'), $content);
        }

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return null;
        }

        protected function renderSelectionContent()
        {
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("ListAttributesSelectionMultiSelect");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.JuiMultiSelect', array(
                'dataAndLabels'  => $this->model->getListAttributesSelector()->getAvailableListAttributesNamesAndLabelsAndAll(),
                'selectedValue'  => $this->model->getListAttributesSelector()->getSelected(),
                'inputId'        => $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES),
                'inputName'      => $this->getEditableInputName(SearchForm::SELECTED_LIST_ATTRIBUTES),
                'options'        => array(
                                          'selectedText'     => '',
                                          'noneSelectedText' => '',
                                          'header'           => false
                                          ),
                'htmlOptions'    => array('class' => 'ignore-style ignore-clearform')
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ListAttributesSelectionMultiSelect'];
        }

        /**
         * On keyUp, the search should be conducted.
         */
        protected function renderEditableScripts()
        {
            $defaultSelectedAttributes = $this->model->getListAttributesSelector()->getMetadataDefinedListAttributeNames();
            Yii::app()->clientScript->registerScript('selectedListAttributesScripts', "
                $('#list-attributes-reset').unbind('click.reset');
                $('#list-attributes-reset').bind('click.reset', function()
                    {
                        resetSelectedListAttributes('" .
                            $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . "', " .
                            CJSON::encode($defaultSelectedAttributes) . ");
                    }
                );
                $('#" . $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . "').bind('multiselectclick', function(event, ui){
                    resolveLastSelectedListAttributesOption('" .
                        $this->getEditableInputId(SearchForm::SELECTED_LIST_ATTRIBUTES) . "')
                });
                ");
        }

        protected function renderApplyLinkContent()
        {
            $params = array();
            $params['label']       = Yii::t('Default', 'Apply');
            $params['htmlOptions'] = array('id'	     => 'list-attributes-apply',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }

        protected function renderApplyResetContent()
        {
            $params = array();
            $params['label']       = Yii::t('Default', 'Reset');
            $params['htmlOptions'] = array('id'	     => 'list-attributes-reset',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }

    }
?>