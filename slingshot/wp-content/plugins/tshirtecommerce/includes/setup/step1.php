<div class="e-steps">
	<div class="e-steps-label">
		<div class="e-step-1 e-step e-step-active">
			Design Tool <span>1</span>
		</div>
		<div class="e-step-2 e-step">
			Cliparts Store <span>2</span>
		</div>
		<div class="e-step-3 e-step">
			Import Products <span>3</span>
		</div>
		<div class="e-step-4 e-step">
			Finished! <span>4</span>
		</div>
	</div>
	
	<div class="progress">
	  <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%">
		<span class="sr-only">25% Complete (success)</span>
	  </div>
	</div>
</div>
<div class="page-content">
	<h2 class="title">Setting Design Tool</h2>
	<p>Thank you for choosing T-Shirt eCommerce. This quick setup wizard will help you configure the basic settings. It's completely optional and shouldn't take longer than five minutes.</p>
	
	<?php if( isset($error) && count($error) ) { ?>
		<div class="alert alert-danger" role="alert">
			<ol>
				<?php foreach($error as $line) { ?>
				<li><?php echo $line; ?></li>
				<?php } ?>
			</ol>
			<p><strong>Please check and fix all problem after reload page and continue settings.</strong></p>
		</div>
	<?php } ?>
	
	<hr />
	
	<form class="form-horizontal" method="post" action="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=store'); ?>">
	
		<div class="form-group">
			<label class="col-xs-5">Choose page will display design tool 
				<span class="pull-right" title="Page contents: [tshirtecommerce]" data-toggle="tooltip" data-placement="top"><i class="glyphicon glyphicon-question-sign"></i></span>
			</label>
			<div class="col-xs-7">
				
				<?php if( isset($pages) && count($pages) ) { ?>
				<select class="form-control input-sm" name="page_url">
				
					<?php 
					foreach($pages as $page) 
					{
						if ($settings_option['url'] == $page->ID)
							$selected = 'selected="selected"';
						else $selected = '';
					?>
					<option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo $page->post_title; ?></option>
					<?php } ?>
				
				</select>
				<?php } ?>
			</div>
			<div class="col-xs-ofsset-5 col-xs-7">
				<div class="center-block text-muted">Choose a page or <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" target="_blank">add new</a>.</div>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-xs-5">Layout of design tool</label>
			<div class="col-xs-7">
				
				<?php if( $layouts != false && is_object($layouts) && count($layouts) > 0 ) { ?>
				<select class="form-control input-sm" name="layout">
				
				<?php foreach($layouts as $layout) { ?>
				<option value="<?php echo $layout->name; ?>" <?php if($layout->default == 1) echo 'selected="selected"'; ?>>
					<?php echo $layout->title; ?>
				</option>
				<?php } ?>
				
				</select>
				<?php }else{ echo '<strong class="text-danger">Layouts not found!</strong>'; } ?>
				
			</div>
			<div class="col-xs-ofsset-5 col-xs-7">
				<div class="center-block text-muted">You can <a href="http://docs.tshirtecommerce.com/knowledgebase/customize-layout-with-color-background-of-design-tool/" target="_blank">add new or customize layout</a></div>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-xs-5">Your language</label>
			<div class="col-xs-7">
				
				<?php if( $languages != false && count($languages) > 0 ) { ?>
				<select class="form-control input-sm" name="language">
				
				<?php foreach($languages as $language) { ?>
				<option value="<?php echo $language->code; ?>" <?php if($language->default == 1) echo 'selected="selected"'; ?>>
					<?php echo $language->title; ?>
				</option>
				<?php } ?>
				
				</select>
				<?php }else{ echo '<strong class="text-danger">Languages not found!</strong>'; } ?>
				
			</div>
			<div class="col-xs-ofsset-5 col-xs-7">
				<div class="center-block text-muted">Choose your language display in page design tool or <a href="http://docs.tshirtecommerce.com/knowledgebase/add-new-language/" target="_blank">add new language</a>.</div>
			</div>
		</div>
		
		<div class="form-group">
			<label class="col-xs-5">Which currency will your store use?</label>
			<div class="col-xs-7">
				
				<?php if( $currencies != false && count($currencies) > 0 ) { $i = 1; ?>
				<select class="form-control input-sm currencies" name="setting[currency_id]">
				
				<?php foreach($currencies as $currency) { ?>
				<option value="<?php echo $i; ?>" <?php if($currency->currency_code == $currency_active) echo 'selected="selected"'; ?> symbol="<?php echo $currency->currency_symbol; ?>" code="<?php echo $currency->currency_code; ?>">
					<?php echo $currency->currency_name; ?> - <?php echo $currency->currency_symbol; ?>
				</option>
				<?php } ?>
				
				</select>
				<?php }else{ echo '<strong class="text-danger">Currency not found!</strong>'; } ?>
				
				<input name="setting[currency_symbol]" type="hidden" value="<?php echo get_woocommerce_currency_symbol(); ?>" id="shop-currency_symbol">
				<input name="setting[currency_code]" type="hidden" id="shop-currency_code" value="<?php echo $currency_active; ?>">
				<input name="setting[currency_postion]" type="hidden" value="<?php echo $wo_currency_pos; ?>">
				<input name="setting[price_thousand]" type="hidden" value="<?php echo $price_thousand; ?>">
				<input name="setting[price_decimal]" type="hidden" value="<?php echo $price_decimal; ?>">
				<input name="setting[price_number]" type="hidden" value="<?php echo $price_number; ?>">
			</div>
			<div class="col-xs-ofsset-5 col-xs-7">
				<div class="center-block text-muted">Please read <a href="http://docs.tshirtecommerce.com/knowledgebase/change-currency/" target="_blank">change display price</a></div>
			</div>
		</div>
		
		<hr />
		
		<p class="text-right">
			<a class="btn btn-default pull-left" href="http://docs.tshirtecommerce.com/kb/woocommerce-custom-product-designer/" target="_blank">Document Online</a>
			<?php if($settings_option['url'] != '') { ?>
			<a class="btn btn-default" href="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=store'); ?>">Skip this step</a>
			<?php } ?>
			<button type="submit" class="btn btn-success <?php if( isset($error) && count($error) ) echo 'disabled'; ?>" role="button">Save & Continue</button>
		</p>
	</form>
</div>