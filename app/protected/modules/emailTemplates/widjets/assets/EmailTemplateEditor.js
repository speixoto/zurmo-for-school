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
        elementsToPlaceClassSelector: '.elementToPlace',
        sortableRowsSelector: '.sortable-rows',
        sortableElementsSelector: '.sortable-elements'
    },
    init : function (rowWrapper, getNewElementUrl) {
        this.settings.rowWrapper    = rowWrapper;
        this.settings.getNewElementUrl = getNewElementUrl;
        this.setupLayout();
    },
    setupLayout : function() {
        emailTemplateEditor = this;
        this.initDraggableElements(emailTemplateEditor.settings.elementsToPlaceClassSelector, emailTemplateEditor.settings.sortableElementsSelector + ", " + emailTemplateEditor.settings.sortableRowsSelector);
        this.initSortableElements(emailTemplateEditor.settings.sortableElementsSelector, emailTemplateEditor.settings.sortableElementsSelector);
        this.initSortableRows(emailTemplateEditor.settings.sortableRowsSelector);
    },
    initDraggableElements: function ( selector , connectToSelector) {
        $( selector ).each(function(){
            if ($(this).data('draggable'))
            {
                $(this).draggable("destroy");
            }
        });
        $( selector ).draggable({
            helper: "clone",
            cursor: 'move',
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
            hoverClass: "ui-state-hover",
            placeholder: "ui-state-highlight",
            stop: function( event, ui ) {
                if (ui.item.hasClass('elementToPlace')) {
                    emailTemplateEditor.placeNewElement(ui.item.children("img").attr("alt"), ui.item);
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
            hoverClass: "ui-state-hover",
            placeholder: "ui-state-highlight",
            handle: "table",
            stop: function( event, ui ) {
                if (ui.item.is('div')) {
                    ui.item.wrap(emailTemplateEditor.settings.rowWrapper);
                    emailTemplateEditor.placeNewElement(ui.item.children("img").attr("alt"), ui.item);
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