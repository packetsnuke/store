<?php
/**
 * WooCommerce Checkout Add-Ons
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Checkout Add-Ons to newer
 * versions in the future. If you wish to customize WooCommerce Checkout Add-Ons for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-checkout-add-ons/ for more information.
 *
 * @package     WC-Checkout-Add-Ons/Views
 * @author      SkyVerge
 * @copyright   Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * View for a new add-on row
 *
 * @since 1.0.0
 * @version 1.0.0
 */
?>
<tr class="wc-checkout-add-on">
	<td class="check-column">
		<input type="checkbox" />
		<input type="hidden" name="wc-checkout-add-on-id[<?php echo $index; ?>]" value="<?php echo esc_attr( $add_on_id ); ?>" />
	</td>

	<td class="wc-checkout-add-on-name">
		<input type="text" name="wc-checkout-add-on-name[<?php echo $index; ?>]" value="<?php echo esc_attr( isset( $add_on->name ) ? $add_on->name : null ); ?>" class="js-wc-checkout-add-on-name" />
		<span class="wc-checkout-add-on-id"><?php echo $add_on_id ? 'ID: ' .  $add_on_id : ''; ?></span>
	</td>

	<td class="wc-checkout-add-on-label">
		<input type="text" name="wc-checkout-add-on-label[<?php echo $index; ?>]" value="<?php echo esc_attr( isset( $add_on->label ) ? $add_on->label : null ); ?>" class="js-wc-checkout-add-on-label" />
	</td>

	<td class="wc-checkout-add-on-type">
		<select name="wc-checkout-add-on-type[<?php echo $index; ?>]" class="js-wc-checkout-add-on-type wc-enhanced-select" style="width: 100px;">
			<?php foreach ( $add_on_types as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $add_on->type ) ? $add_on->type : null, $value );?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>

	<td class="wc-checkout-add-on-options-costs">
		<input type="text" name="wc-checkout-add-on-options-costs[<?php echo $index; ?>]" value="<?php echo esc_attr( isset( $add_on->options_costs ) ? $add_on->options_costs : null ); ?>" class="js-wc-checkout-add-on-options-costs placeholder" placeholder="<?php esc_attr_e( 'Pipe (|) separates options', 'ultimatewoo-pro' ); ?>" />
	</td>

	<td class="wc-checkout-add-on-attributes">
		<select name="wc-checkout-add-on-attributes[<?php echo $index; ?>][]" class="js-wc-checkout-add-on-attributes wc-enhanced-select" multiple="multiple" style="width: 250px;">
			<?php foreach ( $add_on_attributes as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $add_on->$value ) ? $add_on->$value : null ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>

	<?php if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) ) : ?>

		<td class="wc-checkout-add-on-taxes">
			<select name="wc-checkout-add-on-tax_status[<?php echo $index; ?>]" class="js-wc-checkout-add-on-tax_status">
				<option value="taxable" <?php selected( isset( $add_on ) ? $add_on->is_taxable() : null ); ?>><?php esc_html_e( 'Taxable', 'ultimatewoo-pro' ); ?></option>
				<option value="none" <?php selected( isset( $add_on ) ? ! $add_on->is_taxable() : true ); ?>><?php esc_html_e( 'Not taxable', 'ultimatewoo-pro' ); ?></option>
			</select>

			<select name="wc-checkout-add-on-tax_class[<?php echo $index; ?>]" class="js-wc-checkout-add-on-tax_class">
				<?php foreach ( $classes_options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $add_on->tax_class ) && $add_on->tax_class === $value );?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>

	<?php endif; ?>

	<td class="js-wc-checkout-add-on-draggable">
		<img src="<?php echo wc_checkout_add_ons()->get_plugin_url() ?>/assets/images/draggable-handle.png" />
	</td>
</tr>
