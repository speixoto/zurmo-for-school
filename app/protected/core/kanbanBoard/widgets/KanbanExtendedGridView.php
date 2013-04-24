<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Extends the StackedExtendedGridView to provide a 'stacked' Kanban Board format for viewing lists of data.
     */
    class KanbanExtendedGridView extends StackedExtendedGridView
    {
        /**
         * @var string
         */
        public $groupByAttribute;

        /**
         * @var array
         */
        public $groupByAttributeVisibleValues = array();

        /**
         * @var array
         */
        public $groupByDataAndTranslatedLabels = array();

        /**
         * Renders the table body.
         */
        public function renderTableBody()
        {
            $data        = $this->dataProvider->getData();
            $n           = count($data);
            $columnsData = $this->resolveDataIntoKanbanColumns();


            echo "<tbody>\n";
            echo "<tr><td>\n";

            if ($n > 0)
            {
                echo "<div>\n";
                foreach($columnsData as $attributeValue => $attributeValueAndData)
                {
                    echo "<div>\n";
                    echo ZurmoHtml::tag('div', array(), $this->resolveGroupByColumnHeaderLabel($attributeValue));
                    //todo: need to make sortable , we aren't using widget so this can be problematic.
                    //todo: swap all these declarations with ZurmoHtml::tag and maybe also in stacked Extended do the same thing?
                    echo "<ul>\n";
                    foreach($attributeValueAndData as $row)
                    {
                        echo "<li>\n";
                        //todo: i could pass to juisortable maybe.
                        //todo: i don't really want sortable, i want just droppable i guess.
                        $this->renderRowAsTableCellOrDiv($row, self::ROW_TYPE_DIV);
                        echo "</li>\n";
                    }
                    echo "</ul>\n";
                    echo "</div>\n";
                }
                echo "</div>\n";
            }
            else
            {
                //todo: figure out. should look nice
                $this->renderEmptyText();

            }
            //todO: also kill paging since it is not needed. well we might need the pager for refreshing not sure.
            echo "</td></tr>\n";
            echo "</tbody>\n";
        }

        protected function resolveGroupByColumnHeaderLabel($value)
        {
            if(isset($this->groupByDataAndTranslatedLabels[$value]))
            {
                return $this->groupByDataAndTranslatedLabels[$value];
            }
            return $value;
        }

        protected function resolveDataIntoKanbanColumns()
        {
            $columnsData = $this->makeColumnsDataAndStructure();
            foreach($this->dataProvider->data as $row => $data)
            {
                if(isset($columnsData[$data->{$this->groupByAttribute}->value]))
                {
                    $columnsData[$data->{$this->groupByAttribute}->value][] = $row;
                }
            }
            return $columnsData;
        }

        protected function makeColumnsDataAndStructure()
        {
            $columnsData = array();
            foreach($this->groupByAttributeVisibleValues as $value)
            {
                $columnsData[$value] = array();
            }
            return $columnsData;
        }
    }
?>
