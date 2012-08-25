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
            $content  = ZurmoHtml::radioButtonList($this->getEditableInputName('avatarType'), '0', $this->resolveRadioOptions(), array('id' => $this->getEditableInputId('avatarType')));
            $content .= $this->renderCustomEmailInput();
            $content .= $this->renderGalleryRadio();
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
                                  '3' => "Select avatar from gallery below");            
            return $radioOptions;
        }
        
        private function renderCustomEmailInput()
        {
            $content = CHtml::textField($this->getEditableInputName('customAvatarEmail'), null,array('id'=> $this->getEditableInputId('customAvatarEmail')));
            return $content;
        }
        
        private function renderGalleryRadio()
        {
            $radioOption = array('1' => "<img src='http://mediacdn.disqus.com/1345757376/images/noavatar32.png' />",
                                 '2' => "<img src='http://mediacdn.disqus.com/1345757376/images/noavatar32.png' />",
                                 '3' => "<img src='http://mediacdn.disqus.com/1345757376/images/noavatar32.png' />",
                                 '4' => "<img src='http://mediacdn.disqus.com/1345757376/images/noavatar32.png' />",
                                 '5' => "<img src='http://mediacdn.disqus.com/1345757376/images/noavatar32.png' />");
            $content = ZurmoHtml::radioButtonList($this->getEditableInputName('galleryAvatar'), '', $radioOption, array('id' => $this->getEditableInputId('galleryAvatar')));
            return $content;
        }
        
        private function renderScripts()
        {
             Yii::app()->clientScript->registerScript('userAvatarRadioElement', "                                 
                $('#UserAvatarForm_avatarType_2').unbind('click');
                $('#UserAvatarForm_avatarType_2').bind('click', function(){
                    alert('me');
                }
            ", CClientScript::POS_END);
        }
    }
?>