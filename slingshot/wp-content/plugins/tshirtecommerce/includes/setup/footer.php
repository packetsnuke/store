<center><a class="text-muted" href="<?php echo network_site_url('wp-admin'); ?>">Return to your dashboard</a></center>

<script type='text/javascript' src='<?php echo str_replace('http:', '', network_site_url('tshirtecommerce/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>'></script>
<script type='text/javascript'>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
  
  jQuery('.currencies').change(function(e){
	var currency_symbol = jQuery('option:selected', this).attr('symbol');
	var currency_code = jQuery('option:selected', this).attr('code');
	jQuery('#shop-currency_symbol').val(currency_symbol);
	jQuery('#shop-currency_code').val(currency_code);
});
})
</script>
</body>
</html>