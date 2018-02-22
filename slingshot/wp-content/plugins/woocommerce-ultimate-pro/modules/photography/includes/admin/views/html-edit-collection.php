<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e( 'Visibility', 'ultimatewoo-pro' ); ?></label></th>
	<td>
		<select name="collection_visibility" id="collection-visibility" class="postform">
			<option value="restricted" <?php selected( $visibility, 'restricted' ); ?>><?php _e( 'Restricted', 'ultimatewoo-pro' ); ?></option>
			<option value="public" <?php selected( $visibility, 'public' ); ?>><?php _e( 'Public', 'ultimatewoo-pro' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Restricted: only users in this collection can access. Public: any customers can access.', 'ultimatewoo-pro' ); ?></p>
	</td>
</tr>

<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'ultimatewoo-pro' ); ?></label></th>
	<td>
		<div id="collection-thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
		<div style="line-height: 60px;">
			<input type="hidden" id="collection-thumbnail-id" name="collection_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
			<button type="submit" class="upload-image-button button"><?php _e( 'Upload/Add image', 'ultimatewoo-pro' ); ?></button>
			<button type="submit" class="remove-image-button button"><?php _e( 'Remove image', 'ultimatewoo-pro' ); ?></button>
		</div>
		<div class="clear"></div>
	</td>
</tr>
