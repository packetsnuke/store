( function( $ ) {

	$( function() {

		/**
		 * Display the pricing fields if is a photography.
		 */
		$( '#product-type' ).on( 'change', function() {
			if ( 'photography' === $( this ).val() ) {
				$( '#general_product_data .pricing' ).show();
			}
		}).change();

	});

}( jQuery ) );
