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
     * Editable column for Zurmo
     */
    class phaZurmoEditColumn extends phaEditColumn
    {
        /**
         * Override to accept htmlEditDecorationOptions
         * @param int $row
         * @param mixed $data
         */
        protected function renderDataCellContent($row,$data)
        {
            $value = CHtml::value($data, $this->name);
            $valueId = $data->{$this->modelId};

            $this->htmlEditFieldOptions['itemId'] = $valueId;
            $fieldUID = $this->getViewDivClass();

            $this->htmlEditDecorationOptions['valueid'] = $valueId;
            $this->htmlEditDecorationOptions['id'] = $fieldUID.'-'.$valueId;
            if (isset($this->htmlEditDecorationOptions['class']))
            {
                $this->htmlEditDecorationOptions['class'] .= ' ' . $fieldUID;
            }
            else
            {
                $this->htmlEditDecorationOptions['class'] = $fieldUID;
            }

            echo CHtml::tag('div', $this->htmlEditDecorationOptions, $value);
            echo CHtml::openTag('div', array(
                'style' => 'display: none;',
                'id' => $this->getFieldDivClass() . $data->{$this->modelId},
            ));
            echo CHtml::textField($this->name.'[' . $valueId . ']', $value, $this->htmlEditFieldOptions);
            echo CHtml::closeTag('div');
        }

        /**
         * Override to fix grid-view class
         */
        public function init() {
            parent::init();

            $cs=Yii::app()->getClientScript();

            $liveClick ='
        phaACActionUrls["'.$this->grid->id.'"]="' . $this->buildActionUrl() . '";
        jQuery(".'. $this->getViewDivClass() . '").live("click", function(e){
            phaACOpenEditField(this, "' . $this->id . '");
            return false;
        });';

            $script ='
        var phaACOpenEditItem = 0;
        var phaACOpenEditGrid = "";
        var phaACActionUrls = [];
        function phaACOpenEditField(itemValue, gridUID, grid ) {
            phaACHideEditField( phaACOpenEditItem, phaACOpenEditGrid );
            var id   = $(itemValue).attr("valueid");

            $("#viewValue-" + gridUID + "-"+id).hide();
            $("#field-" + gridUID + "-" + id).show();
            $("#field-" + gridUID + "-" + id+" input")
                .focus()
                .keydown(function(event) {
                    switch (event.keyCode) {
                       case 27:
                          phaACHideEditField( phaACOpenEditItem, gridUID );
                       break;
                       case 13:
                          phaACEditFieldSend( itemValue );
                       break;
                       default: break;
                    }
                });

            phaACOpenEditItem = id;
            phaACOpenEditGrid = gridUID;
        }
        function phaACHideEditField( itemId, gridUID ) {
            var clearVal = $("#viewValue-" + gridUID + "-"+itemId).text();
            $("#field-" + gridUID + "-" + itemId+" input").val( clearVal );
            $("#field-" + gridUID + "-" + itemId).hide();
            $("#field-" + gridUID + "-" + itemId+" input").unbind("keydown");
            $("#viewValue-" + gridUID + "-" + itemId).show();
            phaACOpenEditItem=0;
            phaACOpenEditGrid = "";
        }
        function phaACEditFieldSend( itemValue ) {
            var id = $(itemValue).parents(".cgrid-view").attr("id");
            $.ajax({
                type: "POST",
                dataType: "json",
                cache: false,
                url: phaACActionUrls[id],
                data: {
                    item: phaACOpenEditItem,
                    value: $("#field-"+phaACOpenEditGrid+"-"+phaACOpenEditItem+" input").val()
                },
                success: function(data){
                  $("#"+id).yiiGridView.update( id );
                }
            });
        }
        ';

            $cs->registerScript(__CLASS__.'#active_column-edit', $script);
            $cs->registerScript(__CLASS__.$this->grid->id.'#active_column_click-'.$this->id, $liveClick);
        }
    }
?>