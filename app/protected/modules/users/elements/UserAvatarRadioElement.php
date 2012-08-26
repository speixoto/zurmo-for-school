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
    class UserAvatarRadioElement extends Element
    {              
        protected function renderControlEditable()
        {            
            $content  = ZurmoHtml::radioButtonList($this->getEditableInputName($this->attribute) . '[type]', 
                                                   '0', 
                                                   $this->resolveRadioOptions(), 
                                                   array('id' => $this->getEditableInputId($this->attribute) . '_type'));  
            $content .= ZurmoHtml::tag('div', 
                                       array('id'    => 'avatarRadioElement_customEmailInput',
                                             'style' => 'display:none;'), 
                                       $this->renderCustomEmailInput());
            $content .= ZurmoHtml::tag('div', 
                                       array('id'    => 'avatarRadioElement_galleryRadio',
                                             'style' => 'display:none;'), 
                                       $this->renderGalleryRadio());              
            $this->renderScripts();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            return null;
        }
        
        private function resolveRadioOptions()
        {            
            $primaryEmail = $this->model->primaryEmail;                        
            $radioOptions = array('0' => "Default Avatar",
                                  '1' => "Use gravatar with primary email ({$primaryEmail})",                                  
                                  '2' => "Use gravatar with custom email",
                                  '3' => "Select avatar from gallery");            
            return $radioOptions;
        }
        
        private function renderCustomEmailInput()
        {
            $content = CHtml::textField($this->getEditableInputName($this->attribute) . '[customAvatarEmail]', 
                                                                    null,
                                                                    array('id'=> $this->getEditableInputId($this->attribute) . '_customAvatarEmail'));
            return $content;
        }
        
        private function renderGalleryRadio()
        {
            $radioOption = array('http://www.gravatar.com/avatar/996629fd6cb5cb14318af472021cd2d4?d=identicon&' => "<img src='http://www.gravatar.com/avatar/996629fd6cb5cb14318af472021cd2d4?d=identicon&s=16' />",
                                 'http://www.gravatar.com/avatar/b702d5ed5d1f982fd445ae005a0801c7?d=identicon&' => "<img src='http://www.gravatar.com/avatar/b702d5ed5d1f982fd445ae005a0801c7?d=identicon&s=16' />",);
            $content = ZurmoHtml::radioButtonList($this->getEditableInputName($this->attribute) . '[galleryAvatarImage]', 
                                                  'http://www.gravatar.com/avatar/996629fd6cb5cb14318af472021cd2d4?d=identicon&', 
                                                  $radioOption, 
                                                  array('id' => $this->getEditableInputId($this->attribute) . '_galleryAvatarImage'));
            return $content;
        }
        
        private function renderScripts()
        {            
             $inputId = $this->getEditableInputId($this->attribute);     
             //TODO: Put closest int the customEmail and galleryRadio div?
             Yii::app()->clientScript->registerScript('userAvatarRadioElement', "                                 
                $('#edit-form').change(function() {
                    if ($('#{$inputId}_type_2').attr('checked')) {
                        $('#avatarRadioElement_customEmailInput').show();
                    } else {
                        $('#avatarRadioElement_customEmailInput').hide();
                    }
                    if ($('#{$inputId}_type_3').attr('checked')) {
                        $('#avatarRadioElement_galleryRadio').show();
                    } else {
                        $('#avatarRadioElement_galleryRadio').hide();
                    }
                });
            ", CClientScript::POS_END);
        }
    }
?>