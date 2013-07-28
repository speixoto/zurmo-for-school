<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for rendering the pager content for the sample column data.  This is used by the import mapping
     * step to allow a user to toggle between different sample rows while deciding on the mappings to do.
     */
    class ImportDataProviderPagerUtil
    {
        public static function renderPagerAndHeaderTextContent(ImportDataProvider $dataProvider, $url)
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $currentPage = $dataProvider->getPagination()->getCurrentPage();
            $pageCount   = $dataProvider->getPagination()->getPageCount();
            $content = null;
            $content .= Zurmo::t('ImportModule', 'Sample Row');
            $previousStyle = null;
            if (!($currentPage > 0))
            {
                $previousStyle = 'display:none;';
            }
            $nextStyle     = null;
            if (!(($currentPage + 1) < $pageCount))
            {
                $nextStyle = 'display:none;';
            }
            $content .= '&#160;';
            $content .= self::renderAjaxLink('sample-column-header-previous-page-link', Zurmo::t('ImportModule', 'Previous'),
                                             $url, $dataProvider->getPagination()->pageVar, $currentPage, $previousStyle);
            $content .= '&#160;';
            $content .= self::renderAjaxLink('sample-column-header-next-page-link', Zurmo::t('ImportModule', 'Next'),
                                             $url, $dataProvider->getPagination()->pageVar, $currentPage + 2, $nextStyle);
            return $content;
        }

        protected static function renderAjaxLink($id, $label, $url, $pageVar, $page, $style)
        {
            assert('is_string($id)');
            assert('is_string($label)');
            assert('is_string($url)');
            assert('is_string($pageVar)');
            assert('is_int($page)');
            assert('is_string($style) || $style == null');
            $urlScript = 'js:$.param.querystring("' . $url . '", "' .
                         $pageVar . '=" + $(this).attr("href"))';
            // Begin Not Coding Standard
            return       ZurmoHtml::ajaxLink($label, $urlScript,
                         array('type' => 'GET',
                               'dataType' => 'json',
                               'success' => 'js:function(data){
                                $.each(data, function(key, value){
                                    $("#" + key).html(value);
                                });
                              }'),
                         array('id' => $id, 'href' => $page, 'style' => $style, 'class' => 'z-link'));
            // End Not Coding Standard
        }
    }
?>