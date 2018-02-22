<?php
/**
 * Settings of plugin
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1>Welcome to Custom Product Designer!</h1>
	<p class="alignright">
		<a class="button button-lg button-primary" href="http://docs.tshirtecommerce.com/wordpress" target="_blank">Read Document</a>
	</p>
	<br />
	<h2 class="nav-tab-wrapper nav-tshirt-tabs">
	 	<a href="javascript:void(0);" data-tab="#tshirt_general_setting" class="nav-tab nav-tab-active">General</a>
	 	<a href="javascript:void(0);" data-tab="#tshirt_woocommerce_setting" class="nav-tab">Product and Cart page</a>
	 	<a href="<?php echo admin_url('admin.php?page=online_designer&task=settings'); ?>" class="nav-tab">Advanced</a>
	 	<a href="<?php echo admin_url('index.php?page=tshirtecommerce-setting'); ?>" style="margin-left: 13px;line-height: 30px;font-size: 15px;" title="Click to setup plugin">Quick setup</a>
	</h2>

	<form name="form1" method="post" action="">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

		<div id="tshirt_general_setting" class="tshirt-tab-content group p">
			<h2>General options</h2>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Designer Page</th>
						<td class="forminp">
							<select name="<?php echo $data_field_name_url; ?>">
								
							<?php if ( count($pages) > 0){ ?>
								<?php foreach($pages as $page) { 
									if ($opt_val['url'] == $page->ID) $selected = 'selected="selected"';
									else $selected = '';
								?>
									<option <?php echo $selected; ?> value="<?php echo $page->ID; ?>"><?php echo $page->post_title; ?></option>
								<?php } ?>
							
							<?php } ?>
								
							</select>
							<p class="description">This page will show design tool. If you want show design with more page, you can use shorcode add to any page. <a href="http://docs.tshirtecommerce.com/wordpress/article/use-shortcode/" target="_blank">Read More</a></p>									
						</td>
					</tr>
					<?php do_action( 'tshirtecommerce_setting', $opt_val ); ?>
					<?php do_action( 'tshirtecommerce_setting_general', $opt_val ); ?>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</div>

		<div id="tshirt_woocommerce_setting" class="tshirt-tab-content group p" style="display: none;">
			<h2>Settings Products, Checkout page of Woocommerce</h2>
			<p>If your theme changed or removed some hooks of Woocommerce this settings can't works good. If you change setting but your site not working, please change theme and check again.</p>
			<table class="form-table">
				<tbody>
					<?php do_action( 'tshirtecommerce_setting_products', $opt_val ); ?>
					<tr>
						<th scope="row" colspan="2">
							<hr />
						</th>
					</tr>

					<tr>
						<th scope="row" colspan="2">
							<h3 style="margin: 0;">Product detail page</h3>
						</th>
					</tr>
					<?php do_action( 'tshirtecommerce_setting_product', $opt_val ); ?>
					<tr>
						<th scope="row">
							<strong>Labels design button</strong>
						</th>
						<td>
							<label>Start Design <small>(This button show with product blank)</small></label>
							<p><input type="text" name="<?php echo $data_field_name_start; ?>" value="<?php echo $opt_val['btn-start']; ?>" /></p>

							<br />
							<label>Customize Design <small>(This button show with product design template)</small></label>
							<p><input type="text" name="<?php echo $data_field_name_custom; ?>" value="<?php echo $opt_val['btn-custom']; ?>" /></p>

							<br />
							<label>Class extra button <small>(This option allow change style of button start design via CSS)</small></label>
							<p><input type="text" name="<?php echo $data_field_extra_class; ?>" value="<?php echo $opt_val['btn-extra_class']; ?>" /></p>
						</td>
					</tr>
					<tr>
						<th scope="row" colspan="2">
							<hr />
						</th>
					</tr>

					<tr>
						<th scope="row" colspan="2">
							<h3 style="margin: 0;">Cart Page</h3>
						</th>
					</tr>
					<tr>
						<th scope="row">Redirect to page cart</th>
						<td>
							<?php
							$redirect_cart = '';
							if(isset($opt_val['redirect_cart']) )
							{
								$redirect_cart = 'checked="checked"';
							}
							?>
							<input type="checkbox" value="1" <?php echo $redirect_cart; ?> name="designer[redirect_cart]"> Automatic redirect to page cart after client click "Buy Now".
						</td>
					</tr>
					<tr>
						<th scope="row">Download file design</th>
						<td>
							<?php
							$allow_download = '';
							if(isset($opt_val['allow_download']) )
							{
								$allow_download = 'checked="checked"';
							}
							?>
							<input type="checkbox" value="1" <?php echo $allow_download; ?> name="designer[allow_download]"> Allow client download file output in page order history.
						</td>
					</tr>
					<?php do_action( 'tshirtecommerce_setting_cart', $opt_val ); ?>
					<tr>
						<th scope="row" colspan="2">
							<hr />
						</th>
					</tr>
				</tbody>
			</table>

			<?php submit_button(); ?>
		</div>
	</form>
</div>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.nav-tshirt-tabs a.nav-tab').click(function(){
			jQuery('.nav-tshirt-tabs a.nav-tab').removeClass('nav-tab-active');
			jQuery(this).addClass('nav-tab-active');
			var id = jQuery(this).data('tab');
			jQuery('.tshirt-tab-content').hide();
			jQuery(id).show();
		});
	});
</script>