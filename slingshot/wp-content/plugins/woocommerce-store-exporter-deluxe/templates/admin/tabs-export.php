<ul class="subsubsub">
	<li><strong><?php _e( 'Quick Export', 'woocommerce-exporter' ); ?></strong> |</li>
	<li><a href="#export-type"><?php _e( 'Export Types', 'woocommerce-exporter' ); ?></a> |</li>
	<li><a href="#export-options"><?php _e( 'Export Options', 'woocommerce-exporter' ); ?></a></li>
	<?php do_action( 'woo_ce_export_quicklinks' ); ?>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<div id="poststuff">
	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'failed' => null, 'empty' => null, 'message' => null ) ) ); ?>" id="postform">

		<?php do_action( 'woo_ce_export_before_options' ); ?>

		<div class="export-types">
			<div class="postbox">
				<h3 class="hndle"><?php _e( 'Loading...', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">
					<p><strong><?php _e( 'The Quick Export screen is loading elements in the background.', 'woocommerce-exporter' ); ?></strong></p>
					<p><?php _e( 'If this notice does not dissapear once the browser has finished loading then something has gone wrong. This could be due to a <a href="http://www.visser.com.au/documentation/store-exporter-deluxe/usage/#The_Export_screen_loads_but_is_missing_fields_andor_elements_including_the_Export_button" target="_blank">JavaScript error</a> or <a href="http://www.visser.com.au/documentation/store-exporter-deluxe/usage/#Increasing_memory_allocated_to_PHP" target="_blank">memory/timeout limitation</a> whilst loading the Export screen, please open a <a href="http://www.visser.com.au/premium-support/" target="_blank">Support ticket</a> with us to look at this with you. :)', 'woocommerce-exporter' ); ?></p>
				</div>
			</div>
			<!-- .postbox -->
		</div>

<?php if( $product && $product_fields ) { ?>
		<div id="export-product" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Product Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $product ) { ?>
					<p class="description"><?php _e( 'Select the Product fields you would like to export, you can drag-and-drop to reorder export fields and change the label of export fields from the Configure link. Your field selection and supported export filters are saved for future exports.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="product-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="product-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="product-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'product' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="product-fields" class="ui-sortable">

		<?php foreach( $product_fields as $field ) { ?>
						<tr id="product-<?php echo $field['reset']; ?>" data-export-type="product" data-field-name="<?php printf( '%s-%s', 'product', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="product_fields[<?php echo $field['name']; ?>]" class="product_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'product' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="product_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_product" value="<?php _e( 'Export Products', 'woocommerce-exporter' ); ?> " class="button-primary" />
					</p>
					<p class="description"><?php printf( __( 'Can\'t find a particular Product field in the above export list? You can export custom Product meta and custom Attributes as fields by scrolling down to <a href="#export-products-custom-fields">Custom Product Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
	<?php } else { ?>
					<p><?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
			</div>
			<!-- .postbox -->

			<div id="export-products-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Product Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_product_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_product_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_product_options_after_table' ); ?>

				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-product -->

<?php } ?>
<?php if( $category && $category_fields ) { ?>
		<div id="export-category" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Category Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
					<p class="description"><?php _e( 'Select the Category fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="category-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="category-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="category-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'category' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="category-fields" class="ui-sortable">

	<?php foreach( $category_fields as $field ) { ?>
						<tr id="category-<?php echo $field['reset']; ?>" data-export-type="category" data-field-name="<?php printf( '%s-%s', 'category', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="category_fields[<?php echo $field['name']; ?>]" class="category_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
		<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'category' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
		<?php } ?>
									<input type="hidden" name="category_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

	<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_category" value="<?php _e( 'Export Categories', 'woocommerce-exporter' ); ?> " class="button-primary" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Category field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-categories-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Category Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_category_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_category_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_category_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- #export-categories-filters -->

		</div>
		<!-- #export-category -->
<?php } ?>
<?php if( $tag && $tag_fields ) { ?>
		<div id="export-tag" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Tag Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
					<p class="description"><?php _e( 'Select the Tag fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="tag-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="tag-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="tag-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a>| 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'tag' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="tag-fields" class="ui-sortable">

	<?php foreach( $tag_fields as $field ) { ?>
						<tr id="tag-<?php echo $field['reset']; ?>" data-export-type="tag" data-field-name="<?php printf( '%s-%s', 'tag', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="tag_fields[<?php echo $field['name']; ?>]" class="tag_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
		<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'tag' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
		<?php } ?>
									<input type="hidden" name="tag_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

	<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_tag" value="<?php _e( 'Export Tags', 'woocommerce-exporter' ); ?> " class="button-primary" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Tag field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-tags-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Product Tag Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_tag_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_tag_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_tag_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- #export-tags-filters -->

		</div>
		<!-- #export-tag -->
<?php } ?>

<?php if( $brand && $brand_fields ) { ?>
		<div id="export-brand" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Brand Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $brand ) { ?>
					<p class="description"><?php _e( 'Select the Brand fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="brand-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="brand-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="brand-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'brand' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="brand-fields" class="ui-sortable">

		<?php foreach( $brand_fields as $field ) { ?>
						<tr id="brand-<?php echo $field['reset']; ?>" data-export-type="brand" data-field-name="<?php printf( '%s-%s', 'brand', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="brand_fields[<?php echo $field['name']; ?>]" class="brand_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'brand' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="brand_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_brand" class="button-primary" value="<?php _e( 'Export Brands', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Brand field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Brands were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-brands-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Brand Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_brand_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_brand_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_brand_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-brand -->

<?php } ?>
<?php if( $order && $order_fields ) { ?>
		<div id="export-order" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Order Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">

	<?php if( $order ) { ?>
					<p class="description"><?php _e( 'Select the Order fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="order-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="order-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> |
						<a href="javascript:void(0)" id="order-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'order' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="order-fields" class="ui-sortable">

		<?php foreach( $order_fields as $field ) { ?>
						<tr id="order-<?php echo $field['reset']; ?>" data-export-type="order" data-field-name="<?php printf( '%s-%s', 'order', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="order_fields[<?php echo $field['name']; ?>]" class="order_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'order' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="order_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_order" class="button-primary" value="<?php _e( 'Export Orders', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php printf( __( 'Can\'t find a particular Order field in the above export list? You can export custom Order meta, Order Item meta and Order Item Product meta as fields by scrolling down to <a href="#export-orders-custom-fields">Custom Order Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
	<?php } else { ?>
					<p><?php _e( 'No Orders were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>

				</div>
			</div>
			<!-- .postbox -->

			<div id="export-orders-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Order Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_order_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_order_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_order_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-order -->

<?php } ?>
<?php if( $customer && $customer_fields ) { ?>
		<div id="export-customer" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Customer Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $customer ) { ?>
					<p class="description"><?php _e( 'Select the Customer fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="customer-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="customer-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="customer-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'customer' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="customer-fields" class="ui-sortable">

		<?php foreach( $customer_fields as $field ) { ?>
						<tr id="customer-<?php echo $field['reset']; ?>" data-export-type="customer" data-field-name="<?php printf( '%s-%s', 'customer', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="customer_fields[<?php echo $field['name']; ?>]" class="customer_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'customer' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="customer_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_customer" class="button-primary" value="<?php _e( 'Export Customers', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php printf( __( 'Can\'t find a particular Customer field in the above export list? You can export custom Customer meta as fields by scrolling down to <a href="#export-customers-custom-fields">Custom Customer Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
	<?php } else { ?>
					<p><?php _e( 'No Customers were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-customers-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Customer Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_customer_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_customer_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_customer_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-customer -->

<?php } ?>
<?php if( $user && $user_fields ) { ?>
		<div id="export-user" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'User Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $user ) { ?>
					<p class="description"><?php _e( 'Select the User fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="user-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="user-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="user-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'user' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="user-fields" class="ui-sortable">

		<?php foreach( $user_fields as $field ) { ?>
						<tr id="user-<?php echo $field['reset']; ?>" data-export-type="user" data-field-name="<?php printf( '%s-%s', 'user', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="user_fields[<?php echo $field['name']; ?>]" class="user_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'user' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="user_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_user" class="button-primary" value="<?php _e( 'Export Users', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php printf( __( 'Can\'t find a particular User field in the above export list? You can export custom User meta as fields by scrolling down to <a href="#export-users-custom-fields">Custom User Fields</a>, if you get stuck <a href="%s" target="_blank">get in touch</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
	<?php } else { ?>
					<p><?php _e( 'No Users were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-users-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'User Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_user_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_user_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_user_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-user -->

<?php } ?>
<?php if( $review && $review_fields ) { ?>
		<div id="export-review" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Review Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $review ) { ?>
					<p class="description"><?php _e( 'Select the review fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="review-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="review-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="review-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'review' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="review-fields" class="ui-sortable">

		<?php foreach( $review_fields as $field ) { ?>
						<tr id="review-<?php echo $field['reset']; ?>" data-export-type="review" data-field-name="<?php printf( '%s-%s', 'review', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="review_fields[<?php echo $field['name']; ?>]" class="review_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'review' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="review_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_review" value="<?php _e( 'Export Reviews', 'woocommerce-exporter' ); ?> " class="button-primary" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Review field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Reviews were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
			</div>
			<!-- .postbox -->

			<div id="export-reviews-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Review Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_review_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_review_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_review_options_after_table' ); ?>

				</div>
				<!-- .inside -->

			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-review -->

<?php } ?>
<?php if( $coupon && $coupon_fields ) { ?>
		<div id="export-coupon" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Coupon Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $coupon ) { ?>
					<p class="description"><?php _e( 'Select the Coupon fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="coupon-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="coupon-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="coupon-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'coupon' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="coupon-fields" class="ui-sortable">

		<?php foreach( $coupon_fields as $field ) { ?>
						<tr id="coupon-<?php echo $field['reset']; ?>" data-export-type="coupon" data-field-name="<?php printf( '%s-%s', 'coupon', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="coupon_fields[<?php echo $field['name']; ?>]" class="coupon_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'coupon' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="coupon_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_coupon" class="button-primary" value="<?php _e( 'Export Coupons', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Coupon field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Coupons were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-coupons-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Coupon Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_coupon_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_coupon_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_coupon_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-coupon -->

<?php } ?>
<?php if( $subscription && $subscription_fields ) { ?>
		<div id="export-subscription" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Subscription Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $subscription ) { ?>
					<p class="description"><?php _e( 'Select the Subscription fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="subscription-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="subscription-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="subscription-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'subscription' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="subscription-fields" class="ui-sortable">

		<?php foreach( $subscription_fields as $field ) { ?>
						<tr id="subscription-<?php echo $field['reset']; ?>" data-export-type="subscription" data-field-name="<?php printf( '%s-%s', 'subscription', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="subscription_fields[<?php echo $field['name']; ?>]" class="subscription_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'subscription' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="subscription_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_subscription" class="button-primary" value="<?php _e( 'Export Subscriptions', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Subscription field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Subscriptions were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-subscriptions-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Subscription Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_subscription_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_subscription_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_subscription_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-subscription -->

<?php } ?>
<?php if( $product_vendor && $product_vendor_fields ) { ?>
		<div id="export-product_vendor" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Product Vendor Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $product_vendor ) { ?>
					<p class="description"><?php _e( 'Select the Product Vendor fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="product_vendor-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="product_vendor-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="product_vendor-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'product_vendor' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="product_vendor-fields" class="ui-sortable">

		<?php foreach( $product_vendor_fields as $field ) { ?>
						<tr id="product_vendor-<?php echo $field['reset']; ?>" data-export-type="product_vendor" data-field-name="<?php printf( '%s-%s', 'product_vendor', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="product_vendor_fields[<?php echo $field['name']; ?>]" class="product_vendor_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'product_vendor' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="product_vendor_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_product_vendor" class="button-primary" value="<?php _e( 'Export Product Vendors', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Product Vendor field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-product_vendor -->

<?php } ?>
<?php if( $commission && $commission_fields ) { ?>
		<div id="export-commission" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Commission Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $commission ) { ?>
					<p class="description"><?php _e( 'Select the Commission fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="commission-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="commission-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="commission-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'commission' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="commission-fields" class="ui-sortable">

		<?php foreach( $commission_fields as $field ) { ?>
						<tr id="commission-<?php echo $field['reset']; ?>" data-export-type="commission" data-field-name="<?php printf( '%s-%s', 'commission', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="commission_fields[<?php echo $field['name']; ?>]" class="commission_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'commission' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="commission_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_commissions" class="button-primary" value="<?php _e( 'Export Commissions', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Commission field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Commissions were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-commissions-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Commission Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_commission_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_commission_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_commission_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-commission -->

<?php } ?>
<?php if( $shipping_class && $shipping_class_fields ) { ?>
		<div id="export-shipping_class" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Shipping Class Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $shipping_class ) { ?>
					<p class="description"><?php _e( 'Select the Shipping Class fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="shipping_class-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="shipping_class-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="shipping_class-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'shipping_class' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="shipping_class-fields" class="ui-sortable">

		<?php foreach( $shipping_class_fields as $field ) { ?>
						<tr id="shipping_class-<?php echo $field['reset']; ?>" data-export-type="shipping_class" data-field-name="<?php printf( '%s-%s', 'shipping_class', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="shipping_class_fields[<?php echo $field['name']; ?>]" class="shipping_class_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'shipping_class' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="shipping_class_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_shipping_class" class="button-primary" value="<?php _e( 'Export Shipping Classes', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Shipping Class field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Shipping Classes were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-shipping-classes-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Shipping Class Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_shipping_class_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_shipping_class_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_shipping_class_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-shipping_class -->

<?php } ?>
<?php if( $ticket && $ticket_fields ) { ?>
		<div id="export-ticket" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Ticket Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $ticket ) { ?>
					<p class="description"><?php _e( 'Select the Ticket fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="ticket-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="ticket-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="ticket-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'ticket' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="ticket-fields" class="ui-sortable">

		<?php foreach( $ticket_fields as $field ) { ?>
						<tr id="ticket-<?php echo $field['reset']; ?>" data-export-type="ticket" data-field-name="<?php printf( '%s-%s', 'ticket', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="ticket_fields[<?php echo $field['name']; ?>]" class="ticket_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'ticket' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="ticket_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_ticket" class="button-primary" value="<?php _e( 'Export Tickets', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Ticket field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Tickets were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-ticket-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Ticket Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_ticket_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_ticket_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_ticket_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-ticket -->

<?php } ?>
<?php if( $booking && $booking_fields ) { ?>
		<div id="export-booking" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Booking Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $booking ) { ?>
					<p class="description"><?php _e( 'Select the Booking fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="booking-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="booking-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="booking-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'booking' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="booking-fields" class="ui-sortable">

		<?php foreach( $booking_fields as $field ) { ?>
						<tr id="booking-<?php echo $field['reset']; ?>" data-export-type="booking" data-field-name="<?php printf( '%s-%s', 'booking', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="booking_fields[<?php echo $field['name']; ?>]" class="booking_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'booking' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="booking_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_booking" class="button-primary" value="<?php _e( 'Export Bookings', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Booking field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Bookings were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

			<div id="export-bookings-filters" class="postbox">
				<h3 class="hndle"><?php _e( 'Booking Filters', 'woocommerce-exporter' ); ?></h3>
				<div class="inside">

					<?php do_action( 'woo_ce_export_booking_options_before_table' ); ?>

					<table class="form-table">
						<?php do_action( 'woo_ce_export_booking_options_table' ); ?>
					</table>

					<?php do_action( 'woo_ce_export_booking_options_after_table' ); ?>

				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->

		</div>
		<!-- #export-booking -->

<?php } ?>
<?php if( $attribute && $attribute_fields ) { ?>
		<div id="export-attribute" class="export-types">

			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'Attribute Fields', 'woocommerce-exporter' ); ?>
				</h3>
				<div class="inside">
	<?php if( $attribute ) { ?>
					<p class="description"><?php _e( 'Select the Attribute fields you would like to export.', 'woocommerce-exporter' ); ?></p>
					<p>
						<a href="javascript:void(0)" id="attribute-checkall" class="checkall"><?php _e( 'Check All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="attribute-uncheckall" class="uncheckall"><?php _e( 'Uncheck All', 'woocommerce-exporter' ); ?></a> | 
						<a href="javascript:void(0)" id="attribute-resetsorting" class="resetsorting"><?php _e( 'Reset Sorting', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'fields', 'type' => 'attribute' ) ) ); ?>"><?php _e( 'Configure', 'woocommerce-exporter' ); ?></a>
					</p>
					<table id="attribute-fields" class="ui-sortable">

		<?php foreach( $attribute_fields as $field ) { ?>
						<tr id="attribute-<?php echo $field['reset']; ?>" data-export-type="attribute" data-field-name="<?php printf( '%s-%s', 'attribute', $field['name'] ); ?>">
							<td>
								<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
									<input type="checkbox" name="attribute_fields[<?php echo $field['name']; ?>]" class="attribute_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?><?php disabled( $field['disabled'], 1 ); ?> />
									<span class="field_title"><?php echo $field['label']; ?></span>
			<?php if( isset( $field['hover'] ) && apply_filters( 'woo_ce_export_fields_hover_label', true, 'attribute' ) ) { ?>
									<span class="field_hover"><?php echo $field['hover']; ?></span>
			<?php } ?>
									<input type="hidden" name="attribute_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
								</label>
							</td>
						</tr>

		<?php } ?>
					</table>
					<p class="submit">
						<input type="submit" id="export_attribute" class="button-primary" value="<?php _e( 'Export Attributes', 'woocommerce-exporter' ); ?>" />
					</p>
					<p class="description"><?php _e( 'Can\'t find a particular Attribute field in the above export list?', 'woocommerce-exporter' ); ?> <a href="<?php echo $troubleshooting_url; ?>" target="_blank"><?php _e( 'Get in touch', 'woocommerce-exporter' ); ?></a>.</p>
	<?php } else { ?>
					<p><?php _e( 'No Attributes were found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
				</div>
				<!-- .inside -->
			</div>
			<!-- .postbox -->
		</div>
		<!-- #export-attributes -->
<?php } ?>

		<?php do_action( 'woo_ce_export_after_options' ); ?>

		<input type="hidden" name="action" value="export" />
		<?php wp_nonce_field( 'manual_export', 'woo_ce_export' ); ?>

	</form>

	<?php do_action( 'woo_ce_export_after_form' ); ?>

</div>
<!-- #poststuff -->
