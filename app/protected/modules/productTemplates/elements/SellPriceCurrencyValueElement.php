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
     * Renders currencyValue input/display and currency code information for sell price.  This element is used to input
     * sell price currency attribute type values in the user interface on a model.
     */
    class SellPriceCurrencyValueElement extends CurrencyValueElement
    {
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof CurrencyValue');
            $currencyValueModel        = $this->model->{$this->attribute};
            $params                    = array();
            $params['inputPrefix']     = $this->resolveInputPrefix();
            $this->resolveParamsForCurrencyId($params);
            //need to somehow override to pass not to default to currency
            $activeCurrenciesElement   = new SellPriceCurrencyIdForAModelsRelatedCurrencyValueDropDownElement(
                                                                $this->model, $this->attribute, $this->form, $params);
            $activeCurrenciesElement->editableTemplate = '{content}';
            $content  = '<div class="hasParallelFields">';
            $content .= ZurmoHtml::tag('div', array('class' => 'quarter'), $activeCurrenciesElement->render());
            $content .= ZurmoHtml::tag('div', array('class' => 'threeQuarters'),
                            $this->renderEditableValueTextField($currencyValueModel, $this->form, $this->attribute, 'value', $this->model));
            $content .= $this->renderExtraEditableContent();
            $content .= '</div>';
            return $content;
        }

	protected function renderEditableValueTextField($model, $form, $inputNameIdPrefix, $attribute, $parentModel = null)
        {
	    $sellPriceFormulaModel = $parentModel->sellPriceFormula;
            $type = $sellPriceFormulaModel->type;
	    $additionalHtmlOptions = array();
	    if($type != null)
	    {
		if($type != SellPriceFormula::TYPE_EDITABLE)
                {
		    $additionalHtmlOptions = array('readonly' => 'readonly', 'class' => 'disabled');
		}
	    }
	    //need to override a resolveValue to NOT default to 0 if not specifically null
            $id =  $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $htmlOptions = array(
                'name' =>  $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'   => $id,
                'value' => $this->resolveAndGetEditableValue($model, $attribute),
            );

	    $htmlOptions = array_merge($htmlOptions, $additionalHtmlOptions);
            $textField = $form->textField($model, $attribute, $htmlOptions);
            $error     = $form->error    ($model, $attribute, array('inputID' => $id), true, true,
                                          $this->renderScopedErrorId($inputNameIdPrefix, $attribute));
            return $textField . $error;
        }
    }
?>
