<?php if (isset($_GET['settings_updated'])): ?>
	<div id="message" class="updated"><p><?php _e('Settings updated', 'ultimatewoo-pro'); ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['imported'])): ?>
	<div id="message" class="updated"><p><?php _e('Data imported successfully', 'ultimatewoo-pro'); ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['subscribers_added'])): ?>
	<div id="message" class="updated"><p><?php printf( __('%d subscribers added', 'ultimatewoo-pro'), absint($_GET['subscribers_added']) ); ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
	<div id="message" class="error"><p><?php echo wp_kses_post( $_GET['error'] ); ?></p></div>
<?php endif; ?>