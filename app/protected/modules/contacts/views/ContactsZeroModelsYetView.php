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
     * Class for showing a message and create link when there are no contacts visible to the logged in user when
     * going to the contacts list view.
     */
    class ContactsZeroModelsYetView extends ZeroModelsYetView
    {
        protected function getCreateLinkDisplayLabel()
        {
            return Yii::t('Default', 'Create ContactsModuleSingularLabel', LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getMessageContent()
        {
            return Yii::t('Default', '<h2>"Skill is fine, and genius is splendid, but the right Contacts are more ' .
                                     'valuable than either."</h2><i>- Arthur Conan Doyle, Sr.</i><div class="large-icon"></div><p>Arthur ' .
                                     'understands the value of having Contacts in the CRM and it takes little skill ' .
                                     'or genius to create one. Why not be the first to do so?</p>');
        }
    }
?>
