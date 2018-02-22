/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/**
 * Actions dispatcher that triggers actions in response to specific events.
 * When multiple models (or both models & views) need to respond to a specific event, model handlers must be run before view handlers (and in the right sequence) to ensure that views have access to correctly updated model data.
 *
 * Without a dispatcher:
 *
 *  - declaring those handlers in the right sequence can make our code hard to read, and
 *  - it is very hard for 3rd party code to add handlers at a specific point in the callback execution queue.
 *
 * The dispatcher:
 *
 *  - translates key events into actions and provides an API for declaring callbacks for specific actions in the desired priority, and
 *  - makes code a lot easier to read since internal functionality is abstracted (models/views listen to key, internal events directly).
 *
 *
 * A complete reference of all application actions & callbacks is provided in the "Actions Reference" below.
 *
 */
wc_cp_classes.WC_CP_Actions_Dispatcher = function( composite ) {

	/*
	 *--------------------------*
	 *                          *
	 *   Actions Reference      *
	 *                          *
	 *--------------------------*
	 *
	 *--------------------------*
	 *   1. Steps/Components    *
	 *--------------------------*
	 *
	 *
	 * Action 'show_step':
	 *
	 * Triggered when navigating to a step.
	 *
	 * @param  WC_CP_Step  step
	 *
	 * @hooked Action 'show_step_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'show_step_{step.step_id}':
	 *
	 * Triggered when navigating to the step with id === step_id.
	 *
	 * @hooked WC_CP_Step::autoscroll_single - 10
	 *
	 *
	 *
	 * Action 'active_step_changed':
	 *
	 * Triggered when the active step changes.
	 *
	 * @param  WC_CP_Step  step
	 *
	 * @hooked Action 'active_step_changed_{step.step_id}'             - 0
	 * @hooked Step_Title_View::active_step_changed_handler            - 100
	 * @hooked Composite_Pagination_View::active_step_changed_handler  - 100
	 * @hooked Composite_Summary_View::active_step_changed_handler     - 100
	 * @hooked Composite_Widget_View::active_step_changed_handler      - 100
	 * @hooked Step_Validation_View::active_step_changed_handler       - 100
	 * @hooked WC_CP_Step::autoscroll_paged                            - 120
	 *
	 *
	 *
	 * Action 'active_step_changed_{step.step_id}':
	 *
	 * Triggered when the step with id === step_id becomes active.
	 *
	 * @hooked Component_Options_Model::active_step_changed_handler - 10
	 *
	 *
	 *
	 * Action 'active_step_transition_start':
	 *
	 * Triggered when the transition animation to an activated step starts.
	 *
	 * @param  WC_CP_Step  step
	 *
	 * @hooked Action 'active_step_transition_start_{step.step_id}'            - 0
	 * @hooked Component_Selection_View::active_step_transition_start_handler  - 100
	 * @hooked Composite_Navigation_View::active_step_transition_start_handler - 110
	 *
	 *
	 *
	 * Action 'active_step_transition_start_{step.step_id}':
	 *
	 * Triggered when the transition animation to the activated step with id === step_id starts.
	 *
	 * @hooked WC_CP_Step::autoload_compat_options - 10
	 *
	 *
	 *
	 * Action 'active_step_transition_end':
	 *
	 * Triggered when the transition animation to an activated step ends.
	 *
	 * @param  WC_CP_Step  step
	 *
	 * @hooked Action 'active_step_transition_end_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'active_step_transition_end_{step.step_id}':
	 *
	 * Triggered when the transition animation to the activated step with id === step_id ends.
	 *
	 * @hooked WC_CP_Step::autoscroll_progressive                       - 10
	 * @hooked WC_CP_Step::autoscroll_paged_relocated                   - 10
	 * @hooked Step_Validation_View::active_step_transition_end_handler - 100
	 *
	 *
	 *
	 * Action 'component_selection_changed':
	 *
	 * Triggered when the product/variation selection of a Component changes.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked WC_CP_Scenarios_Manager::selection_changed_handler   - 10
	 * @hooked Step_Validation_Model::selection_changed_handler     - 20
	 * @hooked Composite_Data_Model::selection_changed_handler      - 30
	 * @hooked Component_Selection_View::refresh_selection_title    - 100
	 * @hooked Composite_Summary_View::selection_changed_handler    - 100
	 * @hooked Composite_Navigation_View::selection_changed_handler - 110
	 *
	 *
	 *
	 * Action 'component_selection_content_changed':
	 *
	 * Triggered when options/content associated with a selected product change, requiring re-validation, re-calculation of totals and re-freshes of all associated views.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Step_Validation_Model::selection_changed_handler        - 20
	 * @hooked Composite_Data_Model::selection_content_changed_handler - 30
	 * @hooked Composite_Summary_View::selection_changed_handler       - 100
	 * @hooked Composite_Navigation_View::selection_changed_handler    - 100
	 *
	 *
	 *
	 * Action 'component_quantity_changed':
	 *
	 * Triggered when the quantity of a selected product/variation changes.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Step_Validation_Model::quantity_changed_handler    - 10
	 * @hooked Composite_Data_Model::quantity_changed_handler     - 20
	 * @hooked Composite_Summary_View::quantity_changed_handler   - 100
	 * @hooked Component_Selection_View::quantity_changed_handler - 100
	 *
	 *
	 *
	 * Action 'component_availability_changed':
	 *
	 * Triggered when the availability of a selected product/variation changes.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Data_Model::availability_changed_handler - 10
	 *
	 *
	 *
	 * Action 'component_addons_changed':
	 *
	 * Triggered when the Product Add-ons associated with a selected product/variation change.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Data_Model::addons_changed_handler   - 10
	 *
	 *
	 *
	 * Action 'component_nyp_changed':
	 *
	 * Triggered when the price of a selected Name-Your-Price product/variation changes.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Data_Model::nyp_changed_handler   - 10
	 *
	 *
	 *
	 * Action 'component_validation_message_changed':
	 *
	 * Triggered when the validation notices associated with a Component change.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Data_Model::validation_message_changed_handler - 10
	 *
	 *
	 *
	 * Action 'options_state_changed':
	 *
	 * Triggered when the in-view active/enabled Component Options of a Component change.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Action 'options_state_changed_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'options_state_changed_{step.step_id}':
	 *
	 * Triggered when the in-view active/enabled Component Options of the Component with id === step_id change.
	 *
	 * @hooked Component_Options_View::render - 10
	 *
	 *
	 *
	 * Action 'active_options_changed':
	 *
	 * Triggered when the active/enabled Component Options of a Component change.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Action 'active_options_changed_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'active_options_changed_{step.step_id}':
	 *
	 * Triggered when the active/enabled Component Options of the Component with id === step_id change.
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'available_options_changed':
	 *
	 * Triggered when the Component Options available in a Component change.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked WC_CP_Scenarios_Manager::available_options_changed_handler - 10
	 * @hooked Action 'available_options_changed_{step.step_id}'          - 0
	 *
	 *
	 *
	 * Action 'available_options_changed_{step.step_id}':
	 *
	 * Triggered when the Component Options available in the Component with id === step_id change.
	 *
	 * @hooked Component_Options_Model::available_options_changed_handler - 10
	 *
	 *
	 *
	 * Action 'options_state_render':
	 *
	 * Triggered before the active Component Options are rendered by the Component_Options_View Backbone view.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Selection_View::options_state_render_handler - 10
	 *
	 *
	 *
	 * Action 'options_state_rendered':
	 *
	 * Triggered after the active Component Options have been rendered by the Component_Options_View Backbone view.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Composite_Selection_View::options_state_rendered_handler  - 10
	 * @hooked Composite_Navigation_View::options_state_rendered_handler - 20
	 *
	 *
	 *
	 *
	 * Action 'component_options_loaded':
	 *
	 * Triggered after a new set of Component Options has been loaded and rendered by the Component_Options_View Backbone view.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Action 'component_options_loaded_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'component_options_loaded_{step.step_id}':
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'component_scripts_initialized':
	 *
	 * Triggered when the details associated with a new product selection are rendered by the Component_Selection_View, once the associated product type scripts have been initialized.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Action 'component_scripts_initialized_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'component_scripts_initialized_{step.step_id}':
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'component_scripts_reset':
	 *
	 * Triggered before unloading the details associated with a new product selection, once all attached script listeners have been unloaded.
	 *
	 * @param  WC_CP_Component  component
	 *
	 * @hooked Action 'component_scripts_reset_{step.step_id}' - 0
	 *
	 *
	 *
	 * Action 'component_scripts_reset_{step.step_id}':
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'component_totals_changed':
	 *
	 * Triggered when the price of a Component changes.
	 *
	 * @param WC_CP_Component  component
	 *
	 * @hooked Composite_Data_Model::calculate_totals                   - 10
	 * @hooked Composite_Summary_View::component_totals_changed_handler - 100
	 *
	 *
	 *
	 * Action 'validate_step':
	 *
	 * Triggered during step validation, before the Step_Validation_Model has been updated with the validation results.
	 *
	 * @param  WC_CP_Step  step
	 * @param  boolean     is_valid
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'component_summary_content_updated':
	 *
	 * Triggered when the content associated with a specific Component in a Composite_Summary_View view changes.
	 *
	 * @param  WC_CP_Component         component
	 * @param  Composite_Summary_View  view
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Action 'step_access_changed':
	 *
	 * Triggered when access to a specific Step is toggled.
	 *
	 * @param  WC_CP_Step   step
	 *
	 * @hooked Composite_Pagination_View::step_access_changed_handler - 100
	 * @hooked Composite_Summary_View::step_access_changed_handler    - 100
	 * @hooked Step_Title_View::step_access_changed_handler           - 100
	 *
	 *
	 *
	 * Action 'step_visibility_changed':
	 *
	 * Triggered when the visibility of a specific Step is toggled.
	 *
	 * @param  WC_CP_Step   step
	 *
	 * @hooked WC_CP_Step::step_visibility_changed_handler                - 10
	 * @hooked Composite_Pagination_View::step_visibility_changed_handler - 100
	 * @hooked Composite_Summary_View::step_visibility_changed_handler    - 100
	 * @hooked Step_Title_View::step_visibility_changed_handler           - 100
	 *
	 *
	 *
	 *--------------------------*
	 *   2. Scenarios           *
	 *--------------------------*
	 *
	 *
	 * Action 'active_scenarios_changed':
	 *
	 * Triggered when the active scenarios change in response to a product/variation selection change in a Component.
	 *
	 * @param  WC_CP_Component  triggered_by
	 *
	 * @hooked Component_Options_Model::refresh_options_state - 10
	 *
	 *
	 *
	 * Action 'active_scenarios_updated':
	 *
	 * Triggered when the active scenarios are updated (but not necessarily changed) in response to a product/variation selection change in a Component.
	 *
	 * @param  WC_CP_Component  triggered_by
	 *
	 * @hooked Component_Options_Model::refresh_options_state - 10
	 *
	 *
	 *
	 *--------------------------*
	 *   3. Composite           *
	 *--------------------------*
	 *
	 *
	 * Action 'initialize_composite':
	 *
	 * Action that handles app initialization by prioritizing the execution of the required functions.
	 *
	 * @hooked @see WC_CP_Composite::init
	 *
	 *
	 *
	 * Action 'composite_initialized':
	 *
	 * Action that handles app post-initialization by prioritizing the execution of the required functions.
	 *
	 * @hooked @see WC_CP_Composite::init
	 *
	 *
	 *
	 * Action 'composite_totals_changed':
	 *
	 * Triggered when the composite price/totals change.
	 *
	 * @hooked Composite_Price_View::render               - 100
	 * @hooked Composite_Price_View::update_addons_totals - 100
	 *
	 *
	 *
	 * Action 'composite_validation_status_changed':
	 *
	 * Triggered when the validation status of the Composite changes.
	 *
	 * @hooked Composite_Add_To_Cart_Button_View::render - 100
	 *
	 *
	 *
	 * Action 'composite_validation_message_changed':
	 *
	 * Triggered when the validation notice of the Composite changes.
	 *
	 * @hooked Composite_Validation_View::render - 100
	 *
	 *
	 *
	 * Action 'composite_availability_status_changed':
	 *
	 * Triggered when the availability status of the Composite changes.
	 *
	 * @hooked Composite_Add_To_Cart_Button_View::render - 100
	 *
	 *
	 *
	 * Action 'composite_availability_message_changed':
	 *
	 * Triggered when the availability html message of the Composite changes.
	 *
	 * @hooked Composite_Availability_View::render - 100
	 *
	 */

	var dispatcher = this,
		actions    = {},
		functions  = {

			add_action: function( hook, callback, priority, context ) {

				var hookObject = {
					callback : callback,
					priority : priority,
					context : context
				};

				var hooks = actions[ hook ];
				if ( hooks ) {
					hooks.push( hookObject );
					hooks = this.sort_actions( hooks );
				} else {
					hooks = [ hookObject ];
				}

				actions[ hook ] = hooks;
			},

			remove_action: function( hook, callback, context ) {

				var handlers, handler, i;

				if ( ! actions[ hook ] ) {
					return;
				}
				if ( ! callback ) {
					actions[ hook ] = [];
				} else {
					handlers = actions[ hook ];
					if ( ! context ) {
						for ( i = handlers.length; i--; ) {
							if ( handlers[ i ].callback === callback ) {
								handlers.splice( i, 1 );
							}
						}
					} else {
						for ( i = handlers.length; i--; ) {
							handler = handlers[ i ];
							if ( handler.callback === callback && handler.context === context ) {
								handlers.splice( i, 1 );
							}
						}
					}
				}
			},

			sort_actions: function( hooks ) {

				var tmpHook, j, prevHook;
				for ( var i = 1, len = hooks.length; i < len; i++ ) {
					tmpHook = hooks[ i ];
					j = i;
					while( ( prevHook = hooks[ j - 1 ] ) &&  prevHook.priority > tmpHook.priority ) {
						hooks[ j ] = hooks[ j - 1 ];
						--j;
					}
					hooks[ j ] = tmpHook;
				}

				return hooks;
			},

			do_action: function( hook, args ) {

				var handlers = actions[ hook ], i, len;

				if ( ! handlers ) {
					return false;
				}

				len = handlers.length;

				for ( i = 0; i < len; i++ ) {
					handlers[ i ].callback.apply( handlers[ i ].context, args );
				}

				return true;
			}

		};

	this.init = function() {

		composite.console_log( 'debug:events', '\nInitializing Actions Dispatcher...' );

		/*
		 *--------------------------*
		 *   1. Components          *
		 *--------------------------*
		 */

		/*
		 * Dispatch actions for key events triggered by step objects and their models.
		 */
		$.each( composite.get_steps(), function( index, step ) {

			if ( step.is_component() ) {

				/*
				 * Dispatch action when a selection change event is triggered.
				 */
				step.component_selection_model.on( 'change:selected_product change:selected_variation', function() {

					if ( ! step.initializing_scripts ) {
						// Run 'component_selection_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_selection_changed', [ step ] );
					}
				} );

				/*
				 * Dispatch action when a quantity change event is triggered.
				 */
				step.component_selection_model.on( 'change:selected_quantity', function() {
					// Run 'component_quantity_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'component_quantity_changed', [ step ] );
				} );

				/*
				 * Dispatch action when a selected addons change event is triggered.
				 */
				step.component_selection_model.on( 'change:selected_addons', function() {

					if ( ! step.initializing_scripts ) {
						// Run 'component_addons_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_addons_changed', [ step ] );
					}
				} );

				/*
				 * Dispatch action when a nyp change event is triggered.
				 */
				step.component_selection_model.on( 'change:selected_nyp', function() {

					if ( ! step.initializing_scripts ) {
						// Run 'component_nyp_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_nyp_changed', [ step ] );
					}
				} );

				/*
				 * Dispatch action when the options state changes.
				 */
				step.component_options_model.on( 'change:options_state', function() {
					// Run 'options_state_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'options_state_changed', [ step ] );
				} );

				/*
				 * Dispatch action when the active options change.
				 */
				step.component_options_model.on( 'change:active_options', function() {
					// Run 'active_options_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'active_options_changed', [ step ] );
				} );

				/*
				 * Dispatch action when the available options change.
				 */
				step.component_options_model.on( 'change:available_options', function() {
					// Run 'available_options_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'available_options_changed', [ step ] );
				} );

				/*
				 * Dispatch action when the component totals change.
				 */
				composite.data_model.on( 'change:component_' + step.step_id + '_totals', function() {
					// Run 'component_totals_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'component_totals_changed', [ step ] );
				} );

				/**
				 * Event triggered by custom product types to indicate that the state of the component selection has changed.
				 */
				step.$el.on ( 'woocommerce-composited-product-update', function() {

					if ( ! step.initializing_scripts ) {
						// Run 'component_selection_changed' action - @see WC_CP_Actions_Dispatcher class description.
						dispatcher.do_action( 'component_selection_changed', [ step ] );
					}
				} );
			}

			/*
			 * Dispatch action when the access state of a step changes.
			 */
			step.step_access_model.on( 'change:is_locked', function() {
				// Run 'step_access_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'step_access_changed', [ step ] );
			} );

			/*
			 * Dispatch action when the visibility of a step changes.
			 */
			step.step_visibility_model.on( 'change:is_visible', function() {
				// Run 'step_visibility_changed' action - @see WC_CP_Actions_Dispatcher class description.
				dispatcher.do_action( 'step_visibility_changed', [ step ] );
			} );

			/*
			 * Dispatch action when the validation state of a step changes.
			 */
			step.step_validation_model.on( 'change:composite_messages', function() {

				if ( ! step.initializing_scripts ) {
					// Run 'component_validation_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'component_validation_message_changed', [ step ] );
				}
			} );

			/*
			 * Dispatch action when the availability state of a step changes.
			 */
			step.step_validation_model.on( 'change:is_in_stock', function() {

				if ( ! step.initializing_scripts ) {
					// Run 'component_availability_changed' action - @see WC_CP_Actions_Dispatcher class description.
					dispatcher.do_action( 'component_availability_changed', [ step ] );
				}
			} );

		} );

		/*
		 * Dispatch step action associated with the 'show_step' action.
		 */
		dispatcher.add_action( 'show_step', function( step ) {
			// Run 'show_step_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'show_step_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'active_step_changed' action.
		 */
		dispatcher.add_action( 'active_step_changed', function( step ) {
			// Run 'active_step_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_step_changed_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'active_step_transition_start' action.
		 */
		dispatcher.add_action( 'active_step_transition_start', function( step ) {
			// Run 'active_step_transition_start_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_step_transition_start_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'active_step_transition_end' action.
		 */
		dispatcher.add_action( 'active_step_transition_end', function( step ) {
			// Run 'active_step_transition_end_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_step_transition_end_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'options_state_changed' action.
		 */
		dispatcher.add_action( 'options_state_changed', function( step ) {
			// Run 'options_state_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'options_state_changed_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'active_options_changed' action.
		 */
		dispatcher.add_action( 'active_options_changed', function( step ) {
			// Run 'active_options_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_options_changed_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'available_options_changed' action.
		 */
		dispatcher.add_action( 'available_options_changed', function( step ) {
			// Run 'available_options_changed_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'available_options_changed_' + step.step_id );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'component_options_loaded' action.
		 */
		dispatcher.add_action( 'component_options_loaded', function( step ) {
			// Run 'component_options_loaded_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'component_options_loaded_' + step.step_id );
			// Trigger event for back-compat.
			step.$el.trigger( 'wc-composite-component-options-loaded', [ step, composite ] );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'component_scripts_initialized' action.
		 */
		dispatcher.add_action( 'component_scripts_initialized', function( step ) {
			// Run 'component_scripts_initialized_{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'component_scripts_initialized_' + step.step_id );
			// Trigger event for back-compat.
			step.$el.trigger( 'wc-composite-component-loaded', [ step, composite ] );
		}, 0, this );

		/*
		 * Dispatch step action associated with the 'component_scripts_reset' action.
		 */
		dispatcher.add_action( 'component_scripts_reset', function( step ) {
			// Run 'component_scripts_reset{step.step_id}' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'component_scripts_reset_' + step.step_id );
			// Trigger event for back-compat.
			step.$el.trigger( 'wc-composite-component-unloaded', [ step, composite ] );
		}, 0, this );


		/*
		 *--------------------------*
		 *   2. Scenarios           *
		 *--------------------------*
		 */

		/*
		 * Dispatch action when the active scenarios change.
		 */
		composite.scenarios.on( 'active_scenarios_changed', function( triggered_by ) {
			// Run 'active_scenarios_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_scenarios_changed', [ triggered_by ] );
		} );

		/*
		 * Dispatch action when the active scenarios are updated.
		 */
		composite.scenarios.on( 'active_scenarios_updated', function( triggered_by ) {
			// Run 'active_scenarios_updated' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'active_scenarios_updated', [ triggered_by ] );
		} );

		/*
		 *--------------------------*
		 *   3. Composite           *
		 *--------------------------*
		 */

		/*
		 * Dispatch action when the composite totals change.
		 */
		composite.data_model.on( 'change:totals', function() {
			// Run 'composite_totals_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'composite_totals_changed' );
		} );

		/*
		 * Dispatch action when the composite validation status changes.
		 */
		composite.data_model.on( 'change:passes_validation', function() {
			// Run 'composite_validation_status_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'composite_validation_status_changed' );
		} );

		/*
		 * Dispatch action when the composite validation message changes.
		 */
		composite.data_model.on( 'change:validation_messages', function() {
			// Run 'composite_validation_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'composite_validation_message_changed' );
		} );

		/*
		 * Dispatch action when the composite availability status changes.
		 */
		composite.data_model.on( 'change:is_in_stock', function() {
			// Run 'composite_availability_status_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'composite_availability_status_changed' );
		} );

		/*
		 * Dispatch action when the composite availability message changes.
		 */
		composite.data_model.on( 'change:stock_statuses', function() {
			// Run 'composite_availability_message_changed' action - @see WC_CP_Actions_Dispatcher class description.
			dispatcher.do_action( 'composite_availability_message_changed' );
		} );

	};

	/**
	 * Adds an action handler to the dispatcher.
	 */
	this.add_action = function( action, callback, priority, context ) {

		if ( typeof action === 'string' && typeof callback === 'function' ) {
			priority = parseInt( ( priority || 10 ), 10 );
			functions.add_action( action, callback, priority, context );
		}

		return dispatcher;
	};

	/**
	 * Performs an action if it exists.
	 */
	this.do_action = function( action, args ) {

		if ( typeof action === 'string' ) {
			functions.do_action( action, args );
		}

		return dispatcher;
	};

	/**
	 * Removes the specified action.
	 */
	this.remove_action = function( action, callback ) {

		if ( typeof action === 'string' ) {
			functions.remove_action( action, callback );
		}

		return dispatcher;
	};

};
