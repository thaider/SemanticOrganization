( function ( mw ) {

	/**
	 * @class mw.semorg
	 * @singleton
	 */
	mw.semorg = {
	};

	$( '.semorg-toggle button' ).click( function(e) {
		$(this).parent().find( '.semorg-toggle-toggle, .semorg-toggle-original' ).toggle();
		var target = $(this).parent().data('semorg-toggle');
		$('.' + target).toggle();
		$(window).trigger('resize');
	});

}( mediaWiki ) );
