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
     * Class to render link to mass delete from a listview.
     */
    class MassDeleteLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'MassDelete';
        }

       public function render()
        {
            $gridId         = $this->getListViewGridId();
            $selectedName   = $gridId . '-deleteMassActionSelected';
            $allName        = $gridId . '-deleteMassActionAll';
            Yii::app()->clientScript->registerScript($gridId . '-listViewDeleteMassActionSelected', "
                $('#" . $gridId . "-deleteMassActionSelected').unbind('click.action');
                $('#" . $gridId . "-deleteMassActionSelected').bind('click.action', function()
                    {
                        if ($('#" . $gridId . "-selectedIds').val() == '')
                        {
                            alert('" . Yii::t('Default', 'You must select at least one record') . "');
                            $(this).val('');
                            return false;
                        }
                        var options =
                        {
                            url     : $.fn.yiiGridView.getUrl('" . $gridId . "'),
                            baseUrl : '" . Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId) . "'
                        }
                        if (options.url.split( '?' ).length == 2)
                        {
                            options.url = options.baseUrl +'/'+ 'massDelete' + '?' + options.url.split( '?' )[1];
                        }
                        else
                        {
                            options.url = options.baseUrl +'/'+ 'massDelete';
                        }
                        addListViewSelectedIdsToUrl('" . $gridId . "', options);
                        var data = '' + 'massDelete=' + '&selectAll=&ajax=&" . $this->getPageVarName() . "=1'; " . // Not Coding Standard
                        "url = $.param.querystring(options.url, data);
                        window.location.href = url;
                        return false;
                    }
                );
            ");
            Yii::app()->clientScript->registerScript($gridId . '-listViewDeleteMassActionAll', "
                $('#" . $gridId . "-deleteMassActionAll').unbind('click.action');
                $('#" . $gridId . "-deleteMassActionAll').bind('click.action', function()
                    {
                        var options =
                        {
                            url     : $.fn.yiiGridView.getUrl('" . $gridId . "'),
                            baseUrl : '" . Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId) . "'
                        }
                        if (options.url.split( '?' ).length == 2)
                        {
                            options.url = options.baseUrl +'/'+ 'massDelete' + '?' + options.url.split( '?' )[1];
                        }
                        else
                        {
                            options.url = options.baseUrl +'/'+ 'massDelete';
                        }
                        var data = '' + 'massDelete=' + '&selectAll=1&ajax=&" . $this->getPageVarName() . "=1'; " . // Not Coding Standard
                        "url = $.param.querystring(options.url, data);
                        window.location.href = url;
                        return false;
                    }
                );
            ");
            $menuItems = array('label' => $this->getLabel(), 'url' => null,
                                    'items' => array(
                                        array(  'label'   => Yii::t('Default', 'Selected'),
                                                'url'     => '#',
                                                'itemOptions' => array( 'id'   => $selectedName)),
                                        array(  'label'   => Yii::t('Default', 'All Results'),
                                                'url'     => '#',
                                                'itemOptions' => array( 'id'   => $allName))));
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'ListViewDeleteMassActionMenu'),
                'items'                   => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        protected function getDefaultLabel()
        {
            return Yii::t('Default', 'Delete');
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }

        protected function getPageVarName()
        {
            if (!isset($this->params['pageVarName']))
            {
                throw new NotSupportedException();
            }
            return $this->params['pageVarName'];
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/massdelete/';
        }
    }
?>
