<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// HTML template for Booking Sorting widget on Store Exporter screen
	function woo_ce_booking_sorting() {

		$booking_orderby = woo_ce_get_option( 'booking_orderby', 'ID' );
		$booking_order = woo_ce_get_option( 'booking_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Booking Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="booking_orderby">
		<option value="ID"<?php selected( 'ID', $booking_orderby ); ?>><?php _e( 'Booking Number', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $booking_orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $booking_orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $booking_orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="booking_order">
		<option value="ASC"<?php selected( 'ASC', $booking_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $booking_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Bookings within the exported file. By default this is set to export Bookings by Booking ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// Add Export to... to Booking screen
	function woo_ce_extend_woocommerce_admin_booking_actions( $actions, $booking ) {

/*
		$actions['export_csv'] = array(
			'url' 		=> admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
			'name' 		=> __( 'Export to CSV', 'woocommerce-bookings' ),
			'action' 	=> "export_booking_csv"
		);
*/
		return $actions;

	}
	add_filter( 'woocommerce_admin_booking_actions', 'woo_ce_extend_woocommerce_admin_booking_actions', 10, 2 );

	/* End of: WordPress Administration */

}
?>