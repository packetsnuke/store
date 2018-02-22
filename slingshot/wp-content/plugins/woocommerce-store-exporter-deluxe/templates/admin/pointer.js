var $j = jQuery.noConflict();
$j(function() {
	$j(document).ready( function($) {

		woo_ce_open_pointer(0);

		function woo_ce_open_pointer(i) {

			pointer = woo_ce_pointers.pointers[i];
			options = $.extend( pointer.options, {
				close: function() {
					$.post( ajaxurl, {
						pointer: pointer.pointer_id,
						action: 'woo_ce_dismiss_pointer'
					});
				}
			});

			$(pointer.target).pointer( options ).pointer('open');

		}

	});
});