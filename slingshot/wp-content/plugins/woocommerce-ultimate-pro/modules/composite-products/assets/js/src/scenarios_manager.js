/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/**
 * Updates the active scenarios when:
 *
 *  - Refreshing/appending new component options: Adds an 'available_options_changed' action handler.
 *  - Selecting a new product/variation: Adds a 'component_selection_changed' action handler ('component_selection_changed' action dispatched when a 'change:selected_product' and 'change:selected_variation' event is triggered by a Component_Selection_Model).
 *
 * Triggers the 'active_scenarios_updated' and 'active_scenarios_changed' events which are picked up by Component_Options_Model models to update their options state (handlers added to the corresponding dispatcher actions).
 */
wc_cp_classes.WC_CP_Scenarios_Manager = function( composite ) {

	var manager      = this,
		manager_data = {
		updating_scenarios:             false,
		scenario_data:                  composite.$composite_data.data( 'scenario_data' ),
		active_scenarios:               [],
		invalid_product_step_indexes:   [],
		invalid_variation_step_indexes: [],
	};

	_.extend( manager, Backbone.Events );

	/**
	 * Initialize after components have been created.
	 */
	this.init = function() {

		/**
		 * Update the active scenarios when refreshing/appending new component options.
		 */
		composite.actions.add_action( 'available_options_changed', this.available_options_changed_handler, -10, this );

		/**
		 * Update the active scenarios when selecting a new product/variation.
		 */
		composite.actions.add_action( 'component_selection_changed', this.selection_changed_handler, 10, this );

		// Initialize scenarios.
		composite.console_log( 'debug:events', '\nInitializing Scenarios Manager...' );
		composite.debug_tab_count = composite.debug_tab_count + 2;

		manager.update_active_scenarios( _.first( composite.steps ), false, false );

		composite.debug_tab_count = composite.debug_tab_count - 2;
		composite.console_log( 'debug:events', '\nScenarios Manager initialized.\n' );
	};

	/**
	 * True if the component was found to contain an invalid product selection.
	 */
	this.has_invalid_product = function( step_index ) {

		return _.contains( manager_data.invalid_product_step_indexes, step_index );
	};

	/**
	 * True if the component was found to contain an invalid variation selection.
	 */
	this.has_invalid_variation = function( step_index ) {

		return _.contains( manager_data.invalid_variation_step_indexes, step_index );
	};

	/**
	 * Return active scenarios.
	 */
	this.get_active_scenarios = function() {

		return manager_data.active_scenarios;
	};

	/**
	 * Return all step indexes where an invalid selection was found during the last update.
	 */
	this.get_invalid_step_indexes = function() {

		return _.sortBy( _.uniq( _.union( manager_data.invalid_product_step_indexes, manager_data.invalid_variation_step_indexes ) ), function( num ) { return num; } );
	};

	/**
	 * Return current scenario data.
	 */
	this.get_scenario_data = function() {

		return manager_data.scenario_data;
	};

	/**
	 * Replace stored scenario data for a given component, for instance when refreshing the component options view.
	 */
	this.set_component_scenario_data = function( component_id, component_scenario_data ) {

		manager_data.scenario_data.scenario_data[ component_id ] = component_scenario_data;
	};

	/**
	 * Append scenario data to a given component, in order to include data for more products.
	 */
	this.merge_component_scenario_data = function( component_id, component_scenario_data ) {

		$.each( component_scenario_data, function( product_id, product_in_scenarios ) {
			manager_data.scenario_data.scenario_data[ component_id ][ product_id ] = product_in_scenarios;
		} );
	};

	this.selection_changed_handler = function( triggered_by ) {

		this.update_active_scenarios( triggered_by );
	};

	this.available_options_changed_handler = function( triggered_by ) {

		this.update_active_scenarios( triggered_by );
	};

	/**
	 * Updates active scenarios and triggers an event if changed.
	 */
	this.update_active_scenarios = function( triggered_by ) {

		composite.console_log( 'debug:scenarios', '\nScenarios update triggered by event originating from "' + triggered_by.get_title() + '"...' );

		// Don't do anything if event originates from script initialization.
		if ( triggered_by.initializing_scripts ) {
			composite.console_log( 'debug:scenarios', 'Breaking out - event triggered during script init...\n\n' );
			return false;
		}

		composite.console_log( 'debug:scenarios', '\nClearing incompatible products/variations data...' );

		manager_data.invalid_product_step_indexes   = [];
		manager_data.invalid_variation_step_indexes = [];

		// Backup current state to compare with new one.
		var active_scenarios_pre                    = manager_data.active_scenarios;

		// Get new scenarios based on selections.
		manager_data.updating_scenarios             = true;
		var updated_scenarios                       = this.calculate_active_scenarios( triggered_by, false, false );
		manager_data.updating_scenarios             = false;

		// Only trigger event if the active scenarios changed :)
		if ( active_scenarios_pre.length !== updated_scenarios.length || active_scenarios_pre.length !== _.intersection( active_scenarios_pre, updated_scenarios ).length ) {
			composite.console_log( 'debug:scenarios', '\nActive scenarios changed: - [' + active_scenarios_pre + '] => [' + updated_scenarios + ']' );
			manager_data.active_scenarios = updated_scenarios;

			manager.trigger( 'active_scenarios_changed', triggered_by );

		} else {
			composite.console_log( 'debug:scenarios', '\nActive scenarios unchanged.' );
		}

		manager.trigger( 'active_scenarios_updated', triggered_by );
	};

	/**
	 * Extract active scenarios from current selections.
	 * Scenarios can be calculated up to, or excluding the step passed as reference.
	 */
	this.calculate_active_scenarios = function( ref_step, up_to_ref, excl_ref ) {

		var ref_step_index         = ref_step.step_index,
			scenarios              = manager.get_scenario_data().scenarios,
			compat_group_scenarios = manager.filter_scenarios_by_type( scenarios, 'compat_group' );

		if ( ref_step.is_review() ) {
			ref_step_index = 1000;
		}

		if ( compat_group_scenarios.length === 0 ) {
			scenarios.push( '0' );
		}

		var active_scenarios            = scenarios;
		var scenario_shaping_components = [];

		if ( ! manager_data.updating_scenarios ) {
			composite.console_log( 'debug:scenarios', '\n' + 'Scenarios requested by "' + ref_step.get_title() + '"...' );
		}

		composite.console_log( 'debug:scenarios', '\n' + 'Calculating active scenarios...' );

		$.each( composite.get_components(), function( index, component ) {

			// Omit reference component when excluded.
			if ( excl_ref && parseInt( component.step_index ) === parseInt( ref_step_index ) ) {
				return true;
			}

			// Exit when reaching beyond reference component.
			if ( up_to_ref && component.step_index > ref_step_index ) {
				return false;
			}

			var product_id   = component.get_selected_product( false ),
				product_type = component.get_selected_product_type();

			if ( product_id !== null && product_id >= 0 ) {

				var scenario_data      = manager.get_scenario_data().scenario_data,
					item_scenario_data = scenario_data[ component.component_id ];

				// Treat '' optional component selections as 'None' if the component is optional.
				if ( product_id === '' ) {
					if ( 0 in item_scenario_data ) {
						product_id = '0';
					} else {
						return true;
					}
				}

				var product_in_scenarios = ( product_id in item_scenario_data ) ? item_scenario_data[ product_id ] : [];

				composite.console_log( 'debug:scenarios', 'Selection #' + product_id + ' of "' + component.get_title() + '" in scenarios: [' + product_in_scenarios + ']' );

				var product_intersection    = _.intersection( active_scenarios, product_in_scenarios ),
					product_is_compatible   = product_intersection.length > 0,
					variation_id            = '',
					variation_is_compatible = true;

				if ( product_is_compatible ) {

					if ( product_type === 'variable' ) {

						variation_id = component.get_selected_variation( false );

						if ( variation_id > 0 ) {

							var variation_in_scenarios = ( variation_id in item_scenario_data ) ? item_scenario_data[ variation_id ] : [];

							composite.console_log( 'debug:scenarios', 'Variation selection #' + variation_id + ' of "' + component.get_title() + '" in scenarios: [' + variation_in_scenarios +']' );

							product_intersection    = _.intersection( product_intersection, variation_in_scenarios );
							variation_is_compatible = product_intersection.length > 0;
						}
					}
				}

				var is_compatible = product_is_compatible && variation_is_compatible;

				if ( is_compatible ) {

					scenario_shaping_components.push( component.component_id );
					active_scenarios = product_intersection;

					composite.console_log( 'debug:scenarios', 'Active scenarios: [' + active_scenarios + ']' );

				} else if ( ! product_is_compatible ) {

					var invalid_product_msg = 'Incompatible product selection found...';
					if ( manager_data.updating_scenarios && product_id > 0 ) {
						invalid_product_msg += ' saved.';
						manager_data.invalid_product_step_indexes.push( component.step_index );
					}
					composite.console_log( 'debug:scenarios', invalid_product_msg );

				} else if ( ! variation_is_compatible ) {

					var invalid_variation_msg = 'Incompatible variation selection found...';
					if ( manager_data.updating_scenarios && variation_id > 0 ) {
						invalid_variation_msg += ' saved.';
						manager_data.invalid_variation_step_indexes.push( component.step_index );
					}
					composite.console_log( 'debug:scenarios', invalid_variation_msg );
				}
			}

		} );

		composite.console_log( 'debug:scenarios', 'Removing active scenarios where all scenario shaping components (' + scenario_shaping_components + ') are masked...' );

		var result = manager.get_binding_scenarios( active_scenarios, scenario_shaping_components );

		composite.console_log( 'debug:scenarios', 'Calculated active scenarios: [' + result + ']\n' );

		return result;
	};

	/**
	 * Filters out unbinding scenarios.
	 */
	this.get_binding_scenarios = function( scenarios, scenario_shaping_components ) {

		var masked = this.get_scenario_data().scenario_settings.masked_components,
			clean  = [];

		if ( scenario_shaping_components.length > 0 ) {

			if ( scenarios.length > 0 ) {
				for ( var i = 0; i < scenarios.length; i++ ) {

					var scenario_id = scenarios[ i ];

					// If all scenario shaping components are masked, filter out the scenario.
					var all_components_masked_in_scenario = true;

					for ( var k = 0; k < scenario_shaping_components.length; k++ ) {

						var component_id = scenario_shaping_components[ k ];

						if ( $.inArray( component_id.toString(), masked[ scenario_id ] ) == -1 ) {
							all_components_masked_in_scenario = false;
							break;
						}
					}

					if ( ! all_components_masked_in_scenario ) {
						clean.push( scenario_id );
					}
				}
			}

		} else {
			clean = scenarios;
		}

		if ( clean.length === 0 && scenarios.length > 0 ) {
			clean = scenarios;
		}

		return clean;
	};

	/**
	 * Gets active scenarios by type.
	 */
	this.get_active_scenarios_by_type = function( type ) {

		return this.filter_scenarios_by_type( manager_data.active_scenarios, type );
	};

	/**
	 * Filters active scenarios by type.
	 */
	this.get_scenarios_by_type = function( type ) {

		return this.filter_scenarios_by_type( this.get_scenario_data().scenarios, type );
	};

	/**
	 * Filters scenarios by type.
	 */
	this.filter_scenarios_by_type = function( scenarios, type ) {

		var filtered    = [],
			scenario_id = '';

		if ( scenarios.length > 0 ) {
			for ( var i = 0; i < scenarios.length; i++ ) {

				scenario_id = scenarios[ i ];

				if ( 'all' === type || $.inArray( type, this.get_scenario_data().scenario_settings.scenario_actions[ scenario_id ] ) > -1 ) {
					filtered.push( scenario_id );
				}
			}
		}

		return filtered;
	};

	/**
	 * Filters out scenarios where a component is masked.
	 */
	this.clean_masked_scenarios = function( scenarios, component_id ) {

		var masked      = this.get_scenario_data().scenario_settings.masked_components,
			clean       = [],
			scenario_id = '';

		if ( scenarios.length > 0 ) {
			for ( var i = 0; i < scenarios.length; i++ ) {

				scenario_id = scenarios[ i ];

				if ( $.inArray( component_id.toString(), masked[ scenario_id ] ) == -1 ) {
					clean.push( scenario_id );
				}

			}
		}

		return clean;
	};

	/**
	 * Returns scenarios where a component is masked.
	 */
	this.get_masked_scenarios = function( scenarios, component_id ) {

		var masked      = this.get_scenario_data().scenario_settings.masked_components,
			dirty       = [],
			scenario_id = '';

		if ( scenarios.length > 0 ) {
			for ( var i = 0; i < scenarios.length; i++ ) {

				scenario_id = scenarios[ i ];

				if ( $.inArray( component_id.toString(), masked[ scenario_id ] ) > -1 ) {
					dirty.push( scenario_id );
				}

			}
		}

		return dirty;
	};
};
