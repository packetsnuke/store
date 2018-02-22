<?php if (isset($_GET['created'])): ?>
	<div id="message" class="updated"><p><?php _e('Follow-up email created', 'ultimatewoo-pro'); ?></p></div>
<?php endif; ?>

<?php
if (isset($_GET['updated'])):
	$message = (empty($_GET['message'])) ? __('Follow-up email updated', 'ultimatewoo-pro') : esc_html($_GET['message']);
?>
	<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
	<div id="message" class="updated"><p><?php _e('Follow-up email deleted!', 'ultimatewoo-pro'); ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
	<div id="message" class="error"><p><?php echo $_GET['error']; ?></p></div>
<?php endif; ?>

<?php if (isset($_GET['manual_sent'])): ?>
	<div id="message" class="updated"><p><?php _e('Email(s) have been added to the queue', 'ultimatewoo-pro'); ?></p></div>
<?php endif; ?>

<?php do_action('fue_settings_notification'); ?>