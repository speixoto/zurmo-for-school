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
     * Class to render link for MashableInboxModels
     */
    class MashableInboxModelActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return null;
        }
        
        public function render()
        {
            $menuItems   = $this->renderMenuItem();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.DividedMenu', array(
                'items'       => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        public function renderMenuItem()
        {
            return array('label'               => $this->getMenuHeader(), 
                         'url'                 => $this->getDefaultRoute(),
                         'htmlOptions'         => $this->getHtmlOptions(),
                         'itemOptions'         => array('iconClass' => $this->getIconClass()),                         
                         'dynamicLabel'        => $this->getUnreadCount(),
                         'items'               => $this->getMenuItems());
        }

        protected function getMenuItems()
        {
            if ($this->getModelClassName() === null)
            {
                return null;
            }
            $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($this->getModelClassName());
            if ($mashableUtilRules->shouldRenderCreateAction)
            {
                return array(array('label'     => $this->getDefaultLabel(),                                   
                                   'url'       => $this->getRouteForItem($this->getModelClassName())));
            }            
        }

        protected function getMenuHeader()
        {
            return $this->getLabel();
        }        

        protected function getDefaultLabel()
        {
            return Zurmo::t('MashableInboxModule', 'Create');
        }

        protected function getDefaultRoute()
        {
            if ($this->getModelClassName() === null)
            {
                return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/');
            }
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/list',
                                        array('modelClassName' => $this->getModelClassName()));
        }
        
        protected function getModelClassName()
        {
            if (!isset($this->params['modelClassName']))
            {
                return null;
            }
            return $this->params['modelClassName'];
        }

        private function getRouteForItem($modelClassName)
        {
            $moduleClassName = $modelClassName::getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            return Yii::app()->createUrl($moduleId . '/' . $this->controllerId . '/create');
        }
        
        protected function getUnreadCount()
        {
            if (!isset($this->params['unread']))
            {
                return null;
            }            
            return ZurmoHtml::wrapLabel($this->params['unread'], 'unread-count');
        }
        
        protected function getIconClass()
        {
            if (!isset($this->params['iconClass']))
            {
                return null;
            }
            return $this->params['iconClass'];
        }
    }
?>
