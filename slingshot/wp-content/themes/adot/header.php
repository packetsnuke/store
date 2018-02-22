<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package agapi
 */
?><!DOCTYPE html>
<?php
global $theme_options_data;
?>
<html <?php language_attributes(); ?><?php if ( isset( $theme_options_data['thim_rtl_support'] ) && $theme_options_data['thim_rtl_support'] == '1' ) {
	echo "dir=\"rtl\"";
} ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php esc_url( bloginfo( 'pingback_url' ) ); ?>">
	<?php
	global $theme_options_data;
	$class_header = '';
	if ( isset( $theme_options_data['thim_favicon'] ) ) {
		$thim_favicon     = $theme_options_data['thim_favicon'];
		$thim_favicon_src = $thim_favicon; // For the default value
		if ( is_numeric( $thim_favicon ) ) {
			$favicon_attachment = wp_get_attachment_image_src( $thim_favicon, 'full' );
			$thim_favicon_src   = $favicon_attachment[0];
		}
	} else {
		$thim_favicon_src = get_template_directory_uri() . "/images/favicon.ico";
	}
	if ( isset( $theme_options_data['thim_header_position'] ) ) {
		$class_header .= $theme_options_data['thim_header_position'];
	}
	if ( isset( $theme_options_data['thim_header_style'] ) ) {
		$class_header .= ' body-' . $theme_options_data['thim_header_style'];
	}
	if ( isset( $theme_options_data['thim_header_layout'] ) ) {
		$class_header .= ' ' . $theme_options_data['thim_header_layout'];
	}
	?>
	<link rel="shortcut icon" href=" <?php echo esc_url( $thim_favicon_src ); ?>" type="image/x-icon" />
	<?php
	wp_head();
	?>
</head>

<body <?php body_class( $class_header ); ?>>
<?php if ( isset( $theme_options_data['thim_preload'] ) && $theme_options_data['thim_preload'] == '1' ) { ?>
	<div id="preload">
		<div class="svg-container" id="boxContainer">
			<div id="boxLoader"></div>
			<div id="base"></div>
		</div>
	</div>
<?php } ?>

<?php
// sticky header
$sticky_header = $class_sticky = '';
if ( isset( $theme_options_data['thim_header_sticky'] ) && $theme_options_data['thim_header_sticky'] == 1 ) {
	$sticky_header = ' sticky-header';
	$class_sticky  = ' wrapper-sticky-header';
}
?>
<!-- menu for mobile-->
<div id="wrapper-container" class="wrapper-container">
	<div class="content-pusher <?php echo esc_attr( $class_sticky );
	if ( isset( $theme_options_data['thim_box_layout'] ) && $theme_options_data['thim_box_layout'] == "boxed" ) {
		echo ' boxed-area';
	} ?>">
		<!-- menu for mobile-->
		<?php //if ( wp_is_mobile() ) {
		?>
		<nav class="visible-xs mobile-menu-container mobile-effect" role="navigation">
			<?php get_template_part( 'inc/header/mobile-menu' ); ?>
		</nav>
		<div id="main-content">
		<?php
		//}
		?>
		<?php
		// Drawer

		if ( isset( $theme_options_data['thim_show_drawer'] ) && $theme_options_data['thim_show_drawer'] == '1' && is_active_sidebar( 'drawer_top' ) ) {
			get_template_part( 'inc/header/drawer' );
		}

		?>
		<header id="masthead" class="site-header <?php echo esc_attr( $theme_options_data['thim_header_style'] ) . $sticky_header;
		if ( $theme_options_data['thim_header_style'] == 'header_v3' ) {
			echo ' hide-menu';
		} ?>" role="banner">
			<?php
			if ( isset( $theme_options_data['thim_header_style'] ) && $theme_options_data['thim_header_style'] ) {
				get_template_part( 'inc/header/' . $theme_options_data['thim_header_style'] );
			} else {
				get_template_part( 'inc/header/header_v1' );
			}
			?>
		</header>

		<?php
		if ( isset( $theme_options_data['thim_header_style'] ) && $theme_options_data['thim_header_style'] == 'header_v3' ) {
			get_template_part( 'inc/header/header-right' );
		} ?>
