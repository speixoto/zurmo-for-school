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

    class MissionDetailsView extends SecuredDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'EditLink'),
                            array('type' => 'MissionDeleteLink'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'MissionStatus',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                        'ownerHasReadLatest',
                        'status',
                        'takenByUserHasReadLatest',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'status', 'type' => 'MissionStatus'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'dueDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'owner', 'type' => 'User'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'takenByUser', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea'),
                                            ),
                                        ),
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'rewardDescription', 'type' => 'TextArea'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'Files'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderTitleContent()
        {
            return '<h1>' . strval($this->model) . "</h1>";
        }

        protected function renderAfterFormLayoutForDetailsContent()
        {
            $content  = $this->renderMissionContent();
            $content .= $this->renderMissionCommentsContent();
            $content .= $this->renderMissionCreateCommentContent();
            return $content;
        }

        protected function renderMissionContent()
        {
            /**
            $content  = '<div class="comment model-details-summary">';
            $content .= '<span class="comment-details"><strong>'.
                            DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $this->model->createdDateTime, 'long', null) . '</strong> ';
            $content .= Yii::t('Default', 'by <strong>{ownerStringContent}</strong>',
                                    array('{ownerStringContent}' => strval($this->model->createdByUser)));
            $content .= '</span>';
            $element  = new TextAreaElement($this->model, 'description');
            $element->nonEditableTemplate = '<div class="comment-content">{content}</div>';
            $content .= $element->render();
            $content .= '</span>';
            $element  = new TextAreaElement($this->model, 'rewardDescription');
            $element->nonEditableTemplate = '<div class="comment-content">{content}</div>';
            $content .= $element->render();
            $content .= 'todo show the due date, but with label or not?????????? hmm. same with reward description above';
            $element  = new FilesElement($this->model, 'null');
            $element->nonEditableTemplate = '<div>{content}</div>';
            $content .= $element->render();
            $content .= '</div>';
            **/
           // return Chtml::tag('div', array('id' => 'ModelDetailsSummaryView'), $content);
        }

        protected function renderMissionCommentsContent()
        {
            $getParams    = array('relatedModelId'           => $this->model->id,
                                  'relatedModelClassName'    => get_class($this->model),
                                  'relatedModelRelationName' => 'comments');
            $pageSize     = 5;
            $commentsData = Comment::getCommentsByRelatedModelTypeIdAndPageSize(get_class($this->model),
                                                                                $this->modelId, ($pageSize + 1));
            $view         = new CommentsForRelatedModelView('default', 'comments', $commentsData, $this->model, $pageSize, $getParams);
            $content      = $view->render();
            return $content;
        }

        protected function renderMissionCreateCommentContent()
        {
            $content       = Chtml::tag('h2', array(), Yii::t('Default', 'Add Comment'));
            $comment       = new Comment();
            $uniquePageId  = 'CommentInlineEditForModelView';
            $redirectUrl   = Yii::app()->createUrl('/missions/default/inlineCreateCommentFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => $this->model->id,
                                   'relatedModelClassName' 	  => 'Mission',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                      $urlParameters, $uniquePageId);
            $content      .= $inlineView->render();
            return Chtml::tag('div', array('id' => 'CommentInlineEditForModelView'), $content);
        }

        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/missions/default/inlineCreateComment');
        }
    }
?>
