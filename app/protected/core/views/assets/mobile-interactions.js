$(window).ready( function(){

	$('#nav-trigger').click(
		function(e){
			e.preventDefault();
            if ( $('.AppContent').hasClass('nav-open') ){
                $('.AppNavigation').removeClass('high-z-index');
                $('#MenuView .nav').children().find('> div, > ul').fadeOut(100);
            }
            $('.AppContent').toggleClass('nav-open');
			return false;
		}
	);

    $('.mobile-flyout-trigger').click(
        function(e){
            $(this).next().fadeToggle();
            $('.AppNavigation').addClass('high-z-index');
            return false;
        }
    );

});
