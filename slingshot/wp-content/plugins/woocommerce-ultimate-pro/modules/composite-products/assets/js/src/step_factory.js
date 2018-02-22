/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* global wc_cp_get_variation_data */
/* @endexclude */

/**
 * Factory class for creating new step objects.
 */
wc_cp_classes.WC_CP_Step_Factory = function() {

	/**
     * Step class.
     */
	function WC_CP_Step( composite, $step, index ) {

		var self                 = this;

		this.step_id             = $step.data( 'item_id' );
		this.step_index          = index;
		this.step_title          = $step.data( 'nav_title' );
		this.step_slug           = composite.$composite_data.data( 'composite_settings' ).slugs[ this.step_id ];

		this._component_messages = [];
		this._composite_messages = [];

		this._is_component       = $step.hasClass( 'component' );
		this._is_review          = $step.hasClass( 'cart' );

		this._is_current         = $step.hasClass( 'active' );
		this._is_previous        = $step.hasClass( 'prev' );
		this._is_next            = $step.hasClass( 'next' );

		this._toggled            = $step.hasClass( 'toggled' );

		this._autotransition     = $step.hasClass( 'autotransition' );

		this.$el                 = $step;
		this.$inner_el           = $step.find( '.component_inner' );

		this.$step_title         = $step.find( '.step_title_wrapper' );

		/**
		* @deprecated
		*/
		this.$self               = $step;

		/**
		 * Step actions - @see WC_CP_Actions_Dispatcher class.
		 */
		this.add_actions = function() {

			/*
			 * Viewport auto-scrolling.
			 */
			if ( composite.settings.layout === 'single' ) {
				// Viewport auto-scrolling on the 'show_step' action.
				composite.actions.add_action( 'show_step_' + self.step_id, this.autoscroll_single, 10, this );
			} else if ( composite.settings.layout === 'paged' ) {
				// Viewport auto-scrolling on the 'active_step_changed' action.
				composite.actions.add_action( 'active_step_changed', this.autoscroll_paged, 120, this );
				// Viewport auto-scrolling on the 'active_step_transition_end' action.
				composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.autoscroll_paged_relocated, 10, this );
			} else if ( composite.settings.layout === 'progressive' ) {
				// Viewport auto-scrolling on the 'active_step_transition_end' hook.
				composite.actions.add_action( 'active_step_transition_end_' + self.step_id, this.autoscroll_progressive, 10, this );
			}

			/**
			 * Update current step pointers when the visibility of a step changes.
			 */
			composite.actions.add_action( 'step_visibility_changed', this.step_visibility_changed_handler, 10, this );
		};

		/**
		 * Current step updates pointers when the visibility of a step changes.
		 */
		this.step_visibility_changed_handler = function() {

			if ( composite.settings.layout !== 'paged' ) {
				if ( false === self.is_visible() ) {
					if ( ! composite.is_initialized ) {
						self.$el.hide();
					} else {
						self.$el.slideUp( 200 );
					}
				} else {
					self.$el.slideDown( 200 );
				}
			}

			if ( ! composite.is_initialized ) {
				return false;
			}

			if ( this.is_current() ) {
				composite.set_current_step( composite.get_current_step() );
			}
		};

		/**
		 * Single layout auto-scrolling behaviour on the 'show_step' hook - single layout.
		 */
		this.autoscroll_single = function() {

			var do_scroll = ( composite.is_initialized === false ) ? false : true;

			// Scroll to the desired section.
			if ( do_scroll ) {
				wc_cp_scroll_viewport( self.$el, { partial: false, duration: 250, queue: false } );
			}
		};

		/**
		 * Paged layout auto-scrolling behaviour on the 'show_step' hook.
		 */
		this.autoscroll_paged = function( step ) {

			if ( self.step_id === step.step_id ) {

				var do_scroll    = ( composite.is_initialized === false ) ? false : true,
					is_component = self.is_component(),
					component    = is_component ? self : false;

				if ( ! is_component || ! component.component_selection_view.is_relocated() ) {
					if ( do_scroll ) {
						wc_cp_scroll_viewport( composite.$composite_transition_helper, { timeout: 20, partial: false, duration: 250, queue: false } );
					}
				}
			}
		};

		/**
		 * Paged layout auto-scrolling behaviour on the 'active_step_transition_end' hook - relocated content.
		 */
		this.autoscroll_paged_relocated = function() {

			var do_scroll    = ( composite.is_initialized === false ) ? false : true,
				is_component = self.is_component(),
				component    = is_component ? self : false;

			if ( is_component && component.component_selection_view.is_relocated() ) {
				if ( do_scroll ) {
					wc_cp_scroll_viewport( component.$component_content, { timeout: 0, partial: false, duration: 250, queue: false, scroll_method: 'middle' } );
				}
			}
		};

		/**
		 * Prog layout auto-scrolling behaviour on the 'active_step_transition_end' hook.
		 */
		this.autoscroll_progressive = function() {

			var do_scroll = ( composite.is_initialized === false ) ? false : true;

			// Scroll.
			if ( do_scroll && self.$el.hasClass( 'autoscrolled' ) ) {
				if ( ! self.$step_title.wc_cp_is_in_viewport( false ) ) {
					wc_cp_scroll_viewport( self.$el, { timeout: 0, partial: false, duration: 250, queue: false } );
				}
			}
		};

		/**
		 * True if the step is configured to transition automatically to the next when a valid selection is made.
		 */
		this.autotransition = function() {

			return this._autotransition;
		};

		/**
		 * Reads the navigation permission of this step.
		 */
		this.is_animating = function() {

			return this.$el.hasClass( 'animating' );
		};

		/**
		 * True if the step UI is toggled.
		 */
		this.has_toggle = function() {

			return this._toggled;
		};

		/**
		 * Reads the navigation permission of this step.
		 */
		this.is_locked = function() {

			var is_locked = this.step_access_model.get( 'is_locked' );

			// Pass through 'step_is_locked' filter - @see WC_CP_Filters_Manager class.
			return composite.filters.apply_filters( 'step_is_locked', [ is_locked, this ] );
		};

		/**
		 * True if the step is visible.
		 */
		this.is_visible = function() {

			return this.step_visibility_model.get( 'is_visible' );
		};

		/**
		 * Forbids navigation to this step.
		 */
		this.lock = function() {

			this.step_access_model.set( { locked: true } );
		};

		/**
		 * Permits navigation to this step.
		 */
		this.unlock = function() {

			this.step_access_model.set( { locked: false } );
		};

		/**
		 * Numeric index of this step for use in titles.
		 */
		this.get_title_index = function() {

			var hidden_steps_before = _.filter( composite.get_steps(), function( check_step ) {
				if ( false === check_step.step_visibility_model.get( 'is_visible' ) && check_step.step_index < self.step_index ) {
					return check_step;
				}
			} ).length;

			return this.step_index + 1 - hidden_steps_before;
		};

		this.get_title = function() {

			return this.step_title;
		};

		this.get_element = function() {

			return this.$el;
		};

		this.is_review = function() {

			return this._is_review;
		};

		this.is_component = function() {

			return this._is_component;
		};

		this.get_component = function() {

			if ( this._is_component ) {
				return this;
			} else {
				return false;
			}
		};

		this.is_current = function() {

			return this._is_current;
		};

		this.is_next = function() {

			return this._is_next;
		};

		this.is_previous = function() {

			return this._is_previous;
		};

		/**
		 * Brings a new step into view - called when clicking on a navigation element.
		 */
		this.show_step = function() {

			if ( this.is_locked() || this.is_animating() ) {
				return false;
			}

			var	is_current = this.is_current();

			if ( composite.settings.layout === 'single' ) {
				// Toggle open if possible.
				this.toggle_step( 'open', true );
			}

			if ( ! is_current || ! composite.is_initialized ) {
				// Move active component.
				this.set_active();
			}

			// Run 'show_step' action - @see WC_CP_Actions_Dispatcher class description.
			composite.actions.do_action( 'show_step', [ this ] );
		};

		/**
		 * Sets a step as active by hiding the previous one and updating the steps' markup.
		 */
		this.set_active = function() {

			var step          = this,
				style         = composite.settings.layout,
				curr_step_pre = composite.get_current_step(),
				$el_out       = curr_step_pre.$el,
				$el_in        = step.$el,
				el_out_height = 0;

			composite.set_current_step( step );

			if ( curr_step_pre.step_id !== step.step_id ) {

				if ( style === 'paged' ) {

					// Prevent clicks while animating.
					composite.$composite_form_blocker.addClass( 'blocked' );

					composite.has_transition_lock = true;

					setTimeout( function() {

						$el_out.addClass( 'faded' );
						$el_in.addClass( 'invisible faded' );

						setTimeout( function() {

							// Measure height.
							if ( typeof $el_out.get( 0 ).getBoundingClientRect().height !== 'undefined' ) {
								el_out_height = $el_out.get( 0 ).getBoundingClientRect().height;
							} else {
								el_out_height = $el_out.outerHeight();
							}

							// Make invisible.
							$el_out.addClass( 'invisible' );

							// Lock height.
							$el_out.css( 'height', el_out_height );

							// Run 'active_step_transition_start' action - @see WC_CP_Actions_Dispatcher class description.
							composite.actions.do_action( 'active_step_transition_start', [ step ] );

							composite.console_log( 'debug:events', 'Starting transition...' );

							// Hide old view with a sliding effect.
							$el_out.slideUp( { duration: 150, always: function() {
								// Release height lock.
								$el_out.css( 'height', 'auto' );
							} } );

							// Show new view with a sliding effect.
							$el_in.slideDown( { duration: 150, always: function() {

								setTimeout( function() {
									// Run 'active_step_transition_end' action - @see WC_CP_Actions_Dispatcher class description.
									composite.actions.do_action( 'active_step_transition_end', [ step ] );
								}, 250 );

								composite.console_log( 'debug:events', 'Transition ended.' );

								setTimeout( function() {
									composite.$steps.removeClass( 'faded invisible' );
								}, 10 );

								composite.has_transition_lock = false;
								composite.$composite_form_blocker.removeClass( 'blocked' );

							} } );

						}, 250 );

					}, 10 );

				} else {

					if ( style === 'progressive' ) {

						// Update blocks.
						step.update_block_state();
					}

					composite.has_transition_lock = true;

					setTimeout( function() {
						// Run 'active_step_transition_start' action - @see WC_CP_Actions_Dispatcher class description.
						composite.actions.do_action( 'active_step_transition_start', [ step ] );
					}, 5 );

					setTimeout( function() {
						// Run 'active_step_transition_end' action - @see WC_CP_Actions_Dispatcher class description.
						composite.actions.do_action( 'active_step_transition_end', [ step ] );

						composite.has_transition_lock = false;

					}, 350 );

				}

			} else {
				step.$el.show();
			}

			// Run 'active_step_changed' action - @see WC_CP_Actions_Dispatcher class description.
			composite.actions.do_action( 'active_step_changed', [ this ] );
		};

		/**
		 * Updates the block state of a progressive step that's brought into view.
		 */
		this.update_block_state = function() {

			var style = composite.settings.layout;

			if ( style !== 'progressive' ) {
				return false;
			}

			$.each( composite.get_steps(), function( index, step ) {

				if ( step.step_index < self.step_index ) {

					step.block_step_inputs();

					// Do not close when the component is set to remain open when blocked.
					if ( ! step.$el.hasClass( 'block-open' ) ) {
						step.toggle_step( 'closed', true );
					}
				}
			} );

			this.unblock_step_inputs();
			this.unblock_step();

			this.block_next_steps();
		};

		/**
		 * Unblocks access to step in progressive mode.
		 */
		this.unblock_step = function() {

			this.toggle_step( 'open', true );

			this.$el.removeClass( 'blocked' );
		};

		/**
		 * Blocks access to all later steps in progressive mode.
		 */
		this.block_next_steps = function() {

			var min_block_index = this.step_index;

			$.each( composite.get_steps(), function( index, step ) {

				if ( index > min_block_index ) {

					if ( step.$el.hasClass( 'disabled' ) ) {
						step.unblock_step_inputs();
					}

					step.block_step();
				}
			} );
		};

		/**
		 * Blocks access to step in progressive mode.
		 */
		this.block_step = function() {

			this.$el.addClass( 'blocked' );

			this.toggle_step( 'closed', false );
		};

		/**
		 * Toggle step in progressive mode.
		 */
		this.toggle_step = function( state, active, complete ) {

			if ( this.has_toggle() ) {

				if ( state === 'open' ) {
					if ( this.$el.hasClass( 'closed' ) ) {
						wc_cp_toggle_element( this.$el, this.$inner_el, complete );
					}

				} else if ( state === 'closed' ) {
					if ( this.$el.hasClass( 'open' ) ) {
						wc_cp_toggle_element( this.$el, this.$inner_el, complete );
					}
				}

				if ( active ) {
					this.$step_title.removeClass( 'inactive' );
				} else {
					this.$step_title.addClass( 'inactive' );
				}
			}
		};

		/**
		 * Unblocks step inputs.
		 */
		this.unblock_step_inputs = function() {

			this.$el.removeClass( 'disabled' );

			var reset_options = this.$el.find( '.clear_component_options' );
			reset_options.html( wc_composite_params.i18n_clear_selection ).removeClass( 'reset_component_options' );
		};

		/**
		 * Blocks step inputs.
		 */
		this.block_step_inputs = function() {

			this.$el.addClass( 'disabled' );

			if ( ! self.has_toggle() || self.$el.hasClass( 'block-open' ) ) {
				var reset_options = this.$el.find( '.clear_component_options' );
				reset_options.html( wc_composite_params.i18n_reset_selection ).addClass( 'reset_component_options' );
			}
		};

		/**
		 * True if access to the step is blocked (progressive mode).
		 */
		this.is_blocked = function() {

			return this.$el.hasClass( 'blocked' );
		};

		/**
		 * True if access to the step inputs is blocked (progressive mode).
		 */
		this.has_blocked_inputs = function() {

			return this.$el.hasClass( 'disabled' );
		};

		/**
		 * Adds a validation message.
		 */
		this.add_validation_message = function( message, scope ) {

			scope = typeof( scope ) === 'undefined' ? 'component' : scope;

			if ( scope === 'composite' ) {
				this._composite_messages.push( message.toString() );
			} else {
				this._component_messages.push( message.toString() );
			}
		};

		/**
		 * Get all validation messages.
		 */
		this.get_validation_messages = function( scope ) {

			var messages;

			scope = typeof( scope ) === 'undefined' ? 'component' : scope;

			if ( scope === 'composite' ) {
				messages = this._composite_messages;
			} else {
				messages = this._component_messages;
			}

			// Pass through 'step_validation_messages' filter - @see WC_CP_Filters_Manager class.
			return composite.filters.apply_filters( 'step_validation_messages', [ messages, scope, this ] );
		};

		/**
		 * Validate component selection and stock status and add validation messages.
		 */
		this.validate = function() {

			if ( self.initializing_scripts ) {
				return false;
			}

			var step     = this,
				valid    = true,
				in_stock = true;

			this._component_messages = [];
			this._composite_messages = [];

			if ( this.is_component() ) {

				var product_id   = this.get_selected_product(),
					variation_id = this.get_selected_variation(),
					product_type = this.get_selected_product_type();

				valid = false;

				// Check if valid selection present.
				if ( product_id === '' && this.is_optional() ) {

					valid = true;

				} else if ( product_id > 0 ) {

					if ( product_type === 'variable' ) {
						if ( variation_id > 0 || this.get_selected_quantity() === 0 ) {
							valid = true;
						}
					} else if ( product_type === 'simple' || product_type === 'none' ) {
						valid = true;
					} else {
						if ( this.$component_data.data( 'component_set' ) === true || this.get_selected_quantity() === 0 ) {
							valid = true;
						}
					}
				}

				// Always valid if invisible.
				if ( ! this.is_visible() ) {
					valid = true;
				} else {
					// Pass through 'step_is_valid' filter - @see WC_CP_Filters_Manager class.
					valid = composite.filters.apply_filters( 'step_is_valid', [ valid, this ] );
				}

				if ( ! valid ) {
					if ( product_id > 0 ) {
						if ( product_type === 'invalid-product' ) {
							this.add_validation_message( wc_composite_params.i18n_item_unavailable_text, 'composite' );
						} else if ( product_type === 'variable' ) {
							if ( ! this.is_selected_variation_valid() ) {
								this.add_validation_message( wc_composite_params.i18n_selected_product_options_invalid, 'composite' );
							} else {
								this.add_validation_message( wc_composite_params.i18n_select_product_options );
								this.add_validation_message( wc_composite_params.i18n_select_product_options_for, 'composite' );
							}
						}
					} else {
						if ( ! this.is_selected_product_valid() ) {
							this.add_validation_message( wc_composite_params.i18n_selected_product_invalid );
							this.add_validation_message( wc_composite_params.i18n_selected_product_invalid, 'composite' );
						} else {
							this.add_validation_message( wc_composite_params.i18n_select_component_option );
							this.add_validation_message( wc_composite_params.i18n_select_component_option_for, 'composite' );
						}
					}
				}

				if ( ! this.is_in_stock() ) {
					in_stock = false;
				}

				// Run 'validate_step' action - @see WC_CP_Actions_Dispatcher class description.
				composite.actions.do_action( 'validate_step', [ step, valid ] );
			}

			this.step_validation_model.update( valid, in_stock );
		};

		/**
		 * Check if any validation messages exist.
		 */
		this.passes_validation = function() {

			return this.step_validation_model.get( 'passes_validation' );
		};

		this.add_actions();
	}

	/**
     * Component class - inherits from WC_CP_Step.
     */
	function WC_CP_Component( composite, $component, index ) {

		WC_CP_Step.call( this, composite, $component, index );

		var self                             = this;

		this.initializing_scripts            = false;

		this.component_index                 = index;
		this.component_id                    = $component.attr( 'data-item_id' );
		this.component_title                 = $component.data( 'nav_title' );

		this._hide_disabled_products         = $component.hasClass( 'hide-incompatible-products' );
		this._hide_disabled_variations       = $component.hasClass( 'hide-incompatible-variations' );
		this._is_static                      = $component.hasClass( 'static' );

		this.$component_summary              = $component.find( '.component_summary' );
		this.$component_summary_content      = $component.find( '.component_summary > .content' );
		this.$component_selections           = $component.find( '.component_selections' );
		this.$component_content              = $component.find( '.component_content' );
		this.$component_options              = $component.find( '.component_options' );
		this.$component_filters              = $component.find( '.component_filters' );
		this.$component_ordering             = $component.find( '.component_ordering select' );
		this.$component_options_inner        = $component.find( '.component_options_inner' );
		this.$component_inner                = $component.find( '.component_inner' );
		this.$component_pagination           = $component.find( '.component_pagination' );
		this.$component_message              = $component.find( '.component_message' );

		this.$component_data                 = this.$component_summary_content.find( '.component_data' );
		this.$component_quantity             = this.$component_summary_content.find( '.component_wrap input.qty' );
		this.$component_options_select       = this.$component_options.find( 'select.component_options_select' );
		this.$component_thumbnail_options    = this.$component_options.find( '.component_option_thumbnails' );
		this.$component_radio_button_options = this.$component_options.find( '.component_option_radio_buttons' );

		/**
		 * True when component options are appended using a 'Load More' button, instead of paginated.
		 */
		this.append_results = function() {

			return 'yes' === composite.$composite_data.data( 'composite_settings' ).pagination_data[ this.step_id ].append_results;
		};

		/**
		 * Results per page.
		 */
		this.get_results_per_page = function() {

			return composite.$composite_data.data( 'composite_settings' ).pagination_data[ this.step_id ].results_per_page;
		};

		/**
		 * Max results.
		 */
		this.get_max_results = function() {

			return composite.$composite_data.data( 'composite_settings' ).pagination_data[ this.step_id ].max_results;
		};

		/**
		 * Pagination range.
		 */
		this.get_pagination_range = function( mid_or_end ) {

			if ( typeof( mid_or_end ) === 'undefined' ) {
				mid_or_end = 'mid';
			}

			var prop = mid_or_end === 'end' ? 'pagination_range_end' : 'pagination_range';

			return composite.$composite_data.data( 'composite_settings' ).pagination_data[ this.step_id ][ prop ];
		};

		/**
		 * Gets the selected option id from the component selection model.
		 */
		this.get_selected_product = function( check_invalid ) {

			if ( typeof( check_invalid ) === 'undefined' ) {
				check_invalid = true;
			}

			if ( check_invalid && ! this.is_selected_product_valid() ) {
				return null;
			}

			return this.component_selection_model.get( 'selected_product' );
		};

		/**
		 * Gets the selected option id from the component selection model.
		 */
		this.get_selected_variation = function( check_invalid ) {

			if ( typeof( check_invalid ) === 'undefined' ) {
				check_invalid = true;
			}

			if ( check_invalid && ! this.is_selected_variation_valid() ) {
				return null;
			}

			return this.component_selection_model.get( 'selected_variation' );
		};

		/**
		 * Gets the selected product/variation quantity from the component selection model.
		 */
		this.get_selected_quantity = function() {

			if ( false === self.is_visible() ) {
				return 0;
			}

			return this.component_selection_model.get( 'selected_quantity' );
		};

		/**
		 * Get the product type of the selected product.
		 */
		this.get_selected_product_type = function() {

			return this.$component_data.data( 'product_type' );
		};

		/**
		 * Gets the (formatted) product title from the component selection model.
		 */
		this.get_selected_product_title = function( formatted, check_invalid ) {

			check_invalid = typeof( check_invalid ) === 'undefined' ? false : check_invalid;
			formatted     = typeof( formatted ) === 'undefined' ? false : formatted;

			if ( check_invalid && ! this.is_selected_product_valid() ) {
				return '';
			}

			var title            = this.find_selected_product_param( 'title' ),
				qty              = this.get_selected_quantity(),
				selected_product = this.get_selected_product( false ),
				formatted_title  = '',
				formatted_meta   = '',
				formatted_qty    = '';

			// Pass through 'component_selection_title' filter - @see WC_CP_Filters_Manager class.
			title = composite.filters.apply_filters( 'component_selection_title', [ title, this ] );

			if ( title && formatted ) {

				if ( '' === selected_product ) {
					formatted_title = '<span class="content_product_title none">' + title + '</span>';
				} else {

					formatted_qty   = qty > 1 ? '<strong>' + wc_composite_params.i18n_qty_string.replace( '%s', qty ) + '</strong>' : '';
					formatted_title = wc_composite_params.i18n_title_string.replace( '%t', title ).replace( '%q', formatted_qty ).replace( '%p', '' );
					formatted_meta  = this.get_selected_product_meta( true );

					if ( formatted_meta ) {
						formatted_title = wc_composite_params.i18n_selected_product_string.replace( '%t', formatted_title ).replace( '%m', formatted_meta );
					}

					formatted_title = '<span class="content_product_title">' + formatted_title + '</span>';
				}

				// Pass through 'component_selection_formatted_title' filter - @see WC_CP_Filters_Manager class.
				formatted_title = composite.filters.apply_filters( 'component_selection_formatted_title', [ formatted_title, title, qty, formatted_meta, this ] );
			}

			return formatted ? formatted_title : title;
		};

		/**
		 * Gets (formatted) meta for the selected product.
		 */
		this.get_selected_product_meta = function( formatted ) {

			formatted = typeof( formatted ) === 'undefined' ? false : formatted;

			var formatted_meta = '',
				meta           = this.get_selected_variation( false ) > 0 ? this.component_selection_model.selected_variation_data : [];

			// Pass through 'component_selection_meta' filter - @see WC_CP_Filters_Manager class.
			meta = composite.filters.apply_filters( 'component_selection_meta', [ meta, this ] );

			if ( meta.length > 0 && formatted ) {

				formatted_meta = '<ul class="content_product_meta">';

				$.each( meta, function( index, data ) {
					formatted_meta = formatted_meta + '<li class="meta_element"><span class="meta_key">' + data.meta_key + ':</span> <span class="meta_value">' + data.meta_value + '</span>';
					if ( index !== meta.length - 1 ) {
						formatted_meta = formatted_meta + '<span class="meta_element_sep">, </span>';
					}
					formatted_meta = formatted_meta + '</li>';
				} );

				formatted_meta = formatted_meta + '</ul>';

				// Pass through 'component_selection_formatted_meta' filter - @see WC_CP_Filters_Manager class.
				formatted_meta = composite.filters.apply_filters( 'component_selection_formatted_meta', [ formatted_meta, meta, this ] );
			}

			return formatted ? formatted_meta : meta;
		};

		/**
		 * Gets image src for the selected product/variation.
		 */
		this.get_selected_product_image_data = function( check_invalid ) {

			check_invalid = typeof( check_invalid ) === 'undefined' ? true : check_invalid;

			if ( check_invalid && ! this.is_selected_product_valid() ) {
				return false;
			}

			return this.get_selected_variation( check_invalid ) > 0 && this.component_selection_model.selected_variation_image_data ? this.component_selection_model.selected_variation_image_data : this.component_selection_model.selected_product_image_data;
		};

		/**
		 * True if the currently selected product is incompatible based on the active scenarios.
		 */
		this.is_selected_product_valid = function( active_options ) {

			if ( typeof( active_options ) === 'undefined' ) {
				active_options = this.component_options_model.get( 'active_options' );
			}

			return this.component_selection_model.get( 'selected_product' ) === '' || _.contains( active_options, this.component_selection_model.get( 'selected_product' ) );
		};

		/**
		 * True if the currently selected variation is incompatible based on the active scenarios.
		 */
		this.is_selected_variation_valid = function( active_options ) {

			if ( typeof( active_options ) === 'undefined' ) {
				active_options = this.component_options_model.get( 'active_options' );
			}

			return this.component_selection_model.get( 'selected_variation' ) === '' || _.contains( active_options, this.component_selection_model.get( 'selected_variation' ) );
		};

		/**
		 * When true, hide incompatible/disabled products.
		 */
		this.hide_disabled_products = function() {

			return composite.filters.apply_filters( 'component_hide_disabled_products', [ this._hide_disabled_products, this ] );
		};

		/**
		 * When true, hide incompatible/disabled variations.
		 */
		this.hide_disabled_variations = function() {

			return composite.filters.apply_filters( 'component_hide_disabled_variations', [ this._hide_disabled_variations, this ] );
		};

		/**
		 * Find a param for the selected product in the DOM.
		 */
		this.find_selected_product_param = function( param ) {

			if ( param === 'id' ) {
				composite.console_log( 'error', '\nMethod \'WC_CP_Component::find_selected_product_param\' was called with a deprecated argument value (\'id\'). Use \'WC_CP_Component::get_selected_product\' instead.' );
				return this.get_selected_product( false );
			} else if ( param === 'variation_id' ) {
				if ( this.get_selected_product_type() === 'variable' ) {
					return this.$component_summary_content.find( '.single_variation_wrap .variations_button input.variation_id' ).val();
				}
				return '';
			} else if ( param === 'title' ) {

				var selected_product = this.get_selected_product( false ),
					title            = '';

				if ( selected_product === '' ) {
					title = wc_composite_params.i18n_no_selection;
				} else if ( selected_product !== '' && this.component_options_model.available_options_data.length > 0 ) {
					$.each( this.component_options_model.available_options_data, function( index, option_data ) {
						if ( option_data.option_id === selected_product ) {
							title = option_data.option_title;
							return false;
						}
					} );
				}

				return title;

			} else if ( param === 'variation_data' ) {
				var $variations = this.$component_summary_content.find( '.variations' );
				if ( $variations.length > 0 ) {
					return wc_cp_get_variation_data( $variations, false );
				}
				return [];
			} else if ( param === 'product_image_data' ) {

				var custom_data = this.$component_data.data( 'custom' ),
					pi_data     = false;

				if ( custom_data && typeof ( custom_data[ 'image_data' ] ) !== 'undefined' ) {
					pi_data = {
						image_src:    custom_data.image_data.image_src,
						image_srcset: custom_data.image_data.image_srcset,
						image_sizes:  custom_data.image_data.image_sizes,
						image_title:  custom_data.image_data.image_title
					};
				}

				return pi_data;

			} else if ( param === 'variation_image_data' ) {

				var variation_id = this.find_selected_product_param( 'variation_id' ),
					variations   = this.$component_data.data( 'product_variations' ),
					vi_data      = false;

				if ( variation_id > 0 && variations ) {
					$.each( variations, function( index, variation ) {
						if ( parseInt( variation.variation_id ) === parseInt( variation_id ) ) {
							if ( 'yes' === wc_composite_params.is_wc_version_gte_2_7 && variation.image ) {
								vi_data = {
									image_src:    variation.image.src,
									image_srcset: variation.image.srcset,
									image_sizes:  variation.image.sizes,
									image_title:  variation.image.title
								};
							} else if ( variation.image_src ) {
								vi_data = {
									image_src:    variation.image_src,
									image_srcset: variation.image_srcset,
									image_sizes:  variation.image_sizes,
									image_title:  variation.image_title
								};
							}
							return false;
						}
					} );
				}

				return vi_data;

			} else if ( param === 'quantity' ) {
				var qty = this.$component_quantity.length > 0 ? this.$component_quantity.val() : 0;
				return parseInt( qty );
			} else if ( param === 'addons_price' ) {
				var $addons_totals = this.$component_summary_content.find( '.component_wrap #product-addons-total' ),
					addons_price   = $addons_totals.length > 0 ? $addons_totals.data( 'addons-raw-price' ) : 0;
				return Number( addons_price );
			} else if ( param === 'nyp_price' ) {
				var $nyp      = this.$component_summary_content.find( '.nyp' ),
					nyp_price = $nyp.length > 0 ? $nyp.data( 'price' ) : 0;
				return Number( nyp_price );
			}
		};

		/**
		 * Find a pagination param in the DOM.
		 */
		this.find_pagination_param = function( param ) {

			var data  = self.$component_pagination.data( 'pagination_data' ),
				value = 1;

			if ( data ) {
				if ( param === 'page' ) {
					value = data.page;
				} else if ( param === 'pages' ) {
					value = data.pages;
				}
			}

			return value;
		};

		/**
		 * Find active order by value in the DOM.
		 */
		this.find_order_by = function() {

			var orderby = '';

			if ( this.$component_ordering.length > 0 ) {
				orderby = this.$component_ordering.val();
			}

			return orderby;
		};

		/**
		 * Find active component filters in the DOM.
		 */
		this.find_active_filters = function() {

			var component_filters = this.$component_filters;
			var filters           = {};

			if ( component_filters.length == 0 ) {
				return filters;
			}

			component_filters.find( '.component_filter_option.selected' ).each( function() {

				var filter_type = $( this ).closest( '.component_filter' ).data( 'filter_type' );
				var filter_id   = $( this ).closest( '.component_filter' ).data( 'filter_id' );
				var option_id   = $( this ).data( 'option_id' );

				if ( filter_type in filters ) {

					if ( filter_id in filters[ filter_type ] ) {

						filters[ filter_type ][ filter_id ].push( option_id );

					} else {

						filters[ filter_type ][ filter_id ] = [];
						filters[ filter_type ][ filter_id ].push( option_id );
					}

				} else {

					filters[ filter_type ]              = {};
					filters[ filter_type ][ filter_id ] = [];
					filters[ filter_type ][ filter_id ].push( option_id );
				}

			} );

			return filters;
		};

		/**
		 * Find component options data in the DOM.
		 */
		this.find_options_data = function() {
			return self.$component_options.data( 'options_data' );
		};

		/**
		 * False if the component has an out-of-stock availability class.
		 */
		this.is_in_stock = function() {

			var is_in_stock = true;

			if ( this.$component_summary_content.find( '.component_wrap .out-of-stock' ).not( '.inactive' ).length > 0 && this.get_selected_quantity() > 0 ) {
				if ( this.get_selected_product_type() !== 'variable' || this.get_selected_variation( false ) > 0 ) {
					is_in_stock = false;
				}
			}

			return is_in_stock;
		};

		this.is_nyp = function() {

			return this.component_selection_model.get( 'selected_nyp' ) > 0;
		};

		/**
		 * Gets the options style for this component.
		 */
		this.has_options_style = function( style ) {

			return this.$el.hasClass( 'options-style-' + style );
		};

		/**
		 * Get the bundle script object.
		 *
		 */
		this.get_bundle_script = function() {

			var bundle = false;

			if ( typeof( wc_pb_bundle_scripts[ self.component_id ] ) !== 'undefined' ) {
				bundle = wc_pb_bundle_scripts[ self.component_id ];
			}

			return bundle;
		};

		/**
		 * Initialize component scripts dependent on product type - called when selecting a new Component Option.
		 * When called with init = false, no type-dependent scripts will be initialized.
		 */
		this.init_scripts = function( init ) {

			if ( typeof( init ) === 'undefined' ) {
				init = true;
			}

			this.$component_data     = this.$component_summary_content.find( '.component_data' );
			this.$component_quantity = this.$component_summary_content.find( '.component_wrap input.qty' );

			if ( init ) {

				self.initializing_scripts = true;

				self.init_qty_input();

				var product_type    = this.get_selected_product_type(),
					summary_content = this.$component_summary_content;

				if ( product_type === 'variable' ) {

					if ( ! summary_content.hasClass( 'cart' ) ) {
						summary_content.addClass( 'cart' );
					}

					if ( ! summary_content.hasClass( 'variations_form' ) ) {
						summary_content.addClass( 'variations_form' );
					}

					// Put filtered variations in place.
					summary_content.data( 'product_variations', this.$component_data.data( 'product_variations' ) );

					// Copy variation ids into the 'active_options' attribute of the options model to ensure that get_selected_variation doesn't return null until the attribute is refreshed.
					$.each( this.$component_data.data( 'product_variations' ), function( index, variation ) {
						self.component_options_model.attributes.active_options.push( variation.variation_id.toString() );
					} );

					// Initialize variations script.
					summary_content.wc_variation_form();

					// Fire change in order to save 'variation_id' input.
					summary_content.find( '.variations select' ).change();

					// Complete all pending animations.
					summary_content.find( 'div' ).stop( true, true );

				} else if ( product_type === 'bundle' ) {

					if ( ! summary_content.hasClass( 'bundle_form' ) ) {
						summary_content.addClass( 'bundle_form' );
					}

					// Initialize bundles script now.
					summary_content.find( '.bundle_data' ).wc_pb_bundle_form();

					// Complete all pending animations.
					summary_content.find( 'div' ).stop( true, true );

				} else {

					if ( ! summary_content.hasClass( 'cart' ) ) {
						summary_content.addClass( 'cart' );
					}
				}

				self.initializing_scripts = false;
			}

			// Run 'component_scripts_initialized' action - @see WC_CP_Actions_Dispatcher class description.
			composite.actions.do_action( 'component_scripts_initialized', [ self ] );
		};

		/**
		 * Resets all listeners before loading new product content and re-initializing any external scripts.
		 */
		this.reset_scripts = function() {

			self.$component_summary_content.removeClass( 'variations_form bundle_form cart' );
			self.$component_summary_content.off().find( '*' ).off();

			// Run 'component_scripts_reset' action - @see WC_CP_Actions_Dispatcher class description.
			composite.actions.do_action( 'component_scripts_reset', [ self ] );
		};

		/**
		 * Get the step that corresponds to this component.
		 */
		this.get_step = function() {

			return composite.get_step( this.component_id );
		};

		/**
		 * True if a Component is static (single option).
		 */
		this.is_static = function() {

			return this._is_static;
		};

		/**
		 * True if a Component is optional taking the active scenarios into account.
		 */
		this.is_optional = function() {

			var is_optional = _.contains( this.component_options_model.get( 'active_options' ), '' );

			// Pass through 'component_is_optional' filter - @see WC_CP_Filters_Manager class.
			return composite.filters.apply_filters( 'component_is_optional', [ is_optional, this ] );
		};

		/**
		 * True if a Component is set as optional.
		 */
		this.maybe_is_optional = function() {

			var scenario_data      = composite.scenarios.get_scenario_data().scenario_data,
				item_scenario_data = scenario_data[ self.component_id ];

			return ( 0 in item_scenario_data );
		};

		this.show_placeholder_option = function() {

			return 'yes' === composite.$composite_data.data( 'composite_settings' ).show_placeholder_option[ this.step_id ];
		};

		/**
		 * Selected option price visibility.
		 */
		this.is_selected_product_price_visible = function() {

			return 'yes' === composite.$composite_data.data( 'composite_settings' ).selected_product_price_visibility_data[ this.step_id ];
		};

		/**
		 * Subtotal price visibility.
		 */
		this.is_subtotal_visible = function() {

			return 'yes' === composite.$composite_data.data( 'composite_settings' ).subtotal_visibility_data[ this.step_id ];
		};

		/**
		 * Initialize quantity input.
		 */
		this.init_qty_input = function() {

			// Quantity buttons.
			if ( wc_composite_params.is_wc_version_gte_2_3 === 'no' || wc_composite_params.show_quantity_buttons === 'yes' ) {
				this.$component_summary_content.find( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).addClass( 'buttons_added' ).append( '<input type="button" value="+" class="plus" />' ).prepend( '<input type="button" value="-" class="minus" />' );
			}

			this.$component_quantity.trigger( 'change' );
		};

		/**
		 * Get component placeholder image.
		 */
		this.get_placeholder_image_data = function() {

			return typeof ( composite.$composite_data.data( 'composite_settings' ).image_data[ this.step_id ] ) === 'undefined' ? false : composite.$composite_data.data( 'composite_settings' ).image_data[ this.step_id ];
		};

		/**
		 * @deprecated
		 */
		this.get_selected_product_id = function() {
			return this.find_selected_product_param( 'id' );
		};
	}

	this.create_component = function( composite, $component, index ) {

		WC_CP_Component.prototype             = this.create( WC_CP_Step.prototype );
		WC_CP_Component.prototype.constructor = WC_CP_Component;

		return new WC_CP_Component( composite, $component, index );
	};

	this.create_step = function( composite, $step, index ) {

		if ( $step.hasClass( 'component' ) ) {
			return this.create_component( composite, $step, index );
		}

		return new WC_CP_Step( composite, $step, index );
	};

	this.create = function( o ) {
		function F() {}
		F.prototype = o;
		return new F();
	};

};
