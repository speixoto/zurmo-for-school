$(window).ready( function(){

	$('#nav-trigger').click(
		function(e){
			e.preventDefault();
			$('.AppContent').toggleClass('nav-open');
			return false;
		}
	);

});
