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
     * Display the conversation status with buttons to change it.
     */
    class ConversationStatusElement extends Element implements DerivedElementInterface
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
            $statusAction      = self::renderStatusButtonsContent($conversation);
            $content = ZurmoHtml::tag('span', array(), Yii::t('Default', 'Status')) . $statusAction;
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
            return ZurmoHTML::radioButtonList(
                    self::getRadioButtonListName($conversation->id),
                    self::getConversationStatus($conversation),
                    array("0"=>"Open", "1"=>"Closed"),
                    array('separator'=>''));
        }

        public static function getConversationStatus(Conversation $conversation)
        {
            //TODO: Should i put this directly in the conversation model? I think it makes more sence!
            if ($conversation->isClosed == true)
            {
                return 1;
            }
            else
            {
                return 0;
            }
        }

        protected static function renderAjaxStatusChange($conversationId)
        {
            $url    = Yii::app()->createUrl('conversations/default/ajaxChangeStatus', array('id' => $conversationId));
            $script = "
                    $('input[name=" . self::getRadioButtonListName($conversationId) . "]').change(function() {
                        $.ajax({
                            url: '{$url}',
                            type: 'GET',
                            success: " . self::resolveOnSucessScript() . ",
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
            return Yii::t('Default', 'Status');
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

        protected static function resolveOnSucessScript()
        {
            $script = "
                function(data){
                    $('#FlashMessageBar').jnotifyAddMessage(
                        {
                            text: '" . CJavaScript::quote(Yii::t('Default', 'Conversation status was changed.')) . "',
                            permanent: false,
                            showIcon: true,
                            type: 'ConversationsChangeStatusMessage'
                        }
                    );
                    $('#CommentInlineEditForModelView').toggle();
                 }";
            return $script;
        }
    }
?>