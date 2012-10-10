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
     * Displays the standard boolean field
     * rendered as a check box.
     */
    class CheckBoxElement extends Element
    {
        /**
         * Render A standard text input.
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            assert('empty($this->model->{$this->attribute}) ||
                is_string($this->model->{$this->attribute}) ||
                is_integer(BooleanUtil::boolIntVal($this->model->{$this->attribute}))'
            );
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            if ($this->getDisabledValue())
            {
                $htmlOptions['disabled'] = $this->getDisabledValue();
                if (BooleanUtil::boolVal($this->model->{$this->attribute}))
                {
                    $htmlOptions['uncheckValue'] = 1;
                }
            }
            return $this->form->checkBox($this->model, $this->attribute, $htmlOptions);
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            $htmlOptions['disabled'] = 'disabled';
            return ZurmoHtml::activeCheckBox($this->model, $this->attribute, $htmlOptions);
        }
    }
?>
