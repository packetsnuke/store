/* @exclude */
/* jshint -W069 */
/* jshint -W041 */
/* jshint -W018 */
/* @endexclude */

/*-----------------------------------------------------------------*/
/*  Global variable for composite apps.                            */
/*-----------------------------------------------------------------*/

var wc_cp_composite_scripts = {};

/*-----------------------------------------------------------------*/
/*  Global utility variables + functions.                          */
/*-----------------------------------------------------------------*/

/**
 * Cache for speed.
 */
var $wc_cp_body     = false,
	$wc_cp_html     = jQuery( 'html' ),
	$wc_cp_window   = jQuery( window ),
	$wc_cp_document = jQuery( document );

/**
 * BlockUI background params.
 */
var wc_cp_block_params = {
	message:    null,
	fadeIn:     0,
	fadeOut:    0,
	overlayCSS: {
		background: 'rgba( 255, 255, 255, 0 )',
		opacity:    1,
	}
};

var wc_cp_block_wait_params = {
	message:    null,
	fadeIn:     200,
	fadeOut:    200,
	overlayCSS: {
		background: 'rgba( 255, 255, 255, 0 )',
		opacity:    0.6,
	}
};

/**
 * Toggle-box handling.
 */
function wc_cp_toggle_element( $container, $content, complete ) {

	if ( $container.data( 'animating' ) === true ) {
		return false;
	}

	if ( $container.hasClass( 'closed' ) ) {
		setTimeout( function() {
			$content.slideDown( { duration: 300, queue: false, always: function() {
				$container.data( 'animating', false );
				if ( typeof( complete ) === 'function' ) {
					complete();
				}
			} } );
		}, 40 );
		$container.removeClass( 'closed' ).addClass( 'open' );
		$container.data( 'animating', true );
	} else {
		setTimeout( function() {
			$content.slideUp( { duration: 300, queue: false, always: function() {
				$container.data( 'animating', false );
				if ( typeof( complete ) === 'function' ) {
					complete();
				}
			} } );
		}, 40 );
		$container.removeClass( 'open' ).addClass( 'closed' );
		$container.data( 'animating', true );
	}

	return true;
}

/**
 * Viewport scroller.
 */
function wc_cp_scroll_viewport( target, params ) {

	var anim_complete;
	var scroll_to;

	var partial         = typeof( params.partial ) === 'undefined' ? true : params.partial;
	var offset          = typeof( params.offset ) === 'undefined' ? 50 : params.offset;
	var timeout         = typeof( params.timeout ) === 'undefined' ? 5 : params.timeout;
	var anim_duration   = typeof( params.duration ) === 'undefined' ? 250 : params.duration;
	var anim_queue      = typeof( params.queue ) === 'undefined' ? false : params.queue;
	var always_complete = typeof( params.always_on_complete ) === 'undefined' ? false : params.always_on_complete;
	var scroll_method   = typeof( params.scroll_method ) === 'undefined' ? false : params.scroll_method;

	var do_scroll       = false;
	var $w              = $wc_cp_window;
	var $d              = $wc_cp_document;

	if ( typeof( params.on_complete ) === 'undefined' || params.on_complete === false ) {
		anim_complete = function() {
			return false;
		};
	} else {
		anim_complete = params.on_complete;
	}

	var scroll_viewport = function() {

		// Scroll viewport by an offset.
		if ( target === 'relative' ) {

			scroll_to = $w.scrollTop() - offset;
			do_scroll = true;

		// Scroll viewport to absolute document position.
		} else if ( target === 'absolute' ) {

			scroll_to = offset;
			do_scroll = true;

		// Scroll to target element.
		} else if ( target.length > 0 && target.is( ':visible' ) && ! target.wc_cp_is_in_viewport( partial ) ) {

			var window_offset = offset;

			if ( scroll_method === 'bottom' || target.hasClass( 'scroll_bottom' ) ) {
				window_offset = $w.height() - target.outerHeight( true ) - offset;
			} else if ( scroll_method === 'middle' ) {
				window_offset = $w.height() / 3 * 2 - target.outerHeight( true ) - offset;
			} else {
				window_offset = wc_composite_params.scroll_viewport_top_offset;
			}

			scroll_to = target.offset().top - window_offset;

			// Ensure element top is in viewport.
			if ( target.offset().top < scroll_to ) {
				scroll_to = target.offset().top;
			}

			do_scroll = true;
		}

		if ( do_scroll ) {

			// Prevent out-of-bounds scrolling.
			if ( scroll_to > $d.height() - $w.height() ) {
				scroll_to = $d.height() - $w.height() - 100;
			}

			// Avoid scrolling both html and body.
			var pos            = $wc_cp_html.scrollTop();
			var animate_target = $wc_cp_body;

			$wc_cp_html.scrollTop( $wc_cp_html.scrollTop() - 1 );
			if ( pos != $wc_cp_html.scrollTop() ) {
				animate_target = $wc_cp_html;
			}

			animate_target.animate( { scrollTop: scroll_to }, { duration: anim_duration, queue: anim_queue, always: anim_complete } );

		} else {
			if ( always_complete ) {
				anim_complete();
			}
		}
	};

	if ( timeout > 0 ) {
		setTimeout( function() {
			scroll_viewport();
		}, timeout );
	} else {
		scroll_viewport();
	}
}

/**
 * Formats price strings according to WC settings.
 */
function wc_cp_woocommerce_number_format( price ) {

	var remove     = wc_composite_params.currency_format_decimal_sep;
	var position   = wc_composite_params.currency_position;
	var symbol     = wc_composite_params.currency_symbol;
	var trim_zeros = wc_composite_params.currency_format_trim_zeros;
	var decimals   = wc_composite_params.currency_format_num_decimals;

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
function wc_cp_number_format( number ) {

	var decimals      = wc_composite_params.currency_format_num_decimals;
	var decimal_sep   = wc_composite_params.currency_format_decimal_sep;
	var thousands_sep = wc_composite_params.currency_format_thousand_sep;

	var n = number, c = isNaN( decimals = Math.abs( decimals ) ) ? 2 : decimals;
	var d = typeof( decimal_sep ) === 'undefined' ? ',' : decimal_sep;
	var t = typeof( thousands_sep ) === 'undefined' ? '.' : thousands_sep, s = n < 0 ? '-' : '';
	var i = parseInt( n = Math.abs( +n || 0 ).toFixed(c) ) + '', j = ( j = i.length ) > 3 ? j % 3 : 0;

	return s + ( j ? i.substr( 0, j ) + t : '' ) + i.substr(j).replace( /(\d{3})(?=\d)/g, '$1' + t ) + ( c ? d + Math.abs( n - i ).toFixed(c).slice(2) : '' );
}

/**
 * Rounds price values according to WC settings.
 */
function wc_cp_number_round( number ) {

	var precision         = wc_composite_params.currency_format_num_decimals,
		factor            = Math.pow( 10, precision ),
		tempNumber        = number * factor,
		roundedTempNumber = Math.round( tempNumber );

	return roundedTempNumber / factor;
}


/**
 * i18n-friendly joining of values in an array of strings.
 */
function wc_cp_join( arr ) {

	var joined_arr = '';
	var count      = arr.length;

	if ( count > 0 ) {

		var loop = 0;

		for ( var i = 0; i < count; i++ ) {

			loop++;

			if ( count == 1 || loop == 1 ) {
				joined_arr = arr[ i ];
			} else {
				joined_arr = wc_composite_params.i18n_comma_sep.replace( '%s', joined_arr ).replace( '%v', arr[ i ] );
			}
		}
	}

	return joined_arr;
}

/**
 * Construct a (formatted) map of selected variation attributes.
 */
function wc_cp_get_variation_data( $variations, formatted ) {

	formatted = typeof( formatted ) === 'undefined' ? false : formatted;

	var $attribute_options       = $variations.find( '.attribute-options' ),
		attribute_options_length = $attribute_options.length,
		meta                     = [],
		formatted_meta           = '';

	if ( attribute_options_length === 0 ) {
		return '';
	}

	$attribute_options.each( function( index ) {

		var $attribute_option = jQuery( this );

		var selected = $attribute_option.find( 'select' ).val();

		if ( selected === '' ) {
			meta           = [];
			formatted_meta = '';
			return false;
		}

		var key   = $attribute_option.data( 'attribute_label' ),
			value = $attribute_option.find( 'select option:selected' ).text();

		meta.push( { meta_key: key, meta_value: value } );

		formatted_meta = formatted_meta + '<span class="meta_element"><span class="meta_key">' + key + ':</span> <span class="meta_value">' + value + '</span>';

		if ( index !== attribute_options_length - 1 ) {
			formatted_meta = formatted_meta + '<span class="meta_element_sep">, </span>';
		}

		formatted_meta = formatted_meta + '</span>';

	} );

	return formatted ? formatted_meta : meta;
}

/**
 * Element-in-viewport check with partial element detection & direction support.
 * Credit: Sam Sehnert - https://github.com/customd/jquery-visible
 */
jQuery.fn.wc_cp_is_in_viewport = function( partial, hidden, direction ) {

	var $w = $wc_cp_window;

	if ( this.length < 1 ) {
		return;
	}

	var $t         = this.length > 1 ? this.eq(0) : this,
		t          = $t.get(0),
		vpWidth    = $w.width(),
		vpHeight   = $w.height(),
		clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;

	direction = (direction) ? direction : 'vertical';

	if ( typeof t.getBoundingClientRect === 'function'){

		// Use this native browser method, if available.
		var rec      = t.getBoundingClientRect(),
			tViz     = rec.top    >= 0 && rec.top    <  vpHeight,
			bViz     = rec.bottom >  0 && rec.bottom <= vpHeight,
			lViz     = rec.left   >= 0 && rec.left   <  vpWidth,
			rViz     = rec.right  >  0 && rec.right  <= vpWidth,
			vVisible = partial ? tViz || bViz : tViz && bViz,
			hVisible = partial ? lViz || rViz : lViz && rViz;

		if ( direction === 'both' ) {
			return clientSize && vVisible && hVisible;
		} else if ( direction === 'vertical' ) {
			return clientSize && vVisible;
		} else if ( direction === 'horizontal' ) {
			return clientSize && hVisible;
		}

	} else {

		var viewTop         = $w.scrollTop(),
			viewBottom      = viewTop + vpHeight,
			viewLeft        = $w.scrollLeft(),
			viewRight       = viewLeft + vpWidth,
			offset          = $t.offset(),
			_top            = offset.top,
			_bottom         = _top + $t.height(),
			_left           = offset.left,
			_right          = _left + $t.width(),
			compareTop      = partial === true ? _bottom : _top,
			compareBottom   = partial === true ? _top : _bottom,
			compareLeft     = partial === true ? _right : _left,
			compareRight    = partial === true ? _left : _right;

		if ( direction === 'both' ) {
			return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) ) && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
		} else if ( direction === 'vertical' ) {
			return !!clientSize && ( ( compareBottom <= viewBottom ) && ( compareTop >= viewTop ) );
		} else if ( direction === 'horizontal' ) {
			return !!clientSize && ( ( compareRight <= viewRight ) && ( compareLeft >= viewLeft ) );
		}
	}
};

/**
 * Composite app object getter.
 */
jQuery.fn.wc_get_composite_script = function() {

	var $composite_form = jQuery( this );

	if ( ! $composite_form.hasClass( 'composite_form' ) ) {
		return false;
	}

	var script_id = $composite_form.data( 'script_id' );

	if ( typeof( wc_cp_composite_scripts[ script_id ] ) !== 'undefined' ) {
		return wc_cp_composite_scripts[ script_id ];
	}

	return false;
};

/*-----------------------------------------------------------------*/
/*  Encapsulation.                                                 */
/*-----------------------------------------------------------------*/

( function( $, Backbone ) {

	/*-----------------------------------------------------------------*/
	/*  Class Definitions.                                             */
	/*-----------------------------------------------------------------*/

	var wc_cp_classes = {};

	/**
	 * Composite product object. The core of the app.
	 */
	function WC_CP_Composite( data ) {

		var composite                           = this;

		this.composite_id                       = data.$composite_data.data( 'container_id' );

		/*
		 * Common jQuery DOM elements for quick, global access.
		 */
		this.$composite_data                    = data.$composite_data;
		this.$composite_form                    = data.$composite_form;
		this.$composite_add_to_cart_button      = data.$composite_form.find( '.composite_add_to_cart_button' );
		this.$composite_navigation              = data.$composite_form.find( '.composite_navigation' );
		this.$composite_navigation_top          = data.$composite_form.find( '.composite_navigation.top' );
		this.$composite_navigation_bottom       = data.$composite_form.find( '.composite_navigation.bottom' );
		this.$composite_navigation_movable      = data.$composite_form.find( '.composite_navigation.movable' );
		this.$composite_pagination              = data.$composite_form.find( '.composite_pagination' );
		this.$composite_summary                 = data.$composite_form.find( '.composite_summary' );
		this.$composite_summary_widget          = $( '.widget_composite_summary' ).filter( function() { return $( this ).find( '.widget_composite_summary_content_' + composite.composite_id ).length > 0; } );

		this.$components                        = data.$composite_form.find( '.component' );
		this.$steps                             = {};

		this.$composite_availability            = data.$composite_data.find( '.composite_availability' );
		this.$composite_price                   = data.$composite_data.find( '.composite_price' );
		this.$composite_message                 = data.$composite_data.find( '.composite_message' );
		this.$composite_button                  = data.$composite_data.find( '.composite_button' );
		this.$composite_status                  = data.$composite_form.find( '.composite_status' );
		this.$composite_transition_helper       = data.$composite_form.find( '.scroll_show_component' );
		this.$composite_form_blocker            = data.$composite_form.find( '.form_input_blocker' );

		/*
		 * Object properties used for some real work.
		 */
		this.timers                             = { on_resize_timer: false };

		this.ajax_url                           = wc_composite_params.use_wc_ajax === 'yes' ? woocommerce_params.wc_ajax_url : woocommerce_params.ajax_url;
		this.debug_tab_count                    = 0;

		this.settings                           = data.$composite_data.data( 'composite_settings' );

		this.is_initialized                     = false;
		this.has_transition_lock                = false;

		this.steps                              = [];
		this.step_factory                       = new wc_cp_classes.WC_CP_Step_Factory();

		// Stores and updates the active scenarios. Used by component models to calculate active scenarios excl and/or up to specific steps.
		this.scenarios                          = new wc_cp_classes.WC_CP_Scenarios_Manager( this );

		// WP-style actions dispatcher. Dispatches actions in response to key model events.
		this.actions                            = new wc_cp_classes.WC_CP_Actions_Dispatcher( this );

		// WP-style filters manager.
		this.filters                            = new wc_cp_classes.WC_CP_Filters_Manager();

		// Backbone Router.
		this.router                             = false;

		// Composite Data Model.
		this.data_model                         = false;

		// View classes. If necessary, override/extend these before any associated views are instantiated - @see 'init_views'.
		this.view_classes                       = new wc_cp_classes.WC_CP_Views( this );

		// Model classes. If necessary, override/extend these before any associated models are instantiated - @see 'init_models'.
		this.model_classes                      = new wc_cp_classes.WC_CP_Models( this );

		// Composite Views - @see 'init_views'.
		this.composite_summary_view             = false;
		this.composite_pagination_view          = false;
		this.composite_navigation_view          = false;
		this.composite_validation_view          = false;
		this.composite_availability_view        = false;
		this.composite_price_view               = false;
		this.composite_add_to_cart_button_view  = false;
		this.composite_summary_widget_views     = [];

		// API.
		this.api                                = {

			/**
			 * Navigate to a step by id.
			 *
			 * @param  string step_id
			 * @return false | void
			 */
			navigate_to_step: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				composite.navigate_to_step( step );
			},

			/**
			 * Navigate to the previous step, if one exists.
			 *
			 * @return void
			 */
			show_previous_step: function() {

				composite.show_previous_step();
			},

			/**
			 * Navigate to the next step, if one exists.
			 *
			 * @return void
			 */
			show_next_step: function() {

				composite.show_next_step();
			},

			/**
			 * Get all created instances of WC_CP_Step.
			 *
			 * @return array
			 */
			get_steps: function() {

				return composite.get_steps();
			},

			/**
			 * Get all created instances of WC_CP_Component (inherits from WC_CP_Step).
			 *
			 * @return array
			 */
			get_components: function() {

				return composite.get_components();
			},

			/**
			 * Get the instance of WC_CP_Step based on its step_id. For components, step_id === component_id.
			 *
			 * @param  string  step_id
			 * @return WC_CP_Step | false
			 */
			get_step: function( step_id ) {

				return composite.get_step( step_id );
			},

			/**
			 * Get the instance of WC_CP_Step based on its step_id, step_index, or step_slug.
			 *
			 * - step_id: for components, step_id === component_id
			 * - step_index: zero-based index of a step
			 * - step_slug: sanitized slug obtained from the step title, used mainly by the Backbone Router to keep track of browser history when navigating between steps.
			 *
			 * @param  string  by
			 * @param  string  id
			 * @return WC_CP_Step | false
			 */
			get_step_by: function( by, id ) {

				return composite.get_step_by( by, id );
			},

			/**
			 * Get the step title of a WC_CP_Step instance based on its step_id.
			 *
			 * @param  string  step_id
			 * @return string | false
			 */
			get_step_title: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				return step.get_title();
			},

			/**
			 * Get the step slug of a WC_CP_Step instance based on its step_id.
			 *
			 * @param  string  step_id
			 * @return string | false
			 */
			get_step_slug: function( step_id ) {

				var step = composite.get_step_by( 'id', step_id );

				if ( false === step ) {
					return false;
				}

				return step.step_slug;
			},

			/**
			 * Get the current step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_current_step: function() {

				return composite.get_current_step();
			},

			/**
			 * Get the previous step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_previous_step: function() {

				return composite.get_previous_step();
			},

			/**
			 * Get the next step.
			 *
			 * @return WC_CP_Step | false
			 */
			get_next_step: function() {

				return composite.get_next_step();
			},

			/**
			 * Get the current composite totals.
			 *
			 * @return object
			 */
			get_composite_totals: function() {

				return composite.data_model.get( 'totals' );
			},

			/**
			 * Get the current stock status of the composite.
			 *
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_composite_stock_status: function() {

				return composite.data_model.get( 'is_in_stock' ) ? 'in-stock' : 'out-of-stock';
			},

			/**
			 * Get the current availability string of the composite.
			 *
			 * @return string
			 */
			get_composite_availability: function() {

				var availability = composite.composite_availability_view.get_components_availability_string();

				if ( availability === '' && false !== composite.composite_availability_view.$composite_stock_status ) {
					availability = composite.composite_availability_view.$composite_stock_status.clone().wrap( '<div></div>' ).parent().html();
				}

				return availability;
			},

			/**
			 * Get the current validation status of the composite.
			 *
			 * @return string ('pass' | 'fail')
			 */
			get_composite_validation_status: function() {

				return composite.data_model.get( 'passes_validation' ) ? 'pass' : 'fail';
			},

			/**
			 * Get the current validation messages for the composite.
			 *
			 * @return array
			 */
			get_composite_validation_messages: function() {

				return composite.data_model.get( 'validation_messages' );
			},

			/**
			 * Gets composite configuration details.
			 *
			 * @return object | false
			 */
			get_composite_configuration: function() {

				var composite_config = {};

				if ( composite.get_components().length === 0 ) {
					return false;
				}

				$.each( composite.get_components(), function( index, component ) {

					var component_config = composite.api.get_component_configuration( component.component_id );

					composite_config[ component.component_id ] = component_config;
				} );

				return composite_config;
			},

			/**
			 * Get the component price.
			 *
			 * @param  string  component_id
			 * @return object | false
			 */
			get_component_totals: function( component_id ) {

				if ( false === composite.get_step_by( 'id', component_id ) ) {
					return false;
				}

				return composite.data_model.get( 'component_' + component_id + '_totals' );
			},

			/**
			 * Get the current stock status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_component_stock_status: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				return component.step_validation_model.get( 'is_in_stock' ) ? 'in-stock' : 'out-of-stock';
			},

			/**
			 * Get the current availability status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('in-stock' | 'out-of-stock')
			 */
			get_component_availability: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				var $availability = component.$component_summary_content.find( '.component_wrap .stock' );

				return $availability.length > 0 ? $availability.clone().wrap( '<div></div>' ).parent().html() : '';
			},

			/**
			 * Get the current validation status of a component.
			 *
			 * @param  string  component_id
			 * @return string ('pass' | 'fail')
			 */
			get_component_validation_status: function( component_id ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				return component.step_validation_model.get( 'passes_validation' ) ? 'pass' : 'fail';
			},

			/**
			 * Get the current validation messages of a component. Context: 'component' or 'composite'.
			 *
			 * @param  string  component_id
			 * @param  string  context
			 * @return array
			 */
			get_component_validation_messages: function( component_id, context ) {

				var component = composite.get_step_by( 'id', component_id );

				if ( false === component ) {
					return false;
				}

				var messages = context === 'composite' ? component.step_validation_model.get( 'composite_messages' ) : component.step_validation_model.get( 'component_messages' );

				return messages;
			},

			/**
			 * Gets configuration details for a single component.
			 *
			 * @param  string  component_id
			 * @return object | false
			 */
			get_component_configuration: function( component_id ) {

				var component        = composite.get_step_by( 'id', component_id ),
					component_config = false;

				if ( false === component ) {
					return component_config;
				}

				component_config = {
					title:           component.get_title(),
					selection_title: component.get_selected_product_title( false ),
					selection_meta:  component.get_selected_product_meta( false ),
					product_id:      component.get_selected_product( false ),
					variation_id:    component.get_selected_variation( false ),
					product_valid:   component.is_selected_product_valid(),
					variation_valid: component.is_selected_variation_valid(),
					quantity:        component.get_selected_quantity(),
					product_type:    component.get_selected_product_type()
				};

				// Pass through 'component_configuration' filter - @see WC_CP_Filters_Manager class.
				component_config = composite.filters.apply_filters( 'component_configuration', [ component_config, component ] );

				return component_config;
			},

			/**
			 * True if the composite is priced per product.
			 *
			 * @deprecated
			 *
			 * @return boolean
			 */
			is_priced_per_product: function() {
				composite.console_log( 'error', '\nMethod \'WC_CP_Composite::api::is_priced_per_product\' is deprecated since v3.7.0. Use \'WC_CP_Composite::api::is_component_priced_individually\' instead.' );
				return undefined;
			},

			/**
			 * True if the component is priced individually.
			 *
			 * @return boolean
			 */
			is_component_priced_individually: function( component_id ) {

				return composite.data_model.price_data[ 'is_priced_individually' ][ component_id ] === 'yes';
			}
		};

		/**
		 * Script initialization.
		 */
		this.init = function() {

			/*
			 * Trigger pre-init jQuery event that 3rd party code may use for initialization.
			 */
			composite.$composite_data.trigger( 'wc-composite-initializing', [ composite ] );

			/*
			 * Init composite on the 'initialize_composite' hook - callbacks declared inline since they are not meant to be unhooked.
			 * To extend/override model/view classes, modify them from action callbacks hooked in at an earlier priority than the 'init_models' and 'init_views' calls.
			 */
			this.actions

				/*
				 * Init steps.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_steps();
				}, 10, this )

				/*
				 * Init models.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_models();
				}, 20, this )

				/*
				 * Init actions dispatcher. Dispatches actions in response to key model events.
				 */
				.add_action( 'initialize_composite', function() {
					composite.actions.init();
				}, 30, this )

				/*
				 * Trigger resize to add responsive CSS classes to form.
				 */
				.add_action( 'initialize_composite', function() {
					composite.on_resize_handler();
				}, 40, this )

				/*
				 * Init views.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_views();
				}, 50, this )

				/*
				 * Init scenarios manager. Models are initialized, so we can now start listening to component model events.
				 */
				.add_action( 'initialize_composite', function() {
					composite.scenarios.init();
				}, 60, this )

				/*
				 * Validate steps.
				 */
				.add_action( 'initialize_composite', function() {
					composite.console_log( 'debug:events', '\nValidating Steps:' );
					composite.debug_tab_count = composite.debug_tab_count + 2;
					$.each( composite.get_steps(), function( index, step ) {
						step.validate();
					} );
					composite.debug_tab_count = composite.debug_tab_count - 2;
					composite.console_log( 'debug:events', '\nValidation complete.' );
				}, 70, this )

				/*
				 * Activate initial step.
				 */
				.add_action( 'initialize_composite', function() {
					composite.get_current_step().show_step();
				}, 80, this )

				/*
				 * Init Backbone router.
				 *
				 * Works with Paged & Progressive layout composites displayed in single-product pages.
				 * Browser history will not work with composites displayed in other places, for instance composites placed in WP pages via WC shortcodes.
				 */
				.add_action( 'initialize_composite', function() {
					composite.init_router();
				}, 90, this );


			/*
			 * Run init action.
			 */
			this.actions.do_action( 'initialize_composite' );

			/*
			 * Mark as initialized.
			 */
			composite.is_initialized = true;

			/*
			 * Add post-init action hooks.
			 */
			this.actions

				/**
				 * Init data model state.
				 */
				.add_action( 'composite_initialized', function() {
					composite.data_model.init();
				}, 10, this )

				/*
				 * Finally, render all views.
				 */
				.add_action( 'composite_initialized', function() {
					composite.render_views();
				}, 20, this );

			/*
			 * Run post-init action.
			 */
			this.actions.do_action( 'composite_initialized' );
		};

		/**
		 * Init backbone router to support browser history when transitioning between steps.
		 */
		this.init_router = function() {

			var	current_step = composite.get_current_step(),
				WC_CP_Router = Backbone.Router.extend( {

					has_initial_route: false,
					is_initialized:    false,

					routes:    {
						':step_slug': 'show_step'
					},

					show_step: function( step_slug ) {

						var encoded_slug = encodeURIComponent( step_slug );
						var step         = composite.get_step_by( 'slug', encoded_slug );

						if ( step ) {

							if ( ! this.is_initialized ) {
								this.has_initial_route = true;
							}

							// If the requested step cannot be viewed, do not proceed: Show a notice and create a new history entry based on the current step.
							if ( step.is_locked() ) {
								window.alert( wc_composite_params.i18n_step_not_accessible.replace( /%s/g, step.get_title() ) );
								composite.router.navigate( composite.get_current_step().step_slug );
							// Otherwise, scroll the viewport to the top and show the requested step.
							} else {
								if ( this.is_initialized ) {
									wc_cp_scroll_viewport( composite.$composite_form, { timeout: 0, partial: false, duration: 0, queue: false } );
								}
								step.show_step();
							}
						}
					}

				} );


			if ( $wc_cp_body.hasClass( 'single-product' ) && $wc_cp_body.hasClass( 'postid-' + composite.composite_id ) ) {

				composite.router = new WC_CP_Router();

				// Start recording history and trigger the initial route.
				Backbone.history.start();

				// Set router as initialized.
				composite.router.is_initialized = true;

				// If no initial route exists, find the initial route as defined by the served markup and write it to the history without triggering it.
				if ( composite.settings.layout !== 'single' && false === composite.router.has_initial_route && ! window.location.hash ) {
					composite.router.navigate( current_step.step_slug, { trigger: false } );
				}
			}
		};

		/**
		 * Initialize composite step objects.
		 */
		this.init_steps = function() {

			composite.console_log( 'debug:events', '\nInitializing Steps...' );

			/*
			 * Prepare markup for "Review" step, if needed.
			 */
			if ( composite.settings.layout === 'paged' ) {

				// Componentized layout: replace the step-based process with a summary-based process.
				if ( composite.settings.layout_variation === 'componentized' ) {

					composite.$composite_form.find( '.multistep.active' ).removeClass( 'active' );
					composite.$composite_data.addClass( 'multistep active' );

					// No summary widget.
					composite.$composite_summary_widget.hide();

				// If the composite-add-to-cart.php template is added right after the component divs, it will be used as the final step of the step-based configuration process.
				} else if ( composite.$composite_data.prev().hasClass( 'multistep' ) ) {

					composite.$composite_data.addClass( 'multistep' );
					composite.$composite_data.hide();

					// If the composite was just added to the cart, make the review/summary step active.
					if ( composite.$composite_data.hasClass( 'composite_added_to_cart' ) ) {
						composite.$composite_form.find( '.multistep.active' ).removeClass( 'active' );
						composite.$composite_data.addClass( 'active' );
					}

				} else {
					composite.$composite_data.show();
					composite.$composite_data.find( '.component_title .step_index' ).hide();
				}

			} else if ( composite.settings.layout === 'progressive' ) {

				composite.$components.show();
				composite.$composite_data.show();

			} else if ( composite.settings.layout === 'single' ) {

				composite.$components.show();
				composite.$composite_data.show();
			}

			/*
			 * Initialize step objects.
			 */

			composite.$steps = composite.$composite_form.find( '.multistep' );

			composite.$composite_form.children( '.component, .multistep' ).each( function( index ) {

				var step = composite.step_factory.create_step( composite, $( this ), index );
				composite.steps[ index ] = step;

			} );

			composite.$composite_navigation.removeAttr( 'style' );
		};

		/**
		 * Ajax URL.
		 */
		this.get_ajax_url = function( action ) {

			return wc_composite_params.use_wc_ajax === 'yes' ? this.ajax_url.toString().replace( '%%endpoint%%', action ) : this.ajax_url;
		};

		/**
		 * Shows a step and updates the history as required.
		 */
		this.navigate_to_step = function( step ) {

			if ( typeof( step ) === 'object' && typeof( step.show_step ) === 'function' ) {
				step.show_step();

				if ( this.allow_history_updates() ) {
					this.router.navigate( step.step_slug );
				}
			}
		};

		/**
		 * True when updating browser history.
		 */
		this.allow_history_updates = function() {

			return ( false !== composite.router && 'yes' === this.settings.update_browser_history && composite.is_initialized );
		};

		/**
		 * Shows the step marked as previous from the current one.
		 */
		this.show_previous_step = function() {

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_previous() ) {
					composite.navigate_to_step( step );
					return false;
				}
			} );
		};

		/**
		 * Shows the step marked as next from the current one.
		 */
		this.show_next_step = function() {

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_next() ) {
					composite.navigate_to_step( step );
					return false;
				}
			} );
		};

		/**
		 * Returns step objects.
		 */
		this.get_steps = function() {

			return this.steps;
		};

		/**
		 * Returns step objects that are components.
		 */
		this.get_components = function() {

			var components = [];

			$.each( this.steps, function( step_index, step ) {

				if ( step.is_component() ) {
					components.push( step );
				}

			} );

			return components;
		};

		/**
		 * Returns a step object by id.
		 */
		this.get_step = function( step_id ) {

			var found = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.step_id == step_id ) {
					found = step;
					return false;
				}

			} );

			return found;
		};

		/**
		 * Returns a step object by id/index.
		 */
		this.get_step_by = function( by, id ) {

			var found = false;

			if ( by !== 'id' && by !== 'index' && by !== 'slug' ) {
				return false;
			}

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( ( by === 'id' && String( step.step_id ) === String( id ) ) || ( by === 'index' && String( step_index ) === String( id ) ) || ( by === 'slug' && String( step.step_slug ).toUpperCase() === String( id ).toUpperCase() ) ) {
					found = step;
					return false;
				}

			} );

			return found;

		};

		/**
		 * Returns the current step object.
		 */
		this.get_current_step = function() {

			var current = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_current() ) {
					current = step;
					return false;
				}

			} );

			return current;
		};

		/**
		 * Current step setter.
		 */
		this.set_current_step = function( step ) {

			var style           = this.settings.layout,
				style_variation = this.settings.layout_variation,
				curr_step_pre   = this.get_current_step(),
				next_step_pre   = this.get_next_step(),
				prev_step_pre   = this.get_previous_step(),
				next_step       = false,
				prev_step       = false;

			if ( style === 'paged' && style_variation === 'componentized' ) {
				next_step = prev_step = this.get_step_by( 'id', 'review' );
			} else {
				$.each( this.get_steps(), function( index, search_step ) {
					if ( false === next_step && search_step.step_index > step.step_index ) {
						if ( search_step.is_visible() ) {
							next_step = search_step;
						}
					}
					if ( search_step.step_index < step.step_index ) {
						if ( search_step.is_visible() ) {
							prev_step = search_step;
						}
					}
				} );
			}

			curr_step_pre._is_current = false;
			step._is_current          = true;

			curr_step_pre.$el.removeClass( 'active' );
			step.$el.addClass( 'active' );

			if ( false !== next_step_pre ) {
				next_step_pre._is_next = false;
				next_step_pre.$el.removeClass( 'next' );
			}

			if ( false !== next_step ) {
				next_step._is_next = true;
				next_step.$el.addClass( 'next' );
			}

			if ( false !== prev_step_pre ) {
				prev_step_pre._is_previous = false;
				prev_step_pre.$el.removeClass( 'prev' );
			}

			if ( false !== prev_step ) {
				prev_step._is_previous = true;
				prev_step.$el.addClass( 'prev' );
			}
		};

		/**
		 * Returns the previous step object.
		 */
		this.get_previous_step = function() {

			var previous = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_previous() ) {
					previous = step;
					return false;
				}

			} );

			return previous;
		};

		/**
		 * Returns the next step object.
		 */
		this.get_next_step = function() {

			var next = false;

			$.each( composite.get_steps(), function( step_index, step ) {

				if ( step.is_next() ) {
					next = step;
					return false;
				}

			} );

			return next;
		};

		/**
		 * Handler for viewport resizing.
		 */
		this.on_resize_handler = function() {

			// Add responsive classes to composite form.

			var form_width = composite.$composite_form.width();

			if ( form_width <= wc_composite_params.small_width_threshold ) {
				composite.$composite_form.addClass( 'small_width' );
			} else {
				composite.$composite_form.removeClass( 'small_width' );
			}

			if ( form_width > wc_composite_params.full_width_threshold ) {
				composite.$composite_form.addClass( 'full_width' );
			} else {
				composite.$composite_form.removeClass( 'full_width' );
			}

			if ( wc_composite_params.legacy_width_threshold ) {
				if ( form_width <= wc_composite_params.legacy_width_threshold ) {
					composite.$composite_form.addClass( 'legacy_width' );
				} else {
					composite.$composite_form.removeClass( 'legacy_width' );
				}
			}

			// Reset relocated container if in wrong position.

			if ( composite.is_initialized ) {
				$.each( composite.get_components(), function( index, component ) {

					if ( component.component_selection_view.is_relocated() ) {

						var relocation_params = component.component_selection_view.get_content_relocation_params();

						if ( relocation_params.relocate ) {

							var $relocation_target    = component.$component_options.find( '.component_option_content_container' ),
								$relocation_reference = relocation_params.reference;

							$relocation_reference.after( $relocation_target );
						}
					}
				} );
			}
		};

		/**
		 * Log stuff in the console.
		 */
		this.console_log = function( context, message ) {

			if ( window.console && typeof( message ) !== 'undefined' ) {
				var log = false;

				if ( context === 'error' ) {
					log = true;
				} else if ( _.contains( wc_composite_params.script_debug_level, context ) ) {
					log = true;
				} else {
					$.each( wc_composite_params.script_debug_level, function( index, debug_level_context ) {
						if ( context.indexOf( debug_level_context ) > -1 ) {
							log = true;
							return false;
						}
					} );
				}

				if ( log ) {
					var tabs = '';
					for ( var i = composite.debug_tab_count; i > 0; i-- ) {
						tabs = tabs + '	';
					}
					if ( typeof( message.substring ) === 'function' && message.substring( 0, 1 ) === '\n' ) {
						message = message.replace( '\n', '\n' + tabs );
					} else {
						message = tabs + message;
					}

					window.console.log( message );
				}
			}
		};

		/**
		 * Creates all necessary composite- and step/component-level models.
		 */
		this.init_models = function() {

			/*
		 	 * Step models associated with the validation status and access permission status of a step.
		 	 */
			$.each( composite.get_steps(), function( step_index, step ) {
				step.step_validation_model = new composite.model_classes.Step_Validation_Model( step );
				step.step_visibility_model = new composite.model_classes.Step_Visibility_Model( step );
				step.step_access_model     = new composite.model_classes.Step_Access_Model( step );
			} );

			/*
		 	 * Component models associated with component options and component selections.
		 	 */
			$.each( composite.get_components(), function( index, component ) {
				component.component_options_model    = new composite.model_classes.Component_Options_Model( component );
				component.component_selection_model  = new composite.model_classes.Component_Selection_Model( component );
			} );

			/*
		 	 * Composite product data model for storing validation, pricing, availability and quantity data.
		 	 */
			composite.data_model = new composite.model_classes.Composite_Data_Model();
		};

		/**
		 * Creates:
		 *
		 *  - Composite product views responsible for updating the composite availability, pricing and add-to-cart button located in: i) the composite form and ii) summary widgets.
		 *  - Composite product views responsible for updateing the navigation, pagination and summary elements.
		 *  - All necessary step & component views associated with the display of validation messages, component selection details and component options.
		 */
		this.init_views = function() {

			composite.console_log( 'debug:events', '\nInitializing Views...' );

			/*
			 * Instantiate composite views.
			 */
			this.composite_validation_view = new composite.view_classes.Composite_Validation_View( {
				is_in_widget: false,
				el:           composite.$composite_message,
				model:        composite.data_model,
			} );

			this.composite_price_view = new composite.view_classes.Composite_Price_View( {
				is_in_widget: false,
				el:           composite.$composite_price,
				model:        composite.data_model,
			} );

			this.composite_availability_view = new composite.view_classes.Composite_Availability_View( {
				is_in_widget: false,
				el:           composite.$composite_availability,
				model:        composite.data_model,
			} );

			this.composite_add_to_cart_button_view = new composite.view_classes.Composite_Add_To_Cart_Button_View( {
				is_in_widget: false,
				el:           composite.$composite_button,
				$el_button:   composite.$composite_add_to_cart_button,
				model:        composite.data_model,
			} );

			this.composite_status_view = new composite.view_classes.Composite_Status_View( {
				el:           composite.$composite_status,
				$el_content:  composite.$composite_status.find( '.wrapper' ),
				model:        composite.data_model,
			} );

			if ( composite.$composite_pagination.length > 0 ) {
				composite.composite_pagination_view = new composite.view_classes.Composite_Pagination_View( { el: composite.$composite_pagination } );
			}

			if ( composite.$composite_summary.length > 0 ) {
				composite.composite_summary_view = new composite.view_classes.Composite_Summary_View( { is_in_widget: false, el: composite.$composite_summary } );
			}

			if ( composite.$composite_navigation.length > 0 ) {
				composite.composite_navigation_view = new composite.view_classes.Composite_Navigation_View( { el: composite.$composite_navigation } );
			}

			if ( composite.$composite_summary_widget.length > 0 ) {
				composite.$composite_summary_widget.each( function( index, $widget ) {
					composite.composite_summary_widget_views.push( new composite.view_classes.Composite_Widget_View( { widget_count: index + 1, el: $widget } ) );
				} );
			}

			/*
			 * Initialize step/component views.
			 */
			$.each( composite.get_steps(), function( step_index, step ) {
				step.validation_view = new composite.view_classes.Step_Validation_View( step, { el: step.$component_message, model: step.step_validation_model } );
				step.step_title_view = new composite.view_classes.Step_Title_View( step, { el: step.$step_title } );
			} );

			$.each( composite.get_components(), function( index, component ) {
				component.component_options_view    = new composite.view_classes.Component_Options_View( component, { el: component.$component_options, model: component.component_options_model } );
				component.component_pagination_view = new composite.view_classes.Component_Pagination_View( component, { el: component.$component_pagination, model: component.component_options_model } );
				component.component_selection_view  = new composite.view_classes.Component_Selection_View( component, { el: component.$component_content, model: component.component_selection_model } );
			} );

			/*
			 * Initialize component selection view scripts.
			 */
			$.each( composite.get_components(), function( index, component ) {
				component.component_selection_view.init_dependencies();
			} );
		};

		/**
		 * Renders component options views and the composite pagination, navigation and summary template views.
		 */
		this.render_views = function() {

			composite.console_log( 'debug:views', '\nRendering Views...' );
			composite.debug_tab_count = composite.debug_tab_count + 2;

			$.each( composite.get_components(), function( index, component ) {
				component.component_selection_view.update_selection_title();
				component.component_options_view.render();
				component.component_pagination_view.render();
			} );

			$.each( composite.get_steps(), function( index, step ) {
				step.step_title_view.render_navigation_state();
				step.step_title_view.render_index();
			} );

			if ( false !== composite.composite_pagination_view ) {
				composite.composite_pagination_view.render();
			}
			if ( false !== composite.composite_summary_view ) {
				composite.composite_summary_view.render();
			}
			if ( false !== composite.composite_navigation_view ) {
				composite.composite_navigation_view.render( 'transition' );
			}

			$.each( composite.composite_summary_widget_views, function( index, view ) {
				view.composite_summary_view.render();
			} );

			composite.debug_tab_count = composite.debug_tab_count - 2;
			composite.console_log( 'debug:views', '\nRendering complete.' );

			/*
			 * Get rid of no-js notice and classes.
			 */
			composite.$composite_form.removeClass( 'cp-no-js' );
			composite.$composite_form.find( '.cp-no-js-msg' ).remove();
		};

		/**
		 * Blocks the composite form and adds a waiting ui cue in the passed elements.
		 */
		this.block = function( $waiting_for ) {

			this.$composite_form.block( wc_cp_block_params );
			$waiting_for.block( wc_cp_block_wait_params );
			composite.has_transition_lock = true;
		};

		/**
		 * Unblocks the composite form and removes the waiting ui cue from the passed elements.
		 */
		this.unblock = function( $waiting_for ) {

			this.$composite_form.unblock();
			$waiting_for.unblock();
			composite.has_transition_lock = false;
		};
	}

	/*
	 * Load classes from external files to keep things tidy.
	 */

	include( 'models.js' );

	include( 'views.js' );

	include( 'actions_dispatcher.js' );

	include( 'filters_manager.js' );

	include( 'scenarios_manager.js' );

	include( 'step_factory.js' );

	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	$wc_cp_document.ready( function() {

		$wc_cp_body = $( document.body );

		/**
		 * QuickView compatibility.
		 */
		$wc_cp_body.on( 'quick-view-displayed', function() {

			$( '.composite_form .composite_data' ).each( function() {
				$( this ).wc_composite_form();
			} );
		} );

		/**
		 * Responsive form CSS (we can't rely on media queries since we must work with the .composite_form width, not screen width).
		 */
		$wc_cp_window.resize( function() {

			$.each( wc_cp_composite_scripts, function( container_id, composite ) {

				clearTimeout( composite.timers.on_resize_timer );

				composite.timers.on_resize_timer = setTimeout( function() {
					composite.on_resize_handler();
				}, 50 );
			} );
		} );

		/**
	 	 * Composite app initialization on '.composite_data' jQuery objects.
	 	 */
		$.fn.wc_composite_form = function() {

			if ( ! $( this ).hasClass( 'composite_data' ) ) {
				return true;
			}

			var composite_id    = $( this ).data( 'container_id' ),
				$composite_form = $( this ).closest( '.composite_form' );

			if ( typeof( wc_cp_composite_scripts[ composite_id ] ) !== 'undefined' ) {
				$composite_form.find( '*' ).off();
			}

			wc_cp_composite_scripts[ composite_id ] = new WC_CP_Composite( { $composite_form: $composite_form, $composite_data: $( this ) } );

			$composite_form.data( 'script_id', composite_id );

			wc_cp_composite_scripts[ composite_id ].init();
		};

		/*
		 * Initialize form script.
		 */
		$( '.composite_form .composite_data' ).each( function() {
			$( this ).wc_composite_form();
		} );

	} );

} ) ( jQuery, Backbone );
