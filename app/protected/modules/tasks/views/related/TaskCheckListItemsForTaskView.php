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
     * A view that displays check list items for a task
     *
     */
    class TaskCheckListItemsForTaskView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $checkListItemsData;

        protected $relatedModel;

        protected $pageSize;

        protected $getParams;

        protected $uniquePageId;

        protected $form;

        public function __construct($controllerId, $moduleId, $checkListItemsData, Item $relatedModel, $form, $getParams, $uniquePageId = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($checkListItemsData)');
            assert('$relatedModel->id > 0');
            assert('is_string($form) || $form == null');
            assert('is_array($getParams)');
            assert('is_string($uniquePageId) || $uniquePageId == null');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->checkListItemsData     = $checkListItemsData;
            $this->relatedModel           = $relatedModel;
            $this->getParams              = $getParams;
            $this->uniquePageId           = $uniquePageId;
        }

        protected function getId()
        {
            return 'TaskCheckListItemsForTaskView' . $this->uniquePageId;
        }

        protected function renderContent()
        {
            $content = null;
            $content = '<div>' . $this->renderHiddenRefreshLinkContent() . '</div>';
            if (count($this->checkListItemsData) > 0)
            {
                $content .= '<div id="TaskCheckListItems' . $this->uniquePageId . '" class="CommentList">' . $this->renderCheckListItemsContent() . '</div>';
            }
            return $content;
        }

        protected function renderHiddenRefreshLinkContent()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/ajaxCheckItemListForRelatedTaskModel',
                            $this->getParams);
            return       ZurmoHtml::ajaxLink('Refresh', $url,
                         array('type' => 'GET',
                               'success' => 'function(data){$("#TaskCheckListItemsForTaskView' . $this->uniquePageId . '").replaceWith(data)}'),
                         array('id'         => 'hiddenCheckListItemRefresh'. $this->uniquePageId,
                                'class'     => 'hiddenCheckListItemRefresh',
                                'namespace' => 'refresh',
                                'style'     => 'display:none;'));
        }

        protected function renderCheckListItemsContent()
        {
            $content  = null;
            $rows = 0;
            $data = array();
            $taskModel = Task::getById(intval($this->getParams['relatedModelId']));
            foreach (array_reverse($this->checkListItemsData) as $checkListItem)
            {
                  $data[] = $checkListItem->name;
            }

            $content = ZurmoHtml::activeCheckBoxList($taskModel, $this->getParams['relatedModelRelationName'], $data, array('separator' => ' '));
            return $content;
        }

//        protected function renderDeleteLinkContent(Comment $comment)
//        {
//            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/deleteViaAjax',
//                            array_merge($this->getParams, array('id' => $comment->id)));
//            // Begin Not Coding Standard
//            return       ZurmoHtml::ajaxLink(Zurmo::t('CommentsModule', 'Delete'), $url,
//                         array('type'     => 'GET',
//                               'complete' => "function(XMLHttpRequest, textStatus){
//                                              $('#deleteCommentLink" . $comment->id . "').parent().parent().parent().remove();}"),
//                         array('id'         => 'deleteCommentLink' . $comment->id,
//                                'class'     => 'deleteCommentLink' . $comment->id,
//                                'namespace' => 'delete'));
//            // End Not Coding Standard
//        }
//
//        /*TODO*/
//        protected function renderEditLinkContent(Comment $comment)
//        {
//            $url     =   '';
//            // Begin Not Coding Standard
//            return       ZurmoHtml::ajaxLink(Zurmo::t('CommentsModule', 'Edit'), $url);
//            // End Not Coding Standard
//        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>