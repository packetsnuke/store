<style>
.tshirt_introduction{position: fixed;bottom: 6px;right: 6px;z-index: 10000;transition: all ease .6s;}
.tshirt_introduction.active{background-color: #fff;width:360px;box-shadow: 0px 0px 8px 1px #ccc;border-radius: 4px;top: 60px;bottom: 30px;}
.tshirt_introduction.active.e_full{width:800px;max-width:90%;}
.tshirt_introduction a.e-head {box-shadow:0 0 10px 0 #72777c;transition:all ease .3s;float: left;overflow: hidden;border-radius: 50%;display:inline-block;width: 46px;height: 46px;}
.e-hide{display:none;}
.tshirt_introduction.active a.e-head{display:none}
.tshirt_introduction.active .e-hide{display:block}
.e-head-title {background-color: #F4F4F4;border-radius: 4px 4px 0 0;font-size: 14px;padding: 12px 10px;font-weight: 600;color: #333;}
.e-head-title span.dashicons {position: absolute;right: 7px;top: 9px;cursor: pointer;color: #666;}
.tshirt_introduction_content {width: 100%;height: calc(100% - 44px);}
.tshirt_introduction_content iframe{width: 100%;height:100%;}
span.e-full.dashicons {right: 34px;}
span.e-full.dashicons.active{}
</style>
<div class="tshirt_introduction">
	<a href="javascript:void(0);" title="T-Shirt eCommerce questions and answers" class="e-head" onclick="tshirt_help(this, 0)">
		<img src="<?php echo plugins_url('tshirtecommerce/assets/images/icon-question.png?version=4.2.1'); ?>" alt="">
	</a>
	<div class="e-head-title e-hide">
		<span class="e-head-text">Find your questions?</span>
		<span class="e-full dashicons dashicons-editor-expand" onclick="tshirt_help_full(this)"></span>
		<span class="dashicons dashicons-no-alt" onclick="tshirt_help(this, 1)" title="close"></span>
	</div>
	<div class="tshirt_introduction_content e-hide">
		<iframe id="load_e_help" src=""></iframe>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('#load_e_help').attr('src', 'https://tshirtecommerce.com/help/wp/index.php');
	<?php 
	if(!isset($_COOKIE['first_e_help'])) {
		setcookie('first_e_help', 1, time() + (86400 * 300), "/");
		echo "jQuery('a.e-head').trigger('click');";
	}
	?>
});
function tshirt_help(e, type){
	if(type == 0)
	{
		jQuery(e).parents('.tshirt_introduction').addClass('active');
	}
	else
	{
		jQuery(e).parents('.tshirt_introduction').removeClass('active');
	}
}
function tshirt_help_full(e)
{
	if(jQuery(e).hasClass('active'))
	{
		jQuery(e).removeClass('active');
		jQuery(e).removeClass('dashicons-editor-contract');
		jQuery(e).addClass('dashicons-editor-expand');
		jQuery('.tshirt_introduction').removeClass('e_full');
	}
	else
	{
		jQuery(e).addClass('active')
		jQuery(e).addClass('dashicons-editor-contract');
		jQuery(e).removeClass('dashicons-editor-expand');
		jQuery('.tshirt_introduction').addClass('e_full');
	}
}
</script>