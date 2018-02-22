<h3>
	<?php _e( 'Export Templates', 'woocommerce-exporter' ); ?>
	<a href="<?php echo esc_url( admin_url( add_query_arg( 'post_type', 'export_template', 'post-new.php' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'woocommerce-exporter' ); ?></a>
</h3>

<table class="widefat page fixed striped export-templates">
	<thead>

		<tr>
			<th class="manage-column"><?php _e( 'Name', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Status', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Excerpt', 'woocommerce-exporter' ); ?></th>
		</tr>

	</thead>
	<tbody id="the-list">

<?php if( !empty( $export_templates ) ) { ?>
	<?php foreach( $export_templates as $export_template ) {
// Fallback detection for empty get_the_excerpt() responses
$post_excerpt = get_the_excerpt( $export_template );
if( empty( $post_excerpt ) ) {
	$export_template_post = get_post( $export_template );
	$post_excerpt = ( isset( $export_template_post->post_excerpt ) ? $export_template_post->post_excerpt : '' );
	unset( $export_template_post );
}
?>

		<tr id="post-<?php echo $export_template; ?>">
			<td class="post-title column-title">
				<strong><a href="<?php echo get_edit_post_link( $export_template ); ?>" title="<?php _e( 'Edit export template', 'woocommerce-exporter' ); ?>"><?php echo woo_ce_format_post_title( get_the_title( $export_template ) ); ?></a></strong>
				<div class="row-actions">
					<a href="<?php echo get_edit_post_link( $export_template ); ?>" title="<?php _e( 'Edit this export template', 'woocommerce-exporter' ); ?>"><?php _e( 'Edit', 'woocommerce-exporter' ); ?></a> | 
					<span class="trash"><a href="<?php echo get_delete_post_link( $export_template ); ?>" class="submitdelete" title="<?php _e( 'Delete this export template', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete', 'woocommerce-exporter' ); ?></a></span>
				</div>
				<!-- .row-actions -->
			</td>
			<td><?php echo ucfirst( get_post_status( $export_template ) ); ?></td>
			<td><?php echo $post_excerpt; ?></td>
		</tr>

	<?php } ?>
<?php } else { ?>
		<tr>
			<td class="colspanchange" colspan="3"><?php _e( 'No export templates found.', 'woocommerce-exporter' ); ?></td>
		</tr>
<?php } ?>
	</tbody>

</table>
<!-- .export-templates -->

<?php if( !empty( $export_templates ) ) { ?>
<p style="text-align:right;"><?php printf( __( '%d items', 'woocommerce-exporter' ), count( $export_templates ) ); ?></p>
<?php } ?>