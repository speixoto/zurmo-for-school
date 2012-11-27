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
     * Element used by user configuration to select a theme color
     */
    class ThemeColorElement extends Element
    {
        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $content = null;
            $content .= $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                Yii::app()->themeManager->getThemeColorNamesAndLabels(),
                $this->getEditableHtmlOptions()
            );
            $this->registerScript();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Clear out html options for 'empty' since it is not applicable for a rado dropdown.
         * @see DropDownElement::getEditableHtmlOptions()
         */
        protected function getEditableHtmlOptions()
        {
            $htmlOptions             = array();
            $htmlOptions['template'] =  '<div class="radio-input color-swatch {value}">{input}<span class="theme-color-1">' .
                                        '</span><span class="theme-color-2"></span>' .
                                        '<span class="theme-color-3"></span>{label}</div>';
            return $htmlOptions;
        }

        public function registerScript()
        {
            $removeScript = null;
            foreach (Yii::app()->themeManager->getThemeColorNamesAndLabels() as $value => $notUsed)
            {
                $removeScript .= '$(document.body).removeClass("' . $value . '");' . "\n";
            }
            // Begin Not Coding Standard
            $script = "$('input[name=\"" . $this->getEditableInputName() . "\"]').live('change', function(){
                          $removeScript
                          $(document.body).addClass(this.value);
                          });
                      ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('changeThemeColor', $script);
        }
    }
?>
