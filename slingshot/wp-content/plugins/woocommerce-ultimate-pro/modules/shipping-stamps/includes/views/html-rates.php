<p><?php _e( 'Choose a rate to generate a shipping label:', 'ultimatewoo-pro' ); ?></p>

<table class="widefat wc-stamps-rates">
	<?php foreach ( $rates as $rate ) : ?>
		<tr>
			<td>
				<input type="radio" id="<?php echo sanitize_title( $rate->service . '-' . $rate->package ); ?>" name="stamps_rate" value="<?php echo esc_attr( json_encode( $rate->rate_object ) ); ?>" />
			</td>
			<th><label for="<?php echo sanitize_title( $rate->service . '-' . $rate->package ); ?>"><?php echo esc_html( $rate->name . ' (' . $rate->package . ')' ); ?></label></th>
			<td><?php echo wc_price( $rate->cost ); ?></td>
		</tr>
		<tr class="addons" style="display:none;">
			<td></td>
			<td colspan="2">
				<?php
					if ( isset( $rate->rate_object->AddOns ) && isset( $rate->rate_object->AddOns->AddOnV7 ) ) {
						WC_Stamps_Order::addons_html( $rate );
					}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<p>
	<?php if ( 'US' !== ( version_compare( WC_VERSION, '3.0', '<' ) ? $order->shipping_country : $order->get_shipping_country() ) ) : ?>
		<button type="submit" class="button button-primary stamps-action" data-stamps_action="customs"><?php _e( 'Enter customs information', 'ultimatewoo-pro' ); ?></button>
	<?php else : ?>
		<button type="submit" class="button button-primary stamps-action" data-stamps_action="request_label"><?php _e( 'Request label', 'ultimatewoo-pro' ); ?></button>
	<?php endif; ?>
	<button type="submit" class="button stamps-action" data-stamps_action="define_package"><?php _e( 'Back', 'ultimatewoo-pro' ); ?></button>
</p>
