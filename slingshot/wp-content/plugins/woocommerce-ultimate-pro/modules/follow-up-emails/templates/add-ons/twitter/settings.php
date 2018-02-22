<h3><?php _e('Twitter Application Access Keys', 'ultimatewoo-pro'); ?></h3>

<?php if (isset($_GET['message'])): ?>
	<div id="message" class="updated"><p><?php echo wp_kses_post( urldecode( $_GET['message'] ) ); ?></p></div>
<?php endif; ?>

<a href="#" class="toggle-guide"><?php _e('Guide to getting your API keys', 'ultimatewoo-pro'); ?></a>

<div id="twitter-guide" style="display: none;">
	<blockquote>
		<p>
			<?php _e('To get your API Keys, create a <a href="https://apps.twitter.com/app/new">new Twitter App</a> and set the following values:', 'ultimatewoo-pro'); ?>
		</p>
		<ul>
			<li><strong>Name:</strong> <?php _e('Your app\'s name', 'ultimatewoo-pro'); ?></li>
			<li><strong>Description:</strong> <?php _e('Your application description, which will be shown in user-facing authorization screens', 'ultimatewoo-pro'); ?></li>
			<li><strong>Website:</strong> <?php _e('Your application\'s publicly accessible home page, where users can go to download, make use of, or find out more information about your application', 'ultimatewoo-pro'); ?></li>
			<li><strong>Callback URL:</strong> <?php printf( __('Set to <code>%s</code>', 'ultimatewoo-pro'), admin_url('admin-post.php?action=twitter-oauth') ); ?></li>
			<li><strong>Permissions:</strong> <?php _e('Set to <code>Read and Write</code>', 'ultimatewoo-pro'); ?></li>
		</ul>

		<p><?php _e('After creating your app, click on the Keys and Access Tokens tab to get your Consumer Key and Consumer Secret.', 'ultimatewoo-pro'); ?></p>
	</blockquote>
</div>


<table class="form-table">
	<tbody>
	<tr valign="top">
		<th><label for="twitter_checkout_fields"><?php _e('Checkout Page', 'ultimatewoo-pro'); ?></label></th>
		<td>
			<input type="checkbox" id="twitter_checkout_fields" name="twitter_checkout_fields" value="1" <?php checked( 1, $this->fue_twitter->settings['checkout_fields'] ); ?> />
			<span class="description"><?php _e('Collect twitter handle on the Checkout page', 'ultimatewoo-pro'); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th><label for="twitter_account_fields"><?php _e('Account Page', 'ultimatewoo-pro'); ?></label></th>
		<td>
			<input type="checkbox" id="twitter_account_fields" name="twitter_account_fields" value="1" <?php checked( 1, $this->fue_twitter->settings['account_fields'] ); ?> />
			<span class="description"><?php _e('Collect twitter handle on the My Account page', 'ultimatewoo-pro'); ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th><label for="twitter_consumer_key"><?php _e('Consumer Key', 'ultimatewoo-pro'); ?></label></th>
		<td>
			<input type="text" name="twitter_consumer_key" id="twitter_consumer_key" value="<?php echo esc_attr( $this->fue_twitter->settings['consumer_key'] ); ?>" size="50" />
		</td>
	</tr>
	<tr valign="top">
		<th><label for="twitter_consumer_secret"><?php _e('Consumer Secret', 'ultimatewoo-pro'); ?></label></th>
		<td>
			<input type="text" name="twitter_consumer_secret" id="twitter_consumer_secret" value="<?php echo esc_attr( $this->fue_twitter->settings['consumer_secret'] ); ?>" size="50" />
		</td>
	</tr>
	<?php
	if ( empty( $this->fue_twitter->settings['access_token'] ) && ( !empty( $this->fue_twitter->settings['consumer_key'] ) && !empty( $this->fue_twitter->settings['consumer_secret'] ) ) ):
		try {
			$connection     = new \Abraham\TwitterOAuth\TwitterOAuth( $this->fue_twitter->settings['consumer_key'], $this->fue_twitter->settings['consumer_secret'] );
			$request_token  = $connection->oauth('oauth/request_token');
			$auth_url       = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

			// store the token for 10 minutes
			set_transient( 'fue_twitter_request_token', $request_token, 600 );
	?>
	<tr valign="top">
		<th><label for="twitter_signin"><?php _e('Grant API Access', 'ultimatewoo-pro'); ?></label></th>
		<td>
			<a href="<?php echo $auth_url; ?>"><img src="<?php echo FUE_TEMPLATES_URL .'/images/sign-in-with-twitter.png'; ?>" alt="<?php _e('Sign In with Twitter', 'ultimatewoo-pro'); ?>" /></a>
		</td>
	</tr>
	<?php
		} catch ( Exception $e ) {
			$exception = json_decode( $e->getMessage() );
			$error = isset( $exception->errors ) ? array_pop( $exception->errors ) : (object) array( 'message' => 'Unknown error' );
			echo '<div class="error"><p>Twitter Error: '. $error->message .'</p></div>';
		}
	else:
	?>
		<tr valign="top">
			<th>&nbsp;</th>
			<td>
				<a href="admin-post.php?action=fue_reset_twitter" class="button"><?php _e('Reset Twitter Data', 'follow_up_email'); ?></a>
			</td>
		</tr>
	<?php
	endif;
		?>
	</tbody>
</table>

<script>
	jQuery(".toggle-guide").click(function(e) {
		e.preventDefault();

		jQuery("#twitter-guide").slideToggle();
	});
</script>
