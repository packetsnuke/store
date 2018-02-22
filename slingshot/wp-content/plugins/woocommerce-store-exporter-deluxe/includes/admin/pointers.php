<?php
// @mod - We'll do this once 2.4+ goes out
function woo_ce_ajax_dismiss_pointer() {

	// Check the User has the view_woocommerce_reports capability
	$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

	if( current_user_can( $user_capability ) ) {
		$pointer_id = ( isset( $_POST['pointer'] ) ? sanitize_text_field( $_POST['pointer'] ) : false );
		$user_id = get_current_user_id();

		if( empty( $user_id ) )
			return;

		// Get existing dismissed pointers
		$pointers = get_user_meta( $user_id, WOO_CD_PREFIX . '_dismissed_pointers', true );
		if( $pointers == false )
			$pointers = array();

		if( in_array( $pointer_id, $pointers ) == false )
			$pointers[] = $pointer_id;

		$pointers = implode( ',', $pointers );

		// Save the updated dismissed pointers
		// update_user_meta( $user_id, WOO_CD_PREFIX . '_dismissed_pointers', $pointers );

	}

}
// add_action( 'wp_ajax_woo_ce_dismiss_pointer', 'woo_ce_ajax_dismiss_pointer' );

// @mod - We'll do this once 2.4+ goes out
function woo_ce_admin_register_pointer_testing( $pointers = array() ) {

	$pointers['xyz140'] = array(
		'target' => '#product',
		'options' => array(
			'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
				__( 'Title' ,'plugindomain'),
				__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.','plugindomain')
			),
			'position' => array( 'edge' => 'top', 'align' => 'left' )
		)
	);
	return $pointers;

}
// add_filter( 'woo_ce_admin_pointers-woocommerce_page_woo_ce', 'woo_ce_admin_register_pointer_testing' );
?>