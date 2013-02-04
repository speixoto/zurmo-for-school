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

    // TODO: @Shoaibi create a new class that has common functions between this and AuditEventsModalListLinkActionElement
    // TODO: @Shoaibi change this class's and AuditEventsModalListLinkActionElement's parent.
    // TODO: @Shoaibi needs test
    class UsersModalListLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'Details';
        }

        public function render()
        {
            return ZurmoHtml::ajaxLink($this->getLabel(), $this->getDefaultRoute(),
                $this->getAjaxLinkOptions(),
                $this->getHtmlOptions()
            );
        }

        public function renderMenuItem()
        {
            if (!empty($this->modelId) && $this->modelId > 0)
            {
                return array('label'           => $this->getLabel(),
                             'url'             => $this->getDefaultRoute(),
                             'linkOptions'     => $this->getHtmlOptions(),
                             'ajaxLinkOptions' => $this->getAjaxLinkOptions()
                );
            }
        }

        protected function getAjaxLinkOptions()
        {
            // TODO: @Shoaibi need transaltion strings for this
            $title = Zurmo::t('ZurmoModule', 'Users');
            return ModalView::getAjaxOptionsForModalLink($title);
        }

        protected function getDefaultLabel()
        {
            throw new NotSupportedException;
        }

        protected function getDefaultRoute()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/usersInRoleModalList/',
                                         array('id' => $this->modelId));
        }
    }
?>