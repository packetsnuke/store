<?php
if ( ! is_user_logged_in() ) {
	echo '<div class="thim-link-login"><a href="' . get_site_url() . '/wp-login.php"><span data-hover="login"><i class="fa fa-user"></i> </span></a></div>';
}else{
	echo '<div><a href="' .wp_logout_url().'"><span data-hover="login"><i class="fa fa-user"></i> </span></a></div>';
}
