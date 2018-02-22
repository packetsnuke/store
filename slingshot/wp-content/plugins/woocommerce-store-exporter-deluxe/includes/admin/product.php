<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_product( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options product-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_product', $post_ID ); ?>

</div>
<!-- .product-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	function woo_ce_scheduled_export_products_filter_by_product_category( $post_ID = 0 ) {

		$args = array(
			'hide_empty' => 1
		);
		$product_categories = false;
		if( apply_filters( 'woo_ce_override_products_filter_by_product_category', true ) )
			$product_categories = woo_ce_get_product_categories( $args );
		$types = get_post_meta( $post_ID, '_filter_product_category', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_category"><?php _e( 'Product category', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $product_categories ) ) { ?>
	<select id="product_filter_category" data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="product_filter_category[]" multiple class="chzn-select select short" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
		<option value="<?php echo $product_category->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_category->term_id, $types ) : false ), true ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Categories you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_product_tag( $post_ID = 0 ) {

		$args = array(
			'hide_empty' => 1
		);
		$product_tags = false;
		if( apply_filters( 'woo_ce_override_products_filter_by_product_tag', true ) )
			$product_tags = woo_ce_get_product_tags( $args );
		$types = get_post_meta( $post_ID, '_filter_product_tag', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_tag"><?php _e( 'Product tag', 'woocommerce-exporter' ); ?></label>
	<?php if( !empty( $product_tags ) ) { ?>
	<select id="product_filter_tag" data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="product_filter_tag[]" multiple class="chzn-select select short" style="width:95%;">
		<?php foreach( $product_tags as $product_tag ) { ?>
		<option value="<?php echo $product_tag->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_tag->term_id, $types ) : false ), true ); ?><?php disabled( $product_tag->count, 0 ); ?>><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
		<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Tag\'s you want to filter exported Products by. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	<?php } else { ?>
	<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_product_status( $post_ID = 0 ) {

		$product_stati = get_post_statuses();
		// Add Trash if it not included in the list
		if( !isset( $product_stati['trash'] ) )
			$product_stati['trash'] = __( 'Trash', 'woocommerce-exporter' );
		// Allow Plugin/Theme authors to add support for custom Product Post Stati
		$product_stati = apply_filters( 'woo_ce_products_filter_post_stati', $product_stati );

		$types = get_post_meta( $post_ID, '_filter_product_status', true );

		ob_start(); ?>
		<p class="form-field discount_type_field">
			<label for="product_filter_status"><?php _e( 'Product status', 'woocommerce-exporter' ); ?></label>
	<?php if( !empty( $product_stati ) ) { ?>
			<select id="product_filter_status" data-placeholder="<?php _e( 'Choose a Product Status...', 'woocommerce-exporter' ); ?>" name="product_filter_status[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $product_stati as $key => $product_status ) { ?>
				<option value="<?php echo $key; ?>"<?php selected( ( !empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo $product_status; ?></option>
		<?php } ?>
			</select>
			<img class="help_tip" data-tip="<?php _e( 'Select the Product Status\'s you want to filter exported Products by. Default is to include all Product Status\'s.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	<?php } else { ?>
			<?php _e( 'No Product Status were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
		</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_product_type( $post_ID = 0 ) {

		$product_types = woo_ce_get_product_types();
		$types = get_post_meta( $post_ID, '_filter_product_type', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_type"><?php _e( 'Product type', 'woocommerce-exporter' ); ?></label>
	<?php if( !empty( $product_types ) ) { ?>
	<select id="product_filter_type" data-placeholder="<?php _e( 'Choose a Product Type...', 'woocommerce-exporter' ); ?>" name="product_filter_type[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $product_types as $key => $product_type ) { ?>
		<option value="<?php echo $key; ?>"<?php selected( ( !empty( $types ) ? in_array( $key, $types ) : false ), true ); ?>><?php echo woo_ce_format_product_type( $product_type['name'] ); ?> (<?php echo $product_type['count']; ?>)</option>
		<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Type\'s you want to filter exported Products by. Default is to include all Product Types except Variations.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	<?php } else { ?>
	<?php _e( 'No Product Types were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_product( $post_ID = 0 ) {

		$products = false;
		if( apply_filters( 'woo_ce_override_products_filter_by_sku', true ) ) {
			$post_status = array( 'publish', 'pending', 'future', 'private' );
			$args = array(
				'product_status' => $post_status
			);
			$products = woo_ce_get_products( $args );
		}
		$types = get_post_meta( $post_ID, '_filter_product_sku', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_sku"><?php _e( 'Product', 'woocommerce-exporter' ); ?></label>
	<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) && apply_filters( 'woo_ce_override_products_filter_by_sku', true ) ) { ?>
	<?php
		$output = '';
		$json_ids = array();
		if( !empty( $types ) ) {
			foreach( $types as $product_id ) {
				$product = wc_get_product( $product_id );
				if( is_object( $product ) ) {
					$json_ids[$product_id] = wp_kses_post( $product->get_formatted_name() );
					$output .= '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
				}
			}
		}
	?>
		<?php if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
	<select 
		data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" 
		id="product_filter_sku" 
		name="product_filter_sku[]" 
		multiple="multiple" 
		class="multiselect wc-product-search" 
		style="width:95%;" 
		data-action="woocommerce_json_search_products_and_variations" 
		data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
	><?php echo $output; ?></select>
		<?php } else { ?>
	<input
		data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" 
		type="hidden" 
		id="product_filter_sku" 
		name="product_filter_sku[]" 
		class="multiselect wc-product-search" 
		data-multiple="true" 
		style="width:95%;" 
		data-action="woocommerce_json_search_products_and_variations" 
		data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>" 
		value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
	 />
		<?php } ?>
	<?php } else { ?>
	<?php
		add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
	?>
		<?php if( !empty( $products ) ) { ?>
	<select id="product_filter_sku" data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="product_filter_sku[]" multiple class="chzn-select" style="width:95%;">
			<?php foreach( $products as $product ) { ?>
		<option value="<?php echo $product; ?>"<?php selected( ( !empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?></option>
			<?php } ?>
	</select>
		<?php } else { ?>
	<?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?>
		<?php } ?>
	<?php
		remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );
	?>
		<?php } ?>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product\'s you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_shipping_class( $post_ID = 0 ) {

		$shipping_classes = woo_ce_get_shipping_classes();
		$types = get_post_meta( $post_ID, '_filter_product_shipping_class', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_shipping_class"><?php _e( 'Shipping class', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $shipping_classes ) ) { ?>
	<select id="product_filter_shipping_class" data-placeholder="<?php _e( 'Choose a Shipping Class...', 'woocommerce-exporter' ); ?>" name="product_filter_shipping_class[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_classes as $shipping_class ) { ?>
		<option value="<?php echo $shipping_class->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $shipping_class->term_id, $types ) : false ), true ); ?><?php disabled( $shipping_class->count, 0 ); ?>><?php echo $shipping_class->name; ?> (<?php echo $shipping_class->count; ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Shipping Class\'s you want to filter exported Products by. Default is to include all Shipping Classes.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_date_modified( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_product_date', true );
		$product_filter_dates_from = get_post_meta( $post_ID, '_filter_product_dates_from', true );
		$product_filter_dates_to = get_post_meta( $post_ID, '_filter_product_dates_to', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_date"><?php _e( 'Date modified', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="product_dates_filter" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_dates_filter" value="today"<?php checked( $types, 'today' ); ?> />&nbsp;<?php _e( 'Today', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php _e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> />&nbsp;<?php _e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
	<input type="text" name="product_dates_from" value="<?php echo $product_filter_dates_from; ?>" size="10" maxlength="10" class="sized datepicker product_export" /> <span style="float:left; margin-right:6px;"><?php _e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="product_dates_to" value="<?php echo $product_filter_dates_to; ?>" size="10" maxlength="10" class="sized datepicker product_export" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_stock_status( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_product_stock', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_stock"><?php _e( 'Stock status', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="product_filter_stock" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'Include both', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_filter_stock" value="instock"<?php checked( $types, 'instock' ); ?> />&nbsp;<?php _e( 'In stock', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_filter_stock" value="outofstock"<?php checked( $types, 'outofstock' ); ?> />&nbsp;<?php _e( 'Out of stock', 'woocommerce-exporter' ); ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_products_filter_by_featured( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_product_featured', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_featured"><?php _e( 'Featured', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="product_filter_featured" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'Include both', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_filter_featured" value="yes"<?php checked( $types, 'yes' ); ?> />&nbsp;<?php _e( 'Featured', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="product_filter_featured" value="no"<?php checked( $types, 'no' ); ?> />&nbsp;<?php _e( 'Not featured', 'woocommerce-exporter' ); ?>
</p>
<?php
		ob_end_flush();

	}

	// HTML template for Product Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_products_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_product_orderby', true );
		// Default to Title
		if( $orderby == false ) {
			$orderby = 'title';
		}

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="product_filter_orderby"><?php _e( 'Product Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="product_filter_orderby" name="product_filter_orderby">
			<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Product ID', 'woocommerce-exporter' ); ?></option>
			<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Product Name', 'woocommerce-exporter' ); ?></option>
			<option value="sku"<?php selected( 'sku', $orderby ); ?>><?php _e( 'Product SKU', 'woocommerce-exporter' ); ?></option>
			<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
			<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
			<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
			<option value="menu_order"<?php selected( 'menu_order', $orderby ); ?>><?php _e( 'Sort Order', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Products by Product Category widget on Store Exporter screen
	function woo_ce_products_filter_by_product_category() {

		if( apply_filters( 'woo_ce_override_products_filter_by_product_category', true ) == false )
			return;

		$args = array(
			'hide_empty' => 1
		);

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_products_filter_by_product_category_args', $args );

		$product_categories = woo_ce_get_product_categories( $args );
		$types = woo_ce_get_option( 'product_category', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-categories"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Category', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-categories" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_categories ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="product_filter_category[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
				<option value="<?php echo $product_category->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_category->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Categories you want to filter exported Products by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-categories -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Tag widget on Store Exporter screen
	function woo_ce_products_filter_by_product_tag() {

		if( apply_filters( 'woo_ce_override_products_filter_by_product_tag', true ) == false )
			return;

		$args = array(
			'hide_empty' => 1
		);

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_products_filter_by_product_tag_args', $args );

		$product_tags = woo_ce_get_product_tags( $args );
		$types = woo_ce_get_option( 'product_tag', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-tags"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Tag', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-tags" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_tags ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="product_filter_tag[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_tags as $product_tag ) { ?>
				<option value="<?php echo $product_tag->term_id; ?>"<?php echo $product_tag->term_id; ?><?php disabled( $product_tag->count, 0 ); ?>><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Tags you want to filter exported Products by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-tags -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Status widget on Store Exporter screen
	function woo_ce_products_filter_by_product_status() {

		$product_stati = get_post_statuses();
		if( !isset( $product_stati['trash'] ) )
			$product_stati['trash'] = __( 'Trash', 'woocommerce-exporter' );

/*
		$post_type = 'product';
		$args = array(
			'post_type' => $post_type
		);
		$product_stati = get_post_stati( $args );
*/

		// Allow Plugin/Theme authors to add support for custom Product Post Stati
		$product_stati = apply_filters( 'woo_ce_products_filter_post_stati', $product_stati );

		$types = woo_ce_get_option( 'product_status', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-status"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-status" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_stati ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Status...', 'woocommerce-exporter' ); ?>" name="product_filter_status[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_stati as $key => $product_status ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo $product_status; ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Status were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Status options you want to filter exported Products by. Default is to include all Product Status options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Type widget on Store Exporter screen
	function woo_ce_products_filter_by_product_type() {

		$product_types = woo_ce_get_product_types();
		$types = woo_ce_get_option( 'product_type', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-type"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-type" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_types ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Type...', 'woocommerce-exporter' ); ?>" name="product_filter_type[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_types as $key => $product_type ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?><?php disabled( $product_type['count'], 0 ); ?>><?php echo woo_ce_format_product_type( $product_type['name'] ); ?> (<?php echo $product_type['count']; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Type\'s you want to filter exported Products by. Default is to include all Product Types except Variations.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-type -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product widget on Store Exporter screen
	function woo_ce_products_filter_by_sku() {

		if( apply_filters( 'woo_ce_override_products_filter_by_sku', true ) == false )
			return;

		$post_status = array( 'publish', 'pending', 'future', 'private' );
		$args = array(
			'product_status' => $post_status
		);

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_products_filter_by_sku_args', $args );

		$products = woo_ce_get_products( $args );
		add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-sku" /> <?php _e( 'Filter Products by Product', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-sku" class="separator">
	<ul>
		<li>
<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
			<p>
	<?php if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
				<select 
					data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" 
					id="product_filter_sku" 
					name="product_filter_sku[]" 
					multiple="multiple" 
					class="multiselect wc-product-search" 
					style="width:95%;" 
					data-action="woocommerce_json_search_products_and_variations"
				></select>
	<?php } else { ?>
				<input 
					data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" 
					type="hidden" 
					id="product_filter_sku" 
					name="product_filter_sku[]" 
					class="multiselect wc-product-search" 
					data-multiple="true" 
					style="width:95;" 
					data-action="woocommerce_json_search_products_and_variations"
				 />
	<?php } ?>
			</p>
<?php } else { ?>
	<?php if( !empty( $products ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="product_filter_sku[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $products as $product ) { ?>
				<option value="<?php echo $product; ?>"><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?></option>
		<?php } ?>
			</select>
	<?php } else { ?>
			<?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product\'s you want to filter exported Products by. Multiple Products can be selected. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-sku -->
<?php
		ob_end_flush();
		remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );

	}

	function woo_ce_scheduled_export_product_filter_by_user_role( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_product_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_product() - before get_user_roles(): ' . ( time() - $start_time ) ) );

		$user_roles = woo_ce_get_user_roles();
		$types = get_post_meta( $post_ID, '_filter_product_user_role', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_product() - before rendering $user_roles: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="product_filter_user_role"><?php _e( 'User role', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $user_roles ) ) { ?>
	<select id="product_filter_user_role" data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="product_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
		<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
	</select>
<?php } else { ?>
	<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by User Role widget on Store Exporter screen
	function woo_ce_products_filter_by_user_role() {

		$user_roles = woo_ce_get_user_roles();
		$types = woo_ce_get_option( 'product_user_role', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-user_role"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by User Role', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-user_role" class="separator">
	<ul>
		<li>
<?php if( !empty( $user_roles ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="product_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Products by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Stock Status widget on Store Exporter screen
	function woo_ce_products_filter_by_stock_status() {

		$types = woo_ce_get_option( 'product_stock', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-stock"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Stock Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-stock" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_stock" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="instock"><label><input type="radio" name="product_filter_stock" value="instock"<?php checked( $types, 'instock' ); ?> /><?php _e( 'In stock', 'woocommerce-exporter' ); ?></label></li>
		<li value="outofstock"><label><input type="radio" name="product_filter_stock" value="outofstock"<?php checked( $types, 'outofstock' ); ?> /><?php _e( 'Out of stock', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Stock Status\'s you want to filter exported Products by. Default is to include all Stock Status\'s.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-stock -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Featured widget on Store Exporter screen
	function woo_ce_products_filter_by_featured() {

		$types = woo_ce_get_option( 'product_featured', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-featured"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Featured', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-featured" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_featured" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="yes"><label><input type="radio" name="product_filter_featured" value="yes"<?php checked( $types, 'yes' ); ?> /><?php _e( 'Featured', 'woocommerce-exporter' ); ?></label></li>
		<li value="no"><label><input type="radio" name="product_filter_featured" value="no"<?php checked( $types, 'no' ); ?> /><?php _e( 'Not featured', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Featured state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-featured -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Featured Image widget on Store Exporter screen
	function woo_ce_products_filter_by_featured_image() {

		$types = woo_ce_get_option( 'product_featured_image', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-featured-image"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Featured Image', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-featured-image" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_featured_image" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="yes"><label><input type="radio" name="product_filter_featured_image" value="yes"<?php checked( $types, 'yes' ); ?> /><?php _e( 'With Featured Image', 'woocommerce-exporter' ); ?></label></li>
		<li value="no"><label><input type="radio" name="product_filter_featured_image" value="no"<?php checked( $types, 'no' ); ?> /><?php _e( 'Without Featured Image', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Featured Image state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-featured-image -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Product Gallery widget on Store Exporter screen
	function woo_ce_products_filter_by_product_gallery() {

		$types = woo_ce_get_option( 'product_gallery', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-gallery"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Product Gallery', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-gallery" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="product_filter_product_gallery" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="yes"><label><input type="radio" name="product_filter_product_gallery" value="yes"<?php checked( $types, 'yes' ); ?> /><?php _e( 'With Product Gallery images', 'woocommerce-exporter' ); ?></label></li>
		<li value="no"><label><input type="radio" name="product_filter_product_gallery" value="no"<?php checked( $types, 'no' ); ?> /><?php _e( 'Without Product Gallery images', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Gallery state you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-gallery -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Shipping Classes widget on Store Exporter screen
	function woo_ce_products_filter_by_shipping_class() {

		if( apply_filters( 'woo_ce_override_products_filter_by_shipping_class', true ) == false )
			return;

		$shipping_classes = woo_ce_get_shipping_classes();

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-shipping_class" /> <?php _e( 'Filter Products by Shipping Class', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-shipping_class" class="separator">
	<ul>
		<li>
<?php if( !empty( $shipping_classes ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Class...', 'woocommerce-exporter' ); ?>" name="product_filter_shipping_class[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_classes as $shipping_class ) { ?>
				<option value="<?php echo $shipping_class->term_id; ?>"<?php disabled( $shipping_class->count, 0 ); ?>><?php echo $shipping_class->name; ?> (<?php echo $shipping_class->count; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Shipping Class you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-shipping_class -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Products by Date Modified widget on Store Exporter screen
	function woo_ce_products_filter_by_date_modified() {

		$today = date( 'l', current_time( 'timestamp' ) );
		$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
		$date_format = 'd/m/Y';
		$types = woo_ce_get_option( 'product_dates_filter' );
		$product_dates_from = woo_ce_get_option( 'product_dates_from' );
		$product_dates_to = woo_ce_get_option( 'product_dates_to' );
		// Check if the Product Modified Date To/From have been saved
		if( empty( $product_dates_from ) || empty( $product_dates_to ) ) {
			if( empty( $product_dates_from ) )
				$product_dates_from = woo_ce_get_product_first_date();
			if( empty( $product_dates_to ) )
				$product_dates_to = date( $date_format );
		}

		ob_start(); ?>
<p><label><input type="checkbox" id="products-filters-date_modified"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Products by Date Modified', 'woocommerce-exporter' ); ?></label></p>
<div id="export-products-filters-date_modified" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="product_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $product_dates_from; ?> - <?php echo $product_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="product_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="product_dates_from" name="product_dates_from" value="<?php echo esc_attr( $product_dates_from ); ?>" class="text code datepicker product_export" /> to <input type="text" size="10" maxlength="10" id="product_dates_to" name="product_dates_to" value="<?php echo esc_attr( $product_dates_to ); ?>" class="text code datepicker product_export" />
				<p class="description"><?php _e( 'Filter the dates of Products to be included in the export. Default is the date of the first Product Modified to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-products-filters-date_modified -->
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Custom Product Fields within Product Options on Store Exporter screen
	function woo_ce_products_custom_fields_link() {

		ob_start(); ?>
<div id="export-products-custom-fields-link">
	<p><a href="#export-products-custom-fields"><?php _e( 'Manage Custom Product Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-products-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for Product Sorting widget on Store Exporter screen
	function woo_ce_product_sorting() {

		$product_orderby = woo_ce_get_option( 'product_orderby', 'ID' );
		$product_order = woo_ce_get_option( 'product_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Product Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="product_orderby">
		<option value="ID"<?php selected( 'ID', $product_orderby ); ?>><?php _e( 'Product ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $product_orderby ); ?>><?php _e( 'Product Name', 'woocommerce-exporter' ); ?></option>
		<option value="sku"<?php selected( 'sku', $product_orderby ); ?>><?php _e( 'Product SKU', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $product_orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $product_orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $product_orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
		<option value="menu_order"<?php selected( 'menu_order', $product_orderby ); ?>><?php _e( 'Sort Order', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="product_order">
		<option value="ASC"<?php selected( 'ASC', $product_order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $product_order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Products within the exported file. By default this is set to export Products by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Up-sells formatting on Store Exporter screen
	function woo_ce_products_upsells_formatting() {

		$upsell_formatting = woo_ce_get_option( 'upsell_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Up-sells formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_upsell_formatting" value="0"<?php checked( $upsell_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Up-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_upsell_formatting" value="1"<?php checked( $upsell_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Up-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the up-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	// HTML template for Cross-sells formatting on Store Exporter screen
	function woo_ce_products_crosssells_formatting() {

		$crosssell_formatting = woo_ce_get_option( 'crosssell_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Cross-sells formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_crosssell_formatting" value="0"<?php checked( $crosssell_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Cross-Sells as Product ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_crosssell_formatting" value="1"<?php checked( $crosssell_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Cross-Sells as Product SKU', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the cross-sell formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	// HTML template for Variation formatting on Store Exporter screen
	function woo_ce_products_variation_formatting() {

		$variation_formatting = woo_ce_get_option( 'variation_formatting', 0 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Variation formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="variation_formatting" value="0"<?php checked( $variation_formatting, 0 ); ?> />&nbsp;<?php _e( 'Leave empty Variant details intact', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="variation_formatting" value="1"<?php checked( $variation_formatting, 1 ); ?> />&nbsp;<?php _e( 'Default Variant details to Parent Product', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the default formatting rule that is applied to Product Variations.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
		ob_end_flush();

	}

	function woo_ce_products_description_excerpt_formatting() {

		$description_excerpt_formatting = woo_ce_get_option( 'description_excerpt_formatting', 0 );

		ob_start(); ?>
<tr class="export-options product-options category-options tag-options order-options">
	<th><label for=""><?php _e( 'Description/Excerpt formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="description_excerpt_formatting" value="0"<?php checked( $description_excerpt_formatting, 0 ); ?> />&nbsp;<?php _e( 'Leave HTML tags from Description/Excerpt intact', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="description_excerpt_formatting" value="1"<?php checked( $description_excerpt_formatting, 1 ); ?> />&nbsp;<?php _e( 'Strip HTML tags from Description/Excerpt', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the HTML tag formatting rule that is applied to the Description/Excerpt within the Product, Category, Tag, Brand and Order export.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Custom Products widget on Store Exporter screen
	function woo_ce_products_custom_fields() {

		if( $custom_products = woo_ce_get_option( 'custom_products', '' ) )
			$custom_products = implode( "\n", $custom_products );
		if( $custom_attributes = woo_ce_get_option( 'custom_attributes', '' ) )
			$custom_attributes = implode( "\n", $custom_attributes );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-products-custom-fields" class="export-options product-options">
	<div id="poststuff">

		<div class="postbox" id="export-options product-options">
			<h3 class="hndle"><?php _e( 'Custom Product Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Product meta or custom Attributes in the Export Products table above fill the meta text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the Product Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom Product meta and Attributes consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'Product meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_products" rows="5" cols="70"><?php echo esc_textarea( $custom_products ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Product meta in your export file by adding each custom Product meta name to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Custom attribute', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_attributes" rows="5" cols="70"><?php echo esc_textarea( $custom_attributes ); ?></textarea>
							<p class="description"><?php _e( 'Include custom Attributes in your export file by adding each custom Attribute name - typically in lowercase and replacing spaces with dashes, e.g. Size becomes size or Sample Attribute becomes sample-attribute - to a new line above.<br />For example: <code>condition</code> (new line) <code>colour</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_products_custom_fields' ); ?>

				</table>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="hidden" name="action" value="update" />
</form>
<!-- #export-products-custom-fields -->
<?php
		ob_end_flush();

	}

	function woo_ce_export_options_featured_image_formatting() {

		$product_image_formatting = woo_ce_get_option( 'product_image_formatting', 1 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Product image formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_image_formatting" value="0"<?php checked( $product_image_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_image_formatting" value="1"<?php checked( $product_image_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image URL', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_image_formatting" value="2"<?php checked( $product_image_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image filepath', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	function woo_ce_export_options_product_gallery_formatting() {

		$gallery_formatting = woo_ce_get_option( 'gallery_formatting', 1 );
		$gallery_unique = woo_ce_get_option( 'gallery_unique', 0 );
		$max_size = woo_ce_get_option( 'max_product_gallery', 3 );

		ob_start(); ?>
<tr class="export-options product-options">
	<th><label for=""><?php _e( 'Product gallery formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="product_gallery_formatting" value="0"<?php checked( $gallery_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_formatting" value="1"<?php checked( $gallery_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_formatting" value="2"<?php checked( $gallery_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ); ?></label>
		<hr />
		<label><input type="radio" name="product_gallery_unique" value="0"<?php checked( $gallery_unique, 0 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as a single combined image cell', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="product_gallery_unique" value="1"<?php checked( $gallery_unique, 1 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as individual image cells', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr id="max_product_gallery_option" class="export-options product-options">
	<th><label for=""><?php _e( 'Max unique Product Gallery images', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<input type="text" id="max_product_gallery" name="max_product_gallery" size="3" class="text" value="<?php echo esc_attr( $max_size ); ?>" />
		<p class="description"><?php _e( 'Manage the number of Product Gallery colums displayed when the \'Export Product Gallery as individual image cells\' Product gallery formatting option is selected.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_product( $post_ID = 0 ) {

		$export_type = 'product';

		$fields = woo_ce_get_product_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Product fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="<?php echo $export_type; ?>-fields" class="ui-sortable">
				<tbody>
	<?php foreach( $fields as $field ) { ?>
					<tr id="<?php echo $export_type; ?>-<?php echo $field['reset']; ?>">
						<td>
							<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
								<input type="checkbox" name="<?php echo $export_type; ?>_fields[<?php echo $field['name']; ?>]" class="<?php echo $export_type; ?>_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?>
							</label>
							<input type="text" name="<?php echo $export_type; ?>_fields_label[<?php echo $field['name']; ?>]" class="text" placeholder="<?php echo $field['label']; ?>" value="<?php echo ( array_key_exists( $field['name'], $labels ) ? $labels[$field['name']] : '' ); ?>" />
							<input type="hidden" name="<?php echo $export_type; ?>_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
						</td>
					</tr>
	<?php } ?>
				</tbody>
			</table>
			<!-- #<?php echo $export_type; ?>-fields -->
<?php } else { ?>
			<p><?php _e( 'No Product fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->

</div>
<!-- .export-options -->
<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}
?>