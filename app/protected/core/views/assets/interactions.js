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

//fgnass.github.com/spin.js#v1.2.5
(function(window, document, undefined) {

    /**
     * Copyright (c) 2011 Felix Gnass [fgnass at neteye dot de]
     * Licensed under the MIT license
     */

    var prefixes = ['webkit', 'Moz', 'ms', 'O']; /* Vendor prefixes */
    var animations = {}; /* Animation rules keyed by their name */
    var useCssAnimations;

    /**
     * Utility function to create elements. If no tag name is given,
     * a DIV is created. Optionally properties can be passed.
     */
    function createEl(tag, prop) {
        var el = document.createElement(tag || 'div');
        var n;

        for(n in prop) {
            el[n] = prop[n];
        }
        return el;
    }

    /**
     * Appends children and returns the parent.
     */
    function ins(parent /* child1, child2, ...*/) {
        for (var i=1, n=arguments.length; i<n; i++) {
            parent.appendChild(arguments[i]);
        }
        return parent;
    }

    /**
     * Insert a new stylesheet to hold the @keyframe or VML rules.
     */
    var sheet = function() {
        var el = createEl('style');
        ins(document.getElementsByTagName('head')[0], el);
        return el.sheet || el.styleSheet;
    }();

    /**
     * Creates an opacity keyframe animation rule and returns its name.
     * Since most mobile Webkits have timing issues with animation-delay,
     * we create separate rules for each line/segment.
     */
    function addAnimation(alpha, trail, i, lines) {
        var name = ['opacity', trail, ~~(alpha*100), i, lines].join('-');
        var start = 0.01 + i/lines*100;
        var z = Math.max(1-(1-alpha)/trail*(100-start) , alpha);
        var prefix = useCssAnimations.substring(0, useCssAnimations.indexOf('Animation')).toLowerCase();
        var pre = prefix && '-'+prefix+'-' || '';

        if (!animations[name]) {
            sheet.insertRule(
                '@' + pre + 'keyframes ' + name + '{' +
                    '0%{opacity:'+z+'}' +
                    start + '%{opacity:'+ alpha + '}' +
                    (start+0.01) + '%{opacity:1}' +
                    (start+trail)%100 + '%{opacity:'+ alpha + '}' +
                    '100%{opacity:'+ z + '}' +
                    '}', 0);
            animations[name] = 1;
        }
        return name;
    }

    /**
     * Tries various vendor prefixes and returns the first supported property.
     **/
    function vendor(el, prop) {
        var s = el.style;
        var pp;
        var i;

        if(s[prop] !== undefined) return prop;
        prop = prop.charAt(0).toUpperCase() + prop.slice(1);
        for(i=0; i<prefixes.length; i++) {
            pp = prefixes[i]+prop;
            if(s[pp] !== undefined) return pp;
        }
    }

    /**
     * Sets multiple style properties at once.
     */
    function css(el, prop) {
        for (var n in prop) {
            el.style[vendor(el, n)||n] = prop[n];
        }
        return el;
    }

    /**
     * Fills in default values.
     */
    function merge(obj) {
        for (var i=1; i < arguments.length; i++) {
            var def = arguments[i];
            for (var n in def) {
                if (obj[n] === undefined) obj[n] = def[n];
            }
        }
        return obj;
    }

    /**
     * Returns the absolute page-offset of the given element.
     */
    function pos(el) {
        var o = {x:el.offsetLeft, y:el.offsetTop};
        while((el = el.offsetParent)) {
            o.x+=el.offsetLeft;
            o.y+=el.offsetTop;
        }
        return o;
    }

    var defaults = {
        lines: 12,            // The number of lines to draw
        length: 7,            // The length of each line
        width: 5,             // The line thickness
        radius: 10,           // The radius of the inner circle
        rotate: 0,            // rotation offset
        color: '#000',        // #rgb or #rrggbb
        speed: 1,             // Rounds per second
        trail: 100,           // Afterglow percentage
        opacity: 1/4,         // Opacity of the lines
        fps: 20,              // Frames per second when using setTimeout()
        zIndex: 2e9,          // Use a high z-index by default
        className: 'spinner', // CSS class to assign to the element
        top: 'auto',          // center vertically
        left: 'auto'          // center horizontally
    };

    /** The constructor */
    var Spinner = function Spinner(o) {
        if (!this.spin) return new Spinner(o);
        this.opts = merge(o || {}, Spinner.defaults, defaults);
    };

    Spinner.defaults = {};
    merge(Spinner.prototype, {
        spin: function(target) {
            this.stop();
            var self = this;
            var o = self.opts;
            var el = self.el = css(createEl(0, {className: o.className}), {position: 'relative', zIndex: o.zIndex});
            var mid = o.radius+o.length+o.width;
            var ep; // element position
            var tp; // target position

            if (target) {
                target.insertBefore(el, target.firstChild||null);
                tp = pos(target);
                ep = pos(el);
                css(el, {
                    left: (o.left == 'auto' ? tp.x-ep.x + (target.offsetWidth >> 1) : o.left+mid) + 'px',
                    top: (o.top == 'auto' ? tp.y-ep.y + (target.offsetHeight >> 1) : o.top+mid)  + 'px'
                });
            }

            el.setAttribute('aria-role', 'progressbar');
            self.lines(el, self.opts);

            if (!useCssAnimations) {
                // No CSS animation support, use setTimeout() instead
                var i = 0;
                var fps = o.fps;
                var f = fps/o.speed;
                var ostep = (1-o.opacity)/(f*o.trail / 100);
                var astep = f/o.lines;

                !function anim() {
                    i++;
                    for (var s=o.lines; s; s--) {
                        var alpha = Math.max(1-(i+s*astep)%f * ostep, o.opacity);
                        self.opacity(el, o.lines-s, alpha, o);
                    }
                    self.timeout = self.el && setTimeout(anim, ~~(1000/fps));
                }();
            }
            return self;
        },
        stop: function() {
            var el = this.el;
            if (el) {
                clearTimeout(this.timeout);
                if (el.parentNode) el.parentNode.removeChild(el);
                this.el = undefined;
            }
            return this;
        },
        lines: function(el, o) {
            var i = 0;
            var seg;

            function fill(color, shadow) {
                return css(createEl(), {
                    position: 'absolute',
                    width: (o.length+o.width) + 'px',
                    height: o.width + 'px',
                    background: color,
                    boxShadow: shadow,
                    transformOrigin: 'left',
                    transform: 'rotate(' + ~~(360/o.lines*i+o.rotate) + 'deg) translate(' + o.radius+'px' +',0)',
                    borderRadius: (o.width>>1) + 'px'
                });
            }
            for (; i < o.lines; i++) {
                seg = css(createEl(), {
                    position: 'absolute',
                    top: 1+~(o.width/2) + 'px',
                    transform: o.hwaccel ? 'translate3d(0,0,0)' : '',
                    opacity: o.opacity,
                    animation: useCssAnimations && addAnimation(o.opacity, o.trail, i, o.lines) + ' ' + 1/o.speed + 's linear infinite'
                });
                if (o.shadow) ins(seg, css(fill('#000', '0 0 4px ' + '#000'), {top: 2+'px'}));
                ins(el, ins(seg, fill(o.color, '0 0 1px rgba(0,0,0,.1)')));
            }
            return el;
        },
        opacity: function(el, i, val) {
            if (i < el.childNodes.length) el.childNodes[i].style.opacity = val;
        }
    });

    /////////////////////////////////////////////////////////////////////////
    // VML rendering for IE
    /////////////////////////////////////////////////////////////////////////

    /**
     * Check and init VML support
     */
    !function() {

        function vml(tag, attr) {
            return createEl('<' + tag + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">', attr);
        }

        var s = css(createEl('group'), {behavior: 'url(#default#VML)'});

        if (!vendor(s, 'transform') && s.adj) {

            // VML support detected. Insert CSS rule ...
            sheet.addRule('.spin-vml', 'behavior:url(#default#VML)');

            Spinner.prototype.lines = function(el, o) {
                var r = o.length+o.width;
                var s = 2*r;

                function grp() {
                    return css(vml('group', {coordsize: s +' '+s, coordorigin: -r +' '+-r}), {width: s, height: s});
                }

                var margin = -(o.width+o.length)*2+'px';
                var g = css(grp(), {position: 'absolute', top: margin, left: margin});

                var i;

                function seg(i, dx, filter) {
                    ins(g,
                        ins(css(grp(), {rotation: 360 / o.lines * i + 'deg', left: ~~dx}),
                            ins(css(vml('roundrect', {arcsize: 1}), {
                                width: r,
                                height: o.width,
                                left: o.radius,
                                top: -o.width>>1,
                                filter: filter
                            }),
                                vml('fill', {color: o.color, opacity: o.opacity}),
                                vml('stroke', {opacity: 0}) // transparent stroke to fix color bleeding upon opacity change
                            )
                        )
                    );
                }

                if (o.shadow) {
                    for (i = 1; i <= o.lines; i++) {
                        seg(i, -2, 'progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)');
                    }
                }
                for (i = 1; i <= o.lines; i++) seg(i);
                return ins(el, g);
            };
            Spinner.prototype.opacity = function(el, i, val, o) {
                var c = el.firstChild;
                o = o.shadow && o.lines || 0;
                if (c && i+o < c.childNodes.length) {
                    c = c.childNodes[i+o]; c = c && c.firstChild; c = c && c.firstChild;
                    if (c) c.opacity = val;
                }
            };
        }
        else {
            useCssAnimations = vendor(s, 'animation');
        }
    }();

    window.Spinner = Spinner;

})(window, document);
/*
 * this function takes care of the save/cancel buttons' position in long forms, ie. edit account.
 */
var windowTop, diff;

$.fn.dockFloatingBar = function(){
    if ($('html').hasClass('zurmo') === true && $('body').hasClass('mobile-app') === false){
        if ($('.float-bar').find('.disable-float-bar').length == 0) {
            windowTop = $(window).scrollTop();
            diff = $(document).height() - $(window).height() - 100; //100px is to dock it before scrolling all the way to the bottom
            if( windowTop > diff ) {
                $('.float-bar .view-toolbar-container').addClass('dock');
            } else {
                if( $('.wrapper').height() > $('.AppNavigation').height() ) {
                    $('.float-bar .view-toolbar-container').removeClass('dock');
                }
            }
        }
    }
};

/*
 * Checkboxes
 * from: http://webdesign.maratz.com/lab/fancy-checkboxes-and-radio-buttons/jquery.html
 *
 */
$.fn.addClickListenerForCheckbox = function($context) {
    $('label', $context[0]).live('click', {
        $inputContext : $(this).content
    }, function(event) {
        if ($('input:checkbox', event.data.$inputContext).length) {
            $('input:checkbox', event.data.$inputContext).each(function() {
                $(this).parent().removeClass('c_on');
            });
            $('label input:checked', event.data.$inputContext).each(function() {
                $(this).parent('label').addClass('c_on');
            });
        }
    });
};


$.fn.setupCheckboxStyling = function( $context ) {
    if ( $('input:checkbox', $context ).length ) {
        $('input:checkbox', $context ).each(function(){
            $(this).parent().removeClass('c_on');
        });
        $('label input:checked', $context ).each(function(){
            $(this).parent('label').addClass('c_on');
        });
    }
    $('label', $context[0] ).
        live( 'click', { $inputContext:$(this).content  },
        function( event ){
            if ( $('input:checkbox', event.data.$inputContext ).length ) {
                $('input:checkbox', event.data.$inputContext ).each(function(){
                    $(this).parent().removeClass('c_on');
                });
                $('label input:checked', event.data.$inputContext ).each(function(){
                    $(this).parent('label').addClass('c_on');
                });
            }
        });
};

$.fn.onAjaxSubmitRelatedListAction = function(confirmTitle, gridId){
    if(!confirm(confirmTitle)){
        return false;
    }
    $('#' + gridId).addClass("loading");
    $(this).makeSmallLoadingSpinner(true, '#' + gridId);
    return true;
};

/*Takes care of padding in input of type has-lang-label*/
$.fn.resolvePaddingForHasLangLabel = function(context){
    if ( $("span", context).outerWidth() > 80 ){
        $("input", context).css({paddingLeft: $("span", context).outerWidth() });
    } else {
        $("input", context).css({paddingLeft: 80 });
    }
};

/*
 * Spinner Functions
 * @state (Bool) true/false, activate/deactivate the spinner.
 * @domObject (String), the object's seletor where the spinner runs inside it. The CSS selector (with . or #).
 * @styleObject (Object) key-value pairs for the spinner's styles.
 * @spinnerClassName (String) Optional, used mostly for the Big-Spinners. The CSS selector for the actuall spinner (with . or #).
 */

$.fn.resolveSpinner = function(state, domObject, styleObject, spinnerClassName){

    if(spinnerClassName === undefined){
        spinnerClassName = '.z-spinner';
    }

    if(state === true){
        $( spinnerClassName, domObject).spin({
            lines     : styleObject.lines  || 9,      // The number of lines to draw
            length    : styleObject.length || 2.3,    // The length of each line
            width     : styleObject.width  || 1.7,    // The line thickness
            radius    : styleObject.radius || 3,      // The radius of the inner circle
            rotate    : 0,                            // The rotation offset
            color     : styleObject.color  || '#fff', // #rgb or #rrggbb
            speed     : styleObject.speed  || 2.5,    // Rounds per second
            trail     : styleObject.trail  || 37,     // Afterglow percentage
            shadow    : false,                        // Whether to render a shadow
            hwaccel   : true,                         // Whether to use hardware acceleration
            className : 'spinner',                    // The CSS class to assign to the spinner
            zIndex    : 2e9,                          // The z-index (defaults to 2000000000)
            top       : styleObject.top    || 0,      // Top position relative to parent in px
            left      : styleObject.left   || 0       // Left position relative to parent in px
        });
    } else {
        $( spinnerClassName, domObject).spin(false);
    }
};

$.fn.attachLoadingSpinnerForLanguageActivation = function( id, state, color ){
    if ( color === 'dark' ){
        color = '#999';
    } else {
        color = '#fff';
    }
    if ( state === true ){
        $( '.z-spinner', id ).spin({
            lines : 9, // The number of lines to draw
            length : 2.3, // The length of each line
            width : 1.7, // The line thickness
            radius : 3, // The radius of the inner circle
            rotate : 0, // The rotation offset
            color : color, // #rgb or #rrggbb
            speed : 2.5, // Rounds per second
            trail : 37, // Afterglow percentage
            shadow : false, // Whether to render a shadow
            hwaccel : true, // Whether to use hardware acceleration
            className : 'spinner', // The CSS class to assign to the spinner
            zIndex : 2e9, // The z-index (defaults to 2000000000)
            top : 4, // Top position relative to parent in px
            left : 0 // Left position relative to parent in px
        });
    } else {
        $( '.z-spinner', id ).spin(false);
    }
};

/*
 * @shade (String) HEX color (with #) or 'dark', default value is #FFF (white).
 */

$.fn.makeOrRemoveLoadingSpinner = function(state, context, shade){
    var style = {
        lines : 9,
        length : 2.3,
        width : 1.7,
        radius : 3,
        color : ( shade === 'dark' ) ? '#999' : '#fff',
        speed : 2.5,
        trail : 37,
        top : 4,
        left : 0
    };
    $(this).resolveSpinner(state, context, style);
};

$.fn.makeSmallLoadingSpinner = function(state, context){
    var style = {
        lines  : 11,
        length : 4,
        width  : 2,
        radius : 4,
        color  : '#FFFFFF',
        speed  : 1.5,
        trail  : 35,
        top    : 0,
        left   : 0
    };
    $(this).resolveSpinner(true, context, style);
};

$.fn.makeLargeLoadingSpinner = function(state, context){
    if($(context).find('.big-spinner').length === 0){
        $(context).append('<span class="big-spinner"></span>');
    }
    var style = {
        lines  : 10,
        length : 6,
        width  : 4,
        radius : 7,
        color  : '#CCCCCC',
        speed  : 2.6,
        trail  : 37,
        top    : 0,
        left   : 0
    };
    $(this).resolveSpinner(state, context, style, '.big-spinner');
};

$.fn.makeOrRemoveTogglableSpinner = function(state, context){
    var style = {
        lines  : 10,
        length : 3,
        width  : 2,
        radius : 4,
        color  : '#999999',
        speed  : 2.5,
        trail  : 100,
        top    : 0,
        left   : 0
    };
    $(this).resolveSpinner(state, context, style);
};


//Graceful handling of ajax processing. If there is a server generated error,
//it can be displayed in an alert or dialog box
$.fn.processAjaxSuccessUpdateHtmlOrShowDataOnFailure = function(dataOrHtml, updateId){
    try{
        jsonData = jQuery.parseJSON(dataOrHtml);
        console.log(jsonData);
        $('#FlashMessageBar').jnotifyAddMessage({
                text: jsonData.message,
                permanent: false,
                showIcon: true,
                type: jsonData.messageType
            }
        );
    } catch (e){
        $('#' + updateId).html(dataOrHtml);
    }
};

$.fn.spin = function(opts) {

    this.each(function() {
        var $this = $(this),
            data = $this.data();

        if (data.spinner) {
            data.spinner.stop();
            delete data.spinner;
        }
        if (opts !== false) {
            data.spinner = new Spinner($.extend({color: $this.css('color')}, opts)).spin(this);
        }
    });
    return this;
};


/*
 Autogrow textfields from https://github.com/rumpl/jquery.autogrow
 */
(function ($) {
    $.fn.autogrow = function () {
        this.filter('textarea').each(function () {
            var $this = $(this),
                minHeight = $this.height(),
                shadow = $('<div></div>').css({
                    position   :   'absolute',
                    top        : -50000,
                    left       : -50000,
                    visibility : 'hidden',
                    width      : $(this).width(),
                    fontSize   : $this.css('fontSize'),
                    fontFamily : $this.css('fontFamily'),
                    lineHeight : $this.css('lineHeight'),
                    resize     : 'none'
                }).addClass('shadow').appendTo($(this).parent()),
                update = function () {
                    var t = this;
                    setTimeout(function () {
                        var val = t.value.replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/&/g, '&amp;')
                            .replace(/\n/g, '<br/>&nbsp;');
                        if ($.trim(val) === '') {
                            val = 'a';
                        }
                        shadow.html(val);
                        $(t).height(Math.max(shadow[0].offsetHeight + 15, minHeight));
                    }, 0);
                };
            $this.change(update).keyup(update).keydown(update).focus(update);
            update.apply(this);
        });
        return this;
    };
}(jQuery));
$(window).ready(function(){
    //main menu flyouts or mbmenu releacment
    $('.nav:not(.user-menu-item):not(.clickable-mbmenu) > .parent').live({
        mouseenter: function() {
            if ( $(this).find('ul').length > 0 ){
                $(this).find('ul').stop(true, true).delay(0).fadeIn(100);
            }
        },
        mouseleave: function() {
            if ( $(this).find('ul').length > 0 ){
                $(this).find('ul').stop(true, true).fadeOut(250);
            }
        }
    });

    $('.user-menu-item').click(
        function(){
            if ( $(this).hasClass('nav-open') === false ){
                $('.nav-open').removeClass('nav-open');
                $(this).addClass('nav-open');
            } else {
                $('.nav-open').removeClass('nav-open');
            }
        }
    );

    $('.clickable-mbmenu').find('a').click(
        function(){
            var parentUl = $(this).parentsUntil('.clickable-mbmenu').parent();
            if ( parentUl.hasClass('nav-open') === false ){
                parentUl.addClass('nav-open');
            } else {
                parentUl.removeClass('nav-open');
            }
            return false;
        }
    );

    //Main nav hover
     $('#MenuView > ul > li > a, #RecentlyViewedView  > ul > li > a').hover(
        function(){
            $('> span:first-child', this).stop(true, true).fadeTo( 50, 1, 'linear' );
            $('> span:last-child', this).stop(true, true).animate({ color : '#555', color: '#fff' }, 50, 'linear');
        },
        function(){
            if ( $(this).parent().hasClass('active') === false ){
                $('> span:first-child',this).stop(true, true).fadeTo( 100, 0, 'linear' );
                $('> span:last-child', this).stop(true, true).animate({ color : '#fff', color: '#555' }, 100, 'linear');
            }
        }
    );

    //Main nav toggle hidden items
    $('.toggle-hidden-nav-items').click(
        function(){
            $('.hidden-nav-item').slideToggle(200);
            $(this).toggleClass('point-up');
        }
    );

    $.fn.resizeWhiteArea = function(){
        /*Resizes the app to fill the browser's window case smaller'*/
        var viewportHeight = $(window).height();
        var wrapperDivHeight = $('body > div').outerHeight(true)
        var appChromeHeight = 0;
        var bufferHeight = 0;
        var recentlyViewedHeight = 0;
        //if login
        if ( $('#LoginPageView').length > 0 ) {
            appChromeHeight = 40 + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                $('#LoginView').css('min-height',  bufferHeight);
            }
        //if admin area
        } else if ( $('.AdministrativeArea').length > 0 ) {
            appChromeHeight = 80 + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                $('.AppContent').css('min-height',  bufferHeight);
            }
        //rest of app
        } else {
            recentlyViewedHeight = $('#RecentlyViewedView').outerHeight(true);
            appChromeHeight = recentlyViewedHeight + $('#MenuView').outerHeight(true) + $('#HeaderView').outerHeight(true) + $('#FooterView').outerHeight(true);
            if ( wrapperDivHeight < viewportHeight  ){
                bufferHeight = viewportHeight - appChromeHeight;
                if ($('#RecentlyViewedView').length > 0){
                    $('#RecentlyViewedView').css('min-height', $('#RecentlyViewedView').height() + bufferHeight);
                } else {
                    $('.AppContent').css('min-height', $('.AppContainer').height() + 30);
                }
            }
        }
    };

    $(window).resize(function(){
      $(this).resizeWhiteArea();
    });

    $(this).resizeWhiteArea();

    /*Autogrow text areas*/
    $('textarea').autogrow();

    /*Label overlays input, address fields*/
    $(".overlay-label-field input").live('focus', function(){
        $(this).prev().fadeOut(100);
    });

    $(".overlay-label-field > input").live('blur', function(){
        if($(this).val() == "")
        {
            $(this).prev().fadeIn(250);
        }
    });

    $(".overlay-label-field input").live('change', function(){
        if($(this).val() != "")
        {
            $(this).prev().fadeOut(250);
        }
    });

    $(".overlay-label-field > input").each( function(){
        if($(this).val() == "") {
            $('label', $(this)).fadeIn(250);
        }
    });

    /*Adds padding to accommodate long labels for lang-label inputs*/
    $(".has-lang-label").each(function(){
        $(this).resolvePaddingForHasLangLabel(this);
    });

    $('.hasDropDown').live({
        mouseenter: function(){
            $('span', this).addClass('over-dd');
        },
        mouseleave: function(){
            $('span', this).removeClass('over-dd');
        }
    });

   //we're doing that because the multiselect widget isn't generated yet..
   window.setTimeout(
       $.fn.setCheckboxes = function(){
           $(this).setupCheckboxStyling( $('#search-form') );
           $(this).setupCheckboxStyling( $('#app-search') );
           if (  $('.items').length > 0 ){
               $(this).addClickListenerForCheckbox( $('.items') );
           }
       },
   1000 );

    /*Docking the save/cancel button in create view*/
    $(window).scroll( $(window).dockFloatingBar );
    $(this).dockFloatingBar();


    /*Spinner*/
   var style = {
        lines : 9,
        length : 3,
        width : 2,
        radius : 4,
        color : 'dark',
        speed : 2,
        trail : 100,
        top : 0,
        left : 0
    };
    $(this).resolveSpinner(true, '#stickyListLoadingArea', style, '.loading');
});



// query string related functions
$.extend({
    getUrlVars: function() {
        var vars = [], hash;
        var q = document.URL.split('?')[1];
        if(q != undefined){
            q = q.split('&');
            for(var i = 0; i < q.length; i++){
                hash = q[i].split('=');
                vars.push(hash[1]);
                vars[hash[0]] = hash[1];
            }
        }
        return vars;
    },
    getUrlVar: function(name) {
        return $.getUrlVars()[name];
    },
    hasActiveAjaxRequests: function() {
        return ($.active > 0);
    }
});

// TODO: @Shoaibi: Medium: Ask Nabeel/Sergio on extending buttonset widget to add this functionality before _create
$.fn.createButtonSetIfNotAlreadyExist = function(qualifier, classFlag) {
    classFlag = typeof classFlag !== 'undefined' ? classFlag : 'ui-buttonset';
    if ($(qualifier).hasClass(classFlag)) {
        return false;
    }
    $(qualifier).buttonset();
};

$.fn.isValidUrl = function(url)
{
    var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    return pattern.test(url);
};

$.fn.readCookie = function(name)
{
    var nameEQ = name + '=';
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++)
    {
        var c = ca[i];
        while (c.charAt(0)==' ')
            c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length,c.length);
    }
    return null;
};