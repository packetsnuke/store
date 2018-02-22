<?php
$title          = $link_face = $link_twitter = $link_google = $link_instagram = $link_pinterest = $link_youtube = '';
$title          = $instance['title'];
$link_face      = $instance['link_face'];
$link_twitter   = $instance['link_twitter'];
$link_google    = $instance['link_google'];
$link_instagram = $instance['link_instagram'];
$link_pinterest = $instance['link_pinterest'];
$link_youtube   = $instance['link_youtube'];

if ( $title ) {
	echo ent2ncr($args['before_title'] . esc_attr( $title ) .  $args['after_title']);
}
?>
<div class="thim-social">
	<ul>
		<?php
		if ( $link_face != '' ) {
			echo '<li><a class="facebook" href="' . esc_url( $link_face ) . '" target="' . $instance['link_target'] . '"><i class="fa fa-facebook"></i></a></li>';
		}
		if ( $link_twitter != '' ) {
			echo '<li><a class="twitter" href="' . esc_url( $link_twitter ) . '" target="' . $instance['link_target'] . '"><i class="fa fa-twitter"></i></a></li>';
		}
		if ( $link_google != '' ) {
			echo '<li><a class="google_plus" href="' . esc_url( $link_google ) . '"  target="' . $instance['link_target'] . '"><i class="fa fa-google-plus"></i></a></li>';
		}
		if ( $link_instagram != '' ) {
			echo '<li><a class="instagram" href="' . esc_url( $link_instagram ) . '"  target="' . $instance['link_target'] . '"><i class="fa fa-instagram"></i></a></li>';
		}

		if ( $link_pinterest != '' ) {
			echo '<li><a class="pinterest" href="' . esc_url( $link_pinterest ) . '"  target="' . $instance['link_target'] . '"><i class="fa fa-pinterest"></i></a></li>';
		}

		if ( $link_youtube != '' ) {
			echo '<li><a class="youtube" href="' . esc_url( $link_youtube ) . '"  target="' . $instance['link_target'] . '"><i class="fa fa-youtube"></i></a></li>';
		}
		?>
	</ul>
</div>