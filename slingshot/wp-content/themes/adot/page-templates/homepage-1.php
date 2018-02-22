<?php
/**
 * Template Name: Home Page No Footer
 *
 **/
get_header(); ?>
	<div class="home-content container" role="main">
 			<?php
			// Start the Loop.
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
			?>
	</div><!-- #main-content -->
</div>
</div></div><!-- .wrapper-container -->

<!-- .box-area -->
<?php if ( isset( $theme_options_data['thim_box_layout'] ) && $theme_options_data['thim_box_layout'] == "boxed" ) {
	echo '</div>';
} ?>
<?php if ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) { ?>
	<div class="slider-sidebar">
		<?php dynamic_sidebar( 'offcanvas_sidebar' ); ?>
	</div>  <!--slider_sidebar-->
<?php } ?>

<?php wp_footer(); ?>
</body>
</html>


