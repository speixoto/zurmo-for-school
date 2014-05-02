<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Display the role selection.  This is specifically
     * for selecting a parent role.
     */
    class ParentRoleElement extends RoleElement
    {
        const CLEAR_PARENT_ROLE_LINK_ID = 'clear-parent-role-link';

        protected static $modalActionId = 'modalParentList';

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Role');
            $content = parent::renderControlEditable();
            $content .= $this->renderClearParentRoleLink();
            return $content;
        }

        /**
         * Override to ensure the text box is disabled.
         * This will force the select button to be utililzed
         * instead of type-ahead.
         * @return The element's content as a string.
         */
        protected function renderTextField($idInputName)
        {
            $htmlOptions               = array();
            $htmlOptions['disabled']   = 'disabled';
            $htmlOptions['id']         = $this->getIdForTextField();
            $htmlOptions['name']       = $this->getNameForTextField();
            $htmlOptions['value']      = $this->getName();
            return $this->form->textField($this->model, $this->attribute, $htmlOptions);
        }

        protected function getModalTitleForSelectingModel()
        {
            return Zurmo::t('ZurmoModule', 'Select a Parent Role');
        }

        protected function renderClearParentRoleLink()
        {
            $this->registerClearParentRoleLinkScripts();
            $htmlOptions    = $this->resolveClearParentRoleLinkHtmlOptions();
            $label          = Zurmo::t('Core', 'Clear');
            $link           = ZurmoHtml::link(ZurmoHtml::wrapLabel($label), '#', $htmlOptions);
            return $link;
        }

        protected function resolveClearParentRoleLinkHtmlOptions()
        {
            $htmlOptions    = array('id' => static::CLEAR_PARENT_ROLE_LINK_ID, 'class' => 'simple-link');
            if ($this->model->role->id < 0)
            {
                $htmlOptions['style'] = 'display:none;';
            }
            return $htmlOptions;
        }

        protected function registerClearParentRoleLinkScripts()
        {
            $this->registerRoleIdHiddenInputChangeScript();
            $this->registerClearParentRoleLinkClickScript();
        }

        protected function registerRoleIdHiddenInputChangeScript()
        {
            Yii::app()->clientScript->registerScript('roleIdHiddenInputChangeScript', '
                $("#Role_role_id").unbind("change.roleIdHiddenInputChangeScript")
                                                        .bind("change.roleIdHiddenInputChangeScript", function(event)
                 {
                    if ($("#' . static::CLEAR_PARENT_ROLE_LINK_ID .'").is(":hidden"))
                    {
                        $("#' . static::CLEAR_PARENT_ROLE_LINK_ID .'").show();
                    }
                 });');
        }

        protected function registerClearParentRoleLinkClickScript()
        {
            Yii::app()->clientScript->registerScript('clearParentRoleLinkClickScript', '
                $("#' . static::CLEAR_PARENT_ROLE_LINK_ID . '").unbind("click.clearParentRoleLinkClickScript")
                                                        .bind("click.clearParentRoleLinkClickScript", function(event)
                 {
                    $("#Role_role_id").val("");
                    $("#Role_role_name").val("");
                    $(this).hide();
                    event.preventDefault();
                 });');
        }
    }
?>
