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
     * Displays a number filtering input.  Allows for picking a type of filter and sometimes depending on
     * the filter, entering a specific date value.
     */
    class MixedNumberTypesElement extends Element
    {
        /**
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $firstValueSpanAreaId               = $this->getFirstValueEditableInputId() . '-first-value-area';
            $secondValueSpanAreaId              = $this->getSecondValueEditableInputId() . '-second-value-area';
            $startingDivStyleSecondValue  = null;
            if ($this->getOperator() != 'between')
            {
                $startingDivStyleSecondValue = "display:none;";
            }
            $content  = ZurmoHtml::tag('span', array('id'    => $firstValueSpanAreaId,
                                                     'class' => 'first-value-area'),
                                       '&#160;' . $this->renderEditableFirstValueContent());
            $content .= ZurmoHtml::tag('span', array('id'    => $secondValueSpanAreaId,
                                                     'class' => 'second-value-area',
                                                     'style' => $startingDivStyleSecondValue),
                                       '&#160;' . Yii::t('Default', 'and') . $this->renderEditableSecondValueContent());
            return $content;
        }

        protected function renderEditableFirstValueContent()
        {
            $htmlOptions = array(
                'id'              => $this->getFirstValueEditableInputId(),
                'name'            => $this->getFirstValueEditableInputName(),
                'encode' => false,
            );
            $textField   = $this->form->textField($this->model, 'value', $htmlOptions);
            $error       = $this->form->error    ($this->model, 'value');
            return $textField . $error;
        }

        protected function renderEditableSecondValueContent()
        {
            $htmlOptions = array(
                'id'              => $this->getSecondValueEditableInputId(),
                'name'            => $this->getSecondValueEditableInputName(),
                'encode' => false,
            );
            $textField   = $this->form->textField($this->model, 'secondValue', $htmlOptions);
            $error       = $this->form->error    ($this->model, 'secondValue');
            return $textField . $error;
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

        protected function getOperator()
        {
            return $this->model->operator;
        }

        protected function getFirstValueEditableInputId()
        {
            return $this->getEditableInputName('value');
        }

        protected function getSecondValueEditableInputId()
        {
            return $this->getEditableInputName('secondValue');
        }

        protected function getFirstValueEditableInputName()
        {
            return $this->getEditableInputName('value');
        }

        protected function getSecondValueEditableInputName()
        {
            return $this->getEditableInputName('secondValue');
        }
    }
?>