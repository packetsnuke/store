<?php
global $theme_options_data;
if ( ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) || is_active_sidebar( 'header_right' ) ) {
	echo '<div class="header-right"><ul>';
	if ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) {
		?>
		<!--header right-->
		<li class="sliderbar-menu-controller">
			<?php
			$icon = '';
			if ( isset( $theme_options_data['thim_icon_offcanvas_sidebar'] ) ) {
				$icon = 'fa ' . $theme_options_data['thim_icon_offcanvas_sidebar'];
			}
			?>
			<span>
			<i class="<?php echo esc_attr( $icon ); ?>"></i>
		</span>
		</li>
	<?php
	}
	if ( is_active_sidebar( 'header_right' ) ) {
		dynamic_sidebar( 'header_right' );
	}
	echo '</ul></div>';
}
?>