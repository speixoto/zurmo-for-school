<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Extends the StackedExtendedGridView to provide a 'stacked' Kanban Board format for viewing lists of data.
     */
    class KanbanBoardExtendedGridView extends StackedExtendedGridView
    {
        public static $maxCount = 50;

        /**
         * Override since Kanban Board does not support pagination. It would always show all available regardless of
         * pagination.
         * @var bool
         */
        public $enablePagination = false;

        /**
         * @var string
         */
        public $groupByAttribute;

        /**
         * @var array
         */
        public $groupByAttributeVisibleValues = array();

        /**
         * @var array
         */
        public $groupByDataAndTranslatedLabels = array();

        public $cardColumns = array();

        /**
         * Need to grab one more than max count to check if we are over the max so we can properly display a message
         * @return int
         */
        public static function resolvePageSizeForMaxCount()
        {
            return static::$maxCount + 1;
        }

        public function init()
        {
            $this->registerScripts();
            parent::init();
        }

        /**
         * Renders the table body.
         */
        public function renderTableBody()
        {
            $data        = $this->dataProvider->getData();
            $n           = count($data);
            $columnsData = $this->resolveDataIntoKanbanColumns();
            $width       = ' style="width:' . 100 / count($columnsData) . '%;"';
            echo "<tbody>\n";
            echo "<tr><td id=\"kanban-holder\">\n";
            if ($n > static::$maxCount)
            {
                $this->renderOverMaxCountText();
            }
            elseif($n > 0)
            {
                echo "<div id=\"kanban-board\">\n";
                foreach($columnsData as $attributeValue => $attributeValueAndData)
                {
                    echo "<div class=\"kanban-column\" $width>\n";
                    echo "<div data-value='" . $attributeValue . "' class='droppable-dynamic-rows-container'>\n";
                    echo ZurmoHtml::tag('div', array('class' => 'column-header'), $this->resolveGroupByColumnHeaderLabel($attributeValue));
                    //todo: swap all these declarations with ZurmoHtml::tag and maybe also in stacked Extended do the same thing?
                    echo "<ul>\n";
                    foreach($attributeValueAndData as $row)
                    {
                        echo "<li data-id='" . $this->dataProvider->data[$row]->id . "' class='kanban-card item-to-place'>\n";
                        //Amit's stuff from here
                        echo '<div>'; // we need this to wrap everything
                        echo $this->renderCardDetailsContent($row);
                        echo '<div class="hidden-content">';
                        echo '<a href="#" onclick="$(this).next().fadeToggle(); return false;">Toggle Details</a>';
                        $this->renderRowAsTableCellOrDiv($row, self::ROW_TYPE_DIV);
                        echo '</div>';
                        echo '</div>';
                        //End Amit's stuff
                        echo "</li>\n";
                    }
                    echo "</ul>\n";
                    $dropZone =  ZurmoHtml::tag('div', array('class' => 'drop-zone'), '');
                    echo ZurmoHtml::tag('div', array('class' => 'drop-zone-container'), $dropZone);
                    echo "</div>\n";
                    echo "</div>\n";
                }
                echo "</div>\n";
            }
            else
            {
                $this->renderEmptyText();

            }
            echo "</td></tr>\n";
            echo "</tbody>\n";
        }

        /**
         * Renders the empty message when there is no data.
         */
        public function renderOverMaxCountText()
        {
            $label = Zurmo::t('Core', 'There are too many results to display. Try filtering your search or switching to the grid view.');
            echo CHtml::tag('span', array('class'=>'empty'), $label);
        }

        protected function getOffset()
        {
            $pagination = $this->dataProvider->getPagination();
            if (isset($pagination))
            {
                $offset = $pagination->getOffset();
            }
            else
            {
                $offset = 0;
            }
            return $offset;
        }


        protected function resolveGroupByColumnHeaderLabel($value)
        {
            if(isset($this->groupByDataAndTranslatedLabels[$value]))
            {
                return $this->groupByDataAndTranslatedLabels[$value];
            }
            return $value;
        }

        protected function resolveDataIntoKanbanColumns()
        {
            $columnsData = $this->makeColumnsDataAndStructure();
            foreach($this->dataProvider->data as $row => $data)
            {
                if(isset($columnsData[$data->{$this->groupByAttribute}->value]))
                {
                    $columnsData[$data->{$this->groupByAttribute}->value][] = $row;
                }
            }
            return $columnsData;
        }

        protected function makeColumnsDataAndStructure()
        {
            $columnsData = array();
            foreach($this->groupByAttributeVisibleValues as $value)
            {
                $columnsData[$value] = array();
            }
            return $columnsData;
        }

        protected function registerScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.kanbanBoard.widgets.assets')) . '/KanbanUtils.js');
            $script = '
                $(".droppable-dynamic-rows-container").live("drop", function(event, ui)
                {
                   ' . $this->getAjaxForDroppedAttribute() . '
                   $("ul", this).append(ui.draggable);
                });
            ';
            Yii::app()->getClientScript()->registerScript('KanbanDragDropScript', $script);
        }

        /**
         * @return string
         */
        protected function getAjaxForDroppedAttribute()
        {
            return ZurmoHtml::ajax(array(
                'type'     => 'GET',
                'url'      => 'js:$.param.querystring("' . $this->getUpdateAttributeValueUrl() .
                              '", "id=" + ui.helper.attr("id") + "&value=" + $(this).data("value"))',
                'beforeSend' => 'js:function()
                    {
                        $(".ui-overlay-block").fadeIn(50);
                        makeLargeLoadingSpinner(true, ".ui-overlay-block"); //- add spinner to block anything else
                    }',
                'success' => 'js:function(data)
                    {
                        makeLargeLoadingSpinner(false, ".ui-overlay-block");
                        $(".ui-overlay-block").fadeOut(50);
                    }'
            ));
        }

        /**
         * @return string
         */
        protected function getUpdateAttributeValueUrl()
        {
            $modelClassName  = $this->dataProvider->getModelClassName();
            $moduleClassName = $modelClassName::getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            return  Yii::app()->createUrl($moduleId . '/default/updateAttributeValue', array('attribute' => $this->groupByAttribute));
        }

        protected function renderCardDetailsContent($row)
        {
            $cardDetails = null;
            foreach($this->cardColumns as $cardData)
            {
                $content      = $this->evaluateExpression($cardData['value'], array('data' => $this->dataProvider->data[$row],
                                                                                    'offset' => ($this->getOffset() + $row)));
                $cardDetails .= ZurmoHtml::tag('span', array('class' => $cardData['class']), $content);

            }
            $userUrl      = Yii::app()->createUrl('/users/default/details', array('id' => $this->dataProvider->data[$row]->owner->id));
            $cardDetails .= ZurmoHtml::link($this->dataProvider->data[$row]->owner->getAvatarImage(36), $userUrl,
                                            array('class' => 'opportunity-owner'));
            return $cardDetails;
        }
    }
?>
