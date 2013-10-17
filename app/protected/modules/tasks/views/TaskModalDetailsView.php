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
     * Modal window for viewing a task
     */
    class TaskModalDetailsView extends SecuredDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'EditLink'),
                            array('type'  => 'AuditEventsModalListLink'),
                            array('type'  => 'TaskDeleteLink'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'ActivityItems',
                        'DerivedExplicitReadWriteModelPermissions',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_FIRST,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => null, 'type' => 'Null'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'ActivityItems'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'project', 'type' => 'ProjectForTask'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                    'type' => 'DerivedExplicitReadWriteModelPermissions'),
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

        /**
         * Gets form id
         * @return string
         */
        protected static function getFormId()
        {
            return 'task-right-column-form-data';
        }

        /**
         * Gets title
         * @return string
         */
        public function getTitle()
        {
            return $this->model->name;
        }

        /**
         * Renders content for a view including a layout title, form toolbar,
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content      = '<div class="details-table">'; //todo: we should probably call this something else?
//          $content     .= $this->renderTitleContent(); //todo: remove
            $content     .= $this->resolveAndRenderActionElementMenu();
            $content     .= $this->renderLeftSideContent();
            $content     .= $this->renderRightSideContent();
            $content     .= '</div>';
            $content     .= $this->renderAfterDetailsTable();
            $this->registerEditInPlaceScript();
            return $content;
        }

        protected function renderLeftSideContent()
        {
            $content  = $this->renderLeftSideTopContent();
            $content .= $this->renderLeftSideBottomContent();
            return ZurmoHtml::tag('div', array('class' => 'left-column'), $content);
        }

        protected function renderLeftSideTopContent()
        {
            $content    = null;
            $content   .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget('ZurmoActiveForm',
                //todo: if i set this to submit on field change it should worK? i dont need special elements then?
                array_merge
                (
                    array('id' => 'task-left-column-form-data')
                ));
            $content .= $formStart;
            $nameElement = new TextElement($this->getModel(), 'name', $form);
            $nameElement->editableTemplate = '{content}{error}';
            $content .= $nameElement->render();
            $descriptionElement = new TextAreaElement($this->getModel(), 'description', $form);
            $content .= $descriptionElement->render();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return ZurmoHtml::tag('div', array('class' => 'left-side-edit-view-panel'), $content);
        }

        protected function renderLeftSideBottomContent()
        {
            $content  = $this->renderTaskCheckListItemsListContent();
            $content .= $this->renderTaskCommentsContent();
            return $content;
        }

        /**
         * Renders right side content
         * @param string $form
         * @return string
         */
        protected function renderRightSideContent($form = null)
        {
            $content  = $this->renderRightSideTopContent();
            $content .= $this->renderRightBottomSideContent();
            $content  = ZurmoHtml::tag('div', array('class' => 'right-column'), $content);
            return $content;
        }

        protected function renderRightSideTopContent()
        {
            $content    = null;
            $content   .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget('ZurmoActiveForm',
                //todo: if i set this to submit on field change it should worK? i dont need special elements then?
                //todo: i think i still need 2 forms
                                        array_merge
                                        (
                                            array('id' => 'task-right-column-form-data')
                                        ));
            $content .= $formStart;
            $content .= $this->renderStatusContent($form);
            $content .= $this->renderOwnerContent($form);
            $content .= $this->renderRequestedByUserContent($form);
            $content .= $this->renderDueDateTimeContent($form);
            $content .= $this->renderNotificationSubscribersContent();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return ZurmoHtml::tag('div', array('class' => 'right-side-edit-view-panel'), $content);
        }

        protected function renderRightBottomSideContent()
        {
            return ZurmoHtml::tag('div', array('class' => 'right-side-details-view-panel'), $this->renderFormLayout());
        }

        /**
         * Renders check items list
         * @return string
         */
        protected function renderTaskCheckListItemsListContent()
        {
            $checkItemsListElement = new TaskCheckListItemsListElement($this->getModel(), 'null');
            return $checkItemsListElement->render();
        }

        /**
         * Renders task comments
         * @return string
         */
        protected function renderTaskCommentsContent()
        {
            $commentsElement = new TaskCommentsElement($this->getModel(), 'null', null, array('moduleId' => 'tasks'));
            return $commentsElement->render();
        }

        /**
         * Renders owner box
         * @param string $form
         * @return string
         */
        protected function renderOwnerContent($form)
        {
            $content  = '<div id="owner-box">';
            $element  = new TaskUserElement($this->getModel(), 'owner', $form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render().'</div>';
            return $content;
        }

        /**
         * Renders requested by user box
         * @param string $form
         * @return string
         */
        protected function renderRequestedByUserContent($form)
        {
            $content  = '<div id="owner-box">';
            $element  = new TaskUserElement($this->getModel(), 'requestedByUser', $form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render().'</div>';
            return $content;
        }

        /**
         * Renders due date time
         * @param string $form
         * @return string
         */
        protected function renderDueDateTimeContent($form)
        {
            $content  = '';
            $element  = new TaskAjaxDateTimeElement($this->getModel(), 'dueDateTime', $form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render();
            return $content;
        }

        /**
         * Renders notification subscribers
         * @param string $form
         * @return string
         */
        protected function renderNotificationSubscribersContent()
        {
            $task = Task::getById($this->model->id);
            $content = '<div id="task-subscriber-box">';
            $content .= Zurmo::t('TasksModule', 'Who is receiving notifications');
            $content .= TasksUtil::getDetailSubscriptionLink($task, 0);
            $content .= '<div id="subscriberList">';

            if ($task->notificationSubscribers->count() > 0)
            {
                $content .= TasksUtil::getTaskSubscriberData($task);
            }

            $content .= '</div>';
            $content .= '</div>';

            TasksUtil::registerSubscriptionScript($this->model->id);
            TasksUtil::registerUnsubscriptionScript($this->model->id);
            return $content;
        }

        /**
         * Resolves Subscribe Url
         * @return string
         */
        protected function resolveSubscribeUrl()
        {
            return Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $this->model->id));
        }

        /**
         * Resolve subscriber ajax options
         * @return array
         */
        protected function resolveSubscriberAjaxOptions()
        {
            return array(
                'type'    => 'GET',
                'dataType'=> 'html',
                'data'    => array(),
                'success' => 'function(data)
                              {
                                $("#subscribe-task-link").hide();
                                $("#subscriberList").replaceWith(data);
                              }'
            );
        }

        /**
         * Renders owner box
         * @param string $form
         * @return string
         */
        protected function renderStatusContent($form)
        {
            $content  = '<div id="status-box">';
            $element  = new TaskStatusDropDownElement($this->getModel(), 'status', $form);
            $content .= $element->render();
            $content .= '<span id="completionDate">';
            if($this->model->status == Task::STATUS_COMPLETED) //todO: deal with showing completedDateTime etc.
            {
                $content .= '<p>' . Zurmo::t('TasksModule', 'Completed On') . ': ' .
                            DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                            $this->model->completedDateTime) . '</p>';
            }
            $content .= '</span>';
            $content .= '</div>';
            return $content;
        }

        /**
         * Registers edit in place script
         */
        protected function registerEditInPlaceScript() //todo: maybe remove this if we don't use it
        {
            /**
            $taskCheckItemUrl     = Yii::app()->createUrl('tasks/taskCheckItems/updateNameViaAjax');
            $updateDesctiptionUrl = Yii::app()->createUrl('tasks/default/updateDescriptionViaAjax');
            Yii::app()->clientScript->registerScriptFile(
                            Yii::app()->getAssetManager()->publish(
                                     Yii::getPathOfAlias('application.modules.tasks.views.assets')) . '/jquery.editinplace.js');
            $script = '$(".editable").editInPlace({
                                                    url: "' . $taskCheckItemUrl . '",
                                                    element_id : "id",
                                                    show_buttons: true,
                                                    value_required : true
                                                    });';

            $scriptTextArea = '$(".editableTextarea").editInPlace({
                                                    url: "' . $updateDesctiptionUrl . '",
                                                    element_id : "id",
                                                    show_buttons: false,
                                                    field_type: "textarea",
                                                    textarea_rows: "15",
                                                    textarea_cols: "35",
                                                    default_text: "' . Zurmo::t('TasksModule', 'Click here to enter description') . '"
                                                    });';
            Yii::app()->getClientScript()->registerScript('editableScript', $script, ClientScript::POS_END);
            Yii::app()->getClientScript()->registerScript('editableTextAreaScript', $scriptTextArea, ClientScript::POS_END);
             * **/
        }

        public static function getDesignerRulesType()
        {
            return 'TaskModalDetailsView';
        }
    }
?>