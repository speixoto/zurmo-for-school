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
     * Display the conversation status with buttons to change it.
     */
    class ConversationOpenCloseElement extends Element implements DerivedElementInterface
    {
        protected function renderEditable()
        {
            throw NotSupportedException();
        }

        protected function renderControlEditable()
        {
            throw NotSupportedException();
        }

        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "isClosed"');
            assert('$this->model instanceof Conversation');
            self::renderAjaxStatusChange($this->model->id);
            return self::renderStatusChangeArea($this->model);
        }

        public static function renderStatusChangeArea(Conversation $conversation)
        {
            $content  = ZurmoHtml::tag('span', array(), Zurmo::t('ConversationsModule', 'Status'));
            $content .= self::renderStatusButtonsContent($conversation);
            return ZurmoHtml::tag('div', array('id' => self::getStatusChangeDivId($conversation->id),
                                               'class' => 'conversationStatusChangeArea clearfix'),
                                                $content);
        }

        public static function getStatusChangeDivId($conversationId)
        {
            return  'ConversationStatusChangeArea-' . $conversationId;
        }

        private static function getRadioButtonListName($conversationId)
        {
            return 'statusChange-' . $conversationId;
        }

        public static function renderStatusButtonsContent(Conversation $conversation)
        {
            $content = ZurmoHTML::radioButtonList(
                            self::getRadioButtonListName($conversation->id),
                            $conversation->resolveIsClosedForNull(),
                            self::getDropDownArray(),
                            array('separator' => '',
                                  'template'  => '<div class="switch-state clearfix">{input}{label}</div>'));
            return ZurmoHtml::tag('div', array('class' => 'switch'), $content);
        }

        protected static function renderAjaxStatusChange($conversationId)
        {
            $url    = Yii::app()->createUrl('conversations/default/changeIsClosed', array('id' => $conversationId));
            $script = "
                    $('input[name=" . self::getRadioButtonListName($conversationId) . "]').change(function()
                    {
                        $.ajax(
                        {
                            url: '{$url}',
                            type: 'GET',
                            success: " . self::resolveOnSuccessScript() . ",
                        });
                    });
                ";
            Yii::app()->clientScript->registerScript('ConversationStatusChange', $script);
        }

        protected function renderLabel()
        {
            return null;
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ConversationsModule', 'Status');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'isClosed',
            );
        }

        protected static function resolveOnSuccessScript()
        {
            // Begin Not Coding Standard
            $script = "
                function(data)
                {
                    $('#FlashMessageBar').jnotifyAddMessage(
                        {
                            text: '" . CJavaScript::quote(Zurmo::t('ConversationsModule', 'Conversation status was changed.')) . "',
                            permanent: false,
                            showIcon: true,
                            type: 'ConversationsChangeStatusMessage'
                        }
                    );
                    $('#CommentInlineEditForModelView').toggle();
                }";
            // End Not Coding Standard
            return $script;
        }

        public static function getDropDownArray()
        {
            return array('0' => Zurmo::t('ConversationsModule', 'Open'),
                         '1' => Zurmo::t('ConversationsModule', 'Closed'));
        }
    }
?>
