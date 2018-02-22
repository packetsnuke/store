<?php
ob_start();
include( 'html-customs-item.php' );
$line_html = ob_get_clean();
?>
<h4><?php _e( 'Customs', 'ultimatewoo-pro' ); ?></h4>
<table class="form-table">
	<tr>
		<th><label><?php _e( 'Content type', 'ultimatewoo-pro' ); ?></label></th>
		<td>
			<select name="stamps_customs_content_type">
				<option value="Merchandise"><?php _e( 'Merchandise', 'ultimatewoo-pro' ); ?></option>
				<option value="Commercial Sample"><?php _e( 'Commercial Sample', 'ultimatewoo-pro' ); ?></option>
				<option value="Gift"><?php _e( 'Gift', 'ultimatewoo-pro' ); ?></option>
				<option value="Document"><?php _e( 'Document', 'ultimatewoo-pro' ); ?></option>
				<option value="Returned Goods"><?php _e( 'Returned Goods', 'ultimatewoo-pro' ); ?></option>
				<option value="Humanitarian Donation"><?php _e( 'Humanitarian Donation', 'ultimatewoo-pro' ); ?></option>
				<option value="Dangerous Goods"><?php _e( 'Dangerous Goods', 'ultimatewoo-pro' ); ?></option>
				<option value="Other"><?php _e( 'Other', 'ultimatewoo-pro' ); ?></option>
			</select>

			<label class="other_describe">
				<input type="text" name="stamps_customs_other" maxlength="20" placeholder="<?php _e( 'Other description', 'ultimatewoo-pro' ); ?>" >
			</label>
		</td>
	</tr>
	<tr>
		<th><label><?php _e( 'Comments', 'ultimatewoo-pro' ); ?></label></th>
		<td>
			<input type="text" name="stamps_customs_comments" maxlength="76" placeholder="<?php _e( 'optional', 'ultimatewoo-pro' ); ?>" />
		</td>
	</tr>
	<tr>
		<th><label><?php _e( 'License Number', 'ultimatewoo-pro' ); ?></label></th>
		<td>
			<input type="text" name="stamps_customs_licence" maxlength="6" placeholder="<?php _e( 'optional', 'ultimatewoo-pro' ); ?>" />
		</td>
	</tr>
	<tr>
		<th><label><?php _e( 'Certificate Number', 'ultimatewoo-pro' ); ?></label></th>
		<td>
			<input type="text" name="stamps_customs_certificate" maxlength="8" placeholder="<?php _e( 'optional', 'ultimatewoo-pro' ); ?>" />
		</td>
	</tr>
</table>

<h4><?php _e( 'Customs line items', 'ultimatewoo-pro' ); ?> <a class="wc-stamps-customs-add-line" href="#" data-line_html="<?php echo esc_attr( $line_html ); ?>">(<?php _e( 'Add line', 'ultimatewoo-pro' ); ?>)</a></h4>
<p class="wc-stamps-customs-line-intro"><?php _e( 'Add line items for the customs form here.', 'ultimatewoo-pro' ); ?></p>
<div class="wc-stamps-customs-items">
	<?php
		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $order->get_product_from_item( $item );

			if ( ! $product->needs_shipping() ) {
				continue;
			}

			$description = $product->get_title();
			$qty         = $item['qty'];
			$value       = $product->get_price() * $item['qty'];
			$weight      = wc_get_weight( $product->get_weight() * $item['qty'], 'lbs' );

			include( 'html-customs-item.php' );
		}
	?>
</div>
<p>
	<input type="hidden" name="parsed_rate" value="<?php echo esc_attr( json_encode( $stamps_rate ) ); ?>" />
	<button type="submit" class="button button-primary stamps-action" data-stamps_action="request_label"><?php _e( 'Request label', 'ultimatewoo-pro' ); ?></button>
	<button type="submit" class="button stamps-action" data-stamps_action="define_package"><?php _e( 'Cancel', 'ultimatewoo-pro' ); ?></button>
</p>