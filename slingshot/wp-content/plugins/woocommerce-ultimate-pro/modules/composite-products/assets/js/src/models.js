/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/**
 * Model classes instantiated in a CP app lifecycle.
 */
wc_cp_classes.WC_CP_Models = function( composite ) {

	/**
	 * Composite product data model for storing validation, pricing, availability and quantity data.
	 */
	this.Composite_Data_Model = function( opts ) {

		var Model = Backbone.Model.extend( {

			price_data:  composite.$composite_data.data( 'price_data' ),
			$nyp:        false,

			initialize: function() {

				var params = {
					passes_validation:     true,
					validation_messages:   [],
					status_messages:       [],
					is_in_stock:           true,
					stock_statuses:        [],
					totals:                { price: '', regular_price: '', price_incl_tax: '', price_excl_tax: '' }
				};

				$.each( composite.get_components(), function( index, component ) {
					params[ 'component_' + component.component_id + '_totals' ] = { price: '', regular_price: '', price_incl_tax: '', price_excl_tax: '' };
				} );

				this.set( params );

				// Price suffix data.
				this.price_data.suffix_exists              = wc_composite_params.price_display_suffix !== '';
				this.price_data.suffix_contains_price_incl = wc_composite_params.price_display_suffix.indexOf( '{price_including_tax}' ) > -1;
				this.price_data.suffix_contains_price_excl = wc_composite_params.price_display_suffix.indexOf( '{price_excluding_tax}' ) > -1;

				/**
				 * Update model totals when the selected addons change.
				 */
				composite.actions.add_action( 'component_addons_changed', this.addons_changed_handler, 10, this );

				/**
				 * Update model totals when the nyp price changes.
				 */
				composite.actions.add_action( 'component_nyp_changed', this.nyp_changed_handler, 10, this );

				/**
				 * Update model totals state when a new component quantity is selected.
				 */
				composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 20, this );

				/**
				 * Update model totals state when a new selection is made.
				 */
				composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 30, this );

 				/**
				 * Update totals when the contents of an existing selection change.
 				 */
				composite.actions.add_action( 'component_selection_content_changed', this.selection_content_changed_handler, 30, this );

				/**
				 * Update model availability state in response to component changes.
				 */
				composite.actions.add_action( 'component_availability_changed', this.availability_changed_handler, 10, this );

				/**
				 * Update model validation state when a step validation model state changes.
				 */
				composite.actions.add_action( 'component_validation_message_changed', this.validation_message_changed_handler, 10, this );

				/**
				 * Update a single summary view element price when its totals change.
				 */
				composite.actions.add_action( 'component_totals_changed', this.component_totals_changed_handler, 10, this );

				/**
				 * Update composite totals when a new NYP price is entered at composite level.
				 */
				var $nyp = composite.$composite_data.find( '.nyp' );

				if ( $nyp.length > 0 ) {

					this.$nyp                       = $nyp;
					this.price_data[ 'base_price' ] = $nyp.data( 'price' );

					composite.$composite_data.on( 'woocommerce-nyp-updated-item', { model: this }, function( event ) {

						var model = event.data.model;

						model.price_data[ 'base_price' ]         = model.$nyp.data( 'price' );
						model.price_data[ 'base_regular_price' ] = model.$nyp.data( 'price' );

						model.calculate_totals();
					} );
				}
			},

			/**
			 * Initializes the model and prepares data for consumption by views.
			 */
			init: function() {

				composite.console_log( 'debug:models', '\nInitializing composite data model...' );
				composite.debug_tab_count = composite.debug_tab_count + 2;

				this.update_validation();
				this.update_totals();
				this.update_availability();

				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Updates component totals when an addons change event is triggered.
			 */
			addons_changed_handler: function( component ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_totals( component );
			},

			/**
			 * Updates component totals when a nyp price change event is triggered.
			 */
			nyp_changed_handler: function( component ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_totals( component );
			},

			/**
			 * Updates model totals state.
			 */
			selection_changed_handler: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_totals();
			},

			/**
			 * Updates model availability state.
			 */
			availability_changed_handler: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_availability();
			},

			/**
			 * Updates model totals state.
			 */
			selection_content_changed_handler: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_totals();
			},

			/**
			 * Updates model totals state.
			 */
			quantity_changed_handler: function( component ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.update_totals( component );
			},

			/**
			 * Updates model validation state when the state of a step validation model changes.
			 */
			validation_message_changed_handler: function() {
				this.update_validation();
			},

			// Updates totals when component subtotals change.
			component_totals_changed_handler: function() {
				this.calculate_totals();
			},

			/**
			 * Updates the validation state of the model.
			 */
			update_validation: function() {

				var messages = [];

				if ( this.is_purchasable() ) {
					messages = this.get_validation_messages();
				} else {
					messages.push( wc_composite_params.i18n_unavailable_text );
				}

				composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' validation state... Attribute count: "validation_messages": ' + messages.length + ', Attribute: "passes_validation": ' + ( messages.length === 0 ).toString() );

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { validation_messages: messages, passes_validation: messages.length === 0 } );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Get all validation messages grouped by source. Messages added from the 'Review' step are displayed individually.
			 */
			get_validation_messages: function() {

				var validation_messages = [];

				$.each( composite.get_steps(), function( step_index, step ) {

					var source = step.get_title();

					$.each( step.get_validation_messages( 'composite' ), function( message_index, message ) {

						if ( step.is_review() ) {
							validation_messages.push( { sources: false, content: message.toString() } );
						} else {
							var appended = false;

							if ( validation_messages.length > 0 ) {
								$.each( validation_messages, function( id, msg ) {
									if ( msg.content === message ) {
										var sources_new = msg.sources;
										var content_new = msg.content;
										sources_new.push( source );
										validation_messages[ id ] = { sources: sources_new, content: content_new };
										appended = true;
										return false;
									}
								} );
							}

							if ( ! appended ) {
								validation_messages.push( { sources: [ source ], content: message.toString() } );
							}
						}

					} );

				} );

				var messages = [];

				if ( validation_messages.length > 0 ) {
					$.each( validation_messages, function( id, msg ) {
						if ( msg.sources === false ) {
							messages.push( msg.content );
						} else {
							var sources = wc_cp_join( msg.sources );
							messages.push( wc_composite_params.i18n_validation_issues_for.replace( '%c', sources ).replace( '%e', msg.content ) );
						}
					} );
				}

				// Pass through 'composite_validation_messages' filter - @see WC_CP_Filters_Manager class.
				messages = composite.filters.apply_filters( 'composite_validation_messages', [ messages ] );

				return messages;
			},

			/**
			 * True if the product is purchasable.
			 */
			is_purchasable: function() {

				if ( this.price_data[ 'is_purchasable' ] === 'no' ) {
					return false;
				}

				return true;
			},

			/**
			 * Updates model availability state.
			 */
			update_availability: function() {

				var stock_statuses = [],
					is_in_stock    = true;

				$.each( composite.get_components(), function( index, component ) {
					stock_statuses.push( component.step_validation_model.get( 'is_in_stock' ) );
				} );

				is_in_stock = _.contains( stock_statuses, false ) ? false : true;

				composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' availability... Attribute: "stock_statuses": ' + stock_statuses.toString() + ', Attribute: "is_in_stock": ' + is_in_stock.toString() );

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( {
					stock_statuses: stock_statuses,
					is_in_stock:    is_in_stock,
				} );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Calculates and updates model subtotals.
			 */
			update_totals: function( component ) {

				var model = this;

				composite.console_log( 'debug:models', '\nUpdating \'Composite_Data_Model\' totals...' );

				composite.debug_tab_count = composite.debug_tab_count + 2;

				if ( typeof( component ) === 'undefined' ) {

					$.each( composite.get_components(), function( index, component ) {
						model.update_component_prices( component );
					} );

					this.calculate_subtotals();

				} else {
					this.update_component_prices( component );
					this.calculate_subtotals( component );
				}

				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Adds model subtotals and calculates model totals.
			 */
			calculate_totals: function( price_data_array ) {

				var model                = this,
					price_data           = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array,
					base_price           = Number( price_data[ 'base_price' ] ),
					base_regular_price   = Number( price_data[ 'base_regular_price' ] ),
					base_price_tax_ratio = Number( price_data[ 'base_price_tax' ] ),
					base_price_totals;

				composite.console_log( 'debug:models', '\nAdding totals...' );

				base_price_totals = this.get_taxed_totals( base_price, base_regular_price, base_price_tax_ratio );

				price_data[ 'base_display_price' ] = base_price_totals.price;

				price_data[ 'total' ]              = base_price_totals.price;
				price_data[ 'regular_total' ]      = base_price_totals.regular_price;

				price_data[ 'total_incl_tax' ]     = base_price_totals.price_incl_tax;
				price_data[ 'total_excl_tax' ]     = base_price_totals.price_excl_tax;

				$.each( composite.get_components(), function( index, component ) {

					var component_totals = typeof( price_data_array ) === 'undefined' ? model.get( 'component_' + component.component_id + '_totals' ) : price_data_array[ 'component_' + component.component_id + '_totals' ];

					price_data[ 'total' ]          += component_totals.price;
					price_data[ 'regular_total' ]  += component_totals.regular_price;

					price_data[ 'total_incl_tax' ] += component_totals.price_incl_tax;
					price_data[ 'total_excl_tax' ] += component_totals.price_excl_tax;
				} );

				var totals = {
					price:          price_data[ 'total' ],
					regular_price:  price_data[ 'regular_total' ],
					price_incl_tax: price_data[ 'total_incl_tax' ],
					price_excl_tax: price_data[ 'total_excl_tax' ]
				};

				// Pass through 'composite_totals' filter - @see WC_CP_Filters_Manager class.
				totals = composite.filters.apply_filters( 'composite_totals', [ totals ] );

				if ( typeof( price_data_array ) === 'undefined' ) {
					composite.debug_tab_count = composite.debug_tab_count + 2;
					this.set( { totals: totals } );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				} else {
					return totals;
				}
			},

			/**
			 * Calculates totals by applying tax ratios to raw prices.
			 */
			get_taxed_totals: function( price, regular_price, tax_ratio, qty ) {

				var totals = {
					price:          price,
					regular_price:  regular_price,
					price_incl_tax: price,
					price_excl_tax: price
				};

				if ( tax_ratio > 0 ) {

					if ( wc_composite_params.prices_include_tax === 'yes' ) {
						totals.price_incl_tax = price;
						totals.price_excl_tax = price / tax_ratio;
					} else {
						totals.price_incl_tax = wc_cp_number_round( price * tax_ratio );
						totals.price_excl_tax = price;
					}

					if ( wc_composite_params.tax_display_shop === 'incl' ) {
						totals.price = totals.price_incl_tax;
						if ( wc_composite_params.prices_include_tax === 'yes' ) {
							totals.regular_price = regular_price;
						} else {
							totals.regular_price = wc_cp_number_round( regular_price * tax_ratio );
						}
					} else {
						totals.price = totals.price_excl_tax;
						if ( wc_composite_params.prices_include_tax === 'yes' ) {
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
			},

			/**
			 * Calculates composite subtotals (component totals) and updates the component totals attributes on the model when the calculation is done on the client side.
			 * For components that require a server-side calculation of incl/excl tax totals, a request is prepared and submitted in order to get accurate values.
			 */
			calculate_subtotals: function( triggered_by, price_data_array ) {

				var model      = this,
					price_data = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array;

				triggered_by = typeof( triggered_by ) === 'undefined' ? false : triggered_by;

				$.each( composite.get_components(), function( index, component ) {

					if ( false !== triggered_by && triggered_by.component_id !== component.component_id ) {
						return true;
					}

					var totals        = {
							price:          0.0,
							regular_price:  0.0,
							price_incl_tax: 0.0,
							price_excl_tax: 0.0
						},

						qty           = price_data[ 'quantities' ][ component.component_id ],
						product_id    = component.get_selected_product_type() === 'variable' ? component.get_selected_variation( false ) : component.get_selected_product( false ),

						tax_ratio     = price_data[ 'prices_tax' ][ component.component_id ],

						regular_price = price_data[ 'regular_prices' ][ component.component_id ] + price_data[ 'addons_prices' ][ component.component_id ],
						price         = price_data[ 'prices' ][ component.component_id ] + price_data[ 'addons_prices' ][ component.component_id ];


					composite.console_log( 'debug:models', 'Calculating "' + component.get_title() + '" totals...' );

					if ( wc_composite_params.calc_taxes === 'yes' ) {

						if ( product_id > 0 && qty > 0 && ( price > 0 || regular_price > 0 ) ) {

							totals = model.get_taxed_totals( price, regular_price, tax_ratio, qty );
						}

					} else {

						totals.price          = qty * price;
						totals.regular_price  = qty * regular_price;
						totals.price_incl_tax = qty * price;
						totals.price_excl_tax = qty * price;
					}

					// Pass through 'component_totals' filter - @see WC_CP_Filters_Manager class.
					totals = composite.filters.apply_filters( 'component_totals', [ totals, component ] );

					if ( typeof( price_data_array ) === 'undefined' ) {

						composite.console_log( 'debug:models', 'Updating \'Composite_Data_Model\' component totals... Attribute: "component_' + component.component_id + '_totals".' );

						composite.debug_tab_count = composite.debug_tab_count + 2;
						model.set( 'component_' + component.component_id + '_totals', totals );
						composite.debug_tab_count = composite.debug_tab_count - 2;

					} else {
						price_data[ 'component_' + component.component_id + '_totals' ] = totals;
					}

				} );

				if ( typeof( price_data_array ) !== 'undefined' ) {
					return price_data;
				}
			},

			/**
			 * Updates the 'price_data' model property with the latest component prices.
			 */
			update_component_prices: function( component ) {

				composite.console_log( 'debug:models', 'Fetching "' + component.get_title() + '" price data...' );

				var quantity    = component.get_selected_quantity(),
					custom_data = component.$component_data.data( 'custom' );

				// Copy prices.
				this.price_data[ 'prices' ][ component.component_id ]         = Number( component.$component_data.data( 'price' ) );
				this.price_data[ 'regular_prices' ][ component.component_id ] = Number( component.$component_data.data( 'regular_price' ) );

				this.price_data[ 'prices_tax' ][ component.component_id ]     = 1.0;

				if ( typeof custom_data !== 'undefined' && custom_data[ 'price_tax' ] !== 'undefined' ) {
					this.price_data[ 'prices_tax' ][ component.component_id ] = Number( custom_data[ 'price_tax' ] );
				}

				// Calculate addons price.
				this.price_data[ 'addons_prices' ][ component.component_id ]  = Number( component.component_selection_model.get( 'selected_addons' ) );

				if ( quantity > 0 ) {
					this.price_data[ 'quantities' ][ component.component_id ] = parseInt( quantity );
				} else {
					this.price_data[ 'quantities' ][ component.component_id ] = 0;
				}
			},

			add_status_message: function( source, content ) {

				var messages = $.extend( true, [], this.get( 'status_messages' ) );

				messages.push( { message_source: source, message_content: content } );

				composite.console_log( 'debug:models', 'Adding "' + source + '" status message: "' + content + '"...' );

				this.set( { status_messages: messages } );
			},

			remove_status_message: function( source ) {

				composite.console_log( 'debug:models', 'Removing "' + source + '" status message...' );

				var messages = _.filter( this.get( 'status_messages' ), function( status_message ) { return status_message.message_source !== source; } );

				this.set( { status_messages: messages } );
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

	/**
	 * Validates the configuration state of a step.
	 */
	this.Step_Validation_Model = function( step, opts ) {

		var self  = step;
		var Model = Backbone.Model.extend( {

			initialize: function() {

				var params = {
					passes_validation:  true,
					is_in_stock:        true,
					component_messages: [],
					composite_messages: [],
				};

				this.set( params );

				/**
				 * Re-validate step when quantity is changed.
				 */
				composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 10, this );

				/**
				 * Re-validate step when a new selection is made.
				 */
				composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 20, this );

				/**
				 * Re-validate step when the contents of an existing selection change.
				 */
				composite.actions.add_action( 'component_selection_content_changed', this.selection_content_changed_handler, 20, this );
			},

			quantity_changed_handler: function() {
				if ( composite.is_initialized ) {
					if ( step.step_id === self.step_id ) {
						self.validate();
					}
				}
			},

			selection_changed_handler: function() {
				if ( composite.is_initialized ) {
					self.validate();
				}
			},

			selection_content_changed_handler: function( step ) {
				if ( composite.is_initialized ) {
					if ( step.step_id === self.step_id ) {
						self.validate();
					}
				}
			},

			update: function( is_valid, is_in_stock ) {

				var params = {
					passes_validation:  is_valid,
					is_in_stock:        is_in_stock,
					component_messages: self.get_validation_messages( 'component' ),
					composite_messages: self.get_validation_messages( 'composite' )
				};

				composite.console_log( 'debug:models', '\nUpdating \'Step_Validation_Model\': "' + self.get_title() + '", Attribute: "passes_validation": ' + params.passes_validation.toString() + ', Attribute: "is_in_stock": ' + params.is_in_stock.toString() );

				if ( this.get( 'passes_validation' ) !== params.passes_validation ) {
					composite.console_log( 'debug:models', 'Validation state changed.\n' );
				} else {
					composite.console_log( 'debug:models', 'Validation state unchanged.\n' );
				}

				if ( ! _.isEqual( this.get( 'component_messages' ), params.component_messages ) ) {
					composite.console_log( 'debug:models', 'Validation message changed.\n' );
				} else {
					composite.console_log( 'debug:models', 'Validation message unchanged.\n' );
				}

				if ( this.get( 'is_in_stock' ) !== params.is_in_stock ) {
					composite.console_log( 'debug:models', 'Stock state changed.\n' );
				} else {
					composite.console_log( 'debug:models', 'Stock state unchanged.\n' );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( params );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

	/**
	 * Controls permission for access to a step.
	 */
	this.Step_Access_Model = function( step, opts ) {

		var self  = step;
		var Model = Backbone.Model.extend( {

			is_lockable: false,

			initialize: function() {

				var model  = this,
					params = {
					is_locked: false,
				};

				this.set( params );

				/*
				 * Permit lock state changes only if:
				 *
				 * - Layout !== 'Stacked'.
				 * - Layout !== 'Componentized', or 'composite_settings.sequential_componentized_progress' === 'yes'.
				 * - Layout === 'Componentized', 'composite_settings.sequential_componentized_progress' === 'yes' and this is not the Review step.
				 */
				this.is_lockable = composite.settings.layout !== 'single' && ( composite.settings.layout_variation !== 'componentized' || composite.settings.sequential_componentized_progress === 'yes' && false === self.is_review() );

				if ( this.is_lockable ) {

					$.each( composite.get_steps(), function( index, step ) {

						if ( step.is_review() ) {
							return true;
						}

						if ( step.step_index < self.step_index ) {
							// Update lock state when the validation state of a previous step changes.
							model.listenTo( step.step_validation_model, 'change:passes_validation', model.update_lock_state );
							// Update lock state when the lock state of a previous step changes.
							model.listenTo( step.step_access_model, 'change:is_locked', model.update_lock_state );
						}

					} );
				}

				/**
				 * Lock state also changes according to own step visibility.
				 */
				this.listenTo( self.step_visibility_model, 'change:is_visible', this.update_lock_state );
			},

			update_lock_state: function() {

				var lock = false;

				if ( false === self.is_visible() ) {
					lock = true;
				} else if ( this.is_lockable ) {

					$.each( composite.get_steps(), function( index, step ) {

						if ( step.step_index === self.step_index ) {
							return false;
						}

						if ( false === step.is_visible() ) {
							return true;
						}

						if ( step.step_access_model.get( 'is_locked' ) ) {
							lock = true;
							return false;
						} else if ( false === step.step_validation_model.get( 'passes_validation' ) ) {
							lock = true;
							return false;
						}

					} );
				}

				composite.console_log( 'debug:models', '\nUpdating \'Step_Access_Model\': "' + self.get_title() + '", Attribute: "is_locked": ' + lock.toString() );

				if ( this.get( 'is_locked' ) !== lock ) {
					composite.console_log( 'debug:models', 'Lock state changed.\n' );
				} else {
					composite.console_log( 'debug:models', 'Lock state unchanged.\n' );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { is_locked: lock } );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

	/**
	 * Controls the visibility of a Component.
	 */
	this.Step_Visibility_Model = function( step, opts ) {

		var self = step;

		/**
		 * Controls the visibility state of a component.
		 */
		var Model = Backbone.Model.extend( {

			initialize: function() {

				var params = {
					is_visible: true,
				};

				this.set( params );

				if ( self.is_component() ) {
					/**
					 * Update model state when the active scenarios change.
					 */
					composite.actions.add_action( 'active_scenarios_changed', this.update_visibility_state, 5, this );
				}
			},

			update_visibility_state: function() {

				var scenarios        = composite.scenarios.get_active_scenarios(),
					active_scenarios = composite.scenarios.filter_scenarios_by_type( scenarios, 'conditional_components' ),
					is_visible       = true;

				composite.console_log( 'debug:models', '\nUpdating "' + self.get_title() + '" visibility...' );

				composite.debug_tab_count = composite.debug_tab_count + 2;

				composite.console_log( 'debug:models', 'Active "Hide Components" Scenarios: [' + active_scenarios + ']' );

				// Get conditional components data.
				var conditional_components = composite.scenarios.get_scenario_data().scenario_settings.conditional_components;

				// Find if the component is hidden in the active scenarios.
				if ( active_scenarios.length > 0 && typeof( conditional_components ) !== 'undefined' ) {

					// Set hide status.
					$.each( conditional_components, function( scenario_id, hidden_components ) {

						if ( _.contains( active_scenarios, scenario_id.toString() ) ) {
							if ( _.contains( hidden_components, self.component_id.toString() ) ) {
								is_visible = false;
							}
						}
					} );
				}

				composite.console_log( 'debug:models', '\nUpdating \'Step_Visibility_Model\': "' + self.get_title() + '", Attribute: "is_visible": ' + is_visible.toString() );

				if ( this.get( 'is_visible' ) !== is_visible ) {
					composite.console_log( 'debug:models', 'Visibility state changed.\n' );
				} else {
					composite.console_log( 'debug:models', 'Visibility state unchanged.\n' );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { is_visible: is_visible } );
				composite.debug_tab_count = composite.debug_tab_count - 2;

				composite.debug_tab_count = composite.debug_tab_count - 2;
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

	/**
	 * Sorting, filtering and pagination data and methods associated with the available component options.
	 */
	this.Component_Options_Model = function( component, opts ) {

		var self  = component;
		var Model = Backbone.Model.extend( {

			available_options_data: [],
			xhr: false,

			initialize: function() {

				this.available_options_data = self.find_options_data();

				var available_options = [];

				if ( this.available_options_data.length > 0 ) {
					available_options = _.pluck( this.available_options_data, 'option_id' );
				}

				var params = {
					filters: self.find_active_filters(),
					orderby: self.find_order_by(),
					page:    self.find_pagination_param( 'page' ),
					pages:   self.find_pagination_param( 'pages' ),

					/*
					 * Available component options, including the current selection, but excluding the empty '' option.
					 */
					available_options: available_options,

					/*
					 * Active (valid) products and variations, including the current selection and the empty '' option.
					 */
					active_options: available_options.slice(),

					/*
					 * Products and variations state in current view, including the empty '' option. The current selection is excluded if not in view.
					 */
					options_state: { active: _.pluck( _.where( this.available_options_data, { is_in_view: true } ), 'option_id' ), inactive: [] },

					/**
					 * 'compat_group' scenarios when the options state was calculated.
					 */
					options_in_scenarios: composite.scenarios.clean_masked_scenarios( composite.scenarios.get_scenarios_by_type( 'compat_group' ), self.component_id )
				};

				this.set( params );

				if ( composite.settings.layout === 'single' ) {

					/**
					 * Update the 'active_options' attribute when the active scenarios are updated.
					 */
					composite.actions.add_action( 'active_scenarios_updated', this.refresh_options_state, 10, this );

				} else {

					/**
					 * Update the 'active_options' attribute when the active scenarios change.
					 */
					composite.actions.add_action( 'active_scenarios_changed', this.refresh_options_state, 10, this );

					/**
					 * Ensure options state is refreshed when the available options of this component change.
					 */
					composite.actions.add_action( 'available_options_changed_' + self.step_id, this.available_options_changed_handler, 10, this );

					/*
					 * Reset invalid product/variation selections when transitioning to this step.
					 */
					composite.actions.add_action( 'active_step_changed_' + self.step_id, this.active_step_changed_handler, 10, this );
				}

			},

			active_step_changed_handler: function() {

				this.refresh_options_state( self );
			},

			available_options_changed_handler: function() {

				this.refresh_options_state( self );
			},

			reload_options_on_scenarios_change: function() {

				var reload = false;

				if ( self.hide_disabled_products() && self.has_options_style( 'thumbnails' ) ) {
					if ( self.get_max_results() > self.get_results_per_page() ) {
						if ( false === self.append_results() ) {
							reload = true;
						} else if ( _.pluck( _.where( this.available_options_data, { is_in_view: true } ), 'option_id' ).length < self.get_max_results() ) {
							reload = true;
						}
					}
				}

				return reload;
			},

			request_options: function( params, request_type ) {

				var model = this;

				// Page will be updated after data has been fetched.
				this.set( _.omit( params, 'page' ) );

				var data = {
					action:               'woocommerce_show_component_options',
					component_id:         self.component_id,
					composite_id:         composite.composite_id,
					load_page:            params.page ? params.page : 1,
					selected_option:      self.get_selected_product( false ),
					filters:              this.get( 'filters' ),
					orderby:              this.get( 'orderby' ),
					options_in_scenarios: this.reload_options_on_scenarios_change() ? this.get( 'options_in_scenarios' ) : [],
				};

				if ( this.xhr ) {
					this.xhr.abort();
				}

				// Get component options via ajax.
				this.xhr = $.post( composite.get_ajax_url( data.action ), data, function( response ) {

					// Trigger 'component_options_data_loaded' event.
					model.trigger( 'component_options_data_loaded', response, request_type );

					if ( 'success' === response.result ) {

						if ( 'reload' === request_type ) {

							// Update component options data.
							model.available_options_data = response.options_data;

							// Update component scenario data.
							composite.scenarios.set_component_scenario_data( self.component_id, response.scenario_data );

							// Update component pagination data.
							model.set( response.pagination_data );

							// Update available options.
							model.refresh_options( _.pluck( model.available_options_data, 'option_id' ) );

						} else if ( 'append' === request_type ) {

							// Merge existing with new component options data, after adding an 'is_appended' prop to the new data.
							model.available_options_data = _.union( _.where( model.available_options_data, { is_in_view: true } ), _.map( response.options_data, function( option_data ) { return _.extend( option_data, { is_appended: true } ); } ) );

							// Merge component scenario data.
							composite.scenarios.merge_component_scenario_data( self.component_id, response.scenario_data );

							// Update component pagination data.
							model.set( response.pagination_data );

							// Update available options.
							model.refresh_options( _.pluck( model.available_options_data, 'option_id' ) );

							// Remove 'is_appended' prop from appended data.
							model.available_options_data = _.map( model.available_options_data, function( option_data ) { return _.omit( option_data, 'is_appended' ); } );
						}

					} else {
						window.alert( response.message );
					}

					// Run 'component_options_loaded' action - @see WC_CP_Actions_Dispatcher class reference.
					composite.actions.do_action( 'component_options_loaded', [ self ] );

				}, 'json' );
			},

			refresh_options: function( options ) {

				composite.console_log( 'debug:models', '\nUpdating \'Component_Options_Model\': "' + self.get_title() + '", Attribute: "available_options": ' + _.map( options, function( num ) { return num === '' ? '0' : num; } ) );

				composite.debug_tab_count = composite.debug_tab_count + 2;

				if ( _.isEqual( this.get( 'available_options' ), options ) ) {
					// Refresh options state if options have been refreshed but the new set is equal to the old: Edge case fix for when the 'is_in_view' property of an existing option changes in the new set.
					this.refresh_options_state( self );
				} else {
					this.set( { available_options: options } );
				}
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			refresh_options_state: function( triggered_by ) {

				/*
				 * 1. Update active options.
				 */

				composite.console_log( 'debug:models', '\nUpdating \'Component_Options_Model\': "' + self.get_title() + '", Attribute: "active_options"...' );

				composite.debug_tab_count = composite.debug_tab_count + 2;

				var active_options          = [],
					active_scenarios        = [],
					options_state           = { active: [], inactive: [] },
					triggered_by_index      = triggered_by.step_index,
					component_id            = self.component_id,
					scenario_data           = composite.scenarios.get_scenario_data().scenario_data,
					item_scenario_data      = scenario_data[ component_id ],
					excl_current            = false,
					up_to_current           = false,
					is_optional             = false,
					invalid_product_found   = false,
					invalid_variation_found = false;

				if ( triggered_by.is_review() ) {
					triggered_by_index = 1000;
				}

				// "Non-Blocking" behaviour === A selection in the Nth component constrains only the active options of the components that follow it.

				// The constraints of the firing item must not be taken into account in paged modes.
				excl_current  = true;
				// No need to look further than the current item.
				up_to_current = true;

				// Now go get them, boy: Get active scenarios filtered by action = 'compat_group'.
				active_scenarios = composite.scenarios.filter_scenarios_by_type( composite.scenarios.calculate_active_scenarios( self, up_to_current, excl_current ), 'compat_group' );

				composite.console_log( 'debug:models', '\nReference scenarios: [' + active_scenarios + ']' );
				composite.console_log( 'debug:models', 'Removing any scenarios where the current component is masked...' );

				active_scenarios = composite.scenarios.clean_masked_scenarios( active_scenarios, component_id );

				// Enable all if all active scenarios ignore this component.
				if ( active_scenarios.length === 0 ) {
					active_scenarios.push( '0' );
				}

				composite.console_log( 'debug:models', '\nUpdating \'Component_Options_Model\': "' + self.get_title() + '", Attribute: "options_in_scenarios"...' );

				if ( ! _.isEqual( this.get( 'options_in_scenarios' ), active_scenarios ) ) {
					composite.console_log( 'debug:models', '\nActive options scenarios changed - [' + this.get( 'options_in_scenarios' ) + '] => [' + active_scenarios + '].\n' );
				} else {
					composite.console_log( 'debug:models', '\nActive options scenarios unchanged - [' + this.get( 'options_in_scenarios' ) + '] => [' + active_scenarios + '].\n' );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { options_in_scenarios: active_scenarios } );
				composite.debug_tab_count = composite.debug_tab_count - 2;

				/*
				 * Set component 'optional' status by adding the '' product ID to the 'active_options' array.
				 */

				if ( 0 in item_scenario_data ) {

					var optional_in_scenarios = item_scenario_data[ 0 ];

					for ( var s = 0; s < optional_in_scenarios.length; s++ ) {

						var optional_in_scenario_id = optional_in_scenarios[ s ];

						if ( $.inArray( optional_in_scenario_id, active_scenarios ) > -1 ) {
							is_optional = true;
							break;
						}
					}

					options_state.inactive.push( '' );

				} else if ( false === self.is_visible() ) {
					is_optional = true;
				}

				if ( is_optional ) {
					composite.console_log( 'debug:models', 'Component set as optional.' );
					active_options.push( '' );
					options_state.active.push( '' );
				}

				/*
				 * Add compatible products to the 'active_options' array.
				 */
				$.each( this.available_options_data, function( index, option_data ) {

					var product_id           = option_data.option_id,
						product_in_scenarios = ( product_id in item_scenario_data ) ? item_scenario_data[ product_id ] : [],
						is_compatible        = false;

					composite.console_log( 'debug:models', 'Updating selection #' + product_id + ':' );
					composite.console_log( 'debug:models', '	Selection in scenarios: [' + product_in_scenarios + ']' );

					for ( var i = 0; i < product_in_scenarios.length; i++ ) {

						var scenario_id = product_in_scenarios[ i ];

						if ( $.inArray( scenario_id, active_scenarios ) > -1 ) {
							is_compatible = true;
							break;
						}
					}

					if ( is_compatible ) {
						composite.console_log( 'debug:models', '	Selection enabled.' );
						active_options.push( product_id );

						if ( option_data.is_in_view ) {
							options_state.active.push( product_id );
						}

					} else {

						composite.console_log( 'debug:models', '	Selection disabled.' );

						if ( option_data.is_in_view ) {
							options_state.inactive.push( product_id );
						}

						if ( self.get_selected_product( false ) === product_id ) {

							invalid_product_found = true;

							if ( invalid_product_found ) {
								composite.console_log( 'debug:models', '	--- Selection invalid.' );
							}
						}
					}
				} );

				/*
				 * Disable incompatible variations.
				 */

				if ( self.get_selected_product_type() === 'variable' ) {

					var variation_input_id = self.get_selected_variation(),
						product_variations = self.$component_data.data( 'product_variations' );

					composite.console_log( 'debug:models', '	Checking variations...' );

					if ( variation_input_id > 0 ) {
						composite.console_log( 'debug:models', '		--- Stored variation is #' + variation_input_id );
					}

					for ( var i = 0; i < product_variations.length; i++ ) {

						var variation_id           = product_variations[ i ].variation_id,
							variation_in_scenarios = ( variation_id in item_scenario_data ) ? item_scenario_data[ variation_id ] : [],
							is_compatible          = false;

						composite.console_log( 'debug:models', '		Checking variation #' + variation_id + ':' );
						composite.console_log( 'debug:models', '		Selection in scenarios: [' + variation_in_scenarios + ']' );

						for ( var k = 0; k < variation_in_scenarios.length; k++ ) {

							var scenario_id = variation_in_scenarios[ k ];

							if ( $.inArray( scenario_id, active_scenarios ) > -1 ) {
								is_compatible = true;
								break;
							}
						}

						if ( is_compatible ) {
							composite.console_log( 'debug:models', '		Variation enabled.' );
							active_options.push( variation_id.toString() );
							options_state.active.push( variation_id.toString() );
						} else {
							composite.console_log( 'debug:models', '		Variation disabled.' );
							options_state.inactive.push( variation_id.toString() );

							if ( self.get_selected_variation( false ).toString() === variation_id.toString() ) {

								invalid_variation_found = true;

								if ( invalid_variation_found ) {
									composite.console_log( 'debug:models', '		--- Selection invalid.' );
								}
							}
						}
					}
				}

				composite.console_log( 'debug:models', 'Done.\n' );

				composite.debug_tab_count = composite.debug_tab_count - 2;

				if ( ! _.isEqual( this.get( 'active_options' ), active_options ) ) {
					composite.console_log( 'debug:models', '\nActive options changed - [' + _.map( this.get( 'active_options' ), function( num ) { return num === '' ? '0' : num; } ) + '] => [' + _.map( active_options, function( num ) { return num === '' ? '0' : num; } ) + '].\n' );
				} else {
					composite.console_log( 'debug:models', '\nActive options unchanged - [' + _.map( this.get( 'active_options' ), function( num ) { return num === '' ? '0' : num; } ) + '] => [' + _.map( active_options, function( num ) { return num === '' ? '0' : num; } ) + '].\n' );
				}

				// Set active options.
				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { active_options: active_options } );
				composite.debug_tab_count = composite.debug_tab_count - 2;


				if ( ! _.isEqual( this.get( 'options_state' ), options_state ) ) {
					composite.console_log( 'debug:models', '\nOptions state changed.\n' );
				} else {
					composite.console_log( 'debug:models', '\nOptions state unchanged.\n' );
				}

				// Set active options in view.
				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { options_state: options_state } );
				composite.debug_tab_count = composite.debug_tab_count - 2;

				/*
				 * 2. Check selections.
				 */

				if ( composite.settings.layout === 'single' || self.is_current() ) {

					composite.console_log( 'debug:models', '\nChecking current "' + self.get_title() + '" selections:' );

					if ( invalid_product_found ) {

						composite.console_log( 'debug:models', '\nProduct selection invalid.\n\n' );

					} else if ( invalid_variation_found ) {

						composite.console_log( 'debug:models', '\nVariation selection invalid - resetting...\n\n' );

						composite.debug_tab_count = composite.debug_tab_count + 2;

						self.component_selection_view.resetting_variation = true;
						self.$component_summary_content.find( '.reset_variations' ).trigger( 'click' );
						self.component_selection_view.resetting_variation = false;

						// Force a re-draw.
						self.component_options_view.render();

						composite.debug_tab_count = composite.debug_tab_count - 2;

					} else {
						composite.console_log( 'debug:models', '...looking good!' );
					}
				}
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

	/**
	 * Data and methods associated with the current selection.
	 */
	this.Component_Selection_Model = function( component, opts ) {

		var self  = component;
		var Model = Backbone.Model.extend( {

			selected_product:              '',

			selected_variation_data:       '',
			selected_product_image_data:   false,
			selected_variation_image_data: false,

			initialize: function() {

				var selected_product = '';

				if ( self.component_options_model.available_options_data.length > 0 ) {
					$.each( self.component_options_model.available_options_data, function( index, option_data ) {
						if ( option_data.is_selected ) {
							selected_product = option_data.option_id;
							return false;
						}
					} );
				}

				var params = {
					selected_product:      selected_product,
					selected_variation:    self.find_selected_product_param( 'variation_id' ),
					selected_quantity:     self.find_selected_product_param( 'quantity' ),
					// Addons only identified by price.
					selected_addons:       0.0,
					// NYP identified by price.
					selected_nyp:          0.0,
				};

				this.selected_product              = params.selected_product;
				this.selected_product_image_data   = self.find_selected_product_param( 'product_image_data' );
				this.selected_variation_image_data = self.find_selected_product_param( 'variation_image_data' );

				this.set( params );
			},

			request_details: function( product_id ) {

				var model = this;

				var data  = {
					action:        'woocommerce_show_composited_product',
					product_id:    product_id,
					component_id:  self.component_id,
					composite_id:  composite.composite_id
				};

				// Get component selection details via ajax.
				$.ajax( {

					type:     'POST',
					url:      composite.get_ajax_url( data.action ),
					data:     data,
					timeout:  15000,
					dataType: 'json',

					success: function( response ) {

						model.selected_product = product_id;
						model.trigger( 'component_selection_details_loaded', response );
					},

					error: function() {

						model.selected_product = self.get_selected_product( false );
						model.trigger( 'component_selection_details_load_error' );
					}

				} );
			},

			update_selected_product: function() {

				var attr_msg                  = '',
					selected_variation_id     = self.find_selected_product_param( 'variation_id' ),
					selected_qty              = self.find_selected_product_param( 'quantity' ),
					updating_product          = this.get( 'selected_product' ) !== this.selected_product,
					updating_variation        = this.get( 'selected_variation' ) !== selected_variation_id,
					updating_qty              = this.get( 'selected_quantity' ) !== selected_qty;

				if ( updating_product ) {

					this.selected_product_image_data = self.find_selected_product_param( 'product_image_data' );

					attr_msg = 'Attribute: "selected_product": #' + ( this.selected_product === '' ? '0' : this.selected_product );
				}

				if ( updating_variation ) {

					if ( selected_variation_id > 0 ) {

						this.selected_variation_data       = self.find_selected_product_param( 'variation_data' );
						this.selected_variation_image_data = self.find_selected_product_param( 'variation_image_data' );

					} else {
						this.selected_variation_data       = '';
						this.selected_variation_image_data = false;
					}

					attr_msg = attr_msg + ( attr_msg !== '' ? ', ' : '' ) + 'Attribute: "selected_variation": #' + ( selected_variation_id === '' ? '0' : selected_variation_id );
				}

				if ( updating_qty ) {
					attr_msg = attr_msg + ( attr_msg !== '' ? ', ' : '' ) + 'Attribute: "selected_quantity": ' + selected_qty;
				}

				if ( updating_product || updating_variation || updating_qty ) {
					composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '": ' + attr_msg );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( {
					selected_product:   this.selected_product,
					selected_variation: selected_variation_id,
					selected_quantity:  selected_qty,
					selected_addons:    0.0,
					selected_nyp:       0.0
				} );
				composite.debug_tab_count = composite.debug_tab_count - 2;

				this.trigger( 'selected_product_updated', this );
			},

			update_selected_variation: function() {

				var selected_variation_id = self.find_selected_product_param( 'variation_id' );

				if ( this.get( 'selected_variation' ) !== selected_variation_id ) {

					if ( selected_variation_id > 0 ) {

						this.selected_variation_data       = self.find_selected_product_param( 'variation_data' );
						this.selected_variation_image_data = self.find_selected_product_param( 'variation_image_data' );

					} else {
						this.selected_variation_data       = '';
						this.selected_variation_image_data = false;
					}

					composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_variation": #' + ( selected_variation_id === '' ? '0' : selected_variation_id ) );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { selected_variation: selected_variation_id } );
				composite.debug_tab_count = composite.debug_tab_count - 2;

				this.trigger( 'selected_variation_updated', this );
			},

			update_selected_quantity: function() {

				var selected_qty = self.find_selected_product_param( 'quantity' );

				if ( this.get( 'selected_quantity' ) !== selected_qty ) {
					composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_quantity": ' + selected_qty );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { selected_quantity: selected_qty } );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			update_selected_addons: function() {

				var selected_addons_price = self.find_selected_product_param( 'addons_price' );

				if ( this.get( 'selected_addons' ) !== selected_addons_price ) {
					composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "selected_addons_price": ' + selected_addons_price );
				}

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { selected_addons: selected_addons_price } );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			update_nyp: function() {

				var nyp_price = self.find_selected_product_param( 'nyp_price' );

				if ( this.get( 'selected_nyp' ) !== nyp_price ) {
					composite.console_log( 'debug:models', '\nUpdating \'Component_Selection_Model\': "' + self.get_title() + '", Attribute: "nyp_price": ' + nyp_price );
				}

				self.$component_data.data( 'price', nyp_price );
				self.$component_data.data( 'regular_price', nyp_price );

				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.set( { selected_nyp: nyp_price } );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			}

		} );

		var obj = new Model( opts );
		return obj;
	};

};
