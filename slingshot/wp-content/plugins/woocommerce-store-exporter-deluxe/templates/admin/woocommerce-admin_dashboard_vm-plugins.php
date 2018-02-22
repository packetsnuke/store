<?php if( !empty( $vl_plugins ) ) { ?>
<div class="table table_content">
	<?php if( $update_available ) { ?>
	<p class="message"><?php _e( 'A new version of a Visser Labs Plugin for WooCommerce is available for download.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
	<table class="woo_vm_version_table">
		<thead>
			<tr>
				<th class="align-left" style="text-align:left;"><?php _e( 'Plugin', 'woocommerce-exporter' ); ?></th>
				<th class="align-left" style="text-align:left;"><?php _e( 'Version', 'woocommerce-exporter' ); ?></th>
				<th class="align-left" style="text-align:left;"><?php _e( 'Status', 'woocommerce-exporter' ); ?></th>
			</tr>
		</thead>
		<tbody>
	<?php foreach( $vl_plugins as $plugin ) { ?>
		<?php if( $plugin['version'] ) { ?>
			<?php if( $plugin['installed'] ) { ?>
			<tr>
				<td><a href="<?php echo $plugin['url']; ?>#toc-news" target="_blank"><?php echo $plugin['name']; ?></a></td>
				<?php if( $plugin['version_existing'] ) { ?>
				<td class="version"><?php printf( __( '%s to %s', 'woocommerce-exporter' ), $plugin['version_existing'], '<span>' . $plugin['version'] . '</span>' ); ?></td>
					<?php if( $plugin['url'] && current_user_can( $user_capability ) ) { ?>
				<td class="status"><a href="<?php echo admin_url( 'update-core.php' ); ?>"><span class="red" title="<?php printf( __( 'Plugin update available for %s', 'woocommerce-exporter' ), $plugin['name'] ); ?>"><?php _e( 'Update', 'woocommerce-exporter' ); ?></span></a></td>
					<?php } else { ?>
				<td class="status"><span class="red" title="<?php printf( __( 'Plugin update available for %s', 'woocommerce-exporter' ), $plugin['name'] ); ?>"><?php _e( 'Update', 'woocommerce-exporter' ); ?></span></td>
					<?php } ?>
				<?php } elseif( $plugin['version_beta'] ) { ?>
				<td class="version"><?php echo $plugin['version_beta']; ?></td>
				<td class="status"><span class="yellow" title="<?php printf( __( '%s is from the future.', 'woocommerce-exporter' ), $plugin['name'] ); ?>"><?php _e( 'Beta', 'woocommerce-exporter' ); ?></span></td>
				<?php } else { ?>
				<td class="version"><?php echo $plugin['version']; ?></td>
				<td class="status"><span class="green" title="<?php printf( __( '%s is up to date.', 'woocommerce-exporter' ), $plugin['name'] ); ?>"><?php _e( 'OK', 'woocommerce-exporter' ); ?></span></td>
				<?php } ?>
			</tr>
			<?php } ?>
		<?php } ?>
	<?php } ?>
		</tbody>
	</table>
	<!-- .woo_vm_version_table -->
	<p class="link"><a href="http://www.visser.com.au/woocommerce/" target="_blank"><?php _e( 'Looking for more WooCommerce Plugins?', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- .table -->
<?php } else { ?>
<p><?php _e( 'Connection failed. Please check your network settings.', 'woocommerce-exporter' ); ?></p>
<?php } ?>