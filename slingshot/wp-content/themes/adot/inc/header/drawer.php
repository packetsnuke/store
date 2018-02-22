<?php
/**
 * Created by PhpStorm.
 * User: Anh Tuan
 * Date: 7/23/14
 * Time: 3:13 PM
 */
global $theme_options_data;

?>

<div id="rt-drawer" class="<?php if ( isset( $theme_options_data['thim_drawer_style'] ) ) {
	echo esc_attr( $theme_options_data["thim_drawer_style"]);
} ?>">
	<div class="container">
		<div class="row">
			<?php
			if ( isset( $theme_options_data['thim_drawer_open'] ) && $theme_options_data['thim_drawer_open'] == 1 ) {
				echo '<div id="collapseDrawer" class="panel-collapse collapse in" style="height: auto;">';
			} else {
				echo '<div id="collapseDrawer" class="panel-collapse collapse" style="height: 0;">';
			}
			?>
			<?php dynamic_sidebar( 'drawer_top' ); ?>
		</div>
	</div>
</div>
<div class="drawer_link">
	<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseDrawer">
		<i class="fa fa-angle-up"></i>
	</a>
</div>
</div>
