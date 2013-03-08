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

    /**
     * Class used by reporting detail view user interface to show a options button that when clicked has a dropdown
     * of links to click
     */
    class ReportOptionsLinkActionElement extends LinkActionElement
    {
        protected $showEdit   = true;

        protected $showDelete = true;

        public function setHideEdit()
        {
            $this->showEdit = false;
        }

        public function setHideDelete()
        {
            $this->showDelete = false;
        }

        /**
         * @return string
         */
        public function getActionType()
        {
            return 'Delete';
        }

        /**
         * @return string
         */
        public function render()
        {
            $menuItems = array('label' => $this->getLabel(), 'url' => null, 'items' => array());

            if($this->showEdit)
            {
                $menuItems['items'][] = array('label' => Zurmo::t('ReportsModule', 'Edit'),
                                                 'url'   => Yii::app()->createUrl($this->getEditRoute(),
                                                                                  array('id' => $this->modelId)));
            }

            if($this->showDelete)
            {
                $menuItems['items'][] = array('label'       => Zurmo::t('ReportsModule', 'Delete'),
                                              'url'         => Yii::app()->createUrl($this->getDeleteRoute(),
                                                                                     array('id' => $this->modelId)),
                                              'linkOptions' =>
                                                array('confirm' =>
                                                    Zurmo::t('ReportsModule', 'Are you sure you want to delete this report?')));
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'ListViewOptionsActionMenu'),
                'items'                   => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Options');
        }

        /**
         * @return null
         */
        protected function getDefaultRoute()
        {
            return null;
        }

        /**
         * @return string
         */
        protected function getEditRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/edit/';
        }

        /**
         * @return string
         */
        protected function getDeleteRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/delete/';
        }
    }
?>