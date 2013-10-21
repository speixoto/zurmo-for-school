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
     * A view that displays check list items for a task
     *
     */
    class TaskCheckListItemsForTaskView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $checkListItemsData;

        protected $relatedModel;

        protected $pageSize;

        protected $getParams;

        protected $uniquePageId;

        protected $form;

        public function __construct($controllerId, $moduleId, $checkListItemsData, Item $relatedModel, $form, $getParams, $uniquePageId = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($checkListItemsData)');
            assert('$relatedModel->id > 0');
            assert('is_string($form) || $form == null');
            assert('is_array($getParams)');
            assert('is_string($uniquePageId) || $uniquePageId == null');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->checkListItemsData     = $checkListItemsData;
            $this->relatedModel           = $relatedModel;
            $this->getParams              = $getParams;
            $this->uniquePageId           = $uniquePageId;
        }

        /**
         * Gets id for the list
         * @return string
         */
        protected function getId()
        {
            return 'TaskCheckListItemsForTaskView' . $this->uniquePageId;
        }

        /**
         * Renders content of the list
         * @return string
         */
        protected function renderContent()
        {
            $content = null;
            $content = '<div>' . $this->renderHiddenRefreshLinkContent() . '</div>';
            if (count($this->checkListItemsData) > 0)
            {
                $content .= ZurmoHtml::tag('ul', array('id' => 'TaskCheckListItems' . $this->uniquePageId,
                                                        'class' => 'taskcheckitemslist'), $this->renderCheckListItemsContent());
            }
            $this->registerCheckBoxEventHandlerScript();
            $this->registerSortableScript();
            return $content;
        }

        /**
         * Renders hidden link which refresh the list on entering new comment
         * @return string
         */
        protected function renderHiddenRefreshLinkContent()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/ajaxCheckItemListForRelatedTaskModel',
                            $this->getParams);
            return       ZurmoHtml::ajaxLink('Refresh', $url,
                         array('type' => 'GET',
                               'success' => 'function(data){$("#TaskCheckListItemsForTaskView' . $this->uniquePageId . '").replaceWith(data)}'),
                         array('id'         => 'hiddenCheckListItemRefresh'. $this->uniquePageId,
                                'class'     => 'hiddenCheckListItemRefresh',
                                'namespace' => 'refresh',
                                'style'     => 'display:none;'));
        }

        /**
         * Renders check list
         * @return string
         */
        protected function renderCheckListItemsContent()
        {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.tasks.elements.assets')) . '/TaskUtils.js',
                CClientScript::POS_END);
            $content  = null;
            $rows = 0;
            $data = array();
            foreach ($this->checkListItemsData as $checkListItem)
            {
                if($checkListItem->completed == null || $checkListItem->completed == 0)
                {
                    $checked = false;
                }
                else
                {
                    $checked = true;
                }
                $checkBox = ZurmoHtml::checkBox('TaskCheckListItem_' . $checkListItem->id, $checked,
                                                array('class' => 'checkListItem', 'value' => $checkListItem->id));
                $itemContent = ZurmoHtml::tag('span', array('class' => 'editable',
                                                            'id' => 'checkListItem_' . $checkListItem->id),
                                                            $checkBox . '<p>' . $checkListItem->name . '</p>');
                $itemContent .= $this->renderHiddenEditableTextField($checkListItem->id, $checkListItem->name);
                $content .= ZurmoHtml::tag('li', array('class' => 'check-list-item clearfix',
                                                       'id' => 'checkListItemContainer_' . $checkListItem->id
                                                       ),
                                                       $itemContent);
            }
            $this->registerCheckItemClickScript($checkListItem->id);
            //$this->registerUpdateCheckListItemScript();
            return $content;
        }

        private function registerCheckItemClickScript()
        {
            $url = Yii::app()->createUrl('/tasks/taskCheckItems/updateNameViaAjax');
            $errorMessage = Yii::t('Core', 'Name can not be blank');
            Yii::app()->getClientScript()->registerScript('checklistitemclick', "
                                                                $('li.check-list-item').click(function()
                                                                    {
                                                                        $(this).find('.task-input').show();
                                                                        $(this).find('.task-input').find('input').focus();
                                                                        $(this).find('.editable').hide();
                                                                   });
                                                                var inputElement = $(this).find('.task-input');

                                                                $('div.task-input').find('input')
                                                                .keydown(function(event)
                                                                {
                                                                    id = $(this).attr('id');
                                                                    idParts = id.split('_');
                                                                    switch (event.keyCode)
                                                                    {
                                                                       case 27:
                                                                       //case 9:
                                                                       case 13:
                                                                                updateCheckListItem(idParts[2], '{$url}', '{$errorMessage}');
                                                                                break;
                                                                       default: break;
                                                                    }
                                                                })
                                                                .blur(function()
                                                                {
                                                                    updateCheckListItem(idParts[2], '{$url}', '{$errorMessage}');
                                                                })
                                                                ;
                                                            ");
        }

        private function renderHiddenEditableTextField($id, $name)
        {
            $errorMessage = Yii::t('Core', 'Name can not be blank');
            $content = "<div class='task-input' id='checkItemEditableDiv_{$id}' style='display:none'>
                            <input id='TaskCheckListItem_name_{$id}' name='TaskCheckListItem[name][{$id}]' type='text' value='{$name}'>
                            <div class='errorMessage' id='TaskCheckListItem_name_{$id}_em_' style='display:none'>
                            {$errorMessage}
                            </div>
                    </div>";
            //$this->registerCheckListEditableItemScript($id);
            return $content;
        }

        private function registerUpdateCheckListItemScript()
        {
            $url = Yii::app()->createUrl('/tasks/taskCheckItems/updateNameViaAjax');
            $errorMessage = Yii::t('Core', 'Name can not be blank');
            Yii::app()->getClientScript()->registerScript('checklistupdateitem', "
                                                                function updateCheckListItem(id)
                                                                {
                                                                    var passedValue = $('#TaskCheckListItem_name_' + id).val();
                                                                    if(passedValue == '')
                                                                    {
                                                                        alert('$errorMessage');
                                                                    }
                                                                    $('#TaskCheckListItem_name_'+ id + '_em_').hide();
                                                                    $.ajax({
                                                                            type: 'GET',
                                                                            url: '{$url}',
                                                                            dataType: 'html',
                                                                            cache: false,
                                                                            data: {
                                                                                id  :id,
                                                                                name:passedValue
                                                                            },
                                                                            success: function(data){
                                                                              $('#checkListItem_' + id).find('p').html(data);
                                                                              $('#checkListItem_' + id).show();
                                                                              $('#checkItemEditableDiv_' + id).hide();
                                                                            }
                                                                        });
                                                                }
                                                            ");
        }

        /**
         * Register check box event handler script
         */
        protected function registerCheckBoxEventHandlerScript()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/updateStatusViaAjax');
            Yii::app()->clientScript->registerScript('checkListItemCheckboxClick',"
                                                      $('.checkListItem').change(function(){
                                                                $.ajax(
                                                                    {
                                                                        url : '" . $url . "?id=' + $(this).val(),
                                                                        type : 'GET',
                                                                        data : {
                                                                                    checkListItemCompleted : $(this).is(':checked')?1:0
                                                                               },
                                                                        dataType: 'json',
                                                                        success : function(data)
                                                                        {
                                                                            console.log('success');
                                                                        },
                                                                        error : function()
                                                                        {

                                                                        }
                                                                    }
                                                                 );
                                                          });
                                                      ", CClientScript::POS_END);
        }

        /**
         * @return bool
         */
        public function isUniqueToAPage()
        {
            return false;
        }

        /**
         * Register sortable script
         */
        protected function registerSortableScript()
        {
            $url     =   Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/updateStatusViaAjax');
            Yii::app()->clientScript->registerScript('checklistitemsSortablescript',"
                                                                $('.taskcheckitemslist').sortable({
                                                                 items: 'li'
                                                          });
                                                      ");
        }
    }
?>