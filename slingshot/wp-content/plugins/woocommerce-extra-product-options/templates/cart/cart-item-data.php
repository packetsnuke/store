<?php
/**
 * Cart item data (when outputting non-flat)
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/cart/cart-item-data.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version 	2.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$separator = TM_EPO()->tm_epo_separator_cart_text;
?>
<dl class="variation">
	<?php foreach ( $item_data as $data ) : 
		$is_epo = false;
		$show_dt = true;
		$show_dd = true;
		$class_name = '';
		$class_value = '';
		if (isset($data['tm_label'])){
			$is_epo = true;
			$class_name = 'tc-name ';
			$class_value = 'tc-value ';
		}
	 
		if (!isset($data['display']) && isset($data['value'])){
			$data['display'] = $data['value'];
		}
		if ($is_epo && $data['key']==='' ){
			$show_dt = false;
		}
		if ($is_epo && $data['display']==='' ){
			$show_dd = false;
		}
		if ( TM_EPO()->tm_epo_cart_field_display=="link" || ! $show_dd ){
			$separator='';
		}
	?>
		<?php if ($show_dt):?>
			<dt class="<?php echo $class_name; ?>variation-<?php echo sanitize_html_class( $data['key'] ); ?>"><?php echo wp_kses_post( $data['key'] ); ?><?php echo $separator;?></dt>
		<?php else:?>
			<dt class="<?php echo $class_name; ?>tc-hidden-variation"></dt>
		<?php endif;?>
		<?php if ($show_dd):?>
			<dd class="<?php echo $class_value; ?>variation-<?php echo sanitize_html_class( $data['key'] ); ?>"><?php echo wp_kses_post( wpautop( $data['display'] ) ); ?></dd>
		<?php else:?>
			<dd class="<?php echo $class_value; ?>tc-hidden-variation"></dd>
		<?php endif;?>
	<?php endforeach; ?>
</dl>
