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

    class ComponentWithTreeForReportWizardView extends ComponentForReportWizardView
    {
        public static function getTreeType()
        {
            throw new NotImplementedException();
        }

        public static function getTreeDivId()
        {
            return static::getTreeType() . 'TreeArea';
        }

        protected function renderFormContent()
        {
            $content              = $this->renderAttributesAndRelationsTreeContent();
            $rowCount             = 0;

            //todo: render existing rows...

            $idInputHtmlOptions   = array('id' => $this->getRowCounterInputId());
            $hiddenInputName      = static::getTreeType() . 'RowCounter';
            $content             .= ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);

            $content             .= ZurmoHtml::tag('div', array(), $this->renderRightSideContent());
            return $content;
        }

        protected function renderRightSideContent()
        {
            $content     = ZurmoHtml::tag('div', array('class' => 'droppable-attributes-container ' .
                                                                           static::getTreeType()), 'todo: drop here');
            $content    .= ZurmoHtml::tag('div', array('class' => 'attribute-rows'), 'some message');
            return $content;
        }

        protected function getRowCounterInputId()
        {
            return static::getTreeType() . 'RowCounter';
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $script = '
                $(".droppable-attributes-container.' . static::getTreeType() . '").live("drop",function(event, ui){
                    ' . $this->getAjaxForDroppedAttribute() . '
                });
                $(".remove-report-attribute-row-link").live("click", function()
                    {
                        $(this).parent().remove();
                        ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                    }
                );
            ';
            Yii::app()->getClientScript()->registerScript(static::getTreeType() . 'ReportComponentForTreeScript', $script);
        }

        protected function getAddAttributeUrl()
        {
            return  Yii::app()->createUrl('reports/default/addAttributeFromTree',
                        array_merge($_GET, array('treeType'                   => static::getTreeType())));
        }

        protected function getAjaxForDroppedAttribute()
        {
            return ZurmoHtml::ajax(array(
                    'type'     => 'POST',
                    'data'     => 'js:$("#' . $this->form->getId() . '").serialize()',
                    'url'      => 'js:$.param.querystring("' .
                                  $this->getAddAttributeUrl() .
                                  '", "nodeId=" + ui.helper.attr("id") + "&rowNumber="  + $(\'#' .
                                  $this->getRowCounterInputId(). '\').val())',
                    'beforeSend' => 'js:function(){
                       // attachLoadingSpinner("' . $this->form->getId() . '", true, "dark"); - add spinner to block anything else
                    }',
                    'success' => 'js:function(data){
                    $(\'#' . $this->getRowCounterInputId(). '\').val(parseInt($(\'#' . $this->getRowCounterInputId() . '\').val()) + 1);
                    $(".droppable-attributes-container.' . static::getTreeType() . '").parent().find(".attribute-rows").append(data);
                    ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                    //attachLoadingSpinner("' . $this->form->getId() . '", false); - remove spinner
                }'
            ));
        }

        protected function getReportAttributeRowAddOrRemoveExtraScript()
        {
        }
    }
?>