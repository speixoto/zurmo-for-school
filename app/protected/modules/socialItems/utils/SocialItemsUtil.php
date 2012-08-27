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
     * Helper class for social module processes
     */
    class SocialItemsUtil
    {
        /**
         * Renders and returns string content of summary content for the given model.
         * @param RedBeanModel $model
         * @param mixed $redirectUrl
         * @param string $ownedByFilter
         * @param string $viewModuleClassName
         * @return string content
         */
        public static function renderItemAndCommentsContent(SocialItem $model, $redirectUrl)
        {
            assert('is_string($redirectUrl) || $redirectUrl == null');
            $content  = '<div class="social-item">';
            //todo: use user's avatar (owner)
            $content .= '<em class="'.get_class($model).'"></em>';
            $content .= '<strong>'. DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                    $model->latestDateTime, 'long', null) . '</strong><br/>';
            //todo: -why do we pass renderItemAndCommentsContent(SocialItem $model, $redirectUrl) here? oh many for comments?
            //Render display content
            $x = $model->description;
            //still need to figure out
            //todo: files too (extra render check how this is done below for notes.
            $content .= '<span>' . strval($model->owner) . ' ' . $x . '</span>';
            $content .= '</div>';
            return $content;
        }
    }
?>