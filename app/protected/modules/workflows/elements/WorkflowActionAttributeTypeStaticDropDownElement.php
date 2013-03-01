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
     * Class used by reporting or workflow to show available action attribute types in a dropdown.
     */
    class WorkflowActionAttributeTypeStaticDropDownElement extends StaticDropDownFormElement
    {
        public function getIdForSelectInput()
        {
            return $this->getEditableInputId($this->attribute);
        }

        public function getDropDownArray()
        {
            if (isset($this->params['typeValuesAndLabels']))
            {
                return $this->params['typeValuesAndLabels'];
            }
            throw new NotSupportedException();
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions = parent::getEditableHtmlOptions();
            if(isset($htmlOptions['class']))
            {
                $htmlOptions['class'] .= ' actionAttributeType';
            }
            else
            {
                $htmlOptions['class']  = 'actionAttributeType';
            }
            return $htmlOptions;
        }

        protected function renderControlEditable()
        {
            $content = parent::renderControlEditable();
            $this->renderChangeScript();
            return $content;
        }

        protected function renderChangeScript()
        {
            Yii::app()->clientScript->registerScript('operatorRules', "
                $('.operatorType').change( function()
                    {
                        arr  = " . CJSON::encode($this->getValueTypesRequiringFirstInput()) . ";
                        arr2 = " . CJSON::encode($this->getValueTypesRequiringSecondInput()) . ";
                        var firstValueArea  = $(this).parent().parent().parent().find('.value-data').find('.first-value-area');
                        var secondValueArea = $(this).parent().parent().parent().find('.value-data').find('.second-value-area');
                        if ($.inArray($(this).val(), arr) != -1)
                        {
                            firstValueArea.show();
                            firstValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            firstValueArea.hide();
                            firstValueArea.find(':input, select').prop('disabled', true);
                        }
                        if ($.inArray($(this).val(), arr2) != -1)
                        {
                            secondValueArea.show();
                            secondValueArea.find(':input, select').prop('disabled', false);
                        }
                        else
                        {
                            secondValueArea.hide();
                            secondValueArea.find(':input, select').prop('disabled', true);
                        }
                    }
                );
            ");
        }

        public static function getValueTypesRequiringFirstInput()
        {
            return array(WorkflowActionAttributeForm::TYPE_STATIC);
        }

        public static function getValueTypesRequiringSecondInput()
        {
            return array(
                DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME,
                DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME,
                DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE,
                DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE,
                DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS,
            );
        }
    }
?>