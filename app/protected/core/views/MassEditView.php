<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * The base View for a module's mass edit view.
     */
    abstract class MassEditView extends MassActionView
    {
        protected static function getFormId()
        {
            return 'edit-form';
        }

        protected function renderAlertMessage()
        {
            if (!empty($this->alertMessage))
            {
                JNotify::addMessage('FlashMessageBar', $this->alertMessage, 'MassEditAlertMessage');
            }
        }

        protected function renderPreActionElementBar($form)
        {
            return  $this->renderFormLayout($form) . $this->renderAfterFormLayout($form);
        }

        protected function renderOperationHighlight()
        {
            return null;
        }

        protected function renderItemOperationType()
        {
            return 'updating';
        }

        protected function renderItemLabel()
        {
            return LabelUtil::getUncapitalizedRecordLabelByCount($this->selectedRecordCount);
        }

        /**
         * Render a form layout.
         *  Gets appropriate meta data and loops through it. Builds form content
         *  as it loops through. For each element in the form it calls the appropriate
         *  Element class.
         *   @param $form If the layout is editable, then pass a $form otherwise it can
         *  be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout($form = null)
        {
            $metadata = self::getMetadata();
            $massEditScript = '';
            $content = '<table class="form-fields">';
            $content .= '<colgroup>';
            $content .= '<col class="col-checkbox" style="width:36px"/><col style="width:20%" /><col/>';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            //loop through each panel
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                $elementClassName = $elementInformation['type'] . 'Element';
                                $realAttributeName = $this->resolveRealAttributeName($elementClassName, $elementInformation['attributeName']);
                                if ($realAttributeName != null)
                                {
                                    $content .= '<tr>';
                                    $params = array_slice($elementInformation, 2);
                                    if (empty($this->activeAttributes[$elementInformation['attributeName']]))
                                    {
                                        $params['disabled'] = true;
                                        //The reason for adding the following loop is that in case of mass edit
                                        //calendar need not be disabled but hidden as it is controlled using checkbox click
                                        if ($elementInformation['type'] == 'DateTime' || $elementInformation['type'] == 'Date')
                                        {
                                            $params['datePickerDisabled'] = false;
                                        }
                                        $checked = false;
                                    }
                                    else
                                    {
                                        $checked = true;
                                    }
                                    $element  = new $elementClassName($this->model, $elementInformation['attributeName'], $form, $params);
                                    $content .= $this->renderActiveAttributesCheckBox($element->getEditableNameIds(),
                                                $elementInformation, $checked, $realAttributeName);
                                    $content .= $element->render();
                                    $content .= '</tr>';
                                }
                            }
                        }
                    }
                }
            }
            $content .= '</tbody>';
            $content .= '</table>';
            $this->renderDateTimeScript();
            return $content;
        }

        protected function renderActiveAttributesCheckBox($elementIds, $elementInformation, $checked, $realAttributeName)
        {
            $checkBoxHtmlOptions         = array();
            if ($elementInformation['type'] == 'DateTime' || $elementInformation['type'] == 'Date')
            {
                $checkBoxHtmlOptions['class'] = 'dateOrDateTime';
            }
            $checkBoxHtmlOptions['id']   = "MassEdit_" . $realAttributeName;
            $enableInputsScript          = "";
            $disableInputsScript         = "";
            foreach ($elementIds as $id)
            {
                if ($elementInformation['type'] == 'DropDown' || $elementInformation['type'] == 'RadioDropDown')
                {
                    $enableInputsScript   .= "$('#" . $id . "').removeAttr('disabled'); \n";
                    $enableInputsScript   .= "$('#" . $id . "').prev().removeClass('disabled-select-element'); \n";
                    $disableInputsScript  .= "$('#" . $id . "').attr('disabled', 'disabled'); \n";
                    $disableInputsScript  .= "$('#" . $id . "').prev().addClass('disabled-select-element'); \n";
                }
                elseif ($elementInformation['type'] == 'TagCloud')
                {
                    $enableInputsScript  .= "$('#token-input-" . $id . "').parent().parent().removeClass('disabled'); \n";
                    $disableInputsScript .= "$('#token-input-" . $id . "').parent().parent().addClass('disabled'); \n";
                }
                else
                {
                    $enableInputsScript .= "$('#" . $id . "').removeAttr('disabled'); \n";
                    $enableInputsScript .= "if ($('#" . $id . "').attr('type') != 'button')
                    {
                        if ($('#" . $id . "').attr('href') != undefined)
                        {
                            $('#" . $id . "').css('display', '');
                        }
                    }; \n";
                    $disableInputsScript .= "$('#" . $id . "').attr('disabled', 'disabled'); \n";
                    $disableInputsScript .= "if ($('#" . $id . "').attr('type') != 'button')
                    {
                        if ($('#" . $id . "').attr('href') != undefined)
                        {
                            $('#" . $id . "').css('display', 'none');
                        }
                        $('#" . $id . "').val('');
                    }; \n";
                }
            }
            $massEditScript = <<<END
$('#{$checkBoxHtmlOptions['id']}').click(function()
    {
        if (this.checked)
        {
            $enableInputsScript
        }
        else
        {
            $disableInputsScript
        }
    }
);
END;
            Yii::app()->clientScript->registerScript($checkBoxHtmlOptions['id'], $massEditScript);
            return "<th>" . ZurmoHtml::checkBox("MassEdit[" . $realAttributeName . "]", $checked, $checkBoxHtmlOptions) ."</th>  \n";
        }

        public static function getDesignerRulesType()
        {
            return 'MassEditView';
        }

        /**
         * If the attributeName is 'null', then ascertain if this is a derived attribute and get the real attribute.
         * If it is a derived attribute but has more than one model attribute name, then send back null since this is
         * not valid for a mass update
         * @param string $elementClassName
         * @param null|string $attributeName
         * @return mixed
         */
        protected function resolveRealAttributeName($elementClassName, $attributeName)
        {
            assert('is_string($elementClassName)');
            if ($attributeName == 'null')
            {
                $classToEvaluate        = new ReflectionClass($elementClassName);
                if ($classToEvaluate->implementsInterface('DerivedElementInterface'))
                {
                    $modelAttributeNames = $elementClassName::getModelAttributeNames();
                    if (count($modelAttributeNames) == 0 || count($modelAttributeNames) > 1)
                    {
                        return null;
                    }
                    return $modelAttributeNames[0];
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return $attributeName;
            }
        }

        /**
         * Register script for date and datetime element
         */
        protected function renderDateTimeScript()
        {
            $script = "
                        $('.dateOrDateTime').click(function()
                        {
                            if ($(this).is(':checked'))
                            {
                                $(this).parent().parent().parent().find('.ui-datepicker-trigger').show();
                            }
                            else
                            {
                                $(this).parent().parent().parent().find('.ui-datepicker-trigger').hide();
                            }
                        });
                        $('.dateOrDateTime').each(
                            function(index)
                            {
                                if ($(this).is(':checked'))
                                {
                                    $(this).parent().parent().parent().find('.ui-datepicker-trigger').show();
                                }
                                else
                                {
                                    $(this).parent().parent().parent().find('.ui-datepicker-trigger').hide();
                                }
                            }
                        );
                      ";
            Yii::app()->clientScript->registerScript('datetimescript', $script);
        }
    }
?>