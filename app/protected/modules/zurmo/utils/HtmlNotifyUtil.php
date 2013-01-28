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
     * Wrapper class to render highlight or alert notify html content given a message.
     */
    class HtmlNotifyUtil
    {
        public static function renderHighlightBoxByMessage($message)
        {
            assert('$message != null && is_string($message)');
            $content   = ZurmoHtml::wrapLabel('&#160', 'ui-icon ui-icon-info') . $message;
            $innerContent = ZurmoHtml::tag('p', array(), $content );
            $innerDiv  = ZurmoHtml::tag('div', array('class' => 'ui-state-highlight ui-corner-all'), $innerContent );
            return ZurmoHtml::tag('div', array('class' => 'ui-widget'), $innerDiv );
        }

        public static function renderAlertBoxByMessage($message)
        {
            assert('$message != null && is_string($message)');
            $alertMessage = '<strong>' . Yii::t('Default', 'Alert') . ':</strong> ' . $message ;
            $content   = ZurmoHtml::wrapLabel('&#160', 'ui-icon ui-icon-alert') . $alertMessage;
            $innerContent = ZurmoHtml::tag('p', array(), $content );
            $innerDiv  = ZurmoHtml::tag('div', array('class' => 'ui-state-highlight ui-corner-all'), $innerContent );
            return ZurmoHtml::tag('div', array('class' => 'ui-widget'), $innerDiv );
        }

        public static function renderWarningBoxByMessage($message)
        {
            assert('$message != null && is_string($message)');
            $content   = ZurmoHtml::wrapLabel('&#160', 'ui-icon ui-icon-info') . $message;
            $innerContent = ZurmoHtml::tag('p', array(), $content );
            $innerDiv  = ZurmoHtml::tag('div', array('class' => 'ui-state-warning ui-corner-all'), $innerContent );
            return ZurmoHtml::tag('div', array('class' => 'ui-widget'), $innerDiv );
        }
    }
?>