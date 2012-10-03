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

    class StackedGridColumnBehavior extends CBehavior
    {
        /**
         * Designed to render cells as divs instead of tds.
         * @param integer $row the row number (zero-based)
         */
        public function renderStackedDataCell($row)
        {
            $data    = $this->owner->grid->dataProvider->data[$row];
            $options = $this->owner->htmlOptions;
            if ($this->owner->cssClassExpression !== null)
            {
                $class = $this->owner->evaluateExpression($this->owner->cssClassExpression,
                         array('row' => $row, 'data' => $data));
                if (isset($options['class']))
                {
                    $options['class'] .= ' ' . $class;
                }
                else
                {
                    $options['class'] = $class;
                }
            }
            ob_start();
            $this->owner->renderDataCellContentFromOutsideClass($row, $data);
            $content = ob_get_contents();
            ob_end_clean();
            if ($content != null && $content != $this->owner->grid->nullDisplay)
            {
                echo ZurmoHtml::openTag('div', $options);
                echo $content;
                echo '</div>';
            }
        }
    }
?>