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
            $columnsData = $this->makeColumnsDataAndStructure();

            $kanbanItemsArray = array();

            foreach ($this->dataProvider->data as $row => $data)
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
         * @param array $row
         * @return string
         */
        protected function renderCardDetailsContentForTask($data, $row)
        {
            $cardDetails = null;
            foreach ($this->cardColumns as $cardData)
            {
                $offset       = $this->getOffset() + $row;
                $content      = $this->evaluateExpression($cardData['value'], array('data' => $data, 'offset' => $offset));
                $cardDetails .= ZurmoHtml::tag('span', array('class' => $cardData['class']), $content);
            }
            return $cardDetails;
        }

        /**
         * Creates ul tag for kanban column
         * @param array $listItems
         * @param string $attributeValue
         * @return string
         */
        protected function renderUlTagForKanbanColumn($listItems, $attributeValue = null)
        {
            return ZurmoHtml::tag('ul id="task-sortable-rows-' . $attributeValue . '" class="connectedSortable"' , array(), $listItems);
        }

        /**
         * Override script registration
         */
        protected function registerScripts()
        {
            $taskSortableScript = "
                        var fixHelper = function(e, ui) {
                            ui.children().each(function() {
                                $(this).width($(this).width());
                            });
                            return ui;
                        };";

            Yii::app()->clientScript->registerScript('task-sortable-data-helper', $taskSortableScript);
        }

        /**
         * Register Kanban Column Scripts
         */
        protected function registerKanbanColumnScripts()
        {
            $taskSortableScript = "";
            $columnDataKeys = array_keys($this->columnsData);
            for($count=0; $count < count($this->columnsData); $count++)
            {
                 $type = $columnDataKeys[$count];
                 if($type != KanbanItem::TYPE_COMPLETED)
                 {
                     $taskSortableScript .= $this->registerKanbanColumnSortableScript($count + 1, $type);
                 }
                 else
                 {
                     $taskSortableScript .= $this->registerKanbanColumnSortableScript($count + 1, $type);
                 }
            }

            Yii::app()->clientScript->registerScript('task-sortable-data', $taskSortableScript);
            $url = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView', array());
            $this->registerKanbanColumnStartActionScript(Zurmo::t('TasksModule', 'Finish'), Task::TASK_STATUS_IN_PROGRESS, $url);
            $this->registerKanbanColumnFinishActionScript(Zurmo::t('TasksModule', 'Accept'), Zurmo::t('TasksModule', 'Reject'), Task::TASK_STATUS_AWAITING_ACCEPTANCE, $url);
            $this->registerKanbanColumnAcceptActionScript('', Task::TASK_STATUS_COMPLETED, $url);
            $this->registerKanbanColumnRejectActionScript(Zurmo::t('TasksModule', 'Start'), Task::TASK_STATUS_NEW, $url);
            TasksUtil::registerSubscriptionScript();
            TasksUtil::registerUnsubscriptionScript();
        }

        /**
         * Registers kanban column sortable script
         * @param int $count
         * @param int $type
         * @return string
         */
        protected function registerKanbanColumnSortableScript($count, $type)
        {
            return "$('#task-sortable-rows-" . $count . "').sortable({
                                                forcePlaceholderSize: true,
                                                forceHelperSize: true,
                                                items: 'li:not(.ui-state-disabled)',
                                                connectWith: '.connectedSortable',
                                                update : function (event, ui) {
                                                    var id = $(ui.item).attr('id');
                                                    var idParts = id.split('_');
                                                    var taskId = parseInt(idParts[1]);
                                                    serial = $('#task-sortable-rows-" . $count . "').sortable('serialize', {key: 'items[]', attribute: 'id'});
                                                    //serial = serial + '&taskId=' + taskId;
                                                    console.log(serial);
                                                    $.ajax({
                                                        'url': '" . Yii::app()->createUrl('tasks/default/updateStatusOnDragInKanbanView', array('type'=> $type)) . "',
                                                        'type': 'get',
                                                        'data': serial,
                                                        'dataType' : 'json',
                                                        'success': function(data){
                                                            if(data.hasOwnProperty('button'))
                                                            {
                                                                if(data.button != '')
                                                                {
                                                                    $(ui.item).find('.task-status').html(data.button);
                                                                }
                                                                else
                                                                {
                                                                    console.log($(ui.item).find('.task-status'));
                                                                    $(ui.item).find('.task-status').remove();
                                                                }
                                                            }
                                                        },
                                                        'error': function(request, status, error){
                                                            alert('We are unable to set the sort order at this time.  Please try again in a few minutes.');
                                                        }
                                                    });
                                                },
                                                helper: fixHelper
                                            }).disableSelection();
                                        ";
        }

        /**
         * Registers kanban column start action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnStartActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('task-start-action', KanbanItem::TYPE_IN_PROGRESS, $label, 'task-finish-action', $url, $targetStatus);

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
            $script = "$('.task-finish-action').click(
                                                    function()
                                                    {
                                                        var element = $(this).parent().parent().parent();
                                                        var ulelement = $(element).parent();
                                                        var id = $(element).attr('id');
                                                        var idParts = id.split('_');
                                                        var taskId = parseInt(idParts[1]);
                                                        var rejectLinkElement = $(this).clone();
                                                        var parent = $(this).parent();
                                                        $(this).find('.z-label').html('" . $labelAccept . "');
                                                        $(this).removeClass('task-finish-action').addClass('task-accept-action');
                                                        $(rejectLinkElement).appendTo($(parent));
                                                        $(rejectLinkElement).find('.z-label').html('" . $labelReject . "');
                                                        $.ajax(
                                                            {
                                                                type : 'GET',
                                                                data : {'targetStatus':" . Task::TASK_STATUS_AWAITING_ACCEPTANCE . ", 'taskId':taskId},
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
                                                      ZurmoHtml::tag('div', array(), $this->renderCardDetailsContentForTask($data, $row)));
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
        protected function registerButtonActionScript($buttonClass, $targetKanbanItemType, $label, $targetButtonClass, $url, $targetStatus)
        {
            return "$('." . $buttonClass . "').click(
                                                    function()
                                                    {
                                                        var element = $(this).parent().parent().parent();
                                                        var ulelement = $(element).parent();
                                                        var id = $(element).attr('id');
                                                        var ulid = $(ulelement).attr('id');
                                                        var ulidParts = ulid.split('-');
                                                        var idParts = id.split('_');
                                                        var taskId = parseInt(idParts[1]);
                                                        var columnType = parseInt(ulidParts[3]);
                                                        $('#task-sortable-rows-" . $targetKanbanItemType . "').append(element);
                                                        $('#task-sortable-rows-' + columnType).remove('#' + id);
                                                        if(" . $targetStatus . " != " . Task::TASK_STATUS_COMPLETED . ")
                                                        {
                                                            var addedElement = $('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' ." . $buttonClass . "');
                                                            $(addedElement).find('.z-label').html('" . $label . "');
                                                            $(addedElement).removeClass('" . $buttonClass . "').addClass('" . $targetButtonClass . "');
                                                            if('" . $buttonClass . "' == 'task-reject-action')
                                                            {
                                                                $('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' .task-accept-action').remove();
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $('#task-sortable-rows-" . $targetKanbanItemType . " #' + id + ' .task-status').remove();
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
            $script = $this->registerButtonActionScript('task-accept-action', KanbanItem::TYPE_COMPLETED, $label, 'task-complete-action ui-state-disabled', $url,$targetStatus);
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
            $script = $this->registerButtonActionScript('task-reject-action', KanbanItem::TYPE_TODO, $label, 'task-start-action', $url, $targetStatus);
            Yii::app()->clientScript->registerScript('reject-action-script', $script);
        }

        /**
         * Wraps card details content
         * @param int $row
         * @return string
         */
        protected function wrapCardDetailsContent($row)
        {
            return ZurmoHtml::tag('div', array('style' => 'height:90px'), $this->renderCardDetailsContent($row));
        }
    }
?>