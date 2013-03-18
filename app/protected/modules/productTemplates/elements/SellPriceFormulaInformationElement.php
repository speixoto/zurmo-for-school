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

    class SellPriceFormulaInformationElement extends Element
    {
        /**
         * Renders the editable sell price formula content.
         * It consist of 2 items
         * Name and discountOrMarkupPercentage where discountOrMarkupPercentage
         * would be dependent on Name selected
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            assert('$this->model->{$this->attribute} instanceof SellPriceFormula');
            $sellPriceFormulaModel        = $this->model->{$this->attribute};
            $content = $this->renderNameDropDown($sellPriceFormulaModel, $this->form, $this->attribute, 'name') . "\n";

            return $content;
        }

        protected function renderControlNonEditable()
        {
            $content = null;
            return $content;
        }

        protected function renderNameDropDown($model, $form, $inputNameIdPrefix, $attribute)
        {
            $id = $this->getEditableInputId($inputNameIdPrefix, $attribute);
            $htmlOptions = array(
                'name' => $this->getEditableInputName($inputNameIdPrefix, $attribute),
                'id'   => $id,
                'onchange' => 'showHideDiscountOrMarkupPercentageTextField($(this).val())'
            );
            $dropDownField = $form->dropDownList($model, $attribute, SellPriceFormula::getNameDropDownArray(), $htmlOptions);
            $error     = $form->error    ($model, $attribute, array('inputID' => $id));
            return $dropDownField . $error;
        }

        protected function renderError()
        {
        }

        protected function registerScripts()
        {
            Yii::app()->clientScript->registerScript(
                'ShowHideDiscountOrMarkupPercentageTextField',
                PoliciesElementUtil::getEnableDisablePolicyTextFieldScript(),
                CClientScript::POS_END
            );
        }
    }
?>