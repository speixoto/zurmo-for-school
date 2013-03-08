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
     * Class to render link to export from a report results
     */
    class ReportExportLinkActionElement extends LinkActionElement
    {
        /**
         * @return string
         */
        public function getActionType()
        {
            return 'Export';
        }

        /**
         * @return string
         */
        public function render()
        {   
            Yii::app()->clientScript->registerScript( 'exportToCsv', "
                $('#exportToCsv').unbind('click.action');
                $('#exportToCsv').bind('click.action', function()
                    {
                        var options =
                        {
                            url     : $.fn.yiiGridView.getUrl('report-results-grid-view'),
                            baseUrl : '" . Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId) . "'
                        }
                        if (options.url.split( '?' ).length == 2)
                        {                            
                            options.url = options.baseUrl +'/'+ 'export' + '?' + options.url.split( '?' )[1];
                        }
                        else
                        {
                            options.url = options.baseUrl +'/'+ 'export';
                        }                                                                        
                        var data = '' + 'export=' + '&ajax='; " . // Not Coding Standard
                        "url = $.param.querystring(options.url, data);                         
                        window.location.href = url;
                        return false;
                    }
                );
            ");            
            $menuItems = array('label' => $this->getLabel(), 'url' => null,
                                    'items' => array(
                                        array(  'label'   => Zurmo::t('ReportsModule', 'to CSV'),
                                                'url'     => '#',
                                                'itemOptions' => array( 'id'   => 'exportToCsv'))));
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'ListViewExportActionMenu'),
                'items'                   => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];                                 
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Export');
        }

        /**
         * @return string
         */
        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/export/';
        }
    }
?>