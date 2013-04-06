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
     * Class to render link user configuration
     */
    class MashableInboxCreateActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'Create';
        }

        public function render()
        {
            $mashableInboxModels = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $items = array();
            foreach ($mashableInboxModels as $modelClassName => $modelLabel)
            {
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
                if ($mashableUtilRules->shouldRenderCreateAction)
                {
                    $items[] = array('label'   => $modelClassName,
                                     'url'     => $this->getRouteForItem($modelClassName));
                }
            }
            $menuItems = array('label' => $this->getLabel(),
                               'url'   => null,
                               'items' => $items);
            if (!empty($items))
            {
                $cClipWidget = new CClipWidget();
                $cClipWidget->beginClip("ActionMenu");
                $cClipWidget->widget('application.core.widgets.MbMenu', array(
                    'htmlOptions' => array('id' => 'MashableInboxCreateDropdown'),
                    'items'       => array($menuItems),
                ));
                $cClipWidget->endClip();
                return $cClipWidget->getController()->clips['ActionMenu'];
            }
            return null;
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('MashableInboxModule', 'Create');
        }

        protected function getDefaultRoute()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/');
        }

        private function getRouteForItem($modelClassName)
        {
            $moduleClassName = $modelClassName::getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            return Yii::app()->createUrl($moduleId . '/' . $this->controllerId . '/create');
        }
    }
?>