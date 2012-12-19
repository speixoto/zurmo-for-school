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

    class FooterView extends View
    {
        protected function renderContent()
        {
            $userInterfaceTypeSelectorHtml = $this->renderUserInterfaceTypeSelector();
            $copyrightHtml = Yii::t('Default', 'Copyright &#169; Zurmo Inc., 2012. All Rights reserved.');

            return $userInterfaceTypeSelectorHtml . $copyrightHtml;
        }

        /**
         * Render section for selection user interface type.
         * Show only if user is using mobile and tablet devices.
         */
        protected function renderUserInterfaceTypeSelector()
        {
            $content = '';
            $htmlOptions = array('class' => 'ui-chooser');
            if (Yii::app()->userInterface->getDefaultUserInterfaceType() != UserInterface::DESKTOP)
            {
                if (Yii::app()->userInterface->getSelectedUserInterfaceType() == UserInterface::DESKTOP)
                {
                    if (Yii::app()->userInterface->getDefaultUserInterfaceType() == UserInterface::MOBILE)
                    {
                        $content = ZurmoHtml::link(Yii::t('Default', 'Show mobile'),
                                                   Yii::app()->createUrl('zurmo/default/userInterface',
                                                                         array('userInterface' => UserInterface::MOBILE)),
                                                   $htmlOptions);
                    }
                    elseif (Yii::app()->userInterface->getDefaultUserInterfaceType() == UserInterface::TABLET)
                    {
                        $content = ZurmoHtml::link(Yii::t('Default', 'Show tablet'),
                                                   Yii::app()->createUrl('zurmo/default/userInterface',
                                                                         array('userInterface' => UserInterface::TABLET)),
                                                   $htmlOptions);
                    }
                }
                else
                {
                    $content = ZurmoHtml::link(Yii::t('Default', 'Full Site'),
                                               Yii::app()->createUrl('zurmo/default/userInterface',
                                                                     array('userInterface' => UserInterface::DESKTOP)),
                                               $htmlOptions);
                }
            }
            return $content;
        }
    }
?>
