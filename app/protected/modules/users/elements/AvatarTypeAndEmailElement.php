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
     * Radio element to choose witch avatar to use     
     */
    class AvatarTypeAndEmailElement extends Element
    {              
        protected function renderControlEditable()
        {                            
            $content  = $this->renderAvatarTypeRadio              ($this->model, $this->form, 'avatarType');  
            $content .= $this->renderCustomAvatarEmailAddressInput($this->model, $this->form, 'customAvatarEmailAddress');            
            $this->renderScripts();            
            return $content;
        }

        protected function renderControlNonEditable()
        {            
            $avatarUrl   = $this->model->getAvatar();
            $avatarImage = ZurmoHtml::image($avatarUrl);
            $url         = Yii::app()->createUrl('/users/default/changeAvatar', array('id' => $this->model->id));
            $modalTitle  = ModalView::getAjaxOptionsForModalLink(Yii::t('Default', 'Change Avatar') . ": " . strval($this->model));
            $content     = ZurmoHtml::ajaxLink($avatarImage, $url, $modalTitle);            
            return $content;
        }
        
        private function renderAvatarTypeRadio($model, $form, $attribute)
        {
            $id          = $this->getEditableInputId($attribute);
            $htmlOptions = array(
                'name' => $this->getEditableInputName($attribute),
                'id'   => $id);
            $label       = $form->labelEx        ($model, $attribute, array('for'   => $id));            
            $radioInput  = $form->radioButtonList($model, $attribute, $this->resolveRadioOptions(), $htmlOptions);
            $error       = $form->error          ($model, $attribute, array('inputID' => $id));
            if ($model->$attribute != null)
            {
                 $label = null;
            }            
            $content = ZurmoHtml::tag('div', array(), $label . $radioInput . $error);
            return $content;
        }
        
        private function resolveRadioOptions()
        {            
            $primaryEmail = $this->model->primaryEmail;                        
            $radioOptions = array(User::AVATAR_TYPE_DEFAULT       => Yii::t('Default', 'Default Avatar'),
                                  User::AVATAR_TYPE_PRIMARY_EMAIL => Yii::t('Default', 'Use gravatar with primary email ({primaryEmail})',
                                                                            array('{primaryEmail}' => $primaryEmail)),
                                  User::AVATAR_TYPE_CUSTOM_EMAIL  => Yii::t('Default', 'Use gravatar with custom email'),);            
            return $radioOptions;
        }
        
        private function renderCustomAvatarEmailAddressInput($model, $form, $attribute)
        {                        
            $id          = $this->getEditableInputId($attribute);
            $htmlOptions = array(
                'name' => $this->getEditableInputName($attribute),
                'id'   => $id,
            );
            $label       = $form->labelEx  ($model, $attribute, array('for'   => $id));            
            $textField   = $form->textField($model, $attribute, $htmlOptions);
            $error       = $form->error    ($model, $attribute, array('inputID' => $id));
            $tooltip     = $this->renderTooltipContent();
            if ($model->$attribute != null)
            {
                 $label = null;
            }            
            $content = ZurmoHtml::tag('div', 
                                      array('id'    => 'customAvatarEmailAddressInput',
                                            'style' => 'display:none'), 
                                      $label . $textField . $error . $tooltip);
            return $content;
        }               
        
        protected static function renderTooltipContent()
        {
            $title       = Yii::t('Default', 'Your Gravatar is an image that follows you from site to site appearing beside your ' .
                                             'name when you do things like comment or post on a blog.');
            $content     = '<span id="user-gravatar-tooltip" class="tooltip"  title="' . $title . '">';
            $content    .= '?</span>';
            $qtip        = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'))));
            $qtip->addQTip("#user-gravatar-tooltip");
            return $content;
        }
        
        private function renderScripts()
        {            
             $inputId = $this->getEditableInputId('avatarType');             
             Yii::app()->clientScript->registerScript('userAvatarRadioElement', "
                $('#edit-form').change(function() {
                    if ($('#{$inputId}_2').attr('checked')) {
                        $('#customAvatarEmailAddressInput').show();
                    } else {
                        $('#customAvatarEmailAddressInput').hide();
                    }                    
                });
            ", CClientScript::POS_END);
        }               
    }
?>