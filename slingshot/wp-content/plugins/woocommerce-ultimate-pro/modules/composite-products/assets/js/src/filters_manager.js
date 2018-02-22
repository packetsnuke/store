/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/**
 * Filters manager that handles filtering of various function outputs.
 *
 * A complete reference of all application filters & callbacks is provided in the "Filters Reference" below.
 */
wc_cp_classes.WC_CP_Filters_Manager = function() {

	/*
	 *--------------------------*
	 *                          *
	 *   Filters Reference      *
	 *                          *
	 *--------------------------*
	 *
	 *--------------------------*
	 *   1. Composite           *
	 *--------------------------*
	 *
	 *
	 * Filter 'composite_validation_messages':
	 *
	 * Filters the individual Composite validation notice messages before updating model state.
	 *
	 * @param  array  messages   Validation messages.
	 * @return array
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'composite_totals':
	 *
	 * Filters the Composite totals before updating model state.
	 *
	 * @param  object  totals   Composite prices.
	 * @return object
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'composite_pagination_view_data':
	 *
	 * Filters the data passed to the pagination view template.
	 *
	 * @param  array  data   Template data.
	 * @return array
	 *
	 * @hooked void
	 *
	 *
	 *
	 *--------------------------*
	 *   2. Components          *
	 *--------------------------*
	 *
	 *
	 * Filter 'component_totals':
	 *
	 * Filters the totals of a Component before updating the data model state.
	 *
	 * @param  object            totals      Component prices.
	 * @param  WC_CP_Component   component   Component object.
	 * @return object
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'step_validation_messages':
	 *
	 * Filters the validation notices associated with a step.
	 *
	 * @param  array        messages   Validation messages.
	 * @param  string       scope      Scope for validation messages ('composite', 'component').
	 * @param  WC_CP_Step   step       Step object.
	 * @return array
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'step_is_valid':
	 *
	 * Filters the validation status of a step before updating the Step_Validation_Model state.
	 *
	 * @param  boolean      is_valid   Validation state.
	 * @param  WC_CP_Step   step       Step object.
	 * @return boolean
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'step_is_locked':
	 *
	 * @param  boolean      is_locked   Access state.
	 * @param  WC_CP_Step   step        Step object.
	 * @return boolean
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_is_optional':
	 *
	 * Filters the optional status of a Component.
	 *
	 * @param  boolean          is_optional   True if optional.
	 * @param  WC_CP_Component  step          Component object.
	 * @return boolean
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_selection_title':
	 *
	 * Filters the raw product title of the current Component selection.
	 *
	 * @param  string            title        The title.
	 * @param  WC_CP_Component   component    Component object.
	 * @return string
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_selection_formatted_title':
	 *
	 * Filters the formatted title of the current Component selection.
	 *
	 * @param  string            title        The returned title.
	 * @param  string            raw_title    The raw, unformatted title.
	 * @param  string            qty          The quantity of the selected product.
	 * @param  string            meta         The formatted meta.
	 * @param  WC_CP_Component   component    Component object.
	 * @return string
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_selection_meta':
	 *
	 * Filters the meta array associated with the current Component selection.
	 *
	 * @param  array             meta         The returned meta array.
	 * @param  WC_CP_Component   component    WC_CP_Component  Component object.
	 * @return array
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_selection_formatted_meta':
	 *
	 * Filters the formatted meta associated with the current Component selection.
	 *
	 * @param  string           formatted_meta   The returned formatted meta.
	 * @param  array            meta             The meta array.
	 * @param  WC_CP_Component  component        Component object.
	 * @return string
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_configuration':
	 *
	 * Filters the configuration data object associated with a Component.
	 *
	 * @param  object           config           The returned component configuration data object.
	 * @param  WC_CP_Component  component        Component object.
	 * @return object
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_selection_change_animation_duration':
	 *
	 * Filters the configuration data object associated with a Component.
	 *
	 * @param  integer          duration         The animation duration.
	 * @param  string           open_or_close    The animation context ('open'|'close').
	 * @param  WC_CP_Component  component        Component object.
	 * @return integer
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_summary_element_content_data':
	 *
	 * Filters the summary element content data.
	 *
	 * @param  object                  content_data     The summary element data passed to the js template.
	 * @param  WC_CP_Component         component        Component object.
	 * @param  Composite_Summary_View  view             Component summary view object.
	 * @return object
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_hide_disabled_products':
	 *
	 * Allows you to filter the output of 'WC_CP_Component::hide_disabled_products()'.
	 *
	 * @param  boolean          hide_disabled_products       Whether to hide disabled product options.
	 * @param  WC_CP_Component  component                    Component object.
	 * @return boolean
	 *
	 * @hooked void
	 *
	 *
	 *
	 * Filter 'component_hide_disabled_variations':
	 *
	 * Allows you to filter the output of 'WC_CP_Component::hide_disabled_variations()'.
	 *
	 * @param  boolean          hide_disabled_variations     Whether to hide disabled product variations.
	 * @param  WC_CP_Component  component                    Component object.
	 * @return boolean
	 *
	 * @hooked void
	 */

	var manager   = this,
		filters   = {},
		functions = {

			add_filter: function( hook, callback, priority, context ) {

				var hookObject = {
					callback : callback,
					priority : priority,
					context : context
				};

				var hooks = filters[ hook ];
				if ( hooks ) {
					hooks.push( hookObject );
					hooks = this.sort_filters( hooks );
				} else {
					hooks = [ hookObject ];
				}

				filters[ hook ] = hooks;
			},

			remove_filter: function( hook, callback, context ) {

				var handlers, handler, i;

				if ( ! filters[ hook ] ) {
					return;
				}
				if ( ! callback ) {
					filters[ hook ] = [];
				} else {
					handlers = filters[ hook ];
					if ( ! context ) {
						for ( i = handlers.length; i--; ) {
							if ( handlers[ i ].callback === callback ) {
								handlers.splice( i, 1 );
							}
						}
					} else {
						for ( i = handlers.length; i--; ) {
							handler = handlers[ i ];
							if ( handler.callback === callback && handler.context === context) {
								handlers.splice( i, 1 );
							}
						}
					}
				}
			},

			sort_filters: function( hooks ) {

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

			apply_filters: function( hook, args ) {

				var handlers = filters[ hook ], i, len;

				if ( ! handlers ) {
					return args[ 0 ];
				}

				len = handlers.length;

				for ( i = 0; i < len; i++ ) {
					args[ 0 ] = handlers[ i ].callback.apply( handlers[ i ].context, args );
				}

				return args[ 0 ];
			}

		};

	/**
	 * Adds a filter.
	 */
	this.add_filter = function( filter, callback, priority, context ) {

		if ( typeof filter === 'string' && typeof callback === 'function' ) {
			priority = parseInt( ( priority || 10 ), 10 );
			functions.add_filter( filter, callback, priority, context );
		}

		return manager;
	};

	/**
	 * Applies all filter callbacks.
	 */
	this.apply_filters = function( filter, args ) {

		if ( typeof filter === 'string' ) {
			return functions.apply_filters( filter, args );
		}
	};

	/**
	 * Removes the specified filter callback.
	 */
	this.remove_filter = function( filter, callback ) {

		if ( typeof filter === 'string' ) {
			functions.remove_filter( filter, callback );
		}

		return manager;
	};

};
