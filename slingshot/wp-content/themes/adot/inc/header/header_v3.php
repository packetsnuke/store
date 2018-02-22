<?php
//global $theme_options_data;
//$width_logo = 2;
//if ( isset( $theme_options_data['thim_width_logo'] ) ) {
//	$width_logo = (int) ( $theme_options_data['thim_width_logo'] / 8.3 );
//}
//$width_menu = 12 - $width_logo;
global $theme_options_data;
$custom_sticky = '';
if ( isset( $theme_options_data['thim_config_att_sticky'] ) && $theme_options_data['thim_config_att_sticky'] == 'sticky_custom' ) {
	$custom_sticky = ' bg-custom-sticky';
}
?>
<?php if ( is_mobile() ) {
	echo '<div class="navigation affix-top' . $custom_sticky . '">
	<div class="tm-table">';
}
?>
<div class="menu-mobile-effect navbar-toggle" data-effect="mobile-effect">
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
	<span class="icon-bar"></span>
</div>
<div class="sm-logo">
	<?php
	do_action( 'thim_logo' );
	do_action( 'thim_sticky_logo' );
	?>
</div>
<a href="javascript:;" class="nav-link-menu hidden-xs">
	<div class="nav-link-menu-content">
		<div class="nav-link-menu-outer">
			<span class="nav-link-menu-inner"></span>
		</div>
	</div>
</a>
<nav class="main-menu hidden-xs" role="navigation">
	<?php get_template_part( 'inc/header/main-menu' ); ?>
</nav>
<?php get_template_part( 'inc/header/menu-right' ); ?>
<?php if ( is_mobile() ) {
	echo '</div></div>';
}
?>
