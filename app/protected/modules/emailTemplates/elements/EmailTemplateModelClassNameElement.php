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
     * Element for displaying the email template model class name options.
     */
    class EmailTemplateModelClassNameElement extends StaticDropDownFormElement
    {
        protected function renderLabel()
        {
            if ($this->form === null)
            {
                return $this->getFormattedAttributeLabel();
            }
            $id = $this->getIdForSelectInput();
            return $this->form->labelEx($this->model, $this->attribute, array('for' => $id));
        }

        protected function getDropDownArray()
        {
            return $this->getAvailableModelNamesArray();
        }

        protected function getAvailableModelNamesArray()
        {
            $modules = Module::getModuleObjects();
            $availableModels = array();
            foreach ($modules as $module)
            {
                $moduleClassName = get_class($module);
                if ($moduleClassName::canHaveContentTemplates() &&
                    RightsUtil::canUserAccessModule($moduleClassName, Yii::app()->user->userModel) &&
                    method_exists($moduleClassName, 'getPrimaryModelName'))
                {
                    try
                    {
                        $modelClassName = $moduleClassName::getPrimaryModelName();
                        if (!isset($availableModels[$modelClassName]))
                        {
                            $availableModels[$modelClassName] = $modelClassName::getModelLabelByTypeAndLanguage('Plural');
                        }
                        else
                        {
                        }
                    }
                    catch (NotSupportedException $e)
                    {
                    }
                }
                else
                {
                }
            }
            asort($availableModels);
            return $availableModels;
        }
    }
?>