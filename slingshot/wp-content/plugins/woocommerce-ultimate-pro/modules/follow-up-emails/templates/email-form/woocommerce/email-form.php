<div class="options_group wc-products-selector">
	<div class="non-signup reminder hideable <?php do_action('fue_form_product_tr_class', $email); ?> product_tr">
		<p class="form-field">
			<label for="product_ids"><?php _e('Product', 'ultimatewoo-pro'); ?></label>
			<?php
			$product_id     = (!empty($email->product_id)) ? $email->product_id : '';
			$product_name   = '';

			if ( !empty( $product_id ) ) {
				$product = WC_FUE_Compatibility::wc_get_product( $product_id );

				if ( $product ) {
					$product_name   = wp_kses_post( $product->get_formatted_name() );
				}
			}
			?>
			<input
				type="hidden"
				id="product_id"
				name="product_id"
				class="ajax_select2_products_and_variations"
				data-multiple="false"
				data-placeholder="<?php _e('Search for a product&hellip;', 'woocommerce'); ?>"
				value="<?php echo $product_id; ?>"
				data-selected="<?php echo esc_attr( $product_name ); ?>"
				data-allow_clear="true"
				>
		</p>

		<?php
		$display = 'display: none;';

		if ($has_variations)
			$display = 'display: inline-block;';
		?>
		<p class="form-field product_include_variations" style="<?php echo $display; ?>">
			<input type="checkbox" name="meta[include_variations]" id="include_variations" value="yes" <?php if (isset($email->meta['include_variations']) && $email->meta['include_variations'] == 'yes') echo 'checked'; ?> />
			<label for="include_variations" class="inline"><?php _e('Include variations', 'ultimatewoo-pro'); ?></label>
		</p>
	</div>

	<div class="non-signup reminder hideable <?php do_action('fue_form_category_tr_class', $email); ?> category_tr">
		<p class="form-field">
			<label for="category_id"><?php _e('Category', 'ultimatewoo-pro'); ?></label>

			<select id="category_id" name="category_id" class="select2" data-placeholder="<?php _e('Search for a category&hellip;', 'ultimatewoo-pro'); ?>">
				<option value="0"><?php _e('Any Category', 'ultimatewoo-pro'); ?></option>
				<?php
				foreach ($categories as $category):
					?>
					<option value="<?php _e($category->term_id); ?>" <?php selected( $email->category_id, $category->term_id ); ?>><?php echo esc_html($category->name); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
</div>
