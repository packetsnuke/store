<?php if ( $key && $status ) : // Site Status ?>
	<p id="site-status">
		<?php printf( '<strong>%s</strong>', __( 'Site Status: ', 'ultimatewoo-pro' ) ); ?>
		<?php
			echo ucwords( $status );
			if ( $status == 'valid' ) {
				printf( ' <img src="%s" width="20px" />', ULTIMATEWOO_PLUGIN_DIR_URL . '/assets/img/green-check.png' );
			}
		?>
	</p>
<?php endif; ?>

<?php if ( $key && $exp ) : // Exp Date ?>
	<p id="expiration">
		<?php printf( '<strong>%s</strong>', __( 'Expires: ', 'ultimatewoo-pro' ) ); ?>
		<?php echo date( 'F j, Y', strtotime( $exp ) ); ?>
	</p>
<?php endif; ?>

<?php if ( $key && is_numeric( $limit ) ) : // Limit ?>
	<p id="limit">
		<?php printf( '<strong>%s</strong>', __( 'Limit: ', 'ultimatewoo-pro' ) ); ?>
		<?php
			if ( $limit === 0 ) {
				_e( 'Unlimited', 'ultimatewoo-pro' );
			} else {
				echo $limit;
			}
		?>
	</p>
<?php endif; ?>

<?php if ( $key && $remaining ) : // Activations Left ?>
	<p id="remaining">
		<?php printf( '<strong>%s</strong>', __( 'Activations Remaining: ', 'ultimatewoo-pro' ) ); ?>
		<?php
			if ( $limit == 0 ) {
				_e( 'Unlimited', 'ultimatewoo-pro' );
			} else {
				echo $remaining;
			}
		?>
	</p>
<?php endif; ?>

<?php if ( ! $key ) : // No license saved ?>
	<p id="no-license"><?php _e( 'No License', 'ultimatewoo-pro' ); ?></p>
<?php endif; ?>