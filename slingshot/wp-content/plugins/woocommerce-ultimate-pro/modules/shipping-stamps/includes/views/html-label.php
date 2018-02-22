<?php if ( $label->is_valid() ) : ?>
	<a href="<?php echo esc_attr( $label->get_label_url() ); ?>" target="_blank" class="label-preview">
		<?php if ( strstr( $label->get_label_url(), '.png' ) || strstr( $label->get_label_url(), '.gif' ) || strstr( $label->get_label_url(), '.jpg' ) ) : ?>
			<img src="<?php echo esc_url( $label->get_label_url() ); ?>" />
		<?php else : ?>
			<?php _e( 'View', 'ultimatewoo-pro' ); ?>
		<?php endif; ?>
	</a>
<?php else : ?>
	<p><?php _e( 'Invalid label', 'ultimatewoo-pro' ); ?>
<?php endif; ?>