<table class="widefat labels">
	<?php
		foreach ( $labels as $label ) {
			if ( $label->is_valid() ) {
				?>
				<tr>
					<td width="64"><?php include( 'html-label.php' ); ?></td>
					<td>
						<?php
							if ( $tracking = $label->get_tracking_number() ) {
								echo '<a href="https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=' . esc_attr( $tracking ) . '">' . esc_html( $tracking ) . '</a>' . '<br/>';
							}
							if ( $date = $label->get_value( 'ShipDate' ) ) {
								echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $date ) ) ) . '<br/>';
							}
						?>
						<div class="label-actions">
							<a href="#" class="stamps-action cancel-label" data-stamps_action="cancel_label" data-id="<?php echo esc_attr( $label->get_id() ); ?>" data-confirm="<?php echo esc_attr( __( 'Are you sure you want to cancel this label? This action cannot be undone.', 'ultimatewoo-pro' ) ) ?>"><?php _e( 'Refund', 'ultimatewoo-pro' ) ?></a> | <a href="#" class="stamps-action delete-label" data-stamps_action="delete_label" data-id="<?php echo esc_attr( $label->get_id() ); ?>" data-confirm="<?php echo esc_attr( __( 'Are you sure you want to delete this label? This action cannot be undone.', 'ultimatewoo-pro' ) ) ?>"><?php _e( 'Delete', 'ultimatewoo-pro' ) ?></a>
						</div>
					</td>
				</tr>
				<?php
			}
		}
	?>
</table>
<p><button type="submit" class="button stamps-action" data-stamps_action="define_package"><?php _e( 'Request another label', 'ultimatewoo-pro' ); ?></button></p>