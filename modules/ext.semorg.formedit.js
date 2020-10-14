( function ( mw ) {
	var prompt;
	var last = new Date();
	var timer = 10*60*1000;

	// show prompt
	function showPrompt() {
		updateTime();
		prompt.show();
	}

	// update prompt message
	function updateTime() {
		let now = new Date();
		let minutes = Math.round( ( now - last ) / ( 60*1000 ) );
		let msg = mw.message( 'semorg-prompt-save-text', minutes ).text();
		$( '.semorg-prompt-save-text' ).text( msg );
		window.setTimeout( updateTime, 10*1000 );
	}

	// save on click and hide prompt until timeout is reached again
	$(document).on( 'click', '#semorg-prompt-save-button', function(e) {
		e.preventDefault();
		$('#semorg-prompt-save-button').addClass( 'disabled' );
		let url = $('#pfForm').attr('action');
		let data = $('#pfForm').serialize() + '&wpSave=Save';
		$.post( url, data )
			.done(function(data, textStatus, jqXHR) {
				prompt.hide();
				$('#semorg-prompt-save-button').removeClass( 'disabled' );
				last = new Date();
				window.setTimeout( showPrompt, timer );
			})
			.fail(function() {
				// TODO: implement error message
			});
	});

	// initialise prompt message and set timer for showing it
	$(document).ready( function() {
		let spinner = '<i class="fa fa-spinner fa-spin"></i>';
		let button = '<a href="#" class="btn btn-primary btn-sm" id="semorg-prompt-save-button">' + mw.message( 'semorg-prompt-save-button-text' ).text() + spinner + '</a>';
		prompt = $('<div class="semorg-prompt-save-wrapper"><div class="semorg-prompt-save"><span class="semorg-prompt-save-text"></span>' + button + '</div></div>');
		$('body').append( prompt );

		window.setTimeout( showPrompt, timer );
	});
}( mediaWiki ) );
