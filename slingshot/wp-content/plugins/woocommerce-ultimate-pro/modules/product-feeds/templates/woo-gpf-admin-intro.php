<div class="woocommerce_gpf_settings">
	<h3><?php _e( 'Settings for your store', 'ultimatewoo-pro' ); ?></h3>
	<div class="woocommerce_gpf_intro_first">
		<p><?php _e( 'This page allows you to control what data is added to your product feeds.', 'ultimatewoo-pro' ); ?></p>
		<p><?php _e( 'Choose the fields you want to include here, and also set store-wide defaults. You can also set defaults against categories, or provide information on each product page. ', 'ultimatewoo-pro' ); ?></p>
		<h4><?php _e( 'Notes about Google', 'ultimatewoo-pro' ); ?></h4>
		<p><?php _e( "Depending on what you sell, and where you are selling it to Google apply different rules as to which information you should supply. You can find Google's list of what information is required on ", 'ultimatewoo-pro' ); ?><a href="http://www.google.com/support/merchants/bin/answer.py?answer=188494" rel="nofollow"><?php _e( 'this page', 'ultimatewoo-pro' ); ?></a></p>
		<h4><?php _e( 'Getting your feed', 'ultimatewoo-pro' ); ?></h4>

		<p><?php _e( 'Your feed is available here: ', 'ultimatewoo-pro' ); ?><br>
			<ul>
				<li><img src="<?php echo ULTIMATEWOO_MODULES_URL . '/product-feeds/images/google.png'; ?>" alt="Google Merchant Centre"> <a href="{google_url}" target="_blank">{google_url}</a>, or <a href="{inventory_url}" target="_blank">{inventory_text}</a></li>
				<li><img src="<?php echo ULTIMATEWOO_MODULES_URL . '/product-feeds/images/bing.png'; ?>" alt="Bing"> <a href="{bing_url}" target="_blank">{bing_url}</a></li>
			</ul>
		</p>
	</div>
	{cache_status}
	<hr>
	<h3><?php _e( 'Feed fields to enable', 'ultimatewoo-pro' ); ?></h3>

	<p><?php _e( 'Choose which fields you want in your feed for each product, and set store-wide defaults below where necessary: ', 'ultimatewoo-pro' ); ?><br/></p>

	<table class="form-table">
