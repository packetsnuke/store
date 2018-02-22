<?php
/**
 * WooCommerce PDF Product Vouchers
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Templates
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * The frontend product page voucher fields
 *
 * @type \WC_Product $product product instance
 * @type int $product_id the product ID
 * @type \WC_Voucher_Template $voucher_template voucher template object
 * @type string[] $fields array of user input voucher fields
 * @type string[] $images array of available voucher images
 * @type string $selected_image the currently selected voucher image
 *
 * @version 3.1.0
 * @since 1.2
 */

defined( 'ABSPATH' ) or exit;

?>

<div class="voucher-fields-wrapper<?php echo $product->is_type( 'variation' ) ? '-variation' : ''; ?>" id="voucher-fields-wrapper-<?php echo esc_attr( $product_id ); ?>">

	<input type="hidden" name="voucher_template_id[<?php echo esc_attr( $product_id ); ?>]" value="<?php echo esc_attr( $voucher_template->id ); ?>" />

	<div class="voucher-fields">
		<?php

		foreach ( $fields as $name => $field ) :

			$key   = $name . '[' . $product_id . ']';
			$value = isset( $_POST[ $name ] ) && ! empty( $_POST[ $name ][ $product_id ] ) ? $_POST[ $name ][ $product_id ] : null;

			woocommerce_form_field( $key, $field, $value );
		endforeach;

		?>

		<div class="voucher-image-options">
		<?php $i = 0; foreach ( $images as $image_id => $image ) : $i++;

			if ( count( $images ) > 1 )  {
				$title = sprintf( esc_attr__( 'Voucher Option %d', 'ultimatewoo-pro' ), $i );
			} else {
				$title = esc_attr__( 'Voucher Image', 'ultimatewoo-pro' );
			}
			?>

			<div class="voucher-image-option">
				<a href="<?php echo esc_url( $image['image'] ); ?>" title="<?php echo esc_attr( $title ); ?>" rel="prettyPhoto[voucher-<?php echo esc_attr( $product_id ); ?>]" data-rel="prettyPhoto[voucher-<?php echo esc_attr( $product_id ); ?>]" data-large_image_width="<?php echo esc_attr( $image['image_width'] ); ?>" data-large_image_height="<?php echo esc_attr( $image['image_height'] ); ?>"><img src="<?php echo esc_url( $image['thumb'] ); ?>" title="<?php echo esc_attr( $title ); ?>" alt="<?php echo esc_attr( $title ); ?>" /></a>

				<?php if ( count( $images ) > 1 ) : ?>
					<input type="radio" name="voucher_image[<?php echo esc_attr( $product_id ); ?>]" value="<?php echo esc_attr( $image_id ); ?>" <?php checked( $selected_image, $image_id ); ?> id="voucher-image-<?php echo esc_attr( $i ); ?>" />
				<?php else : ?>
					<input type="hidden" name="voucher_image[<?php echo esc_attr( $product_id ); ?>]" value="<?php echo esc_attr( $image_id ); ?>" />
				<?php endif; ?>
			</div>

		<?php endforeach; ?>
		</div>

	</div>

</div>
