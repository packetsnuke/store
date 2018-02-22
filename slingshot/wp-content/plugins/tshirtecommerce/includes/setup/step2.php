<?php $token	= session_id(); ?>
<div class="e-steps">
	<div class="e-steps-label">
		<div class="e-step-1 e-step e-step-active">
			Design Tool <span>1</span>
		</div>
		<div class="e-step-2 e-step e-step-active">
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
	  <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
		<span class="sr-only">25% Complete (success)</span>
	  </div>
	</div>
</div>

<div class="page-content">
	<h2 class="title">Cliparts and Design Template</h2>
	<p>We give to you librarie clipart and design template. You can active <a href="http://9file.net/" target="_blank">T-Shirt eCommerce Store</a> to use design from our community.</p>
	
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
	
	<form class="form-horizontal" method="post" action="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=product'); ?>">
		
		<p>1. Create account on website <a href="http://store.9file.net/users/register" target="_blank">http://store.9file.net</a></p>
		
		<div class="form-group" style="margin-bottom: 0px;">
			<label class="col-xs-7">2. Enter your API</label>
			<div class="col-xs-7">
				<input type="text" class="form-control input-sm" id="store-api">
			</div>
		</div>
		<p><small>Video create account and get your API <a href="https://youtu.be/cPvRpXdVe0s?t=23s" target="_blank">https://youtu.be/cPvRpXdVe0s</a></small></p>
		
		<p>3. Click button "<strong>Active & Download Design</strong>"</p>
		
		<div class="download-store">
			<p class="help-block">Status: <strong class="text-danger text-status">Checking API</strong></p>
			<div class="progress">
			  <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
				<span class="sr-only">1% Complete (success)</span>
			  </div>
			</div>
			<p class="text-danger">Please don't navigate away while importing</p>
		</div>
		<hr />
		
		<p class="text-right">
			<a class="btn btn-default pull-left" href="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=designer'); ?>" role="button">Prev</a>
			<a class="btn btn-default" href="<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=product'); ?>" role="button">Skip this step</a>
			<button type="button" onclick="check_api(this)" class="btn btn-success <?php if( isset($error) && count($error) ) echo 'disabled'; ?>" role="button">Active & Download Design</button>
		</p>
	</form>
</div>
<script type="text/javascript">
function check_api(e)
{
	var api = jQuery('#store-api').val();
	if(api == '')
	{
		alert('Please enter your API');
		return false;
	}
	jQuery(e).attr('disabled', 'true');
	jQuery('.text-status').html('Checking API');
	jQuery('.download-store').show('slow');
	var width 	= 1;
    var id 		= setInterval(frame, 100);
    function frame() {
        if (width >= 100) {
            clearInterval(id);
        } else {
            width++; 
            jQuery('.download-store .progress-bar').css('width', width + '%'); 
        }
    }
	
	jQuery.ajax({
		url: "<?php echo admin_url('admin-ajax.php?action=store_check_api&api='); ?>"+api,
		type: "GET",
		success: function(text){
			clearInterval(id);
			jQuery('.download-store .progress-bar').css('width', '100%');
			if(text == '1')
			{
				var url = '<?php echo admin_url('admin-ajax.php?action=tshirt_store_add_cronjobs'); ?>';
				jQuery.ajax({
					url: url,
					type: "GET",
					complete: function(data) {}
				});
				import_data(0);
			}
			else
			{
				jQuery('.text-status').html('Your API invalid, please check and try again.');
				jQuery(e).removeAttr('disabled');
			}
		},
		error: function(xhr) {
			clearInterval(id);
			jQuery('.text-status').html('Your API invalid, please check and try again.');
			jQuery(e).removeAttr('disabled');
		}
	});
}
function import_data(status)
{
	status = status + 1;
	var msg = '';
	switch(status){
		case 1:
			msg = 'Importing types of design idea';
			break;
		case 2:
			msg = 'Importing categories of cliparts';
			break;
		case 3:
			msg = 'Importing cliparts';
			break;
		case 4:
			msg = 'Importing categories of design idea';
			break;
		case 5:
			msg = 'Importing design idea';
			break;
		default:
			msg = 'Download data success!';
			break;
	}
	
	jQuery('.text-status').html(msg);
	if(status > 6)
	{
		return false;
	}
	var width 	= 1;
    var id 		= setInterval(frame, 100);
    function frame() {
        if (width >= 100) {
            clearInterval(id);
        } else {
            width++; 
            jQuery('.download-store .progress-bar').css('width', width + '%'); 
        }
    }
	jQuery.ajax({
		url: "<?php echo home_url('tshirtecommerce/ajax.php?type=addon&task=store_admin&token='.$token, false); ?>"+'&status='+status,
		type: "GET",
		success: function(text){
			clearInterval(id);
			jQuery('.store-mask .progress-bar').css('width', '100%');
			var data = eval ("(" + text + ")");
			if(typeof data != 'undefined' && typeof data.error != 'undefined' && data.error == 0)
			{
				if(data.step == 0)
				{
					jQuery('.text-status').html('Download data success!');
					window.location.href = '<?php echo admin_url('index.php?page=tshirtecommerce-setting&step=product'); ?>';
				}
				else
				{
					import_data(status);
				}
			}
			else
			{
				jQuery('.text-status').html('Download data error!');
			}
		},
		error: function(xhr) {
			clearInterval(id);
			jQuery('.text-status').html('Download data error!');
		}
	});
}
</script>