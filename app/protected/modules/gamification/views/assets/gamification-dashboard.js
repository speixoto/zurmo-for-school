$(window).ready(function(){

    var highestZIndex = 100;
    var leftAnimation = 0;
    var animationTime = 425;
    var easingType = 'easeOutQuint';
    var carouselWidth = $('#gd-carousel').outerWidth();
    var viewfinderWidth = 1140;
    var maxLeft = viewfinderWidth - carouselWidth;
    var maxRight = 0;
    var myLeft;
    var fixedStep = 285;
    var carouselPosition = 0;
    var firstVisible = 1;

    $('#gd-carousel').on('mouseenter', '.gd-collection-panel', function() {
        if ($(this).hasClass('visible-panel-last') === true){
            leftAnimation = -285;
        } else {
            leftAnimation = 0;
        }
        $('> div', this).css('z-index', highestZIndex++).stop( true, true ).animate({
            width:'570px', top:'-95px', left: leftAnimation
        }, animationTime, easingType);
    });

    $('#gd-carousel').on('mouseleave', '.gd-collection-panel', function() {
        $('> div', this).stop( true, true ).animate({width:'285px', top:0, left: 0}, animationTime, easingType,
            function(){
                $(this).css('z-index', 0);
            });
        }
    );

    $('#nav-right').click(function(){
        var step = fixedStep;
        myLeft = parseInt($('#gd-carousel').css('margin-left'));
        if((myLeft - step) <= maxLeft){
            step = maxLeft;
            currentLeftMargin = maxLeft;
        } else {
            step = '-=' + fixedStep.toString();
        }
        $('#gd-carousel').stop( true, true ).animate({ marginLeft : step.toString() }, animationTime, easingType);
        getCurrentVisibleCollections('forward');
        return false;
    });

    $('#nav-left').click(function(){
        var step = fixedStep;
        myLeft = parseInt($('#gd-carousel').css('margin-left'));
        if( (myLeft + step) > maxRight){
            step = 0;
            currentLeftMargin = 0;
        } else {
            step = '+=' + fixedStep.toString();
        }
        $('#gd-carousel').stop( true, true ).animate({ marginLeft : step.toString() }, animationTime, easingType);
        getCurrentVisibleCollections('back');
        return false;
    });

    function getCurrentVisibleCollections(direction){
        $('.gd-collection-panel').removeClass('visible-panel');
        $('.gd-collection-panel').removeClass('visible-panel-last');
        carouselPosition = parseInt($('#gd-carousel').css('margin-left'));
        var i;
        if(direction === 'forward'){
            firstVisible++;
        }
        if(direction === 'back'){
            firstVisible--;
        }
        for (i = firstVisible; i < firstVisible + 4; i++){
            $( '#gd-carousel > .gd-collection-panel:nth-child(' + i + ')').addClass('visible-panel');
        }
        $( '#gd-carousel > .gd-collection-panel:nth-child(' + (i-1) + ')').addClass('visible-panel-last');
    }
    getCurrentVisibleCollections();

    $(document).on('mouseover', '.gd-collection-item img', function(event) {
        $(this).qtip({
            overwrite: false,
            content: {'attr':'data-tooltip'},
            position: {my: 'bottom center', at: 'top center'},
            show: {
                event: event.type,
                ready: true
            }
        }, event);
    })
});