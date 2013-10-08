$(window).ready(function(){

    var highestZIndex = 100;

    $('.gd-collection-panel').on({
        mouseenter: function(event) {
            $('> div', this).css('z-index', highestZIndex++).stop( true, true ).animate({width:'570px', top:'-95px'}, 250, 'linear');
        },
        mouseleave: function(event) {
            $('> div', this).stop( true, true ).animate({width:'285px', top:0}, 250, 'linear', function(){
                $(this).css('z-index', 0);
            });
        }
    });

    var carouselWidth = jQuery('#gd-carousel').outerWidth();
    var viewfinderWidth = 1140;
    var maxLeft = viewfinderWidth - carouselWidth;
    var maxRight = 0;
    var myLeft;
    var fixedStep = 285;
    var animationTime = 425;
    var easingType = 'easeOutQuint';

    $('#nav-right').click(function(){
        var step = fixedStep;
        myLeft = parseInt($('#gd-carousel').css('margin-left'));
        if((myLeft - step) <= maxLeft){
            step = maxLeft;
        } else {
            step = '-=' + fixedStep.toString();
        }
        $('#gd-carousel').stop( true, true ).animate({ marginLeft : step.toString() }, animationTime, easingType, getCurrentVisibleCollections);
        return false;
    });

    $('#nav-left').click(function(){
        var step = fixedStep;
        myLeft = parseInt($('#gd-carousel').css('margin-left'));
        if( (myLeft + step) > maxRight){
            step = 0;
        } else {
            step = '+=' + fixedStep.toString();
        }
        $('#gd-carousel').stop( true, true ).animate({ marginLeft : step.toString() }, animationTime, easingType, getCurrentVisibleCollections);
        return false;
    });

    var carouselPosition = 0;

    function getCurrentVisibleCollections(){
        $('.gd-collection-panel').removeClass('visible-panel');
        $('.gd-collection-panel').removeClass('visible-panel-last');

        carouselPosition = parseInt($('#gd-carousel').css('margin-left'));

        var firstVisible = Math.abs(carouselPosition / fixedStep) + 1;
        var i;
        for (i = firstVisible; i < firstVisible + 4; i++){
            $( '#gd-carousel > .gd-collection-panel:nth-child(' + i + ')').addClass('visible-panel');
        }

        $( '#gd-carousel > .gd-collection-panel:nth-child(' + (i-1) + ')').addClass('visible-panel-last');
    }

    getCurrentVisibleCollections();

});