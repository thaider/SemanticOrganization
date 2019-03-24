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

	$(document).ready( function() {
		agendaTimerSetup();
		agendaTimerClock();
	});

	function agendaTimerSetup() {
		$( '.semorg-field-agenda-time-suffix' ).each( function( index ) {
			$(this).after('<i class="fa fa-play semorg-agenda-timer"></i><span class="semorg-agenda-timer-time"></span>');
		});

		agendaTimerCalc();

		$(document).on('click', '.semorg-agenda-timer', function(e) {
			var $time_planned = $(this).parent().find('.semorg-field-agenda-time');
			var time_planned = $time_planned.val();

			var name = $time_planned.attr('name');

			var $time_end = $('[name="'+name.replace('time','end')+'"]');
			var $time_start = $('[name="'+name.replace('time','start')+'"]');
			var $time_real = $('[name="'+name.replace('time','time-real')+'"]');

			if( $(this).hasClass( 'fa-play' ) ) {
				$time_start.val( Date.now() );
				$time_end.val( '' );
				$time_real.val( '' );
				$(this).siblings('.semorg-agenda-timer-time').text(time_planned + ':00');
			} else {
				$time_end.val( Date.now() );
				$time_real.val( Math.floor( ( $time_end.val() - $time_start.val() ) / ( 60 * 1000 ) ) );
			}
			$(this).toggleClass( 'fa-play fa-stop' );
		});
	}

	function agendaTimerCalc() {
		$( '.semorg-agenda-timer-time' ).each( function( index ) {
			var $time_planned = $(this).parent().find('.semorg-field-agenda-time');
			var time_planned = $time_planned.val();

			var name = $time_planned.attr('name');

			var $time_start = $('[name="'+name.replace('time','start')+'"]');
			var time_start = $time_start.val();

			/* not yet started */
			if( time_start === '' ) {
				$(this).text('');
			} else {
				/* running */
				var $time_end = $('[name="'+name.replace('time','end')+'"]');
				var time_end = $time_end.val();
				var $time_real = $('[name="'+name.replace('time','time-real')+'"]');
				var time_real = $time_real.val();
				if( time_end === '' ) {
					var time_elapsed = Date.now() - time_start;
					var time_remaining = msec2mins( ( time_planned * 60 * 1000 ) - time_elapsed );
					$(this).html( time_remaining.toString().replace( '-', '<b>+</b>' ));

				/* finished */
				} else {
					$(this).text( msec2mins( time_end - time_start ) );
				}
			}
		});
	}

	function agendaTimerClock() {
		agendaTimerCalc();
		window.setTimeout( agendaTimerClock, 1000 );
	}

	function msec2mins( msec ) {
		var negative = '';
		if( msec < 0 ) {
			msec = msec * -1;
			negative = '-';
		}
		var secs = Math.floor( ( msec % ( 60 * 1000 ) ) / 1000 );
		var mins = Math.floor( msec / ( 60 * 1000 ) );
		if( secs < 10 ) {
			secs = '0' + secs;
		}
		return negative + mins + ':' + secs;
	}
}( mediaWiki ) );
