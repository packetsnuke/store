<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * i18n collections visibility.
 *
 * @param  int $collection_id
 *
 * @return string
 */
function wc_photography_i18n_collection_visibility( $collection_id, $visibility = '' ) {
	if ( ! $visibility ) {
		$visibility = get_woocommerce_term_meta( $collection_id, 'visibility', true );
	}

	$i18n = array(
		'restricted' => __( 'Restricted', 'ultimatewoo-pro' ),
		'public'     => __( 'Public', 'ultimatewoo-pro' )
	);

	if ( isset( $i18n[ $visibility ] ) ) {
		return $i18n[ $visibility ];
	}

	return $visibility;
}

/**
 * Is collection public.
 *
 * @param  int $collection_id
 *
 * @return bool
 */
function wc_photography_is_collection_public( $collection_id ) {
	$visibility = get_woocommerce_term_meta( $collection_id, 'visibility', true );

	return ( 'public' == $visibility );
}
