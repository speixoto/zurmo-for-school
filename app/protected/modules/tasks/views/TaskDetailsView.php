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
     * Details view for the task
     */
    class TaskDetailsView extends SecuredEditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'EditLink',  'renderType' => 'Details'),
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
            $content      = '<div class="details-table">';
            $content     .= $this->renderTitleContent();
            $content     .= $this->resolveAndRenderActionElementMenu();
            $leftContent  = $this->renderBeforeFormLayoutForDetailsContent();
            $leftContent .= $this->renderFormLayout();
            $content     .= ZurmoHtml::tag('div', array('class' => 'left-column'), $leftContent);
            $content     .= $this->renderRightSideContent();
            $content     .= $this->renderAfterRightSideContent();
            $content     .= '</div>';
            $content     .= $this->renderAfterDetailsTable();
            $this->registerEditInPlaceScript();
            return $content;
        }

        /**
         * Renders form layout
         * @param string $form
         * @return string
         */
        protected function renderFormLayout($form = null)
        {
            $content  = $this->renderTaskCheckListItemsList($form);
            $content .= $this->renderTaskComments($form);
            return $content;
        }

        /**
         * Renders check items list
         * @param type $form
         * @return string
         */
        protected function renderTaskCheckListItemsList($form)
        {
            $checkItemsListElement = new TaskCheckListItemsListElement($this->getModel(), 'null', $form, array());
            $content = $checkItemsListElement->render();
            return $content;
        }

        /**
         * Renders task comments
         * @param type $form
         * @return string
         */
        protected function renderTaskComments($form)
        {
            $commentsElement = new TaskCommentsElement($this->getModel(), 'null', $form, array('moduleId' => 'tasks'));
            $content = $commentsElement->render();
            return $content;
        }

        /**
         * Render content before form layout
         */
        protected function renderBeforeFormLayoutForDetailsContent()
        {
            $leftContent = '<p class="editableTextarea" id="description_' . $this->model->id . '">' . $this->model->description . '</p>';
            $content     = ZurmoHtml::tag('div', array('class' => 'left-column'), $leftContent);
            return $content;
        }

        /**
         * Renders right side content
         * @param string $form
         * @return string
         */
        protected function renderRightSideContent($form = null)
        {
            $content    = null;
            $content    .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                'ZurmoActiveForm',
                                                array_merge
                                                (
                                                    array('id' => 'task-right-column-form-data')
                                                ));
            $content .= $formStart;
            if ($this->getModel() instanceof OwnedSecurableItem)
            {
                $content .= $this->renderTaskStatus($form);
                $content .= $this->renderOwnerBox($form);
                $content .= $this->renderRequestedByUserBox($form);
                $content .= $this->renderDueDateTime($form);
                $content .= $this->renderNotificationSubscribers($form);
            }
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= $this->renderModalContainer();
            $content .= '</div>';
            $content  = ZurmoHtml::tag('div', array('class' => 'right-side-edit-view-panel thread-info'), $content);
            $content  = ZurmoHtml::tag('div', array('class' => 'right-column'), $content);
            return $content;
        }

        /**
         * Renders owner box
         * @param string $form
         * @return string
         */
        protected function renderOwnerBox($form)
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
        protected function renderRequestedByUserBox($form)
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
        protected function renderDueDateTime($form)
        {
            $content  = '';
            $element  = new TaskAjaxDateTimeElement($this->getModel(), 'dueDateTime', $form);
            $element->editableTemplate = '{label}{content}{error}';
            $content .= $element->render();
            return $content;
        }

        /**
         * Renders the detail after right side content is displayed
         * @return string
         */
        protected function renderAfterRightSideContent()
        {
            return '<div id="permissionContent">' . $this->renderAfterFormLayoutForDetailsContent() . '</div>';
        }

        /**
         * Renders notification subscribers
         * @param string $form
         * @return string
         */
        protected function renderNotificationSubscribers($form)
        {
            $task = Task::getById($this->model->id);
            $notificationSubscribers = $task->notificationSubscribers;
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
        protected function renderTaskStatus($form)
        {
            $content  = '<div id="status-box">';
            $element  = new TaskStatusDropDownElement($this->getModel(), 'status', $form);
            $content .= $element->render();
            $content .= '<span id="completionDate">';
            if($this->model->status == Task::TASK_STATUS_COMPLETED)
            {
                $content .= '<p>' . Zurmo::t('TasksModule', 'Completed On') . ': ' . DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($this->model->completedDateTime) . '</p>';
            }
            $content .= '</span>';
            $content .= '</div>';
            return $content;
        }

        /**
         * Registers edit in place script
         */
        protected function registerEditInPlaceScript()
        {
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
        }
    }
?>