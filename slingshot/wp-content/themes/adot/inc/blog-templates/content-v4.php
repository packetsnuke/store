<?php
/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/7/15
 * Time: 8:47 AM
 */
global $theme_options_data;
$classes = array();
$classes[] = 'article';
$style_layout_front_page = 'cat-style-masory';

if ( isset( $theme_options_data['thim_front_page_style_layout'] ) ) {
	$style_layout_front_page = 'cat-' . $theme_options_data['thim_front_page_style_layout'];
}
$classes[] = $style_layout_front_page;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<div class="content-grid">
		<?php
		$sidebar_thumb_size = 'full';
		if ( has_post_format( 'link' ) && thim_meta( 'thim_url' ) && thim_meta( 'thim_text' ) ) {
			?>

			<?php
			$url  = thim_meta( 'thim_url' );
			$text = thim_meta( 'thim_text' );
			?>
			<div class="article-header">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>

				<div class="entry-meta">
					<?php thim_posted_on_v4(); ?>
					<div class="clear"></div>
				</div>
			</div>
			<?php
			if ( $url && $text ) {
				echo '<div class="article-content">
						<a class="link" href="' . esc_url( $url ) . '">' . esc_attr( $text ) . '</a>
					</div>';
			}
			?>
		<?php
		} elseif ( has_post_format( 'quote' ) && thim_meta( 'thim_quote' ) && thim_meta( 'thim_author_url' ) ) {
			$quote      = thim_meta( 'thim_quote' );
			$author     = thim_meta( 'thim_author' );
			$author_url = thim_meta( 'thim_author_url' );
			if ( $author_url ) {
				$author = ' <a href=' . esc_url( $author_url ) . '>' . $author . '</a>';
			}
			if ( $quote && $author ) {
				?>
				<div class="article-header">
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h2>

					<div class="entry-meta">
						<?php thim_posted_on_v4(); ?>
						<div class="clear"></div>
					</div>
				</div>
				<?php
				echo '
					<div class="article-content">
						<blockquote>' . $quote . '<cite>' . $author . '</cite></blockquote>
					</div>
					';
			}
		}
		else {
			?>

			<?php
			do_action( 'thim_entry_top', $sidebar_thumb_size );
			?>
			<div class="article-header">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>

				<div class="entry-meta">
					<?php thim_posted_on_v4(); ?>
					<div class="clear"></div>
				</div>
			</div>
			<div class="article-content">
				<?php
				global $theme_options_data;
				$length = '50';
				if ( isset( $theme_options_data['thim_archive_excerpt_length'] ) ) {
					$length = $theme_options_data['thim_archive_excerpt_length'];
				}
				echo excerpt( $length ) . '... ';
				?>

			</div>

		<?php } ?>
	</div>
</article><!-- #post-## -->