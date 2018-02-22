(function ( $ ) {
	"use strict";

	$( document ).ready( function () {

		var _tm_check_for_changes = 0;
		var tm_epo_sortable_options = {
			items: '.woocommerce_tm_epo',
			cursor: 'move',
			axis: 'y',
			handle: 'h3 .move',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start: function ( event, ui ) {
				ui.item.css( 'background-color', '#f6f6f6' );
			},
			stop: function ( event, ui ) {
				ui.item.removeAttr( 'style' );
				tm_epo_row_indexes();
			}
		};

		/**
		 * Update boxes
		 */
		function tm_epo_update_boxes() {
			jQuery( '.woocommerce_tm_epo' ).each( function ( index, element ) {
				var _root = jQuery( element );
				var _current_att = _root.find( '.tmcp_attribute' ).val();
				_root.find( 'select.tmcp_att' ).hide();
				_root.find( "select.tmcp_att[data-tm-attr='" + _current_att + "']" ).show();

				var attribute = _root.find( 'select.tmcp_att' ).val();
				var variation = _root.find( 'select.tmcp-variation' ).val();
				var show_field = "input.tmcp-price-input-variation-" + variation + "[data-price-input-attribute='" + attribute + "'],select.tmcp-price-input-variation-" + variation + "[data-price-input-attribute='" + attribute + "']";
				_root.find( "input.tmcp-price-input" ).hide();
				_root.find( "select.tmcp-price-input-type" ).hide();
				_root.find( show_field ).show();
				tm_epo_show_price_field( _root.find( 'select.tmcp-variation' ), "variation" );
			} );
			// trigger woocommerce hide classes function
			jQuery( 'input#_downloadable' ).trigger( 'change' );
		}

		tm_epo_update_boxes();

		function tm_epo_show_price_field( obj, what ) {
			var val = obj.val();
			var _root = obj.closest( '.woocommerce_tm_epo' );
			_root.find( "input.tmcp-price-input" ).hide();
			_root.find( "select.tmcp-price-input-type" ).hide();
			var loop = _root.find( 'input.tmcp_loop' ).val();
			var attribute = _root.find( 'select.tmcp_att' ).val();
			var variation = _root.find( 'select.tmcp-variation' ).val();
			var show_field = "";
			switch ( what ) {
				case "variation":
					variation = val;
					show_field = "input.tmcp-price-input-variation-" + val + "[data-price-input-attribute='" + attribute + "'],select.tmcp-price-input-variation-" + val + "[data-price-input-attribute='" + attribute + "']";
					break;
				case "attribute":
					attribute = val;
					show_field = "input.tmcp-price-input-variation-" + variation + "[data-price-input-attribute='" + val + "'],select.tmcp-price-input-variation-" + variation + "[data-price-input-attribute='" + val + "']";
					break;
			}
			if ( _root.find( show_field ).length <= 0 ) {
				_root.find( ".tmcp_pricing" ).append( '<input type="text" size="5" name="tmcp_regular_price[' + loop + '][' + attribute + '][' + variation + ']" value="" class="wc_input_price tmcp-price-input tmcp-price-input-variation-' + variation + '" data-price-input-attribute="' + attribute + '" />' );
				_root.find( ".tmcp_pricing" ).append( '<select class="tmcp-price-input-type tmcp-price-input tmcp-price-input-variation-' + variation + '" data-price-input-attribute="' + attribute + '" name="tmcp_regular_price_type[' + loop + '][' + attribute + '][' + variation + ']"><option value="">' + tm_epo_admin_meta_boxes.i18n_fixed_type + '</option><option value="percent">' + tm_epo_admin_meta_boxes.i18n_percent_type + '</option></select>' );
			}
			_root.find( show_field ).show();
		}

		$( '#tm_extra_product_options' ).on( 'change', 'select.tmcp-variation', function ( e ) {
			tm_epo_show_price_field( $( this ), "variation" );
		} );
		$( '#tm_extra_product_options' ).on( 'change', 'select.tmcp_att', function ( e ) {
			tm_epo_show_price_field( $( this ), "attribute" );
		} );

		/**
		 * Set Ordering
		 */
		$( '#tm_extra_product_options' ).on( 'woocommerce_tm_epo_added', function () {
			$( '.woocommerce_tm_epos' ).sortable( tm_epo_sortable_options );
		} );
		$( '#tm_extra_product_options' ).on( 'woocommerce_tm_epos_loaded', function () {
			$( '.woocommerce_tm_epos' ).sortable( tm_epo_sortable_options );
		} );

		function tm_epo_row_indexes() {
			$( '.woocommerce_tm_epos .woocommerce_tm_epo' ).each( function ( index, el ) {
				$( '.tm_epo_menu_order', el ).val( parseInt( $( el ).index( '.woocommerce_tm_epos .woocommerce_tm_epo' ), 10 ) );
			} );
		}

		$( '.woocommerce_tm_epos' ).sortable( tm_epo_sortable_options );

		/*
		 * Add custom check handlers
		 */
		function tm_epo_check() {

			if ( _tm_check_for_changes == 1 ) {
				$( '#tm_extra_product_options' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff url(' + tm_epo_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
						opacity: 0.6
					}
				} );
				var data = {
					action: 'woocommerce_tm_load_epos',
					post_id: tm_epo_admin_meta_boxes.post_id,
					security: tm_epo_admin_meta_boxes.load_tm_epo_nonce
				};
				$.post( tm_epo_admin_meta_boxes.ajax_url, data, function ( response ) {
					$( '.tm_mode_local' ).html( response );
					$( '#tm_extra_product_options' ).unblock();
					$( '#tm_extra_product_options' ).trigger( 'woocommerce_tm_epos_loaded' );
					_tm_check_for_changes = 0;
					tm_epo_update_boxes();
				} );
			}

		}


		$( '#variable_product_options' ).on( 'click', 'button.remove_variation', function ( e ) {
			_tm_check_for_changes = 1;
		} );
		$( '#variable_product_options' ).on( 'woocommerce_variations_added', function () {
			_tm_check_for_changes = 1;
		} );
		$( '.product_attributes' ).on( 'click', 'button.add_new_attribute', function () {
			_tm_check_for_changes = 1;
		} );
		$( '.save_attributes' ).on( 'click', function () {
			_tm_check_for_changes = 1;
		} );
		$( '.tm_epo_class a' ).on( 'click', function () {
			tm_epo_check();
		} );

		/*
		 * Add extra option
		 */
		$( '#tm_extra_product_options' ).on( 'click', 'button.tm_add_epo', function () {
			var attribute_type = $( 'select.tmcp_attr_list' ).val(),
				thisrow;
			try {
				thisrow = $( ".woocommerce_tm_epos .woocommerce_tm_epo[data-epo-attr='" + attribute_type + "']" );
			} catch ( err ) {
				thisrow = $();
			}
			if ( thisrow.length > 0 ) {
				thisrow.find( '.woocommerce_tmcp_attributes' ).show();
				return;
			}
			$( '.tm_mode_local' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff url(' + tm_epo_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			} );
			var loop = $( '.woocommerce_tm_epo' ).length;
			var data = {
				action: 'woocommerce_tm_add_epo',
				post_id: tm_epo_admin_meta_boxes.post_id,
				att_id: attribute_type,
				loop: loop,
				security: tm_epo_admin_meta_boxes.add_tm_epo_nonce
			};
			$.post( tm_epo_admin_meta_boxes.ajax_url, data, function ( response ) {
				if ( response == 'max' ) {
					alert( tm_epo_admin_meta_boxes.i18n_max_tmcp );
					$( '.tm_mode_local' ).unblock();
				} else {
					if ( response == 0 ) {

					} else {
						$( '.woocommerce_tm_epos' ).append( response );

						$( '#tm_extra_product_options' ).trigger( 'woocommerce_tm_epo_added' );
						tm_epo_update_boxes();
					}
				}
			} )
				.always( function ( response ) {
					$( '.tm_mode_local' ).unblock();
				} );
			return false;
		} );

		/*
		 * Remove extra option
		 */
		$( '#tm_extra_product_options' ).on( 'click', '.remove_tm_epo', function ( e ) {
			e.preventDefault();
			var answer = window.confirm( tm_epo_admin_meta_boxes.i18n_remove_tmcp );
			if ( answer ) {
				var el = $( this ).parent().parent();
				var variation = $( this ).attr( 'rel' );
				if ( variation > 0 ) {
					$( el ).block( {
						message: null,
						overlayCSS: {
							background: '#fff url(' + tm_epo_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
							opacity: 0.6
						}
					} );
					var data = {
						action: 'woocommerce_tm_remove_epo',
						tmcpid: variation,
						security: tm_epo_admin_meta_boxes.delete_tm_epo_nonce
					};
					$.post( tm_epo_admin_meta_boxes.ajax_url, data, function ( response ) {
						// Success
						$( el ).fadeOut( '300', function () {
							$( el ).remove();
						} );
					} );
				} else {
					$( el ).fadeOut( '300', function () {
						$( el ).remove();
					} );
				}
			}
			return false;
		} );

		$( '#tm_extra_product_options' ).on( 'change', '.tm-type', function ( e ) {
			var t = $( this ), 
				c = t.closest( '.data' ).find( '.tmcp_choices' );
			if ( t.val() == "checkbox" ) {
				c.removeClass( "tm-hidden" );
			} else {
				c.addClass( "tm-hidden" );
			}
		} );

		/*
		 * Mode Selector
		 */
		function tm_set_mode( mode ) {
			if ( ! mode ) {
				return;
			}
			$( '#tm_meta_cpf_mode' ).val( mode )
			$( ".tm_mode_selector" ).addClass( "tm_hidden" );
			$( ".tm_mode_builder,.tm_mode_local,.tm_mode_settings" ).hide();
			$( ".tm_mode_" + mode ).show();
			$( ".tm_builder_select,.tm_local_select,.tm_settings_select" ).removeClass( "button-primary" );
			$( ".tm_" + mode + "_select" ).addClass( "button-primary" );
		}

		$( '#tm_extra_product_options' ).on( 'click', '.tm_select_mode', function ( e ) {
			e.preventDefault();
			var mode = "local";
			if ( $( this ).is( ".tm_builder_select" ) ) {
				mode = "builder";
			}
			if ( $( this ).is( ".tm_settings_select" ) ) {
				mode = "settings";
			}
			tm_set_mode( mode );
		} );
		if ( ! $( '#tm_meta_cpf_mode' ).val() ) {
			$( '#tm_meta_cpf_mode' ).val( 'builder' );
		}
		tm_set_mode( $( '#tm_meta_cpf_mode' ).val() );

		//Order page
		$( '#woocommerce-order-items' ).on( 'click.tc', 'a.tm-delete-order-item', function ( e ) {
			e.preventDefault();
			var $item = $( this ).closest( 'tr.item, tr.fee, tr.shipping' ),
				item_id = $item.attr( 'data-tm_item_id' ),
				key = $item.attr( 'data-tm_key_id' );

			item_id = $( "<input type='hidden' class='tm_meta_serialized' name='tm_item_id' />" ).val( item_id );
			key = $( "<input type='hidden' class='tm_meta_serialized' name='tm_key' />" ).val( key );
			$item.prepend( item_id ).prepend( key );
			$( '.button.calculate-action' ).trigger( 'click' );
		} );

	} );
})( jQuery );