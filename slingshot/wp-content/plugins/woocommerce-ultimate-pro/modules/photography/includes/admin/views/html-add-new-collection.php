<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$settings = get_option( 'woocommerce_photography', array() );
$default  = isset( $settings['collections_default_visibility'] ) ? $settings['collections_default_visibility'] : 'restricted';
?>

<div class="form-field">
	<label for="collection-visibility"><?php _e( 'Visibility', 'ultimatewoo-pro' ); ?></label>
	<select name="collection_visibility" id="collection-visibility" class="postform">
		<option value="restricted" <?php selected( $default, 'restricted', true ); ?>><?php _e( 'Restricted', 'ultimatewoo-pro' ); ?></option>
		<option value="public" <?php selected( $default, 'public', true ); ?>><?php _e( 'Public', 'ultimatewoo-pro' ); ?></option>
	</select>
	<p><?php _e( 'Restricted: only users in this collection can access. Public: any customers can access.', 'ultimatewoo-pro' ); ?></p>
</div>

<div class="form-field">
	<label><?php _e( 'Thumbnail', 'ultimatewoo-pro' ); ?></label>
	<div id="collection-thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
	<div style="line-height: 60px;">
		<input type="hidden" id="collection-thumbnail-id" name="collection_thumbnail_id" />
		<button type="button" class="upload-image-button button"><?php _e( 'Upload/Add image', 'ultimatewoo-pro' ); ?></button>
		<button type="button" class="remove-image-button button"><?php _e( 'Remove image', 'ultimatewoo-pro' ); ?></button>
	</div>
	<div class="clear"></div>
</div>
