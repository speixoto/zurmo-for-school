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
        public static $maxCount = 50;

        /**
         * Renders the table body.
         */
        public function renderTableBody()
        {
            $data        = $this->dataProvider->getData();
            $modelsCount = count($data);
            $columnsData = $this->resolveDataIntoKanbanColumns();
            $width       = 100 / count($columnsData);
            echo "<tbody>";
            echo "<tr><td id=\"kanban-holder\" class='". $this->selectedTheme . "'>";
            if ($modelsCount > static::$maxCount)
            {
                $this->renderOverMaxCountText();
            }
            elseif ($modelsCount > 0)
            {
                $counter = 0;
                echo "<div id=\"kanban-board\" class=\"clearfix\">";
                foreach ($columnsData as $attributeValue => $attributeValueAndData)
                {
                    echo '<div class="kanban-column" style="width:'.$width.'%;">'; // Not Coding Standard
                    echo "<div data-value='" . $attributeValue . "' class='droppable-dynamic-rows-container'>";
                    //Add header label
                    echo ZurmoHtml::tag('div', array('class' => 'column-header'), $this->resolveGroupByColumnHeaderLabel($attributeValue));
                    $listItems = '';
                    foreach ($attributeValueAndData as $row)
                    {
                        $listItems .= ZurmoHtml::tag('li',
                                                      array('class' => 'kanban-card item-to-place',
                                                            'data-id' => $this->dataProvider->data[$row]->id),
                                                      ZurmoHtml::tag('div', array(), $this->renderCardDetailsContent($row)));
                    }
                    echo ZurmoHtml::tag('ul', array(), $listItems);
                    $dropZone =  ZurmoHtml::tag('div', array('class' => 'drop-zone'), '');
                    echo ZurmoHtml::tag('div', array('class' => 'drop-zone-container'), $dropZone);
                    echo "</div>";
                    echo "</div>";
                    $counter++;
                }
                echo "</div>";
            }
            else
            {
                $this->renderEmptyText();
            }
            echo "</td></tr>";
            echo "</tbody>";
        }

        /**
         * @return array
         */
        protected function resolveDataIntoKanbanColumns()
        {
            $columnsData = $this->makeColumnsDataAndStructure();
            $rowCount = 1;
            foreach ($this->dataProvider->data as $row => $data)
            {
                //Create KanbanItem here
                $kanbanItem                     = new KanbanItem();
                $kanbanItem->type               = $this->resolveKanbanItemTypeForTaskStatus($data->status);
                $kanbanItem->task               = $data;
                $kanbanItem->kanbanRelatedItem  = $data->activityItems->offsetGet(0);
                $kanbanItem->order              = $rowCount;
                $kanbanItem->save();
                if (isset($columnsData[$kanbanItem->type]))
                {
                    $columnsData[$kanbanItem->type][] = $row;
                }
                $rowCount++;
            }
            return $columnsData;
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
            return $columnsData;
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
        protected function renderCardDetailsContent($row)
        {
            $cardDetails = null;
            foreach ($this->cardColumns as $cardData)
            {
                $content      = $this->evaluateExpression($cardData['value'], array('data' => $this->dataProvider->data[$row],
                                                                                    'offset' => ($this->getOffset() + $row)));
                $cardDetails .= ZurmoHtml::tag('span', array('class' => $cardData['class']), $content);
            }
            //$userUrl      = Yii::app()->createUrl('/users/default/details', array('id' => $this->dataProvider->data[$row]->owner->id));
            /*$cardDetails .= ZurmoHtml::link($this->dataProvider->data[$row]->owner->getAvatarImage(20), $userUrl,
                                            array('class' => 'opportunity-owner'));*/
            return $cardDetails;
        }
    }
?>
