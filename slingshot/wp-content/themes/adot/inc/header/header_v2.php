<?php
global $theme_options_data;
$width_logo = 2;
if ( isset( $theme_options_data['thim_width_logo'] ) ) {
	$width_logo = (int) ( $theme_options_data['thim_width_logo'] / 8.3 );
}
$width_menu    = 12 - $width_logo;
$custom_sticky = '';
if ( isset( $theme_options_data['thim_config_att_sticky'] ) && $theme_options_data['thim_config_att_sticky'] == 'sticky_custom' ) {
	$custom_sticky = ' bg-custom-sticky';
}
?>
<!-- <div class="main-menu"> -->
<div class="navigation affix-top<?php echo esc_attr($custom_sticky); ?>">
	<div class="tm-table">
		<div class="menu-mobile-effect navbar-toggle" data-effect="mobile-effect">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</div>
		<div class="width-logo table-cell sm-logo">
			<a href="javascript:;" class="nav-link-menu hidden-xs">
				<div class="nav-link-menu-content">
					<div class="nav-link-menu-outer">
						<span class="nav-link-menu-inner"></span>
					</div>
				</div>
			</a>
			<?php
			do_action( 'thim_logo' );
			do_action( 'thim_sticky_logo' );
			?>
		</div>

		<nav class="width-navigation table-cell table-right" role="navigation">
			<?php get_template_part( 'inc/header/menu-right' ); ?>
		</nav>
	</div>
	<nav id="nav-menu">
		<?php get_template_part( 'inc/header/main-menu' ); ?>
	</nav>
</div>
