<div class="woocommerce_gpf_intro_second">
	<h3><?php _e( 'Feed status', 'ultimatewoo-pro' ); ?></h3>
	<hr>
	<ul class="ul-disc">
		<li><?php _e( 'Google', 'ultimatewoo-pro' ); ?>
			<ul class="ul-disc"><li>{google_cache_status}</li></ul>
		</li>
		<li><?php _e( 'Google inventory', 'ultimatewoo-pro' ); ?>
			<ul class="ul-disc"><li>{google_inventory_cache_status}</li></ul>
		</li>
		<li><?php _e( 'Bing', 'ultimatewoo-pro' ); ?>
			<ul class="ul-disc"><li>{bing_cache_status}</li></ul>
		</li>
	</ul>
	<p style="vertical-align: center; text-align: center;">
		<a href="{settings_url}" class="button button-primary">Refresh stats &raquo;</a>&nbsp;&nbsp;
		<small><a href="{rebuild_url}" >Rebuild feed &raquo;</a></small>
	</p>
</div>
