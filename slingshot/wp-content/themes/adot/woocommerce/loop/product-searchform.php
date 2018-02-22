<?php
/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/16/2015
 * Time: 3:14 PM
 */
?>
<div id="header-search-form-input" class="search-form-product default">
	<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" class="form-control ob-search-input" autocomplete="off">
		<button type="submit" class="button-search-product" role="button"><i></i></button>
	</form>
	<div class="clear"></div>
</div>