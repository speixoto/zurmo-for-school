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
;(function($) {

function load(settings, root, child, container) {
    function createNode(parent) {
        var current = $("<li/>").attr("id", this.id || "").html("<span>" + this.text + "</span>").appendTo(parent);
        if (this.wrapperClass) {
            current.addClass(this.wrapperClass);
        }
        if (this.classes) {
            current.children("span").addClass(this.classes);
        }
        if (this.expanded) {
            current.addClass("open");
        }
        if (this.hasChildren || this.children && this.children.length) {
            var branch = $("<ul/>").appendTo(current);
            if (this.hasChildren) {
                current.addClass("hasChildren");
                createNode.call({
                    classes: "placeholder",
                    text: "&nbsp;",
                    children:[]
                }, branch);
            }
            if (this.children && this.children.length) {
                $.each(this.children, createNode, [branch])
            }
        }
    }
    $.ajax($.extend(true, {
        url: $.param.querystring(settings.url, 'nodeId=' + root),
        dataType: "json",
        type: 'POST',
        data : $('#' + settings.formName).serialize(),
        success: function(response) {
            child.empty();
            $.each(response, createNode, [child]);
            $(container).treeview({add: child});
        }
    }, settings.ajax));
}

var proxied = $.fn.treeview;
$.fn.treeview = function(settings) {
    if (!settings.url) {
        return proxied.apply(this, arguments);
    }
    var container = this;
    if (!container.children().size())
        load(settings, "source", this, container);
    var userToggle = settings.toggle;
    return proxied.call(this, $.extend({}, settings, {
        collapsed: true,
        toggle: function() {
            var $this = $(this);
            if ($this.hasClass("hasChildren")) {
                var childList = $this.removeClass("hasChildren").find("ul");
                load(settings, this.id, childList, container);
            }
            if (userToggle) {
                userToggle.apply(this, arguments);
            }
        }
    }));
};

})(jQuery);