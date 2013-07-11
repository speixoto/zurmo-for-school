<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class TaskDetailsView extends DetailsView
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
            $content  = '<div class="details-table">';
            $content .= $this->renderTitleContent();
            $content .= $this->resolveAndRenderActionElementMenu();
            $leftContent  = $this->renderBeforeFormLayoutForDetailsContent();
            $leftContent .= $this->renderFormLayout();
            $content .= ZurmoHtml::tag('div', array('class' => 'left-column'), $leftContent);
            $content .= $this->renderRightSideContent();
            $content .= $this->renderAfterRightSideContent();
            $content .= '</div>';
            $content .= $this->renderAfterDetailsTable();
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
    }
?>