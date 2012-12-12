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

    class HomeDashboardPortletSelectionView extends View
    {
        protected $controllerId;
        protected $moduleId;
        protected $dashboardId;
        protected $uniqueLayoutId;

        public function __construct($controllerId, $moduleId, $dashboardId, $uniqueLayoutId)
        {
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->dashboardId    = $dashboardId;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        protected function renderContent()
        {
            $placedViewTypes = $this->getPlacedViewTypes();
            $content = '<ul>';
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module->isEnabled())
                {
                    $p = $module->getParentModule();
                    $viewClassNames = $module::getViewClassNames();
                    foreach ($viewClassNames as $className)
                    {
                        $viewReflectionClass = new ReflectionClass($className);
                        if (!$viewReflectionClass->isAbstract())
                        {
                            $portletRules = PortletRulesFactory::createPortletRulesByView($className);
                            if ($portletRules != null && $portletRules->allowOnDashboard())
                            {
                                if ($portletRules->allowMultiplePlacementOnDashboard() ||
                                   (!$portletRules->allowMultiplePlacementOnDashboard() &&
                                    !in_array($portletRules->getType(), $placedViewTypes)))
                                {
                                    $metadata = $className::getMetadata();
                                    $url = Yii::app()->createUrl($this->moduleId . '/defaultPortlet/add', array(
                                        'uniqueLayoutId' => $this->uniqueLayoutId,
                                        'dashboardId'    => $this->dashboardId,
                                        'portletType'    => $portletRules->getType(),
                                        )
                                    );
                                    $onClick = 'window.location.href = "' . $url . '"';
                                    $content .= '<li>';
                                    $title    = $metadata['perUser']['title'];
                                    MetadataUtil::resolveEvaluateSubString($title);
                                    $label    = '<span>\</span>' . $title;
                                    $content .= ZurmoHtml::link(Yii::t('Default', $label ), null, array('onclick' => $onClick));
                                    $content .= '</li>';
                                }
                            }
                        }
                    }
                }
            }
            $content .= '</ul>';
            return $content;
        }

        protected function getPlacedViewTypes()
        {
            $portlets        = Portlet::getByLayoutIdAndUserSortedById($this->uniqueLayoutId,
                                                                       Yii::app()->user->userModel->id);
            $placedViewTypes = array();
            foreach ($portlets as $portlet)
            {
                $placedViewTypes[] = $portlet->viewType;
            }
            return $placedViewTypes;
        }
    }
?>
