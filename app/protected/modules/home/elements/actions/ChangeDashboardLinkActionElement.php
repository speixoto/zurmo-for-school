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

    class ChangeDashboardLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'Details';
        }

        public function render()
        {
            $menuItems = array('label' => $this->getDefaultLabel(), 'items' => array());
            foreach($this->getDashboardsData() as $dashboardData)
            {
                $menuItems['items'][] = array('label' => $dashboardData['name'],
                                               'url'   => Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/dashboardDetails/',
                                                   array('id' => $dashboardData['id'])));
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("DetailsOptionMenu");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'ChangeDashboardsMenu', 'class'   => 'icon-change-dashboard'),
                'items'                   => array($menuItems),
                'navContainerClass'       => 'nav-single-container',
                'navBarClass'             => 'nav-single-bar',
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['DetailsOptionMenu'];
        }

        protected function getDefaultLabel()
        {
            return Yii::t('Default', 'Change Dashboard');
        }

        protected function getDefaultRoute()
        {
            return null;
        }

        protected function getDashboardsData()
        {
            if (isset($this->params['dashboardsData']))
            {
                return $this->params['dashboardsData'];
            }
        }

    }
?>