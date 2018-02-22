/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/**
 * View classes instantiated in a CP app lifecycle.
 */
wc_cp_classes.WC_CP_Views = function( composite ) {

	/**
	 * View that handles the display of simple status messages.
	 */
	this.Composite_Status_View = function( opts ) {

		var View = Backbone.View.extend( {

			is_active: false,
			template: false,

			worker: false,

			$el_content: false,

			initialize: function( options ) {

				var view = this;

				this.template    = wp.template( 'wc_cp_composite_status' );
				this.$el_content = options.$el_content;

				/**
			 	 * Update the view when its model state changes.
				 */
				this.listenTo( this.model, 'change:status_messages', this.status_changed );

				var Worker = function() {

					var worker = this;

					this.timer = false;
					this.tasks = [];

					this.last_added_task = [];

					this.is_idle = function() {
						return this.timer === false;
					};

					this.work = function() {
						if ( worker.tasks.length > 0 ) {
							var task = worker.tasks.shift();
							view.render( task );
							worker.timer = setTimeout( function() { worker.work(); }, 400 );
						} else {
							clearTimeout( worker.timer );
							worker.timer = false;
						}
					};

					this.add_task = function( messages ) {

						var task = [];

						// Message added...
						if ( _.pluck( _.where( this.last_added_task, { is_old: false } ), 'message_content' ).length < messages.length ) {
							task = _.map( messages, function( message ) { return { message_content: message, is_old: false }; } );
						// Message removed...
						} else {
							task = _.map( _.where( this.last_added_task, { is_old: false } ), function( data ) { return { message_content: data.message_content, is_old: false === _.contains( messages, data.message_content ) }; } );
						}

						this.last_added_task = task;
						this.tasks.push( task );

						if ( _.where( task, { is_old: true } ).length === task.length ) {
							this.tasks.push( [] );
						}
					};

				};

				this.worker = new Worker();
			},

			/**
			 * Renders the status box.
			 */
			render: function( messages ) {

				var view = this;

				if ( messages.length === 0 ) {

					composite.console_log( 'debug:views', '\nHiding composite status view...' );

					this.$el.removeClass( 'visible' );

					setTimeout( function() {
						view.$el.removeClass( 'active' );
					}, 200 );

					this.is_active = false;

				} else {

					composite.console_log( 'debug:views', '\nUpdating composite status view...' );

					this.$el_content.html( this.template( messages ) );

					if ( false === this.is_active ) {

						this.$el.addClass( 'active' );

						setTimeout( function() {
							view.$el.addClass( 'visible' );
						}, 5 );

						this.is_active = true;

					} else {
						setTimeout( function() {
							view.$el.find( '.message:not(.current)' ).addClass( 'old' );
						}, 100 );
					}
				}
			},

			status_changed: function() {

				var	messages = this.model.get( 'status_messages' );

				if ( messages.length > 0 ) {
					this.worker.add_task( _.pluck( messages, 'message_content' ) );
				} else {
					this.worker.add_task( [] );
				}

				if ( this.worker.is_idle() ) {
					this.worker.work();
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * Handles the display of composite validation messages.
	 */
	this.Composite_Validation_View = function( opts ) {

		var View = Backbone.View.extend( {

			render_timer: false,
			is_in_widget: false,
			template:     false,

			initialize: function( options ) {

				this.template     = wp.template( 'wc_cp_validation_message' );
				this.is_in_widget = options.is_in_widget;

				/**
				 * Update the view when the validation messages change.
				 */
				composite.actions.add_action( 'composite_validation_message_changed', this.render, 100, this );
			},

			render: function() {

				var view  = this,
					model = this.model;

				composite.console_log( 'debug:views', '\nScheduled update of composite validation view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

				clearTimeout( view.render_timer );
				view.render_timer = setTimeout( function() {
					view.render_task( model );
				}, 10 );
			},

			render_task: function( model ) {

				composite.console_log( 'debug:views', '\nUpdating composite validation view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				var messages = model.get( 'validation_messages' );

				if ( false === model.get( 'passes_validation' ) && messages.length > 0 ) {

					this.$el.html( this.template( messages ) );
					this.$el.removeClass( 'inactive' ).slideDown( 200 );

				} else {
					this.$el.addClass( 'inactive' ).slideUp( 200 );
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the price template.
	 */
	this.Composite_Price_View = function( opts ) {

		var View = Backbone.View.extend( {

			render_timer:   false,
			is_in_widget:   false,
			$addons_totals: false,
			suffix:         '',

			suffix_contains_price_incl: false,
			suffix_contains_price_excl: false,

			refreshing_addons_totals: false,

			initialize: function( options ) {

				this.is_in_widget = options.is_in_widget;

				// Add-ons support.
				if ( ! this.is_in_widget ) {

					var $addons_totals = composite.$composite_data.find( '#product-addons-total' );

					if ( $addons_totals.length > 0 ) {

						this.$addons_totals = $addons_totals;

						/**
						 * Update the addons totals when the composite totals change.
						 */
						composite.actions.add_action( 'composite_totals_changed', this.update_addons_totals, 100, this );

						/**
						 * Update addons grand totals with correct prices without triggering an ajax call.
						 */
						composite.$composite_data.on( 'updated_addons', { view: this }, this.updated_addons_handler );

						this.$el.after( $addons_totals );
					}
				}

				// Suffix.
				if ( wc_composite_params.price_display_suffix !== '' ) {
					this.suffix = ' <small class="woocommerce-price-suffix">' + wc_composite_params.price_display_suffix + '</small>';

					this.suffix_contains_price_incl = wc_composite_params.price_display_suffix.indexOf( '{price_including_tax}' ) > -1;
					this.suffix_contains_price_excl = wc_composite_params.price_display_suffix.indexOf( '{price_excluding_tax}' ) > -1;
				}

				/**
				 * Update the view when the composite totals change.
				 */
				composite.actions.add_action( 'composite_totals_changed', this.render, 100, this );

				/**
				 * Update the view when the validation messages change.
				 */
				composite.actions.add_action( 'composite_validation_message_changed', this.render, 100, this );
			},

			/**
			 * Populate prices used by the addons script and re-trigger a 'woocommerce-product-addons-update' event.
			 */
			updated_addons_handler: function( event ) {

				var view = event.data.view;

				if ( false === view.refreshing_addons_totals && view.model.get( 'passes_validation' ) ) {

					var composite_totals = view.model.get( 'totals' ),
						addons_tax_diff  = 0;

					view.refreshing_addons_totals = true;

					view.$addons_totals.data( 'price', composite_totals.price );
					view.$addons_totals.data( 'raw-price', composite_totals.price );

					if ( wc_composite_params.calc_taxes === 'yes' ) {
						if ( wc_composite_params.tax_display_shop === 'incl' ) {

							if ( wc_composite_params.prices_include_tax === 'yes' ) {
								addons_tax_diff = view.$addons_totals.data( 'addons-price' ) * ( 1 - 1 / view.model.price_data[ 'base_price_tax' ] );
							}

							view.$addons_totals.data( 'raw-price', composite_totals.price_excl_tax - addons_tax_diff );
							view.$addons_totals.data( 'tax-mode', 'excl' );

						} else {

							if ( wc_composite_params.prices_include_tax === 'no' ) {
								addons_tax_diff = view.$addons_totals.data( 'addons-price' ) * ( 1 - view.model.price_data[ 'base_price_tax' ] );
							}

							view.$addons_totals.data( 'raw-price', composite_totals.price_incl_tax - addons_tax_diff );
							view.$addons_totals.data( 'tax-mode', 'incl' );
						}
					}

					composite.$composite_data.trigger( 'woocommerce-product-addons-update' );

					view.refreshing_addons_totals = false;
				}
			},

			/**
			 * Prevent addons ajax call, since composite container-level tax does not apply to entire contents.
			 */
			update_addons_totals: function() {

				if ( false !== this.$addons_totals ) {

					// Ensure addons ajax is not triggered at this point.
					this.$addons_totals.data( 'price', 0 );
					this.$addons_totals.data( 'raw-price', 0 );

					composite.$composite_data.trigger( 'woocommerce-product-addons-update' );
				}
			},

			get_price_html: function( price_data_array ) {

				var model            = this.model,
					price_data       = typeof( price_data_array ) === 'undefined' ? model.price_data : price_data_array,
					composite_totals = typeof( price_data_array ) === 'undefined' ? model.get( 'totals' ) : price_data_array[ 'totals' ],
					price_html       = '';

				if ( composite_totals.price === 0.0 && price_data[ 'show_free_string' ] === 'yes' ) {
					price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span>' + wc_composite_params.i18n_free + '</p>';
				} else {

					var formatted_price         = wc_cp_woocommerce_number_format( wc_cp_number_format( composite_totals.price ) ),
						formatted_regular_price = wc_cp_woocommerce_number_format( wc_cp_number_format( composite_totals.regular_price ) ),
						formatted_suffix        = '',
						formatted_price_incl    = '',
						formatted_price_excl    = '';

					if ( this.suffix !== '' ) {

						formatted_suffix = this.suffix;

						if ( this.suffix_contains_price_incl ) {
							formatted_price_incl = '<span class="amount">' + wc_cp_woocommerce_number_format( wc_cp_number_format( price_data[ 'total_incl_tax' ] ) ) + '</span>';
							formatted_suffix     =  formatted_suffix.replace( '{price_including_tax}', formatted_price_incl );
						}

						if ( this.suffix_contains_price_excl ) {
							formatted_price_excl = '<span class="amount">' + wc_cp_woocommerce_number_format( wc_cp_number_format( price_data[ 'total_excl_tax' ] ) ) + '</span>';
							formatted_suffix     =  formatted_suffix.replace( '{price_excluding_tax}', formatted_price_excl );
						}
					}

					if ( composite_totals.regular_price > composite_totals.price ) {
						price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span><del>' + formatted_regular_price + '</del> <ins>' + formatted_price + '</ins>' + formatted_suffix + '</p>';
					} else {
						price_html = '<p class="price"><span class="total">' + wc_composite_params.i18n_total + '</span>' + formatted_price + formatted_suffix + '</p>';
					}
				}

				return composite.filters.apply_filters( 'composite_price_html', [ price_html, this, price_data_array ] );
			},

			render: function() {

				var view  = this,
					model = this.model;

				composite.console_log( 'debug:views', '\nScheduled update of composite price view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

				clearTimeout( view.render_timer );
				view.render_timer = setTimeout( function() {
					view.render_task( model );
				}, 10 );
			},

			render_task: function( model ) {

				var price_html;

				composite.console_log( 'debug:views', '\nUpdating composite price view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				if ( model.get( 'passes_validation' ) && ( model.price_data[ 'total' ] !== model.price_data[ 'base_display_price' ] || 'yes' === model.price_data[ 'has_price_range' ] ) ) {

					price_html = this.get_price_html();

					this.$el.html( price_html );
					this.$el.removeClass( 'inactive' ).slideDown( 200 );

				} else {
					this.$el.addClass( 'inactive' ).slideUp( 200 );
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the availability status.
	 */
	this.Composite_Availability_View = function( opts ) {

		var View = Backbone.View.extend( {

			$composite_stock_status: false,
			is_in_widget:            false,
			render_timer:            false,

			initialize: function( options ) {

				this.is_in_widget = options.is_in_widget;

				// Save composite stock status.
				if ( composite.$composite_data.find( '.composite_wrap p.stock' ).length > 0 ) {
					this.$composite_stock_status = composite.$composite_data.find( '.composite_wrap p.stock' ).clone();
				}

				/**
				 * Update the view when the stock statuses change.
				 */
				composite.actions.add_action( 'composite_availability_message_changed', this.render, 100, this );
			},

			render: function() {

				var view  = this,
					model = this.model;

				composite.console_log( 'debug:views', '\nScheduled update of composite availability view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

				clearTimeout( view.render_timer );
				view.render_timer = setTimeout( function() {
					view.render_task( model );
				}, 10 );
			},

			render_task: function( model ) {

				composite.console_log( 'debug:views', '\nUpdating composite availability view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				/*
				 * Update composite availability string.
				 */
				var insufficient_stock_components = this.get_insufficient_stock_components();

				if ( insufficient_stock_components.length > 0 ) {
					var composite_out_of_stock_string = wc_composite_params.i18n_insufficient_stock.replace( '%s', wc_cp_join( _.map( insufficient_stock_components, function( component_id ) { return composite.api.get_step_title( component_id ); } ) ) );
					this.$el.html( composite_out_of_stock_string ).slideDown( 200 );
				} else {
					if ( false !== this.$composite_stock_status ) {
						this.$el.html( this.$composite_stock_status ).slideDown( 200 );
					} else {
						this.$el.slideUp( 200 );
					}
				}
			},

			get_insufficient_stock_components: function() {

				var data = [];

				$.each( composite.get_components(), function( index, component ) {
					if ( ! component.step_validation_model.get( 'is_in_stock' ) ) {
						data.push( component.component_id );
					}
				} );

				return data;
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the composite add-to-cart button.
	 */
	this.Composite_Add_To_Cart_Button_View = function( opts ) {

		var View = Backbone.View.extend( {

			render_timer: false,
			is_in_widget: false,
			$el_button:   false,
			$qty:         false,

			widget_qty_synced: false,

			initialize: function( options ) {

				this.is_in_widget = options.is_in_widget;
				this.$el_button   = options.$el_button;
				this.$el_qty      = this.$el.find( '.quantity input.qty' );

				/**
				 * Update the view when the validation messages change, or when the stock status of the composite changes.
				 */
				composite.actions.add_action( 'composite_availability_status_changed', this.render, 100, this );
				composite.actions.add_action( 'composite_validation_status_changed', this.render, 100, this );

				/*
				 * Events for non-widgetized view.
				 */
				if ( ! this.is_in_widget ) {
					/**
					 * Button click event handler: Activate all fields for posting.
					 */
					this.$el_button.on( 'click', function() {
						$.each( composite.get_steps(), function( index, step ) {
							step.$el.find( 'select, input' ).each( function() {
								$( this ).prop( 'disabled', false );
							} );
							if ( false === step.step_visibility_model.get( 'is_visible' ) && step.is_component() ) {
								step.$component_options_select.val( '' );
							}
						} );
					} );
				}

				/*
				 * Events for widgetized view.
				 */
				if ( this.is_in_widget ) {
					/**
					 * Button click event handler: Trigger click in non-widgetized view, located within form.
					 */
					this.$el_button.on( 'click', function() {
						composite.composite_add_to_cart_button_view.$el_button.trigger( 'click' );
					} );

					/**
					 * Copy changed quantity quantity into non-widgetized view.
					 */
					this.$el_qty.on( 'change', { view: this }, function( event ) {

						var view = event.data.view;

						if ( ! view.widget_qty_synced ) {
							composite.console_log( 'debug:views', '\nCopying widget #' + view.is_in_widget + ' quantity value into composite add-to-cart quantity field...' );
							view.widget_qty_synced = true;
							composite.composite_add_to_cart_button_view.$el_qty.val( view.$el_qty.val() ).change();
							view.widget_qty_synced = false;
						}
					} );

					/**
					 * Copy changed composite quantity into view.
					 */
					composite.composite_add_to_cart_button_view.$el_qty.on( 'change', { view: this }, function( event ) {

						var view = event.data.view;

						composite.console_log( 'debug:views', '\nCopying composite add-to-cart quantity value into widget #' + view.is_in_widget + ' quantity field...' );
						view.$el_qty.val( composite.composite_add_to_cart_button_view.$el_qty.val() ).change();
					} );
				}
			},

			render: function() {

				var view  = this,
					model = this.model;

				composite.console_log( 'debug:views', '\nScheduled update of composite add-to-cart button view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

				clearTimeout( view.render_timer );
				view.render_timer = setTimeout( function() {
					view.render_task( model );
				}, 10 );
			},

			render_task: function( model ) {

				composite.console_log( 'debug:views', '\nUpdating composite add-to-cart button view' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				if ( model.get( 'passes_validation' ) && model.get( 'is_in_stock' ) ) {

					if ( composite.settings.button_behaviour === 'new' ) {
						this.$el_button.prop( 'disabled', false ).removeClass( 'disabled' );
					} else {
						this.$el.slideDown( 200 );
					}

				} else {
					if ( composite.settings.button_behaviour === 'new' ) {
						this.$el_button.prop( 'disabled', true ).addClass( 'disabled' );
					} else {
						this.$el.slideUp( 200 );
					}
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the composite pagination template.
	 */
	this.Composite_Pagination_View = function( opts ) {

		var View = Backbone.View.extend( {

			template: false,
			template_html: '',

			initialize: function() {

				this.template = wp.template( 'wc_cp_composite_pagination' );

				/**
			 	 * Update view when access to a step changes.
				 */
				composite.actions.add_action( 'step_access_changed', this.step_access_changed_handler, 100, this );

				/**
				 * Update outer element classes when the visibility of a step changes.
				 */
				composite.actions.add_action( 'step_visibility_changed', this.step_visibility_changed_handler, 100, this );

				/**
			 	 * Update view elements on transitioning to a new step.
				 */
				composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );

				/**
				 * On clicking a composite pagination link.
				 */
				this.$el.on( 'click', '.pagination_element a', this.clicked_pagination_element );
			},

			step_visibility_changed_handler: function() {

				this.render();
			},

			step_access_changed_handler: function() {

				this.render();
			},

			active_step_changed_handler: function() {

				this.render();
			},

			/**
			 * Pagination element clicked.
			 */
			clicked_pagination_element: function() {

				$( this ).blur();

				if ( composite.has_transition_lock ) {
					return false;
				}

				if ( $( this ).hasClass( 'inactive' ) ) {
					return false;
				}

				var step_id = $( this ).closest( '.pagination_element' ).data( 'item_id' ),
					step    = composite.get_step( step_id );

				if ( step ) {
					composite.navigate_to_step( step );
				}

				return false;
			},

			/**
			 * Renders all elements state (active/inactive).
			 */
			render: function() {

				var data = [];

				if ( ! composite.is_initialized ) {
					return false;
				}

				composite.console_log( 'debug:views', '\nRendering pagination view elements...' );

				$.each( composite.get_steps(), function( index, step ) {

					if ( step.is_visible() ) {

						var item_data = {
							element_id:          step.step_id,
							element_title:       step.get_title(),
							element_class:       '',
							element_state_class: ''
						};

						if ( step.is_current() ) {
							item_data.element_state_class = 'inactive';
							item_data.element_class       = 'pagination_element_current';
						} else if ( step.is_locked() ) {
							item_data.element_state_class = 'inactive';
						}

						data.push( item_data );
					}

				} );

				// Pass through 'composite_pagination_view_data' filter - @see WC_CP_Filters_Manager class.
				data = composite.filters.apply_filters( 'composite_pagination_view_data', [ data ] );

				var new_template_html = this.template( data );

				if ( new_template_html !== this.template_html ) {
					this.template_html = new_template_html;
					this.$el.html( new_template_html );
				} else {
					composite.console_log( 'debug:views', '...skipped!' );
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the composite summary template.
	 */
	this.Composite_Summary_View = function( opts ) {

		var View = Backbone.View.extend( {

			update_content_timers: {},
			view_elements:         {},
			is_in_widget:          false,
			template:              false,

			initialize: function( options ) {

				var view = this;

				this.template     = wp.template( 'wc_cp_summary_element_content' );
				this.is_in_widget = options.is_in_widget;

				$.each( composite.get_steps(), function( index, step ) {
					/**
				 	 * Update a single summary view element content when its validation state changes.
					 */
					step.step_validation_model.on( 'change:passes_validation', function() { view.render_element_content( step ); } );
				} );

				$.each( composite.get_components(), function( index, component ) {

					view.view_elements[ component.component_id ] = {

						$summary_element:         view.$el.find( '.summary_element_' + component.component_id ),
						$summary_element_link:    view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_link' ),

						$summary_element_wrapper: view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_wrapper' ),
						$summary_element_inner:   view.$el.find( '.summary_element_' + component.component_id + ' .summary_element_wrapper_inner' ),

						template_html: ''
					};
				} );

				/**
			 	 * Update view when access to a step changes.
				 */
				composite.actions.add_action( 'step_access_changed', this.step_access_changed_handler, 100, this );

				/**
				 * Update outer element classes when the visibility of a step changes.
				 */
				composite.actions.add_action( 'step_visibility_changed', this.step_visibility_changed_handler, 100, this );

				/**
				 * Update a single summary view element content when its quantity changes.
				 */
				composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 100, this );

				/**
				 * Update a single summary view element content when a new selection is made.
				 */
				composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 100, this );

				/**
				 * Update a single summary view element content when the contents of an existing selection change.
				 */
				composite.actions.add_action( 'component_selection_content_changed', this.selection_changed_handler, 100, this );

				/**
				 * Update a single summary view element price when its totals change.
				 */
				composite.actions.add_action( 'component_totals_changed', this.component_totals_changed_handler, 100, this );

				/**
			 	 * Update all summary view elements on transitioning to a new step.
				 */
				if ( composite.settings.layout !== 'single' ) {
					composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
				}

				/**
				 * On clicking a summary link.
				 */
				this.$el.on( 'click', '.summary_element_link', this.clicked_summary_element );

				/**
				 * On tapping a summary link.
				 */
				this.$el.on( 'click', 'a.summary_element_tap', function() {
					$( this ).closest( '.summary_element_link' ).trigger( 'click' );
					return false;
				} );
			},

			step_access_changed_handler: function( step ) {

				this.render_element_state( step );
			},

			step_visibility_changed_handler: function( step ) {

				this.render_element_visibility( step );
				this.render_indexes( step.step_index );
			},

			active_step_changed_handler: function() {

				this.render_state();
			},

			selection_changed_handler: function( step ) {

				this.render_element_content( step );
			},

			quantity_changed_handler: function( step ) {

				this.render_element_content( step );
			},

			component_totals_changed_handler: function( step ) {

				this.render_element_content( step );
			},

			/**
			 * Summary element clicked.
			 */
			clicked_summary_element: function() {

				if ( composite.has_transition_lock ) {
					return false;
				}

				if ( $( this ).hasClass( 'disabled' ) ) {
					return false;
				}

				var step_id = $( this ).closest( '.summary_element' ).data( 'item_id' ),
					step    = composite.get_step( step_id );

				if ( step === false ) {
					return false;
				}

				if ( ! step.is_current() || composite.settings.layout === 'single' ) {
					composite.navigate_to_step( step );
				}

				return false;
			},

			render_indexes: function( after_index ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				after_index = typeof( after_index ) === 'undefined' ? 0 : after_index;

				var summary_element_columns = parseInt( composite.$composite_summary.data( 'columns' ) ),
					summary_element_loop    = 0,
					view                    = this;

				composite.console_log( 'debug:views', '\nUpdating summary view element indexes...' );

				$.each( composite.get_steps(), function( index, step ) {

					if ( typeof view.view_elements[ step.step_id ] === 'undefined' ) {
						return true;
					}

					if ( step.step_index < after_index ) {
						if ( step.is_visible() ) {
							summary_element_loop++;
						}
						return true;
					}

					if ( false === view.is_in_widget ) {

						var summary_element_classes = '';

						if ( step.is_visible() ) {

							summary_element_loop++;

							if ( ( ( summary_element_loop - 1 ) % summary_element_columns ) == 0 || summary_element_columns == 1 ) {
								summary_element_classes += ' first';
							}

							if ( summary_element_loop % summary_element_columns == 0 ) {
								summary_element_classes += ' last';
							}
						}

						view.view_elements[ step.step_id ].$summary_element.removeClass( 'first last' ).addClass( summary_element_classes );
					}

					composite.debug_tab_count = composite.debug_tab_count + 2;
					view.render_element_content( step );
					composite.debug_tab_count = composite.debug_tab_count - 2;
				} );
			},

			/**
			 * Renders all elements visibility.
			 */
			render_visibility: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				var view = this;

				composite.console_log( 'debug:views', '\nRendering summary view element visibility' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
				composite.debug_tab_count = composite.debug_tab_count + 2;
				$.each( composite.get_steps(), function( index, step ) {
					view.render_element_visibility( step );
				} );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Renders all elements state (active/inactive).
			 */
			render_state: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				var view = this;

				composite.console_log( 'debug:views', '\nRendering summary view element states' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
				composite.debug_tab_count = composite.debug_tab_count + 2;
				$.each( composite.get_steps(), function( index, step ) {
					view.render_element_state( step );
				} );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Render content.
			 */
			render_content: function() {

				var view = this;

				composite.console_log( 'debug:views', '\nRendering summary view element contents' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
				composite.debug_tab_count = composite.debug_tab_count + 2;
				$.each( composite.get_steps(), function( index, step ) {
					view.render_element_content( step );
				} );
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Render view.
			 */
			render: function() {

				composite.console_log( 'debug:views', '\nRendering summary view elements' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );
				composite.debug_tab_count = composite.debug_tab_count + 2;
				this.render_visibility();
				this.render_state();
				this.render_indexes();
				this.render_content();
				composite.debug_tab_count = composite.debug_tab_count - 2;
			},

			/**
			 * Returns a single element's price (scheduler).
			 */
			get_element_price_html: function( step ) {

				var price_data = composite.data_model.price_data,
					price_html = '';

				if ( step.is_component() && step.is_subtotal_visible() ) {

					var component    = step,
						component_id = component.component_id,
						product_id   = component.get_selected_product_type() === 'variable' ? component.get_selected_variation( false ) : component.get_selected_product( false ),
						qty          = component.get_selected_quantity();

					// Update price.
					if ( product_id > 0 && qty > 0 ) {

						var component_totals = composite.data_model.get( 'component_' + component_id + '_totals' );

						if ( price_data[ 'is_priced_individually' ][ component_id ] === 'no' && component_totals.price === 0.0 && component_totals.regular_price === 0.0 ) {
							price_html = '';
						} else {
							var price_format         = wc_cp_woocommerce_number_format( wc_cp_number_format( component_totals.price ) ),
								regular_price_format = wc_cp_woocommerce_number_format( wc_cp_number_format( component_totals.regular_price ) );

							if ( component_totals.regular_price > component_totals.price ) {
								price_html = '<span class="price summary_element_content"><del>' + regular_price_format + '</del> <ins>' + price_format + '</ins></span>';
							} else {
								price_html = '<span class="price summary_element_content">' + price_format + '</span>';
							}
						}
					}
				}

				return price_html;
			},

			/**
			 * Renders a single element's content (scheduler).
			 */
			render_element_content: function( step ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				var view = this;

				if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
					return false;
				}

				composite.console_log( 'debug:views', '\nScheduled update of "' + step.get_title() + '" summary view element content' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '.' );

				if ( typeof( this.update_content_timers[ step.step_index ] ) !== 'undefined' ) {
					clearTimeout( view.update_content_timers[ step.step_index ] );
				}

				this.update_content_timers[ step.step_index ] = setTimeout( function() {
					view.render_element_content_task( step );
				}, 50 );
			},

			/**
			 * Renders a single element's content.
			 */
			render_element_content_task: function( step ) {

				composite.console_log( 'debug:views', '\nRendering "' + step.get_title() + '" summary view element content' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				if ( step.is_component() ) {

					var component           = step,
						component_id        = component.component_id,

						$item_summary_outer = this.view_elements[ component_id ].$summary_element_wrapper,
						$item_summary_inner = this.view_elements[ component_id ].$summary_element_inner,

						template_html       = this.view_elements[ component_id ].template_html,

						content_data        = {
							element_index:           step.get_title_index(),
							element_title:           step.get_title(),
							element_selection_title: '',
							element_action:          '',
							element_image_src:       '',
							element_image_srcset:    '',
							element_image_sizes:     '',
							element_image_title:     '',
							element_price:           ''
						},

						title               = '',
						action              = '',
						image_data          = false,

						load_height         = 0,

						is_visible          = this.$el.is( ':visible' );

					// Lock height if animating.
					if ( is_visible ) {
						load_height = $item_summary_inner.outerHeight( true );
						$item_summary_outer.css( 'height', load_height );
					}

					// Selection title.
					title = component.get_selected_product_title( true, false );

					// Action text.
					if ( false === this.is_in_widget ) {
						if ( title && component.passes_validation() ) {
							if ( component.is_static() ) {
								action = wc_composite_params.i18n_summary_static_component;
							} else {
								action = wc_composite_params.i18n_summary_configured_component;
							}
						} else {
							action = wc_composite_params.i18n_summary_empty_component;
						}
					}

					// Hide action text.
					if ( ( step.is_current() && is_visible ) || this.is_in_widget ) {
						action = '';
					}

					content_data.element_selection_title = title;
					content_data.element_action          = action;

					// Selection image data.
					image_data = component.get_selected_product_image_data( false );

					if ( false === image_data ) {
						image_data = component.get_placeholder_image_data();
					}

					if ( image_data ) {
						content_data.element_image_src    = image_data.image_src;
						content_data.element_image_srcset = image_data.image_srcset ? image_data.image_srcset : '';
						content_data.element_image_sizes  = image_data.image_sizes ? image_data.image_sizes : '';
						content_data.element_image_title  = image_data.image_title;
					}

					// Selection price.
					content_data.element_price = this.get_element_price_html( step );

					// Pass through 'component_summary_element_content_data' filter - @see WC_CP_Filters_Manager class.
					content_data = composite.filters.apply_filters( 'component_summary_element_content_data', [ content_data, component, this ] );

					var new_template_html = this.template( content_data );

					if ( new_template_html !== template_html ) {

						this.view_elements[ component_id ].template_html = new_template_html;

						// Update content.
						$item_summary_inner.html( new_template_html );

					} else {
						composite.console_log( 'debug:views', '...skipped!' );
					}

					// Update element class.
					if ( component.passes_validation() ) {
						$item_summary_outer.addClass( 'configured' );
					} else {
						$item_summary_outer.removeClass( 'configured' );
					}

					// Run 'component_summary_content_updated' action to allow 3rd party code to add data to the summary - @see WC_CP_Actions_Dispatcher class.
					composite.actions.do_action( 'component_summary_content_updated', [ component, this ] );

					// Wait for image to load.
					var wait_time = 0;

					var finalize = function() {

						if ( image_data.image_src ) {

							var image = $item_summary_inner.find( '.summary_element_image img' );

							if ( image.height() === 0 && wait_time < 1000 ) {
								wait_time += 50;
								setTimeout( function() {
									finalize();
								}, 50 );
							} else {
								animate();
							}
						} else {
							animate();
						}
					};

					// Animate.
					var animate = function() {

						// Measure height.
						var new_height     = $item_summary_inner.outerHeight( true ),
							animate_height = false;

						if ( Math.abs( new_height - load_height ) > 1 ) {
							animate_height = true;
						} else {
							$item_summary_outer.css( 'height', 'auto' );
						}

						if ( animate_height ) {

							composite.console_log( 'debug:events', 'Starting updated element content animation...' );

							$item_summary_outer.animate( { 'height': new_height }, { duration: 200, queue: false, always: function() {

								composite.console_log( 'debug:events', 'Ended updated element content animation.' );

								$item_summary_outer.css( { 'height': 'auto' } );
							} } );
						}

					};

					if ( is_visible ) {
						setTimeout( function() {
							finalize();
						}, 10 );
					}
				}
			},

			/**
			 * Renders a single element's state (active/inactive).
			 */
			render_element_visibility: function( step ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
					return false;
				}

				var $element = this.view_elements[ step.step_id ].$summary_element;

				composite.console_log( 'debug:views', '\nUpdating "' + step.get_title() + '" summary view element visibility' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				if ( false === step.is_visible() ) {
					$element.addClass( 'hidden' );
				} else {
					$element.removeClass( 'hidden' );
				}
			},

			/**
			 * Renders a single element's state (active/inactive).
			 */
			render_element_state: function( step ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				if ( typeof this.view_elements[ step.step_id ] === 'undefined' ) {
					return false;
				}

				var $element      = this.view_elements[ step.step_id ].$summary_element,
					$element_link = this.view_elements[ step.step_id ].$summary_element_link;

				composite.console_log( 'debug:views', '\nUpdating "' + step.get_title() + '" summary view element state' + ( this.is_in_widget ? ' (widget #' + this.is_in_widget + ')' : '' ) + '...' );

				if ( step.is_current() ) {

					$element_link.removeClass( 'disabled' );

					if ( composite.settings.layout !== 'single' ) {
						$element_link.addClass( 'selected' );
					}

					if ( false === composite.get_step( 'review' ) ) {
						$element.find( '.summary_element_selection_prompt' ).slideUp( 200 );
					}

				} else {

					if ( step.is_locked() ) {

						$element_link.removeClass( 'selected' );
						$element_link.addClass( 'disabled' );

					} else {

						$element_link.removeClass( 'disabled' );
						$element_link.removeClass( 'selected' );
					}

					$element.find( '.summary_element_selection_prompt' ).slideDown( 200 );
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with navigation view elements.
	 */
	this.Composite_Navigation_View = function( opts ) {

		var View = Backbone.View.extend( {

			render_timer:         false,
			render_movable_timer: false,

			updated_buttons_data: {},

			navi_in_step:         false,

			template:             false,

			$el_progressive:      composite.$composite_form.find( '.composite_navigation.progressive' ),
			$el_paged_top:        composite.$composite_navigation_top,
			$el_paged_bottom:     composite.$composite_navigation_bottom,
			$el_paged_movable:    composite.$composite_navigation_movable,

			initialize: function() {

				this.template = wp.template( 'wc_cp_composite_navigation' );

				/**
			 	 * Update navigation view elements when a new selection is made.
				 */
				composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 110, this );

				/**
			 	 * Update navigation view elements when the contents of an existing selection are changed.
				 */
				composite.actions.add_action( 'component_selection_content_changed', this.selection_content_changed_handler, 100, this );

				/**
			 	 * Update navigation view elements on transitioning to a new step.
				 */
				composite.actions.add_action( 'active_step_transition_start', this.active_step_transition_start_handler, 110, this );

				/**
				 * Update movable navi visibility when appending more options.
				 */
				composite.actions.add_action( 'options_state_rendered', this.options_state_rendered_handler, 20, this );

				/**
				 * On clicking the Previous/Next navigation buttons.
				 */
				this.$el.on( 'click', '.page_button', this.clicked_navigation_button );
			},

			selection_content_changed_handler: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				this.render_change();
			},

			/**
		 	 * Updates navigation view elements when a new selection is made.
		 	 * Handled by the composite actions dispatcher.
			 */
			selection_changed_handler: function( step ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				// Autotransition to next.
				if ( step.is_current() && step.autotransition() && step.passes_validation() && step.get_selected_product() > 0 && ! step.component_selection_view.resetting_product ) {

					var next_step = composite.get_next_step();

					if ( next_step && next_step.is_component() && next_step.component_options_view.is_updating() ) {
						composite.actions.add_action( 'component_options_refreshed_' + next_step.step_id, this.autotransition, 10, this );
					} else {
						composite.show_next_step();
					}

					return false;
				}

				this.render_change();
			},

			/**
			 * Callback attached to 'component_options_refreshed_{step_id}' to handle autotransitioning when the component options view is busy.
			 */
			autotransition: function() {

				composite.show_next_step();
				composite.actions.remove_action( 'component_options_refreshed_' + composite.get_current_step().step_id, this.autotransition );
			},

			/**
		 	 * Update navigation view elements on transitioning to a new step.
			 */
			active_step_transition_start_handler: function() {

				var view = this;

				clearTimeout( view.render_timer );
				view.render( 'transition' );
			},

			/**
			 * Update movable navi visibility in relocated containers when appending more options.
			 */
			options_state_rendered_handler: function( step, changed ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				if ( step.is_current() && _.contains( changed, 'thumbnails' ) && step.component_selection_view.is_relocated() ) {
					this.render_movable();
				}
			},

			/**
			 * Previous/Next navigation button clicked.
			 */
			clicked_navigation_button: function() {

				$( this ).blur();

				if ( $( this ).hasClass( 'inactive' ) ) {
					return false;
				}

				if ( composite.has_transition_lock ) {
					return false;
				}

				if ( $( this ).hasClass( 'next' ) ) {

					if ( composite.get_next_step() ) {
						composite.show_next_step();
					} else {
						wc_cp_scroll_viewport( composite.$composite_form.find( '.scroll_final_step' ), { partial: false, duration: 250, queue: false } );
					}

				} else {
					composite.show_previous_step();
				}

				return false;
			},

			update_buttons: function() {

				var view = this,
					data = {
						prev_btn: { btn_classes: '', btn_text: '' },
						next_btn: { btn_classes: '', btn_text: '' },
					};

				if ( false !== this.updated_buttons_data.button_next_html ) {
					data.next_btn.btn_text = this.updated_buttons_data.button_next_html;
				}

				if ( false !== this.updated_buttons_data.button_prev_html ) {
					data.prev_btn.btn_text = this.updated_buttons_data.button_prev_html;
				}

				if ( false === this.updated_buttons_data.button_next_visible ) {
					data.next_btn.btn_classes = 'invisible';
				}

				if ( false === this.updated_buttons_data.button_prev_visible ) {
					data.prev_btn.btn_classes = 'invisible';
				}

				if ( false === this.updated_buttons_data.button_next_active ) {
					data.next_btn.btn_classes += ' inactive';
				}

				this.$el.html( view.template( data ) );
			},

			render_change: function() {

				var view = this;

				composite.console_log( 'debug:views', '\nScheduling navigation UI update...' );

				clearTimeout( view.render_timer );
				view.render_timer = setTimeout( function() {
					view.render( 'change' );
				}, 40 );
			},

			render: function( event_type ) {

				composite.console_log( 'debug:views', '\nRendering navigation UI...' );

				var current_step        = composite.get_current_step(),
					next_step           = composite.get_next_step(),
					prev_step           = composite.get_previous_step(),
					view                = this;

				this.updated_buttons_data = {
					button_next_html:    false,
					button_prev_html:    false,
					button_next_visible: false,
					button_prev_visible: false,
					button_next_active:  false,
				};

				if ( event_type === 'transition' && composite.settings.layout === 'paged' && composite.settings.layout_variation === 'componentized' ) {
					if ( current_step.is_review() ) {
						this.$el_paged_bottom.hide();
					} else {
						this.$el_paged_bottom.show();
					}
				}

				if ( current_step.is_component() ) {

					// Selectively show next/previous navigation buttons.
					if ( next_step && composite.settings.layout_variation !== 'componentized' ) {

						this.updated_buttons_data.button_next_html    = wc_composite_params.i18n_next_step.replace( '%s', next_step.get_title() );
						this.updated_buttons_data.button_next_visible = true;

					} else if ( next_step && composite.settings.layout === 'paged' ) {
						this.updated_buttons_data.button_next_html    = wc_composite_params.i18n_final_step;
						this.updated_buttons_data.button_next_visible = true;
					}
				}

				// Paged previous/next.
				if ( current_step.passes_validation() || ( composite.settings.layout_variation === 'componentized' && current_step.is_component() ) ) {

					if ( next_step ) {
						this.updated_buttons_data.button_next_active = true;
					}

					if ( prev_step && composite.settings.layout === 'paged' && prev_step.is_component() ) {
						this.updated_buttons_data.button_prev_html    = wc_composite_params.i18n_previous_step.replace( '%s', prev_step.get_title() );
						this.updated_buttons_data.button_prev_visible = true;
					} else {
						this.updated_buttons_data.button_prev_html = '';
					}

				} else {

					if ( prev_step && prev_step.is_component() ) {

						var product_id = prev_step.get_selected_product();

						if ( product_id > 0 || product_id === '0' || product_id === '' && prev_step.is_optional() ) {

							if ( composite.settings.layout === 'paged' ) {
								this.updated_buttons_data.button_prev_html    = wc_composite_params.i18n_previous_step.replace( '%s', prev_step.get_title() );
								this.updated_buttons_data.button_prev_visible = true;
							}
						}
					}
				}

				/*
				 * Move navigation into the next component when using the progressive layout without toggles.
				 */
				if ( composite.settings.layout === 'progressive' ) {

					var navi = view.$el_progressive;

					if ( view.navi_in_step !== current_step.step_id ) {

						navi.slideUp( { duration: 200, always: function() {

							view.update_buttons();
							navi.appendTo( current_step.$inner_el ).hide();

							view.navi_in_step = current_step.step_id;

							setTimeout( function() {

								var show_navi = false;

								if ( ! current_step.$el.hasClass( 'last' ) ) {
									if ( current_step.passes_validation() && ! next_step.has_toggle() ) {
										show_navi = true;
									}
								}

								if ( show_navi ) {
									navi.slideDown( { duration: 200, queue: false } );
								}

							}, 200 );

						} } );

					} else {

						view.update_buttons();

						var show_navi = false;

						if ( ! current_step.$el.hasClass( 'last' ) ) {
							if ( current_step.passes_validation() && ! next_step.has_toggle() ) {
								show_navi = true;
							}
						}

						if ( show_navi ) {
							navi.slideDown( 200 );
						} else {
							navi.slideUp( 200 );
						}
					}

				/*
				 * Move navigation when using a paged layout with thumbnails.
				 */
				} else if ( composite.settings.layout === 'paged' ) {

					if ( view.navi_in_step !== current_step.step_id ) {
						current_step.$el.prepend( view.$el_paged_top );
						current_step.$el.append( view.$el_paged_bottom );
						view.navi_in_step = current_step.step_id;
					}

					view.update_buttons();

					view.render_movable();
				}
			},

			render_movable: function() {

				var view = this;

				composite.console_log( 'debug:views', '\nScheduling movable navigation visibility update...' );

				clearTimeout( view.render_movable_timer );
				view.render_movable_timer = setTimeout( function() {
					view.render_movable_task();
				}, 10 );
			},

			render_movable_task: function() {

				var current_step = composite.get_current_step(),
					view         = this;

				if ( current_step.is_component() && current_step.has_options_style( 'thumbnails' ) ) {

					if ( current_step.get_selected_product() > 0 ) {

						// Measure distance from bottom navi and only append navi in content if far enough.
						var navi_in_content    = current_step.$component_content.find( '.composite_navigation' ).length > 0,
							bottom_navi_nearby = false;

						if ( current_step.append_results() ) {

							if ( current_step.component_selection_view.is_relocated() ) {

								var visible_thumbnails       = current_step.$component_options.find( '.component_option_thumbnail_container' ).not( '.hidden' ),
									selected_thumbnail       = current_step.$component_options.find( '.component_option_thumbnail.selected' ).closest( '.component_option_thumbnail_container' ),
									selected_thumbnail_index = visible_thumbnails.index( selected_thumbnail ) + 1,
									thumbnail_columns        = composite.$composite_form.width() > wc_composite_params.small_width_threshold && false === composite.$composite_form.hasClass( 'legacy_width' ) ? parseInt( current_step.$component_thumbnail_options.data( 'columns' ) ) : 1;

								if ( Math.ceil( selected_thumbnail_index / thumbnail_columns ) === Math.ceil( visible_thumbnails.length / thumbnail_columns ) ) {
									bottom_navi_nearby = true;
								}
							}
						}

						if ( ! navi_in_content && ! bottom_navi_nearby ) {
							view.$el_paged_movable.appendTo( current_step.$component_summary );
							navi_in_content = true;
						}

						if ( navi_in_content ) {
							if ( bottom_navi_nearby || current_step.is_static() ) {
								view.$el_paged_movable.addClass( 'hidden' );
							} else {
								view.$el_paged_movable.removeClass( 'hidden' );
							}
						}
					}
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the Composite Summary Widget and its elements.
	 */
	this.Composite_Widget_View = function( opts ) {

		var View = Backbone.View.extend( {

			show_hide_timer: false,

			initialize: function( options ) {

				this.$el.removeClass( 'cp-no-js' );

				this.validation_view = new composite.view_classes.Composite_Validation_View( {
					is_in_widget: options.widget_count,
					el:           this.$el.find( '.widget_composite_summary_error .composite_message' ),
					model:        composite.data_model,
				} );

				this.price_view = new composite.view_classes.Composite_Price_View( {
					is_in_widget: options.widget_count,
					el:           this.$el.find( '.widget_composite_summary_price .composite_price' ),
					model:        composite.data_model,
				} );

				this.availability_view = new composite.view_classes.Composite_Availability_View( {
					is_in_widget: options.widget_count,
					el:           this.$el.find( '.widget_composite_summary_availability .composite_availability' ),
					model:        composite.data_model,
				} );

				this.add_to_cart_button_view = new composite.view_classes.Composite_Add_To_Cart_Button_View( {
					is_in_widget: options.widget_count,
					el:           this.$el.find( '.widget_composite_summary_button .composite_button' ),
					$el_button:   this.$el.find( '.widget_composite_summary_button .composite_button .composite_add_to_cart_button' ),
					model:        composite.data_model,
				} );

				this.composite_summary_view = new composite.view_classes.Composite_Summary_View( {
					is_in_widget: options.widget_count,
					el:           this.$el.find( '.widget_composite_summary_elements' ),
				} );

				// Run 'widget_view_initialized' action - @see WC_CP_Composite_Dispatcher class.
				composite.actions.do_action( 'widget_view_initialized', [ options, this ] );

				/**
				 * Show/hide the widget when transitioning to a new step.
				 */
				if ( composite.settings.layout === 'paged' ) {
					composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
				}
			},

			active_step_changed_handler: function() {

				this.show_hide();
			},

			show_hide: function() {

				var view = this;

				clearTimeout( view.show_hide_timer );
				this.show_hide_timer = setTimeout( function() {
					view.show_hide_task();
				}, 20 );
			},

			show_hide_task: function() {

				var is_review = composite.get_current_step().is_review();

				if ( is_review ) {
					this.$el.slideUp( 250 );
					this.$el.animate( { opacity: 0 }, { duration: 250, queue: false } );
					this.$el.addClass( 'inactive' );
				} else {
					if ( this.$el.hasClass( 'inactive' ) ) {
						this.$el.removeClass( 'inactive' );
						this.$el.slideDown( 250 );
						this.$el.animate( { opacity: 1 }, { duration: 250, queue: false } );
					}
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * Handles the display of step validation messages.
	 */
	this.Step_Validation_View = function( step, opts ) {

		var self = step;
		var View = Backbone.View.extend( {

			render_timer: false,
			render_html:  false,
			template:     false,

			event_type: '',

			initialize: function() {

				var view      = this;
				this.template = wp.template( 'wc_cp_validation_message' );

				this.listenTo( this.model, 'change:component_messages', function() {

					if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' ) {
						return false;
					}

					if ( self.component_selection_view.resetting_product ) {
						return false;
					}

					composite.console_log( 'debug:views', '\nScheduling "' + self.get_title() + '" validation message update...' );
					clearTimeout( view.render_timer );
					view.render_timer = setTimeout( function() {
						view.prepare_render( 'change' );
						view.render();
					}, 10 );
				} );

				/**
				 * Prepare display of component messages when transitioning to this step.
				 */
				if ( composite.settings.layout !== 'single' ) {
					composite.actions.add_action( 'active_step_changed_' + self.step_id, this.active_step_changed_handler, 100, this );
				}

				/**
				 * Display component messages after transitioning to this step.
				 */
				if ( composite.settings.layout !== 'single' ) {
					composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.active_step_transition_end_handler, 100, this );
				}
			},

			/**
			 * Shows component messages when transitioning this step.
			 */
			active_step_changed_handler: function() {

				if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' ) {
					return false;
				}

				this.prepare_render( 'transition' );
			},

			/**
			 * Shows component messages when transitioning this step.
			 */
			active_step_transition_end_handler: function() {

				if ( ! self.is_current() || typeof( self.$component_message ) === 'undefined' ) {
					return false;
				}

				clearTimeout( this.render_timer );
				this.render();
			},

			/**
			 * Prepares validation messages for rendering.
			 */
			prepare_render: function( event_type ) {

				this.event_type = '' === this.event_type ? event_type : this.event_type;

				var display_message;

				composite.console_log( 'debug:views', '\nPreparing "' + self.get_title() + '" validation message update...' );

				this.render_html = false;

				if ( self.passes_validation() || ( composite.settings.layout_variation === 'componentized' && self.is_component() ) ) {
					display_message = false;
				} else {
					display_message = true;
				}

				if ( display_message ) {

					// Don't show the prompt if it's the last component of the progressive layout.
					if ( ! self.$el.hasClass( 'last' ) || ! self.$el.hasClass( 'progressive' ) ) {

						// We actually have something to display here.
						var validation_messages = self.get_validation_messages();

						if ( validation_messages.length > 0 ) {
							this.render_html = this.template( validation_messages );
						}
					}
				}

				if ( this.event_type === 'transition' && false === this.render_html ) {
					if ( composite.settings.layout === 'progressive' ) {
						if ( self.has_toggle() ) {
							self.$component_message.hide();
						}
					} else if ( composite.settings.layout === 'paged' ) {
						self.$component_message.hide();
					}
				}
			},

			/**
			 * Renders validation messages.
			 */
			render: function() {

				var view = this;

				composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" validation message...' );

				if ( false !== this.render_html ) {
					self.$component_message.html( this.render_html );
				}

				if ( composite.settings.layout === 'progressive' ) {

					if ( this.event_type === 'transition' ) {

						setTimeout( function() {

							if ( false === view.render_html ) {
								self.$component_message.slideUp( 200 );
							} else {
								self.$component_message.slideDown( 200 );
							}

						}, 200 );

					} else {

						if ( false === this.render_html ) {
							self.$component_message.slideUp( 200 );
						} else {
							self.$component_message.slideDown( 200 );
						}
					}

				} else if ( composite.settings.layout === 'paged' ) {

					var component_message_delay = 0;

					// Add a delay when loading a new component option with notices, in order to display the message after the animation has finished.
					if ( self.is_component() && self.$component_content.hasClass( 'updating' ) && false !== this.render_html ) {
						component_message_delay = 600;
					}

					// Hide the message container when moving into a relocating summary and add a delay.
					if ( self.is_component() && self.$component_content.hasClass( 'relocating' ) ) {
						self.$component_message.hide();
						component_message_delay = 600;
					}

					setTimeout( function() {
						if ( false === view.render_html ) {
							self.$component_message.slideUp( 200 );
						} else {
							self.$component_message.slideDown( 200 );
						}
					}, component_message_delay );
				}

				this.event_type = '';
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * View associated with the composite pagination template.
	 */
	this.Component_Pagination_View = function( component, opts ) {

		var self = component;
		var View = Backbone.View.extend( {

			template: false,

			initialize: function() {

				this.template = wp.template( 'wc_cp_options_pagination' );

				/**
			 	 * Update the view when its model state changes.
				 */
				this.listenTo( this.model, 'change:page change:pages', this.render );

				/**
				 * Reload component options upon requesting a new page.
				 */
				self.$el.on( 'click', '.component_pagination a.component_pagination_element', { view: this }, this.load_page );

				/**
				 * Append component options upon clicking the 'Load More' button.
				 */
				self.$el.on( 'click', '.component_pagination a.component_options_load_more', { view: this }, this.load_more );

			},

			load_page: function() {

				$( this ).blur();

				var page = parseInt( $( this ).data( 'page_num' ) );

				if ( page > 0 ) {

					// Block container.
					composite.block( self.$component_options );
					self.component_options_view.$blocked_element = self.$component_options;
					self.$component_options.find( '.blockUI' ).addClass( 'bottom' );

					self.component_options_view.update_options( { page: page }, 'reload' );
				}

				return false;
			},

			load_more: function() {

				$( this ).blur();

				var page  = parseInt( self.component_options_model.get( 'page' ) ),
					pages = parseInt( self.component_options_model.get( 'pages' ) );

				if ( page > 0 && page < pages ) {

					// Block container.
					composite.block( self.$component_options );
					self.component_options_view.$blocked_element = self.$component_options;
					self.$component_options.find( '.blockUI' ).addClass( 'bottom' );

					self.component_options_view.update_options( { page: page + 1 }, 'append' );
				}

				return false;
			},

			/**
			 * Renders the view.
			 */
			render: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				var	model = this.model,
					data  = {
						page:                model.get( 'page' ),
						pages:               model.get( 'pages' ),
						range_mid:           self.get_pagination_range(),
						range_end:           self.get_pagination_range( 'end' ),
						pages_in_range:      ( ( self.get_pagination_range() + self.get_pagination_range( 'end' ) ) * 2 ) + 1,
						i18n_page_of_pages:  wc_composite_params.i18n_page_of_pages.replace( '%p', model.get( 'page' ) ).replace( '%t', model.get( 'pages' ) )
					};

				composite.console_log( 'debug:views', '\nRendering "' + self.get_title() + '" options pagination...' );

				if ( self.append_results() ) {
					if ( data.page < data.pages ) {
						self.$component_pagination.slideDown( 200 );
					} else {
						self.$component_pagination.slideUp( 200 );
					}
				} else {
					this.$el.html( this.template( data ) );
				}
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * Updates the model data from UI interactions and listens to the component options model for updated content.
	 */
	this.Component_Options_View = function( component, opts ) {

		var self = component;
		var View = Backbone.View.extend( {

			templates: {
				dropdown:   false,
				thumbnails: false,
				radios:     false
			},

			update_action:    '',
			load_height:      0,
			$blocked_element: false,

			append_results_retry_count: 0,

			changes: {
				dropdown:   { changed: false, to: '' },
				thumbnails: { changed: false, to: '' },
				radios:     { changed: false, to: '' },
				variations: { changed: false, to: [] }
			},

			initialize: function() {

				this.templates.dropdown   = wp.template( 'wc_cp_options_dropdown' );
				this.templates.thumbnails = wp.template( 'wc_cp_options_thumbnails' );
				this.templates.radios     = wp.template( 'wc_cp_options_radio_buttons' );

				/**
			 	 * Reload component options upon activating a filter.
				 */
				self.$el.on( 'click', '.component_filter_option a', { view: this }, this.activate_filter );

				/**
				 * Reload component options upon resetting a filter.
				 */
				self.$el.on( 'click', '.component_filters a.reset_component_filter', { view: this }, this.reset_filter );

				/**
				 * Reload component options upon resetting all filters.
				 */
				self.$el.on( 'click', '.component_filters a.reset_component_filters', { view: this }, this.reset_filters );

				/**
				 * Reload component options upon reordering.
				 */
				self.$el.on( 'change', '.component_ordering select', { view: this }, this.order_by );

				/**
				 * Toggle filters.
				 */
				self.$el.on( 'click', '.component_filter_title label', { view: this }, this.toggle_filter );


				/**
				 * Navigate to step on clicking the blocked area in progressive mode.
				 */
				if ( composite.settings.layout === 'progressive' ) {
					self.$el.on( 'click', '.block_component_selections_inner', { view: this }, this.clicked_blocked_area );
				}

				/**
				 * Change selection when clicking a thumbnail or thumbnail tap area.
				 */
				if ( self.has_options_style( 'thumbnails' ) ) {
					self.$el.on( 'click', '.component_option_thumbnail', { view: this }, this.clicked_thumbnail );
					self.$el.on( 'click', 'a.component_option_thumbnail_tap', { view: this }, this.clicked_thumbnail_tap );
				}

				/**
				 * Change selection when clicking a radio button.
				 */
				if ( self.has_options_style( 'radios' ) ) {
					self.$el.on( 'change', '.component_option_radio_buttons input', { view: this }, this.clicked_radio );
					self.$el.on( 'click', 'a.component_option_radio_button_tap', { view: this }, this.clicked_radio_tap );
				}

				/**
				 * Update view after appending/reloading component options.
				 */
				composite.actions.add_action( 'component_options_loaded_' + self.step_id, this.updated_options, 10, this );

				/**
				 * Render component options in view.
				 */
				composite.actions.add_action( 'options_state_changed_' + self.step_id, this.render, 10, this );

				/**
				 * Reload options if the scenarios used to render them have changed.
				 */
				this.listenTo( this.model, 'change:options_in_scenarios', this.options_in_scenarios_changed );
			},

			options_in_scenarios_changed: function() {

				if ( this.model.reload_options_on_scenarios_change() ) {

					// Block options container.
					composite.block( self.$component_options );
					this.$blocked_element = self.$component_options;

					// Add status message.
					composite.data_model.add_status_message( self.component_id, wc_composite_params.i18n_loading_options.replace( '%s', self.get_title() ) );

					this.update_options( { page: 1 }, 'reload' );
				}
			},

			clicked_blocked_area: function() {

				composite.navigate_to_step( self );
				return false;
			},

			clicked_thumbnail_tap: function() {

				$( this ).closest( '.component_option_thumbnail' ).trigger( 'click' );
				return false;
			},

			clicked_thumbnail: function() {

				$( this ).blur();

				if ( self.$el.hasClass( 'disabled' ) || $( this ).hasClass( 'disabled' ) ) {
					return true;
				}

				if ( ! $( this ).hasClass( 'selected' ) ) {
					var value = $( this ).data( 'val' );
					self.$component_options_select.val( value ).change();
				}
			},

			clicked_radio_tap: function() {

				$( this ).closest( '.component_option_radio_button' ).find( 'input' ).trigger( 'click' );
				return false;
			},

			clicked_radio: function() {

				var $container = $( this ).closest( '.component_option_radio_button' );

				if ( self.$el.hasClass( 'disabled' ) || $container.hasClass( 'disabled' ) ) {
					return true;
				}

				if ( ! $container.hasClass( 'selected' ) ) {
					var value = $( this ).val();
					self.$component_options_select.val( value ).change();
				}
			},

			toggle_filter: function() {

				$( this ).blur();

				var component_filter         = $( this ).closest( '.component_filter' ),
					component_filter_content = component_filter.find( '.component_filter_content' );

				wc_cp_toggle_element( component_filter, component_filter_content );

				return false;
			},

			activate_filter: function( event ) {

				$( this ).blur();

				// Do nothing if the component is disabled.
				if ( self.$el.hasClass( 'disabled' ) ) {
					return false;
				}

				var view                    = event.data.view,
					component_filter_option = $( this ).closest( '.component_filter_option' );

				if ( ! component_filter_option.hasClass( 'selected' ) ) {
					component_filter_option.addClass( 'selected' );
				} else {
					component_filter_option.removeClass( 'selected' );
				}

				// Add/remove 'active' classes.
				view.update_filters_ui();

				// Block container.
				composite.block( self.$component_filters );
				view.$blocked_element = self.$component_filters;

				view.update_options( { page: 1, filters: self.find_active_filters() }, 'reload' );

				return false;
			},

			reset_filter: function( event ) {

				$( this ).blur();

				// Get active filters.
				var view                     = event.data.view,
					component_filter_options = $( this ).closest( '.component_filter' ).find( '.component_filter_option.selected' );

				if ( component_filter_options.length == 0 ) {
					return false;
				}

				component_filter_options.removeClass( 'selected' );

				// Add/remove 'active' classes.
				view.update_filters_ui();

				// Block container.
				composite.block( self.$component_filters );
				view.$blocked_element = self.$component_filters;

				view.update_options( { page: 1, filters: self.find_active_filters() }, 'reload' );

				return false;
			},

			reset_filters: function( event ) {

				$( this ).blur();

				// Get active filters.
				var view                     = event.data.view,
					component_filter_options = self.$component_filters.find( '.component_filter_option.selected' );

				if ( component_filter_options.length == 0 ) {
					return false;
				}

				component_filter_options.removeClass( 'selected' );

				// Add/remove 'active' classes.
				view.update_filters_ui();

				// Block container.
				composite.block( self.$component_filters );
				view.$blocked_element = self.$component_filters;

				view.update_options( { page: 1, filters: self.find_active_filters() }, 'reload' );

				return false;
			},

			/**
			 * Add active/filtered classes to the component filters markup, can be used for styling purposes.
			 */
			update_filters_ui: function() {

				var filters   = self.$component_filters.find( '.component_filter' ),
					all_empty = true;

				if ( filters.length == 0 ) {
					return false;
				}

				filters.each( function() {

					if ( $( this ).find( '.component_filter_option.selected' ).length == 0 ) {
						$( this ).removeClass( 'active' );
					} else {
						$( this ).addClass( 'active' );
						all_empty = false;
					}

				} );

				if ( all_empty ) {
					self.$component_filters.removeClass( 'filtered' );
				} else {
					self.$component_filters.addClass( 'filtered' );
				}
			},

			order_by: function( event ) {

				var view    = event.data.view,
					orderby = $( this ).val();

				$( this ).blur();

				// Block container.
				composite.block( self.$component_options );
				view.$blocked_element = self.$component_options;

				view.update_options( { page: 1, orderby: orderby }, 'reload' );

				return false;
			},

			/**
			 * Renders options in the DOM based on 'active_options' model attribute changes.
			 */
			render: function( dropdown_only ) {

				if ( ! composite.is_initialized ) {
					return false;
				}

				dropdown_only = typeof( dropdown_only ) === 'undefined' ? false : dropdown_only;

				composite.console_log( 'debug:views', '\nRendering "' + self.get_title() + '" options in view...' );

				var model               = self.component_options_model,
					active_options      = model.get( 'options_state' ).active,
					selected_product    = self.get_selected_product( false ),
					options_data        = $.extend( true, [], model.available_options_data ),
					change_what         = [];

				this.changes.dropdown.changed   = false;
				this.changes.thumbnails.changed = false;
				this.changes.radios.changed     = false;
				this.changes.variations.changed = false;

				/*
				 * Hide or grey-out inactive products.
				 */

				$.each( options_data, function( index, option_data ) {

					var product_id    = option_data.option_id,
						is_compatible = _.contains( active_options, product_id );

					if ( ! is_compatible ) {
						options_data[ index ].is_disabled = true;
					} else {
						options_data[ index ].is_disabled = false;
					}

					options_data[ index ].is_hidden   = options_data[ index ].is_disabled && self.hide_disabled_products();
					options_data[ index ].is_selected = options_data[ index ].option_id === selected_product;
				} );


				// Dropdown template data.
				var dropdown_options_data = $.extend( true, [], options_data );

				$.each( dropdown_options_data, function( index, option_data ) {
					dropdown_options_data[ index ].option_dropdown_title = self.has_options_style( 'dropdowns' ) ? option_data.option_display_title : option_data.option_title;
					dropdown_options_data[ index ].is_selected           = options_data[ index ].is_selected && self.is_selected_product_valid();
				} );

				var show_empty_option     = false,
					show_switching_option = false,
					empty_option_disabled = false,
					empty_option_title;

				// Always add an empty option when there are no valid options to select - necessary to allow resetting an existing invalid selection.
				if ( active_options.length === 0 ) {

					show_empty_option  = true;
					empty_option_title = wc_composite_params.i18n_no_options;

				} else {

					empty_option_title = self.is_optional() ? wc_composite_params.i18n_no_option.replace( '%s', self.get_title() ) : wc_composite_params.i18n_select_option.replace( '%s', self.get_title() );

					if ( self.maybe_is_optional() ) {

						show_empty_option = true;

						if ( false === self.is_selected_product_valid() ) {
							show_switching_option = true;
						}

						if ( false === self.is_optional() ) {

							if ( '' === selected_product ) {
								show_switching_option = true;
							}

							empty_option_disabled = true;
							empty_option_title    = wc_composite_params.i18n_no_option.replace( '%s', self.get_title() );
						}

					} else if ( false === self.is_static() && self.show_placeholder_option() ) {
						show_empty_option = true;
					} else if ( '' === selected_product && false === self.show_placeholder_option() ) {
						show_empty_option = true;
					} else if ( false === self.is_selected_product_valid() && false === self.show_placeholder_option() ) {
						show_switching_option = true;
					}
				}

				if ( show_empty_option ) {
					dropdown_options_data.unshift( {
						option_id:             '',
						option_dropdown_title: empty_option_title,
						is_disabled:           empty_option_disabled,
						is_hidden:             empty_option_disabled && self.hide_disabled_products(),
						is_selected:           selected_product === '' && false === show_switching_option
					} );
				}

				if ( show_switching_option ) {

					self.$component_options_select.data( 'has_extra_empty_option', true );

					dropdown_options_data.unshift( {
						option_id:             '',
						option_dropdown_title: wc_composite_params.i18n_select_option.replace( '%s', self.get_title() ),
						is_disabled:           false,
						is_hidden:             false,
						is_selected:           false
					} );
				}

				// Render Dropdown template.
				this.changes.dropdown.changed = true;
				this.changes.dropdown.to      = this.templates.dropdown( dropdown_options_data );

				if ( false === dropdown_only ) {

					// Thumbnails template.
					if ( self.has_options_style( 'thumbnails' ) ) {

						var thumbnail_options_data = _.where( options_data, { is_in_view: true } ),
							thumbnail_columns      = parseInt( self.$component_thumbnail_options.data( 'columns' ) ),
							thumbnail_loop         = 0;

						if ( thumbnail_options_data.length > 0 ) {

							$.each( thumbnail_options_data, function( index, option_data ) {

								thumbnail_options_data[ index ].outer_classes  = option_data.is_hidden ? 'hidden' : '';
								thumbnail_options_data[ index ].inner_classes  = option_data.is_disabled ? 'disabled' : '';
								thumbnail_options_data[ index ].inner_classes += option_data.option_id === selected_product ? ' selected' : '';
								thumbnail_options_data[ index ].inner_classes += option_data.is_appended ? ' appended' : '';

								if ( false === option_data.is_hidden ) {

									thumbnail_loop++;

									if ( ( ( thumbnail_loop - 1 ) % thumbnail_columns ) == 0 || thumbnail_columns == 1 ) {
										thumbnail_options_data[ index ].outer_classes += ' first';
									}

									if ( thumbnail_loop % thumbnail_columns == 0 ) {
										thumbnail_options_data[ index ].outer_classes += ' last';
									}
								}
							} );
						}

						// Render Thumbnails template.
						var new_template_html = this.templates.thumbnails( thumbnail_options_data );

						// Ignore 'selected' class changes in comparison.
						if ( new_template_html.replace( / selected/g, '' ) !== this.changes.thumbnails.to.replace( / selected/g, '' ) ) {
							this.changes.thumbnails.changed = true;
							this.changes.thumbnails.to      = new_template_html;
						} else {
							composite.console_log( 'debug:views', '...skipped!' );
						}

					// Radio buttons template.
					} else if ( self.has_options_style( 'radios' ) ) {

						var radio_options_data  = _.where( options_data, { is_in_view: true } ),
							show_empty_radio    = false,
							disable_empty_radio = self.maybe_is_optional() && false === self.is_optional(),
							hide_empty_radio    = disable_empty_radio && self.hide_disabled_products();

						if ( self.maybe_is_optional() ) {
							show_empty_radio = true;
						} else if ( false === self.is_static() && self.show_placeholder_option() ) {
							show_empty_radio = true;
							hide_empty_radio = true;
						}

						if ( show_empty_radio ) {
							radio_options_data.unshift( {
								option_id:             '',
								option_display_title:  wc_composite_params.i18n_no_option.replace( '%s', self.get_title() ),
								is_disabled:           disable_empty_radio,
								is_hidden:             hide_empty_radio,
								is_selected:           selected_product === ''
							} );
						}

						if ( radio_options_data.length > 0 ) {

							$.each( radio_options_data, function( index, option_data ) {

								radio_options_data[ index ].outer_classes  = option_data.is_hidden ? 'hidden' : '';
								radio_options_data[ index ].inner_classes  = option_data.is_disabled ? 'disabled' : '';
								radio_options_data[ index ].inner_classes += option_data.option_id === selected_product ? ' selected' : '';

								radio_options_data[ index ].option_suffix   = option_data.option_id === '' ? '0' : option_data.option_id;
								radio_options_data[ index ].option_group_id = self.component_id;
							} );
						}

						// Render Radio buttons template.
						this.changes.radios.changed = true;
						this.changes.radios.to      = this.templates.radios( radio_options_data );
					}

					/*
					 * Hide or grey-out inactive variations.
					 */

					if ( self.get_selected_product_type() === 'variable' ) {

						var selected_variation    = self.get_selected_variation( false ),
							product_variations    = self.$component_data.data( 'product_variations' ),
							compatible_variations = [],
							variation;

						for ( var i = 0; i < product_variations.length; i++ ) {

							var variation_id  = product_variations[ i ].variation_id.toString(),
								is_compatible = _.contains( active_options, variation_id );

							// Copy all variation objects but set the variation_is_active property to false in order to disable the attributes of incompatible variations.
							// Only if WC v2.3 and disabled variations are set to be visible.
							if ( wc_composite_params.is_wc_version_gte_2_3 === 'yes' && ! self.hide_disabled_variations() ) {

								var variation_has_empty_attributes = false;

								variation = $.extend( true, {}, product_variations[ i ] );

								if ( ! is_compatible ) {

									variation.variation_is_active = false;

									// Do not include incompatible variations with empty attributes - they can break stuff when prioritized.
									for ( var attr_name in variation.attributes ) {
										if ( variation.attributes[ attr_name ] === '' ) {
											variation_has_empty_attributes = true;
											break;
										}
									}

								}

								if ( ! variation_has_empty_attributes ) {
									compatible_variations.push( variation );
								}

							// Copy only compatible variations.
							// Only if disabled variations are set to be hidden.
							} else {
								if ( is_compatible ) {
									compatible_variations.push( product_variations[ i ] );
								} else {
									if ( parseInt( selected_variation ) === parseInt( variation_id ) ) {
										variation                     = $.extend( true, {}, product_variations[ i ] );
										variation.variation_is_active = false;
										compatible_variations.push( variation );
									}
								}
							}
						}

						this.changes.variations.changed = ! _.isEqual( self.$component_summary_content.data( 'product_variations' ), compatible_variations );
						this.changes.variations.to      = compatible_variations;
					}
				}

				change_what = _.keys( _.pick( this.changes, function( value ) { return value.changed; } ) );

				if ( change_what.length > 0 ) {

					// Run 'options_state_render' action - @see WC_CP_Composite_Dispatcher class.
					composite.actions.do_action( 'options_state_render', [ self, change_what ] );

					if ( this.changes.dropdown.changed ) {
						self.$component_options_select.html( this.changes.dropdown.to );
					}

					if ( this.changes.thumbnails.changed ) {
						self.$component_thumbnail_options.html( this.changes.thumbnails.to );
					}

					if ( this.changes.radios.changed ) {
						self.$component_radio_button_options.html( this.changes.radios.to );
					}

					if ( this.changes.variations.changed ) {

						// Put filtered variations in place.
						self.$component_summary_content.data( 'product_variations', this.changes.variations.to );

						// Update the variations script.
						self.$component_summary_content.triggerHandler( 'reload_product_variations' );
					}

					// Run 'options_state_rendered' action - @see WC_CP_Composite_Dispatcher class.
					composite.actions.do_action( 'options_state_rendered', [ self, change_what ] );
				}
			},

			/**
			 * Update options after collecting user input.
			 */
			update_options: function( params, update_action ) {

				this.update_action = update_action;

				if ( 'reload' === update_action ) {
					self.$component_selections.addClass( 'refresh_component_options' );
				}

				if ( typeof self.$component_options.get( 0 ).getBoundingClientRect().height !== 'undefined' ) {
					this.load_height = self.$component_options.get( 0 ).getBoundingClientRect().height;
				} else {
					this.load_height = self.$component_options.outerHeight();
				}

				// Lock height.
				self.$component_options.css( 'height', this.load_height );

				setTimeout( function() {
					self.component_options_model.request_options( params, update_action );
				}, 200 );
			},

			/**
			 * Update view after appending/reloading component options.
			 */
			updated_options: function() {

				if ( false === this.$blocked_element ) {
					return false;
				}

				if ( 'append' === this.update_action && self.hide_disabled_products() ) {
					if ( self.$component_thumbnail_options.find( '.appended:not(.disabled)' ).length < self.get_results_per_page() ) {

						var retry = this.model.get( 'page' ) < this.model.get( 'pages' );

						if ( retry && this.append_results_retry_count > 10 ) {
							if ( false === window.confirm( wc_composite_params.i18n_reload_threshold_exceeded.replace( '%s', self.get_title() ) ) ) {
								retry = false;
							}
						}

						if ( retry ) {
							this.append_results_retry_count++;
							this.model.request_options( { page: this.model.get( 'page' ) + 1 }, 'append' );
							return false;
						} else {
							this.append_results_retry_count = 0;
						}
					}
				}

				// Preload images before proceeding.
				var $thumbnails_container = self.$component_thumbnail_options.find( '.component_option_thumbnails_container' ),
					$thumbnail_images     = $thumbnails_container.find( '.component_option_thumbnail_container:not(.hidden) img' ),
					wait_time             = 0,
					view                  = this;

				var finalize = function() {

					if ( $thumbnail_images.length > 0 && $thumbnails_container.is( ':visible' ) ) {

						var wait = false;

						$thumbnail_images.each( function() {

							var image = $( this );

							if ( image.height() === 0 && wait_time < 10000 ) {
								wait = true;
								return false;
							}

						} );

						if ( wait ) {
							wait_time += 100;
							setTimeout( function() {
								finalize();
							}, 100 );
						} else {
							view.animate_options();
						}
					} else {
						view.animate_options();
					}
				};

				setTimeout( function() {
					finalize();
				}, 10 );
			},

			/**
			 * Animate view when reloading/appending options.
			 */
			animate_options: function() {

				var view           = this,
					new_height     = self.$component_options_inner.outerHeight( true ),
					animate_height = false;

				if ( Math.abs( new_height - view.load_height ) > 1 ) {
					animate_height = true;
				} else {
					self.$component_options.css( 'height', 'auto' );
				}

				var appended = {};

				if ( 'append' === this.update_action ) {
					appended = self.$component_thumbnail_options.find( '.appended' );
					appended.removeClass( 'appended' );
				}

				// Animate component options container.
				if ( animate_height ) {

					if ( 'reload' === view.update_action ) {
						self.$component_selections.removeClass( 'refresh_component_options' );
					}

					self.$component_options.animate( { 'height' : new_height }, { duration: 250, queue: false, always: function() {
						self.$component_options.css( { 'height' : 'auto' } );
						setTimeout( function() {
							view.unblock();
						}, 100 );
					} } );

				} else {
					setTimeout( function() {
						view.unblock();
					}, 250 );
				}

			},

			/**
			 * Unblock blocked view element.
			 */
			unblock: function() {
				self.$component_selections.removeClass( 'refresh_component_options' );
				composite.unblock( this.$blocked_element );
				this.$blocked_element = false;

				// Remove status message.
				composite.data_model.remove_status_message( self.component_id );

				composite.actions.do_action( 'component_options_refreshed_' + self.step_id );
			},

			/**
			 * True if the view is updating.
			 */
			is_updating: function() {

				return false !== this.$blocked_element;
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * Updates the model data from UI interactions and listens to the component selection model for updated content.
	 */
	this.Component_Selection_View = function( component, opts ) {

		var self = component;
		var	View = Backbone.View.extend( {

			templates:                    {},

			$relocation_origin:           false,
			relocated:                    false,

			relocate_component_content:   false,
			relocate_to_origin:           false,
			$relocation_target:           false,
			$relocation_reference:        false,
			load_height:                  0,

			resetting_product:            false,
			resetting_variation:          false,

			flushing_component_options:   false,

			initialize: function() {

				this.templates = {
					selection_title: wp.template( 'wc_cp_component_selection_title' ),
					selection_title_html: ''
				};

				/**
				 * Update model on changing a component option.
				 */
				self.$el.on( 'change', '.component_options select.component_options_select', { view: this }, this.option_changed );

				/**
				 * Update model data when a new variation is selected.
				 */
				self.$el.on( 'woocommerce_variation_has_changed', { view: this }, function( event ) {
					// Update model.
					event.data.view.model.update_selected_variation();
					// Ensure min/max constraints are always honored.
					self.$component_quantity.trigger( 'change' );
					// Remove images class from composited_product_images div in order to avoid styling issues.
					self.$component_summary_content.find( '.composited_product_images' ).removeClass( 'images' );
				} );

				/**
				 * Add 'images' class to composited_product_images div when initiating a variation selection change.
				 */
				self.$el.on( 'woocommerce_variation_select_change', function() {
					// Required by the variations script to flip images.
					self.$component_summary.find( '.composited_product_images' ).addClass( 'images' );
					// Reset component prices.
					self.$component_data.data( 'price', 0.0 );
					self.$component_data.data( 'regular_price', 0.0 );

					var custom_data = self.$component_data.data( 'custom' );

					custom_data[ 'price_tax' ] = 1.0;
				} );

				/**
				 * Update composite totals and form inputs when a new variation is selected.
				 */
				self.$el.on( 'found_variation', function( event, variation ) {
					// Update component prices.
					self.$component_data.data( 'price', variation.price );
					self.$component_data.data( 'regular_price', variation.regular_price );

					var custom_data = self.$component_data.data( 'custom' );

					custom_data[ 'price_tax' ] = variation.price_tax;
				} );

				/**
				 * Update model upon changing quantities.
				 */
				self.$el.on( 'input change', '.component_wrap input.qty', function( e ) {

					var min = parseFloat( $( this ).attr( 'min' ) ),
						max = parseFloat( $( this ).attr( 'max' ) );

					if ( 'change' === e.type && min >= 0 && ( parseFloat( $( this ).val() ) < min || isNaN( parseFloat( $( this ).val() ) ) ) ) {
						$( this ).val( min );
					}

					if ( 'change' === e.type && max > 0 && parseFloat( $( this ).val() ) > max ) {
						$( this ).val( max );
					}

					if ( ! self.initializing_scripts ) {
						self.component_selection_model.update_selected_quantity();
					}
				} );

				/**
				 * Initialize prettyPhoto/phptoSwipe script when component selection scripts are initialized.
				 */
				self.$el.on( 'wc-composite-component-loaded', { view: this }, function() {

					// Init PhotoSwipe if present.
					if ( 'yes' === wc_composite_params.photoswipe_enabled && typeof PhotoSwipe !== 'undefined' ) {

						var $product_image = self.$component_summary_content.find( '.composited_product_images' );

						$product_image.wc_product_gallery( { zoom_enabled: false, flexslider_enabled: false } );

						var $placeholder = $product_image.find( 'a.placeholder_image' );

						if ( $placeholder.length > 0 ) {
							$placeholder.on( 'click', function() {
								return false;
							} );
						}

					// Otherwise, fall back to prettyPhoto.
					} else if ( $.isFunction( $.fn.prettyPhoto ) ) {

						var $prettyphoto_images = self.$component_summary_content.find( 'a[data-rel^="prettyPhoto"]' ),
							$active_images      = $prettyphoto_images.not( '.placeholder_image' ),
							$inactive_images    = $prettyphoto_images.filter( '.placeholder_image' );

						if ( $active_images.length > 0 ) {
							$active_images.prettyPhoto( {
								hook: 'data-rel',
								social_tools: false,
								theme: 'pp_woocommerce',
								horizontal_padding: 20,
								opacity: 0.8,
								deeplinking: false
							} );
						}

						if ( $inactive_images.length > 0 ) {
							$inactive_images.on( 'click', function() {
								return false;
							} );
						}
					}
				} );

				/**
				 * On clicking the clear options button.
				 */
				self.$el.on( 'click', '.clear_component_options', function() {

					if ( $( this ).hasClass( 'reset_component_options' ) ) {
						return false;
					}

					var empty_option = self.$component_options_select.find( 'option[value=""]' );

					if ( empty_option.length > 0 && false === empty_option.first().prop( 'disabled' ) ) {
						self.$component_options_select.val( '' ).change();
					}

					return false;
				} );

				/**
				 * On clicking the reset options button.
				 */
				self.$el.on( 'click', '.reset_component_options', function() {

					var empty_option = self.$component_options_select.find( 'option[value=""]' );

					self.unblock_step_inputs();

					self.set_active();

					if ( empty_option.length > 0 && false === empty_option.first().prop( 'disabled' ) ) {
						self.$component_options_select.val( '' ).change();
					}

					self.block_next_steps();

					return false;
				} );

				/**
				 * Update model upon changing addons selections.
				 */
				self.$el.on( 'updated_addons', this.updated_addons_handler );

				/**
				 * Update composite totals when a new NYP price is entered.
				 */
				self.$el.on( 'woocommerce-nyp-updated-item', this.updated_nyp_handler );

				/*
				 * When leaving a component with relocated selection details,
				 * reset the position of the relocated container if the 'relocated_content_reset_on_return' flag is set to 'yes'.
				 */
				if ( wc_composite_params.relocated_content_reset_on_return === 'yes' ) {
					composite.actions.add_action( 'active_step_transition_start', this.active_step_transition_start_handler, 100, this );
				}

				/**
				 * Update "Clear selection" button in view.
				 */
				composite.actions.add_action( 'options_state_changed_' + self.step_id, this.options_state_changed_handler, 100, this );

				/*
				 * When rendering a component with relocated selection details,
				 * back up and put back the relocated container after rendering the JS template contents.
				 */
				composite.actions.add_action( 'options_state_render', this.options_state_render_handler, 10, this );
				composite.actions.add_action( 'options_state_rendered', this.options_state_rendered_handler, 10, this );

				/**
				 * Update the selection title when the product selection is changed.
				 */
				composite.actions.add_action( 'component_selection_changed', this.refresh_selection_title, 100, this );

				/**
				 * Render selection details responses into view.
				 */
				this.listenTo( this.model, 'component_selection_details_loaded', this.selection_details_loaded );
				this.listenTo( this.model, 'component_selection_details_load_error', this.selection_details_load_error );

				/**
				 * Reset relocated content before flushing outdated component options.
				 */
				this.listenTo( self.component_options_model, 'component_options_data_loaded', this.component_options_flush_handler );

				/**
				 * Update the selection title when the quantity is changed.
				 */
				composite.actions.add_action( 'component_quantity_changed', this.quantity_changed_handler, 100, this );
			},

			/**
			 * Allows filtering animation durations.
			 */
			get_animation_duration: function( open_or_close ) {

				var duration = 200;

				open_or_close = open_or_close !== 'open' && open_or_close !== 'close' ? 'open' : open_or_close;

				// Pass through 'component_animation_duration' filter - @see WC_CP_Filters_Manager class.
				return composite.filters.apply_filters( 'component_selection_change_animation_duration', [ duration, open_or_close, self ] );
			},

			/**
			 * Resets the position of the relocated container when the active step changes.
			 */
			active_step_transition_start_handler: function( step ) {

				if ( self.step_id !== step.step_id ) {
					if ( this.is_relocated() ) {
						this.reset_relocated_content();
					}
				}
			},

			/**
			 * Redraw the selection title.
			 */
			options_state_changed_handler: function() {

				this.update_selection_title();
			},

			/**
			 * Backup the relocated container before appending new thumbnails:
			 */
			options_state_render_handler: function( step, changed ) {

				if ( self.step_id === step.step_id ) {
					if ( _.contains( changed, 'thumbnails' ) && this.is_relocated() ) {
						// Save component content.
						self.$el.append( self.$component_content.hide() );
					}
				}
			},

			/**
			 * Put back the relocated container after appending new thumbnails:
			 */
			options_state_rendered_handler: function( step, changed ) {

				if ( self.step_id === step.step_id ) {
					if ( _.contains( changed, 'thumbnails' ) && this.is_relocated() ) {
						var relocation_params = this.get_content_relocation_params();
						if ( relocation_params.relocate ) {
							this.$relocation_target    = $( '<li class="component_option_content_container">' );
							this.$relocation_reference = relocation_params.reference;
							this.$relocation_reference.after( this.$relocation_target );
							self.$component_content.appendTo( this.$relocation_target );
							self.$component_content.show();
						}
					}
				}
			},

			/**
			 * Updates the selection title when the quantity is changed.
			 */
			quantity_changed_handler: function( step ) {

				if ( step.step_id === self.step_id ) {
					this.update_selection_title( this.model );
				}
			},

			/**
			 * Updates the model upon changing addons selections.
			 */
			updated_addons_handler: function() {

				self.component_selection_model.update_selected_addons();
			},

			/**
			 * Updates the composite data model upon changing addons selections.
			 */
			updated_nyp_handler: function() {

				self.component_selection_model.update_nyp();
			},

			/**
			 * Refreshes the selection title every time it changes.
			 */
			refresh_selection_title: function( step ) {

				if ( step.step_id === self.step_id ) {
					this.update_selection_title( this.model );
				}
			},

			/**
			 * Renders the selected product title and the "Clear selection" button.
			 */
			update_selection_title: function( model ) {

				var view = this;

				model = typeof ( model ) === 'undefined' ? view.model : model;

				if ( self.get_selected_product( false ) > 0 ) {
					composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" selection title...' );
					view.update_selection_title_task( model );
				}
			},

			/**
			 * Gets the selected product title and appends quantity data.
			 */
			get_updated_selection_title: function( model ) {

				var selection_qty            = parseInt( model.get( 'selected_quantity' ) ),
					selection_title          = self.get_selected_product_title( false ),
					selection_qty_string     = selection_qty > 1 ? wc_composite_params.i18n_qty_string.replace( '%s', selection_qty ) : '',
					selection_title_incl_qty = wc_composite_params.i18n_title_string.replace( '%t', selection_title ).replace( '%q', selection_qty_string ).replace( '%p', '' );

				return selection_title_incl_qty;
			},

			/**
			 * Renders the selected product title and the "Clear selection" button.
			 */
			update_selection_title_task: function( model ) {

				var $title_html = self.$component_summary_content.find( '.composited_product_title_wrapper' ),
					view        = this,
					data        = {
						tag:               'single' === composite.settings.layout || 'progressive' === composite.settings.layout ? 'p' : 'h4',
						show_title:        'yes' === $title_html.data( 'show_title' ),
						show_selection_ui: self.is_static() ? false : true,
						show_reset_ui:     ( self.show_placeholder_option() && false === self.maybe_is_optional() ) || self.is_optional() || false === self.is_selected_product_valid(),
						selection_title:   view.get_updated_selection_title( model )
					};

				var new_template_html = view.templates.selection_title( data );

				if ( new_template_html !== view.templates.selection_title_html ) {
					view.templates.selection_title_html = new_template_html;
					$title_html.html( new_template_html );
				}
			},

			/**
			 * Initializes the view by triggering selection-related scripts.
			 */
			init_dependencies: function() {

				self.init_scripts();
			},

			/**
			 * Blocks the composite form and adds a waiting ui cue in the working element.
			 */
			block: function() {

				if ( self.has_options_style( 'thumbnails' ) ) {
					composite.block( self.$component_thumbnail_options.find( '.selected' ) );
				} else {
					composite.block( self.$component_options );
				}
			},

			/**
			 * Unblocks the composite form and removes the waiting ui cue from the working element.
			 */
			unblock: function() {

				if ( self.has_options_style( 'thumbnails' ) ) {
					composite.unblock( self.$component_thumbnail_options.find( '.selected' ) );
				} else {
					composite.unblock( self.$component_options );
				}
			},

			/**
			 * Collect component option change input.
			 */
			option_changed: function( event ) {

				var view                = event.data.view,
					selected_product_id = $( this ).val();

				$( this ).blur();

				view.set_option( selected_product_id );

				return false;
			},

			/**
			 * Update model on changing a component option.
			 */
			set_option: function( option_id ) {

				var view = this;

				// Exit if triggering 'change' for the existing selection.
				if ( self.get_selected_product( false ) === option_id ) {
					return false;
				}

				// Toggle thumbnail/radio selection state.
				if ( self.has_options_style( 'thumbnails' ) ) {
					self.$component_thumbnail_options.find( '.selected' ).removeClass( 'selected' );
					self.$component_thumbnail_options.find( '#component_option_thumbnail_' + option_id ).addClass( 'selected' );
				} else if ( self.has_options_style( 'radios' ) ) {
					var $selected = self.$component_radio_button_options.find( '.selected' );
					$selected.removeClass( 'selected' );
					$selected.find( 'input' ).prop( 'checked', false );
					self.$component_options.find( '#component_option_radio_button_' + ( option_id === '' ? '0' : option_id ) ).addClass( 'selected' ).find( 'input' ).prop( 'checked', true );
				}

				if ( option_id !== '' ) {

					// Block composite form + add waiting cues.
					this.block();

					// Add updating class to content.
					self.$component_content.addClass( 'updating' );

					setTimeout( function() {
						// Request product details from model and let the model update itself.
						view.model.request_details( option_id );
					}, 120 );

				} else {

					// Handle selection resets within the view, but update the model data.
					this.model.selected_product = '';
					this.selection_details_loaded( false );
				}
			},

			/**
			 * Auto-reset option if invalid.
			 */
			reset_option: function() {

				var reset_id = '';

				this.resetting_product = true;
				this.set_option( reset_id );
			},

			/**
			 * Re-set current option.
			 */
			 selection_details_load_error: function() {

			 	var option_id = self.get_selected_product( false );

			 	self.$component_options_select.val( option_id ).change();

			 	this.unblock();

				// Toggle thumbnail/radio selection state.
				if ( self.has_options_style( 'thumbnails' ) ) {
					self.$component_thumbnail_options.find( '.selected' ).removeClass( 'selected' );
					self.$component_thumbnail_options.find( '#component_option_thumbnail_' + option_id ).addClass( 'selected' );
				} else if ( self.has_options_style( 'radios' ) ) {
					var $selected = self.$component_radio_button_options.find( '.selected' );
					$selected.removeClass( 'selected' );
					$selected.find( 'input' ).prop( 'checked', false );
					self.$component_options.find( '#component_option_radio_button_' + ( option_id === '' ? '0' : option_id ) ).addClass( 'selected' ).find( 'input' ).prop( 'checked', true );
				}

			 	self.$component_content.removeClass( 'updating' );

			 	window.alert( wc_composite_params.i18n_selection_request_timeout );
			 },

			/**
			 * Update view with new selection details passed by model.
			 */
			selection_details_loaded: function( response ) {

				var view                = this,
					selected_product    = this.model.selected_product,
					relocations_allowed = this.relocations_allowed();

				if ( typeof self.$component_content.get( 0 ).getBoundingClientRect().height !== 'undefined' ) {
					view.load_height = self.$component_content.get( 0 ).getBoundingClientRect().height;
				} else {
					view.load_height = self.$component_content.outerHeight();
				}

				view.relocate_component_content = false;
				view.relocate_to_origin         = false;
				view.$relocation_target         = false;
				view.$relocation_reference      = false;

				// Save initial location of component_content div.
				if ( relocations_allowed ) {
					if ( false === view.$relocation_origin ) {
						view.$relocation_origin = $( '<div class="component_content_origin">' );
						self.$component_content.before( view.$relocation_origin );
					}
				}

				// Check if fetched component content will be relocated under current product thumbnail.
				if ( relocations_allowed ) {

					var relocation_params           = view.get_content_relocation_params();

					view.$relocation_reference      = relocation_params.reference;
					view.relocate_component_content = relocation_params.relocate;

				} else if ( view.is_relocated() ) {

					view.relocate_component_content = true;
					view.relocate_to_origin         = true;
				}

				// Get the selected product data.
				if ( selected_product !== '' ) {

					// Check if component_content div must be relocated.
					if ( view.relocate_component_content ) {

						if ( view.relocate_to_origin ) {

							// Animate component content height to 0.
							// Then, reset relocation and update content.
							self.$component_content.animate( { 'height': 0 }, { duration: view.get_animation_duration( 'close' ), queue: false, always: function() {
								view.reset_relocated_content();
								view.update_content( response.markup );
							} } );

							view.load_height = 0;

						} else {

							var was_in_viewport = self.$component_content.wc_cp_is_in_viewport( true );

							view.relocated = true;

							self.$component_content.addClass( 'relocated' );
							self.$component_content.addClass( 'relocating' );

							view.$relocation_target = $( '<li class="component_option_content_container">' );
							view.$relocation_reference.after( view.$relocation_target );

							var do_illusion_scroll = self.$component_content.offset().top < view.$relocation_target.offset().top && ! was_in_viewport;

							// Animate component content height to 0 while scrolling as much as its height (if needed).
							// Then, update content.
							if ( do_illusion_scroll ) {

								var illusion_scroll_to     = 0,
									illusion_scroll_offset = view.load_height,
									remaining_scroll       = $wc_cp_document.height() - $wc_cp_window.scrollTop() - $wc_cp_window.height();

								// Prevent out-of-bounds scrolling.
								if ( view.load_height > remaining_scroll ) {
									illusion_scroll_offset = remaining_scroll;
								}

								illusion_scroll_to = $wc_cp_window.scrollTop() - Math.floor( illusion_scroll_offset );

								// Introduce async to hopefully do this between repaints and avoid flicker.
								setTimeout( function() {

									// Set height to 0.
									self.$component_content.css( { 'height': illusion_scroll_offset - Math.floor( illusion_scroll_offset ) } );

									// ...while scrolling as much as the height offset.
									window.scroll( 0, illusion_scroll_to );

									setTimeout( function() {
										// Update content.
										view.update_content( response.markup );
									}, 10 );

								}, 100 );

							} else {
								self.$component_content.animate( { 'height': 0 }, { duration: view.get_animation_duration( 'close' ), queue: false, always: function() {
									view.update_content( response.markup );
								} } );
							}

							view.load_height = 0;
						}

					} else {

						// Lock height.
						self.$component_content.css( 'height', view.load_height );

						// Process response content.
						view.update_content( response.markup );
					}

				} else {

					var animate = true;

					if ( view.resetting_product && composite.settings.layout !== 'single' ) {
						animate = false;
					}

					if ( animate ) {

						// Set to none just in case a script attempts to read this.
						self.$component_data.data( 'product_type', 'none' );

						// Allow the appended message container to remain visible.
						var navigation_movable_height = composite.$composite_navigation_movable.is( ':visible' ) ? composite.$composite_navigation_movable.outerHeight( true ) : 0;
						var reset_height              = view.is_relocated() ? 0 : ( self.$component_summary.outerHeight( true ) - self.$component_summary_content.innerHeight() - navigation_movable_height );

						// Animate component content height.
						self.$component_content.animate( { 'height': reset_height }, { duration: view.get_animation_duration( 'close' ), queue: false, always: function() {

							// Reset content.
							view.reset_content();

							self.$component_content.css( { 'height': 'auto' } );

						} } );

					} else {
						// Reset content.
						view.reset_content();
					}
				}
			},

			/**
			 * Updates view with new selection details markup.
			 */
			update_content: function( content ) {

				var view = this;

				// Reset scripts/classes before replacing markup.
				self.reset_scripts();

				// Put content in place.
				self.$component_summary_content.addClass( 'populated' );
				self.$component_summary_content.html( content );

				// Remove clearing button if the loaded product is invalid and the current selection can't be reset.
				if ( content.indexOf( 'data-product_type="invalid-product"' ) > 0 ) {
					var empty_option = self.$component_options_select.find( 'option[value=""]' );
					if ( empty_option.length === 0 || empty_option.first().prop( 'disabled' ) ) {
						self.$component_summary_content.find( '.clear_component_options' ).remove();
					}
				}

				// Relocate content.
				if ( view.relocate_component_content ) {
					self.$component_content.appendTo( view.$relocation_target );
					self.$component_options.find( '.component_option_content_container' ).not( view.$relocation_target ).remove();
				}

				// Clear selection title template html.
				this.templates.selection_title_html = '';

				view.updated_content();

				var wait_time                  = 0,
					$thumbnail_image_container = self.$component_summary_content.find( '.composited_product_images' ),
					$thumbnail_image           = $thumbnail_image_container.find( 'img' );

				var finalize = function() {

					if ( $thumbnail_image_container.length > 0 && $thumbnail_image_container.is( ':visible' ) && $thumbnail_image.length > 0 ) {
						if ( $thumbnail_image.height() === 0 && wait_time < 1000 ) {
								wait_time += 50;
								setTimeout( function() {
									finalize();
								}, 50 );
						} else {
							view.animate_updated_content();
						}
					} else {
						view.animate_updated_content();
					}
				};

				setTimeout( function() {
					finalize();
				}, 300 );
			},

			/**
			 * Update model and trigger scripts after updating view with selection content.
			 */
			updated_content: function() {

				var reset_product = this.resetting_product;

				if ( this.model.selected_product > 0 ) {
					self.init_scripts();
				} else {
					self.init_scripts( false );
				}

				this.resetting_product = false;

				// Update the model.
				this.model.update_selected_product();

				// Refresh options state.
				self.component_options_model.refresh_options_state( self );

				// Refresh options view if resetting.
				if ( reset_product ) {
					self.component_options_view.render();
				}

				// Redraw dropdowns if placeholder options were added.
				if ( true === self.$component_options_select.data( 'has_extra_empty_option' ) ) {
					self.$component_options_select.data( 'has_extra_empty_option', false );
					self.component_options_view.render( true );
				}
			},

			animate_updated_content: function() {

				// Measure height.
				var new_height     = self.$component_summary.outerHeight( true ),
					animate_height = false,
					view           = this;

				if ( this.relocate_component_content || Math.abs( new_height - this.load_height ) > 1 ) {
					animate_height = true;
				} else {
					self.$component_content.css( 'height', 'auto' );
				}

				if ( this.is_relocated() ) {
					self.$component_content.removeClass( 'relocating' );
				}

				// Animate component content height and scroll to selected product details.
				if ( animate_height ) {

					composite.console_log( 'debug:events', 'Starting updated content animation...' );

					// Animate component content height.
					self.$component_content.animate( { 'height': new_height }, { duration: view.get_animation_duration( 'open' ), queue: false, always: function() {

						composite.console_log( 'debug:events', 'Ended updated content animation.' );

						// Scroll...
						wc_cp_scroll_viewport( self.$component_content, { offset: 50, partial: composite.settings.layout !== 'paged', scroll_method: 'middle', duration: 200, queue: false, always_on_complete: true, on_complete: function() {

							// Reset height.
							self.$component_content.css( { 'height' : 'auto' } );

							// Unblock.
							view.unblock();
							self.$component_content.removeClass( 'updating' );

						} } );

					} } );

				} else {

					// Scroll.
					wc_cp_scroll_viewport( self.$component_content, { offset: 50, partial: composite.settings.layout !== 'paged', scroll_method: 'middle', duration: 200, queue: false, always_on_complete: true, on_complete: function() {

						// Unblock.
						view.unblock();
						self.$component_content.removeClass( 'updating' );

					} } );
				}

			},

			reset_content: function() {

				// Reset scripts/classes before emptying markup.
				self.reset_scripts();

				// Reset content.
				self.$component_summary_content.html( '<div class="component_data" data-price="0" data-regular_price="0" data-product_type="none" style="display:none;"></div>' );
				self.$component_summary_content.removeClass( 'populated' );

				// Remove appended navi.
				if ( self.$el.find( '.composite_navigation.movable' ).length > 0 ) {
					composite.$composite_navigation_movable.addClass( 'hidden' );
				}

				// Clear selection title template to resolve an issue with rendering after clearing and selecting the same product.
				this.templates.selection_title_html = '';

				this.reset_relocated_content();
				this.updated_content();
			},

			/**
			 * Move relocated view back to its original position before reloading component options into our Component_Options_View.
			 */
			component_options_flush_handler: function( response, render_type ) {

				if ( this.is_relocated() && render_type === 'reload' && response.result === 'success' ) {
					this.flushing_component_options = true;
					this.reset_relocated_content();
					this.flushing_component_options = false;
				}
			},

			/**
			 * Move relocated view back to its original position.
			 */
			reset_relocated_content: function() {

				if ( this.is_relocated() ) {

					// Hide message if visible.
					self.$component_message.hide();

					if ( this.flushing_component_options ) {
						self.$component_content.hide();
					}

					// Move content to origin.
					self.component_selection_view.$relocation_origin.after( self.$component_content );

					if ( this.flushing_component_options ) {
						setTimeout( function() {
							self.$component_content.slideDown( 250 );
							// Scroll to component options.
							wc_cp_scroll_viewport( 'relative', { offset: -self.$component_summary.outerHeight( true ), timeout: 0, duration: 250, queue: false } );
						}, 200 );
					}

					// Remove origin and relocation container.
					self.component_selection_view.$relocation_origin.remove();
					self.component_selection_view.$relocation_origin = false;
					self.$component_options.find( '.component_option_content_container' ).remove();

					if ( false === this.flushing_component_options ) {
						if ( this.model.selected_product === '' ) {
							// Scroll to selections.
							wc_cp_scroll_viewport( self.$component_selections, { partial: false, duration: 250, queue: false } );
						}
					}

					this.relocated = false;
					self.$component_content.removeClass( 'relocated' );
				}
			},

			/**
			 * True if the view is allowed to relocate below the thumbnail.
			 */
			relocations_allowed: function() {

				if ( composite.settings.layout === 'paged' && self.append_results() && self.has_options_style( 'thumbnails' ) && ! self.$el.hasClass( 'disable-relocations' ) ) {
					if ( self.$component_options.height() > $wc_cp_window.height() ) {
						return true;
					}
				}

				return false;
			},

			/**
			 * True if the component_content container is relocated below the thumbnail.
			 */
			is_relocated: function() {

				return this.relocated;
			},

			/**
			 * Get relocation parameters for this view, when allowed. Returns:
			 *
			 * - A thumbnail (list item) to be used as the relocation reference (the relocated content should be right after this element).
			 * - A boolean indicating whether the view should be moved under the reference element.
			 */
			get_content_relocation_params: function() {

				var relocate_component_content = false,
					$relocation_reference      = false,
					$selected_thumbnail        = self.$component_options.find( '.component_option_thumbnail.selected' ).closest( '.component_option_thumbnail_container' ),
					thumbnail_to_column_ratio  = $selected_thumbnail.outerWidth( true ) / self.$component_options.outerWidth(),
					$last_thumbnail_in_row     = ( $selected_thumbnail.hasClass( 'last' ) || thumbnail_to_column_ratio > 0.6 ) ? $selected_thumbnail : $selected_thumbnail.nextAll( '.last' ).first();

				if ( $last_thumbnail_in_row.length > 0 ) {
					$relocation_reference = $last_thumbnail_in_row;
				} else {
					$relocation_reference = self.$component_options.find( '.component_option_thumbnail_container' ).last();
				}

				if ( $relocation_reference.next( '.component_option_content_container' ).length === 0 ) {
					relocate_component_content = true;
				}

				return { reference: $relocation_reference,  relocate: relocate_component_content };
			}

		} );

		var obj = new View( opts );
		return obj;
	};

	/**
	 * Updates step title elements by listening to step model changes.
	 */
	this.Step_Title_View = function( step, opts ) {

		var self = step;
		var View = Backbone.View.extend( {

			$step_title_index: false,

			initialize: function() {

				this.$step_title_index = self.$step_title.find( '.step_index' );

				if ( step.is_component && self.has_toggle() ) {

					/**
					 * On clicking toggled component titles.
					 */
					this.$el.on( 'click', this.clicked_title_handler );

					if ( composite.settings.layout === 'progressive' ) {

						/**
					 	 * Update view when access to the step changes.
						 */
						composite.actions.add_action( 'step_access_changed', this.step_access_changed_handler, 100, this );

						/**
					 	 * Update view on transitioning to a new step.
						 */
						composite.actions.add_action( 'active_step_changed', this.active_step_changed_handler, 100, this );
					}
				}

				if ( false !== this.$step_title_index ) {
					/**
					 * Update step title indexes.
					 */
					composite.actions.add_action( 'step_visibility_changed', this.step_visibility_changed_handler, 100, this );
				}
			},

			clicked_title_handler: function() {

				$( this ).blur();

				if ( ! self.has_toggle() ) {
					return false;
				}

				if ( composite.settings.layout === 'single' ) {
					wc_cp_toggle_element( self.$el, self.$component_inner );
				} else {

					if ( self.is_current() ) {
						return false;
					}

					if ( $( this ).hasClass( 'inactive' ) ) {
						return false;
					}

					composite.navigate_to_step( self );
				}

				return false;
			},

			step_access_changed_handler: function( step ) {

				if ( step.step_id === self.step_id ) {
					this.render_navigation_state();
				}
			},

			active_step_changed_handler: function() {

				this.render_navigation_state();
			},

			/**
			 * Update progressive component title based on lock state.
			 */
			render_navigation_state: function() {

				if ( composite.settings.layout === 'progressive' && self.has_toggle() ) {

					composite.console_log( 'debug:views', '\nUpdating "' + self.get_title() + '" component title state...' );

					if ( self.is_current() ) {
						this.$el.removeClass( 'inactive' );
					} else {
						if ( self.is_locked() ) {
							this.$el.addClass( 'inactive' );
						} else {
							this.$el.removeClass( 'inactive' );
						}
					}
				}
			},

			/**
			 * Render step title index.
			 */
			render_index: function() {

				if ( ! composite.is_initialized ) {
					return false;
				}

				if ( false === this.$step_title_index ) {
					return false;
				}

				// Count number of hidden components before this one.
				var title_index = step.get_title_index();

				// Refresh index in step title.
				this.$step_title_index.text( title_index );
			},

			step_visibility_changed_handler: function( step ) {

				if ( self.step_index < step.step_index ) {
					return false;
				}

				this.render_index();
			}

		} );

		var obj = new View( opts );
		return obj;
	};

};
