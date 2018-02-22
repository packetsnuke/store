<form action="<?php echo admin_url( 'admin.php?import=' . $this->import_page . '&step=2&merge=' . $merge ); ?>" method="post">
	<?php wp_nonce_field( 'import-woocommerce' ); ?>
	<input type="hidden" name="import_id" value="<?php echo $this->id; ?>" />
	<?php if ( $this->file_url_import_enabled ) : ?>
	<input type="hidden" name="import_url" value="<?php echo $this->file_url; ?>" />
	<?php endif; ?>

	<h3><?php _e( 'Map Fields', 'ultimatewoo-pro' ); ?></h3>
	<p><?php _e( 'Here you can map your imported columns to product data fields.', 'ultimatewoo-pro' ); ?></p>

	<table class="widefat widefat_importer">
		<thead>
			<tr>
				<th><?php _e( 'Map to', 'ultimatewoo-pro' ); ?></th>
				<th><?php _e( 'Column Header', 'ultimatewoo-pro' ); ?></th>
				<th><?php _e( 'Example Column Value', 'ultimatewoo-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $row as $key => $value ) : ?>
			<tr>
				<td width="25%">
					<?php
						if ( strstr( $key, 'tax:' ) ) {

							$column = trim( str_replace( 'tax:', '', $key ) );
							printf(__('Taxonomy: <strong>%s</strong>', 'ultimatewoo-pro'), $column);

						} elseif ( strstr( $key, 'meta:' ) ) {

							$column = trim( str_replace( 'meta:', '', $key ) );
							printf(__('Custom Field: <strong>%s</strong>', 'ultimatewoo-pro'), $column);

						} elseif ( strstr( $key, 'attribute:' ) ) {

							$column = trim( str_replace( 'attribute:', '', $key ) );
							printf(__('Product Attribute: <strong>%s</strong>', 'ultimatewoo-pro'), sanitize_title( $column ) );

						} elseif ( strstr( $key, 'attribute_data:' ) ) {

							$column = trim( str_replace( 'attribute_data:', '', $key ) );
							printf(__('Product Attribute Data: <strong>%s</strong>', 'ultimatewoo-pro'), sanitize_title( $column ) );

						} elseif ( strstr( $key, 'attribute_default:' ) ) {

							$column = trim( str_replace( 'attribute_default:', '', $key ) );
							printf(__('Product Attribute default value: <strong>%s</strong>', 'ultimatewoo-pro'), sanitize_title( $column ) );

						} else {
							?>
							<select name="map_to[<?php echo $key; ?>]">
								<option value=""><?php _e( 'Do not import', 'ultimatewoo-pro' ); ?></option>
								<option value="import_as_images" <?php selected( $key, 'images' ); ?>><?php _e( 'Images/Gallery', 'ultimatewoo-pro' ); ?></option>
								<option value="import_as_meta"><?php _e( 'Custom Field with column name', 'ultimatewoo-pro' ); ?></option>
								<optgroup label="<?php _e( 'Taxonomies', 'ultimatewoo-pro' ); ?>">
									<?php
										foreach ($taxonomies as $taxonomy ) {
											if ( substr( $taxonomy, 0, 3 ) == 'pa_' ) continue;
											echo '<option value="tax:' . $taxonomy . '" ' . selected( $key, 'tax:' . $taxonomy, true ) . '>' . $taxonomy . '</option>';
										}
									?>
								</optgroup>
								<optgroup label="<?php _e( 'Attributes', 'ultimatewoo-pro' ); ?>">
									<?php
										foreach ($taxonomies as $taxonomy ) {
											if ( substr( $taxonomy, 0, 3 ) == 'pa_' )
												echo '<option value="attribute:' . $taxonomy . '" ' . selected( $key, 'attribute:' . $taxonomy, true ) . '>' . $taxonomy . '</option>';
										}
									?>
								</optgroup>
								<optgroup label="<?php _e( 'Map to parent (variations and grouped products)', 'ultimatewoo-pro' ); ?>">
									<option value="post_parent" <?php selected( $key, 'post_parent' ); ?>><?php _e( 'By ID', 'ultimatewoo-pro' ); ?>: post_parent</option>
									<option value="parent_sku" <?php selected( $key, 'parent_sku' ); ?>><?php _e( 'By SKU', 'ultimatewoo-pro' ); ?>: parent_sku</option>
								</optgroup>
								<optgroup label="<?php _e( 'Post data', 'ultimatewoo-pro' ); ?>">
									<option <?php selected( $key, 'post_id' ); selected( $key, 'id' ); ?>>post_id</option>
									<option <?php selected( $key, 'post_type' ); ?>>post_type</option>
									<option <?php selected( $key, 'menu_order' ); ?>>menu_order</option>
									<option <?php selected( $key, 'post_status' ); ?>>post_status</option>
									<option <?php selected( $key, 'post_title' ); ?>>post_title</option>
									<option <?php selected( $key, 'post_name' ); ?>>post_name</option>
									<option <?php selected( $key, 'post_date' ); ?>>post_date</option>
									<option <?php selected( $key, 'post_date_gmt' ); ?>>post_date_gmt</option>
									<option <?php selected( $key, 'post_content' ); ?>>post_content</option>
									<option <?php selected( $key, 'post_excerpt' ); ?>>post_excerpt</option>
									<option <?php selected( $key, 'post_author' ); ?>>post_author</option>
									<option <?php selected( $key, 'post_password' ); ?>>post_password</option>
									<option <?php selected( $key, 'comment_status' ); ?>>comment_status</option>
									<option <?php selected( $key, 'variation_description' ); ?>>variation_description</option>
								</optgroup>
								<optgroup label="<?php _e( 'Product data', 'ultimatewoo-pro' ); ?>">
									<option value="tax:product_type" <?php selected( $key, 'tax:product_type' ); ?>><?php _e( 'Type', 'ultimatewoo-pro' ); ?>: product_type</option>
									<option value="downloadable" <?php selected( $key, 'downloadable' ); ?>><?php _e( 'Type', 'ultimatewoo-pro' ); ?>: downloadable</option>
									<option value="virtual" <?php selected( $key, 'virtual' ); ?>><?php _e( 'Type', 'ultimatewoo-pro' ); ?>: virtual</option>
									<option value="sku" <?php selected( $key, 'sku' ); ?>><?php _e( 'SKU', 'ultimatewoo-pro' ); ?>: sku</option>
									<option value="visibility" <?php selected( $key, 'visibility' ); ?>><?php _e( 'Visibility', 'ultimatewoo-pro' ); ?>: visibility</option>
									<option value="featured" <?php selected( $key, 'featured' ); ?>><?php _e( 'Visibility', 'ultimatewoo-pro' ); ?>: featured</option>
									<option value="stock" <?php selected( $key, 'stock' ); ?>><?php _e( 'Inventory', 'ultimatewoo-pro' ); ?>: stock</option>
									<option value="stock_status" <?php selected( $key, 'stock_status' ); ?>><?php _e( 'Inventory', 'ultimatewoo-pro' ); ?>: stock_status</option>
									<option value="backorders" <?php selected( $key, 'backorders' ); ?>><?php _e( 'Inventory', 'ultimatewoo-pro' ); ?>: backorders</option>
									<option value="manage_stock" <?php selected( $key, 'manage_stock' ); ?>><?php _e( 'Inventory', 'ultimatewoo-pro' ); ?>: manage_stock</option>
									<option value="regular_price" <?php selected( $key, 'regular_price' ); ?>><?php _e( 'Price', 'ultimatewoo-pro' ); ?>: regular_price</option>
									<option value="sale_price" <?php selected( $key, 'sale_price' ); ?>><?php _e( 'Price', 'ultimatewoo-pro' ); ?>: sale_price</option>
									<option value="sale_price_dates_from" <?php selected( $key, 'sale_price_dates_from' ); ?>><?php _e( 'Price', 'ultimatewoo-pro' ); ?>: sale_price_dates_from</option>
									<option value="sale_price_dates_to" <?php selected( $key, 'sale_price_dates_to' ); ?>><?php _e( 'Price', 'ultimatewoo-pro' ); ?>: sale_price_dates_to</option>
									<option value="weight" <?php selected( $key, 'weight' ); ?>><?php _e( 'Dimensions', 'ultimatewoo-pro' ); ?>: weight</option>
									<option value="length" <?php selected( $key, 'length' ); ?>><?php _e( 'Dimensions', 'ultimatewoo-pro' ); ?>: length</option>
									<option value="width" <?php selected( $key, 'width' ); ?>><?php _e( 'Dimensions', 'ultimatewoo-pro' ); ?>: width</option>
									<option value="height" <?php selected( $key, 'height' ); ?>><?php _e( 'Dimensions', 'ultimatewoo-pro' ); ?>: height</option>
									<option value="tax_status" <?php selected( $key, 'tax_status' ); ?>><?php _e( 'Tax', 'ultimatewoo-pro' ); ?>: tax_status</option>
									<option value="tax_class" <?php selected( $key, 'tax_class' ); ?>><?php _e( 'Tax', 'ultimatewoo-pro' ); ?>: tax_class</option>
									<option value="upsell_ids" <?php selected( $key, 'upsell_ids' ); ?>><?php _e( 'Related Products', 'ultimatewoo-pro' ); ?>: upsell_ids</option>
									<option value="crosssell_ids" <?php selected( $key, 'crosssell_ids' ); ?>><?php _e( 'Related Products', 'ultimatewoo-pro' ); ?>: crosssell_ids</option>
									<option value="upsell_skus" <?php selected( $key, 'upsell_skus' ); ?>><?php _e( 'Related Products', 'ultimatewoo-pro' ); ?>: upsell_skus</option>
									<option value="crosssell_skus" <?php selected( $key, 'crosssell_skus' ); ?>><?php _e( 'Related Products', 'ultimatewoo-pro' ); ?>: crosssell_skus</option>
									<option value="file_paths" <?php selected( $key, 'file_paths' ); ?>><?php _e( 'Downloads', 'ultimatewoo-pro' ); ?>: file_paths <?php _e( '(WC 2.0.x)', 'ultimatewoo-pro' ); ?></option>
									<option value="downloadable_files" <?php selected( $key, 'downloadable_files' ); ?>><?php _e( 'Downloads', 'ultimatewoo-pro' ); ?>: downloadable_files <?php _e( '(WC 2.1+)', 'ultimatewoo-pro' ); ?></option>
									<option value="download_limit" <?php selected( $key, 'download_limit' ); ?>><?php _e( 'Downloads', 'ultimatewoo-pro' ); ?>: download_limit</option>
									<option value="download_expiry" <?php selected( $key, 'download_expiry' ); ?>><?php _e( 'Downloads', 'ultimatewoo-pro' ); ?>: download_expiry</option>
									<option value="product_url" <?php selected( $key, 'product_url' ); ?>><?php _e( 'External', 'ultimatewoo-pro' ); ?>: product_url</option>
									<option value="button_text" <?php selected( $key, 'button_text' ); ?>><?php _e( 'External', 'ultimatewoo-pro' ); ?>: button_text</option>
									<?php do_action( 'woocommerce_csv_product_data_mapping', $key ); ?>
								</optgroup>
								<?php if( function_exists( 'woocommerce_gpf_install' ) ) : ?>
								<optgroup label="<?php _e( 'Google Product Feed', 'ultimatewoo-pro' ); ?>">
									<option value="gpf:exclude_product" <?php selected( $key, 'gpf:exclude_product' ); ?>><?php _e( 'Exclude Product', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:availability" <?php selected( $key, 'gpf:availability' ); ?>><?php _e( 'Availability', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:condition" <?php selected( $key, 'gpf:condition' ); ?>><?php _e( 'Condition', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:brand" <?php selected( $key, 'gpf:brand' ); ?>><?php _e( 'Brand', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:product_type" <?php selected( $key, 'gpf:product_type' ); ?>><?php _e( 'Product Type', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:google_product_category" <?php selected( $key, 'gpf:google_product_category' ); ?>><?php _e('Google Product Category', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:gtin" <?php selected( $key, 'gpf:gtin' ); ?>><?php _e( 'Global Trade Item Number (GTIN)', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:mpn" <?php selected( $key, 'gpf:mpn' ); ?>><?php _e( 'Manufacturer Part Number (MPN)', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:gender" <?php selected( $key, 'gpf:gender' ); ?>><?php _e( 'Gender', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:age_group" <?php selected( $key, 'gpf:age_group' ); ?>><?php _e( 'Age Group', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:color" <?php selected( $key, 'gpf:color' ); ?>><?php _e( 'Color', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:size" <?php selected( $key, 'gpf:size' ); ?>><?php _e( 'Size', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:size_type" <?php selected( $key, 'gpf:size_type' ); ?>><?php _e( 'Size type', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:size_system" <?php selected( $key, 'gpf:size_system' ); ?>><?php _e( 'Size system', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:material" <?php selected( $key, 'gpf:material' ); ?>><?php _e( 'Material', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:pattern" <?php selected( $key, 'gpf:pattern' ); ?>><?php _e( 'Pattern', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:delivery_label" <?php selected( $key, 'gpf:delivery_label' ); ?>><?php _e( 'Delivery label', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:adwords_grouping" <?php selected( $key, 'gpf:adwords_grouping' ); ?>><?php _e( 'adwords_grouping', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:adwords_labels" <?php selected( $key, 'gpf:adwords_labels' ); ?>><?php _e( 'adwords_labels', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:custom_label_0" <?php selected( $key, 'gpf:custom_label_0' ); ?>><?php _e( 'custom_label_0', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:custom_label_1" <?php selected( $key, 'gpf:custom_label_1' ); ?>><?php _e( 'custom_label_1', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:custom_label_2" <?php selected( $key, 'gpf:custom_label_2' ); ?>><?php _e( 'custom_label_2', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:custom_label_3" <?php selected( $key, 'gpf:custom_label_3' ); ?>><?php _e( 'custom_label_3', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:custom_label_4" <?php selected( $key, 'gpf:custom_label_4' ); ?>><?php _e( 'custom_label_4', 'ultimatewoo-pro' ); ?></option>
									<option value="gpf:promotion_id" <?php selected( $key, 'gpf:promotion_id' ); ?>><?php _e( 'Promotion ID', 'ultimatewoo-pro' ); ?></option>
								</optgroup>
								<?php endif; ?>
							</select>
							<?php
						}
					?>
				</td>
				<td width="25%"><?php echo $raw_headers[$key]; ?></td>
				<td><code><?php if ( $value != '' ) echo esc_html( $value ); else echo '-'; ?></code></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="submit">
		<input type="submit" class="button" value="<?php esc_attr_e( 'Submit', 'ultimatewoo-pro' ); ?>" />
		<input type="hidden" name="delimiter" value="<?php echo $this->delimiter ?>" />
		<input type="hidden" name="merge_empty_cells" value="<?php echo $this->merge_empty_cells ?>" />
	</p>
</form>
