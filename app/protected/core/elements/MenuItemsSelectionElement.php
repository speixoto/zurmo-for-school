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
     * Element to renders two multi-select inputs representing available list view attributes that can be selected when
     * running a search and viewing a list.
     */
    class MenuItemsSelectionElement extends Element
    {
        protected function renderControlEditable()
        {
            $content      = $this->renderSelectionContent();
            $content      = ZurmoHtml::tag('div', array('class' => 'attributesContainer'), $content);
            return $content;
        }

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlNonEditable()
         */
        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * (non-PHPdoc)
         * @see Element::renderLabel()
         */
        protected function renderLabel()
        {
            return Zurmo::t('UsersModule', 'Menu Preferences');
        }

        protected function renderSelectionContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("TabMenuItemsList");
            $cClipWidget->widget('application.core.widgets.SortableCompareLists', array(
                'hasLeftSideBox'         => false,
                'rightSideId'            => $this->getEditableInputId(UserConfigurationForm::VISIBLE_AND_ORDERED_TAB_MENU_ITEMS),
                'rightSideName'          => $this->getEditableInputName(UserConfigurationForm::VISIBLE_AND_ORDERED_TAB_MENU_ITEMS),
                'rightSideValue'         => $this->model->selectedVisibleAndOrderedTabMenuItems,
                'rightSideData'          => $this->model->visibleAndOrderedTabMenuItems,
                'rightSideDisplayLabel'  => Zurmo::t('Core', 'Menu Items'),
                'formId'                 => $this->form->getId(),
                'allowSorting'           => true,
                'multiselectNavigationClasses' => 'multiselect-nav-updown',
            ));
            $cClipWidget->endClip();
            $cellsContent  = $cClipWidget->getController()->clips['TabMenuItemsList'];
            $content       = '<table class="user-menu-preferences">';
            $content      .= '<tbody>';
            $content      .= '<tr>';
            $content      .= $cellsContent;
            $content      .= '</tr>';
            $content      .= '</tbody>';
            $content      .= '</table>';
            return $content;
        }
    }
?>