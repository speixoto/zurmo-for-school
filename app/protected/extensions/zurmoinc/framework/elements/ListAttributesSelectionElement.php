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
            $this->renderEditableScripts();
            return $content;
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
                                          'header'           => false,
                                          ),
                'htmlOptions'    => array('class' => 'ignore-style')
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ListAttributesSelectionMultiSelect'];
        }

        /**
         * On keyUp, the search should be conducted.
         */
        protected function renderEditableScripts()
        {
            $x = $this->model->getListAttributesSelector()->getMetadataDefinedListAttributeNames();
            //echo "<pre>";
            //print_r($x);
           // echo "</pre>";
            return;
            //todo:
            $inputId = $this->getEditableInputId();
            $script   = " basicSearchQueued = 0;";
            $script  .= " basicSearchOldValue = '';";
            $script  .= "   var basicSearchHandler = function(event)
                            {
                                if ($(this).val() != '')
                                {
                                    if (basicSearchOldValue != $(this).val())
                                    {
                                        basicSearchOldValue = $(this).val();
                                        basicSearchQueued = basicSearchQueued  + 1;
                                        setTimeout('basicSearchQueued = basicSearchQueued - 1', 900);
                                        setTimeout('searchByQueuedSearch(\"" . $inputId . "\")', 1000);
                                    }
                                }
                            }
                            $('#" . $inputId . "').unbind('input.ajax propertychange.ajax keyup.ajax');
                            $('#" . $inputId . "').bind('input.ajax propertychange.ajax keyup.ajax', basicSearchHandler);
                            ";
            Yii::app()->clientScript->registerScript('basicSearchAjaxSubmit', $script);
        }
    }
?>