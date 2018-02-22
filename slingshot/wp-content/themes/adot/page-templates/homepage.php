<?php
/**
 * Template Name: Home Page
 *
 **/
get_header(); ?>
	<div class="home-content container" role="main">
		<div class="row">
 			<?php
			// Start the Loop.
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
			?>
		</div>
	</div><!-- #main-content -->
<?php get_footer();
