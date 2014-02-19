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

var emailTemplateEditor = {
    jQuery : $,
    settings : {
        getNewElementUrl: '',
        editElementUrl: '',
        iframeSelector: '#preview-template',
        editSelector: '',
        iframeOverlaySelector: '#iframe-overlay',
        elementsToPlaceSelector: '#building-blocks',
        sortableRowsSelector: '.sortable-rows',
        sortableElementsSelector: '.sortable-elements',
        editActionSelector: 'span.action-edit',
        moveActionSelector: 'span.action-move',
        deleteActionSelector: 'span.action-delete',
        cachedSerializedDataSelector: '#serialized-data-cache',
        ghost : '',
        alertErrorOnDelete: 'You cannot delete last row'
    },
    init : function (elementsToPlaceSelector, iframeSelector, editSelector, editActionSelector, moveActionSelector, deleteActionSelector,
                     iframeOverlaySelector, cachedSerializedDataSelector, editElementUrl, getNewElementUrl, alertErrorOnDelete) {
        this.settings.elementsToPlaceSelector = elementsToPlaceSelector;
        this.settings.iframeSelector          = iframeSelector;
        this.settings.editSelector            = editSelector;
        this.settings.editActionSelector      = editActionSelector;
        this.settings.moveActionSelector      = moveActionSelector;
        this.settings.deleteActionSelector    = deleteActionSelector;
        this.settings.iframeOverlaySelector   = iframeOverlaySelector;
        this.settings.cachedSerializedDataSelector  = cachedSerializedDataSelector;
        this.settings.editElementUrl          = editElementUrl;
        this.settings.getNewElementUrl        = getNewElementUrl;
        this.settings.alertErrorOnDelete      = alertErrorOnDelete;
        this.setupLayout();
        emailTemplateEditor = this;
    },
    setupLayout : function() {
        $(emailTemplateEditor.settings.iframeSelector).load(function () {
            contents = $(this).contents();

            $( contents.find('body') ).on( "click", emailTemplateEditor.settings.editActionSelector, emailTemplateEditor.onClickEditEvent);
            $( contents.find('body') ).on( "click", emailTemplateEditor.settings.deleteActionSelector, emailTemplateEditor.onClickDeleteEvent);

            contents.find(emailTemplateEditor.settings.sortableElementsSelector + ', ' + emailTemplateEditor.settings.sortableRowsSelector).on({
                mousemove: function(event) {
                    $(parent.document).trigger(event);
                },
                mouseup: function(event) {
                    $(parent.document).trigger(event);
                }
            });

            emailTemplateEditor.initDraggableElements(emailTemplateEditor.settings.elementsToPlaceSelector,
                emailTemplateEditor.settings.sortableElementsSelector + ", " + emailTemplateEditor.settings.sortableRowsSelector,
                contents);
            emailTemplateEditor.initSortableElements(emailTemplateEditor.settings.sortableElementsSelector,
                emailTemplateEditor.settings.sortableElementsSelector,
                contents);
            emailTemplateEditor.initSortableRows(emailTemplateEditor.settings.sortableRowsSelector, contents);
        });
    },
    initDraggableElements: function ( selector , connectToSelector, iframeContents) {
        $( selector ).each(function(){
            if ($(this).data('draggable')){
                $(this).draggable("destroy");
            }
        });

        var clone = '';
        var elementDraggedClass = '';
        var elementDragged;

        $('li', selector ).draggable({
            appendTo: 'body',
            cursor: 'move',
            iframeFix: true,
            revert: 'invalid',
            cursorAt: { left:  -20, top: -20 },
            helper: function(event, ui){
                elementDragged      = $(event.currentTarget),
                elementDraggedClass = $(event.currentTarget).data('class');
                clone = $('<div class="blox">' + $(event.currentTarget).html() + '</div>');
                return clone;
            }
        });

        var containers = [];
        var offset = {};
        var rect = {};
        var innerElements = [];
        var point = {};
        var i = 0;
        emailTemplateEditor.settings.ghost = $('<div class="ghost">drop here</div>');
        var isDragging = false;
        var mostTopElement;

        $('body').on('mousedown', function(event){
            offset = $(emailTemplateEditor.settings.iframeSelector).offset();
            containers = $(emailTemplateEditor.settings.iframeSelector).contents().find('.element-wrapper');
            isDragging = true;
        });

        $('body').on('mousemove', function doMouseMoveOnBody(event){
            if(isDragging === true){
                point.left = event.pageX - offset.left;
                point.top = event.pageY - offset.top;
                $(innerElements).each(function(){$(this).removeClass('hover');});
                innerElements = [];
                for (i = 0; i < containers.length; i++){
                    rect = containers[i].getBoundingClientRect();
                    if( point.left > rect.left && point.left < rect.right &&
                        point.top > rect.top && point.top < rect.bottom ){
                        innerElements.push(containers[i]);
                    }
                }
                if(innerElements.length > 0){
                    mostTopElement = innerElements[innerElements.length-1];
                    $(mostTopElement).addClass('hover');
                    if( point.top - $(mostTopElement).offset().top < $(mostTopElement).outerHeight(true) / 2 ){
                        $(mostTopElement).before(emailTemplateEditor.settings.ghost);
                    } else {
                        $(mostTopElement).after(emailTemplateEditor.settings.ghost);
                    }
                }
            }
        });

        $('body').on('mouseup', function(event){
            isDragging = false;
            if (elementDragged != undefined && elementDragged.is('li')){
                emailTemplateEditor.placeNewElement(elementDraggedClass, null, false);
            } else {
                console.log('error while droppping', elementDragged);
            }
        });
    },
    initSortableElements: function ( selector , connectToSelector, iframeContents) {
        $( iframeContents.find(selector) ).each(function(){
            if ($(this).data('sortable')) {
                $(this).sortable("destroy");
            }
        });
        $( iframeContents.find(selector) ).sortable({
            handle: emailTemplateEditor.settings.moveActionSelector,
            iframeFix: true,
            stop: function( event, ui ) {
                if (ui.item.is('li')) {
                    emailTemplateEditor.placeNewElement(ui.item.data("class"), ui.item, false);
                }
                emailTemplateEditor.canvasChanged();
            },
            cursorAt: { top: 0, left: 0 },
            cursor: 'move',
            connectWith: iframeContents.find(connectToSelector)
        });
    },
    initSortableRows: function ( selector , iframeContents) {
        $( iframeContents.find(selector) ).each(function(){
            if ($(this).data('sortable')){
                $(this).sortable("destroy");
            }
        });
        $( iframeContents.find(selector) ).sortable({
            handle: emailTemplateEditor.settings.moveActionSelector,
            iframeFix: true,
            stop: function( event, ui ) {
                if (ui.item.is('li')) {
                    ui.item.wrap(emailTemplateEditor.settings.rowWrapper);
                    emailTemplateEditor.placeNewElement(ui.item.data("class"), ui.item, true);
                    emailTemplateEditor.initSortableElements(emailTemplateEditor.settings.sortableElementsSelector,
                        emailTemplateEditor.settings.sortableElementsSelector,
                        iframeContents);
                }
                emailTemplateEditor.canvasChanged();
            },
            cursorAt: { top: 0, left: 0 },
            cursor: 'move'
        });
    },
    placeNewElement: function ( elementClass, item , wrapElement) { //@TODO SERGIO, do we need _item_? its not used inside the function
        $.ajax({
            url: emailTemplateEditor.settings.getNewElementUrl,
            type: 'POST',
            data: {className: elementClass, renderForCanvas: 1, wrapElementInRow: wrapElement},
            beforeSend: function() {
                    emailTemplateEditor.freezeLayoutEditor();
            },
            success: function (html) {
                //@TODO SERGIO, it seems like Shoaibi wraps these in table eventhough they should be divs now
                emailTemplateEditor.settings.ghost.after(html);
                emailTemplateEditor.unfreezeLayoutEditor();
                emailTemplateEditor.settings.ghost.detach();
            }
        });
    },
    canvasChanged: function () {
        $(emailTemplateEditor.settings.cachedSerializedDataSelector).val('');
    },
    freezeLayoutEditor: function () {
        $(emailTemplateEditor.settings.iframeOverlaySelector).addClass('freeze');
        $(this).makeLargeLoadingSpinner(true, emailTemplateEditor.settings.iframeOverlaySelector);
    },
    unfreezeLayoutEditor: function () {
        $(emailTemplateEditor.settings.iframeOverlaySelector).removeClass('freeze');
        $(this).makeLargeLoadingSpinner(false, emailTemplateEditor.settings.iframeOverlaySelector);
    },
    onClickEditEvent: function () {
        emailTemplateEditor.freezeLayoutEditor();
        var element = $(this).closest('.builder-element-non-editable');
        id           = element.attr('id');
        elementClass = element.data("class") + 'removeThisWhenImplemented';
        $.ajax({
            url: emailTemplateEditor.settings.editElementUrl,
            type: 'POST',
            data: {id: id, className: elementClass, renderForCanvas: 1},
            success: function (html) {
                $(emailTemplateEditor.settings.editSelector).html(html);
            }
        });
        $(emailTemplateEditor.settings.editSelector).show();
        emailTemplateEditor.unfreezeLayoutEditor();
    },
    onClickDeleteEvent: function () {
        if ($(this).closest('.sortable-rows').children('.element-wrapper').length > 1 ||
            $(this).parents('.sortable-elements').length > 0) {
                $(this).closest(".element-wrapper").remove();
        } else {
            alert(emailTemplateEditor.settings.alertErrorOnDelete);
        }
    },
    reloadCanvas: function () {
        $(emailTemplateEditor.settings.iframeSelector).attr( 'src', function ( i, val ) { return val; });
        emailTemplateEditor.canvasChanged();
    },
    compileSerializedData: function () {
        var getSerializedData = function (element) {
            var data = {};
            var content = {};
            content['content'] = $(element).data('content');
            data['content'] = content;
            data['properties'] = $(element).data('properties');
            data['class'] = $(element).data('class');
            return data;
        };

        var findParentAndAppendSerializedData = function findParent(parent, elementId, serializedData, data) {
            for(var key in data) {
                if (key == $(parent).attr('id')) {
                    data[key]['content'][elementId] = serializedData;
                }
                else
                {
                    findParent(parent, elementId, serializedData, data[key]['content']);
                }
            }
            return data;
        }

        //Gets the cachedSerializedData and if its set return it
        var value = $(emailTemplateEditor.settings.cachedSerializedDataSelector).val();
        if (value != '') {
            return value;
        };

        var data    = {};
        var elementDataArray = contents.find('.element-data');
        for (var i = 0; i < elementDataArray.length; i++){
            var parentsElementData = $(elementDataArray[i]).parents('.element-data:first');
            if (parentsElementData.length == 0)
            {
                //Its the first element, the canvas
                data[$(elementDataArray[i]).attr('id')] = getSerializedData(elementDataArray[i]);
            }
            else
            {
                var parent = parentsElementData[0];
                data = findParentAndAppendSerializedData(parent, $(elementDataArray[i]).attr('id'), getSerializedData(elementDataArray[i]), data);
            }
        }
        value = JSON.stringify(data);
        $(emailTemplateEditor.settings.cachedSerializedDataSelector).val(value);
        return value;
    }
}