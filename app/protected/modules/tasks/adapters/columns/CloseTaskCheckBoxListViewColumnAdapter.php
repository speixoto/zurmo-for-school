<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class CloseTaskCheckBoxListViewColumnAdapter extends CheckBoxListViewColumnAdapter
    {
        /**
         * @param string $checkboxId
         * @param RedBeanModel $modelId
         * @param bool $completedValue
         * @return string
         */
        public static function renderCloseCheckBox($checkboxId, $modelId, $completedValue)
        {
            $htmlOptions = array('class'    => 'close-task-checkbox',
                                 'onclick'  => "closeOpenTaskByCheckBoxClick('" . $checkboxId . "', '" . $modelId . "')");
            if ($completedValue == true)
            {
                $htmlOptions['disabled']   = 'disabled';
                $htmlOptions['labelClass'] = 'disabled';
                Yii::app()->clientScript->registerScript('closeTaskCheckBoxScriptStartingState' . $checkboxId, "
                    $('#" . $checkboxId ."').parentsUntil('tr').parent().children().css('text-decoration', 'line-through');
                ", CClientScript::POS_END);
            }
            return ZurmoHtml::checkBox($checkboxId, $completedValue, $htmlOptions);
        }

        /**
         * Returns grid view data array
         * @return array
         */
        public function renderGridViewData()
        {
            return array(
                'name'        => $this->attribute,
                'header'      => Zurmo::t('Core', 'Close'),
                'value'       => $this->resolveToRenderCheckBox('Task', '$data->' . 'id', '$data->completed'),
                'type'        => 'raw',
                'htmlOptions' => array('class' => 'checkbox-column')
            );
        }

        /**
         * @param string $modelClassName
         * @param int $modelId
         * @param bool $completedValue
         * @return string
         */
        protected function resolveToRenderCheckBox($modelClassName, $modelId, $completedValue)
        {
            if (!ActionSecurityUtil::canCurrentUserPerformAction( 'Edit', new $modelClassName(false)))
            {
                return '';
            }
            $checkboxId = 'closeTask' . $modelId;
            // Begin Not Coding Standard
            $content    = 'CloseTaskCheckBoxListViewColumnAdapter::renderCloseCheckBox("' .
                          $checkboxId . '", "' . $modelId . '", "' . $completedValue . '")';
            Yii::app()->clientScript->registerScript('closeTaskCheckBoxScript', "
                function closeOpenTaskByCheckBoxClick(checkboxId, modelId)
                {
                    if ($('#' + checkboxId).attr('checked') == 'checked')
                    {
                        $('#' + checkboxId).attr('disabled', true);
                        $('#' + checkboxId).parent().addClass('c_on');
                        $('#' + checkboxId).parent().addClass('disabled');
                        $('#' + checkboxId).parentsUntil('tr').parent().children().css('text-decoration', 'line-through');
                        $.ajax({
                            url : '" . Yii::app()->createUrl('tasks/default/closeTask') . "?id=' + modelId,
                            type : 'GET',
                            dataType : 'json',
                            success : function(data)
                            {
                                //find if there is a latest activities portlet
                                $('.LatestActivitiesForPortletView').each(function(){
                                    $(this).find('.pager').find('.refresh').find('a').click();
                                });
                            },
                            error : function()
                            {
                                //todo: error call
                            }
                        });
                    }
                }
            ", CClientScript::POS_END);
            // End Not Coding Standard
            return $content;
        }
        //todo make sure live actually works on paged tasks
    }
?>