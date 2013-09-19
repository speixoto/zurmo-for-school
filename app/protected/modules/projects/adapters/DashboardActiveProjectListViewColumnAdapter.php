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
     * Column adapter for account for product list in portlet
     */
    class DashboardActiveProjectListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'DashboardActiveProjectListViewColumnAdapter::getProjectInformationForDashboard($data)',
                    'type'  => 'raw'
                );
        }

        /**
         * Make search attribute data
         * @param array $data
         * @return string
         */
        protected static function makeSearchAttributeData($data)
        {
            $searchAttributeData['clauses'][1] =
            array(
                'attributeName'        => 'activityItems',
                'relatedAttributeName' => 'id',
                'operatorType'         => 'equals',
                'value'                => (int)$data->getClassId('Item')
            );
            $searchAttributeData['structure'] = '(1)';
            return $searchAttributeData;
        }

        /**
         * Get active project information for dashboard
         * @param array $data
         * @return string
         */
        public static function getProjectInformationForDashboard($data)
        {
            $content = '<h4>' . ZurmoHtml::link($data->name, Yii::app()->createUrl('/projects/default/details', array('id' => $data->id))) . '</h4>' . '<table>';
            $searchAttributeData = self::makeSearchAttributeData($data);
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Task');
            $where  = RedBeanModelDataProvider::makeWhere('Task', $searchAttributeData, $joinTablesAdapter);
            $models = Task::getSubset($joinTablesAdapter, null, null, $where, null);

            if(count($models) > 0)
            {
                $kanbanItemsArray = array();
                $kanbanItemsCountArray = array();
                $totalToDosCount = 0;
                $completedTodosCount = 0;
                foreach ($models as $data)
                {
                    $totalToDosCount += count($data->checkListItems);
                    if(count($data->checkListItems) != 0)
                    {
                        $completedTodosCount += TasksUtil::getTaskCompletedCheckListItems($data);
                    }
                    $kanbanItem  = KanbanItem::getByTask($data->id);
                    if($kanbanItem == null)
                    {
                        //Create KanbanItem here
                        $kanbanItem = TasksUtil::createKanbanItemFromTask($data);
                    }

                    $kanbanItemsArray[$kanbanItem->type] = $kanbanItem->id;
                }
                if($totalToDosCount != 0)
                {
                    $completionPercent = ($completedTodosCount/$totalToDosCount)*100;
                }
                else
                {
                    $completionPercent = 0;
                }
                $kanbanTypeDropDownData = KanbanItem::getTypeDropDownArray();
                //todo:@Mayank The following content creation would change based on amit's design
                $content .= '<tr>';
                foreach($kanbanTypeDropDownData as $type => $label)
                {
                    $content .= '<th>' . $label . '</th>';
                }
                $content .= '<th>' . Zurmo::t('ProjectsModule', '% Complete') . '</th>';
                $content .= '</tr><tr>';
                foreach($kanbanTypeDropDownData as $type => $label)
                {
                    if(isset($kanbanItemsArray[$type]))
                    {
                        $content .= '<td>' . count($kanbanItemsArray[$type]) . '</td>';
                    }
                    else
                    {
                        $content .= '<td>0</td>';
                    }
                }
                $content .= '<td>' . $completionPercent . '</td>';
                $content .= '</tr></table>';
            }
            else
            {
                $content .= '<tr><td colspan="5">' . Zurmo::t('ProjectsModule','No Tasks') . '</td></tr></table>';
            }
            return $content;
        }
    }
?>