<?php
// Adds custom Ticket columns to the Ticket fields list
function woo_ce_extend_ticket_fields( $fields = array() ) {

	// WooCommerce Events - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'wc_events' ) ) {
		$fields[] = array(
			'name' => 'barcode',
			'label' => __( 'Barcode', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Events', 'woocommerce-exporter' )
		);
	}

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$fields[] = array(
			'name' => 'ticket_code',
			'label' => __( 'Ticket Code', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_type_id',
			'label' => __( 'Ticket Type ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_event_id',
			'label' => __( 'Ticket Event ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_first_name',
			'label' => __( 'Ticket First Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_last_name',
			'label' => __( 'Ticket Last Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$tickera_fields = woo_ce_get_tickera_custom_fields();
		if( !empty( $tickera_fields ) ) {
			foreach( $tickera_fields as $tickera_field ) {
				$fields[] = array(
					'name' => sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) ),
					'label' => sprintf( __( 'Ticket: %s', 'woocommerce-exporter' ), $tickera_field['label'] ),
					'hover' => __( 'Tickera', 'woocommerce-exporter' )
				);
			}
		}
		unset( $tickera_fields );
	}

	return $fields;

}
add_filter( 'woo_ce_ticket_fields', 'woo_ce_extend_ticket_fields' );

function woo_ce_extend_ticket_item( $ticket ) {

	// WooCommerce Events - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'wc_events' ) ) {
		$ticket->user_id = get_post_meta( $ticket->ID, 'WooCommerceEventsCustomerID', true );
		$ticket->ticket_id = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );
		$ticket->status = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );
		$ticket->order_id = get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true );
		$ticket->product_id = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );
		$barcode_path = false;
		// WooCommerce Events - http://www.woocommerceevents.com/
		if( class_exists( 'WooCommerce_Events_Config' ) ) {
			$ticket_config = new WooCommerce_Events_Config();
			if( !empty( $ticket_config ) ) {
				$barcode_path = ( isset( $ticket_config->barcodePath ) ? sanitize_text_field( $ticket_config->barcodePath ) : false );
			}
			unset( $ticket_config );
		}
		$ticket->barcode = ( !empty( $barcode_path ) ? $barcode_path . $ticket->ticket_id . '.png' : $ticket->ticket_id );
	}

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$ticket->user_id = $ticket->post_author;
		$ticket->status = $ticket->post_status;
		$ticket->order_id = $ticket->post_parent;
		$ticket->ticket_code = get_post_meta( $ticket->ID, 'ticket_code', true );
		$ticket->ticket_type_id = get_post_meta( $ticket->ID, 'ticket_type_id', true );
		$ticket->ticket_event_id = get_post_meta( $ticket->ID, 'event_id', true );
		$ticket->ticket_first_name = get_post_meta( $ticket->ID, 'first_name', true );
		$ticket->ticket_last_name = get_post_meta( $ticket->ID, 'last_name', true );
		$tickera_fields = woo_ce_get_tickera_custom_fields();
		if( !empty( $tickera_fields ) ) {
			foreach( $tickera_fields as $tickera_field )
				$ticket->{sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) )} = get_post_meta( $ticket->ID, $tickera_field['name'], true );
		}
	}
	return $ticket;

}
add_filter( 'woo_ce_ticket_item', 'woo_ce_extend_ticket_item' );

function woo_ce_extend_ticket_post_type( $post_type = '' ) {

	if( woo_ce_detect_export_plugin( 'tickera' ) )
		$post_type = 'tc_tickets_instances';

	return $post_type;

}
if( woo_ce_detect_tickets() )
	add_filter( 'woo_ce_ticket_post_type', 'woo_ce_extend_ticket_post_type' );
?>