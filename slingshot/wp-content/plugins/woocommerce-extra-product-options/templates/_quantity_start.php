<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}



$located = wc_locate_template( 'tm-element-quantity-start.php', TM_EPO()->get_namespace(), apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, NULL, NULL ) );

if ( ! file_exists( $located ) ) {
	wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'woocommerce' ), '<code>' . $located . '</code>' ), '2.1' );
	return;
}


include( $located );

