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
     * Base view for rendering a list of groups the user is a
     * member of.
     */
    class UserGroupMembershipView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $groupMembership;

        protected $userId;

        public function __construct($controllerId, $moduleId, array $groupMembership, $userId, $title = null)
        {
            assert('is_string($controllerId) && $controllerId != null');
            assert('is_string($moduleId) && $moduleId != null');
            assert('is_int($userId) && $controllerId != null');
            assert('$title == null || is_string($title)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->groupMembership        = $groupMembership;
            $this->userId                 = $userId;
            $this->title                  = $title;
        }

        protected function renderContent()
        {
            $content  = '<div class="details-table">';
            $content .= $this->renderTitleContent();
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:100%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Yii::t('Default', 'Group') . '</th></tr>'; //<th></th>
            foreach ($this->groupMembership as $groupId => $information)
            {
                $content .= '<tr>';
                $content .= '<td>';
                $content .= $information['displayName'];
                $content .= '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            $content .= '</div>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return false;
        }
    }
?>