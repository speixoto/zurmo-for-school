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
                $kanbanItem  = KanbanItem::getKanbanItemForTask($data->id);
                if($kanbanItem == null)
                {
                    //Create KanbanItem here
                    $kanbanItem                     = new KanbanItem();
                    $kanbanItem->type               = $this->resolveKanbanItemTypeForTaskStatus($data->status);
                    $kanbanItem->task               = $data;
                    $kanbanItem->kanbanRelatedItem  = $data->activityItems->offsetGet(0);
                    $sortOrder = KanbanItem::getMaximumSortOrderByType($kanbanItem->type);
                    $kanbanItem->sortOrder          = $sortOrder;
                    $kanbanItem->save();
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

        protected static function getTaskStatusMappingToKanbanItemTypeArray()
        {
            return array(
                            Task::TASK_STATUS_NEW                   => KanbanItem::TYPE_TODO,
                            Task::TASK_STATUS_IN_PROGRESS           => KanbanItem::TYPE_IN_PROGRESS,
                            Task::TASK_STATUS_AWAITING_ACCEPTANCE   => KanbanItem::TYPE_COMPLETED,
                            Task::TASK_STATUS_REJECTED              => KanbanItem::TYPE_IN_PROGRESS,
                            Task::TASK_STATUS_COMPLETED             => KanbanItem::TYPE_COMPLETED
                        );
        }

        protected function resolveKanbanItemTypeForTaskStatus($status)
        {
            if($status == null)
            {
                return KanbanItem::TYPE_TODO;
            }
            $data = self::getTaskStatusMappingToKanbanItemTypeArray();
            return $data[$status];
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
            //$userUrl      = Yii::app()->createUrl('/users/default/details', array('id' => $this->dataProvider->data[$row]->owner->id));
            /*$cardDetails .= ZurmoHtml::link($this->dataProvider->data[$row]->owner->getAvatarImage(20), $userUrl,
                                            array('class' => 'opportunity-owner'));*/
            return $cardDetails;
        }

        protected function createUlTagForKanbanColumn($listItems, $counter = null)
        {
            return ZurmoHtml::tag('ul id="task-sortable-rows-' . $counter . '"' , array(), $listItems);
        }

        protected function registerScripts()
        {
            //parent::registerScripts();

            $taskSortableScript = "
                        var fixHelper = function(e, ui) {
                            ui.children().each(function() {
                                $(this).width($(this).width());
                            });
                            return ui;
                        };";

            Yii::app()->clientScript->registerScript('task-sortable-data-helper', $taskSortableScript);
        }

        protected function registerKanbanColumnScripts()
        {
            //parent::registerScripts();

            $taskSortableScript = "";

            for($count=0; $count < count($this->columnsData); $count++)
            {
                 $taskSortableScript .= "$('#task-sortable-rows-" . $count . "').sortable({
                                            forcePlaceholderSize: true,
                                            forceHelperSize: true,
                                            items: 'li',
                                            update : function () {
                                                serial = $('#task-sortable-rows-" . $count . "').sortable('serialize', {key: 'items[]', attribute: 'id'});
                                                $.ajax({
                                                    'url': '" . Yii::app()->createUrl('tasks/default/updateItemsSortInKanbanView') . "',
                                                    'type': 'get',
                                                    'data': serial,
                                                    'success': function(data){
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

            Yii::app()->clientScript->registerScript('task-sortable-data', $taskSortableScript);
        }

        protected function createKanbanRowForKanbanColumn($data, $row)
        {
            return ZurmoHtml::tag('li', array('class' => $this->getRowClassForKanbanColumn(),
                                                'id' => 'items_' . $data->id),
                                                      ZurmoHtml::tag('div', array(), $this->renderCardDetailsContentForTask($data, $row)));
        }

        protected function getListItemsByAttributeValueAndData($attributeValueAndData)
        {
            $listItems = '';
            foreach ($attributeValueAndData as $key => $data)
            {
                $listItems .= $this->createKanbanRowForKanbanColumn($data, $key + 1);
            }

            return $listItems;
        }
    }
?>
