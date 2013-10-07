$(window).ready(function(){

    var highestZIndex = 100;

    $('.gd-collection-panel').on({
        mouseenter: function(event) {
            $('> div', this).css('z-index', highestZIndex++).animate({width:'575px', top:'-95px'}, 250, 'linear');
        },
        mouseleave: function(event) {
            $('> div', this).animate({width:'285px', top:0}, 250, 'linear', function(){
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
        $('#gd-carousel').animate({ marginLeft : step.toString() }, animationTime, easingType);
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
        $('#gd-carousel').animate({ marginLeft : step.toString() }, animationTime, easingType);
        return false;
    });

});