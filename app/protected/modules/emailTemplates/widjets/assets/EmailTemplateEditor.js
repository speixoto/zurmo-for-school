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
        elementsToPlaceSelector: '#building-blocks',
        sortableRowsSelector: '.sortable-rows',
        sortableElementsSelector: '.sortable-elements',
        iframeSelector: '#preview-template'
    },
    init : function (elementsToPlaceSelector, rowWrapper, getNewElementUrl) {
        this.settings.elementsToPlaceSelector = elementsToPlaceSelector;
        this.settings.rowWrapper    = rowWrapper;
        this.settings.getNewElementUrl = getNewElementUrl;
        this.setupLayout();
        emailTemplateEditor = this;
    },
    setupLayout : function() {
        emailTemplateEditor = this;
        $(emailTemplateEditor.settings.iframeSelector).load(function () {
            contents = $(emailTemplateEditor.settings.iframeSelector).contents();
            emailTemplateEditor.initDraggableElements(emailTemplateEditor.settings.elementsToPlaceSelector,
                contents.find(emailTemplateEditor.settings.sortableElementsSelector + ", " + emailTemplateEditor.settings.sortableRowsSelector));
            emailTemplateEditor.initSortableElements(contents.find(emailTemplateEditor.settings.sortableElementsSelector),
                contents.find(emailTemplateEditor.settings.sortableElementsSelector));
            emailTemplateEditor.initSortableRows(contents.find(emailTemplateEditor.settings.sortableRowsSelector));
        });
    },
    initDraggableElements: function ( selector , connectToSelector) {
        $( selector ).each(function(){
            if ($(this).data('draggable'))
            {
                $(this).draggable("destroy");
            }
        });
        $('li', selector ).draggable({
            appendTo: 'body',
            zIndex: 9999999,
            helper: "clone",
            cursor: 'move',
            iframeFix: true,
            revert: 'invalid',
            connectToSortable: connectToSelector
        });
    },
    initSortableElements: function ( selector , connectToSelector) {
        $( selector ).each(function(){
            if ($(this).data('sortable'))
            {
                $(this).sortable("destroy");
            }
        });
        $( selector ).sortable({
//            hoverClass: "ui-state-hover",
//            placeholder: "ui-state-highlight",
            iframeFix: true,
            stop: function( event, ui ) {
                if (ui.item.is('li')) {
                    emailTemplateEditor.placeNewElement(ui.item.data("class"), ui.item);
                }
            },
            remove: function ( event, ui ) {
                 if ($(this).sortable("toArray").length < 1)
                 {
                     //TODO: @sergio: What should we the sortable-elements became empty?
                     // We should not have that problem , we need to be able to drag item in an empty sortable
                     //$(this).remove();
                 }
            },
            cursor: 'move',
            connectWith: connectToSelector
        });
    },
    initSortableRows: function ( selector ) {
        emailTemplateEditor = this;
        $( selector ).each(function(){
            if ($(this).data('sortable'))
            {
                $(this).sortable("destroy");
            }
        });
        $( selector ).sortable({
//            hoverClass: "ui-state-hover",
//            placeholder: "ui-state-highlight",
//            handle: "span.ui-icon-arrow-4",
            iframeFix: true,
            stop: function( event, ui ) {
                if (ui.item.is('li')) {
                    ui.item.wrap(emailTemplateEditor.settings.rowWrapper);
                    emailTemplateEditor.placeNewElement(ui.item.data("class"), ui.item);
                    emailTemplateEditor.initSortableElements(ui.item.closest(emailTemplateEditor.settings.sortableElementsSelector), emailTemplateEditor.settings.sortableElementsSelector);
                }
            },
            cursor: 'move'
        });
    },
    placeNewElement: function ( elementClass, item ) {
        $.ajax({
            url: emailTemplateEditor.settings.getNewElementUrl,
            data: {'className': elementClass},
            success: function (html) {
                item.replaceWith(html);
            }
        });
    }
}