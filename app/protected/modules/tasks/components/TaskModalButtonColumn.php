<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Override class for ButtonColumn for ajaxlink button
     * @see CGridView class
     */
    class TaskModalButtonColumn extends ButtonColumn
    {
        /**
         * The url for redirection.
         * @var string
         */
        public $redirectUrl;
        /**
         * The id of the grid on which button column is used.
         * @var string
         */
        public $gridId;
        
        /**
         * Renders an ajaxlink button.
         * @param string $id the ID of the button
         * @param array $button the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
         * See {@link buttons} for more details.
         * @param integer $row the row number (zero-based)
         * @param mixed $data the data object associated with the row
         */
        protected function renderButton($id, $button, $row, $data)
        {
            if (isset($button['visible']) && !$this->evaluateExpression($button['visible'],
                    array('row' => $row, 'data' => $data)))
            {
                return;
            }
            $label = isset($button['label']) ? $button['label'] : $id;
            $url        = Yii::app()->custom->resolveTaskModalButtonColumnUrl($button, $row, $data);
            $options = isset($button['options']) ? $button['options'] : array();
            if (!isset($options['title']))
            {
                $options['title'] = $label;
            }
            echo Yii::app()->custom->resolveTaskModalButtonColumnLink($button, $label, $options, $url, $data);
        }
    }
?>