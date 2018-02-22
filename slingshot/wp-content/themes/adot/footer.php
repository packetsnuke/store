<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package thim
 */
?>
<footer id="footer" class="site-footer" role="contentinfo">
	<?php global $theme_options_data; ?>
	<?php if ( is_active_sidebar( 'footer' ) ) : ?>
		<div class="footer">
			<div class="container">
				<div class="row">
					<?php dynamic_sidebar( 'footer' ); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<!--==============================powered=====================================-->
	<?php if ( isset( $theme_options_data['thim_copyright_text'] ) || is_active_sidebar( 'copyright' ) ) { ?>
		<div id="powered">
			<div class="container">
				<div class="row">
					<div class="col-sm-12 copyright">
						<?php if ( is_active_sidebar( 'copyright' ) ) : ?>
							<?php dynamic_sidebar( 'copyright' ); ?>
						<?php endif; ?>
						<?php
						if ( isset( $theme_options_data['thim_copyright_text'] ) ) {
							echo '<p class="text-copyright">'. $theme_options_data['thim_copyright_text'].'</p>';
						}
						?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</footer><!-- #colophon -->
</div><!--end -->
</div></div><!-- .wrapper-container -->

<!-- .box-area -->
<?php if ( isset( $theme_options_data['thim_show_to_top'] ) && $theme_options_data['thim_show_to_top'] == 1 ) { ?>
	<a id='topcontrol' class="scrollup show" title="<?php esc_attr_e( 'Go To Top', 'thim' ); ?>"><?php //esc_attr_e( 'Go To Top', 'thim' ); ?></a>
<?php } ?>

<?php //if ( isset( $theme_options_data['thim_box_layout'] ) && $theme_options_data['thim_box_layout'] == "boxed" ) {
//	echo '</div>';
//} ?>

<?php if ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) { ?>
	<div class="slider-sidebar">
		<?php dynamic_sidebar( 'offcanvas_sidebar' ); ?>
	</div>  <!--slider_sidebar-->
<?php } ?>

<?php wp_footer(); ?>
</body>
</html>

