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
     * currency attribute type values in the user interface on a model.
     */
    class CurrencyValueElement extends TextElement
    {
            /**
         * Renders the editable currency attribute. Also renders a currency id selector if there is more
         * than one currency. If there is only one currency, then show a display only currency code with
         * a hidden input for the currency id.
         * @return A string containing the element's content
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof CurrencyValue');
            $currencyValueModel        = $this->model->{$this->attribute};
            $params                    = array();
            $params['inputPrefix']     = $this->resolveInputPrefix();
            $this->resolveParamsForCurrencyId($params);
            //need to somehow override to pass not to default to currency
            $activeCurrenciesElement   = new CurrencyIdForAModelsRelatedCurrencyValueDropDownElement(
                                                                $this->model, $this->attribute, $this->form, $params);
            $activeCurrenciesElement->editableTemplate = '{content}{error}';
            $content  = '<div class="hasParallelFields">';
            $content .= ZurmoHtml::tag('div', array('class' => 'quarter'), $activeCurrenciesElement->render());
            $content .= ZurmoHtml::tag('div', array('class' => 'threeQuarters'),
                            $this->renderEditableValueTextField($currencyValueModel, $this->form, $this->attribute, 'value'));
            $content .= $this->renderExtraEditableContent();
            $content .= '</div>';
            return $content;
        }

        protected function renderEditableValueTextField($model, $form, $inputNameIdPrefix, $attribute)
        {
            //need to override a resolveValue to NOT default to 0 if not specifically null
            $htmlOptions = array(
                'name' =>  $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'   =>  $this->getEditableInputId($inputNameIdPrefix, $attribute),
                'value' => $this->resolveAndGetEditableValue($model, $attribute),
            );
            $textField = $form->textField($model, $attribute, $htmlOptions);
            $error     = $form->error    ($model, $attribute);
            return $textField . $error;
        }

        /**
         * Renders the noneditable currency content formatted into the localized format and with the
         * currency symbol.
         * @return A string containing the element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->model->{$this->attribute} instanceof CurrencyValue');
            $currencyValueModel = $this->model->{$this->attribute};
            return Yii::app()->numberFormatter->formatCurrency( $currencyValueModel->value,
                                                                $currencyValueModel->currency->code);
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getEditableInputId($this->attribute, 'value');
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }

        /**
         * Override as needed
         */
        protected function renderExtraEditableContent()
        {
        }

        /**
         * Override as needed
         */
        protected function resolveParamsForCurrencyId(& $params)
        {
        }

        protected function resolveAndGetEditableValue($model, $attribute)
        {
            return $model->$attribute;
        }
    }
?>
