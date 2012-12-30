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
    Yii::import('zii.widgets.grid.CGridColumn');

    class DrillDownColumn extends CGridColumn
    {
        public function init()
        {
            //todo: why are we using async false in the ajax call below?
            //todo: remove console.logs
            // Begin Not Coding Standard
            $script = <<<END
jQuery('.drillDownExpandAndLoadLink').live('click', function()
{
    $(this).hide();
    $(this).parent().find('.drillDownCollapseLink').first().show();
    $(this).parentsUntil('tr').parent().next().show();
    var loadDivId = $(this).parentsUntil('tr').parent().next().find('.drillDownContent').attr('id');
    console.log(loadDivId);
    console.log($(this).data('url'));
    $.ajax({
        url      : $(this).data('url'),
        async    : false,
        type     : 'GET',
        beforeSend : function(){ makeSmallLoadingSpinner(loadDivId);},
        success  : function(data)
        {
            jQuery('#' + loadDivId).html(data)
        },
        error : function()
        {
            //todo: error call
        }
    });
});
jQuery('.drillDownExpandLink').live('click', function()
{
    $(this).hide();
    $(this).parent().find('.drillDownCollapseLink').first().show();
    $(this).parentsUntil('tr').parent().next().show();
});
jQuery('.drillDownCollapseLink').live('click', function()
{
    $(this).hide();
    $(this).parent().find('.drillDownExpandLink').first().show();
    $(this).parentsUntil('tr').parent().next().hide();
});
END;
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $script);
        }

        /**
         * (non-PHPdoc)
         * @see CCheckBoxColumn::renderDataCellContent()
         */
        protected function renderDataCellContent($row, $data)
        {
            $dataParams               = array_merge(array('rowId' => $data->getId()),
                                                    $data->getDataParamsForDrillDownAjaxCall());
            $expandAndLoadLinkContent = ZurmoHtml::tag('span', array('class' => 'drillDownExpandAndLoadLink',
                                                                     'data-url' => $this->getDrillDownLoadUrl($dataParams)),
                                                                     'Drill Down & L');
            $expandLinkContent        = ZurmoHtml::tag('span', array('class' => 'drillDownExpandLink',
                                                                     'style' => "display:none;"), 'Drill Down');
            $collapseLinkContent      = ZurmoHtml::tag('span', array('class' => 'drillDownCollapseLink',
                                                                     'style' => "display:none;"), 'Hide');
            echo $expandAndLoadLinkContent . $expandLinkContent . $collapseLinkContent;
        }

        protected function getDrillDownLoadUrl(Array $dataParams)
        {
            return Yii::app()->createUrl('/reports/default/drillDownDetails/',
                   array_merge(GetUtil::getData(), $dataParams));
        }
    }
?>
