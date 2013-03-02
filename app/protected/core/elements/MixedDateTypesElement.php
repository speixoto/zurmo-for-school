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
     * Displays a date and dateTime filtering input.  Allows for picking a type of filter and sometimes depending on
     * the filter, entering a specific date value.
     */
    abstract class MixedDateTypesElement extends Element
    {
        abstract protected function getValueTypeEditableInputId();

        abstract protected function getValueFirstDateEditableInputId();

        abstract protected function getValueSecondDateEditableInputId();

        abstract protected function getValueTypeEditableInputName();

        abstract protected function getValueFirstDateEditableInputName();

        abstract protected function getValueSecondDateEditableInputName();

        abstract protected function getValueFirstDate();

        abstract protected function getValueSecondDate();

        abstract protected function getValueType();

        protected function renderEditable()
        {
            $data = array();
            $data['label']     = $this->renderLabel();
            $data['content']   = $this->renderEditableValueTypeContent() . $this->renderControlEditable();
            $data['error']     = $this->renderError();
            $data['colspan']   = $this->getColumnSpan();
            return $this->resolveContentTemplate($this->editableTemplate, $data);
        }

        /**
         * Render a date JUI widget
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $valueTypeId                        = $this->getValueTypeEditableInputId();
            $firstDateSpanAreaSuffix            = '-first-date-area';
            $secondDateSpanAreaSuffix           = '-second-date-area';
            $firstDateSpanAreaId                = $valueTypeId . $firstDateSpanAreaSuffix;
            $secondDateSpanAreaId               = $valueTypeId . $secondDateSpanAreaSuffix;
            $valueTypesRequiringFirstDateInput  = MixedDateTypesSearchFormAttributeMappingRules::
                                                  getValueTypesRequiringFirstDateInput();
            $valueTypesRequiringSecondDateInput = MixedDateTypesSearchFormAttributeMappingRules::
                                                  getValueTypesRequiringSecondDateInput();
            Yii::app()->clientScript->registerScript('mixedDateTypes', "
                $('.dateValueType').change( function()
                    {
                        arr  = " . CJSON::encode($valueTypesRequiringFirstDateInput) . ";
                        arr2 = " . CJSON::encode($valueTypesRequiringSecondDateInput) . ";
                        firstDateSpanAreaQualifier = '#' + $(this).attr('id') + '".$firstDateSpanAreaSuffix."';
                        secondDateSpanAreaQualifier = '#' + $(this).attr('id') + '".$secondDateSpanAreaSuffix."';
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                            $(firstDateSpanAreaQualifier).show();
                            $(firstDateSpanAreaQualifier).find('.hasDatepicker').prop('disabled', false);
                        }
                        else
                        {
                            $(firstDateSpanAreaQualifier).hide();
                            $(firstDateSpanAreaQualifier).find('.hasDatepicker').prop('disabled', true);
                        }
                        if ($.inArray($(this).val(), arr2) != -1)
                        {
                            $(secondDateSpanAreaQualifier).show();
                            $(secondDateSpanAreaQualifier).find('.hasDatepicker').prop('disabled', false);
                        }
                        else
                        {
                            $(secondDateSpanAreaQualifier).hide();
                            $(secondDateSpanAreaQualifier).find('.hasDatepicker').prop('disabled', true);
                        }
                    }
                );
            ");
            $startingDivStyleFirstDate   = null;
            $startingDivStyleSecondDate  = null;
            if (!in_array($this->getValueType(), $valueTypesRequiringFirstDateInput))
            {
                $startingDivStyleFirstDate = "display:none;";
            }
            if (!in_array($this->getValueType(), $valueTypesRequiringSecondDateInput))
            {
                $startingDivStyleSecondDate = "display:none;";
            }
            $content  = ZurmoHtml::tag('span', array('id'    => $firstDateSpanAreaId,
                                                     'class' => 'first-date-area',
                                                     'style' => $startingDivStyleFirstDate),
                                                     $this->renderEditableFirstDateContent());
            $content .= ZurmoHtml::tag('span', array('id'    => $secondDateSpanAreaId,
                                                     'class' => 'second-date-area',
                                                     'style' => $startingDivStyleSecondDate),
                                                     ZurmoHtml::Tag('span', array('class' => 'dynamic-and-for-mixed'), Zurmo::t('Core', 'and')) .
                                                     $this->renderEditableSecondDateContent());
            return $content;
        }

        protected function renderEditableValueTypeContent()
        {
            return       ZurmoHtml::dropDownList($this->getValueTypeEditableInputName(),
                                                 $this->getValueType(),
                                                 $this->getValueTypeDropDownArray(),
                                                 $this->getEditableValueTypeHtmlOptions());
        }

        protected function renderEditableFirstDateContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDateElement");
            $cClipWidget->widget('application.core.widgets.JuiDatePicker', array(
                'attribute'           => $this->attribute,
                'value'               => DateTimeUtil::resolveValueForDateLocaleFormattedDisplay(
                                         $this->getValueFirstDate()),
                'language'            => YiiToJqueryUIDatePickerLocalization::getLanguage(),
                'htmlOptions'         => array(
                    'id'              => $this->getValueFirstDateEditableInputId(),
                    'name'            => $this->getValueFirstDateEditableInputName(),
                ),
                'options'             => array(
                    'showOn'          => 'both',
                    'buttonText'      => ZurmoHtml::tag('span', array(), '<!--Date-->'),
                    'showButtonPanel' => true,
                    'buttonImageOnly' => false,
                    'dateFormat'      => YiiToJqueryUIDatePickerLocalization::resolveDateFormat(
                                            DateTimeUtil::getLocaleDateFormat()),
                ),
            ));
            $cClipWidget->endClip();
            $content =  $cClipWidget->getController()->clips['EditableDateElement'];
            return      ZurmoHtml::tag('div', array('class' => 'has-date-select'), $content);
        }

        protected function renderEditableSecondDateContent()
        {
            $themePath = Yii::app()->baseUrl . '/themes/' . Yii::app()->theme->name;
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("EditableDateElement");
            $cClipWidget->widget('application.core.widgets.JuiDatePicker', array(
                'attribute'           => $this->attribute,
                'value'               => DateTimeUtil::resolveValueForDateLocaleFormattedDisplay(
                                         $this->getValueSecondDate()),
                'language'            => YiiToJqueryUIDatePickerLocalization::getLanguage(),
                'htmlOptions'         => array(
                    'id'              => $this->getValueSecondDateEditableInputId(),
                    'name'            => $this->getValueSecondDateEditableInputName(),
                ),
                'options'             => array(
                    'showOn'          => 'both',
                    'buttonText'      => ZurmoHtml::tag('span', array(), '<!--Date-->'),
                    'showButtonPanel' => true,
                    'buttonImageOnly' => false,
                    'dateFormat'      => YiiToJqueryUIDatePickerLocalization::resolveDateFormat(
                                            DateTimeUtil::getLocaleDateFormat()),
                ),
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['EditableDateElement'];
            return ZurmoHtml::tag('div', array('class' => 'has-date-select'), $content);
        }

        protected function getEditableValueTypeHtmlOptions()
        {
            $htmlOptions = array(
                'id'    => $this->getValueTypeEditableInputId(),
                'class' => 'dateValueType',
            );
            $htmlOptions['empty']    = Zurmo::t('Core', '(None)');
            $htmlOptions['disabled'] = $this->getDisabledValue();
            return $htmlOptions;
        }

        protected function getValueTypeDropDownArray()
        {
            return MixedDateTimeTypesSearchFormAttributeMappingRules::getValueTypesAndLabels();
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderLabel()
        {
            $label = $this->getFormattedAttributeLabel();
            if ($this->form === null)
            {
                return $label;
            }
            return ZurmoHtml::label($label, false);
        }

        /**
         * Render during the Editable render
         * (non-PHPdoc)
         * @see Element::renderError()
         */
        protected function renderError()
        {
        }
    }
?>