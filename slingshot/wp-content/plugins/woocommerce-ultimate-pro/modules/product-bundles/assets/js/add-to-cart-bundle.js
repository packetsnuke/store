/* jshint -W069 */
/* jshint -W041 */
/* global wc_bundle_params */
/* global wc_composite_params */
/* global wc_cp_get_variation_data */
/* global woocommerce_params */

/*-----------------------------------------------------------------*/
/*  Global script variable.                                        */
/*-----------------------------------------------------------------*/

var wc_pb_bundle_scripts = {};

/*-----------------------------------------------------------------*/
/*  Global utility variables + functions.                          */
/*-----------------------------------------------------------------*/

/**
 * Formats price strings according to WC settings.
 */
function wc_pb_woocommerce_number_format( price ) {

	var remove     = wc_bundle_params.currency_format_decimal_sep,
		position   = wc_bundle_params.currency_position,
		symbol     = wc_bundle_params.currency_symbol,
		trim_zeros = wc_bundle_params.currency_format_trim_zeros,
		decimals   = wc_bundle_params.currency_format_num_decimals;

	if ( trim_zeros == 'yes' && decimals > 0 ) {
		for ( var i = 0; i < decimals; i++ ) { remove = remove + '0'; }
		price = price.replace( remove, '' );
	}

	var price_format = '';

	if ( position == 'left' ) {
		price_format = '<span class="amount">' + symbol + price + '</span>';
	} else if ( position == 'right' ) {
		price_format = '<span class="amount">' + price + symbol +  '</span>';
	} else if ( position == 'left_space' ) {
		price_format = '<span class="amount">' + symbol + ' ' + price + '</span>';
	} else if ( position == 'right_space' ) {
		price_format = '<span class="amount">' + price + ' ' + symbol +  '</span>';
	}

	return price_format;
}

/**
 * Formats price values according to WC settings.
 */
function wc_pb_number_format( number ) {

	var decimals      = wc_bundle_params.currency_format_num_decimals,
		decimal_sep   = wc_bundle_params.currency_format_decimal_sep,
		thousands_sep = wc_bundle_params.currency_format_thousand_sep;

	var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	var d = decimal_sep == undefined ? ',' : decimal_sep;
	var t = thousands_sep == undefined ? '.' : thousands_sep, s = n < 0 ? '-' : '';
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '', j = (j = i.length) > 3 ? j % 3 : 0;

	return s + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '');
}

/**
 * Rounds price values according to WC settings.
 */
function wc_pb_number_round( number ) {

	var precision         = wc_bundle_params.currency_format_num_decimals,
		factor            = Math.pow( 10, precision ),
		tempNumber        = number * factor,
		roundedTempNumber = Math.round( tempNumber );

	return roundedTempNumber / factor;
}

/**
 * Bundle script object getter.
 */
jQuery.fn.wc_get_bundle_script = function() {

	var $bundle_form = jQuery( this );

	if ( ! $bundle_form.hasClass( 'bundle_form' ) ) {
		return false;
	}

	var script_id = $bundle_form.data( 'script_id' );

	if ( typeof( wc_pb_bundle_scripts[ script_id ] ) !== 'undefined' ) {
		return wc_pb_bundle_scripts[ script_id ];
	}

	return false;
};

/*-----------------------------------------------------------------*/
/*  Encapsulation.                                                 */
/*-----------------------------------------------------------------*/

( function( $ ) {

	/**
	 * Main bundle object.
	 */
	function WC_PB_Bundle( data ) {

		var bundle                    = this;

		this.bundle_id                = data.bundle_id;

		this.$bundle_form             = data.$bundle_form;
		this.$bundle_data             = data.$bundle_data;
		this.$bundle_wrap             = data.$bundle_data.find( '.bundle_wrap' );
		this.$bundled_items           = data.$bundle_form.find( '.bundled_product' );

		this.$bundle_price            = data.$bundle_data.find( '.bundle_price' );
		this.$bundle_error            = data.$bundle_data.find( '.bundle_error' );
		this.$bundle_error_content    = data.$bundle_data.find( '.bundle_error ul.msg' );
		this.$bundle_button           = data.$bundle_data.find( '.bundle_button' );
		this.$bundle_quantity         = this.$bundle_wrap.find( 'input.qty' );

		this.$addons_totals           = false;

		this.bundled_items            = {};

		this.price_data               = data.$bundle_data.data( 'bundle_price_data' );

		this.$initial_stock_status    = false;

		this.update_bundle_timer      = false;
		this.update_price_timer       = false;

		this.validation_messages      = [];

		this.is_initialized           = false;

		this.composite_data           = data.composite_data;

		this.refreshing_addons_totals = false;

		this.dirty_subtotals          = false;

		this.api                      = {

			/**
			 * Get the current bundle totals.
			 *
			 * @return object
			 */
			get_bundle_totals: function() {

				return bundle.price_data[ 'totals' ];
			},

			/**
			 * Get the current bundled item totals.
			 *
			 * @return object
			 */
			get_bundled_item_totals: function( bundled_item_id ) {

				return bundle.price_data[ 'bundled_item_' + bundled_item_id + '_totals' ];
			},

			/**
			 * Get the current bundled item recurring totals.
			 *
			 * @return object
			 */
			get_bundled_item_recurring_totals: function( bundled_item_id ) {

				return bundle.price_data[ 'bundled_item_' + bundled_item_id + '_recurring_totals' ];
			},

			/**
			 * Get the current validation status of the bundle.
			 *
			 * @return string ('pass' | 'fail')
			 */
			get_bundle_validation_status: function() {

				return bundle.passes_validation() ? 'pass' : 'fail';
			},

			/**
			 * Get the current validation messages for the bundle.
			 *
			 * @return array
			 */
			get_bundle_validation_messages: function() {

				return bundle.get_validation_messages();
			},

			/**
			 * Get the current stock status of the bundle.
			 *
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_bundle_stock_status: function() {

				var availability = bundle.$bundle_wrap.find( 'p.out-of-stock' ).not( '.inactive' );

				return availability.length > 0 ? 'out-of-stock' : 'in-stock';
			},

			/**
			 * Get the current availability string of the bundle.
			 *
			 * @return string
			 */
			get_bundle_availability: function() {

				var availability = bundle.$bundle_wrap.find( 'p.stock' );

				if ( availability.hasClass( 'inactive' ) ) {
					if ( false !== bundle.$initial_stock_status ) {
						availability = bundle.$initial_stock_status.clone().wrap( '<div></div>' ).parent().html();
					} else {
						availability = '';
					}
				} else {
					availability = availability.clone().removeAttr( 'style' ).wrap( '<div></div>' ).parent().html();
				}

				return availability;
			},

			/**
			 * Gets bundle configuration details.
			 *
			 * @return object | false
			 */
			get_bundle_configuration: function() {

				var bundle_config = {};

				if ( bundle.bundled_items.length === 0 ) {
					return false;
				}

				$.each( bundle.bundled_items, function( index, bundled_item ) {

					var bundled_item_config = {
						title:        bundled_item.get_title(),
						product_id:   bundled_item.get_product_id(),
						variation_id: bundled_item.get_variation_id(),
						quantity:     bundle.price_data[ 'quantities' ][ bundled_item.bundled_item_id ],
						product_type: bundled_item.get_product_type(),
					};

					bundle_config[ bundled_item.bundled_item_id ] = bundled_item_config;
				} );

				return bundle_config;
			}
		};

		/**
		 * Object initialization.
		 */
		this.init = function() {

			/**
			 * Initial states and loading.
			 */

			// Ensure error div exists (template back-compat).
			if ( this.$bundle_error_content.length === 0 && false === this.composite_data ) {
				if ( this.$bundle_error.length > 0 ) {
					this.$bundle_error.remove();
				}
				this.$bundle_price.after( '<div class="bundle_error" style="display:none"><ul class="msg woocommerce-info"></ul></div>' );
				this.$bundle_error         = this.$bundle_data.find( '.bundle_error' );
				this.$bundle_error_content = this.$bundle_error.find( '.msg' );
			}

			// Addons compatibility.
			var $addons_totals = this.$bundle_data.find( '#product-addons-total' );

			if ( $addons_totals.length > 0 ) {
				this.$addons_totals = $addons_totals;
				this.$bundle_price.after( $addons_totals );
			}

			// Save initial availability.
			if ( this.$bundle_wrap.find( 'p.stock' ).length > 0 ) {
				this.$initial_stock_status = this.$bundle_wrap.find( 'p.stock' ).clone();
			}

			// Price suffix data.
			this.price_data.suffix_exists              = wc_bundle_params.price_display_suffix !== '';
			this.price_data.suffix                     = wc_bundle_params.price_display_suffix !== '' ? ' <small class="woocommerce-price-suffix">' + wc_bundle_params.price_display_suffix + '</small>' : '';
			this.price_data.suffix_contains_price_incl = wc_bundle_params.price_display_suffix.indexOf( '{price_including_tax}' ) > -1;
			this.price_data.suffix_contains_price_excl = wc_bundle_params.price_display_suffix.indexOf( '{price_excluding_tax}' ) > -1;

			// Delete redundant form inputs.
			this.$bundle_button.find( 'input[name*="bundle_variation"], input[name*="bundle_attribute"]' ).remove();

			/**
			 * Bind bundle event handlers.
			 */

			this.bind_event_handlers();

			/**
			 * Init Bundled Items.
			 */

			this.init_bundled_items();

			/**
			 * Init Composite Products integration.
			 */

			if ( this.is_composited() ) {
				this.init_composite();
			}

			/**
			 * Initialize.
			 */

			this.$bundle_data.triggerHandler( 'woocommerce-product-bundle-initializing', [ this ] );

			$.each( this.bundled_items, function( index, bundled_item ) {
				bundled_item.init_scripts();
			} );

			this.update_bundle_task();

			this.is_initialized = true;

			this.$bundle_data.trigger( 'woocommerce-product-bundle-initialized', [ this ] );
		};

		/**
		 * Shuts down events, actions and filters managed by this script object.
		 */
		this.shutdown = function() {

			this.$bundle_form.find( '*' ).off();

			if ( false !== this.composite_data ) {
				this.remove_composite_hooks();
			}
		};

		/**
		 * Composite Products app integration.
		 */
		this.init_composite = function() {

			/**
			 * If priced per product, replace 'i18n_total' string with 'i18n_subtotal'.
			 */
			if ( this.composite_data.composite.api.is_component_priced_individually( this.composite_data.component.step_id ) ) {
				wc_bundle_params.i18n_total = wc_bundle_params.i18n_subtotal;
			}

			/**
			 * Add/remove hooks on the 'component_scripts_initialized' action.
			 */
			this.composite_data.composite.actions.add_action( 'component_scripts_initialized_' + this.composite_data.component.step_id, this.component_scripts_initialized_action, 10, this );
		};

		/**
		 * Add hooks on the 'component_scripts_initialized' action.
		 */
		this.component_scripts_initialized_action = function() {

			if ( parseInt( this.composite_data.component.component_selection_model.selected_product ) === parseInt( this.bundle_id ) ) {
				this.add_composite_hooks();
			} else {
				this.remove_composite_hooks();
			}
		};

		/**
		 * Composite Products app integration - add actions and filters.
		 */
		this.add_composite_hooks = function() {

			/**
			 * Filter validation state.
			 */
			this.composite_data.composite.filters.add_filter( 'step_is_valid', this.cp_step_is_valid_filter, 10, this );

			/**
			 * Filter title in summary.
			 */
			this.composite_data.composite.filters.add_filter( 'component_selection_formatted_title', this.cp_component_selection_formatted_title_filter, 10, this );
			this.composite_data.composite.filters.add_filter( 'component_selection_meta', this.cp_component_selection_meta_filter, 10, this );

			/**
			 * Filter totals.
			 */
			this.composite_data.composite.filters.add_filter( 'component_totals', this.cp_component_totals_filter, 10, this );

			/**
			 * Filter component configuration data.
			 */
			this.composite_data.composite.filters.add_filter( 'component_configuration', this.cp_component_configuration_filter, 10, this );

			/**
			 * Add validation messages.
			 */
			this.composite_data.composite.actions.add_action( 'validate_step', this.cp_validation_messages_action, 10, this );
		};

		/**
		 * Composite Products app integration - remove actions and filters.
		 */
		this.remove_composite_hooks = function() {

			this.composite_data.composite.filters.remove_filter( 'step_is_valid', this.cp_step_is_valid_filter );
			this.composite_data.composite.filters.remove_filter( 'component_selection_formatted_title', this.cp_component_selection_formatted_title_filter );
			this.composite_data.composite.filters.remove_filter( 'component_selection_meta', this.cp_component_selection_meta_filter );
			this.composite_data.composite.filters.remove_filter( 'component_totals', this.cp_component_totals_filter );
			this.composite_data.composite.filters.remove_filter( 'component_configuration', this.cp_component_configuration_filter );

			this.composite_data.composite.actions.remove_action( 'component_scripts_initialized_' + this.composite_data.component.step_id, this.component_scripts_initialized_action );
			this.composite_data.composite.actions.remove_action( 'validate_step', this.cp_validation_messages_action );
		};

		/**
		 * Appends bundle configuration data to component config data.
		 */
		this.cp_component_configuration_filter = function( configuration_data, component ) {

			if ( component.step_id === this.composite_data.component.step_id ) {
				configuration_data[ 'bundled_items' ] = bundle.api.get_bundle_configuration();
			}

			return configuration_data;
		};

		/**
		 * Filters the component totals to pass on the calculated bundle totals.
		 */
		this.cp_component_totals_filter = function( totals, component ) {

			if ( component.step_id === this.composite_data.component.step_id ) {

				var bundle_addons_price  = Number( component.component_selection_model.get( 'selected_addons' ) ),
					bundle_addons_totals = bundle.get_taxed_totals( bundle_addons_price, bundle_addons_price, bundle.price_data[ 'base_price_tax' ] );

				totals.price          = bundle_addons_totals.price + this.price_data[ 'totals' ].price * component.get_selected_quantity();
				totals.regular_price  = bundle_addons_totals.regular_price + this.price_data[ 'totals' ].regular_price * component.get_selected_quantity();
				totals.price_incl_tax = bundle_addons_totals.price_incl_tax + this.price_data[ 'totals' ].price_incl_tax * component.get_selected_quantity();
				totals.price_excl_tax = bundle_addons_totals.price_excl_tax + this.price_data[ 'totals' ].price_excl_tax * component.get_selected_quantity();

				$.each( bundle.bundled_items, function( index, bundled_item ) {
					if ( bundled_item.is_sold_individually() && component.get_selected_quantity() > 1 ) {
						totals.price          = totals.price - ( component.get_selected_quantity() - 1 ) * bundle.price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ].price;
						totals.regular_price  = totals.regular_price - ( component.get_selected_quantity() - 1 ) * bundle.price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ].regular_price;
						totals.price_incl_tax = totals.price_incl_tax - ( component.get_selected_quantity() - 1 ) * bundle.price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ].price_incl_tax;
						totals.price_excl_tax = totals.price_excl_tax - ( component.get_selected_quantity() - 1 ) * bundle.price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ].price_excl_tax;
					}
				} );
			}

			// Update price string to include component quantity.
			this.updated_totals();

			return totals;
		};

		/**
		 * Filters the summary view title to include bundled product details.
		 */
		this.cp_component_selection_formatted_title_filter = function( formatted_title, raw_title, qty, formatted_meta, component ) {

			if ( component.step_id === this.composite_data.component.step_id ) {

				var bundled_products_count    = 0,
					bundle                    = this;

				$.each( bundle.bundled_items, function( index, bundled_item ) {
					if ( bundled_item.$bundled_item_cart.data( 'quantity' ) > 0 ) {
						bundled_products_count++;
					}
				} );

				if ( component.is_static() ) {
					if ( bundled_products_count === 0 ) {
						formatted_title = wc_composite_params.i18n_no_selection;
					} else {

						var contents = this.cp_get_formatted_contents( component );

						if ( contents ) {
							formatted_title = contents;
						}
					}
				}
			}

			return formatted_title;
		};

		/**
		 * Filters the summary view title to include bundled product details.
		 */
		this.cp_component_selection_meta_filter = function( meta, component ) {

			if ( component.step_id === this.composite_data.component.step_id ) {

				var bundled_products_count = 0,
					bundle                 = this;

				$.each( bundle.bundled_items, function( index, bundled_item ) {
					if ( bundled_item.$bundled_item_cart.data( 'quantity' ) > 0 ) {
						bundled_products_count++;
					}
				} );

				if ( bundled_products_count !== 0 && ! component.is_static() ) {

					var selected_bundled_products = this.cp_get_formatted_contents( component );

					if ( selected_bundled_products !== '' ) {
						meta.push( { meta_key: wc_bundle_params.i18n_contents, meta_value: selected_bundled_products } );
					}
				}
			}

			return meta;
		};

		/**
		 * Formatted bundle contents for display in Composite Products summary views.
		 */
		this.cp_get_formatted_contents = function( component ) {

			var formatted_contents = '',
				formatted_titles   = [],
				bundle_qty         = component.get_selected_quantity();

			$.each( this.bundled_items, function( index, bundled_item ) {

				if ( bundled_item.$self.hasClass( 'bundled_item_hidden' ) ) {
					return true;
				}

				if ( bundled_item.$bundled_item_cart.data( 'quantity' ) > 0 ) {

					var formatted_item_title    = bundled_item.$bundled_item_cart.data( 'title' ),
						item_quantity           = parseInt( bundled_item.$bundled_item_cart.data( 'quantity' ) * bundle_qty ),
						item_meta               = wc_cp_get_variation_data( bundled_item.$bundled_item_cart.find( '.variations' ) ),
						formatted_item_quantity = item_quantity > 1 ? '<strong>' + wc_composite_params.i18n_qty_string.replace( '%s', item_quantity ) + '</strong>' : '',
						formatted_item_meta     = '';

					if ( item_meta.length > 0 ) {

						$.each( item_meta, function( index, meta ) {
							formatted_item_meta = formatted_item_meta + '<span class="bundled_meta_element"><span class="bundled_meta_key">' + meta.meta_key + ':</span> <span class="bundled_meta_value">' + meta.meta_value + '</span>';
							if ( index !== item_meta.length - 1 ) {
								formatted_item_meta = formatted_item_meta + '<span class="bundled_meta_value_sep">, </span>';
							}
							formatted_item_meta = formatted_item_meta + '</span>';
						} );

						formatted_item_title = wc_bundle_params.i18n_title_meta_string.replace( '%t', formatted_item_title ).replace( '%m', '<span class="content_bundled_product_meta">' + formatted_item_meta + '</span>' );
					}

					formatted_item_title = wc_composite_params.i18n_title_string.replace( '%t', formatted_item_title ).replace( '%q', formatted_item_quantity ).replace( '%p', '' );

					formatted_titles.push(  formatted_item_title );
				}
			} );

			if ( formatted_titles.length > 0 ) {
				$.each( formatted_titles, function( index, formatted_title ) {
					formatted_contents = formatted_contents + '<span class="content_bundled_product_title">' + formatted_title;
					if ( index !== formatted_titles.length - 1 ) {
						formatted_contents = formatted_contents + '<span class="bundled_product_title_sep">, </span>';
					}
					formatted_contents = formatted_contents + '</span>';
				} );
			}

			return formatted_contents;
		};

		/**
		 * Filters the validation state of the component containing this bundle.
		 */
		this.cp_step_is_valid_filter = function( is_valid, step ) {

			if ( step.step_id === this.composite_data.component.step_id ) {
				is_valid = this.passes_validation();
			}

			return is_valid;
		};

		/**
		 * Adds validation messages to the component containing this bundle.
		 */
		this.cp_validation_messages_action = function( step, is_valid ) {

			if ( step.step_id === this.composite_data.component.step_id && false === is_valid ) {

				var validation_messages = this.get_validation_messages();

				$.each( validation_messages, function( index, message ) {
					step.add_validation_message( message );
					step.add_validation_message( message, 'composite' );
				} );
			}
		};

		/**
		 * WC front-end ajax URL.
		 */
		this.get_ajax_url = function( action ) {

			return woocommerce_params.wc_ajax_url.toString().replace( '%%endpoint%%', action );
		};

		/**
		 * Attach bundle-level event handlers.
		 */
		this.bind_event_handlers = function() {

			if ( false !== bundle.$addons_totals ) {

				this.$bundle_data.on( 'updated_addons', function() {

					if ( false === bundle.refreshing_addons_totals && bundle.passes_validation() ) {

						bundle.refreshing_addons_totals = true;

						bundle.$addons_totals.data( 'price', bundle.price_data[ 'totals' ].price );
						bundle.$addons_totals.data( 'raw-price', bundle.price_data[ 'totals' ].price );

						var addons_tax_diff = 0;

						if ( wc_bundle_params.calc_taxes === 'yes' ) {
							if ( wc_bundle_params.tax_display_shop === 'incl' ) {

								if ( wc_bundle_params.prices_include_tax === 'yes' ) {
									addons_tax_diff = bundle.$addons_totals.data( 'addons-price' ) * ( 1 - 1 / Number( bundle.price_data[ 'base_price_tax' ] ) );
								}

								bundle.$addons_totals.data( 'raw-price', bundle.price_data[ 'totals' ].price_excl_tax - addons_tax_diff );
								bundle.$addons_totals.data( 'tax-mode', 'excl' );

							} else {

								if ( wc_bundle_params.prices_include_tax === 'no' ) {
									addons_tax_diff = bundle.$addons_totals.data( 'addons-price' ) * ( 1 - Number( bundle.price_data[ 'base_price_tax' ] ) );
								}

								bundle.$addons_totals.data( 'raw-price', bundle.price_data[ 'totals' ].price_incl_tax - addons_tax_diff );
								bundle.$addons_totals.data( 'tax-mode', 'incl' );
							}
						}

						bundle.$bundle_data.trigger( 'woocommerce-product-addons-update' );

						bundle.refreshing_addons_totals = false;
					}

				} );
			}

			this.$bundle_data

				.on( 'woocommerce-nyp-updated-item', function( event ) {

					var nyp = $( this ).find( '.nyp' );

					if ( nyp.is( ':visible' ) ) {

						bundle.price_data[ 'base_price' ] = nyp.data( 'price' );

						if ( bundle.is_initialized ) {
							bundle.dirty_subtotals = true;
							bundle.update_totals();
						}
					}

					event.stopPropagation();
				} )

				.on( 'woocommerce-product-bundle-calculate-totals', function( event, bundle ) {
					bundle.calculate_totals();
				} )

				.on( 'woocommerce-product-bundle-validation-status-changed', function( event, bundle ) {
					bundle.updated_totals();
				} );
		};

		/**
		 * Initialize bundled item objects.
		 */
		this.init_bundled_items = function() {

			bundle.$bundled_items.each( function( index ) {

				bundle.bundled_items[ index ] = new WC_PB_Bundled_Item( bundle, $( this ), index );

				bundle.bind_bundled_item_event_handlers( bundle.bundled_items[ index ] );

			} );
		};

		/**
		 * Attach bundled-item-level event handlers.
		 */
		this.bind_bundled_item_event_handlers = function( bundled_item ) {

			bundled_item.$self

				/**
				 * Update totals upon changing quantities.
				 */
				.on( 'input change', 'input.bundled_qty', function( e ) {

					var min  = parseFloat( $( this ).attr( 'min' ) ),
						max  = parseFloat( $( this ).attr( 'max' ) );

					if ( e.type === 'change' && min >= 0 && ( parseFloat( $( this ).val() ) < min || isNaN( parseFloat( $( this ).val() ) ) ) ) {
						$( this ).val( min );
					}

					if ( e.type === 'change' && max > 0 && parseFloat( $( this ).val() ) > max ) {
						$( this ).val( max );
					}

					bundled_item.update_selection_title();
					bundle.update_bundle( bundled_item );
				} )

				.on( 'change', '.bundled_product_optional_checkbox input', function( event ) {

					if ( $( this ).is( ':checked' ) ) {

						bundled_item.$bundled_item_content.slideDown( 200 );
						bundled_item.set_selected( true );

						// Tabular mini-extension compat.
						bundled_item.$self.find( '.bundled_item_qty_col .quantity' ).removeClass( 'quantity_hidden' );

						// Allow variations script to flip images in bundled_product_images div.
						bundled_item.$self.find( '.variations_form .variations select:eq(0)' ).trigger( 'change' );

					} else {

						bundled_item.$bundled_item_content.slideUp( 200 );
						bundled_item.set_selected( false );

						// Tabular mini-extension compat.
						bundled_item.$self.find( '.bundled_item_qty_col .quantity' ).addClass( 'quantity_hidden' );

						// Reset image in bundled_product_images div
						bundled_item.add_wc_core_gallery_class();
						bundled_item.$self.find( '.variations_form' ).trigger( 'reset_image' );
						bundled_item.remove_wc_core_gallery_class();
					}

					bundled_item.update_selection_title();
					bundle.update_bundle( bundled_item );

					event.stopPropagation();
				} )

				.on( 'found_variation', function( event, variation ) {

					bundled_item.variation_id = variation.variation_id.toString();

					// Put variation price data in price table.
					bundle.price_data[ 'prices' ][ bundled_item.bundled_item_id ]                   = Number( variation.price );
					bundle.price_data[ 'regular_prices' ][ bundled_item.bundled_item_id ]           = Number( variation.regular_price );

					bundle.price_data[ 'prices_tax' ][ bundled_item.bundled_item_id ]               = Number( variation.price_tax );

					// Put variation recurring component data in price table.
					bundle.price_data[ 'recurring_prices' ][ bundled_item.bundled_item_id ]         = Number( variation.recurring_price );
					bundle.price_data[ 'regular_recurring_prices' ][ bundled_item.bundled_item_id ] = Number( variation.regular_recurring_price );

					bundle.price_data[ 'recurring_html' ][ bundled_item.bundled_item_id ]           = variation.recurring_html;
					bundle.price_data[ 'recurring_keys' ][ bundled_item.bundled_item_id ]           = variation.recurring_key;

					// Remove .images class from bundled_product_images div in order to avoid styling issues.
					bundled_item.remove_wc_core_gallery_class();

					// Tabular mini-extension compat.
					var $tabular_qty = bundled_item.$self.find( '.bundled_item_qty_col .quantity input' );

					if ( $tabular_qty.length > 0 ) {
						$tabular_qty.attr( 'min', variation.min_qty ).attr( 'max', variation.max_qty );
					}

					// Ensure min/max value are always honored.
					bundled_item.$bundled_item_qty.trigger( 'change' );

					bundled_item.update_selection_title();
					bundle.update_bundle( bundled_item );

					event.stopPropagation();
				} )

				.on( 'reset_image', function() {

					// Remove .images class from bundled_product_images div in order to avoid styling issues.
					bundled_item.remove_wc_core_gallery_class();

				} )

				.on( 'woocommerce-product-addons-update', function( event ) {

					event.stopPropagation();
				} )

				.on( 'woocommerce_variation_select_focusin', function( event ) {

					event.stopPropagation();
				} )

				.on( 'woocommerce_variation_select_change', function( event ) {

					bundled_item.variation_id = '';

					var variations = $( this ).find( '.variations_form' );

					// Add .images class to bundled_product_images div ( required by the variations script to flip images ).
					if ( bundled_item.is_selected() ) {
						bundled_item.add_wc_core_gallery_class();
					}

					$( this ).find( '.variations .attribute-options select' ).each( function() {

						if ( $( this ).val() === '' ) {

							// Prevent from appearing as out of stock.
							variations.find( '.bundled_item_wrap .stock' ).addClass( 'disabled' );

							bundle.update_bundle( bundled_item );
							return false;
						}
					} );

					event.stopPropagation();
				} );


			bundled_item.$bundled_item_cart

				.on( 'updated_addons', function( event ) {

					var $addons_totals = bundled_item.$addons_totals,
						addons_price   = bundle.price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ];

					if ( $addons_totals.length > 0 ) {
						if ( typeof( $addons_totals.data( 'addons-raw-price' ) ) !== 'undefined' ) {
							addons_price = Number( $addons_totals.data( 'addons-raw-price' ) );
						}
					}

					if ( bundle.price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ] !== addons_price ) {
						bundle.price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ] = addons_price;
						bundle.update_totals( bundled_item );
					}

					event.stopPropagation();
				} )

				.on( 'woocommerce-nyp-updated-item', function( event ) {

					if ( bundled_item.is_nyp() && bundled_item.$nyp.is( ':visible' ) ) {

						var nyp_price = bundled_item.$nyp.data( 'price' );

						bundle.price_data[ 'prices' ][ bundled_item.bundled_item_id ]         = nyp_price;
						bundle.price_data[ 'regular_prices' ][ bundled_item.bundled_item_id ] = nyp_price;

						bundle.update_bundle( bundled_item );
					}

					event.stopPropagation();
				} );
		};

		/**
		 * Returns the quantity of this bundle.
		 */
		this.get_quantity = function() {

			var qty = parseInt( bundle.$bundle_quantity.val() );

			return qty;
		};

		/**
		 * Schedules an update of the bundle totals.
		 */
		this.update_bundle = function( triggered_by ) {

			clearTimeout( bundle.update_bundle_timer );

			bundle.update_bundle_timer = setTimeout( function() {
				bundle.update_bundle_task( triggered_by );
			}, 10 );
		};

		/**
		 * Updates the bundle totals.
		 */
		this.update_bundle_task = function( triggered_by ) {

			var out_of_stock_found       = false,
				$overridden_stock_status = false,
				validation_status        = bundle.is_initialized ? '' : bundle.api.get_bundle_validation_status(),
				all_set                  = true;

			/*
			 * Validate bundle.
			 */

			// Reset validation messages.

			bundle.validation_messages = [];

			// Validate bundled items and prepare price data for totals calculation.

			$.each( bundle.bundled_items, function( index, bundled_item ) {

				// Check variable products.
				if ( ( bundled_item.get_product_type() === 'variable' || bundled_item.get_product_type() === 'variable-subscription' ) && bundled_item.get_variation_id() === '' ) {
					if ( bundled_item.is_selected() && bundled_item.get_quantity() > 0 ) {
						all_set = false;
					}
				}

			} );

			if ( ! all_set ) {
				bundle.add_validation_message( wc_bundle_params.i18n_select_options );
			}

			// Bundle not purchasable?
			if ( bundle.price_data[ 'is_purchasable' ] !== 'yes' ) {
				// Show 'i18n_unavailable_text' message.
				bundle.add_validation_message( wc_bundle_params.i18n_unavailable_text );
			} else {
				// Validate 3rd party constraints.
				bundle.$bundle_data.triggerHandler( 'woocommerce-product-bundle-validate', [ bundle ] );
			}

			// Validation status changed?
			if ( validation_status !== bundle.api.get_bundle_validation_status() ) {
				bundle.$bundle_data.triggerHandler( 'woocommerce-product-bundle-validation-status-changed', [ bundle ] );
			}

			/*
			 * Calculate totals.
			 */

			if ( bundle.price_data[ 'is_purchasable' ] === 'yes' ) {
				bundle.update_totals( triggered_by );
			}

			/*
			 * Validation result handling.
			 */

			if ( bundle.passes_validation() ) {

				// Check if any item is out of stock.
				$.each( bundle.bundled_items, function( index, bundled_item ) {

					if ( ! bundled_item.is_selected() ) {
						return true;
					}

					var $item_stock_p = bundled_item.$bundled_item_cart.find( 'p.stock:not(.disabled)' );

					if ( $item_stock_p.hasClass( 'out-of-stock' ) && bundle.price_data[ 'quantities' ][ bundled_item.bundled_item_id ] > 0 ) {
						out_of_stock_found = true;
					}

				} );

				// Show add-to-cart button.
				if ( out_of_stock_found ) {
					bundle.$bundle_button.find( 'button' ).prop( 'disabled', true ).addClass( 'disabled' );
				} else {
					bundle.$bundle_button.find( 'button' ).prop( 'disabled', false ).removeClass( 'disabled' );
				}

				// Hide validation messages.
				setTimeout( function() {
					bundle.$bundle_error.slideUp( 200 );
				}, 10 );

				bundle.$bundle_wrap.trigger( 'woocommerce-product-bundle-show' );

			} else {

				bundle.hide_bundle();
			}

			/**
			 * Override bundle availability.
			 */
			$.each( bundle.bundled_items, function( index, bundled_item ) {

				if ( ! bundled_item.is_selected() ) {
					return true;
				}

				var $item_stock_p = bundled_item.$bundled_item_cart.find( 'p.stock:not(.disabled)' );

				if ( $item_stock_p.hasClass( 'out-of-stock' ) && bundle.price_data[ 'quantities' ][ bundled_item.bundled_item_id ] > 0 ) {
					$overridden_stock_status = $item_stock_p.clone().html( wc_bundle_params.i18n_partially_out_of_stock );
				}

				if ( ! out_of_stock_found && $item_stock_p.hasClass( 'available-on-backorder' ) && bundle.price_data[ 'quantities' ][ bundled_item.bundled_item_id ] > 0 ) {
					$overridden_stock_status = $item_stock_p.clone().html( wc_bundle_params.i18n_partially_on_backorder );
				}

			} );

			var $current_stock_status = bundle.$bundle_wrap.find( 'p.stock' );

			if ( $overridden_stock_status ) {
				if ( $current_stock_status.length > 0 ) {
					if ( $current_stock_status.hasClass( 'inactive' ) ) {
						$current_stock_status.replaceWith( $overridden_stock_status.hide() );
						$overridden_stock_status.slideDown( 200 );
					} else {
						$current_stock_status.replaceWith( $overridden_stock_status );
					}
				} else {
					bundle.$bundle_button.before( $overridden_stock_status.hide() );
					$overridden_stock_status.slideDown( 200 );
				}
			} else {
				if ( bundle.$initial_stock_status ) {
					$current_stock_status.replaceWith( bundle.$initial_stock_status );
				} else {
					$current_stock_status.addClass( 'inactive' ).slideUp( 200 );
				}
			}

			// If composited, run 'component_selection_content_changed' action to update all models/views.
			if ( bundle.is_composited() ) {
				bundle.composite_data.composite.actions.do_action( 'component_selection_content_changed', [ bundle.composite_data.component ] );
			}
		};

		/**
		 * Hide the add-to-cart button and show validation messages.
		 */
		this.hide_bundle = function( hide_message ) {

			var messages = $( '<ul/>' );

			if ( typeof( hide_message ) === 'undefined' ) {

				var hide_messages = bundle.get_validation_messages();

				if ( hide_messages.length > 0 ) {
					$.each( hide_messages, function( i, message ) {
						messages.append( $( '<li/>' ).html( message ) );
					} );
				} else {
					messages.append( $( '<li/>' ).html( wc_bundle_params.i18n_unavailable_text ) );
				}

			} else {
				messages.append( $( '<li/>' ).html( hide_message.toString() ) );
			}

			bundle.$bundle_error_content.html( messages.html() );
			setTimeout( function() {
				bundle.$bundle_error.slideDown( 200 );
			}, 10 );
			bundle.$bundle_button.find( 'button' ).prop( 'disabled', true ).addClass( 'disabled' );

			bundle.$bundle_wrap.trigger( 'woocommerce-product-bundle-hide' );
		};

		/**
		 * Updates the 'price_data' property with the latest values.
		 */
		this.update_price_data = function() {

			$.each( bundle.bundled_items, function( index, bundled_item ) {

				var cart            = bundled_item.$bundled_item_cart,
					bundled_item_id = bundled_item.bundled_item_id,
					item_quantity   = bundled_item.get_quantity();

				bundle.price_data[ 'quantities' ][ bundled_item_id ] = 0;

				// Set quantity based on optional flag.
				if ( bundled_item.is_selected() && item_quantity > 0 ) {
					bundle.price_data[ 'quantities' ][ bundled_item_id ] = parseInt( item_quantity );
				}

				// Store quantity for easy access by 3rd parties.
				cart.data( 'quantity', bundle.price_data[ 'quantities' ][ bundled_item_id ] );

				// Check variable products.
				if ( ( bundled_item.get_product_type() === 'variable' || bundled_item.get_product_type() === 'variable-subscription' ) && bundled_item.get_variation_id() === '' ) {
					bundle.price_data[ 'prices' ][ bundled_item_id ]         = 0.0;
					bundle.price_data[ 'regular_prices' ][ bundled_item_id ] = 0.0;
					bundle.price_data[ 'prices_tax' ][ bundled_item_id ]     = 0.0;
				}

				// Cast amounts.
				bundle.price_data[ 'prices' ][ bundled_item_id ]                   = Number( bundle.price_data[ 'prices' ][ bundled_item_id ] );
				bundle.price_data[ 'regular_prices' ][ bundled_item_id ]           = Number( bundle.price_data[ 'regular_prices' ][ bundled_item_id ] );

				bundle.price_data[ 'prices_tax' ][ bundled_item_id ]               = Number( bundle.price_data[ 'prices_tax' ][ bundled_item_id ] );

				bundle.price_data[ 'addons_prices' ][ bundled_item_id ]            = Number( bundle.price_data[ 'addons_prices' ][ bundled_item_id ] );

				bundle.price_data[ 'recurring_prices' ][ bundled_item_id ]         = Number( bundle.price_data[ 'recurring_prices' ][ bundled_item_id ] );
				bundle.price_data[ 'regular_recurring_prices' ][ bundled_item_id ] = Number( bundle.price_data[ 'regular_recurring_prices' ][ bundled_item_id ] );
			} );
		};

		/**
		 * Calculates and updates bundle subtotals.
		 */
		this.update_totals = function( triggered_by ) {

			this.update_price_data();
			this.calculate_subtotals( triggered_by );

			if ( bundle.dirty_subtotals || false === bundle.is_initialized ) {
				bundle.dirty_subtotals = false;
				bundle.$bundle_data.triggerHandler( 'woocommerce-product-bundle-calculate-totals', [ bundle ] );
			}
		};

		/**
		 * Calculates bundled item subtotals (bundle totals) and updates the corresponding 'price_data' fields.
		 */
		this.calculate_subtotals = function( triggered_by, price_data_array ) {

			var price_data = typeof( price_data_array ) === 'undefined' ? bundle.price_data : price_data_array;

			triggered_by = typeof( triggered_by ) === 'undefined' ? false : triggered_by;

			$.each( bundle.bundled_items, function( index, bundled_item ) {

				if ( false !== triggered_by && triggered_by.bundled_item_id !== bundled_item.bundled_item_id ) {
					return true;
				}

				var totals                  = {
						price:          0.0,
						regular_price:  0.0,
						price_incl_tax: 0.0,
						price_excl_tax: 0.0
					},

					recurring_totals        = {
						price:          0.0,
						regular_price:  0.0,
						price_incl_tax: 0.0,
						price_excl_tax: 0.0
					},

					qty                     = price_data[ 'quantities' ][ bundled_item.bundled_item_id ],
					product_id              = bundled_item.get_product_type() === 'variable' ? bundled_item.get_variation_id() : bundled_item.get_product_id(),

					tax_ratio               = price_data[ 'prices_tax' ][ bundled_item.bundled_item_id ],

					regular_price           = price_data[ 'regular_prices' ][ bundled_item.bundled_item_id ] + price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ],
					price                   = price_data[ 'prices' ][ bundled_item.bundled_item_id ] + price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ],

					regular_recurring_price = price_data[ 'regular_recurring_prices' ][ bundled_item.bundled_item_id ] + price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ],
					recurring_price         = price_data[ 'recurring_prices' ][ bundled_item.bundled_item_id ] + price_data[ 'addons_prices' ][ bundled_item.bundled_item_id ];


				if ( wc_bundle_params.calc_taxes === 'yes' ) {

					if ( product_id > 0 && qty > 0 ) {

						if ( price > 0 || regular_price > 0 ) {
							totals = bundle.get_taxed_totals( price, regular_price, tax_ratio, qty );
						}

						if ( recurring_price > 0 || regular_recurring_price > 0 ) {
							recurring_totals = bundle.get_taxed_totals( recurring_price, regular_recurring_price, tax_ratio, qty );
						}
					}

				} else {

					totals.price                    = qty * price;
					totals.regular_price            = qty * regular_price;
					totals.price_incl_tax           = qty * price;
					totals.price_excl_tax           = qty * price;

					recurring_totals.price          = qty * recurring_price;
					recurring_totals.regular_price  = qty * regular_recurring_price;
					recurring_totals.price_incl_tax = qty * recurring_price;
					recurring_totals.price_excl_tax = qty * recurring_price;
				}

				if ( bundle.totals_changed( price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ], totals ) ) {
					bundle.dirty_subtotals = true;
					price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ] = totals;
				}

				if ( bundle.totals_changed( price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_recurring_totals' ], recurring_totals ) ) {
					bundle.dirty_subtotals = true;
					price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_recurring_totals' ] = recurring_totals;
				}

			} );

			if ( typeof( price_data_array ) !== 'undefined' ) {
				return price_data;
			}
		};

		/**
		 * Calculates totals by applying tax ratios to raw prices.
		 */
		this.get_taxed_totals = function( price, regular_price, tax_ratio, qty ) {

			var totals = {
				price:          price,
				regular_price:  regular_price,
				price_incl_tax: price,
				price_excl_tax: price
			};

			if ( tax_ratio > 0 ) {

				if ( wc_bundle_params.prices_include_tax === 'yes' ) {
					totals.price_incl_tax = price;
					totals.price_excl_tax = price / tax_ratio;
				} else {
					totals.price_incl_tax = wc_pb_number_round( price * tax_ratio );
					totals.price_excl_tax = price;
				}

				if ( wc_bundle_params.tax_display_shop === 'incl' ) {
					totals.price = totals.price_incl_tax;
					if ( wc_bundle_params.prices_include_tax === 'yes' ) {
						totals.regular_price = regular_price;
					} else {
						totals.regular_price = wc_pb_number_round( regular_price * tax_ratio );
					}
				} else {
					totals.price = totals.price_excl_tax;
					if ( wc_bundle_params.prices_include_tax === 'yes' ) {
						totals.regular_price = regular_price / tax_ratio;
					} else {
						totals.regular_price = regular_price;
					}
				}
			}

			qty = typeof( qty ) === 'undefined' ? 1 : qty;

			// Do not include quantities in rounding.
			totals.price          = qty * totals.price;
			totals.regular_price  = qty * totals.regular_price;
			totals.price_incl_tax = qty * totals.price_incl_tax;
			totals.price_excl_tax = qty * totals.price_excl_tax;

			return totals;
		};

		/**
		 * Adds bundle subtotals and calculates bundle totals.
		 */
		this.calculate_totals = function( price_data_array ) {

			var price_data = typeof( price_data_array ) === 'undefined' ? bundle.price_data : price_data_array;

			var totals_changed       = false,
				base_price           = Number( price_data[ 'base_price' ] ),
				base_regular_price   = Number( price_data[ 'base_regular_price' ] ),
				base_price_tax_ratio = Number( price_data[ 'base_price_tax' ] );

			price_data[ 'base_price_totals' ] = this.get_taxed_totals( base_price, base_regular_price, base_price_tax_ratio );

			// Non-recurring totals.
			price_data[ 'total' ]          = price_data[ 'base_price_totals' ].price;
			price_data[ 'regular_total' ]  = price_data[ 'base_price_totals' ].regular_price;

			price_data[ 'total_incl_tax' ] = price_data[ 'base_price_totals' ].price_incl_tax;
			price_data[ 'total_excl_tax' ] = price_data[ 'base_price_totals' ].price_excl_tax;

			$.each( bundle.bundled_items, function( index, bundled_item ) {

				var item_totals = price_data[ 'bundled_item_' + bundled_item.bundled_item_id + '_totals' ];

				if ( typeof item_totals !== 'undefined' ) {

					price_data[ 'total' ]          += item_totals.price;
					price_data[ 'regular_total' ]  += item_totals.regular_price;

					price_data[ 'total_incl_tax' ] += item_totals.price_incl_tax;
					price_data[ 'total_excl_tax' ] += item_totals.price_excl_tax;
				}

			} );

			var totals = {
				price:          price_data[ 'total' ],
				regular_price:  price_data[ 'regular_total' ],
				price_incl_tax: price_data[ 'total_incl_tax' ],
				price_excl_tax: price_data[ 'total_excl_tax' ]
			};

			totals_changed = bundle.totals_changed( price_data[ 'totals' ], totals );

			// Store raw total.
			price_data[ 'total_raw' ] = totals.price;

			if ( wc_bundle_params.tax_display_shop === 'incl' && wc_bundle_params.prices_include_tax === 'no' ) {
				price_data[ 'total_raw' ] = totals.price_excl_tax;
			} else if ( wc_bundle_params.tax_display_shop === 'excl' && wc_bundle_params.prices_include_tax === 'yes' ) {
				price_data[ 'total_raw' ] = totals.price_incl_tax;
			}

			// Recurring totals, grouped by recurring id.
			var bundled_subs     = bundle.get_bundled_subscriptions(),
				recurring_totals = {};

			if ( bundled_subs ) {

				$.each( bundled_subs, function( index, bundled_sub ) {

					var bundled_item_id = bundled_sub.bundled_item_id;

					if ( price_data[ 'quantities' ][ bundled_item_id ] === 0 ) {
						return true;
					}

					var recurring_key         = price_data[ 'recurring_keys' ][ bundled_item_id ],
						recurring_item_totals = price_data[ 'bundled_item_' + bundled_item_id + '_recurring_totals' ];

					if ( typeof( recurring_totals[ recurring_key ] ) === 'undefined' ) {

						var recurring_key_data = {
							html:           price_data[ 'recurring_html' ][ bundled_item_id ],
							price:          recurring_item_totals.price,
							regular_price:  recurring_item_totals.regular_price,
							price_incl_tax: recurring_item_totals.price_incl_tax,
							price_excl_tax: recurring_item_totals.price_excl_tax
						};

						recurring_totals[ recurring_key ] = recurring_key_data;

					} else {

						recurring_totals[ recurring_key ].price          += recurring_item_totals.price;
						recurring_totals[ recurring_key ].regular_price  += recurring_item_totals.regular_price;
						recurring_totals[ recurring_key ].price_incl_tax += recurring_item_totals.price_incl_tax;
						recurring_totals[ recurring_key ].price_excl_tax += recurring_item_totals.price_excl_tax;
					}
				} );

				if ( ! totals_changed ) {
					var recurring_totals_pre  = JSON.stringify( price_data[ 'recurring_totals' ] ),
						reccuring_totals_post = JSON.stringify( recurring_totals );

					if ( recurring_totals_pre !== reccuring_totals_post ) {
						totals_changed = true;
					}
				}
			}

			// Render.
			if ( totals_changed || false === bundle.is_initialized ) {
				price_data[ 'totals' ]           = totals;
				price_data[ 'recurring_totals' ] = recurring_totals;

				if ( typeof( price_data_array ) === 'undefined' ) {
					this.updated_totals();
				} else {
					return price_data;
				}
			}
		};

		/**
		 * Schedules a UI bundle price string refresh.
		 */
		this.updated_totals = function() {

			clearTimeout( bundle.update_price_timer );

			bundle.update_price_timer = setTimeout( function() {
				bundle.updated_totals_task();
				bundle.$bundle_data.triggerHandler( 'woocommerce-product-bundle-updated-totals', [ bundle ] );
			}, 10 );
		};

		/**
		 * Build the non-recurring price html component.
		 */
		this.get_price_html = function( price_data_array ) {

			var price_data        = typeof( price_data_array ) === 'undefined' ? bundle.price_data : price_data_array,
				qty               = bundle.is_composited() ? this.composite_data.component.get_selected_quantity() : 1,
				bundle_price_html = '',
				show_total_string = ( price_data[ 'total_raw' ] !== price_data[ 'raw_bundle_price_min' ] || price_data[ 'raw_bundle_price_min' ] !== price_data[ 'raw_bundle_price_max' ] ) && false === bundle.get_bundled_subscriptions(),
				total_string      = show_total_string ? '<span class="total">' + wc_bundle_params.i18n_total + '</span>' : '',
				tag               = bundle.is_composited() ? 'span' : 'p';

			// Non-recurring price html data.
			var formatted_price         = price_data[ 'totals' ].price === 0.0 && price_data[ 'show_free_string' ] === 'yes' ? wc_bundle_params.i18n_free : wc_pb_woocommerce_number_format( wc_pb_number_format( price_data[ 'totals' ].price * qty ) ),
				formatted_regular_price = wc_pb_woocommerce_number_format( wc_pb_number_format( price_data[ 'totals' ].regular_price * qty ) ),
				formatted_suffix        = '',
				formatted_price_incl    = '',
				formatted_price_excl    = '';

			if ( price_data.suffix_exists ) {

				formatted_suffix = price_data.suffix;

				if ( price_data.suffix_contains_price_incl ) {
					formatted_price_incl = '<span class="amount">' + wc_pb_woocommerce_number_format( wc_pb_number_format( price_data[ 'total_incl_tax' ] * qty ) ) + '</span>';
					formatted_suffix     =  formatted_suffix.replace( '{price_including_tax}', formatted_price_incl );
				}

				if ( price_data.suffix_contains_price_excl ) {
					formatted_price_excl = '<span class="amount">' + wc_pb_woocommerce_number_format( wc_pb_number_format( price_data[ 'total_excl_tax' ] * qty ) ) + '</span>';
					formatted_suffix     =  formatted_suffix.replace( '{price_excluding_tax}', formatted_price_excl );
				}
			}

			if ( price_data[ 'totals' ].regular_price > price_data[ 'totals' ].price ) {
				bundle_price_html = '<' + tag + ' class="price">' + price_data[ 'price_string' ].replace( '%s', total_string + '<del>' + formatted_regular_price + '</del> <ins>' + formatted_price + '</ins>' + formatted_suffix ) + '</' + tag + '>';
			} else {
				bundle_price_html = '<' + tag + ' class="price">' + price_data[ 'price_string' ].replace( '%s', total_string + formatted_price + formatted_suffix ) + '</' + tag + '>';
			}

			return bundle_price_html;
		};

		/**
		 * Builds the recurring price html component for bundles that contain subscription products.
		 */
		this.get_recurring_price_html = function( price_data_array ) {

			var price_data = typeof( price_data_array ) === 'undefined' ? bundle.price_data : price_data_array;

			var bundle_recurring_price_html = '',
				bundled_subs                = bundle.get_bundled_subscriptions();

			if ( bundled_subs ) {

				$.each( price_data[ 'recurring_totals' ], function( recurring_component_key, recurring_component_data ) {

					var formatted_recurring_price         = recurring_component_data.price == 0 ? wc_bundle_params.i18n_free : wc_pb_woocommerce_number_format( wc_pb_number_format( recurring_component_data.price ) ),
						formatted_regular_recurring_price = wc_pb_woocommerce_number_format( wc_pb_number_format( recurring_component_data.regular_price ) ),
						formatted_suffix                  = '',
						formatted_price_incl              = '',
						formatted_price_excl              = '';

					if ( price_data.suffix_exists ) {

						formatted_suffix = price_data.suffix;

						if ( price_data.suffix_contains_price_incl ) {
							formatted_price_incl = '<span class="amount">' + wc_pb_woocommerce_number_format( wc_pb_number_format( recurring_component_data[ 'price_incl_tax' ] ) ) + '</span>';
							formatted_suffix     =  formatted_suffix.replace( '{price_including_tax}', formatted_price_incl );
						}

						if ( price_data.suffix_contains_price_excl ) {
							formatted_price_excl = '<span class="amount">' + wc_pb_woocommerce_number_format( wc_pb_number_format( recurring_component_data[ 'price_excl_tax' ] ) ) + '</span>';
							formatted_suffix     =  formatted_suffix.replace( '{price_excluding_tax}', formatted_price_excl );
						}
					}

					if ( recurring_component_data.regular_price > recurring_component_data.price ) {
						recurring_component_data.html = '<span class="amount bundled_sub_price_html">' + recurring_component_data.html.replace( '%s', '<del>' + formatted_regular_recurring_price + '</del> <ins>' + formatted_recurring_price + '</ins>' + formatted_suffix ) + '</span>';
					} else {
						recurring_component_data.html = '<span class="amount bundled_sub_price_html">' + recurring_component_data.html.replace( '%s', formatted_recurring_price + formatted_suffix ) + '</span>';
					}

					bundle_recurring_price_html = ( bundle_recurring_price_html !== '' ? ( bundle_recurring_price_html + '<span class="plus"> + </span>' ) : bundle_recurring_price_html ) + recurring_component_data.html;
				} );
			}

			return bundle_recurring_price_html;
		};

		/**
		 * Determines whether to show a bundle price html string.
		 */
		this.show_price_html = function() {

			var show_price = false;

			if ( false === bundle.get_bundled_subscriptions() ) {
				show_price = bundle.price_data[ 'total_raw' ] !== bundle.price_data[ 'raw_bundle_price_min' ] || bundle.price_data[ 'raw_bundle_price_min' ] !== bundle.price_data[ 'raw_bundle_price_max' ];
			} else {
				var bundled_variable_subs = bundle.get_bundled_subscriptions( 'variable' );
				if ( bundled_variable_subs ) {
					$.each( bundled_variable_subs, function( index, bundled_variable_sub ) {
						if ( bundle.price_data[ 'recurring_prices' ][ bundled_variable_sub.bundled_item_id ] > 0 ) {
							show_price = true;
							return false;
						}
					} );
				}
			}

			return show_price;
		};

		/**
		 * Refreshes the bundle price string in the UI.
		 */
		this.updated_totals_task = function() {

			var show_price = bundle.show_price_html();

			if ( bundle.is_composited() ) {

				if ( ! show_price ) {
					if ( bundle.composite_data.composite.api.is_component_priced_individually( this.composite_data.component.step_id ) ) {
						show_price = true;
					}
				}

				if ( show_price ) {
					if ( false === this.composite_data.component.is_selected_product_price_visible() ) {
						show_price = false;
					}
				}
			}

			if ( bundle.passes_validation() && show_price ) {

				var bundle_price_html           = bundle.get_price_html(),
					bundle_recurring_price_html = bundle.get_recurring_price_html();

				bundle_price_html = bundle_price_html.replace( '%r', bundle_recurring_price_html );

				bundle.$bundle_price.html( bundle_price_html );

				if ( bundle_recurring_price_html ) {
					bundle.$bundle_price.find( '.bundled_subscriptions_price_html' ).show();
				}

				bundle.$bundle_price.slideDown( 200 );

			} else {
				bundle.$bundle_price.slideUp( 200 );
			}

			// Composite products compatibility - trigger price update.
			if ( bundle.is_composited() ) {
				bundle.composite_data.composite.actions.do_action( 'component_totals_changed', [ bundle.composite_data.component ] );
			}

			// Addons compatibility.
			this.update_addons_totals();
		};

		/**
		 * Prevent addons ajax call, since composite container-level tax does not apply to entire contents.
		 */
		this.update_addons_totals = function() {

			if ( false !== this.$addons_totals ) {

				// Ensure addons ajax is not triggered at this point.
				this.$addons_totals.data( 'price', 0 );
				this.$addons_totals.data( 'raw-price', 0 );

				this.$bundle_data.trigger( 'woocommerce-product-addons-update' );
			}
		};

		/**
		 * Comparison of totals.
		 */
		this.totals_changed = function( totals_pre, totals_post ) {

			if ( typeof( totals_pre ) === 'undefined' || totals_pre.price !== totals_post.price || totals_pre.regular_price !== totals_post.regular_price || totals_pre.price_incl_tax !== totals_post.price_incl_tax || totals_pre.price_excl_tax !== totals_post.price_excl_tax ) {
				return true;
			}

			return false;
		};

		/**
		 * True if the bundle is part of a composite product.
		 */
		this.is_composited = function() {
			return false !== this.composite_data;
		};

		/**
		 * Find and return WC_PB_Bundled_Item objects that are subs.
		 */
		this.get_bundled_subscriptions = function( type ) {

			var bundled_subs = {},
				has_sub      = false;

			$.each( bundle.bundled_items, function( index, bundled_item ) {

				if ( bundled_item.is_subscription( type ) ) {

					bundled_subs[ index ] = bundled_item;
					has_sub               = true;
				}

			} );

			if ( has_sub ) {
				return bundled_subs;
			}

			return false;
		};

		/**
		 * Adds a validation message.
		 */
		this.add_validation_message = function( message ) {

			this.validation_messages.push( message.toString() );
		};

		/**
		 * Validation messages getter.
		 */
		this.get_validation_messages = function() {

			return this.validation_messages;
		};

		/**
		 * Validation state getter.
		 */
		this.passes_validation = function() {

			if ( this.validation_messages.length > 0 ) {
				return false;
			}

			return true;
		};
	}

	/**
     * Bundled Item object.
     */
	function WC_PB_Bundled_Item( bundle, $bundled_item, index ) {

		this.$self                        = $bundled_item;
		this.$bundled_item_cart           = $bundled_item.find( '.cart' );
		this.$bundled_item_content        = $bundled_item.find( '.bundled_item_optional_content, .bundled_item_cart_content' );
		this.$bundled_item_image          = $bundled_item.find( '.bundled_product_images' );
		this.$bundled_item_title          = $bundled_item.find( '.bundled_product_title_inner' );
		this.$bundled_item_qty            = $bundled_item.find( 'input.bundled_qty' );

		this.$addons_totals               = $bundled_item.find( '#product-addons-total' );
		this.$nyp                         = $bundled_item.find( '.nyp' );

		this.bundled_item_index           = index;
		this.bundled_item_id              = this.$bundled_item_cart.data( 'bundled_item_id' );
		this.bundled_item_title           = this.$bundled_item_cart.data( 'title' );
		this.bundled_item_optional_suffix = typeof( this.$bundled_item_cart.data( 'optional_suffix' ) ) === 'undefined' ? wc_bundle_params.i18n_optional : this.$bundled_item_cart.data( 'optional_suffix' );

		this.product_type                 = this.$bundled_item_cart.data( 'type' );
		this.product_id                   = typeof( bundle.price_data[ 'product_ids' ][ this.bundled_item_id ] ) === 'undefined' ? '' : bundle.price_data[ 'product_ids' ][ this.bundled_item_id ].toString();
		this.nyp                          = typeof( bundle.price_data[ 'product_ids' ][ this.bundled_item_id ] ) === 'undefined' ? false : bundle.price_data[ 'is_nyp' ][ this.bundled_item_id ] === 'yes';
		this.sold_individually            = typeof( bundle.price_data[ 'product_ids' ][ this.bundled_item_id ] ) === 'undefined' ? false : bundle.price_data[ 'is_sold_individually' ][ this.bundled_item_id ] === 'yes';
		this.variation_id                 = '';

		this.has_wc_core_gallery_class    = this.$bundled_item_image.hasClass( 'images' );

		if ( typeof( this.bundled_item_id ) === 'undefined' ) {
			this.bundled_item_id = this.$bundled_item_cart.attr( 'data-bundled-item-id' );
		}

		this.get_title = function() {

			return this.bundled_item_title;
		};

		this.get_optional_suffix = function() {

			return this.bundled_item_optional_suffix;
		};

		this.get_product_id = function() {

			return this.product_id;
		};

		this.get_variation_id = function() {

			return this.variation_id;
		};

		this.get_product_type = function() {

			return this.product_type;
		};

		this.get_quantity = function() {

			return this.$bundled_item_qty.val();
		};

		this.is_optional = function() {

			return ( this.$bundled_item_cart.data( 'optional' ) === 'yes' || this.$bundled_item_cart.data( 'optional' ) === 1 );
		};

		this.is_selected = function() {

			var selected = true;

			if ( this.is_optional() ) {
				if ( this.$bundled_item_cart.data( 'optional_status' ) === false ) {
					selected = false;
				}
			}

			return selected;
		};

		this.set_selected = function( status ) {

			if ( this.is_optional() ) {
				this.$bundled_item_cart.data( 'optional_status', status );
			}
		};

		this.init_scripts = function() {

			// Init PhotoSwipe if present.
			if ( typeof PhotoSwipe !== 'undefined' && 'yes' === wc_bundle_params.photoswipe_enabled ) {
				this.init_photoswipe();
			}

			// Init dependencies.
			this.$self.find( '.bundled_product_optional_checkbox input' ).change();

			if ( ( this.product_type === 'variable' || this.product_type === 'variable-subscription' ) && ! this.$bundled_item_cart.hasClass( 'variations_form' ) ) {

				// Initialize variations script.
				this.$bundled_item_cart.addClass( 'variations_form' ).wc_variation_form();

				this.$bundled_item_cart.find( '.variations select:eq(0)' ).change();
			}

			this.$self.find( 'div' ).stop( true, true );
			this.update_selection_title();
		};

		this.init_photoswipe = function() {

			this.$bundled_item_image.wc_product_gallery( { zoom_enabled: false, flexslider_enabled: false } );

			var $placeholder = this.$bundled_item_image.find( 'a.placeholder_image' );

			if ( $placeholder.length > 0 ) {
				$placeholder.on( 'click', function() {
					return false;
				} );
			}
		};

		this.update_selection_title = function( reset ) {

			if ( this.$bundled_item_title.length === 0 ) {
				return false;
			}

			var bundled_item_qty_val = parseInt( this.get_quantity() );

			if ( isNaN( bundled_item_qty_val ) ) {
				return false;
			}

			reset = typeof( reset ) === 'undefined' ? false : reset;

			if ( reset ) {
				bundled_item_qty_val = parseInt( this.$bundled_item_qty.attr( 'min' ) );
			}

			var selection_title           = this.bundled_item_title,
				selection_qty_string      = bundled_item_qty_val > 1 ? wc_bundle_params.i18n_qty_string.replace( '%s', bundled_item_qty_val ) : '',
				selection_optional_string = ( this.is_optional() && this.get_optional_suffix() !== '' ) ? wc_bundle_params.i18n_optional_string.replace( '%s', this.get_optional_suffix() ) : '',
				selection_title_incl_qty  = wc_bundle_params.i18n_title_string.replace( '%t', selection_title ).replace( '%q', selection_qty_string ).replace( '%o', selection_optional_string );

			this.$bundled_item_title.html( selection_title_incl_qty );
		};

		this.reset_selection_title = function() {

			this.update_selection_title( true );
		};

		this.is_subscription = function( type ) {

			if ( 'simple' === type ) {
				return this.product_type === 'subscription';
			} else if ( 'variable' === type ) {
				return this.product_type === 'variable-subscription';
			} else {
				return this.product_type === 'subscription' || this.product_type === 'variable-subscription';
			}
		};

		this.is_nyp = function() {

			return this.nyp;
		};

		this.is_sold_individually = function() {

			return this.sold_individually;
		};

		this.add_wc_core_gallery_class = function() {

			if ( ! this.has_wc_core_gallery_class ) {
				this.$bundled_item_image.addClass( 'images' );
			}
		};

		this.remove_wc_core_gallery_class = function() {

			if ( ! this.has_wc_core_gallery_class ) {
				this.$bundled_item_image.removeClass( 'images' );
			}
		};
	}


	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	jQuery( document ).ready( function($) {

		/**
		 * QuickView compatibility.
		 */
		$( 'body' ).on( 'quick-view-displayed', function() {
			$( '.bundle_form .bundle_data' ).each( function() {
				$( this ).wc_pb_bundle_form();
			} );
		} );

		/**
	 	 * Script initialization on '.bundle_data' jQuery objects.
	 	 */
		$.fn.wc_pb_bundle_form = function() {

			if ( ! $( this ).hasClass( 'bundle_data' ) ) {
				return true;
			}

			var $bundle_data = $( this ),
				container_id = $bundle_data.data( 'bundle_id' );

			if ( typeof( container_id ) === 'undefined' ) {
				container_id = $bundle_data.attr( 'data-bundle-id' );

				if ( container_id ) {
					$bundle_data.data( 'bundle_id', container_id );
				} else {
					return false;
				}
			}

			var $bundle_form     = $bundle_data.closest( '.bundle_form' ),
				$composite_form  = $bundle_form.closest( '.composite_form' ),
				composite_data   = false,
				bundle_script_id = container_id;

			// If part of a composite product, get a unique id for the script object and prepare variables for integration code.
			if ( $composite_form.length > 0 ) {

				var $component   = $bundle_form.closest( '.component' ),
					component_id = $component.data( 'item_id' );

				if ( component_id > 0 && $.isFunction( $.fn.wc_get_composite_script ) ) {

					var composite_script = $composite_form.wc_get_composite_script();

					if ( false !== composite_script ) {

						var component = composite_script.api.get_step( component_id );

						if ( false !== component ) {
							composite_data = {
								composite: composite_script,
								component: component
							};
							bundle_script_id = component_id;
						}
					}
				}
			}

			if ( typeof( wc_pb_bundle_scripts[ bundle_script_id ] ) !== 'undefined' ) {
				wc_pb_bundle_scripts[ bundle_script_id ].shutdown();
			}

			wc_pb_bundle_scripts[ bundle_script_id ] = new WC_PB_Bundle( { $bundle_form: $bundle_form, $bundle_data: $bundle_data, bundle_id: container_id, composite_data: composite_data } );

			$bundle_form.data( 'script_id', bundle_script_id );

			wc_pb_bundle_scripts[ bundle_script_id ].init();
		};

		/*
		 * Initialize form script.
		 */
		$( '.bundle_form .bundle_data' ).each( function() {

			$( this ).wc_pb_bundle_form();
		} );

	} );

} ) ( jQuery );
