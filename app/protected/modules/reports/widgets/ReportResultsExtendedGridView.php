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

    Yii::import('zii.widgets.grid.CGridView');

    /**
     * Extends the yii CGridView to provide additional functionality.
     * @see CGridView class
     */
    class ReportResultsExtendedGridView extends ExtendedGridView
    {
        public $expandableRows = false;

        public function renderTableBody()
        {
            $data = $this->dataProvider->getData();
            $n    = count($data);
            echo "<tbody>\n";

            if($n > 0)
            {
                for($row = 0; $row < $n; ++$row)
                {
                    $this->renderTableRow($row);
                    if($this->expandableRows)
                    {
                        $this->renderExpandableRow($this->dataProvider->data[$row]->getId());
                    }
                }
            }
            else
            {
                echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
                $this->renderEmptyText();
                echo "</td></tr>\n";
            }
            echo "</tbody>\n";
        }

        protected function renderExpandableRow($id)
        {
            echo '<tr style="display:none;"><td></td><td colspan="' . (count($this->columns) - 1) . '">';
            echo '<div class="drillDownContent" id="drillDownContentFor-' . $id . '"></div>';
            echo "</td></tr>\n";
        }
    }
?>
