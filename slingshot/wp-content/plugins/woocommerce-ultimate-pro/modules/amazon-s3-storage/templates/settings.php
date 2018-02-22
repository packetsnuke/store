<?php
if ( isset( $_POST['save'] ) ) {
	echo '<div class="updated fade"><p>' . esc_html__( 'Your settings have been saved.', 'ultimatewoo-pro' ) . '</p></div>';
}
?>
<form method="post" id="mainform" action="">
	<h2><?php _e( 'Amazon S3 Storage', 'ultimatewoo-pro' ); ?></h2>
	<h3><?php _e( 'Security Credentials', 'ultimatewoo-pro' ); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Access Key ID', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp"><input name="woo_amazon_access_key" id="woo_amazon_access_key" type="text" style="min-width:300px;" value="<?php echo esc_attr( $admin_options['amazon_access_key'] ); ?>"><span class="description"><?php _e( 'Your Amazon Web Services access key id.', 'ultimatewoo-pro' ); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'Secret Access Key', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp"><input name="woo_amazon_access_secret" id="woo_amazon_access_secret" type="text" style="min-width:300px;" value="<?php echo esc_attr( $admin_options['amazon_access_secret'] ); ?>"><span class="description"><?php _e( 'Your Amazon Web Services secret access key.', 'ultimatewoo-pro' ); ?></span></td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'HTTPS File Serving', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<input id="woo_amazon_https_downloads" type="checkbox" <?php checked( $admin_options['amazon_https_downloads'], '1' ); ?> name="woo_amazon_https_downloads" />
				<span class="description"><?php _e( 'Serve downloads via a https url.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc"><?php _e( 'URL Valid Period', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<input name="woo_amazon_url_period" id="woo_amazon_url_period" type="text" style="min-width:100px;" value="<?php echo esc_attr( $admin_options['amazon_url_period'] ); ?>">
				<span class="description"><?php _e( 'Time in minutes the URL are valid for downloading, default is 1 minute.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
	</table>
	<p class="submit"><input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', 'ultimatewoo-pro' ); ?>" /></p>
</form>
