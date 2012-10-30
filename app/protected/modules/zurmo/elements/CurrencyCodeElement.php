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
     * Element to be used when adding new currency codes to the available currency list. Utilizes type-ahead to ensure
     * the code is valid.  Can also search by partial currency name.
     */
    class CurrencyCodeElement extends TextElement
    {
        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute}) || is_string($this->model->{$this->attribute})');
            $htmlOptions             = array();
            $htmlOptions['disabled'] = $this->getDisabledValue();
            $htmlOptions             = array_merge($this->getHtmlOptions(), $htmlOptions);
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip('CurrencyCodeElement');
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'        => $this->getEditableInputName(),
                'id'          => $this->getEditableInputId(),
                'value'       => $this->model->{$this->attribute},
                'source'      => Yii::app()->createUrl('zurmo/currency/autoComplete'),
                'htmlOptions' => $htmlOptions,
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['CurrencyCodeElement'];
            $content.= '<span class="field-description">' . Yii::t('Default', 'Type a currency code or name to search.') . '</span>';
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
    }
?>
