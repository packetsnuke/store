<?php
/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/3/15
 * Time: 3:24 PM
 */
global $theme_options_data;
$classes                 = array();
$classes[]               = 'article';
$style_layout_front_page = 'cat-style-3';
$classes[]               = $style_layout_front_page;
?>


<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<?php
	$sidebar_thumb_size = 'full';
	if ( has_post_format( 'link' ) && thim_meta( 'thim_url' ) && thim_meta( 'thim_text' ) ) {
		?>
		<div class="article-meta">
			<?php thim_posted_on_date_v3() ?>
		</div>

		<?php
		$url  = thim_meta( 'thim_url' );
		$text = thim_meta( 'thim_text' );

		if ( $url && $text ) {
			echo '';
			echo '<div class="article-content">';
			?>

			<div class="article-header">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>

				<div class="entry-meta">
					<?php thim_posted_on_v3(); ?>
				</div>
			</div>
			<?php

			echo '<a class="link" href="' . esc_url( $url ) . '">' . esc_attr( $text ) . '</a>
					</div>';
			echo '<div class="clear"></div>';
		}
	} elseif ( has_post_format( 'quote' ) && thim_meta( 'thim_quote' ) && thim_meta( 'thim_author_url' ) ) {
		$quote      = thim_meta( 'thim_quote' );
		$author     = thim_meta( 'thim_author' );
		$author_url = thim_meta( 'thim_author_url' );
		if ( $author_url ) {
			$author = ' <a href=' . esc_url( $author_url ) . '>' . $author . '</a>';
		}
		if ( $quote && $author ) {
			?>
			<div class="article-meta">
				<?php thim_posted_on_date_v3(); ?>
			</div>
			<div class="article-content">
				<div class="article-header">
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h2>

					<div class="entry-meta">
						<?php thim_posted_on_v3(); ?>
					</div>
				</div>
				<div class="box-header box-quote">
					<blockquote><?php echo ent2ncr( $quote ) ?><cite><?php echo ent2ncr( $author ) ?></cite>
					</blockquote>
				</div>
			</div>
			<div class="clear"></div>
		<?php
		}
	} else {
		?>

		<div class="article-meta">
			<?php thim_posted_on_date_v3(); ?>
		</div>
		<div class="article-content">
			<?php
			do_action( 'thim_entry_top', $sidebar_thumb_size );
			?>
			<div class="article-header">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>

				<div class="entry-meta">
					<?php thim_posted_on_v3(); ?>
				</div>
			</div>

			<div class="article-excerpt">
				<?php
				global $theme_options_data;
				$length = '50';
				if ( isset( $theme_options_data['thim_archive_excerpt_length'] ) ) {
					$length = $theme_options_data['thim_archive_excerpt_length'];
				}
				echo excerpt( $length ) . '... ';
				?>
			</div>

			<div class="article-footer">
				<?php thim_posted_on_comment_v3(); ?>
				<div class="article-read-more">
					<a href="<?php the_permalink(); ?>" class="read-more"><?php echo _e( 'Continue reading', 'thim' ); ?>
						<span class="arrow">
							<svg xmlns="http://www.w3.org/2000/svg" width="34" height="12" viewBox="-30 0 52 12">
								<path d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path>
							</svg>
						</span>
					</a>
				</div>
				<div class="clear"></div>
			</div>
			<!--end article-footer-->
		</div>
		<div class="clear"></div>
	<?php } ?>
</article><!-- #post-## -->