<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package thim
 */
?>
<section class="error-404 not-found">
	<div class="page-content">
		<p>
			<span>4</span><img src="<?php echo get_template_directory_uri() . '/images/404.png' ?>" alt="" /><span>4</span>
		</p>

		<h1 class="page-title"><?php _e( 'Oops, looks like a ghost!', 'thim' ); ?></h1>

		<p>
			<?php _e( 'The page you are looking for can\'t be found. Go home by', 'thim' ); ?>
			<a href="<?php echo esc_url( get_home_url() ); ?>"><?php echo _e( 'clicking here.', 'thim' ); ?></a>
		</p>
	</div>
	<!-- .page-content -->
</section>