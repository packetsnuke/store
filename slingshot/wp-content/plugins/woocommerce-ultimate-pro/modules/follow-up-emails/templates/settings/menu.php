<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
	<a href="admin.php?page=followup-emails-settings&amp;tab=system" class="nav-tab <?php if ($tab == 'system') echo 'nav-tab-active'; ?>"><?php _e(' General Settings', 'ultimatewoo-pro'); ?></a>
	<a href="admin.php?page=followup-emails-settings&amp;tab=auth" class="nav-tab <?php if ($tab == 'auth') echo 'nav-tab-active'; ?>"><?php _e(' DKIM & SPF', 'ultimatewoo-pro'); ?></a>
	<a href="admin.php?page=followup-emails-settings&amp;tab=subscribers" class="nav-tab <?php if ($tab == 'subscribers') echo 'nav-tab-active'; ?>"><?php _e(' Subscribers', 'ultimatewoo-pro'); ?></a>
	<a href="admin.php?page=followup-emails-settings&amp;tab=tools" class="nav-tab <?php if ($tab == 'tools') echo 'nav-tab-active'; ?>"><?php _e(' Tools', 'ultimatewoo-pro'); ?></a>
	<a href="admin.php?page=followup-emails-settings&amp;tab=integration" class="nav-tab <?php if ($tab == 'integration') echo 'nav-tab-active'; ?>"><?php _e(' Optional Extras', 'ultimatewoo-pro'); ?></a>
	<?php do_action( 'fue_settings_tabs' ); ?>
</h2>