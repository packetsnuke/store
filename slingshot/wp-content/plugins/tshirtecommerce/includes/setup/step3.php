<div class="e-steps">
	<div class="e-steps-label">
		<div class="e-step-1 e-step e-step-active">
			Design Tool <span>1</span>
		</div>
		<div class="e-step-2 e-step e-step-active">
			Cliparts Store <span>2</span>
		</div>
		<div class="e-step-3 e-step e-step-active">
			Import Products <span>3</span>
		</div>
		<div class="e-step-4 e-step">
			Finished! <span>4</span>
		</div>
	</div>
	
	<div class="progress">
	  <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%">
		<span class="sr-only">75% Complete (success)</span>
	  </div>
	</div>
</div>

<div class="page-content">
	<h2 class="title">Import Product Demo</h2>
	<p>Please check button "Import Products" to automatic import products demo.</p>
	
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
	
	<div class="download-store">
		<p class="help-block">Status: <strong class="text-danger text-status">Downloading data</strong></p>
		<div class="progress">
		  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
			<span class="sr-only">1% Complete (success)</span>
		  </div>
		</div>
		<p class="text-danger">Please don't navigate away while importing</p>
	</div>
	<hr />
	
	<p class="text-right">
		<a class="btn btn-default pull-left" href="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=store'); ?>" role="button">Prev</a>
		<a class="btn btn-default" href="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=complete'); ?>" role="button">Skip this step</a>
		<button type="button" onclick="import_products(this)" class="btn btn-success <?php if( isset($error) && count($error) ) echo 'disabled'; ?>">Import Products</button>
	</p>
</div>
<script type="text/javascript">
function import_products(e)
{
	jQuery(e).attr('disabled', 'true');
	jQuery('.download-store').show('slow');
	var width 	= 1;
    var id 		= setInterval(frame, 300);
    function frame() {
        if (width >= 100) {
            clearInterval(id);
        } else {
            width++; 
            jQuery('.download-store .progress-bar').css('width', width + '%'); 
        }
    }
	
	jQuery.ajax({
		url: "<?php echo admin_url('admin-ajax.php?action=download_products'); ?>",
		type: "GET",
		success: function(text){
			var data = eval ("(" + text + ")");
			if(typeof data.error && data.error == 1)
			{
				clearInterval(id);
				jQuery('.text-status').html(data.msg);
				jQuery(e).removeAttr('disabled');
			}
			else
			{
				jQuery('.text-status').html('Importing products');
				step_import(1);
			}
		},
		error: function(xhr) {
			clearInterval(id);
			jQuery('.text-status').html('Import data error!');
			jQuery(e).removeAttr('disabled');
		}
	});
}
function step_import(step)
{
	jQuery.ajax({
			url: "<?php echo admin_url('admin-ajax.php?action=import_products&step='); ?>"+step,
			type: "GET",
			success: function(text){
				if(text == '1')
				{
					step = step + 1;
					step_import(step);
				}
				else
				{
					jQuery('.store-mask .progress-bar').css('width', '100%');
					window.location.href = '<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=complete'); ?>';
				}
				
			},
			error: function(xhr) {
				clearInterval(id);
				jQuery('.text-status').html('Import data error!');
				jQuery(e).removeAttr('disabled');
			}
		});
}
</script>