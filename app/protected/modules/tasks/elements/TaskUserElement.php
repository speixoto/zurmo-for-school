<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Display the user selection. This is a
     * combination of a type-ahead input text field
     * and a selection button which renders a modal list view
     * to search on user.  Also includes a hidden input for the user
     * id. On selection of user, task is updated
     */
    class TaskUserElement extends UserElement
    {
        protected static $modalActionId = 'ownerModalListForTask';

        /**
         * Gets modal javascript file base path
         */
        protected static function getModalJavascriptFileBasePath()
        {
            return 'application.modules.tasks.elements.assets';
        }

        /**
         * Resolve input text box with select link content
         * @return string
         */
        protected function resolveInputTextBoxWithSelectLinkContent()
        {
            $inputContent  = $this->renderTextField($this->getIdForHiddenField());
            $inputContent .= $this->renderSelectLink();
            return $inputContent;
        }

        /**
         * @return array
         */
        protected function getModalTransferInformation()
        {
            return array_merge(array(
                    'sourceIdFieldId'   => $this->getIdForHiddenField(),
                    'sourceNameFieldId' => $this->getIdForTextField(),
                    'modalId'           => $this->getModalContainerId(),
                    'attribute'         => $this->attribute
            ), $this->resolveSourceModelIdForModalTransferInformation());
        }

        /**
         * Register script file for handling link on items in modal window
         */
        protected static function registerModalScriptFile()
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias(static::getModalJavascriptFileBasePath())
                    ) . '/TaskUtils.js',
                CClientScript::POS_END
            );
        }
    }
?>
