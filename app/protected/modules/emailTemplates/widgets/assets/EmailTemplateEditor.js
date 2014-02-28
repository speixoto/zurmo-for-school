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
        elementsContainerId: '',
        elementsToPlaceSelector: '#building-blocks',
        sortableRowsSelector: '.sortable-rows',
        sortableElementsSelector: '.sortable-elements',
        editActionSelector: 'span.action-edit',
        moveActionSelector: 'span.action-move',
        deleteActionSelector: 'span.action-delete',
        cachedSerializedDataSelector: '#serialized-data-cache',
        ghost : '',
        alertErrorOnDelete: 'You cannot delete last row',
        dropHereMessage: 'Drop here',
        csrfToken: '',
        doNotWrapInRow: 0,
        wrapInRow: 1,
        wrapInHeaderRow: 2,
        isDragging: false,
        isInited: false
    },
    init : function (elementsContainerId, elementsToPlaceSelector, iframeSelector, editSelector, editFormSelector,
                     editActionSelector, moveActionSelector, deleteActionSelector, iframeOverlaySelector,
                     cachedSerializedDataSelector, editElementUrl, getNewElementUrl, alertErrorOnDelete,
                     dropHereMessage, csrfToken, doNotWrapInRow, wrapInRow, wrapInHeaderRow) {
        if (!this.settings.isInited)
        {
            this.settings.elementsContainerId           = elementsContainerId;
            this.settings.elementsToPlaceSelector       = elementsToPlaceSelector;
            this.settings.iframeSelector                = iframeSelector;
            this.settings.editSelector                  = editSelector;
            this.settings.editFormSelector              = editFormSelector;
            this.settings.editActionSelector            = editActionSelector;
            this.settings.moveActionSelector            = moveActionSelector;
            this.settings.deleteActionSelector          = deleteActionSelector;
            this.settings.iframeOverlaySelector         = iframeOverlaySelector;
            this.settings.cachedSerializedDataSelector  = cachedSerializedDataSelector;
            this.settings.editElementUrl                = editElementUrl;
            this.settings.getNewElementUrl              = getNewElementUrl;
            this.settings.alertErrorOnDelete            = alertErrorOnDelete;
            this.settings.dropHereMessage               = dropHereMessage;
            this.settings.csrfToken                     = csrfToken;
            this.settings.doNotWrapInRow                = doNotWrapInRow;
            this.settings.wrapInRow                     = wrapInRow;
            this.settings.wrapInHeaderRow               = wrapInHeaderRow;
            this.setupLayout();
            this.settings.isInited                = true;
            emailTemplateEditor = this;
        }
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
    //Init the elements from outside iframe to be draggable
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
            cursorAt: { left:  -10, top: -10 },
            helper: function(event, ui){
                elementDragged      = $(event.currentTarget),
                elementDraggedClass = $(event.currentTarget).data('class');
                clone = $('<div class="draggable-element-clone">' + $(event.currentTarget).html() + '</div>');
                return clone;
            }
        });

        var containers = [];
        var offset = {};
        var iframeElement = document.getElementById('canvas-iframe');
        var iframeRect = {};
        var rect = {};
        var innerElements = [];
        var point = {};
        var i = 0;
        var mostTopElement;
        var mostTopElementHalf = 0;
        var positions = [];
        emailTemplateEditor.settings.ghost = $('<div class="ghost">' +  emailTemplateEditor.settings.dropHereMessage + '</div>');

        $('#building-blocks').on('mousedown', onBodyMouseDown);

        function onBodyMouseDown(event){
            offset = $(emailTemplateEditor.settings.iframeSelector).offset();
            iframeRect = iframeElement.getBoundingClientRect();
            containers = $(emailTemplateEditor.settings.iframeSelector).contents().find('.sortable-elements > .element-wrapper, .sortable-rows > .element-wrapper');
            emailTemplateEditor.settings.isDragging = true;
            $('body').on('mousemove', onBodyMouseMove);
            $('body').on('mouseup', onBodyMouseUp);
            //calculate position of droppables on mousedown, ONLY ONCE each time
            positions = [];
            for (i = 0; i < containers.length; i++){
                rect = containers[i].getBoundingClientRect();
                positions.push(rect);
            }
        }

        function onBodyMouseUp(event){
            $('body').off('mousemove', onBodyMouseMove);
            $('body').off('mouseup', onBodyMouseUp);
            emailTemplateEditor.settings.isDragging = false;
            if (elementDragged != undefined && elementDragged.is('li') && $(event.target).hasClass('ui-draggable-iframeFix')){
                var wrapInRow       = elementDragged.data('wrap');
                if (typeof wrapInRow == 'undefined') {
                    if( emailTemplateEditor.settings.ghost.parent().hasClass('sortable-rows') === true ){
                        wrapInRow   = emailTemplateEditor.settings.wrapInRow;
                    } else {
                        wrapInRow   = emailTemplateEditor.settings.doNotWrapInRow;
                    }
                }
                emailTemplateEditor.placeNewElement(elementDraggedClass, wrapInRow, iframeContents, innerElements);
            } else {
                console.log('dropped either outside of canvas or not on element');
                //Remove the ghost element
                $(innerElements).each(function(){$(this).removeClass('hover');});
                emailTemplateEditor.settings.ghost.detach();
            }
        }

        function onBodyMouseMove(event){
            if(emailTemplateEditor.settings.isDragging === true){
                $(innerElements).each(function(){$(this).removeClass('hover');});
                innerElements = [];
                point.left = event.pageX - offset.left;
                point.top = event.pageY - offset.top;
                for(i = 0; i < positions.length; i++){
                    if( point.left > positions[i].left && point.left < positions[i].right &&
                        point.top > positions[i].top && point.top < positions[i].bottom ){
                        innerElements.push(containers[i]);
                    }
                }
                if(innerElements.length > 0){
                    mostTopElement = $(innerElements[innerElements.length-1]);
                    $(mostTopElement).addClass('hover');
                    mostTopElementHalf = mostTopElement.outerHeight(true) / 2;
                    if(event.pageY < mostTopElement.offset().top + offset.top + mostTopElementHalf){
                        mostTopElement.before(emailTemplateEditor.settings.ghost);
                    } else {
                        mostTopElement.after(emailTemplateEditor.settings.ghost);
                    }
                }
            }
        }
    },
    //Init the cells to be sortable
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
                emailTemplateEditor.canvasChanged();
            },
            cursorAt: { top: 0, left: 0 },
            cursor: 'move',
            connectWith: iframeContents.find(connectToSelector)
        });
    },
    //Init the rows to be sortable
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
                emailTemplateEditor.canvasChanged();
            },
            cursorAt: { top: 0, left: 0 },
            cursor: 'move'
        });
    },
    //Used on a new element is dragged and dropped from outside iframa
    placeNewElement: function ( elementClass, wrapElement, iframeContents, innerElements) {
        $.ajax({
            url: emailTemplateEditor.settings.getNewElementUrl,
            type: 'POST',
            data: {className: elementClass, renderForCanvas: 1, wrapElementInRow: wrapElement, 'YII_CSRF_TOKEN': emailTemplateEditor.settings.csrfToken},
            beforeSend: function() {
                    //Show an overlay with loading spinner
                    emailTemplateEditor.freezeLayoutEditor();
            },
            success: function (html) {
                //Places the element
                emailTemplateEditor.settings.ghost.after(html);
                if (wrapElement != emailTemplateEditor.doNotWrapInRow)
                {
                    //If its a new row init the sortable element so the the row cells can be sortable/draggable
                    emailTemplateEditor.initSortableElements(emailTemplateEditor.settings.sortableElementsSelector,
                        emailTemplateEditor.settings.sortableElementsSelector,
                        iframeContents);
                }
                //Process canvasChanged event
                emailTemplateEditor.canvasChanged();
                //Hide the overlay with loading spinner
                emailTemplateEditor.unfreezeLayoutEditor();
                //Remove the ghost element
                emailTemplateEditor.settings.ghost.detach();
                //Remove any class 'hover' for elements
                $(innerElements).each(function(){$(this).removeClass('hover');});
            }
        });
    },
    //Resets the serializedData cache
    canvasChanged: function () {
        $(emailTemplateEditor.settings.cachedSerializedDataSelector).val('');
    },
    //Shows an overlay with loading spinner
    freezeLayoutEditor: function () {
        $(emailTemplateEditor.settings.iframeOverlaySelector).addClass('freeze');
        $(this).makeLargeLoadingSpinner(true, emailTemplateEditor.settings.iframeOverlaySelector);
    },
    //Removes the overlay with loading spinner
    unfreezeLayoutEditor: function () {
        $(emailTemplateEditor.settings.iframeOverlaySelector).removeClass('freeze');
        $(this).makeLargeLoadingSpinner(false, emailTemplateEditor.settings.iframeOverlaySelector);
    },
    onClickEditEvent: function () {
        //Shows overlay with loading spinner
        emailTemplateEditor.freezeLayoutEditor();
        // closest always traversal to the parents, in out case the actual element is a sibling of its parent.
        var element         = $(this).parent().siblings('.builder-element-non-editable.element-data');
        //Get the element id next to which Edit was clicked.
        id                  = element.attr('id');
        //Get data-class of that element.
        elementClass        = element.data('class');
        //Extract serializedData and assign to JS vars e.g. content, properties
        elementProperties   = $.extend({}, element.data('properties'));
        var serializedData = $.parseJSON(emailTemplateEditor.compileSerializedData());
        elementContent      = emailTemplateEditor.getElementContent(id, serializedData);
        postData            = {id: id, className: elementClass, renderForCanvas: 1, properties: elementProperties,
                                content: elementContent, 'YII_CSRF_TOKEN': emailTemplateEditor.settings.csrfToken};
        postData            = decodeURIComponent($.param(postData));
        //Send an ajax to resolveElementEditableActionUrl()
        $.ajax({
            url: emailTemplateEditor.settings.editElementUrl,
            type: 'POST',
            data: postData,
            cache: false,
            success: function (html) {
                $(emailTemplateEditor.settings.editFormSelector).html(html);
            }
        });
        //Make the left side overlay visible.
        $('#droppable-element-sidebar').hide();
        $(emailTemplateEditor.settings.editSelector).show();
        //Hides overlay with loading spinner
        emailTemplateEditor.unfreezeLayoutEditor();
    },
    onClickDeleteEvent: function () {
        //Check if removing last row
        if ($(this).closest('.sortable-rows').children('.element-wrapper').length > 1 ||
            $(this).parents('.sortable-elements').length > 0) {
                //Remove row/element
                $(this).closest(".element-wrapper").remove();
                //Process canvasChanged event
                emailTemplateEditor.canvasChanged();
        } else {
            //Alert use cant remove last row
            alert(emailTemplateEditor.settings.alertErrorOnDelete);
        }
    },
    reloadCanvas: function () {
        //Reload the canvas by reloading iframe
        $(emailTemplateEditor.settings.iframeSelector).attr( 'src', function ( i, val ) { return val; });
        //Process canvasChanged event
        emailTemplateEditor.canvasChanged();
    },
    //Compile serializedData
    compileSerializedData: function () {
        var getSerializedData = function (element) {
            var data = {};
            data['content'] = $.extend({}, $(element).data('content'));
            data['properties'] = $.extend({}, $(element).data('properties'));
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
        //Store the serializedData on cache selector
        $(emailTemplateEditor.settings.cachedSerializedDataSelector).val(value);
        return value;
    },
    //Recursive get the element and child elements content
    getElementContent: function findContent (elementId, data) {
        var content = {};
        if ($.type(data) === 'object') {
            for (var key in data) {
                if (key == elementId)
                {
                    return data[key]['content'];
                }
                else
                {
                    if (data[key] != undefined)
                    {
                        content = $.extend(content, findContent(elementId, data[key]['content']));
                    }
                }
            }
        }
        return content;
    }
}