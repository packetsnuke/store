<?php
$settings = array(
	'pagination'  => true,
	'speed'       => $instance['thim_slider_speed'],
	'timeout'     => $instance['thim_slider_timeout'],
	'full_screen' => $instance['slider_full_screen'],
	'show_icon_scroll' => $instance['show_icon_scroll']
);

if ( empty( $instance['thim_slider_frames'] ) ) {
	return;
}
?>
<div class="ob-slider-base <?php if ( wp_is_mobile() ) {
	echo 'sow-slider-is-mobile';
} ?>">

	<ul class="ob-slider-images" data-settings="<?php echo esc_attr( json_encode( $settings ) ) ?>">
		<?php
		foreach ( $instance['thim_slider_frames'] as $frame ) {
			if ( empty( $frame['thim_slider_background_image'] ) ) {
				$background_image_url = 'background-image: url(\''. TP_THEME_URI .'/images/demo_images/demo_image.jpg\');';
			} else {
				$background_image     = wp_get_attachment_image_src( $frame['thim_slider_background_image'], 'full' );
				$background_image_url = 'background-image: url(' . esc_url( $background_image['0'] ) . ');';
			}
			?>
			<li
				class="ob-slider-image sow-slider-image-<?php echo esc_attr( $frame['thim_slider_background_image_type'] ) ?>"
				<?php //if ( $instance['slider_full_screen'] == '1' ) {
					echo 'style="' . $background_image_url . '"';
				//} ?>>
				<?php
				if ( $instance['slider_full_screen'] == '0' ) {
					echo wp_get_attachment_image( $frame['thim_slider_background_image'], 'full' );
				}
				$button_before = $button_affter = $button = $style_des = $style_heading = $style_opt = $style_border = '';
				$style_link .= ( $frame['content']['thim_color_border'] != '' ) ? 'border: 2px solid ' . $frame['content']['thim_color_border'] . ';' : '';
				$style_link .= ( $frame['content']['thim_color_bk_border'] != '' ) ? 'background-color: ' . $frame['content']['thim_color_bk_border'] . ';' : 'background-color:transparent;';
				$style_link .= ( $frame['content']['thim_color_button'] != '' ) ? 'color: ' . $frame['content']['thim_color_button'] . ';' : '';
				$style_border = 'style="' . $style_link . '"';
				if ( $frame['content']['button_text'] <> '' ) {
					$button        = '<span ' . $style_border . ' class="btn button-arrow">  ' . $frame['content']['button_text'] . '<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="12" viewBox="-30 0 52 12"><path fill="' . $frame['content']['thim_color_button'] . ' " d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path></svg></span></span>';
					$button_before = '<a  href="' . $frame['content']['button_link'] . '">';
					$button_after  = '</a>';
				}
				if ( !empty( $frame['content']['thim_slider_title'] ) ) {
					?>

					<div class="ob-slider-image-container <?php echo 'slider-' . esc_attr( $frame['content']['thim_slider_align'] ) ?>">
						<div class="wrapper-container">
							<div class="container">
								<?php
								if ( $frame['content']['thim_color_des'] <> '' ) {
									$style_des = 'style="color:' . $frame['content']['thim_color_des'] . '"';
								}
								$style_opt .= ( $frame['content']['thim_color_title'] != '' ) ? 'color: ' . $frame['content']['thim_color_title'] . ';' : '';
								$style_opt .= ( $frame['content']['size'] != '' ) ? 'font-size: ' . $frame['content']['size'] . 'px;line-height:' . $frame['content']['size'] . 'px;' : '';
								$style_opt .= ( $frame['content']['custom_font_weight'] != '' ) ? 'font-weight: ' . $frame['content']['custom_font_weight'] : '';

								if ( $style_opt <> '' ) {
									$style_heading = 'style="' . $style_opt . '"';
								}

								if ( !empty( $frame['content']['thim_slider_title'] ) ) {
									?>
									<h2 class="slider-title" <?php echo ent2ncr( $style_heading ); ?>><?php echo ent2ncr( $frame['content']['thim_slider_title'] ); ?></h2>
								<?php
								}
								if ( !empty( $frame['content']['thim_slider_description'] ) ) {
									echo '<div class="slider-desc">';
									echo '<p ' . $style_des . '>' . $frame['content']['thim_slider_description'] . '</p>';
									echo ent2ncr( $button_before . $button . $button_after );
									echo '</div>';
								}
								?>
							</div>
						</div>
					</div>
					<?php //echo ent2ncr( $button_after ); ?>
				<?php
				}
				?>
			</li>
		<?php
		}
		?>
	</ul>
	<?php
	if ( $instance['show_icon_scroll'] == '1' ) { ?>
		<div class="local-scroll">
			<a class="scroll-down" target="_self" title="button" href="#">
				<img src="<?php echo TP_THEME_URI ?>/images/icon-scroll-copy.png" alt="" />
			</a>
		</div>
	<?php }

	$class_navi      = '';
	$slider_position = $instance['thim_slider_position'];
	if ( $slider_position ) {
		$class_navi = 'navigation-' . $slider_position . '';
	}
	$style_color .= ( $instance['thim_color_badge'] != '' ) ? 'color: ' . $instance['thim_color_badge'] . ';' : '';
	if ( $style_color <> '' ) {
		$style_color_bd = 'style="' . $style_color . '"';
	}

	?>
	<?php if ( count( $instance['thim_slider_frames'] ) > 1 ) {
		?>
		<div class="site-badge-content <?php echo esc_attr( $class_navi ); ?>">
			<div class="site-badge-perspective">
				<div class="badge-diamond">
					<div class="badge-diamond-bg"></div>
				</div>
				<div class="badge-text">
					<div class="ob-slide-nav ob-slide-nav-prev">
						<a href="#" data-goto="previous" data-action="prev">
							<i <?php echo ent2ncr( $style_color_bd ); ?> class="fa fa-long-arrow-left"></i>
						</a>
					</div>
					<div class="badge-navigation">
						<span <?php echo ent2ncr( $style_color_bd ); ?> class="badge-current">1</span>
						<span <?php echo ent2ncr( $style_color_bd ); ?>><?php echo __( 'of', 'thim' ) ?></span>
						<span <?php echo ent2ncr( $style_color_bd ); ?> class="badge-total"><?php echo count( $instance['thim_slider_frames'] ); ?></span>
					</div>

					<div class="ob-slide-nav ob-slide-nav-next">
						<a href="#" data-goto="next" data-action="next">
							<i <?php echo ent2ncr( $style_color_bd ); ?> class="fa fa-long-arrow-right"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<ol class="ob-slider-pagination">
		<?php foreach ( $instance['thim_slider_frames'] as $i => $frame ) : ?>
			<?php
			$class_active = '';
			if ( $i == 0 ) {
				$class_active = 'class="ob-active"';
			} ?>
			<li <?php echo ent2ncr( $class_active ); ?>>
				<a href="#" data-goto="<?php echo esc_attr( $i ) ?>"><?php echo esc_attr( $i + 1 ) ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</div>

