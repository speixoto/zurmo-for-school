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

    class OutboundSettingsCheckBoxElement extends CheckBoxElement
    {
        protected function renderControlEditable()
        {
            $attribute = $this->attribute;
            $isHidden = $this->model->$attribute;
            $content  = parent::renderControlEditable();
            //For now we only support SMTP type so this is not used
            //$content .= $this->renderEditableTextField($this->model, $this->form, 'outboundType');
            $content .= $this->renderEditableTextField($this->model, $this->form, 'outboundHost', $isHidden);
            $content .= $this->renderEditableTextField($this->model, $this->form, 'outboundPort', $isHidden);
            $content .= $this->renderEditableTextField($this->model, $this->form, 'outboundUsername', $isHidden);
            $content .= $this->renderEditableTextField($this->model, $this->form, 'outboundPassword', $isHidden);
            $content .= $this->renderEditableTextField($this->model, $this->form, 'outboundSecurity', $isHidden);
            $this->renderScripts();
            return $content;
        }

        public function renderEditableTextField($model, $form, $attribute, $isHidden = false)
        {
            $style = $isHidden ? 'display: none;' : null;
            $id          = $this->getEditableInputId($attribute);
            $htmlOptions = array(
                'name'  => $this->getEditableInputName($attribute),
                'id'    => $id,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error    ($model, $attribute);
            return ZurmoHtml::tag('div', array('class' => 'outbound-settings', 'style' => $style),
                                         $label . $textField . $error);
        }

        protected function renderScripts()
        {
            $checkBoxId = $this->getEditableInputId();
            Yii::app()->clientScript->registerScript('userMailConfigurationOutbound', "
                    $('#{$checkBoxId}').change(function(){
                        $('.outbound-settings').toggle();
                    });
                ");
        }
    }
?>