<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// HTML template for Filter Categories by Language widget on Store Exporter screen
	function woo_ce_categories_filter_by_language() {

		if( !woo_ce_detect_wpml() )
			return;

		$languages = ( function_exists( 'icl_get_languages' ) ? icl_get_languages( 'skip_missing=N' ) : array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="categories-filters-language" /> <?php _e( 'Filter Categories by Language', 'woocommerce-exporter' ); ?></label></p>
<div id="export-categories-filters-language" class="separator">
	<ul>
		<li>
<?php if( !empty( $languages ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Language...', 'woocommerce-exporter' ); ?>" name="category_filter_language[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $languages as $key => $language ) { ?>
				<option value="<?php echo $key; ?>"><?php echo $language['native_name']; ?> (<?php echo $language['translated_name']; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Languages were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Language\'s you want to filter exported Categories by. Default is to include all Language\'s.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-tags-filters-language -->

<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}

function woo_ce_extend_cron_category_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Category Export Type
	if( $export_type <> 'category' )
		return $args;

	$category_orderby = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$category_orderby = get_post_meta( $scheduled_export, '_filter_category_orderby', true );
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'category_orderby' => ( !empty( $category_orderby ) ? $category_orderby : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_action( 'woo_ce_extend_cron_dataset_args', 'woo_ce_extend_cron_category_dataset_args', 10, 3 );

// Adds custom Category columns to the Category fields list
function woo_ce_extend_category_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$fields[] = array(
			'name' => 'wpseo_title',
			'label' => __( 'WordPress SEO - SEO Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_description',
			'label' => __( 'WordPress SEO - SEO Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_focuskw',
			'label' => __( 'WordPress SEO - Focus Keyword', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_canonical',
			'label' => __( 'WordPress SEO - Canonical', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_noindex',
			'label' => __( 'WordPress SEO - Noindex', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_sitemap_include',
			'label' => __( 'WordPress SEO - Sitemap include', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_title',
			'label' => __( 'WordPress SEO - Facebook Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_description',
			'label' => __( 'WordPress SEO - Facebook Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_image',
			'label' => __( 'WordPress SEO - Facebook Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_title',
			'label' => __( 'WordPress SEO - Twitter Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_description',
			'label' => __( 'WordPress SEO - Twitter Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_image',
			'label' => __( 'WordPress SEO - Twitter Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$fields[] = array(
			'name' => 'language',
			'label' => __( 'Language', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Multilingual', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_category_fields', 'woo_ce_extend_category_fields' );

// Turns out we didn't need this after all...
function woo_ce_extend_get_product_categories_args( $args ) {

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {

		global $sitepress;

		remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
		remove_filter( 'get_term', array( $sitepress,'get_term_adjust_id' ) );
		remove_filter( 'terms_clauses', array( $sitepress,'terms_clauses' ) );

	}

	return $args;

}
// add_filter( 'woo_ce_get_product_categories_args', 'woo_ce_extend_get_product_categories_args' );

function woo_ce_extend_category_item( $category ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$category->blog_id = get_current_blog_id();
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$meta = get_option( 'wpseo_taxonomy_meta' );
		// Check if the WordPress Option is empty
		if( $meta !== false ) {
			// Check if the WordPress Option is an array
			if( is_array( $meta ) ) {
				// Check if the product_cat Taxonomy exists within the WordPress Option
				$term_taxonomy = 'product_cat';
				if( array_key_exists( $term_taxonomy, $meta ) ) {
					$meta = $meta[$term_taxonomy];
					// Check if the Term ID exists within the array
					$term_id = ( isset( $category->term_id ) ? $category->term_id : 0 );
					if( array_key_exists( $term_id, $meta ) ) {
						$category->wpseo_title = ( isset( $meta[$term_id]['wpseo_title'] ) ? $meta[$term_id]['wpseo_title'] : '' );
						$category->wpseo_description = ( isset( $meta[$term_id]['wpseo_desc'] ) ? $meta[$term_id]['wpseo_desc'] : '' );
						$category->wpseo_canonical = ( isset( $meta[$term_id]['wpseo_canonical'] ) ? $meta[$term_id]['wpseo_canonical'] : '' );
						$category->wpseo_noindex = ( isset( $meta[$term_id]['wpseo_noindex'] ) ? woo_ce_format_wpseo_noindex( $meta[$term_id]['wpseo_noindex'] ) : '' );
						$category->wpseo_sitemap_include = ( isset( $meta[$term_id]['wpseo_sitemap_include'] ) ? woo_ce_format_wpseo_sitemap_include( $meta[$term_id]['wpseo_sitemap_include'] ) : '' );
						$category->wpseo_focuskw = ( isset( $meta[$term_id]['wpseo_focuskw'] ) ? $meta[$term_id]['wpseo_focuskw'] : '' );
						$category->wpseo_opengraph_title = ( isset( $meta[$term_id]['wpseo_opengraph-title'] ) ? $meta[$term_id]['wpseo_opengraph-title'] : '' );
						$category->wpseo_opengraph_description = ( isset( $meta[$term_id]['wpseo_opengraph-description'] ) ? $meta[$term_id]['wpseo_opengraph-description'] : '' );
						$category->wpseo_opengraph_image = ( isset( $meta[$term_id]['wpseo_opengraph-image'] ) ? $meta[$term_id]['wpseo_opengraph-image'] : '' );
						$category->wpseo_twitter_title = ( isset( $meta[$term_id]['wpseo_twitter-title'] ) ? $meta[$term_id]['wpseo_twitter-title'] : '' );
						$category->wpseo_twitter_description = ( isset( $meta[$term_id]['wpseo_twitter-description'] ) ? $meta[$term_id]['wpseo_twitter-description'] : '' );
						$category->wpseo_twitter_image = ( isset( $meta[$term_id]['wpseo_twitter-image'] ) ? $meta[$term_id]['wpseo_twitter-image'] : '' );
					}
					unset( $term_id );
				}
			}
		}
		unset( $meta );
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$term_id = ( isset( $category->term_id ) ? $category->term_id : 0 );
		$term_taxonomy = 'product_cat';
		$category->language = woo_ce_wpml_get_language_name( apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $term_id, 'element_type' => $term_taxonomy ) ) );
	}

	return $category;

}
add_filter( 'woo_ce_category_item', 'woo_ce_extend_category_item' );
?>