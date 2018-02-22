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
 * The base template for displaying vouchers and voucher templates.
 * This isn't a page template in the regular sense, instead it displays
 * a preview of the voucher template or is used as input for rendering the
 * PDF voucher.
 *
 * @type array $voucher_fields associative array of voucher fields with their values
 * @type string $image_url primary voucher image url
 * @type string $additional_image_url $image_url additional (second page) voucher image url
 *
 * @since 3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) or exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( ! $image_url ? 'voucher-no-image' : null ); ?>>

	<?php if ( is_customize_preview() ) : ?>
	<div id="no-image-message">
		<p><?php esc_html_e( 'Please set the voucher image to see the preview.', 'ultimatewoo-pro' ); ?></p>
	</div>
	<?php endif; ?>

	<div id="voucher-container">

		<div id="voucher">

			<div id="voucher-image" class="image-container"><img src="<?php echo esc_url( $image_url ); ?>" /></div>

			<?php foreach ( $voucher_fields as $field_id => $value ) : ?>
				<div id="<?php echo esc_attr( $field_id ); ?>" class="voucher-field js-voucher-field-pos"><?php echo wp_kses_post( $value ); ?></div>
			<?php endforeach; ?>

			<div id="voucher-additional-image" class="image-container"><img src="<?php echo esc_url( $additional_image_url ); ?>" /></div>

		</div>
	</div>

	<?php wp_footer(); ?>

</body>
</html>
