<?php
/**
 * @package purify
 */
$classes   = array();
$classes[] = 'article-detail';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		<?php thim_posted_on_single(); ?>
	</header>
	<!-- .entry-header -->
	<?php

	/* Video, Audio, Image, Gallery, Default will get thumb */
	if ( has_post_format( 'quote' ) && thim_meta( 'thim_quote' ) && thim_meta( 'thim_author_url' ) ) {
		$quote      = thim_meta( 'thim_quote' );
		$author     = thim_meta( 'thim_author' );
		$author_url = thim_meta( 'thim_author_url' );
		if ( $author_url ) {
			$author = ' <a href=' . esc_url( $author_url ) . '>' . $author . '</a>';
		}
		if ( $quote && $author ) {
			echo '
					<header class="entry-header">
					<div class="box-header box-quote">
						<blockquote>' . $quote . '<cite>' . $author . '</cite></blockquote>
					</div>
					</header>
					';
		}
	} else {
		do_action( 'thim_entry_top', 'full' );
	}
	?>

	<div class="page-content-inner">
		<div class="entry-content">
			<?php the_content(); ?>
			<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'thim' ),
				'after'  => '</div>',
			) );
			?>
		</div>
	</div>
</article><!-- #post-## -->
