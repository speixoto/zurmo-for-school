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
     * Class for showing a message and create link when there are no leads visible to the logged in user when
     * going to the leads list view.
     */
    class LeadsZeroModelsYetView extends ZeroModelsYetView
    {
        protected function getCreateLinkDisplayLabel()
        {
            return Yii::t('Default',
                          'Create LeadsModuleSingularLabel',
                          LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getMessageContent()
        {
            return Yii::t('Default', '<h2>"Luke, don\'t give in to hate - that Leads to the dark side."</h2> ' .
                                     '<i>Obi Wan</i><br/>Luke received sage advice from his Jedi mentor on how ' .
                                     'important Leads are in the CRM. Turn away from the dark side and use the ' .
                                     'force to create the first Lead record.');
        }

        /**
         * While the model is still a contact, the image should show a lead.
         * (non-PHPdoc)
         * @see ZeroModelsYetView::getIconName()
         */
        protected function getIconName()
        {
            return 'lead-large-icon';
        }
    }
?>
