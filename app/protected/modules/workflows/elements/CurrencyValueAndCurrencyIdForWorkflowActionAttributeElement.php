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
     * Renders currencyValue input/display and currency code information.  This element is used to input
     * currency attribute type values in the user interface in the workflow wizard for the actions.
     */
    class CurrencyValueAndCurrencyIdForWorkflowActionAttributeElement extends TextElement
    {
        /**
         * Renders the editable currency attribute. Also renders a currency id selector if there is more
         * than one currency. If there is only one currency, then show a display only currency code with
         * a hidden input for the currency id.
         * @return A string containing the element's content
         */
        protected function renderControlEditable()
        {
            assert('$this->model instanceof CurrencyValueWorkflowActionAttributeForm');
            $content  = '<div class="hasParallelFields">';
            $content .= ZurmoHtml::tag('div', array('class' => 'quarter'), $this->renderCurrencyIdDropDownField());
            $content .= ZurmoHtml::tag('div', array('class' => 'threeQuarters'), $this->renderEditableValueTextField());
            $content .= '</div>';
            return $content;
        }

        protected function renderCurrencyIdDropDownField()
        {
            $htmlOptions  = array(
                'id'     => $this->getEditableInputId('currencyId'),
                'name'   => $this->getEditableInputName('currencyId'),
            );
            $dropDownArray = $this->getCurrencyDropDownArray();
            $inputContent  = $this->form->dropDownList($this->model, 'currencyId', $dropDownArray, $htmlOptions);
            $error         = $this->form->error($this->model, 'currencyId',
                             array('inputID' => $this->getEditableInputId('currencyId')));
            return $inputContent . $error;
        }

        protected function renderEditableValueTextField()
        {
            $htmlOptions  = array(
                                'id'     => $this->getEditableInputId('value'),
                                'name'   => $this->getEditableInputName('value'),
                            );
            $inputContent = $this->form->textField($this->model, 'value', $htmlOptions);
            $error        = $this->form->error($this->model, 'value',
                            array('inputID' => $this->getEditableInputId('value')));
            return $inputContent . $error;
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         * @throws NotSupportedException
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

       protected function renderError()
       {
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
         * @return array
         */
        protected function getCurrencyDropDownArray()
        {
            return Yii::app()->currencyHelper->getActiveCurrenciesOrSelectedCurrenciesData((int)$this->model->currencyId);
        }
    }
?>
