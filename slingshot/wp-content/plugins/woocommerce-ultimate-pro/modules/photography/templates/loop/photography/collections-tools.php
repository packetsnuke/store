<?php
/**
 * Photography loop collections tools.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$term      = get_queried_object();
$term_id   = $term->term_id;
$term_name = $term->slug;

if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '>=' ) ) {
	$class = '';
} else {
	$class = ' legacy-quantity';
}

?>

<div class="tools">
	<div class="global-quantity<?php echo $class; ?>">
		<?php _e( 'Select', 'ultimatewoo-pro' ); ?>
		<?php
			woocommerce_quantity_input( array(
				'input_name'  => '',
				'input_value' => apply_filters( 'wc_photography_collections_quantity_input_value', 0, $term_id, $term_name ),
				'min_value'   => apply_filters( 'wc_photography_collections_quantity_input_min', 0, $term_id, $term_name ),
				'max_value'   => apply_filters( 'wc_photography_collections_quantity_input_max', '', $term_id, $term_name )
			), 0 );
		?>
		<?php _e( 'of each photo', 'ultimatewoo-pro' ); ?>
	</div>

	<button type="submit" class="button"><?php _e( 'Add to cart', 'ultimatewoo-pro' ); ?></button>
</div>
