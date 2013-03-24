$(window).ready( function(){

	$('#nav-trigger').click(
		function(e){
			e.preventDefault();
			$('.AppContent').toggleClass('nav-open');
			return false;
		}
	);


    $('.mobile-flyout-trigger').click(
        function(e){
            $(this).next().fadeToggle();
            return false;
        }
    );

});
