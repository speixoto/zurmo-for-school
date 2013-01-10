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

    abstract class ComponentWithTreeForReportWizardView extends ComponentForReportWizardView
    {
        abstract protected function getItems(& $rowCount);

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
            $content             .= ZurmoHtml::tag('div', array('class' => 'dynamic-droppable-area'), $this->renderRightSideContent());
            return $content;
        }

        protected function renderRightSideContent()
        {
            $rowCount             = 0;

            $items                = $this->getItems($rowCount);
            if($this->isListContentSortable())
            {
                $itemsContent         = $this->getSortableListContent($items);
            }
            else
            {
                $itemsContent         = $this->getNonSortableListContent($items);
            }
            $idInputHtmlOptions   = array('id' => $this->getRowCounterInputId());
            $hiddenInputName      = static::getTreeType() . 'RowCounter';
            $attributeRows        = ZurmoHtml::tag('div', array('class' => 'attribute-rows'), $itemsContent);
            $content              = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            $content             .= ZurmoHtml::tag('div', array('class' => 'droppable-attributes-container ' .
                                                                           static::getTreeType()), $attributeRows);
            $content             .= ZurmoHtml::tag('div', array('class' => 'drop-zone'), 'Todo: Drop Here');
            return $content;
        }

        protected function renderItems(& $rowCount, $componentData, $trackableStructurePosition = false)
        {
            assert('is_int($rowCount)');
            assert('is_array($componentData)');
            assert('is_bool($trackableStructurePosition)');
            $items                      = array();
            $wizardFormClassName        = get_class($this->model);
            foreach($componentData as $component)
            {
                $nodeIdWithoutTreeType      = $component->attributeIndexOrDerivedType;
                $inputPrefixData            = ReportRelationsAndAttributesToTreeAdapter::
                                              resolveInputPrefixData($nodeIdWithoutTreeType, $wizardFormClassName,
                                              $this->getTreeType(), $rowCount);
                $adapter                    = new ReportAttributeToElementAdapter($inputPrefixData, $component,
                                              $this->form, $this->getTreeType());
                $view                       = new AttributeRowForReportComponentView($adapter,
                                              $rowCount, $inputPrefixData,
                                              ReportRelationsAndAttributesToTreeAdapter::
                                              resolveAttributeByNodeId($nodeIdWithoutTreeType),
                                              (bool)$trackableStructurePosition);
                $view->addWrapper           = false;
                $items[]                    = array('content' => $view->render());
                $rowCount ++;
            }
            return $items;
        }

        protected function getNonSortableListContent(Array $items)
        {
            $content = null;
            foreach($items as $item)
            {
                $content .= ZurmoHtml::tag('li', array(), $item['content']);
            }
            return ZurmoHtml::tag('ul', array(), $content);
        }

        protected function getSortableListContent(Array $items)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip(static::getTreeType() . 'ReportComponentSortable');
            $cClipWidget->widget('application.core.widgets.JuiSortable', array(
                'items' => $items,
                'itemTemplate' => '<li>content</li>',
                'htmlOptions' =>
                array(
                    'id'    => static::getTreeType() . 'attributeRowsUl',
                    'class' => 'sortable',
                ),
                'options' => array(
                    'placeholder' => 'ui-state-highlight',
                ),
                'showEmptyList' => false
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips[static::getTreeType() . 'ReportComponentSortable'];
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
               
                $(".attribute-to-place").live("dblclick",function(event){
                    var treeType = "'.static::getTreeType().'";
                    if(treeType === "Filters"){
                        ' . $this->getAjaxForDoubleClickedAttribute() . '
                    }
                });
                $(".remove-dynamic-attribute-row-link").live("click", function()
                    {
                        $(this).parent().parent().remove(); //removes the <li>
                        ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                    }
                );
            ';
            Yii::app()->getClientScript()->registerScript(static::getTreeType() . 'ReportComponentForTreeScript', $script);
        }

        protected function getAddAttributeUrl()
        {
            return  Yii::app()->createUrl('reports/default/addAttributeFromTree',
                        array_merge($_GET, array('type'     => $this->model->type,
                                                 'treeType' => static::getTreeType())));
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
                    $(".droppable-attributes-container.' . static::getTreeType() . '").parent().find(".attribute-rows").find("ul").append(data);
                    ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                    //attachLoadingSpinner("' . $this->form->getId() . '", false); - remove spinner
                }'
            ));
        }
        
        protected function getAjaxForDoubleClickedAttribute()
        {
            return ZurmoHtml::ajax(array(
                    'type'     => 'POST',
                    'data'     => 'js:$("#' . $this->form->getId() . '").serialize()',
                    'url'      => 'js:$.param.querystring("' . $this->getAddAttributeUrl() . '",
                                        "nodeId=" + event.currentTarget.id + "&rowNumber=" + $(\'#' . $this->getRowCounterInputId(). '\').val())',
                    'beforeSend' => 'js:function(){
                       // attachLoadingSpinner("' . $this->form->getId() . '", true, "dark"); - add spinner to block anything else
                    }',
                    'success' => 'js:function(data){
                        $(\'#' . $this->getRowCounterInputId(). '\').val(parseInt($(\'#' . $this->getRowCounterInputId() . '\').val()) + 1);
                        $(".droppable-attributes-container.' . static::getTreeType() . '").parent().find(".attribute-rows").find("ul").append(data);
                        ' . $this->getReportAttributeRowAddOrRemoveExtraScript() . '
                        //attachLoadingSpinner("' . $this->form->getId() . '", false); - remove spinner
                }'
            ));
        }

        protected function getReportAttributeRowAddOrRemoveExtraScript()
        {
        }

        protected function isListContentSortable()
        {
            return false;
        }
    }
?>