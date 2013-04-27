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
     * Helper class to work with KanbanBoard views
     */
    class KanbanBoard
    {
        const GROUP_BY_ATTRIBUTE_VISIBLE_VALUES     = 'groupByAttributeVisibleValues';

        const SELECTED_THEME                        = 'selectedTheme';

        protected $model;

        protected $groupByAttribute;

        protected $groupByDataAndTranslatedLabels;

        protected $groupByAttributeVisibleValues;

        protected $selectedTheme;

        /**
         * @var boolean Whether a Kanban Board is in use, which means the user interface should display it
         */
        protected $active;

        /**
         * @var boolean. When toggling back to a grid view from a Kanban view, we need to explicity
         * tell the search logic to clear sticky data.
         */
        protected $clearSticky = false;

        /**
         * From the get array, if the groupByAttributeVisibleValues variable is present, retrieve and set into the
         * $searchModel.  Also resolves for the selectedTheme variable.
         * @param object $searchModel
         * @param string $getArrayName
         */
        public static function resolveKanbanBoardOptionsForSearchModelFromGetArray($searchModel, $getArrayName)
        {
            assert('$searchModel instanceof RedBeanModel || $searchModel instanceof ModelForm');
            assert('is_string($getArrayName)');
            if ($searchModel->getKanbanBoard() != null && !empty($_GET[$getArrayName]))
            {
                assert('$searchModel instanceof SearchForm');
                if(isset($_GET[$getArrayName][self::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]))
                {
                    if (!is_array($_GET[$getArrayName][self::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]))
                    {
                        $groupByAttributeVisibleValues = null;
                    }
                    else
                    {
                        $groupByAttributeVisibleValues = $_GET[$getArrayName][self::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES];
                        $searchModel->getKanbanBoard()->setIsActive();
                    }
                    $searchModel->getKanbanBoard()->setGroupByAttributeVisibleValues($groupByAttributeVisibleValues);
                }
                if(isset($_GET[$getArrayName][self::SELECTED_THEME]))
                {
                    if (!is_array($_GET[$getArrayName][self::SELECTED_THEME]))
                    {
                        $selectedTheme = null;
                    }
                    else
                    {
                        $selectedTheme = $_GET[$getArrayName][self::SELECTED_THEME];
                    }
                    $searchModel->getKanbanBoard()->setSelectedTheme($selectedTheme);
                }
            }
        }

        public static function getGridViewWidgetPath()
        {
            return 'application.core.kanbanBoard.widgets.KanbanBoardExtendedGridView';
        }

        /**
         * @param RedBeanModel $model
         * @param string $groupByAttribute
         * @throws NotSupportedException
         */
        public function __construct(RedBeanModel $model, $groupByAttribute)
        {
            $this->model            = $model;
            $this->groupByAttribute = $groupByAttribute;
            if(!$this->model->{$this->groupByAttribute} instanceof CustomField)
            {
                throw new NotSupportedException();
            }
            $this->groupByDataAndTranslatedLabels = $this->resolveGroupByDataAndTranslatedLabels();
            $this->groupByAttributeVisibleValues  = array_keys($this->groupByDataAndTranslatedLabels);
        }

        public function getIsActive()
        {
            return $this->active;
        }

        public function setIsActive()
        {
           $this->active  = true;
        }

        public function setIsNotActive()
        {
            $this->active = false;
        }

        public function getGridViewParams()
        {
            return array('groupByAttribute'               => $this->groupByAttribute,
                         'groupByAttributeVisibleValues'  => $this->groupByAttributeVisibleValues,
                         'groupByDataAndTranslatedLabels' => $this->groupByDataAndTranslatedLabels);
        }

        public function getGroupByAttributeVisibleValues()
        {
            return $this->groupByAttributeVisibleValues;
        }

        public function setGroupByAttributeVisibleValues($groupByAttributeVisibleValues)
        {
            assert('$groupByAttributeVisibleValues === null || is_array($groupByAttributeVisibleValues)');
            $this->groupByAttributeVisibleValues = $groupByAttributeVisibleValues;
        }

        public function getGroupByDataAndTranslatedLabels()
        {
            return $this->groupByDataAndTranslatedLabels;
        }

        public function getSelectedTheme()
        {
            return $this->selectedTheme;
        }

        public function setSelectedTheme($selectedTheme)
        {
            assert('is_string($selectedTheme) || $selectedTheme == null');
            $this->selectedTheme = $selectedTheme;
        }

        public function getThemeNamesAndLabels()
        {
            //todo:
            return array('' => 'None', 'todo1' => 'implement something', 'todo2' => 'implement something else');
        }

        public function setClearSticky()
        {
            $this->clearSticky = true;
        }

        public function getClearSticky()
        {
            return $this->clearSticky;
        }

        protected function resolveGroupByDataAndTranslatedLabels()
        {
            $dropDownModel = $this->model->{$this->groupByAttribute};
            return CustomFieldDataUtil::getDataIndexedByDataAndTranslatedLabelsByLanguage($dropDownModel->data, Yii::app()->language);
        }
}
?>