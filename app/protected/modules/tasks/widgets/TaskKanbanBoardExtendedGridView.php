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
     * Extends the KanbanBoardExtendedGridView to provide a 'stacked' Kanban Board format for viewing lists of data.
     */
    class TaskKanbanBoardExtendedGridView extends KanbanBoardExtendedGridView
    {
        public $relatedModelId;

        public $relatedModelClassName;

        public $columnsData;

        /**
         * @return array
         */
        protected function resolveDataIntoKanbanColumns()
        {
            $this->makeColumnsDataAndStructure();
            $kanbanItemsArray = array();
            foreach ($this->dataProvider->data as $notUsed => $data)
            {
                $kanbanItem  = KanbanItem::getByTask($data->id);
                if($kanbanItem == null)
                {
                    //Create KanbanItem here
                    $kanbanItem = TasksUtil::createKanbanItemFromTask($data);
                }

                $kanbanItemsArray[$kanbanItem->type][intval($kanbanItem->sortOrder)] = $kanbanItem->task;
            }
            foreach($kanbanItemsArray as $type => $kanbanData)
            {
                ksort($kanbanData, SORT_NUMERIC);
                foreach($kanbanData as $sort => $item)
                {
                    if (isset($this->columnsData[$type]))
                    {
                        $this->columnsData[$type][] = $item;
                    }
                }
            }
            $this->registerKanbanColumnScripts();
            return $this->columnsData;
        }

        /**
         * Resolve order by type
         * @param array $columnsData
         * @param int $type
         * @return int
         */
        protected function resolveOrderByType($columnsData, $type)
        {
            if (isset($columnsData[$type]))
            {
                return count($columnsData[$type]) + 1;
            }
            return 1;
        }

        /**
         * @return array
         */
        protected function makeColumnsDataAndStructure()
        {
            $columnsData = array();
            foreach ($this->groupByAttributeVisibleValues as $value)
            {
                $columnsData[$value] = array();
            }
            $this->columnsData = $columnsData;
        }

        /**
         * Creates ul tag for kanban column
         * @param array $listItems
         * @param string $attributeValue
         * @return string
         */
        protected function renderUlTagForKanbanColumn($listItems, $attributeValue = null)
        {
            return ZurmoHtml::tag('ul id="task-sortable-rows-' . $attributeValue . '" class="connectedSortable"' ,
                                  array(), $listItems);
        }

        /**
         * Override script registration
         */
        protected function registerScripts()
        {
            $taskSortableScript = "
                        var fixHelper = function(e, ui) {
                            var label = $($('<div></div>').html(ui.clone())).html();
                            var width = $(ui).width();
                            var clone = $('<div class=\"kanban-card clone\">' + label + '</div>');
                            clone.width(width);
                            return clone;
                        };";
            Yii::app()->clientScript->registerScript('task-sortable-data-helper', $taskSortableScript);

            /*@TODO Mayank: we need to integrate the drag/drop actions from KanbanUtils.js into your code, this is for the visual feedabck, see opps kanban when u drag/drop
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                Yii::getPathOfAlias('application.core.kanbanBoard.widgets.assets')) . '/KanbanUtils.js');
            $script = 'setupKanbanDragDrop();';
            Yii::app()->getClientScript()->registerScript('KanbanDragDropScript', $script);
            */
        }

        /**
         * Register Kanban Column Scripts
         */
        protected function registerKanbanColumnScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                Yii::getPathOfAlias('application.core.kanbanBoard.widgets.assets')) . '/KanbanUtils.js');

            $columnDataKeys = array_keys($this->columnsData);
            Yii::app()->clientScript->registerScript('task-sortable-data', $this->registerKanbanColumnSortableScript());
            $url = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView', array());
            $this->registerKanbanColumnStartActionScript(Zurmo::t('Core', 'Finish'), Task::STATUS_IN_PROGRESS, $url);
            $this->registerKanbanColumnFinishActionScript(Zurmo::t('Core', 'Accept'),
                        Zurmo::t('Core', 'Reject'), Task::STATUS_AWAITING_ACCEPTANCE, $url);
            $this->registerKanbanColumnAcceptActionScript('', Task::STATUS_COMPLETED, $url);
            $this->registerKanbanColumnRejectActionScript(Zurmo::t('Core', 'Start'), Task::STATUS_NEW, $url);
            TasksUtil::registerSubscriptionScript();
            TasksUtil::registerUnsubscriptionScript();
        }

        /**
         * Registers kanban column sortable script
         * @param int $count
         * @param int $type
         * @return string
         */
        protected function registerKanbanColumnSortableScript()
        {
            $url = Yii::app()->createUrl('tasks/default/updateStatusOnDragInKanbanView');
            return "setUpTaskKanbanSortable('{$url}')";
        }

        /**
         * Registers kanban column start action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnStartActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('action-type-start', KanbanItem::TYPE_IN_PROGRESS,
                                                        $label, 'action-type-finish', $url, $targetStatus);
            Yii::app()->clientScript->registerScript('start-action-script', $script);
        }

        /**
         * Registers kanban column finish action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnFinishActionScript($labelAccept, $labelReject, $targetStatus, $url)
        {
            $acceptanceStatusLabel = Task::getStatusDisplayName(Task::STATUS_AWAITING_ACCEPTANCE);
            $script = "$(document).on('click','.action-type-finish',function()
                            {
                                var element = $(this).parent().parent().parent().parent();
                                var ulelement = $(element).parent();
                                var id = $(element).attr('id');
                                var idParts = id.split('_');
                                var taskId = parseInt(idParts[1]);
                                var rejectLinkElement = $(this).clone();
                                var parent = $(this).parent();
                                $(this).find('.button-label').html('" . $labelAccept . "');
                                $(this).removeClass('action-type-finish').addClass('action-type-accept');
                                $(rejectLinkElement).appendTo($(parent));
                                $(rejectLinkElement).find('.button-label').html('" . $labelReject . "');
                                $(rejectLinkElement).removeClass('action-type-finish').addClass('action-type-reject');
                                $(element).find('.task-status').html('{$acceptanceStatusLabel}');
                                $.ajax(
                                    {
                                        type : 'GET',
                                        data : {'targetStatus':" . Task::STATUS_AWAITING_ACCEPTANCE . ", 'taskId':taskId},
                                        url  : '" . $url . "'
                                    }
                                );
                            }
                        );";
            Yii::app()->clientScript->registerScript('finish-action-script', $script);
        }

        /**
         * @return string
         */
        protected function getRowClassForTaskKanbanColumn($data)
        {
            if((bool)$data->completed)
            {
                return 'kanban-card item-to-place ui-state-disabled';
            }
            else
            {
                return 'kanban-card item-to-place';
            }
        }

        /**
         * Creates task item for kanban column
         * @param array $data
         * @param int $row
         * @return string
         */
        protected function createTaskItemForKanbanColumn($data, $row)
        {
            return ZurmoHtml::tag('li', array('class' => $this->getRowClassForTaskKanbanColumn($data),
                                              'id' => 'items_' . $data->id),
                                              ZurmoHtml::tag('div', array('class' => 'clearfix'),
                                                  $this->renderTaskCardDetailsContent($data, $row)));
        }

        /**
         * Get list items by attribute value and data
         * @param array $attributeValueAndData
         * @return array
         */
        protected function getListItemsByAttributeValueAndData($attributeValueAndData)
        {
            $listItems = '';
            foreach ($attributeValueAndData as $key => $data)
            {
                $listItems .= $this->createTaskItemForKanbanColumn($data, $key + 1);
            }

            return $listItems;
        }

        /**
         * Register button action script
         * @param string $buttonClass
         * @param int $targetKanbanItemType
         * @param string $label
         * @param string $targetButtonClass
         * @param string $url
         * @param int $targetStatus
         * @return string
         */
        protected function registerButtonActionScript($buttonClass, $targetKanbanItemType, $label,
                                                      $targetButtonClass, $url, $targetStatus)
        {
            $completionText = Zurmo::t('TasksModule', '% Complete - 100');
            $newStatusLabel = Task::getStatusDisplayName(Task::STATUS_NEW);
            $completedStatusLabel = Task::getStatusDisplayName(Task::STATUS_COMPLETED);
            return "$(document).on('click','." . $buttonClass . "',
                        function()
                        {
                            var element = $(this).parent().parent().parent().parent();
                            var ulelement = $(element).parent();
                            var id = $(element).attr('id');
                            var ulid = $(ulelement).attr('id');
                            var ulidParts = ulid.split('-');
                            var idParts = id.split('_');
                            var taskId = parseInt(idParts[1]);
                            var columnType = parseInt(ulidParts[3]);
                            $('#task-sortable-rows-" . $targetKanbanItemType . "').append(element);
                            $('#task-sortable-rows-' + columnType).remove('#' + id);

                            if(" . $targetStatus . " != " . Task::STATUS_COMPLETED . ")
                            {
                                var linkTag = $('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' ." . $buttonClass . "');
                                $(linkTag).find('.button-label').html('" . $label . "');
                                $(linkTag).removeClass('" . $buttonClass . "').addClass('" . $targetButtonClass . "');
                                if('" . $buttonClass . "' == 'action-type-reject')
                                {
                                    $('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' .action-type-accept').remove();
                                    $(element).find('.task-status').html('{$newStatusLabel}');
                                }
                            }
                            else
                            {
                                $(element).find('.button-label').remove();
                                $(element).find('.task-action-toolbar').remove();
                                $(element).addClass('ui-state-disabled');
                                $(element).find('.task-status').html('{$completedStatusLabel}');
                                //$('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' .task-completion').html('" . $completionText . "');
                            }
                            $.ajax(
                            {
                                type : 'GET',
                                data : {'targetStatus':" . $targetStatus . ", 'taskId':taskId},
                                url  : '" . $url . "'
                            }
                            );
                        }
                    );";
        }

        /**
         * Register kanban column accept action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnAcceptActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('action-type-accept', KanbanItem::TYPE_COMPLETED,
                      $label, 'task-complete-action ui-state-disabled', $url,$targetStatus);
            Yii::app()->clientScript->registerScript('accept-action-script', $script);
        }

        /**
         * Register kanban column reject action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnRejectActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('action-type-reject', KanbanItem::TYPE_SOMEDAY,
                      $label, 'action-type-start', $url, $targetStatus);
            Yii::app()->clientScript->registerScript('reject-action-script', $script);
        }

        /**
         * @param Task $task
         * @param $row
         * @return string
         */
        protected function renderTaskCardDetailsContent(Task $task, $row)
        {
            $statusClass = 'status-' . $task->status;

            $content  = $this->renderCardDataContent($this->cardColumns['completionBar'], $task, $row);
            $content .= ZurmoHtml::openTag('div', array('class' => 'task-details clearfix ' . $statusClass));
            $content .= ZurmoHtml::tag('span', array('class' => 'task-status'), Task::getStatusDisplayName($task->status));
            $content .= $this->resolveAndRenderTaskCardDetailsDueDateContent($task);
            $content .= ZurmoHtml::closeTag('div');

            $content .= ZurmoHtml::openTag('div', array('class' => 'task-content clearfix'));
            $content .= $this->resolveAndRenderTaskCardDetailsStatusContent($task, $row);
            $content .= ZurmoHtml::openTag('h4');
            $content .= $this->renderCardDataContent($this->cardColumns['name'], $task, $row);
            $content .= ZurmoHtml::closeTag('h4');
            if($task->description != null)
            {
                $description = $task->description;
                if (strlen($description) > TaskKanbanBoard::TASK_DESCRIPTION_LENGTH)
                {
                    $description = substr($description, 0, TaskKanbanBoard::TASK_DESCRIPTION_LENGTH) . '...';
                }
                $content .= ZurmoHtml::tag('p', array(), $description);
            }
            $content .= ZurmoHtml::closeTag('div');

            $content .= ZurmoHtml::openTag('div', array('class' => 'task-subscribers'));
            $content .= $this->resolveAndRenderTaskCardDetailsSubscribersContent($task);
            $content .= $this->renderCardDataContent($this->cardColumns['subscribe'], $task, $row);
            $content .= ZurmoHtml::closeTag('div');

            return $content;
        }

        protected function resolveAndRenderTaskCardDetailsDueDateContent(Task $task)
        {
            if($task->dueDateTime != null)
            {
                $content = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                           $task->dueDateTime, DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH, null);
                return ZurmoHtml::tag('span', array('class' => 'task-due-date'), $content);
            }
        }

        protected function resolveAndRenderTaskCardDetailsStatusContent(Task $task, $row)
        {
            $statusContent = $this->renderCardDataContent($this->cardColumns['status'], $task, $row);
            if($statusContent != null)
            {
                $content  = ZurmoHtml::openTag('div', array('class' => 'task-action-toolbar pillbox'));
                $content .= $this->renderCardDataContent($this->cardColumns['status'], $task, $row);
                $content .= ZurmoHtml::closeTag('div');
                return $content;
            }
        }

        protected function resolveAndRenderTaskCardDetailsSubscribersContent(Task $task)
        {
            $content         = null;
            $subscribedUsers = TasksUtil::getTaskSubscribers($task);
            foreach($subscribedUsers as $user)
            {
                if($user->isSame($task->owner))
                {
                    $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 20, 'task-owner');
                }
            }
            foreach($subscribedUsers as $user)
            {
                if(!$user->isSame($task->owner))
                {
                    $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 20);
                }
            }
            return $content;
        }
    }
?>
