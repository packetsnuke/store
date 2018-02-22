<h2><?php _e('Twitter', 'ultimatewoo-pro'); ?></h2>

<p>
	<strong><?php _e('Twitter Handle:', 'ultimatewoo-pro'); ?></strong>
	<?php
	$handle = get_user_meta( get_current_user_id(), 'twitter_handle', true );

	if ( !$handle ) {
		_e('<em>not set</em>', 'ultimatewoo-pro');
	} else {
		echo '@'. sanitize_user( $handle );
	}
	?>
	<a href="edit-account" style="margin-left: 50px;"><?php _e('Change', 'ultimatewoo-pro'); ?></a>
</p>