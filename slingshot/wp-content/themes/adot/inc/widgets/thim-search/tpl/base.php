<?php
//echo ent2ncr( $args['before_widget'] );
?>
<?php if ( $instance['title'] <> '' ) {
	echo '<h3 class="widget-title">' . esc_attr( $instance['title'] ) . '</h3>';
} ?>
	<a href="#" class="button-search"><i class="fa fa-search"></i></a>
	<div id="header-search-form-input" class="main-header-search-form-input">
		<div class="search-popup-bg"></div>
		<form class="woocommerce-product-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="text" value="" name="s" id="s" class="form-control ob-search-input" autocomplete="off" placeholder="<?php _e( 'Search Products...', 'thim' ) ?>" />
			<button type="submit" class="button-on-search" role="button"><i class="fa fa-search"></i></button>
			<input type="hidden" name="post_type" value="product" />
		</form>
		<div class="clear"></div>
	</div>
<?php
//echo ent2ncr( $args['after_widget'] );