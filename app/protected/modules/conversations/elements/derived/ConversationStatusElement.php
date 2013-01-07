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
     * Display the conversation status with the action button when applicable.
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

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "isClosed"');
            assert('$this->model instanceof Conversation');
            return self::renderStatusTextAndActionArea($this->model);
        }

        public static function renderStatusTextAndActionArea(Conversation $conversation)
        {
            $statusAction      = self::renderStatusActionContent($conversation, self::getStatusChangeDivId($conversation->id));
            $content = ZurmoHtml::tag('span', array(), Yii::t('Default', 'Status')) . $statusAction;
            return ZurmoHtml::tag('div', array('id' => self::getStatusChangeDivId($conversation->id),
                                               'class' => 'conversationStatusChangeArea clearfix'),
                                                $content);
        }

        public static function getStatusChangeDivId($conversationId)
        {
            return  'ConversationStatusChangeArea-' . $conversationId;
        }

        public static function renderStatusActionContent(Conversation $conversation, $updateDivId)
        {
            assert('is_string($updateDivId)');
            return self::renderAjaxStatusActionChangeLink(false, $conversation->id, Yii::t('Default', 'Open'), $updateDivId); 
                   //self::renderAjaxStatusActionChangeLink(true, $conversation->id, Yii::t('Default', 'Closed'), $updateDivId);
        }

        protected static function renderAjaxStatusActionChangeLink($newStatus, $conversationId, $label, $updateDivId)
        {
            assert('is_bool($newStatus)');
            assert('is_int($conversationId)');
            assert('is_string($label)');
            assert('is_string($updateDivId)');
            $url       =   Yii::app()->createUrl('conversations/default/ajaxChangeStatus', array('id' => $conversationId));
            $aContent  = ZurmoHtml::tag('span', array('class' => 'z-spinner'), null);
            $aContent .= ZurmoHtml::tag('span', array('class' => 'z-icon'), null);
            $aContent .= ZurmoHtml::tag('span', array('class' => 'z-label'), $label);
            $statusClass =  '';
            if ($newStatus)
            {
                $statusClass =  'current-status';
            }   
            $link = ZurmoHtml::ajaxLink($aContent, $url,
                        array('type'      => 'GET', 'success' => self::resolveOnSucessSctipt($updateDivId) ),
                        array('id'        => 'ConversationStatusChange',
                               'class'     => 'conversation-change-status-link clearfix switch-state ' . $statusClass . ' ' . 
                                               self::resolveLinkSpecificCssClassNameByNewStatus($newStatus),
                               'namespace' => 'update',
                               'onclick'   => 'js:$(this).addClass("loading").addClass("loading-ajax-submit"); attachLoadingSpinner($(this).attr("id"), true);'));
            //return $link;
            return '<label><input type="radio">Open</label><label><input type="radio">Close</label>';
        }

        protected static function resolveLinkSpecificCssClassNameByNewStatus($status)
        {
            assert('is_bool($status)');
            if ($status)
            {
                return 'action-close';
            }
            else
            {
                return 'action-open';
            }
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

        protected static function resolveOnSucessSctipt($updateDivId)
        {
            $script = '
                function(data){
                    $("#' . $updateDivId . '").replaceWith(data);
                    $("#CommentInlineEditForModelView").toggle();
                 }';
            return $script;
        }
    }
?>