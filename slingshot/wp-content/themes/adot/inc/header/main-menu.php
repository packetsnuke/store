<?php
/**
 * Created by PhpStorm.
 * User: Anh Tuan
 * Date: 7/29/14
 * Time: 10:06 AM
 */
global $theme_options_data;
?>

<ul class="nav navbar-nav menu-main-menu">
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'items_wrap'     => '%3$s'
		) );
	} else {
		wp_nav_menu( array(
			'theme_location' => '',
			'container'      => false,
			'items_wrap'     => '%3$s'
		) );
	}
	//sidebar menu_right
	if ( isset( $theme_options_data['thim_header_style'] ) && $theme_options_data['thim_header_style'] == 'header_v1' ) {
 		if ( is_active_sidebar( 'menu_right' ) || ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) ) {
			echo '<li class="menu-right"><ul>';
			if ( is_active_sidebar( 'menu_right' ) ) {
				dynamic_sidebar( 'menu_right' );
			}
			if ( isset( $theme_options_data['thim_show_offcanvas_sidebar'] ) && $theme_options_data['thim_show_offcanvas_sidebar'] == '1' && is_active_sidebar( 'offcanvas_sidebar' ) ) {
				?>
				<li class="sliderbar-menu-controller">
					<?php
					$icon = '';
					if ( isset( $theme_options_data['thim_icon_offcanvas_sidebar'] ) ) {
						$icon = 'fa ' . $theme_options_data['thim_icon_offcanvas_sidebar'];
					}
					?>
					<span>
				<i class="<?php echo esc_attr($icon); ?>"></i>
			</span>
				</li>

			<?php
			}
			echo '</ul></li>';
		}
	}
	?>
</ul>
<!--</div>-->