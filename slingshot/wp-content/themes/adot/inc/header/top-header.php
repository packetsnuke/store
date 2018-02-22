<?php
/**
 * Created by PhpStorm.
 * User: Anh Tuan
 * Date: 11/29/2014
 * Time: 11:36 AM
 */
global $theme_options_data;
//$width_clumn = (int) ( $theme_options_data['thim_width_left_top'] / 8.3 );
//$width_top_sidebar_right = 12 - $width_clumn;
?>

<?php if ( is_active_sidebar( 'toolbar' ) ) : ?>
	<div class="top-header">
		<?php
		if ( isset( $theme_options_data['thim_header_layout'] ) && $theme_options_data['thim_header_layout'] == 'wide' ) {
			echo "<div class=\"container\">";
		}
		?>
		<?php if ( is_active_sidebar( 'toolbar' ) ) : ?>
			<div class="row">
				<?php dynamic_sidebar( 'toolbar' ); ?>
			</div>
		<?php endif; ?>
		<?php if ( isset( $theme_options_data['thim_header_layout'] ) && $theme_options_data['thim_header_layout'] == 'wide' ) {
			echo "</div>";
		}
		?>
	</div><!--End/div.top-->
<?php
endif;