<?php if ( ! empty( $result['matched'] ) ) : ?>

	<p><?php _e( 'Stamps.com matched the following address:', 'ultimatewoo-pro' ); ?></p>
	<address><?php echo implode( '<br/>', array_filter( array_map( 'wc_clean', $result['address'] ) ) ); ?></address>
	<p>
		<button type="submit" class="button button-primary stamps-action" data-stamps_action="accept_address"><?php _e( 'Accept', 'ultimatewoo-pro' ); ?></button>
		<button type="submit" class="button stamps-action" data-stamps_action="override_address"><?php _e( 'Continue without changes', 'ultimatewoo-pro' ); ?></button>
	</p>

<?php elseif ( ! empty( $result['matched_zip'] ) ) : ?>

		<p><?php _e( 'Stamps.com could not find an exact match for the shipping address.', 'ultimatewoo-pro' ); ?></p>
		<p><button type="submit" class="button stamps-action" data-stamps_action="override_address"><?php _e( 'Continue anyway', 'ultimatewoo-pro' ); ?></button>

<?php else : ?>

		<p><?php _e( 'Invalid shipping address - a label cannot be generated. Please correct the shipping address manually.', 'ultimatewoo-pro' ); ?></p>

<?php endif; ?>