<?php
$style = $style_css = $class = $style_arrow = $background_color = $icon_color = $show_number = '';

$background_color = $instance['bg_color'];
$show_number      = $instance['show_number'];
$icon_color       = $instance['color_number'];
$style .= ( $background_color != '' ) ? 'background: ' . $background_color . ';' : '';
$style .= ( $icon_color != '' ) ? 'color: ' . $icon_color : '';
$style_css .= ( $style != '' ) ? ' style="' . $style . '"' : '';

$style_arrow       = ( $background_color != '' ) ? 'style="border-bottom: 6px solid  ' . $background_color . ';"' : '';
$background_widget = $instance['bg_color_icon'];
if ( $background_widget == 1 ) {
	$class = ' background-primary';
}
//$color_widget      = $instance['color_icon'];
//$style_widget .= ( $background_widget != '' ) ? 'background: ' . $background_widget . ';' : '';
//$style_widget .= ( $color_widget != '' ) ? 'color: ' . $color_widget : '';
//$style_widget_css .= ( $style_widget != '' ) ? ' style="' . $style_widget . '"' : '';

global $yith_wcwl; ?>
<a class="widget-wishlist<?php echo ent2ncr( $class ); ?>" href="<?php echo esc_url( $yith_wcwl->get_wishlist_url() ); ?>">
	<i class="fa fa-fw fa-heart"></i>
	<?php if ( $show_number == '1' ) { ?>
		<span class="wrapper-number"<?php echo ent2ncr( $style_css ); ?>><i class="icon-arrow" <?php echo ent2ncr( $style_arrow ); ?>></i><span class="wishlist_items_number"><?php echo yith_wcwl_count_products(); ?></span></span>
	<?php } ?>
</a>