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
        rowWrapper: '',
        getNewElementUrl: '',
        editElementUrl: '',
        iframeSelector: '#preview-template',
        editSelector: '#edit-element',
        elementsToPlaceSelector: '#building-blocks',
        sortableRowsSelector: '.sortable-rows',
        sortableElementsSelector: '.sortable-elements'
    },
    init : function (elementsToPlaceSelector, editSelector, rowWrapper, editElementUrl, getNewElementUrl) {
        this.settings.elementsToPlaceSelector = elementsToPlaceSelector;
        this.settings.editSelector            = editSelector;
        this.settings.rowWrapper              = rowWrapper;
        this.settings.editElementUrl          = editElementUrl;
        this.settings.getNewElementUrl        = getNewElementUrl;
        this.setupLayout();
        emailTemplateEditor = this;
    },
    setupLayout : function() {
        $(emailTemplateEditor.settings.iframeSelector).load(function () {
            contents = $(this).contents();

            $( contents.find('body') ).on( "click", "span.edit", emailTemplateEditor.onClickEditEvent);
            $( contents.find('body') ).on( "click", "span.delete", emailTemplateEditor.onClickDeleteEvent);

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
            if ($(this).data('draggable'))
            {
                $(this).draggable("destroy");
            }
        });

        var clone = '';
        var label = '';

        $('li', selector ).draggable({
            appendTo: 'body',
            helper: "clone",
            cursor: 'move',
            iframeFix: true,
            revert: 'invalid',
            cursorAt: { left:  -20, top: -20 },
            helper: function(event, ui){
                label = $(event.currentTarget).html();
                clone = $('<div class="blox">' + label + '</div>');
                return clone;
            }
            //,connectToSortable: iframeContents.find(connectToSelector)
        });

        var containers = [];
        var offset = {};
        var rect = {};
        var point = {};
        var i = 0;

        $('body').on('mousedown', function(event){
            offset = $(emailTemplateEditor.settings.iframeSelector).offset();
            containers = $(emailTemplateEditor.settings.iframeSelector).contents().find(
                            emailTemplateEditor.settings.sortableElementsSelector + ', ' +
                            emailTemplateEditor.settings.sortableRowsSelector);
        });

        $('body').on('mousemove', function(event){
            point.left = event.pageX - offset.left;
            point.top = event.pageY - offset.top;
            for (i = 0; i < containers.length; i++){
                rect = containers[i].getBoundingClientRect();
                if( point.left > rect.left &&
                    point.left < rect.right &&
                    point.top > rect.top &&
                    point.top < rect.bottom ){
                        $(containers[i]).addClass('hover');
                } else {
                    $(containers[i]).removeClass('hover');
                }
            }
        });

        $('body').on('mouseup', function(event){
            point.left = event.pageX - offset.left;
            point.top = event.pageY - offset.top;
            for (i = 0; i < containers.length; i++){
                rect = containers[i].getBoundingClientRect();
                if( point.left > rect.left &&
                    point.left < rect.right &&
                    point.top > rect.top &&
                    point.top < rect.bottom ){
                        $(containers[i]).addClass('on');
                        $(containers[i]).prepend('<strong>inserted: '+label+'</strong>');
                        alert('Sergio, implement me please..');
                } else {
                    $(containers[i]).removeClass('on');
                }
            }
        });


    },
    initSortableElements: function ( selector , connectToSelector, iframeContents) {
        $( iframeContents.find(selector) ).each(function(){
            if ($(this).data('sortable'))
            {
                $(this).sortable("destroy");
            }
        });
        $( iframeContents.find(selector) ).sortable({
            handle: '.handle',
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
            if ($(this).data('sortable'))
            {
                $(this).sortable("destroy");
            }
        });
        $( iframeContents.find(selector) ).sortable({
            handle: '.handle',
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
    placeNewElement: function ( elementClass, item , wrapElement) {
        $.ajax({
            url: emailTemplateEditor.settings.getNewElementUrl,
            data: {className: elementClass, renderForCanvas: 1, wrapElementInRow: wrapElement},
            beforeSend: function() {
                    emailTemplateEditor.freezeLayoutEditor();
            },
            success: function (html) {
                item.replaceWith(html);
            }
        });
    },
    canvasChanged: function () {
        console.log('The canvas has changed');
    },
    freezeLayoutEditor: function () {
        $(this).makeOrRemoveLoadingSpinner(true, "#builder", 'dark');
    },
    unfreezeLayoutEditor: function () {
        $(this).makeOrRemoveLoadingSpinner(true, "#builder", 'dark');
    },
    onClickEditEvent: function () {
        emailTemplateEditor.freezeLayoutEditor();
        var element = $(this).closest('.builder-element-non-editable');
        id           = element.attr('id');
        elementClass = element.data("class") + 'removeThisWhenImplemented';
        $.ajax({
            url: emailTemplateEditor.settings.editElementUrl,
            data: {id: id, className: elementClass, renderForCanvas: 1},
            success: function (html) {
                $(emailTemplateEditor.settings.editSelector).html(html);
            }
        });
        $(emailTemplateEditor.settings.editSelector).show();
        emailTemplateEditor.unfreezeLayoutEditor();
    },
    onClickDeleteEvent: function () {
        $(this).closest(".builder-element-non-editable").remove();
    }
}