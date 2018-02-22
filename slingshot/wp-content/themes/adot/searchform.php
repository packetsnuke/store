<?php
/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/27/2015
 * Time: 2:39 PM
 */
?>
<div id="search-form-wrapper" class="search-form">
	<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" class="form-control ob-search-input" autocomplete="off">
		<button type="submit" class="search-submit" role="button"><i class="fa fa-search"></i></button>
	</form>
	<div class="clear"></div>
</div>