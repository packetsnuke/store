(function ( $ ) {
	"use strict";

	var global_variation_object = false;

	$.tmEPOAdmin = {

		add_events: function () {
			// Export button
			$( document ).on( "click.cpf", "#builder_export", function ( e ) {
				e.preventDefault();
				var $this = $( this );
				if ( $this.data( 'doing_export' ) ) {
					return;
				}
				$this.data( 'doing_export', 1 ).prepend( '<i class="tm-icon tcfa tcfa-refresh tcfa-spin"></i>' );
				var tm_meta, data;//,frame;
				// disable variations customization.
				//$(".tma-variations-wrap :input").prop("disabled", true);
				tm_meta = $.tmEPOAdmin.prepare_for_json( $( "#post" ).tm_serializeObject() );
				tm_meta = $.toJSON( tm_meta );
				// enable variations customization.
				//$(".tma-variations-wrap :input").removeProp("disabled");
				data = {
					action: 'tm_export',
					metaserialized: tm_meta,
					security: tm_epo_admin.export_nonce
				};

				$.post( tm_epo_admin.ajax_url, data, function ( response ) {
					if ( response && response.result && response.result != '' ) {
						window.location = response.result;
					} else {
						if ( response && response.error && response.message ) {
							var $_html = $.tmEPOAdmin.builder_floatbox_template_import( {
								"id": "temp_for_floatbox_insert",
								"html": '<div class="tm-inner">' + response.message + '</div>',
								"title": tm_epo_admin.i18n_error_title
							} );
							var temp_floatbox = $( "body" ).tm_floatbox( {
								"fps": 1,
								"ismodal": true,
								"refresh": "fixed",
								"width": "50%",
								"height": "300px",
								"classname": "flasho tm_wrapper tm-error",
								"data": $_html
							} );
							$( ".details_cancel" ).click( function () {
								if ( temp_floatbox ) temp_floatbox.cancelfunc();
							} );
						}
					}
				}, 'json' )
					.always( function ( response ) {
						$this.data( 'doing_export', 0 ).find( '.tm-icon' ).remove();
					} );
			} );

			// Import button
			$( '#builder_import_file' ).fileupload( {
				dataType: 'json',
				global: false,
				url: tm_epo_admin.import_url,
				dropZone: null,

				add: function ( e, data ) {
					var $_html = $.tmEPOAdmin.builder_floatbox_template_import( {
						"id": "temp_for_floatbox_insert",
						"html": "",
						"title": tm_epo_admin.i18n_import_title
					} );

					data._to = $( "body" ).tm_floatbox( {
						"fps": 1,
						"ismodal": true,
						"refresh": "fixed",
						"width": "50%",
						"height": "300px",
						"classname": "flasho tm_wrapper",
						"data": $_html
					} );
					var $progress = $( '<div class="tm_progress_bar tm_orange"><span class="tm_percent"></span></div><div class="tm_progress_info"><span class="tm_info"></span></div>' );


					var $selection = $( '<div class="override-selection"><button type="button" class="tc tc-button details_override">' + tm_epo_admin.i18n_overwrite_existing_elements + '</button><button type="button" class="tc tc-button details_append">' + tm_epo_admin.i18n_append_new_elements + '</button></div>' );
					$selection.appendTo( "#temp_for_floatbox_insert" );

					$( ".details_cancel" ).click( function () {
						data.abort();
						if ( data._to ) data._to.cancelfunc();
					} );

					if ( data.autoUpload || (data.autoUpload !== false && $( this ).fileupload( 'option', 'autoUpload' )) ) {
						data.process().done( function () {

							$( ".details_override" ).click( function () {
								$( "#temp_for_floatbox_insert" ).find( '.override-selection' ).remove();
								$progress.appendTo( "#temp_for_floatbox_insert" );
								$( '.tm_info' ).html( tm_epo_admin.i18n_importing );
								data.formData = { 'action': 'import', 'import_override': 1 };
								data.submit();
								$( '.flasho' ).addClass( 'tm_color_orange' );
							} );

							$( ".details_append" ).click( function () {
								$( "#temp_for_floatbox_insert" ).find( '.override-selection' ).remove();
								$progress.appendTo( "#temp_for_floatbox_insert" );
								$( '.tm_info' ).html( tm_epo_admin.i18n_importing );
								data.formData = { 'action': 'import', 'import_override': 0 };
								data.submit();
								$( '.flasho' ).addClass( 'tm_color_orange' );
							} );

						} );
					}

				},
				done: function ( e, data ) {
					if ( data.result && data.result.message ) {
						$( '.tm_info' ).html( data.result.message );
					}
					if ( data.result && data.result.result == 1 ) {
						$( '.tm_progress_bar' ).removeClass( 'tm_orange' ).addClass( 'tm_turquoise' );
						$( '.tm_info' ).removeClass( 'tm_color_pomegranate' ).addClass( 'tm_color_turquoise' );
						$( '.flasho' ).removeClass( 'tm_color_orange tm_color_pomegranate' ).addClass( 'tm_color_turquoise' );
						$( ".details_cancel" ).remove();
						$( '.tm_info' ).html( tm_epo_admin.i18n_saving );
						$( window ).off( 'beforeunload.edit-post' );
						$( "#post" ).submit();
					} else {
						$( '.tm_progress_bar' ).removeClass( 'tm_orange' ).addClass( 'tm_pomegranate' );
						$( '.tm_info' ).removeClass( 'tm_color_turquoise' ).addClass( 'tm_color_pomegranate' );
						$( '.flasho' ).removeClass( 'tm_color_orange tm_color_turquoise' ).addClass( 'tm_color_pomegranate' );
					}
				},
				fail: function ( e, data ) {
					if ( data.result && data.result.message ) {
						$( '.tm_info' ).removeClass( 'tm_color_turquoise' ).addClass( 'tm_color_pomegranate' ).html( data.result.message );
					}
					$( '.tm_progress_bar' ).removeClass( 'tm_orange' ).addClass( 'tm_pomegranate' );
					$( '.flasho' ).removeClass( 'tm_color_orange tm_color_turquoise' ).addClass( 'tm_color_pomegranate' );
				},
				progressall: function ( e, data ) {
					var progress = parseInt( data.loaded / data.total * 100, 10 );
					$( '.tm_progress_bar' ).css( 'width', progress + '%' );
					$( '.tm_percent' ).html( progress + '%' );
				},
				always: function ( e, data ) {
					$( "body" ).removeClass( "overflow" );
				}
			} );
			$( document ).on( "click.cpf", "#builder_import,.tc-add-import-csv", function ( e ) {
				e.preventDefault();
				$( '#builder_import_file' ).click();
			} );

			// Fullsize button
			$( document ).on( "click.cpf", "#builder_fullsize", function ( e ) {
				e.preventDefault();
				$( "body" ).addClass( "overflow" );
				var _fullsize = $( ".tm_mode_builder" );
				if ( ! _fullsize.length ) {
					_fullsize = $( '.builder_selector' ).closest( '.postbox' );
				}
				_fullsize.css( {
					opacity: 0
				} ).addClass( "fullsize" );
				$( '<div class="fl-overlay forfullsize"></div>' ).css( {
					zIndex: 10000000,
					opacity: 1,
					height: 0
				} ).appendTo( "body" ).animate( {
					opacity: 1,
					height: "100%"
				}, 500, "easeOutExpo", function () {
					_fullsize.css( {
						opacity: 1
					} );
					$( ".forfullsize" ).animate( {
						opacity: 0
					}, 300, "easeInExpo", function () {
						$( ".forfullsize" ).remove();
					} );
				} );
			} );

			// Close Fullsize button
			$( document ).on( "click.cpf", "#builder_fullsize_close", function ( e ) {
				e.preventDefault();
				var _fullsize = $( ".tm_mode_builder" );
				if ( ! _fullsize.length ) {
					_fullsize = $( '.builder_selector' ).closest( '.postbox' );
				}
				$( '<div class="fl-overlay forfullsize"></div>' ).css( {
					zIndex: 10000000,
					opacity: 1,
					height: 0
				} ).appendTo( "body" ).animate( {
					opacity: 1,
					height: "100%"
				}, 500, "easeOutExpo", function () {
					_fullsize.removeClass( "fullsize" );
					$( "body" ).removeClass( "overflow" );
					$( ".forfullsize" ).animate( {
						opacity: 0
					}, 300, "easeInExpo", function () {
						$( ".forfullsize" ).remove();
					} );
				} );
			} );

			// Add Element button
			$( document ).on( "click.cpf", ".builder-add-element", $.tmEPOAdmin.builder_add_element_onClick );
			// Section add button
			$( document ).on( "click.cpf", ".builder_add_section,.tc-add-section ", $.tmEPOAdmin.builder_add_section_onClick );
			$( document ).on( "click.cpf", ".builder-add-section-and-element,.tc-add-element", $.tmEPOAdmin.builder_add_section_and_element_onClick );
			// Variation button
			$( document ).on( "click.cpf", ".builder_add_variation", $.tmEPOAdmin.builder_add_variation_onClick );

			// Section edit button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .edit", $.tmEPOAdmin.builder_section_item_onClick );
			// Section clone button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .clone", $.tmEPOAdmin.builder_section_clone_onClick );
			// Section plus button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .plus", $.tmEPOAdmin.builder_section_plus_onClick );
			// Section minus button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .minus", $.tmEPOAdmin.builder_section_minus_onClick );
			// Section delete button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .delete", $.tmEPOAdmin.builder_section_delete_onClick );
			// Section fold button
			$( document ).on( "click.cpf", ".builder_wrapper .btitle .fold", $.tmEPOAdmin.builder_section_fold_onClick );

			// Element edit button
			$( document ).on( "click.cpf", ".bitem .edit", $.tmEPOAdmin.builder_item_onClick );
			// Element clone button
			$( document ).on( "click.cpf", ".bitem .clone", $.tmEPOAdmin.builder_clone_onClick );
			// Element plus button
			$( document ).on( "click.cpf", ".bitem .plus", $.tmEPOAdmin.builder_plus_onClick );
			// Element minus button
			$( document ).on( "click.cpf", ".bitem .minus", $.tmEPOAdmin.builder_minus_onClick );
			// Element delete button
			$( document ).on( "click", ".bitem .delete", $.tmEPOAdmin.builder_delete_onClick );

			// Add options button
			$( document ).on( "click.cpf", ".builder-panel-add", $.tmEPOAdmin.builder_panel_add_onClick );
			// Mass add options button
			$( document ).on( "click.cpf", ".builder-panel-mass-add", $.tmEPOAdmin.builder_panel_mass_add_onClick );
			// Populate options button
			$( document ).on( "click.cpf", ".builder-panel-populate", $.tmEPOAdmin.builder_panel_populate_onClick );
			// Delete options button
			$( document ).on( "click.cpf", ".builder_panel_delete", $.tmEPOAdmin.builder_panel_delete_onClick );
			$( ".builder_panel_delete" ).on( "click.cpf", $.tmEPOAdmin.builder_panel_delete_onClick ); //sortable bug
			$( document ).on( "click.cpf", ".builder_panel_delete_all", $.tmEPOAdmin.builder_panel_delete_all_onClick );
			$( document ).on( "click.cpf", ".builder_panel_up", function () {
				var t = $( this ),
					options_wrap = t.closest( ".options_wrap" ),
					prev = options_wrap.prev();

				prev.before( options_wrap );
				$.tmEPOAdmin.panels_reorder( t.closest( ".panels_wrap" ) );
				$.tmEPOAdmin.paginattion_init( "current" );
			} );
			$( document ).on( "click.cpf", ".builder_panel_down", function () {
				var t = $( this ),
					options_wrap = t.closest( ".options_wrap" ),
					next = options_wrap.next();

				next.after( options_wrap );
				$.tmEPOAdmin.panels_reorder( t.closest( ".panels_wrap" ) );
				$.tmEPOAdmin.paginattion_init( "current" );
			} );

			// Auto generate option value
			$( document ).on( "keyup.cpf change.cpf", ".tm_option_title", function () {
				$( this ).closest( '.options_wrap' ).find( '.tm_option_value' ).val( $( this ).val() );
			} );
			// Upload button
			$( document ).on( "click.cpf", ".tm_upload_button", $.tmEPOAdmin.upload );
			$( document ).on( "change.cpf", ".use_images,.tm-use-lightbox,.use_colors", $.tmEPOAdmin.tm_upload );
			$( document ).on( "change.cpf", ".use_url", $.tmEPOAdmin.tm_url );

			$( document ).on( "change.cpf", ".tm-qty-selector", $.tmEPOAdmin.tm_qty_selector );
			$( document ).on( "change.cpf", ".tm-pricetype-selector", $.tmEPOAdmin.tm_pricetype_selector );

			$( document ).on( "change.cpf", ".variations-display-as", $.tmEPOAdmin.variations_display_as );
			$( document ).on( "change.cpf", ".tm-attribute .tm-changes-product-image", $.tmEPOAdmin.variations_display_as );
			$( document ).on( "click.cpf", ".tm-upload-button-remove", $.tmEPOAdmin.tm_upload_button_remove_onClick );

			$( document ).on( "change.cpf", ".tm-weekday-picker", $.tmEPOAdmin.tm_weekday_picker );

			$( document ).on( "click.cpf", ".tm-tags-container .tab-header", function () {

				var $this = $( this ),
					tm_tags_container = $this.closest( '.tm-tags-container' ),
					tm_elements_container = tm_tags_container.find( '.tm-elements-container' ),
					elements = tm_elements_container.find( "li.tm-element-button" ),
					headers = tm_tags_container.find( '.tab-header' ),
					tag_to_show = $this.attr( "data-tm-tag" );

				headers.removeClass( "open" ).addClass( "closed" );
				$this.removeClass( "closed" ).addClass( "open" );

				if ( tag_to_show == "all" ) {
					elements.removeClass( "tm-hidden" );
				} else {
					elements.addClass( "tm-hidden" );
					elements.filter( "." + tag_to_show ).removeClass( "tm-hidden" );
				}

			} );

			// popup editor identification
			$( document ).on( "click.cpf", ".tm_editor_wrap", function () {
				var t = $( this ).find( 'textarea' );
				if ( t.attr( 'id' ) ) {
					window.wpActiveEditor = t.attr( 'id' );
				}
			} );

			$( document ).on( "change.cpf", ".cpf-logic-element", $.tmEPOAdmin.cpf_logic_element_onchange );
			$( document ).on( "change.cpf", ".cpf-logic-operator", $.tmEPOAdmin.cpf_logic_operator_onchange );


			$( document ).on( "change.cpf", ".activate-sections-logic, .activate-element-logic", function () {
				var value = parseInt( $( this ).val() );
				if ( value == 1 ) {
					$( this ).parent().find( ".builder-logic-div" ).show();
				} else {
					$( this ).parent().find( ".builder-logic-div" ).hide();
				}
			} );
			$( document ).on( "dblclick.cpf", ".tm-default-radio", function () {
				$( this ).removeAttr( "checked" ).prop( "checked", false );
			} );

			$( document ).on( "click", ".tm-element-label,.tm-internal-label", function () {
				var t = $( this );
				$.tmEPOAdmin.current_edit_label = t;
				t.addClass( 'tm-hidden' ).closest( ".tm-label-desc" ).addClass( 'tm-hidden' ).next( ".tm-label-desc-edit" ).removeClass( 'tm-hidden' ).find( ".tm-internal-name" ).focus();
			} );
			$( document ).mouseup( function ( e ) {
				if ( ! $.tmEPOAdmin.current_edit_label ) {
					return;
				}
				var container = $.tmEPOAdmin.current_edit_label;

				if ( ! container.is( e.target ) // if the target of the click isn't the container...
					&& container.has( e.target ).length === 0 // ... nor a descendant of the container
					&& ! $( e.target ).is( ".tm-internal-name" )
				) {
					var input = container.closest( ".tm-label-desc" ).next( ".tm-label-desc-edit" ).find( ".tm-internal-name" );
					input.change();

					if ( container.is( ".tm-internal-label" ) ) {
						container.html( input.val() ).removeClass( 'tm-hidden' );
					}
					if ( container.is( ".tm-element-label" ) ) {
						container.removeClass( 'tm-hidden' ).closest( ".tm-label-desc" ).find( ".tm-internal-label" ).html( input.val() );
					}

					container.closest( ".tm-label-desc" ).removeClass( 'tm-hidden' ).next( ".tm-label-desc-edit" ).addClass( 'tm-hidden' );

					if ( input.val() === '' ) {
						container.closest( ".tm-label-desc" ).removeClass( 'tc-has-value' ).addClass( 'tc-empty-value' );
					} else {
						container.closest( ".tm-label-desc" ).removeClass( 'tc-empty-value' ).addClass( 'tc-has-value' );
					}
					$.tmEPOAdmin.current_edit_label = false;
				}
			} );

			$( document ).on( "change.cpf", ".multiple_radiobuttons_options", function () {
				var panels_wrap = $( this ).closest( '.panels_wrap' );

				if ( $( this ).val() == "fee" ) {
					panels_wrap.find( '.multiple_radiobuttons_options' ).val( 'fee' );
				} else {
					panels_wrap.find( ".multiple_radiobuttons_options" ).filter( function () {
						return this.value == 'fee'
					} ).val( $( this ).val() );
				}
			} );

			$( document ).on( "change.cpf", ".tm_select_price_type", function () {
				var panels_wrap = $( this ).closest( '.builder_element_wrap' ).find(".panels_wrap");console.log(panels_wrap);

				if ( $( this ).val() == "fee" ) {
					panels_wrap.find( '.multiple_selectbox_options' ).children('option[value="percentcurrenttotal"]').hide();
					panels_wrap.find( ".multiple_selectbox_options" ).filter( function () {
						return this.value == 'percentcurrenttotal'
					} ).val( "" );
				} else {
					panels_wrap.find( '.multiple_selectbox_options' ).children('option[value="percentcurrenttotal"]').show();					
				}
			} );


			$( document ).on( "click.cpf", ".cpf-add-rule", $.tmEPOAdmin.cpf_add_rule );
			$( document ).on( "click.cpf", ".cpf-delete-rule", $.tmEPOAdmin.cpf_delete_rule );

			// General fold button
			$( document ).on( "click.cpf", ".tma-handle-wrap .tma-handle", $.tmEPOAdmin.builder_fold_onClick );

			$( document ).on( 'keyup change', '#temp_for_floatbox_insert .n[type=text]', function () {
				var value = $( this ).val(),
					regex = new RegExp( "[^\-0-9\%.\\" + woocommerce_admin.mon_decimal_point + "]+", "gi" ),
					newvalue = value.replace( regex, '' );

				if ( value !== newvalue ) {
					$( this ).val( newvalue );
					if ( $( this ).parent().find( '.wc_error_tip' ).length == 0 ) {
						var offset = $( this ).position();
						$( this ).after( '<div class="wc_error_tip">' + woocommerce_admin.i18n_mon_decimal_error + '</div>' );
						$( '.wc_error_tip' )
							.css( 'left', offset.left + $( this ).width() - ( $( this ).width() / 2 ) - ( $( '.wc_error_tip' ).width() / 2 ) )
							.css( 'top', offset.top + $( this ).height() )
							.fadeIn( '100' );
					}
				}
				return this;
			} );

			$( document ).on( 'click', '.builder_elements .tc-handle', function () {
				var $this = $( this );

				var handle_wrapper = $this.closest( ".builder_elements" );
				if ( ! $this.data( "folded" ) && $this.data( "folded" ) != undefined ) {
					$this.data( "folded", true );
					$this.removeClass( "tcfa-caret-down" ).addClass( "tcfa-caret-up" );
					handle_wrapper.addClass( 'closed' );
				} else {
					$this.data( "folded", false );
					$this.removeClass( "tcfa-caret-up" ).addClass( "tcfa-caret-down" );
					handle_wrapper.removeClass( 'closed' );
				}

				$.tmEPOAdmin.fix_content_float();
			} );

			$( document ).on( 'click', '.tc-enable-responsive', function () {
				var $this = $( this ),
					on = $this.find( ".on" ),
					off = $this.find( ".off" ),
					divs = $( "#temp_for_floatbox_insert" ).find( ".builder_responsive_div" );

				if ( $this.is( '.active' ) ) {
					$this.removeClass( 'active' );
					on.addClass( 'tm-hidden' );
					;
					off.removeClass( 'tm-hidden' );
					divs.hide();
				} else {
					$this.addClass( 'active' );
					on.removeClass( 'tm-hidden' );
					off.addClass( 'tm-hidden' );
					;
					divs.show();
				}
			} );


			$( document ).on( 'change.cpf', '.tma-variations-section .sections_style', function () {
				var $this = $( this ), v = $this.val();
				if ( v == 'collapse' || v == 'accordion' || v == 'collapseclosed' ) {
					$( '#temp_for_floatbox_insert .tma-tab-title' ).show();
					$( '#temp_for_floatbox_insert .tm-tab' ).find( '.builder_hide_for_variation' ).show();
				} else {
					$( '#temp_for_floatbox_insert .tma-tab-title' ).hide();
					$( '#temp_for_floatbox_insert .tm-tab' ).find( '.builder_hide_for_variation' ).hide();
				}
				$( '#temp_for_floatbox_insert' ).find( '.builder_hide_for_variations' ).hide();
			} );

			if ( $().ajaxChosen ) {
				$( "select.ajax_chosen_select_tm_product_ids" ).ajaxChosen( {
					method: 'GET',
					url: tm_epo_admin.ajax_url,
					dataType: 'json',
					afterTypeDelay: 100,
					data: {
						action: 'woocommerce_json_search_products',
						security: tm_epo_admin.search_products_nonce
					}
				}, function ( data ) {

					var terms = {};

					$.each( data, function ( i, val ) {
						terms[ i ] = val;
					} );

					return terms;
				} );
			}

			$( "body" ).on( "woocommerce-product-type-change", function () {
				$.tmEPOAdmin.init_sections_check();
				$.tmEPOAdmin.fix_content_float();

				var product_type = $( "#product-type" );
				if ( ! product_type.length ) {
					return;
				} else {
					if ( product_type.val() == "variable" || product_type.val() == "variable-subscription" ) {

					} else {
						var variation_element = $( ".builder_layout .element-variations" );
						if ( variation_element.length ) {
							variation_element.closest( ".builder_wrapper" ).remove();
							$.tmEPOAdmin.builder_reorder_multiple();
							$( ".builder_layout .builder_wrapper" ).each( function ( i, el ) {
								$.tmEPOAdmin.logic_init( $( el ) );
							} );
							$.tmEPOAdmin.init_sections_check();
							$.tmEPOAdmin.fix_content_float();

							$.tmEPOAdmin.toggle_variation_button();
							$.tmEPOAdmin.var_remove( "tm-style-variation-added" );
						}
					}
					$.tmEPOAdmin.toggle_variation_button();
				}
			} );

		},
		initialitize: function () {
			$.tmEPOAdmin.set_global_variation_object( "initialitize_on" );
		},
		initialitize_on: function () {
			$.tmEPOAdmin.isinit = true;
			$.tmEPOAdmin.pre_element_logic_init_obj = {};
			$.tmEPOAdmin.pre_element_logic_init_obj_options = {};

			$.tmEPOAdmin.pre_element_logic_init( true );
			$.tmEPOAdmin.pre_element_logic_init_done = true;

			$.tmEPOAdmin.is_original = ($( '.tm-wmpl-disabled' ).length == 0);
			$.tmEPOAdmin.current_edit_label = false;

			$.tmEPOAdmin.toggle_variation_button();

			if ( $.tmEPOAdmin.is_original ) {
				// Sections sortable
				$( ".builder_layout" ).sortable( {
					handle: ".move",
					cursor: "move",
					items: ".builder_wrapper:not(.tma-nomove)",
					start: function ( e, ui ) {
						ui.placeholder.height( ui.helper.outerHeight() );
						ui.placeholder.width( ui.helper.outerWidth() );
					},
					stop: function ( e, ui ) {
						$.tmEPOAdmin.builder_reorder_multiple();
					},
					cancel: '.tma-nomove',
					forcePlaceholderSize: true,
					placeholder: 'bitem pl2',

					forceHelperSize: true,
					helper: 'clone',

					tolerance: 'pointer'
				} );

				// Elements sortable
				$.tmEPOAdmin.builder_items_sortable( $( ".builder_wrapper .bitem_wrapper" ) );

				// Elements draggable
				$( ".builder_elements .ditem" ).draggable( {
					zIndex: 5000,
					scroll: true,
					helper: "clone",
					start: function ( event, ui ) {
						var current = $( event.target );
						current.css( {
							opacity: 0.3
						} );
						$( ".builder_layout .bitem_wrapper" ).not( ".tma-variations-wrap .bitem_wrapper" ).addClass( "highlight" );
					},
					stop: function ( event, ui ) {
						$( ".builder_layout .bitem_wrapper" ).removeClass( "highlight" );
						$( event.target ).css( {
							opacity: 1
						} );
					},
					connectToSortable: ".builder_layout .bitem_wrapper:not(.tma-nomove .bitem_wrapper)"
				} );
			}

			$.tmEPOAdmin.add_events();

			// Check section logic
			$.tmEPOAdmin.check_section_logic();
			// Check element logic
			$.tmEPOAdmin.check_element_logic();
			// Start logic
			$.tmEPOAdmin.section_logic_start();
			$.tmEPOAdmin.element_logic_start();

			// Prevent refresh page changes to hidden elements
			$.tmEPOAdmin.set_hidden();

			$.tmEPOAdmin.set_fields_change();
			$( document ).on( "change.cpf", ".builder_textfield_price_type", function () {
				$.tmEPOAdmin.set_fields_change( $( this ) );
			} );

			$.tmEPOAdmin.set_field_title();
			$( document ).on( "changetitle.cpf", ".tm-header-title", function () {
				$.tmEPOAdmin.set_field_title( $( this ) );
			} );

			$( document ).on( "sections_type_onChange.cpf", ".builder_wrapper", function () {
				$.tmEPOAdmin.sections_type_onChange( $( this ) );
			} );

			$( ".builder_wrapper.tm-slider-wizard" ).each( function () {
				var bw = $( this );
				$.tmEPOAdmin.create_slider( bw );
			} );

			// Move disabled categories checkbox
			$( "#taxonomy-product_cat" ).before( $( "#tc_disabled_categories" ).removeClass( "hidden" ) );
			$.tmEPOAdmin.disable_categories();
			$( document ).on( "click.cpf", ".meta-disable-categories", function () {
				$.tmEPOAdmin.disable_categories();
			} );

			$( '#product_catdiv' ).before( $( '<div class="tc-info-box hidden"></div>' ) );
			$( document ).on( "change.cpf", ".product_page_tm-global-epo #product_catdiv input:checkbox, .product_page_tm-global-epo #tm_product_ids, .product_page_tm-global-epo #tm_enabled_options, .product_page_tm-global-epo #tm_disabled_options", function () {
				$.tmEPOAdmin.check_if_applied();
			} );
			$.tmEPOAdmin.check_if_applied();

			$.tmEPOAdmin.init_sections_check();
			$.tmEPOAdmin.fix_content_float();

			$.tmEPOAdmin.fix_form_submit();

			$.tmEPOAdmin.pre_element_logic_init_done = false;
			$.tmEPOAdmin.isinit = false;
		},

		cpf_logic_element_onchange: function ( e, ison ) {
			var $this = $( this );
			if ( e instanceof jQuery ) {
				$this = e;
			}
			if ( ison === undefined ) {
				ison = $( this ).closest( ".section_elements, .builder_wrapper, .bitem, .builder_element_wrap" );
				if ( ison.is( ".section_elements" ) || ison.is( ".builder_wrapper" ) ) {
					ison = false;
				} else {
					ison = true;
				}
			}
			var logic;
			if ( ! ison ) {
				logic = $.tmEPOAdmin.logic_object;
			} else {
				logic = $.tmEPOAdmin.element_logic_object;
			}
			var element = $this.val();
			var section = $this.children( 'option:selected' ).attr( 'data-section' );
			var type = $this.children( 'option:selected' ).attr( 'data-type' );
			var cpf_logic_value = logic;
			if ( section in cpf_logic_value ) {
				cpf_logic_value = logic[ section ].values;
				if ( element in cpf_logic_value ) {
					cpf_logic_value = logic[ section ].values[ element ];
				} else {
					cpf_logic_value = false;
				}
			} else {
				cpf_logic_value = false;
			}
			var select = $this.closest( '.tm-logic-rule' ).find( '.tm-logic-value' );
			var selectoperator = $this.closest( '.tm-logic-rule' ).find( '.cpf-logic-operator' );

			if ( cpf_logic_value ) {
				cpf_logic_value = $( cpf_logic_value );
				var value = selectoperator.val();

				select.empty().append( cpf_logic_value );

				selectoperator.find( "[value='is']" ).show();
				selectoperator.find( "[value='isnot']" ).show();
				if ( type == "variation" || type == "multiple" ) {
					var value = selectoperator.val();
					if ( value == 'startswith' || value == 'endswith' || value == 'greaterthan' || value == 'lessthan' ) {
						selectoperator.val( "isempty" );
					}
					selectoperator.find( "[value='startswith']" ).hide();
					selectoperator.find( "[value='endswith']" ).hide();
					selectoperator.find( "[value='greaterthan']" ).hide();
					selectoperator.find( "[value='lessthan']" ).hide();
					selectoperator.trigger( "change.cpf" );
				} else {
					selectoperator.find( "[value='startswith']" ).show();
					selectoperator.find( "[value='endswith']" ).show();
					selectoperator.find( "[value='greaterthan']" ).show();
					selectoperator.find( "[value='lessthan']" ).show();
				}
			} else {
				if ( element == section ) {
					var value = selectoperator.val();
					if ( value == 'is' || value == 'isnot' || value == 'startswith' || value == 'endswith' || value == 'greaterthan' || value == 'lessthan' ) {
						selectoperator.val( "isempty" );
					}
					selectoperator.find( "[value='is']" ).hide();
					selectoperator.find( "[value='isnot']" ).hide();
					selectoperator.find( "[value='startswith']" ).hide();
					selectoperator.find( "[value='endswith']" ).hide();
					selectoperator.find( "[value='greaterthan']" ).hide();
					selectoperator.find( "[value='lessthan']" ).hide();
					selectoperator.trigger( "change.cpf" );
				} else {
					selectoperator.find( "[value='is']" ).show();
					selectoperator.find( "[value='isnot']" ).show();
					selectoperator.find( "[value='startswith']" ).show();
					selectoperator.find( "[value='endswith']" ).show();
					selectoperator.find( "[value='greaterthan']" ).show();
					selectoperator.find( "[value='lessthan']" ).show();
				}
			}
		},

		cpf_logic_operator_onchange: function ( e, ison ) {
			var $this = $( this );
			if ( e instanceof jQuery ) {
				$this = e;
			}
			var value = $this.val();
			var select = $this.closest( '.tm-logic-rule' ).find( '.tm-logic-value' );
			if ( value == 'isempty' || value == 'isnotempty' ) {
				select.hide();
			} else {
				select.show();
			}
		},
		create_slider: function ( bw ) {
			bw.tmtabs( {
				headers: ".tm-slider-wizard-headers",
				header: ".tm-slider-wizard-header",
				selectedtab: 0,
				showonhover: function () {
					return $.tmEPOAdmin.is_element_dragged;
				},
				useclasstohide: true,
				afteraddtab: function ( h, t ) {
					$.tmEPOAdmin.builder_items_sortable( t );
					bw.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
						if ( ! bw.is( ".tm-slider-wizard" ) ) {
							return "";
						}
						return bw.find( '.bitem_wrapper' )
							.map( function ( i, e ) {
								return $( e ).children( '.bitem' ).not( '.pl2' ).length;
							} ).get().join( "," );
					} );
				},
				deletebutton: true,
				deleteconfirm: true,
				afterdeletetab: function () {
					bw.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
						if ( ! bw.is( ".tm-slider-wizard" ) ) {
							return "";
						}
						return bw.find( '.bitem_wrapper' )
							.map( function ( i, e ) {
								return $( e ).children( '.bitem' ).not( '.pl2' ).length;
							} ).get().join( "," );
					} );
					$.tmEPOAdmin.builder_reorder_multiple();
					$( ".builder_layout .builder_wrapper" ).each( function ( i, el ) {
						$.tmEPOAdmin.logic_init( $( el ) );
					} );
					$.tmEPOAdmin.init_sections_check();
					$.tmEPOAdmin.fix_content_float();
				}
			} );
		},
		sections_type_onChange: function ( bw ) {
			var bitem_wrapper = bw.find( ".bitem_wrapper" ),
				style = bw.find( ".section_elements .sections_type" ).val();

			if ( style == "slider" && ! bw.hasClass( "tm-slider-wizard" ) ) {
				bw.addClass( "tm-slider-wizard" );
				var tab1 = '<div class="tm-box"><h4 class="tm-slider-wizard-header" data-id="tm-slide0">1</h4></div>',
					add = '<div class="tm-box tm-add-box"><h4 class="tm-add-tab"><span class="tcfa tcfa-plus"></span></h4></div>';
				bitem_wrapper.before( '<div class="transition tm-slider-wizard-headers">' + tab1 + add + '</div>' );
				bitem_wrapper.addClass( "tm-slider-wizard-tab tm-slide0" );

				$.tmEPOAdmin.create_slider( bw );

				bw.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
					if ( ! bw.is( ".tm-slider-wizard" ) ) {
						return "";
					}
					return bw.find( '.bitem_wrapper' )
						.map( function ( i, e ) {
							return $( e ).children( '.bitem' ).not( '.pl2' ).length;
						} ).get().join( "," );
				} );
			} else if ( style != "slider" && bw.hasClass( "tm-slider-wizard" ) ) {
				bw.find( '.bitem_wrapper' ).wrapAll( '<div class="tmtemp"></div>' );

				bw.find( '.bitem_wrapper .bitem' ).appendTo( bw.find( ".tmtemp" ) );
				bw.find( '.bitem_wrapper' ).remove();

				bw.find( ".tmtemp" ).addClass( "bitem_wrapper" ).removeClass( "tmtemp" );
				$.tmEPOAdmin.builder_items_sortable( bw.find( ".bitem_wrapper" ) );
				bw.find( ".tm-slider-wizard-headers" ).remove();
				bw.removeClass( "tm-slider-wizard" );
				bw.find( ".tm_builder_section_slides" ).val( "" );
			}
		},

		variation_events_success: function ( event, xhr, settings ) {
			setTimeout( function () {
				$.tmEPOAdmin.set_global_variation_object( "reindex" );
			}, 600 );
			$( document ).unbind( "ajaxSuccess", $.tmEPOAdmin.variation_events_success );
			$.tmEPOAdmin.var_remove( "tma-remove_variation-added" );
		},

		tm_variations_check_events_success: function ( event, xhr, settings ) {
			setTimeout( function () {
				$.tmEPOAdmin.tm_variations_check_for_changes = 1;
				$.tmEPOAdmin.tm_variations_check();
			}, 600 );
			$( document ).unbind( "ajaxSuccess", $.tmEPOAdmin.tm_variations_check_events_success );
			$.tmEPOAdmin.var_remove( "tma-remove_variation-added" );
		},

		add_variation_events: function () {
			if ( $.tmEPOAdmin.var_is( "tma-variation-events-added" ) === true ) {
				return;
			}
			if ( $.tmEPOAdmin.var_is( "tma-remove_variation-added" ) !== true ) {
				$( '#variable_product_options' ).on( 'click.tma', '.remove_variation', function ( e ) {
					$( document ).ajaxSuccess( $.tmEPOAdmin.variation_events_success );
					$.tmEPOAdmin.var_is( "tma-remove_variation-added", true );

					$( document ).ajaxSuccess( $.tmEPOAdmin.tm_variations_check_events_success );
				} );
			}
			if ( $.tmEPOAdmin.var_is( "tma-remove_variation-added" ) !== true ) {
				$( '.wc-metaboxes-wrapper' ).on( 'click', 'a.bulk_edit', function ( event ) {
					var bulk_edit = $( 'select#field_to_edit' ).val();
					if ( bulk_edit == "delete_all" ) {
						$( document ).ajaxSuccess( $.tmEPOAdmin.variation_events_success );
						$.tmEPOAdmin.var_is( "tma-remove_variation-added", true );

						$( document ).ajaxSuccess( $.tmEPOAdmin.tm_variations_check_events_success );
					}
				} );
			}
			$( '#variable_product_options' ).on( 'woocommerce_variations_added', function () {
				$.tmEPOAdmin.set_global_variation_object( "reindex" );
				$( document ).ajaxSuccess( $.tmEPOAdmin.tm_variations_check_events_success );
			} );

			$( '#woocommerce-product-data' ).on( 'woocommerce_variations_saved', function () {
				$.tmEPOAdmin.set_global_variation_object( "reindex" );
				$( document ).ajaxSuccess( $.tmEPOAdmin.tm_variations_check_events_success );
			} );

			$( document ).on( "click.cpf", ".save_attributes", function ( e ) {
				$( document ).ajaxSuccess( $.tmEPOAdmin.tm_variations_check_events_success );
			} );

			$.tmEPOAdmin.var_is( "tma-variation-events-added", true );
		},

		toggle_variation_button: function () {
			var product_type = $( "#product-type" );
			if ( ! product_type.length ) {
				return;
			} else {
				if ( product_type.val() == "variable" || product_type.val() == "variable-subscription" ) {
					$( ".builder_add_section" ).addClass( "inline" );
					$( ".builder_add_variation" ).addClass( "inline" ).removeClass( "tm-hidden" );
					$.tmEPOAdmin.add_variation_events();
					var variation_element = $( ".builder_layout .element-variations" );
					if ( variation_element.length ) {
						var variation_element_builder_wrapper = variation_element.closest( ".builder_wrapper" );
						variation_element_builder_wrapper
							.find( ".tm-add-element-action,.tmicon.clone,.tmicon.size,.tmicon.move,.tmicon.plus,.tmicon.minus" ).remove();
						variation_element_builder_wrapper.addClass( "tma-nomove tma-variations-wrap" );
						variation_element_builder_wrapper
							.find( ".builder_hide_for_variation" ).hide();
						var _rlogictab = variation_element_builder_wrapper.find( ".tma-tab-title,.tma-tab-logic,.tma-tab-css,.tma-tab-woocommerce" );
						_rlogictab.hide();

						$.tmEPOAdmin.var_is( "tm-style-variation-added", true );

						variation_element.addClass( "tma-nomove" );
						variation_element.find( ".tmicon.size,.tmicon.clone,.tmicon.move,.tmicon.plus,.tmicon.minus,.tmicon.delete" ).remove();
						_rlogictab = variation_element.find( ".tma-tab-title,.tma-tab-logic,.tma-tab-css,.tma-tab-woocommerce" );
						_rlogictab.remove();

						$( ".builder_add_section" ).removeClass( "inline" );
						$( ".builder_add_variation" ).removeClass( "inline" ).addClass( "tm-hidden" );
					}
				} else {
					$( ".builder_add_section" ).removeClass( "inline" );
					$( ".builder_add_variation" ).removeClass( "inline" ).addClass( "tm-hidden" );
				}
			}
		},

		can_take_logic: function () {
			return ".element-color,.element-range,.element-radiobuttons,.element-checkboxes,.element-selectbox,.element-textfield,.element-textarea,.element-variations";
		},

		prepare_for_json: function ( data ) {
			var result = {}, arr,
				value, must_be_array;
			for ( var i in data ) {
				if ( i.indexOf( "tm_meta[" ) == 0 ) {
					arr = i.split( /[[\]]{1,2}/ );
					arr.pop();
					arr = arr.map( function ( item ) {
						return item === '' ? null : item
					} );
					if ( arr.length > 0 && arr[ arr.length - 1 ] == null ) {
						must_be_array = true;
					} else {
						must_be_array = false;
					}
					arr = arr.filter( function ( v, k, el ) {
						if ( v !== null && v !== undefined ) {
							return v;
						}
					} );
					if ( typeof data[ i ] != "object" && must_be_array ) {
						value = [ data[ i ] ];
					} else {
						value = data[ i ];
					}
					result = $.tmEPOAdmin.constructObject( arr, value, result );
				}
			}
			return result;
		},

		constructObject: function ( a, final_value, obj ) {
			var val = a.shift();
			if ( a.length > 0 ) {
				if ( ! obj.hasOwnProperty( val ) ) {
					obj[ val ] = {};
				}
				obj[ val ] = $.tmEPOAdmin.constructObject( a, final_value, obj[ val ] );
			} else {
				obj[ val ] = final_value;
			}
			return obj;
		},

		fix_form_submit: function () {

			$( "#post" ).submit( function () {
				var tm_meta, 
					data, 
					data_wpml, 
					tm_meta_serialized, 
					previewField = $( 'input#wp-preview' );
				
				tm_meta = $( this ).find( '[name^="tm_meta["]' );

				$( '.tm_meta_serialized' ).remove();
				$( '.tm_meta_serialized_wpml' ).remove();

				tm_meta.attr( "disabled", false );

				if ( ! $.tmEPOAdmin.is_original ) {
					data_wpml = $.tmEPOAdmin.prepare_for_json( $( this ).tm_serializeObject() );
					data_wpml = $.tmEPOAdmin.tm_escape( $.toJSON( data_wpml ) );
					tm_meta_serialized = $( "<textarea class='tm_meta_serialized tm-hidden' name='tm_meta_serialized_wpml'></textarea>" ).val( data_wpml );
					$( this ).prepend( tm_meta_serialized );
				} else {
					data = $.tmEPOAdmin.prepare_for_json( $( this ).tm_serializeObject() );
					data = $.tmEPOAdmin.tm_escape( $.toJSON( data ) );
					tm_meta_serialized = $( "<textarea class='tm_meta_serialized tm-hidden' name='tm_meta_serialized'></textarea>" ).val( data );
					$( this ).prepend( tm_meta_serialized );
				}
				tm_meta.attr( "disabled", "disabled" );
				if ( previewField.length > 0 && previewField.val() != "" ) {
					tm_meta.not( '.tm-wmpl-disabled' ).attr( "disabled", false );
					$( '.tm_meta_serialized' ).remove();
				}
				return true; // ensure form still submits
			} );

		},

		init_sections_check: function () {
			var length = $( ".builder_wrapper" ).length;
			if ( ! length ) {
				$( '.builder_elements' ).hide();
				$( '.builder-add-section-action' ).hide();
				$( '.builder_selector' ).hide();
				$( '.tc-welcome' ).show();
			} else {
				$( '.builder_elements' ).show();
				$( '.builder-add-section-action' ).show();
				$( '.builder_selector' ).show();
				$( '.tc-welcome' ).hide();
			}
		},

		fix_content_float: function () {
			var height;
			if ( $( '.builder_elements' ).is( ':hidden' ) ) {
				height = 0;
			} else {
				height = $( '.builder_elements' ).outerHeight();

			}
			$( "#wpcontent" ).css( "margin-bottom", height + "px" );
		},

		disable_categories: function ( e ) {
			if ( $( ".meta-disable-categories" ).is( ":checked" ) ) {
				$( "#taxonomy-product_cat" ).slideUp();
			} else {
				$( "#taxonomy-product_cat" ).slideDown();
			}
		},

		check_if_applied: function ( e ) {
			if ( ! $( "body" ).is( '.product_page_tm-global-epo' ) ) {
				return;
			}
			var nocat = $( '#tm_meta_disable_categories:checked' ).length > 0,
				cat = $( '#product_catdiv input:checkbox' ).not( $( '#tm_meta_disable_categories' ) ).filter( ':checked' ).length > 0,
				tm_product_ids = $( '#tm_product_ids' ).val(),
				tm_enabled_options = $( '#tm_enabled_options' ).val(),
				tm_disabled_options = $( '#tm_disabled_options' ).val();
			tm_product_ids = tm_product_ids && tm_product_ids !== null ? tm_product_ids.length > 0 : false;
			tm_enabled_options = tm_enabled_options && tm_enabled_options !== null ? tm_enabled_options.length > 0 : false;
			tm_disabled_options = tm_disabled_options && tm_disabled_options !== null ? tm_disabled_options.length > 0 : false;
			if ( nocat ) {
				if ( tm_product_ids || tm_enabled_options || tm_disabled_options ) {
					$( '.tc-info-box' ).removeClass( 'tc-error tc-all-products' ).addClass( 'hidden' ).html( '' );
				} else {
					$( '.tc-info-box' ).removeClass( 'hidden tc-all-products' ).addClass( 'tc-error' ).html( tm_epo_admin.i18n_form_not_applied_to_all );
				}
			} else {
				if ( cat ) {
					$( '.tc-info-box' ).removeClass( 'tc-error tc-all-products' ).addClass( 'hidden' ).html( '' );
				} else {
					$( '.tc-info-box' ).removeClass( 'hidden error' ).addClass( 'tc-all-products' ).html( tm_epo_admin.i18n_form_is_applied_to_all );
				}
			}
		},

		check_section_logic: function ( section ) {
			if ( ! section && $.tmEPOAdmin.isinit && $.tmEPOAdmin.done_check_section_logic ) {
				return;
			}
			if ( ! section ) {
				section = $( '#tmformfieldsbuilderwrap' ).find( "div.builder_wrapper" );
			}
			section.each( function ( i, el ) {
				var current_section = $( el );
				var this_section_id = current_section.find( '.tm-builder-sections-uniqid' ).val();
				if ( ! this_section_id || this_section_id === '' || this_section_id === undefined || this_section_id === false ) {
					current_section.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );
				}
				var this_section_activate_sections_logic = parseInt( current_section.find( '.activate-sections-logic' ).val() );
				if ( this_section_activate_sections_logic == 1 ) {
					current_section.find( '.builder-logic-div' ).show();
				} else {
					current_section.find( '.builder-logic-div' ).hide();
				}
			} );
			$.tmEPOAdmin.done_check_section_logic = true;
		},

		check_element_logic: function ( element ) {
			if ( ! element && $.tmEPOAdmin.isinit && $.tmEPOAdmin.done_check_section_logic ) {
				return;
			}
			var uniqids = [],
				all = false;
			if ( ! element ) {
				element = $( '#tmformfieldsbuilderwrap' ).find( "div.bitem" );
				all = true;
			}
			element.each( function ( i, el ) {
				el = $( el );
				var this_element_id = el.find( '.tm-builder-element-uniqid' ).val();
				if ( (all && uniqids.indexOf( this_element_id ) !== - 1 ) || ! this_element_id || this_element_id === '' || this_element_id === undefined || this_element_id === false ) {
					el.find( '.tm-builder-element-uniqid' ).val( $.tm_uniqid( "", true ) );
				}
				if ( all ) {
					uniqids.push( el.find( '.tm-builder-element-uniqid' ).val() );
				}
				var this_element_activate_element_logic = parseInt( el.find( '.activate-element-logic' ).val() );
				if ( this_element_activate_element_logic == 1 ) {
					el.find( '.builder-logic-div' ).show();
				} else {
					el.find( '.builder-logic-div' ).hide();
				}
			} );
			$.tmEPOAdmin.done_check_section_logic = true;
		},

		section_logic_start: function ( section ) {
			if ( ! section ) {
				section = $( ".builder_layout .builder_wrapper" );
			}
			section.each( function ( i, el ) {
				el = $( el );
				$.tmEPOAdmin.logic_init( el );
				try {
					var rules = el.find( ".section_elements .tm-builder-clogic" ).val() || "null";
					rules = rules.tmparseJSON();
					rules = $.tmEPOAdmin.logic_check_section_rules( rules );
					el.find( ".section_elements .tm-builder-clogic" ).val( JSON.stringify( rules ) );
					el.find( ".section_elements .epo-rule-toggle" ).val( rules.toggle );
					el.find( ".section_elements .epo-rule-what" ).val( rules.what );
				} catch ( err ) {
				}
			} );
		},

		element_logic_start: function ( element ) {
			if ( ! element ) {
				element = $( ".builder_layout .builder_wrapper .bitem" );
			}
			element.each( function ( i, el ) {
				el = $( el );
				$.tmEPOAdmin.element_logic_init( el );
				try {
					var rules = el.find( ".tm-builder-clogic" ).val() || "null";
					rules = rules.tmparseJSON();
					rules = $.tmEPOAdmin.logic_check_element_rules( rules );
					el.find( ".tm-builder-clogic" ).val( JSON.stringify( rules ) );
					el.find( ".epo-rule-toggle" ).val( rules.toggle );
					el.find( ".epo-rule-what" ).val( rules.what );
				} catch ( err ) {
				}
			} );
		},

		panels_reorder: function ( obj ) {
			var panels = $( obj );
			panels.children( ".options_wrap" ).each( function ( i, el ) {
				var tm_default_radio = $( el ).find( ".tm-default-radio,.tm-default-checkbox" );
				tm_default_radio.val( i );
			} );
		},

		// Options sortable
		panels_sortable: function ( obj ) {
			if ( $( obj ).length == 0 || ! $.tmEPOAdmin.is_original ) {
				return;
			}
			obj.not( $( ".builder_elements .panels_wrap" ) ).sortable( {
				cursor: "move",
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				placeholder: 'panel_wrap pl',
				stop: function ( e, ui ) {
					$.tmEPOAdmin.panels_reorder( $( ui.item ).closest( ".panels_wrap" ) );
				}
			} );
		},

		// Delete all options button
		builder_panel_delete_all_onClick: function ( e ) {
			e.preventDefault();
			$( this ).trigger( "hideTtooltip" );
			var _panels_wrap = $( this ).closest( ".panels_wrap" );
			var panels_wrap = $( '.flasho.tm_wrapper' ).find( '.panels_wrap' );
			if ( panels_wrap.children().length > 1 ) {
				var options_wrap = panels_wrap.find( '.options_wrap' );
				options_wrap.each( function ( i ) {
					if ( i == 0 ) {
						return true;
					}
					$( this ).remove();
					panels_wrap.find( ".numorder" ).each( function ( i2, el2 ) {
						$( this ).html( i2 + 1 );
					} );
				} );
				options_wrap.find( 'input' ).val( '' );
				$.tmEPOAdmin.panels_reorder( _panels_wrap );
			}
		},

		// Delete options button
		builder_panel_delete_onClick: function ( e ) {
			e.preventDefault();
			$( this ).trigger( "hideTtooltip" );
			var _panels_wrap = $( this ).closest( ".panels_wrap" );
			if ( _panels_wrap.children().length > 1 ) {
				$( this ).closest( ".options_wrap" ).css( {
					margin: "0 auto"
				} ).animate( {
					opacity: 0,
					height: 0,
					width: 0
				}, 300, function () {
					$( this ).remove();
					_panels_wrap.find( ".numorder" ).each( function ( i2, el2 ) {
						$( this ).html( i2 + 1 );
					} );
					_panels_wrap.children( ".options_wrap" ).each( function ( k, v ) {
						$( this ).find( "[id]" ).each( function ( k2, v2 ) {
							var _name = $( this ).attr( "name" ).replace( /[\[\]]/g, "" );
							$( this ).attr( "id", _name + "_" + k );
						} );
					} );
					$.tmEPOAdmin.panels_reorder( _panels_wrap );
				} );
			}
		},

		// Mass add options button
		builder_panel_mass_add_onClick: function ( e ) {
			e.preventDefault();
			if ( $( this ).is( '.disabled' ) ) {
				return;
			}
			$( this ).addClass( 'disabled' );
			var $html = '<div class="tm-panel-populate-wrapper">' +
				'<textarea class="tm-panel-populate"></textarea>' +
				'<button type="button" class="tc tc-button builder-panel-populate">' + tm_epo_admin.i18n_populate + '</button>' +
				'</div>';
			$( this ).after( $html );
		},

		// Populate options button
		builder_panel_populate_onClick: function ( e ) {
			e.preventDefault();
			$( this ).remove();

			var panels_wrap = $( '.flasho.tm_wrapper' ).find( '.panels_wrap' ),
				_last = panels_wrap.children(),
				full_element = $( '' ),
				lines = $( '.tm-panel-populate' ).val().split( /\n/ ),
				texts = [];

			for ( var i = 0; i < lines.length; i ++ ) {
				// only push this line if it contains a non whitespace character.
				if ( /\S/.test( lines[ i ] ) ) {
					texts.push( $.trim( lines[ i ] ) );
				}
			}
			for ( var i = 0; i < texts.length; i ++ ) {
				var line = texts[ i ].split( '|' );
				var len = line.length;
				if ( len == 0 ) {
					continue;
				}
				if ( len == 1 ) {
					line[ 1 ] = 0;
				}
				line[ 0 ] = $.trim( line[ 0 ] );
				line[ 1 ] = parseFloat( $.trim( line[ 1 ] ) );
				if ( isNaN( line[ 1 ] ) ) {
					line[ 1 ] = '';
				}
				var toadd = $.tmEPOAdmin.add_panel_row( line, panels_wrap, _last );

				full_element = full_element.add( toadd );
			}
			if ( full_element.length ) {
				panels_wrap.append( full_element );
				$.tm_tooltip( full_element.find( '.tm-tooltip' ) );
			}
			$( '.builder-panel-mass-add' ).removeClass( 'disabled' );
			$( '.tm-panel-populate-wrapper' ).remove();
			$.tmEPOAdmin.paginattion_init( "last" );
		},

		add_panel_row: function ( line, panels_wrap, _last ) {
			var _clone = _last.last().tm_clone();
			if ( _clone ) {
				$.tmEPOAdmin.builder_clone_after_events( _clone );
				$.tmEPOAdmin.gen_events( _clone );
				_clone.find( "[name]" ).val( "" );
				_clone.find( "[id]" ).each( function ( k, v ) {
					var _name = $( this ).attr( "name" ).replace( /[\[\]]/g, "" );
					var _l = _last.length;
					$( this ).attr( "id", _name + "_" + _l );
				} );
				_clone.find( ".tm_option_title" ).val( line[ 0 ] );
				_clone.find( ".tm_option_value" ).val( line[ 0 ] );
				_clone.find( ".tm_option_price" ).val( line[ 1 ] );
				if ( line[ 2 ] ) {
					_clone.find( ".tm_option_price_type" ).val( line[ 2 ] );
					if ( _clone.find( ".tm_option_price_type" ).val() == null ) {
						_clone.find( ".tm_option_price_type" ).val( "" );
					}
				}
				if ( line[ 3 ] ) {
					_clone.find( ".tm_option_description" ).val( line[ 3 ] );
					if ( _clone.find( ".tm_option_description" ).val() == null ) {
						_clone.find( ".tm_option_description" ).val( "" );
					}
				}
				_clone.find( ".numorder" ).html( parseInt( parseInt( _last.length ) + 1 ) );
				_clone.find( ".tm_upload_image img" ).attr( "src", "" );
				_clone.find( "input.tm_option_image" ).val( "" );
				_clone.find( ".tm-default-radio,.tm-default-checkbox" ).removeAttr( "checked" ).prop( "checked", false ).val( _last.length );

				return _clone;
			}
			return $( '' );
		},

		// Add options button
		builder_panel_add_onClick: function ( e ) {
			e.preventDefault();
			var _last = $( this ).prev( ".panels_wrap" ).children();
			var _clone = _last.last().tm_clone();
			if ( _clone ) {
				$.tmEPOAdmin.builder_clone_after_events( _clone );
				$.tmEPOAdmin.gen_events( _clone );
				_clone.find( "[name]" ).val( "" );
				_clone.find( "[id]" ).each( function ( k, v ) {
					var _name = $( this ).attr( "name" ).replace( /[\[\]]/g, "" );
					var _l = _last.length;
					$( this ).attr( "id", _name + "_" + _l );
				} );
				_clone.find( ".numorder" ).html( parseInt( parseInt( _last.length ) + 1 ) );
				_clone.find( ".tm_upload_image img" ).attr( "src", "" );
				_clone.find( "input.tm_option_image" ).val( "" );
				_clone.find( ".tm-default-radio,.tm-default-checkbox" ).removeAttr( "checked" ).prop( "checked", false ).val( _last.length );

				$( this ).prev( ".panels_wrap" ).append( _clone );
				$.tm_tooltip( _clone.find( '.tm-tooltip' ) );
				$.tmEPOAdmin.paginattion_init( "last" );
				$.tmEPOAdmin.tm_upload();
			}
		},

		// Section add button
		builder_add_section_onClick: function ( e, ap ) {
			if ( e ) {
				e.preventDefault();
			}
			var _template = $( '.builder_hidden_section' ).data( 'template' );
			if ( _template ) {
				var _clone = $( _template[ 'html' ] );
				if ( _clone ) {
					_clone.addClass( "w100" );
					_clone.addClass( "appear" );
					_clone.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );
					console.log(ap);
					if (ap){
						_clone.appendTo( ".builder_layout" );	
					}else{
						_clone.prependTo( ".builder_layout" );
					}					
					
					
					$.tmEPOAdmin.gen_events( _clone );
					_clone.find( ".tm-tabs" ).tmtabs();
					$.tmEPOAdmin.check_section_logic( _clone );
					$.tmEPOAdmin.logic_init( _clone );
					$.tmEPOAdmin.builder_items_sortable( _clone.find( ".bitem_wrapper" ) );
					$.tmEPOAdmin.builder_reorder_multiple();
					if ( $( this ).is( "a" ) ) {
						$( window ).tc_scrollTo( _clone );
					}

					$.tmEPOAdmin.init_sections_check();
					$.tmEPOAdmin.fix_content_float();

					return _clone;
				}
			}

			return false;

		},

		builder_add_element_onClick: function ( e ) {
			if ( e ) {
				e.preventDefault();
			}
			var $this = $( this );
			var $_html = $.tmEPOAdmin.builder_floatbox_template_import( {
				"id": "temp_for_floatbox_insert",
				"html": '<div class="tm-inner">' + tm_epo_admin.element_data + '</div>',
				"title": tm_epo_admin.i18n_add_element
			} );
			var temp_floatbox = $( "body" ).tm_floatbox( {
				"fps": 1,
				"ismodal": false,
				"refresh": "fixed",
				"width": "70%",
				"height": "70%",
				"top": "15%",
				"left": "15%",
				"classname": "flasho tm_wrapper tc-builder-add-element",
				"data": $_html
			} );

			$( "#flasho .details_cancel" ).click( function () {
				if ( temp_floatbox ) temp_floatbox.cancelfunc();
			} );

			$( ".tc-builder-add-element .tc-element-button" ).on( "click.cpf", function ( ev ) {
				ev.preventDefault();
				var new_section = $this.closest( ".builder_wrapper" ),
					el = $( this ).attr( "data-element" );

				if ( $this.is( ".tc-prepend" ) ) {
					$.tmEPOAdmin.builder_clone_element( el, new_section, "prepend" );
				} else {
					$.tmEPOAdmin.builder_clone_element( el, new_section );
				}

				$.tmEPOAdmin.logic_reindex();
				$( "#flasho .details_cancel" ).trigger( "click" );
			} );
		},

		builder_add_section_and_element_onClick: function ( e ) {
			if ( e ) {
				e.preventDefault();
			}

			var $_html = $.tmEPOAdmin.builder_floatbox_template_import( {
				"id": "temp_for_floatbox_insert",
				"html": '<div class="tm-inner">' + tm_epo_admin.element_data + '</div>',
				"title": tm_epo_admin.i18n_add_element
			} );
			var temp_floatbox = $( "body" ).tm_floatbox( {
				"fps": 1,
				"ismodal": false,
				"refresh": "fixed",
				"width": "70%",
				"height": "70%",
				"top": "15%",
				"left": "15%",
				"classname": "flasho tm_wrapper tc-builder-add-section-and-element",
				"data": $_html
			} );

			$( "#flasho .details_cancel" ).click( function () {
				if ( temp_floatbox ) temp_floatbox.cancelfunc();
			} );

			$( ".tc-builder-add-section-and-element .tc-element-button" ).on( "click.cpf", function ( ev ) {
				ev.preventDefault();

				var new_section = $.tmEPOAdmin.builder_add_section_onClick(false,true);

				if ( new_section ) {
					var el = $( this ).attr( "data-element" );
					$.tmEPOAdmin.builder_clone_element( el, new_section );
					$.tmEPOAdmin.logic_reindex();
					$( "#flasho .details_cancel" ).trigger( "click" );
				}
			} );

		},

		tm_variations_check_for_changes: 0,

		tm_variations_check: function () {

			var variation_element = $( ".builder_layout .element-variations" );
			if ( ! variation_element.length ) {
				return;
			}

			if ( typeof(tm_global_epo_admin) == "undefined" && typeof(tm_epo_admin_meta_boxes) != "undefined" ) {
				var tm_global_epo_admin = tm_epo_admin_meta_boxes;
			}
			if ( $.tmEPOAdmin.tm_variations_check_for_changes == 1 && typeof(tm_global_epo_admin) != "undefined" ) {
				$( '#tm_extra_product_options' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff url(' + tm_global_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
						opacity: 0.6
					}
				} );
				var data = {
					action: 'woocommerce_tm_variations_check',
					post_id: tm_global_epo_admin.post_id,
					security: tm_global_epo_admin.check_attributes_nonce
				};
				$.post( tm_global_epo_admin.ajax_url, data, function ( response ) {
					$( '.tma-variations-wrap .tm-all-attributes' ).html( response );
					$( '#tm_extra_product_options' ).unblock();
					$( '#tm_extra_product_options' ).trigger( 'woocommerce_tm_variations_check_loaded' );
					$.tmEPOAdmin.tm_variations_check_for_changes = 0;
					$.tmEPOAdmin.builder_reorder_multiple();
				} );
			}

		},

		// Variation button
		builder_add_variation_onClick: function ( e ) {
			if ( e ) {
				e.preventDefault();
			}
			if ( $.tmEPOAdmin.var_is( "tm-style-variation-added" ) === true ) {
				return;
			}
			var _template = $( '.builder_hidden_section' ).data( 'template' );
			if ( _template ) {
				var _clone = $( _template[ 'html' ] );
				if ( _clone ) {
					_clone.addClass( "w100" );
					_clone.addClass( "appear" );
					_clone.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );

					_clone.find( ".tm-add-element-action,.tmicon.clone,.tmicon.size,.tmicon.move,.tmicon.plus,.tmicon.minus" ).remove();
					_clone.addClass( "tma-nomove tma-variations-wrap" );
					_clone.find( ".builder_hide_for_variation" ).hide();

					var _rlogictab = _clone.find( ".tma-tab-title,.tma-tab-logic,.tma-tab-css,.tma-tab-woocommerce" );
					_rlogictab.hide();

					_clone.prependTo( ".builder_layout" );
					$.tmEPOAdmin.gen_events( _clone );
					_clone.find( ".tm-tabs" ).tmtabs();
					$.tmEPOAdmin.check_section_logic( _clone );
					$.tmEPOAdmin.logic_init( _clone );

					$.tmEPOAdmin.init_sections_check();
					$.tmEPOAdmin.fix_content_float();
					$.tmEPOAdmin.var_is( "tm-style-variation-added", true );
					$( ".builder_add_variation" ).addClass( "tm-hidden" );
					$( ".builder_add_section" ).removeClass( "inline" );

					var _clone2 = $.tmEPOAdmin.builder_clone_element( "variations", $( ".builder_layout" ).find( ".builder_wrapper" ).first() );
					_clone2.find( ".tmicon.size,.tmicon.clone,.tmicon.move,.tmicon.plus,.tmicon.minus,.tmicon.delete" ).remove();
					_rlogictab = _clone2.find( ".tma-tab-title,.tma-tab-logic,.tma-tab-css,.tma-tab-woocommerce" );
					_clone2.addClass( "tma-nomove" );
					_clone2.find( ".builder_hide_for_variation" ).hide();
					_rlogictab.hide();
					$.tmEPOAdmin.logic_reindex_force();

					$.tmEPOAdmin.tm_variations_check_for_changes = 1;
					$.tmEPOAdmin.tm_variations_check();
				}
			}
		},

		var_is: function ( v, d ) {
			if ( ! d ) {
				return $( "body" ).data( v );
			} else {
				$( "body" ).data( v, d );
				return;
			}
		},

		var_remove: function ( v ) {
			if ( v ) {
				$( "body" ).removeData( v );
			}
		},

		element_logic_object: {},

		logic_object: {},

		logic_operators: {
			'is': tm_epo_admin.i18n_is,
			'isnot': tm_epo_admin.i18n_is_not,
			'isempty': tm_epo_admin.i18n_is_empty,
			'isnotempty': tm_epo_admin.i18n_is_not_empty,
			'startswith': tm_epo_admin.i18n_starts_with,
			'endswith': tm_epo_admin.i18n_ends_with,
			'greaterthan': tm_epo_admin.i18n_greater_than,
			'lessthan': tm_epo_admin.i18n_less_than
		},

		tm_escape: function ( val ) {
			return encodeURIComponent( val );
		},

		tm_unescape: function ( val ) {
			return decodeURIComponent( val );
		},

		get_element_logic_init: function ( do_section ) {
			if ( ! $.tmEPOAdmin.pre_element_logic_init_done ) {
				$.tmEPOAdmin.pre_element_logic_init( do_section );
			}
			if ( ! $.tmEPOAdmin.isinit ) {
				$.tmEPOAdmin.pre_element_logic_init_done = false;
			} else {
				$.tmEPOAdmin.pre_element_logic_init_done = true;
			}
			return $.tmEPOAdmin.pre_element_logic_init_obj;
		},
		get_element_logic_options_init: function ( do_section ) {
			if ( ! $.tmEPOAdmin.pre_element_logic_init_done ) {
				$.tmEPOAdmin.pre_element_logic_init( do_section );
			}
			if ( ! $.tmEPOAdmin.isinit ) {
				$.tmEPOAdmin.pre_element_logic_init_done = false;
			} else {
				$.tmEPOAdmin.pre_element_logic_init_done = true;
			}
			return $.tmEPOAdmin.pre_element_logic_init_obj_options;
		},
		find_index: function ( is_slider, field ) {
			var sib = 0;
			if ( is_slider ) {
				sib = field.closest( '.bitem_wrapper' ).prevAll( '.bitem_wrapper' ).find( '.bitem' ).length;
			}
			return sib + field.index();
		},
		set_global_variation_object: function ( logic, do_section ) {
			if ( typeof(tm_global_epo_admin) == "undefined" && typeof(tm_epo_admin_meta_boxes) != "undefined" ) {
				var tm_global_epo_admin = tm_epo_admin_meta_boxes;
			}
			if ( tm_global_epo_admin ) {
				$( '#tm_extra_product_options' ).block( {
					message: null,
					overlayCSS: {
						background: '#fff url(' + tm_global_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
						opacity: 0.6
					}
				} );
				var data = {
					action: 'woocommerce_tm_get_variations_array',
					post_id: tm_global_epo_admin.post_id,
					security: tm_global_epo_admin.check_attributes_nonce
				};
				var c_ajaxurl = window.ajaxurl || tm_global_epo_admin.ajax_url;

				$.post( c_ajaxurl, data, function ( response ) {

					if ( response ) {
						global_variation_object = response;
						if ( logic === true ) {
							$.tmEPOAdmin.pre_element_logic_init_set( do_section );
						} else if ( logic == "reindex" ) {
							$.tmEPOAdmin.logic_reindex_force();
						} else if ( logic == "initialitize_on" ) {
							$.tmEPOAdmin.initialitize_on();
						}
					}
				}, 'json' )
					.always( function ( response ) {
						$( '#tm_extra_product_options' ).unblock();
					} );
			} else {
				if ( logic == "initialitize_on" ) {
					if ( ! global_variation_object ) {
						global_variation_object = {};
					}
					;
					$.tmEPOAdmin.initialitize_on();
				}
			}
		},

		pre_element_logic_init: function ( do_section ) {
			if ( ! global_variation_object ) {
				$.tmEPOAdmin.set_global_variation_object( true, do_section );
			} else {
				$.tmEPOAdmin.pre_element_logic_init_set( do_section );
			}
		},

		pre_element_logic_init_set: function ( do_section ) {
			if ( ! global_variation_object ) {
				return;
			}
			$.tmEPOAdmin.pre_element_logic_init_obj = {};
			$.tmEPOAdmin.pre_element_logic_init_obj_options = {};

			var options = {},
				logicobj = {},
				sections = $( ".builder_layout .builder_wrapper" ),
				log_section_id = [];

			sections.each( function ( i, section ) {
				section = $( section );
				var section_id = section.find( '.tm-builder-sections-uniqid' ).val();

				//check if section id exists
				if ( (log_section_id.indexOf( section_id ) != - 1) ) {
					section.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );
					section_id = section.find( '.tm-builder-sections-uniqid' ).val();
				}
				log_section_id.push( section_id );

				options[ section_id ] = [];
				if ( do_section ) {
					$.tmEPOAdmin.check_section_logic( section );
				}
				var _section_name = section.find( '.tm-internal-name' ).val() || "Section";
				if ( ! section.is( ".tma-variations-wrap" ) ) {
					options[ section_id ][ 0 ] = '<option data-type="section" data-section="' + section_id + '" value="' + section_id + '">' + _section_name + ' (' + section_id + ')</option>';
				}

				var fields = section.find( $.tmEPOAdmin.can_take_logic() );
				var values = [];
				var is_slider = section.is( ".tm-slider-wizard" );

				// all the fields of current section that can be used as selector in logic
				fields.each( function ( ii, field ) {
					var field = $( field ),
						name = field.find( '[name^="tm_meta\\[tmfbuilder\\]\\["][name$="_header_title\\]\\[\\]"]' ),
						field_index = $.tmEPOAdmin.find_index( is_slider, field );

					if ( name.length == 1 ) {
						var value = name.val();
						if ( value.length == 0 ) {
							value = name.closest( ".bitem" ).find( '.tm-label' ).text();
						}
						var internal_name = name.closest( ".bitem" ).find( '.tm-internal-name' ).val();
						if ( internal_name != value ) {
							value = value + " (" + internal_name + ")";
						}

						var field_type = field.is( ".element-variations" ) ? "variation" : (field.is( ".element-radiobuttons,.element-checkboxes,.element-selectbox" )) ? "multiple" : "text";
						options[ section_id ][ field_index + 1 ] = '<option data-type="' + field_type + '" data-section="' + section_id + '" value="' + field_index + '">' + value + '</option>';

						if ( field.is( ".element-variations" ) ) {
							var tm_title,
								field_values = [];
							$( global_variation_object[ "variations" ] ).each( function ( index, variation ) {
								tm_title = [];
								$( variation[ "attributes" ] ).each( function ( i, sel ) {
									var arr = $.map( sel, function ( el ) {
										return el;
									} );

									$( arr ).each( function ( i2, sel2 ) {
										tm_title.push( sel2 );
									} );

								} );
								tm_title = tm_title.join( ' - ' );
								var tm_title_label = tm_title;
								try{
									tm_title_label = $.tmEPOAdmin.tm_unescape( tm_title );
								}catch(err){

								}
								field_values.push( '<option value="' + $.tmEPOAdmin.tm_escape( variation.variation_id ) + '">' + tm_title_label + '</option>' );
							} );

							values[ field_index ] = '<select data-element="' + field_index + '" data-section="' + section_id + '" class="cpf-logic-value">' + field_values.join( '' ) + '</select>';
						}
						else if ( field.is( ".element-radiobuttons,.element-checkboxes,.element-selectbox" ) ) {

							var tm_option_titles = field.find( '.tm_option_title' );
							var tm_option_values = field.find( '.tm_option_value' );
							var field_values = [];

							tm_option_titles.each( function ( index, title ) {
								field_values.push( '<option value="' + $.tmEPOAdmin.tm_escape( $( tm_option_values[ index ] ).val() ) + '">' + $( title ).val() + '</option>' );
							} );

							values[ field_index ] = '<select data-element="' + field_index + '" data-section="' + section_id + '" class="cpf-logic-value">' + field_values.join( '' ) + '</select>';

						} else {

							values[ field_index ] = '<input data-element="' + field_index + '" data-section="' + section_id + '" class="cpf-logic-value" type="text" value="">';

						}
					}
				} );

				logicobj[ section_id ] = {
					'values': values
				}

			} );
			$.tmEPOAdmin.pre_element_logic_init_obj = logicobj;
			$.tmEPOAdmin.pre_element_logic_init_obj_options = options;

		},

		element_logic_init: function ( el ) {
			var _el = $( el ),
				is_slider = _el.closest( ".builder_wrapper" ).is( ".tm-slider-wizard" ),
				field_index = $.tmEPOAdmin.find_index( is_slider, _el );
			$.tmEPOAdmin.check_section_logic();
			$.tmEPOAdmin.check_element_logic( _el );
			var logicobj = $.extend( true, {}, $.tmEPOAdmin.get_element_logic_init() );
			var options_pre = $.extend( true, {}, $.tmEPOAdmin.get_element_logic_options_init() ),
				options = [];
			var section_to_find = _el.closest( ".builder_layout .builder_wrapper" );
			var section_id = section_to_find.find( '.tm-builder-sections-uniqid' ).val();

			if ( section_to_find && section_id && logicobj[ section_id ] && logicobj[ section_id ].values[ field_index ] ) {
				delete logicobj[ section_id ].values[ field_index ];
				delete options_pre[ section_id ][ field_index + 1 ];
				delete options_pre[ section_id ][ 0 ];
			} else if ( section_to_find && section_id && options_pre[ section_id ] && options_pre[ section_id ][ 0 ] ) {
				delete options_pre[ section_id ][ 0 ];
			}
			$.each( options_pre, function ( i, c ) {
				if ( c ) {
					$.each( c, function ( i, d ) {
						if ( d ) {
							options.push( d );
						}
					} );
				}
			} );
			if ( ! $.tmEPOAdmin.element_logic_object.init ) {
				$.tmEPOAdmin.element_logic_object.init = true;
			}
			$.tmEPOAdmin.element_logic_object = $.extend( $.tmEPOAdmin.element_logic_object, logicobj );
			$.tmEPOAdmin.logic_append( el, options );

		},

		logic_init: function ( el ) {
			el = $( el );
			$.tmEPOAdmin.check_section_logic( el );
			var options = [],
				logicobj = {};

			var logicobj = $.extend( true, {}, $.tmEPOAdmin.get_element_logic_init( true ) );

			var options_pre = $.extend( true, {}, $.tmEPOAdmin.get_element_logic_options_init( true ) ),
				options = [];
			var section_id = el.find( '.tm-builder-sections-uniqid' ).val();
			if ( el && section_id && logicobj[ section_id ] ) {
				delete logicobj[ section_id ];
				delete options_pre[ section_id ];
			}
			$.each( options_pre, function ( i, c ) {
				if ( c ) {
					$.each( c, function ( i, d ) {
						if ( d ) {
							options.push( d );
						}
					} );
				}
			} );
			if ( ! $.tmEPOAdmin.logic_object.init ) {
				$.tmEPOAdmin.logic_object.init = true;
			}

			$.tmEPOAdmin.logic_object = $.extend( $.tmEPOAdmin.logic_object, logicobj );

			$.tmEPOAdmin.logic_append( el, options );

		},

		logic_check_section_rules: function ( rules ) {
			if ( typeof rules != "object" || rules === null ) {
				rules = {};
			}
			if ( ! ("toggle" in rules) ) {
				rules.toggle = "show";
			}
			if ( ! ("what" in rules) ) {
				rules.what = "any";
			}
			if ( ! ("rules" in rules) ) {
				rules.rules = [];
			}
			var copy = rules;
			var _logic = $.tmEPOAdmin.logic_object;
			$.each( rules.rules, function ( i, _rule ) {
				var section = _rule.section;
				var element = _rule.element;
				var found = ((section in _logic) && (element in _logic[ section ].values)) || (section = element);
				if ( ! found ) {
					delete copy.rules[ i ];
				}
			} );
			copy.rules = $.tm_array_values( copy.rules );
			return copy;
		},

		logic_check_element_rules: function ( rules ) {
			if ( typeof rules != "object" || rules === null ) {
				rules = {};
			}
			if ( ! ("toggle" in rules) ) {
				rules.toggle = "show";
			}
			if ( ! ("what" in rules) ) {
				rules.what = "any";
			}
			if ( ! ("rules" in rules) ) {
				rules.rules = [];
			}
			var copy = rules;
			var _logic = $.tmEPOAdmin.element_logic_object;
			$.each( rules.rules, function ( i, _rule ) {
				var section = _rule.section;
				var element = _rule.element;
				var found = ((section in _logic) && (element in _logic[ section ].values)) || (section = element);
				if ( ! found ) {
					delete copy.rules[ i ];
				}
			} );
			copy.rules = $.tm_array_values( copy.rules );
			return copy;
		},

		logic_append: function ( el, options ) {
			var obj;
			el = $( el );
			if ( el.is( ".bitem" ) ) {
				obj = el.find( ".builder_element_wrap" );
			} else {
				obj = el.find( ".section_elements" );
			}
			var logic = $( obj ).find( ".tm-logic-wrapper" );
			if ( ! options || options.length == 0 ) {
				logic.html( '<div class="errortitle"><p>' + tm_epo_admin.i18n_cannot_apply_rules + '</p></div>' );
				return false;
			}
			var rules;
			try {
				var rawrules = $( obj ).find( ".tm-builder-clogic" ).val() || "null";
				var rulesobj = rawrules.tmparseJSON();
				if ( el.is( ".bitem" ) ) {
					rules = $.tmEPOAdmin.logic_check_element_rules( rulesobj );
				} else {
					rules = $.tmEPOAdmin.logic_check_section_rules( rulesobj );
				}
				$( obj ).find( ".tm-builder-clogic" ).val( JSON.stringify( rules ) );

			} catch ( err ) {
			}
			logic.empty();
			var h = '';
			h = '<div class="tm-row nopadding tm-logic-rule">'
				+ '<div class="tm-cell col-4 tm-logic-element">'
				+ '</div>'
				+ '<div class="tm-cell col-2 tm-logic-operator">'
				+ '</div>'
				+ '<div class="tm-cell col-4 tm-logic-value">'
				+ '</div>'
				+ '<div class="tm-cell col-2 tm-logic-func">'
				+ '<button type="button" class="tc tc-button cpf-add-rule"><i class="tcfa tcfa-plus"></i></button>'
				+ ' <button type="button" class="tc tc-button cpf-delete-rule"><i class="tcfa tcfa-times"></i></button>'
				+ '</div>'
				+ '</div>';
			var rule = $( h ),
				tm_logic_element = $( '<select class="cpf-logic-element">' + options.join( '' ) + '</select>' ),
				operators = '';

			for ( var o in $.tmEPOAdmin.logic_operators ) {
				operators = operators + '<option value="' + o + '">' + $.tmEPOAdmin.logic_operators[ o ] + '</option>';
			}
			operators = $( '<select class="cpf-logic-operator">' + operators + '</select>' );

			rule.find( '.tm-logic-element' ).append( tm_logic_element );
			rule.find( '.tm-logic-operator' ).append( operators );

			if ( ! rules || ! ('rules' in rules) || ! rules.rules.length ) {
				rule.appendTo( logic ).find( '.cpf-logic-element' ).trigger( 'change.cpf', [ el.is( ".bitem" ) ] );
				rule.appendTo( logic ).find( '.cpf-logic-operator' ).trigger( 'change.cpf', [ el.is( ".bitem" ) ] );
			} else {
				var ruleshtml = $( '<div class="temp">' );
				$.each( rules.rules, function ( i, _rule ) {
					if ( typeof(_rule) != 'function' && _rule != null ) {
						var current_rule = rule.clone(),
							set_select = current_rule.find( '.cpf-logic-element' ).find( 'option[data-section="' + _rule.section + '"][value="' + _rule.element + '"]' );
						if ( $( set_select ).length ) {
							$( set_select )[ 0 ].selected = true;
						}
						$.tmEPOAdmin.cpf_logic_element_onchange( current_rule.find( '.cpf-logic-element' ), el.is( ".bitem" ) );
						
						current_rule.find( '.cpf-logic-operator' ).val( _rule.operator );
						$.tmEPOAdmin.cpf_logic_operator_onchange( current_rule.find( '.cpf-logic-operator' ), el.is( ".bitem" ) );
						
						if ( current_rule.find( '.cpf-logic-value' ).is( "select" ) ) {
							current_rule.find( '.cpf-logic-value' ).val( $.tmEPOAdmin.tm_escape( $.tmEPOAdmin.tm_unescape( _rule.value ) ) );
						} else {
							current_rule.find( '.cpf-logic-value' ).val( ($.tmEPOAdmin.tm_unescape( _rule.value )) );
						}
						ruleshtml.append( current_rule );
					}
				} );
				ruleshtml = ruleshtml.children();
				logic.append( ruleshtml );
			}
		},

		logic_get_JSON: function ( s ) {
			var rules = $( s ).find( ".builder-logic-div" );
			var this_section_id = s.find( '.tm-builder-sections-uniqid' ).val();
			var section_logic = {};
			var _toggle = rules.find( ".epo-rule-toggle" ).val();
			var _what = rules.find( ".epo-rule-what" ).val();
			section_logic.section = this_section_id;
			section_logic.toggle = _toggle;
			section_logic.what = _what;
			section_logic.rules = [];
			rules.find( ".tm-logic-wrapper" ).children( ".tm-logic-rule" ).each( function ( i, el ) {
				var cpf_logic_section = $( el ).find( ".cpf-logic-element" ).children( "option:selected" ).attr( 'data-section' );
				var cpf_logic_element = $( el ).find( ".cpf-logic-element" ).val();
				var cpf_logic_operator = $( el ).find( ".cpf-logic-operator" ).val();
				var cpf_logic_value = $( el ).find( ".cpf-logic-value" ).val();
				if ( ! $( el ).find( ".cpf-logic-value" ).is( "select" ) ) {
					cpf_logic_value = $.tmEPOAdmin.tm_escape( cpf_logic_value );
				}

				section_logic.rules.push( {
					"section": cpf_logic_section,
					"element": cpf_logic_element,
					"operator": cpf_logic_operator,
					"value": cpf_logic_value
				} );

			} );
			return JSON.stringify( section_logic );
		},

		element_logic_get_JSON: function ( s ) {
			var rules = $( s ).find( ".builder-logic-div" );
			var this_element_id = s.find( '.tm-builder-element-uniqid' ).val();
			var element_logic = {};
			var _toggle = rules.find( ".epo-rule-toggle" ).val();
			var _what = rules.find( ".epo-rule-what" ).val();
			element_logic.element = this_element_id;
			element_logic.toggle = _toggle;
			element_logic.what = _what;
			element_logic.rules = [];
			rules.find( ".tm-logic-wrapper" ).children( ".tm-logic-rule" ).each( function ( i, el ) {
				var cpf_logic_section = $( el ).find( ".cpf-logic-element" ).children( "option:selected" ).attr( 'data-section' );
				var cpf_logic_element = $( el ).find( ".cpf-logic-element" ).val();
				var cpf_logic_operator = $( el ).find( ".cpf-logic-operator" ).val();
				var cpf_logic_value = $( el ).find( ".cpf-logic-value" ).val();

				element_logic.rules.push( {
					"section": cpf_logic_section,
					"element": cpf_logic_element,
					"operator": cpf_logic_operator,
					"value": cpf_logic_value
				} );

			} );
			return JSON.stringify( element_logic );
		},

		cpf_add_rule: function ( e ) {
			e.preventDefault();
			var _last = $( this ).closest( ".tm-logic-rule" );
			var _clone = _last.tm_clone( true );
			if ( _clone ) {
				_last.after( _clone );
			}
		},

		cpf_delete_rule: function ( e ) {
			e.preventDefault();
			$( this ).trigger( "hideTtooltip" );
			var _wrapper = $( this ).closest( ".tm-logic-wrapper" );
			if ( _wrapper.children().length > 1 ) {
				$( this ).closest( ".tm-logic-rule" ).css( {
					margin: "0 auto"
				} ).animate( {
					opacity: 0,
					height: 0,
					width: 0
				}, 300, function () {
					$( this ).remove();
				} );
			}
		},

		builder_items_sortable_obj: {
			"start": {},
			"end": {}
		},

		section_logic_reindex: function () {
			var l = $.tmEPOAdmin.builder_items_sortable_obj;
			$( ".builder_layout .builder_wrapper" ).each( function ( i, el ) {

				var obj = $( el ).find( ".section_elements" );
				var section_eq = $( el ).index();
				var copy_rules = [];

				var section_rules = $( obj ).find( ".tm-builder-clogic" ).val() || "null";
				section_rules = section_rules.tmparseJSON();

				if ( ! (section_rules && ("rules" in section_rules) && section_rules[ "rules" ].length > 0) ) {

					return true; // skip
				}

				// Element is dragged on this section
				if ( l.end.section_eq == section_eq ) {

					// Getting here means that an element from another section
					// is being dragged on this section
					$.each( section_rules[ "rules" ], function ( i, rule ) {
						var copy = rule;
						if ( rule.element == l.start.element && rule.section == l.start.section ) {
							// delete rule on this element
						}
						else if ( rule.element > l.start.element && rule.secion == l.start.section ) {
							copy.element = parseInt( copy.element ) - 1;
							copy_rules[ i ] = $.tmEPOAdmin.validate_rule( copy, $( el ) );
						}
						else {
							copy_rules[ i ] = $.tmEPOAdmin.validate_rule( copy, $( el ) );
						}
					} );
					copy_rules = $.tm_array_values( copy_rules );
					if ( copy_rules.length == 0 ) {
						$( obj ).find( ".activate-sections-logic" ).val( "" ).trigger( "change.cpf" );
					}
					section_rules[ "rules" ] = copy_rules;
					$( obj ).find( ".tm-builder-clogic" ).val( JSON.stringify( section_rules ) );

					// Element is not dragged on this section
				} else {

					// Getting here means that an element from another section
					// is being dragged on another section that is not the current section
					$.each( section_rules[ "rules" ], function ( i, rule ) {
						var copy = rule;
						if ( l.start.section != "check" ) {
							// Element is not changing sections
							if ( rule.section == l.start.section && rule.section == l.end.section ) {
								// Element belonging to a rule is being dragged
								if ( rule.element == l.start.element ) {
									copy.section = l.end.section;
									copy.element = l.end.element;
								}
								// Element not belonging to a rule is being dragged
								// and breaks the rule
								else if ( rule.element > l.start.element && rule.element <= l.end.element ) {

									copy.element = parseInt( copy.element ) - 1;
								}
								else if ( rule.element < l.start.element && rule.element >= l.end.element ) {

									copy.element = parseInt( copy.element ) + 1;
								}
							}
							// Element is getting dragged off this section
							else if ( rule.section == l.start.section && rule.section != l.end.section ) {
								// Element belonging to a rule is being dragged
								if ( rule.element == l.start.element ) {
									copy.section = l.end.section;
									copy.element = l.end.element;
								}
								// Element not belonging to a rule is being dragged
								// and breaks the rule
								else if ( rule.element > l.start.element ) {
									copy.element = parseInt( copy.element ) - 1;
								}
							}
							// Element is getting dragged on this section
							else if ( rule.section != l.start.section && rule.section == l.end.section ) {
								if ( rule.element >= l.end.element ) {
									copy.element = parseInt( copy.element ) + 1;
								}
							}
						}
						if ( l.end.section == "delete" && copy.element == "delete" ) {
							// rule needs to be deleted
						} else {
							copy_rules[ i ] = $.tmEPOAdmin.validate_rule( copy, $( el ) );
						}
					} );
					copy_rules = $.tm_array_values( copy_rules );
					if ( copy_rules.length == 0 ) {
						$( obj ).find( ".activate-sections-logic" ).val( "" ).trigger( "change.cpf" );
					}
					section_rules[ "rules" ] = copy_rules;
					$( obj ).find( ".tm-builder-clogic" ).val( JSON.stringify( section_rules ) );

				}
			} );
		},

		element_logic_reindex: function () {
			var l = $.tmEPOAdmin.builder_items_sortable_obj;
			$( ".bitem" ).each( function ( i, el ) {

				var obj = $( el ).find( ".builder_element_wrap" );
				var copy_rules = [];
				var element_rules = $( obj ).find( ".tm-builder-clogic" ).val() || "null";

				element_rules = element_rules.tmparseJSON();

				if ( ! (element_rules && ("rules" in element_rules) && element_rules[ "rules" ].length > 0) ) {

					return true; // skip
				}

				$.each( element_rules[ "rules" ], function ( i, rule ) {
					var copy = rule;
					if ( l.start.section != "check" ) {
						// Element is not changing sections
						if ( rule.section == l.start.section && rule.section == l.end.section ) {
							// Element belonging to a rule is being dragged
							if ( rule.element == l.start.element ) {
								//copy.section=l.end.section;
								copy.element = l.end.element;
							}
							// Element not belonging to a rule is being dragged
							// and breaks the rule
							else if ( rule.element > l.start.element && rule.element <= l.end.element ) {
								copy.element = parseInt( copy.element ) - 1;
							}
							else if ( rule.element < l.start.element && rule.element >= l.end.element ) {
								copy.element = parseInt( copy.element ) + 1;
							}
						}
						// Element is getting dragged off its section
						else if ( rule.section == l.start.section && rule.section != l.end.section ) {
							// Element belonging to a rule is being dragged
							if ( rule.element == l.start.element ) {
								copy.section = l.end.section;
								copy.element = l.end.element;
							}
							// Element not belonging to a rule is being dragged
							// and breaks the rule
							else if ( rule.element > l.start.element ) {
								copy.element = parseInt( copy.element ) - 1;
							}
						}
						// Element is getting dragged on this rule's section
						else if ( rule.section != l.start.section && rule.section == l.end.section ) {
							if ( rule.element >= l.end.element ) {
								copy.element = parseInt( copy.element ) + 1;
							}
						}
					}
					if ( l.end.section == "delete" && copy.element == "delete" ) {
						// rule needs to be deleted
					} else {
						copy_rules[ i ] = $.tmEPOAdmin.validate_rule( copy, $( el ) );
					}
				} );
				copy_rules = $.tm_array_values( copy_rules );
				if ( copy_rules.length == 0 ) {
					$( obj ).find( ".activate-element-logic" ).val( "" ).trigger( "change.cpf" );
				}
				element_rules[ "rules" ] = copy_rules;
				$( obj ).find( ".tm-builder-clogic" ).val( JSON.stringify( element_rules ) );
			} );
		},
		validate_rule: function ( rule, bitem ) {
			if ( ! global_variation_object || ! rule || typeof rule != "object" || ! ("element" in rule) || ! ("operator" in rule) || ! ("section" in rule) || ! ("value" in rule) ) {
				return [];//false wrong rule
			} else {
				var section = $( ".tm-builder-sections-uniqid[value='" + rule[ "section" ] + "']" ).closest( ".builder_wrapper" ),
					element = $( section ).find( ".bitem_wrapper" ).children( ".bitem" ).eq( rule[ "element" ] ),
					check = false;

				if ( $( element ).is( ".element-radiobuttons,.element-checkboxes,.element-selectbox" ) ) {

					var tm_option_values = $( element ).find( '.tm_option_value' );

					tm_option_values.each( function ( index, value ) {
						if ( $.tmEPOAdmin.tm_escape( $( value ).val() ) == rule[ "value" ] ) {
							check = true;
							return false;
						} else if ( $( value ).val() == rule[ "value" ] ) {
							rule[ "value" ] = $.tmEPOAdmin.tm_escape( rule[ "value" ] );
							check = true;
							return false;
						}
					} );

				} else if ( $( element ).is( ".element-variations" ) ) {
					$( global_variation_object[ "variations" ] ).each( function ( index, variation ) {
						if ( $.tmEPOAdmin.tm_escape( variation.variation_id ) == rule[ "value" ] ) {
							check = true;
							return false;
						}
					} );
				} else {
					check = true;//other fields always true if they exist
				}

				if ( check ) {
					$( bitem ).removeClass( "tm-wrong-rule" );
					return rule;
				} else {
					$( bitem ).addClass( "tm-wrong-rule" );
					return [];//false
				}
			}
		},
		logic_reindex: function () {
			var l = $.tmEPOAdmin.builder_items_sortable_obj;
			if ( l.start.section == l.end.section && l.start.section_eq == l.end.section_eq && l.start.element == l.end.element ) {
				// Getting here means that dragging did not occur
			} else {
				$.tmEPOAdmin.section_logic_reindex();
				$.tmEPOAdmin.element_logic_reindex();
			}
			$.tmEPOAdmin.builder_items_sortable_obj = { "start": {}, "end": {} };
		},
		logic_reindex_force: function () {
			$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = "check";
			$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = "check";
			$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = "check";
			$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section = "check2";
			$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section_eq = "check2";
			$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].element = "check2";

			$.tmEPOAdmin.section_logic_reindex();
			$.tmEPOAdmin.element_logic_reindex();
			$.tmEPOAdmin.builder_items_sortable_obj = { "start": {}, "end": {} };
		},

		is_element_dragged: false,
		// Elements sortable
		builder_items_sortable: function ( obj ) {
			if ( ! $.tmEPOAdmin.is_original ) {
				return;
			}
			obj.sortable( {
				handle: ".move",//".move,.tm-label,.tm-label-icon",
				cursor: "move",
				items: ".bitem",
				start: function ( e, ui ) {
					ui.placeholder.height( ui.helper.outerHeight() );
					ui.placeholder.width( ui.helper.outerWidth() );
					$.tmEPOAdmin.is_element_dragged = true;
					if ( ! $( ui.item ).hasClass( "ditem" ) ) {
						var builder_wrapper = $( ui.item ).closest( ".builder_wrapper" ),
							is_slider = builder_wrapper.is( ".tm-slider-wizard" ),
							field_index = $.tmEPOAdmin.find_index( is_slider, $( ui.item ) );
						builder_wrapper.addClass( "tm-zindex" ).css( "zIndex", 3 );
						builder_wrapper.find( ".tm_builder_sections" ).val( function ( i, oldval ) {
							return -- oldval;
						} );
						builder_wrapper.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
							if ( ! builder_wrapper.is( ".tm-slider-wizard" ) ) {
								return "";
							}
							return builder_wrapper.find( '.bitem_wrapper' )
								.map( function ( i, e ) {
									return $( e ).children( '.bitem' ).not( '.pl2' ).length;
								} ).get().join( "," );
						} );
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = builder_wrapper.find( ".tm-builder-sections-uniqid" ).val();
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = builder_wrapper.index();
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = field_index;
					} else {
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = "drag";
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = "drag";
						$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = "drag";
					}

					$( ".builder_layout .bitem_wrapper" ).not( ".tma-variations-wrap .bitem_wrapper" ).addClass( "highlight" );
				},
				stop: function ( e, ui ) {
					$.tmEPOAdmin.is_element_dragged = false;
					var builder_wrapper = $( ui.item ).closest( ".builder_wrapper" ),
						is_slider = builder_wrapper.is( ".tm-slider-wizard" ),
						field_index = $.tmEPOAdmin.find_index( is_slider, $( ui.item ) );
					if ( ! $( ui.item ).hasClass( "ditem" ) ) {
						$( ".builder_wrapper.tm-zindex" ).css( "zIndex", "" ).removeClass( "tm-zindex" );
						builder_wrapper.find( ".tm_builder_sections" ).val( function ( i, oldval ) {
							return ++ oldval;
						} );
						builder_wrapper.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
							if ( ! builder_wrapper.is( ".tm-slider-wizard" ) ) {
								return "";
							}
							return builder_wrapper.find( '.bitem_wrapper' )
								.map( function ( i, e ) {
									return $( e ).children( '.bitem' ).not( '.pl2' ).length;
								} ).get().join( "," );
						} );
					}
					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section = builder_wrapper.find( ".tm-builder-sections-uniqid" ).val();
					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section_eq = builder_wrapper.index();
					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].element = field_index;

					$.tmEPOAdmin.builder_reorder_multiple();
					if ( $( ui.item ).hasClass( "ditem" ) ) {
						ui.draggable = ui.item;
						$.tmEPOAdmin.drag_drop( e, ui, $( this ) );
					}
					$.tmEPOAdmin.logic_reindex();
					$( ".builder_layout .bitem_wrapper" ).removeClass( "highlight" );

				},
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				placeholder: 'bitem pl2',

				forceHelperSize: true,
				helper: 'clone',

				cancel: '.panels_wrap,.tma-nomove',
				dropOnEmptyType: true,
				revert: 200,
				connectWith: '.builder_wrapper:not(.tma-nomove) .bitem_wrapper'
			} );
		},

		// Element delete button
		builder_delete_onClick: function () {
			if ( confirm( tm_epo_admin.i18n_builder_delete ) ) {
				var builder_wrapper = $( this ).closest( ".builder_wrapper" ),
					_bitem = $( this ).closest( ".bitem" ),
					is_slider = builder_wrapper.is( ".tm-slider-wizard" ),
					field_index = $.tmEPOAdmin.find_index( is_slider, _bitem );
				builder_wrapper.find( ".tm_builder_sections" ).val( function ( i, oldval ) {
					return -- oldval;
				} );


				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = builder_wrapper.find( ".tm-builder-sections-uniqid" ).val();
				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = builder_wrapper.index();
				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = field_index;

				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section = "delete";
				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section_eq = "delete";
				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].element = "delete";
				$( this ).closest( ".bitem" ).remove();
				$.tmEPOAdmin.logic_reindex();

				builder_wrapper.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
					if ( ! builder_wrapper.is( ".tm-slider-wizard" ) ) {
						return "";
					}
					return builder_wrapper.find( '.bitem_wrapper' )
						.map( function ( i, e ) {
							return $( e ).children( '.bitem' ).not( '.pl2' ).length;
						} ).get().join( "," );
				} );

				$.tmEPOAdmin.builder_reorder_multiple();
			}
		},

		builder_fold_onClick: function ( e ) {
			var $this = $( this );
			if ( $this.is( ".tma-handle" ) ) {
				$this = $this.find( ".fold" );
			}
			var handle_wrap = $this.closest( ".tma-handle-wrap" ),
				handle_wrapper = handle_wrap.find( ".tma-handle-wrapper" ).first();
			if ( ! $this.data( "folded" ) && $this.data( "folded" ) != undefined ) {
				$this.data( "folded", true );
				$this.removeClass( "tcfa-caret-down" ).addClass( "tcfa-caret-up" );
				handle_wrapper.addClass( 'tm-hidden' );
			} else {
				$this.data( "folded", false );
				$this.removeClass( "tcfa-caret-up" ).addClass( "tcfa-caret-down" );
				handle_wrapper.removeClass( 'tm-hidden' );
			}
		},
		builder_section_fold_onClick: function ( e ) {
			var builder_wrapper = $( this ).closest( ".builder_wrapper" );

			if ( ! $( this ).data( "folded" ) ) {
				$( this ).data( "folded", true );
				builder_wrapper.addClass( "tm-hide-bitems" );//hide
				$( this ).removeClass( "tcfa-caret-down" ).addClass( "tcfa-caret-up" );
			} else {
				$( this ).data( "folded", false );
				builder_wrapper.removeClass( "tm-hide-bitems" );//show
				$( this ).removeClass( "tcfa-caret-up" ).addClass( "tcfa-caret-down" );
			}

			$( this ).closest( ".builder_wrapper" ).find( ".float.builder_drag_elements" ).remove();
		},

		// Section delete button
		builder_section_delete_onClick: function () {
			if ( confirm( tm_epo_admin.i18n_builder_delete ) ) {
				$( this ).closest( ".builder_wrapper" ).remove();
				$.tmEPOAdmin.builder_reorder_multiple();
				$( ".builder_layout .builder_wrapper" ).each( function ( i, el ) {
					$.tmEPOAdmin.logic_init( $( el ) );
				} );
				$.tmEPOAdmin.init_sections_check();
				$.tmEPOAdmin.fix_content_float();
				if ( $( this ).closest( ".builder_wrapper" ).is( ".tma-variations-wrap" ) ) {
					$.tmEPOAdmin.toggle_variation_button();
					$.tmEPOAdmin.var_remove( "tm-style-variation-added" );
				}
			}
		},

		// Element plus button
		builder_plus_onClick: function () {
			var s = $.tmEPOAdmin.builder_size(),
				current_size = $( this ).parentsUntil( ".bitem" ).parent().first(),
				x;
			for ( x in s ) {
				if ( current_size.hasClass( s[ x ][ 0 ] ) ) {
					if ( x < 5 ) {
						current_size.removeClass( "" + s[ x ][ 0 ] );
						current_size.addClass( "" + s[ parseInt( parseInt( x ) + 1 ) ][ 0 ] );
						current_size.find( ".size" ).text( s[ parseInt( parseInt( x ) + 1 ) ][ 1 ] );
						current_size.find( ".div_size" ).val( s[ parseInt( parseInt( x ) + 1 ) ][ 0 ] );
					}
					break;
				}
			}
		},

		// Element minus button
		builder_minus_onClick: function () {
			var s = $.tmEPOAdmin.builder_size(),
				current_size = $( this ).parentsUntil( ".bitem" ).parent().first(),
				x;
			for ( x in s ) {
				if ( current_size.hasClass( s[ x ][ 0 ] ) ) {
					if ( x > 0 ) {
						current_size.removeClass( "" + s[ x ][ 0 ] );
						current_size.addClass( "" + s[ parseInt( parseInt( x ) - 1 ) ][ 0 ] );
						current_size.find( ".size" ).text( s[ parseInt( parseInt( x ) - 1 ) ][ 1 ] );
						current_size.find( ".div_size" ).val( s[ parseInt( parseInt( x ) - 1 ) ][ 0 ] );
					}
					break;
				}
			}
		},

		// Section plus button
		builder_section_plus_onClick: function () {
			var s = $.tmEPOAdmin.builder_size(),
				current_size = $( this ).closest( ".builder_wrapper" ),
				x;
			for ( x in s ) {
				if ( current_size.hasClass( s[ x ][ 0 ] ) ) {
					if ( x < 5 ) {
						current_size.removeClass( "" + s[ x ][ 0 ] );
						current_size.addClass( "" + s[ parseInt( parseInt( x ) + 1 ) ][ 0 ] );
						current_size.find( ".btitle .size" ).text( s[ parseInt( parseInt( x ) + 1 ) ][ 1 ] );
						current_size.find( ".tm_builder_sections_size" ).val( s[ parseInt( parseInt( x ) + 1 ) ][ 0 ] );
					}
					break;
				}
			}
		},

		// Section minus button
		builder_section_minus_onClick: function () {
			var s = $.tmEPOAdmin.builder_size(),
				current_size = $( this ).closest( ".builder_wrapper" ),
				x;
			for ( x in s ) {
				if ( current_size.hasClass( s[ x ][ 0 ] ) ) {
					if ( x > 0 ) {
						current_size.removeClass( "" + s[ x ][ 0 ] );
						current_size.addClass( "" + s[ parseInt( parseInt( x ) - 1 ) ][ 0 ] );
						current_size.find( ".btitle .size" ).text( s[ parseInt( parseInt( x ) - 1 ) ][ 1 ] );
						current_size.find( ".tm_builder_sections_size" ).val( s[ parseInt( parseInt( x ) - 1 ) ][ 0 ] );
					}
					break;
				}
			}
		},

		// Section edit button
		builder_section_item_onClick: function () {
			var _bs = $( this ).closest( ".builder_wrapper" );

			$.tmEPOAdmin.check_section_logic( _bs );
			var _current_logic = $.tmEPOAdmin.logic_object;
			$.tmEPOAdmin.logic_init( _bs );
			var _s = $( this ).closest( ".builder_wrapper" ).find( ".section_elements" );

			var _st = _s.find( ".tm-tabs" );
			if ( ! _st.data( 'tm-has-tmtabs' ) ) {
				_st.tmtabs();
			}
			var _c = _s.tm_clone();
			$.tmEPOAdmin.gen_events( _bs );
			var $_html = $.tmEPOAdmin.builder_floatbox_template( {
				"id": "temp_for_floatbox_insert",
				"html": "",
				"title": tm_epo_admin.i18n_edit_settings,
				"uniqid": tm_epo_admin.i18n_section_uniqid + ":" + _s.find( ".tm-builder-sections-uniqid" ).val()
			} );

			var _to = $( "body" ).tm_floatbox( {
				"fps": 1,
				"ismodal": true,
				"refresh": "fixed",
				"width": "80%",
				"height": "80%",
				"classname": "flasho tm_wrapper" + (_bs.is( '.tma-variations-wrap' ) ? ' tma-variations-section' : ''),
				"data": $_html
			} );
			var clicked = false;
			$( ".details_cancel" ).click( function () {
				if ( clicked ) {
					return;
				}
				clicked = true;
				$.tmEPOAdmin.logic_object = _current_logic;
				$.tmEPOAdmin.removeTinyMCE( '.flasho.tm_wrapper' );
				_c.prependTo( _bs ).addClass( "closed" );
				$.tmEPOAdmin.builder_clone_after_events( _c );

				if ( _to ) _to.cancelfunc();
			} );
			$( ".details_update" ).click( function () {
				if ( clicked ) {
					return;
				}
				clicked = true;
				$.tmEPOAdmin.removeTinyMCE( '.flasho.tm_wrapper' );
				_s.find( ".tm-builder-clogic" ).val( $.tmEPOAdmin.logic_get_JSON( _s ) );
				_s.prependTo( _bs ).addClass( "closed" );
				$.tmEPOAdmin.builder_clone_after_events( _s );
				_bs.trigger( "sections_type_onChange.cpf" );
				$.tmEPOAdmin.logic_reindex_force();
				if ( _to ) _to.cancelfunc();
			} );
			_s.appendTo( "#temp_for_floatbox_insert" ).removeClass( "closed" );
			_s.find( '.sections_style' ).trigger( 'change.cpf' );
			$.tmEPOAdmin.addTinyMCE( '.flasho.tm_wrapper' );
		},

		// Element edit button
		builder_item_onClick: function () {
			var bitem = $( this ).closest( ".bitem" );
			var bitemt = bitem.find( ".tm-tabs" );
			if ( ! bitemt.data( 'tm-has-tmtabs' ) ) {
				bitemt.tmtabs();
			}
			$.tmEPOAdmin.panels_sortable( bitem.find( ".panels_wrap" ) );

			$.tmEPOAdmin.check_element_logic( bitem );
			var _current_logic = $.tmEPOAdmin.element_logic_object;
			$.tmEPOAdmin.element_logic_init( bitem );
			var _bs = $( this ).closest( ".hstc2" );
			var _s = $( this ).closest( ".hstc2" ).find( ".inside:first" );
			var _c = _s.tm_clone();
			$.tmEPOAdmin.gen_events( bitem );
			var $_html = $.tmEPOAdmin.builder_floatbox_template( {
				"id": "temp_for_floatbox_insert",
				"html": "",
				"title": tm_epo_admin.i18n_edit_settings,
				"uniqid": (bitem.is( '.element-variations' ) || bitem.find( ".tm-builder-element-uniqid" ).length == 0) ? "" : tm_epo_admin.i18n_element_uniqid + ":" + bitem.find( ".tm-builder-element-uniqid" ).val()
			} );
			var _to = $( "body" ).tm_floatbox( {
				"fps": 1,
				"ismodal": true,
				"refresh": "fixed",
				"width": "80%",
				"height": "80%",
				"classname": "flasho tm_wrapper",
				"data": $_html
			} );
			var clicked = false;
			$( ".details_cancel" ).click( function () {
				if ( clicked ) {
					return;
				}
				clicked = true;

				var pager = _c.find( ".tcpagination" );
				if ( pager.data( "tc-pagination" ) ) {
					pager.tcPagination( "destroy" );
				}

				$.tmEPOAdmin.element_logic_object = _current_logic;
				$.tmEPOAdmin.removeTinyMCE( '.flasho.tm_wrapper' );
				_c.appendTo( _bs );
				_c = _c.parentsUntil( ".bitem" ).parent();
				$.tmEPOAdmin.builder_clone_after_events( _c );
				if ( _to ) _to.cancelfunc();
			} );
			$( ".details_update" ).click( function () {
				if ( clicked ) {
					return;
				}
				clicked = true;
				var pager = _s.find( ".tcpagination" );
				if ( pager.data( "tc-pagination" ) ) {
					pager.tcPagination( "destroy" );
				}
				$.tmEPOAdmin.removeTinyMCE( '.flasho.tm_wrapper' );
				_s.find( ".tm-builder-clogic" ).val( $.tmEPOAdmin.element_logic_get_JSON( _s ) );
				_s.appendTo( _bs );

				$.tmEPOAdmin.builder_clone_after_events( _s );
				_s.find( ".tm-header-title" ).trigger( "changetitle.cpf" );
				$.tmEPOAdmin.logic_reindex_force();
				if ( _to ) _to.cancelfunc();
			} );
			_s.appendTo( "#temp_for_floatbox_insert" );
			if ( bitem.is( '.element-variations' ) ) {
				$.tmEPOAdmin.variations_display_as();
			} else {
				$.tmEPOAdmin.tm_upload();
			}

			$( "#temp_for_floatbox_insert" ).find( '.tm_select_price_type' ).trigger('change.cpf');
			$.tm_tooltip( $( "#temp_for_floatbox_insert" ).find( '.tm-tooltip' ) );
			$.tmEPOAdmin.tm_weekdays( $( "#temp_for_floatbox_insert" ) );
			$.tmEPOAdmin.tm_url();
			$.tmEPOAdmin.tm_qty_selector();
			$.tmEPOAdmin.tm_pricetype_selector();
			$.tmEPOAdmin.addTinyMCE( '.flasho.tm_wrapper' );
			$.tmEPOAdmin.paginattion_init();
		},

		// Add Element draggable to sortable
		drag_drop: function ( event, ui, dropable ) {
			var selected_element = $( ui.draggable ).attr( 'class' ).split( /\s+/ ).filter( function ( item ) {
				return item.indexOf( "element-" ) === - 1 ? "" : item.indexOf( "-element-" ) === - 1 ? item : "";
			} ).toString();
			selected_element = selected_element.replace( /element-/gi, "" );
			if ( selected_element ) {
				$.tmEPOAdmin.builder_clone_element( selected_element, dropable.closest( ".builder_wrapper" ) );
			}
		},

		// Add Element to sortable via Add button
		builder_clone_element: function ( element, wrapper_selector, append_or_prepend ) {
			var _template = $( '.builder_hidden_elements' ).data( 'template' );
			if ( ! _template ) {
				return;
			}
			if ( ! append_or_prepend ) {
				append_or_prepend = "append";
			}
			wrapper_selector = $( wrapper_selector );
			var _clone = $( _template[ 'html' ] ).filter( ".bitem.element-" + element ).tm_clone( true );
			if ( _clone ) {
				_clone.find( '.tm-builder-element-uniqid' ).val( $.tm_uniqid( "", true ) );
				if ( $( ".builder_wrapper" ).length <= 0 ) {
					$.tmEPOAdmin.builder_add_section_onClick();
				}
				_clone.addClass( "appear" );
				$.tmEPOAdmin.set_field_title( _clone );
				if ( wrapper_selector.find( ".bitem_wrapper" ).find( ".ditem" ).length > 0 ) {
					wrapper_selector.find( ".bitem_wrapper" ).find( ".ditem" ).replaceWith( _clone );
				} else {
					if ( append_or_prepend == "append" ) {
						wrapper_selector.find( ".bitem_wrapper" ).not( ".tm-hide" ).append( _clone );
					}
					if ( append_or_prepend == "prepend" ) {
						wrapper_selector.find( ".bitem_wrapper" ).not( ".tm-hide" ).prepend( _clone );
					}

				}
				wrapper_selector.find( ".tm_builder_sections" ).val( function ( i, oldval ) {
					return ++ oldval;
				} );
				wrapper_selector.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
					if ( ! wrapper_selector.is( ".tm-slider-wizard" ) ) {
						return "";
					}
					return wrapper_selector.find( '.bitem_wrapper' )
						.map( function ( i, e ) {
							return $( e ).children( '.bitem' ).not( '.pl2' ).length;
						} ).get().join( "," );
				} );
				_clone.find( ".tm-tabs" ).tmtabs();
				_clone.find( ".tm-header-title" ).data( "id", _clone );
				$.tmEPOAdmin.panels_sortable( _clone.find( ".panels_wrap" ) );

				if ( append_or_prepend == "prepend" || append_or_prepend == "append" ) {
					var is_slider = wrapper_selector.is( ".tm-slider-wizard" ),
						field_index = $.tmEPOAdmin.find_index( is_slider, _clone );

					$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = "drag";
					$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = "drag";
					$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = "drag";

					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section = wrapper_selector.find( ".tm-builder-sections-uniqid" ).val();
					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section_eq = wrapper_selector.index();
					$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].element = field_index;
				}

				$.tmEPOAdmin.check_element_logic( _clone );
				$.tmEPOAdmin.builder_reorder_multiple();
				return _clone;
			}
		},

		// Element clone button
		builder_clone_onClick: function ( e ) {
			e.preventDefault();
			if ( ! confirm( tm_epo_admin.i18n_builder_clone ) ){
				return;
			}
			var _bitem = $( this ).closest( ".bitem" ),
				_label_data = _bitem.data( "original_title" ),
				_clone = _bitem.tm_clone();
			_clone.data( "original_title", _label_data );

			if ( _clone ) {

				_bitem.after( _clone );
				var is_slider = _clone.closest( ".builder_wrapper" ).is( ".tm-slider-wizard" );

				var field_index = $.tmEPOAdmin.find_index( is_slider, _clone );

				_clone.closest( ".builder_wrapper" ).find( ".tm_builder_sections" ).val( function ( i, oldval ) {
					return ++ oldval;
				} );
				_clone.closest( ".builder_wrapper" ).find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
					if ( ! _clone.closest( ".builder_wrapper" ).is( ".tm-slider-wizard" ) ) {
						return "";
					}
					return _clone.closest( ".builder_wrapper" ).find( '.bitem_wrapper' )
						.map( function ( i, e ) {
							return $( e ).children( '.bitem' ).not( '.pl2' ).length;
						} ).get().join( "," );
				} );
				_clone.find( ".tm-header-title" ).data( "id", _clone );
				_clone.find( '.tm-builder-element-uniqid' ).val( $.tm_uniqid( "", true ) );
				_clone.find( ".tcpagination" ).tcPagination( "destroy" );
				$.tmEPOAdmin.builder_clone_after_events( _clone );
				$.tmEPOAdmin.builder_reorder_multiple();
				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section = "clone";
				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].section_eq = "clone";
				$.tmEPOAdmin.builder_items_sortable_obj[ "start" ].element = "clone";
				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section = _bitem.closest( ".builder_wrapper" ).find( ".tm-builder-sections-uniqid" ).val();
				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].section_eq = _bitem.closest( ".builder_wrapper" ).index();
				$.tmEPOAdmin.builder_items_sortable_obj[ "end" ].element = field_index;
				$.tmEPOAdmin.logic_reindex();
			}
		},

		// Section clone button
		builder_section_clone_onClick: function ( e ) {
			e.preventDefault();
			if ( ! confirm( tm_epo_admin.i18n_builder_clone ) ) return;
			var _bitem = $( this ).closest( ".builder_wrapper" );
			var _clone = _bitem.tm_clone();
			if ( _clone ) {
				_clone.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );
				var original_titles = [];
				_bitem.find( ".bitem" ).each( function ( i, el ) {
					original_titles[ i ] = $( el ).data( "original_title" );
				} );
				_bitem.after( _clone );
				_clone.find( ".bitem" ).each( function ( i, el ) {
					$( el ).data( "original_title", original_titles[ i ] );
					$( el ).find( ".tm-header-title" ).data( "id", $( el ) );
				} );

				var style = _clone.find( ".section_elements .sections_type" ).val();

				if ( style == "slider" ) {

					$.tmEPOAdmin.create_slider( _clone );

				}

				$.tmEPOAdmin.builder_reorder_multiple();
				$.tmEPOAdmin.builder_items_sortable( _clone.find( ".bitem_wrapper" ) );
				$.tmEPOAdmin.builder_clone_after_events( _clone );
				_clone.find( '.tm-builder-sections-uniqid' ).val( $.tm_uniqid( "", true ) );
				$.tmEPOAdmin.check_section_logic( _clone );
				$.tmEPOAdmin.check_element_logic();
				$.tmEPOAdmin.logic_init( _clone );
				_clone.addClass( "appear" );
			}
		},

		// Helper : Holds element and sections available sizes
		builder_size: function () {
			var s = [];
			s[ 0 ] = [ 'w25', '1/4' ];
			s[ 1 ] = [ 'w33', '1/3' ];
			s[ 2 ] = [ 'w50', '1/2' ];
			s[ 3 ] = [ 'w66', '2/3' ];
			s[ 4 ] = [ 'w75', '3/4' ];
			s[ 5 ] = [ 'w100', '1/1' ];
			return s;
		},

		// Helper : Creates the html for the edit pop up
		builder_floatbox_template: function ( data ) {
			var out = '', uniqid = '';
			if ( data.uniqid ) {
				uniqid = "<span class=\'tm-element-uniqid\'>" + data.uniqid + "<\/span>";
			}
			out = "<div class=\'header\'><h3>" + data.title + "<\/h3>" + uniqid + "<\/div>" +
				"<div id=\'" + data.id + "\' class=\'float_editbox\'>" +
				data.html + "<\/div>" +
				"<div class=\'footer\'><div class=\'inner\'><button type=\'button\' class=\'tc tc-button details_update\'>" +
				tm_epo_admin.i18n_update +
				"<\/button>&nbsp;<button type=\'button\' class=\'tc tc-button details_cancel\'>" +
				tm_epo_admin.i18n_cancel +
				"<\/button><\/div><\/div>";
			return out;
		},

		builder_floatbox_template_import: function ( data ) {
			var out = '';
			out = "<div class=\'header\'><h3>" + data.title + "<\/h3><\/div>" +
				"<div id=\'" + data.id + "\' class=\'float_editbox\'>" +
				data.html + "<\/div>" +
				"<div class=\'footer\'><div class=\'inner\'><button type=\'button\' class=\'tc tc-button details_cancel\'>" +
				tm_epo_admin.i18n_cancel +
				"<\/button><\/div><\/div>";
			return out;
		},

		// Helper : Renames all fields that contain multiple options
		builder_reorder_multiple: function () {
			var obj;
			var inputArray = $( ".builder_layout" ).find( '[name^="tm_meta\\[tmfbuilder\\]\\[multiple_"]' ).map( function () {
				return $( this ).closest( ".bitem" ).attr( 'class' ).split( ' ' )
					.map( function ( cls ) {
						if ( cls.indexOf( "element-", 0 ) !== - 1 ) {
							return cls;
						}
					} )
					.filter( function ( v, k, el ) {
						if ( v !== null && v !== undefined ) {
							return v;
						}
					} );
			} ).toArray();
			var outputArray = [];
			for ( var i = 0; i < inputArray.length; i ++ ) {
				if ( (jQuery.inArray( inputArray[ i ], outputArray )) == - 1 ) {
					outputArray.push( inputArray[ i ] );
				}
			}
			var id_array = {};
			for ( var key in outputArray ) {
				// Correct ugly extra array protoypes
				if ( typeof(outputArray[ key ]) == 'function' ) {
					continue;
				}
				obj = $( ".builder_layout ." + outputArray[ key ] );
				obj.each(
					function ( i, el ) {
						$( el ).find( ".tm-default-radio" ).each( function ( index, element ) {
							var _m = $( element ).attr( 'name' );
							var __m = /\[[0-9]+\]\[\]/g;
							var __m2 = /\[[0-9]+\]/g;
							if ( _m.match( __m ) != null ) {
								_m = _m.replace( __m, "[" + i + "][]" );
							} else {
								if ( _m.match( __m2 ) != null ) {
									_m = _m.replace( __m2, "[" + i + "]" );
								}
							}
							var _name = _m.replace( /[\[\]]/g, "" );
							if ( _name in id_array ) {
								id_array[ _name ] = parseInt( id_array[ _name ] ) + 1;
							} else {
								id_array[ _name ] = 0;
							}
							var _check = false;
							if ( $( element ).is( ":radio:checked" ) ) {
								_check = true;
							}
							_m = _m + '_temp';
							$( element ).attr( 'name', _m );
							if ( _check ) {
								$( element ).attr( "checked", "checked" ).prop( "checked", true );
							} else {
								$( element ).removeAttr( "checked" ).prop( "checked", false );
							}
						} );

						$( el ).find( "[name]" ).not( ".tm-default-radio" ).attr( 'name', function () {
							var _m = $( this ).attr( 'name' );
							var __m = /\[[0-9]+\]\[\]/g;
							var __m2 = /\[[0-9]+\]/g;
							if ( _m.match( __m ) != null ) {
								_m = _m.replace( __m, "[" + i + "][]" );
							} else {
								if ( _m.match( __m2 ) != null ) {
									_m = _m.replace( __m2, "[" + i + "]" );
								}
							}
							var _name = _m.replace( /[\[\]]/g, "" );
							if ( _name in id_array ) {
								id_array[ _name ] = parseInt( id_array[ _name ] ) + 1;
							} else {
								id_array[ _name ] = 0;
							}
							$( this ).attr( 'id', _name + "_" + id_array[ _name ] );
							return _m;
						} );
					}
				);
			}

			/* preserving checked radios */
			$( ".builder_layout" ).find( ".tm-default-radio" ).each( function ( index, element ) {
				var _n = $( element ).attr( 'name' );
				_n = _n.replace( /_temp/g, "" );
				$( this ).attr( 'name', _n );
			} );

			obj = $( ".builder_layout" ).find( '[name]' ).not( '[name^="tm_meta\\[tmfbuilder\\]\\[multiple_"]' );
			obj.each(
				function ( i, el ) {
					var _name = $( this ).attr( 'name' ).replace( /[\[\]]/g, "" );
					if ( _name in id_array ) {
						id_array[ _name ] = parseInt( id_array[ _name ] ) + 1;
					} else {
						id_array[ _name ] = 0;
					}
					$( el ).attr( 'id', _name + id_array[ _name ] );
				}
			);
			$.tmEPOAdmin.id_array = id_array;
		},

		// Helper : Generates new events after cloning an element
		builder_clone_after_events: function ( _clone ) {
			_clone.find( "input.tm-color-picker" ).spectrum( "destroy" );
			_clone.find( ".sp-replacer" ).remove();
			$.tmEPOAdmin.panels_sortable( _clone.find( ".panels_wrap" ) );
			_clone.find( ".tm-tabs" ).tmtabs();
		},

		// Helper : Generates general events
		gen_events: function ( obj ) {
			if ( ! obj ) {
				obj = $( ".builder_layout " );
			}
			obj.find( "input.tm-color-picker" ).spectrum( {
				showInput: true,
				showInitial: true,
				allowEmpty: true,
				showAlpha: false,
				showPalette: false,
				clickoutFiresChange: true,
				showButtons: false,
				preferredFormat: "hex"
			} );
		},

		paginattion_init: function ( start ) {
			var obj = $( "#temp_for_floatbox_insert" ),
				pager = obj.find( ".tcpagination" );

			if (pager.length===0){
				return;
			}
			var panels_wrap = obj.find( ".panels_wrap" ),
				options_wrap = panels_wrap.find( ".options_wrap" ),
				perpage = parseInt( pager.attr( "data-perpage" ) ),
				total = Math.ceil( options_wrap.length / perpage );

			if ( pager.data( "tc-pagination" ) ) {
				pager.tcPagination( "destroy" );
			}

			if ( start == "last" ) {
				start = total;
			} else if ( start == "current" ) {
				start = $.tmEPOAdmin.pagination_current;
			} else {
				start = 1;
			}
			$.tmEPOAdmin.pagination_current = start;
			pager.tcPagination( {
				totalPages: total,
				startPage: start,
				visiblePages: 10,
				onPageClick: function ( event, page ) {
					$.tmEPOAdmin.paginationOnClick( page, perpage, options_wrap );
				}
			} );

		},
		paginationOnClick: function ( page, perpage, options_wrap ) {
			var page = parseInt( page );
			$.tmEPOAdmin.pagination_current = page;
			options_wrap.addClass( "tm-hidden" );
			for ( var i = (perpage * page) - 1; i >= (perpage * (page - 1)); i -- ) {
				options_wrap.eq( i ).removeClass( "tm-hidden" );
			}
			;
		},

		addTinyMCE: function ( element ) {
			if ( ! $( element ) || typeof tinymce === 'undefined' ) {
				return;
			}
			var getter_tmce = 'excerpt';
			var tmc_defaults = {
				theme: 'modern',
				menubar: false,
				wpautop: true,
				indent: false,
				toolbar1: 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink',
				plugins: 'fullscreen,image,wordpress,wpeditimage,wplink'
			};
			var qt_defaults = {
				buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,fullscreen'
			};
			var init_settings = ((typeof tinyMCEPreInit == 'object') && ('mceInit' in tinyMCEPreInit) && (getter_tmce in tinyMCEPreInit.mceInit)) ? tinyMCEPreInit.mceInit[ getter_tmce ] : tmc_defaults;
			var qt_settings = ((typeof tinyMCEPreInit == 'object') && ('qtInit' in tinyMCEPreInit) && (getter_tmce in tinyMCEPreInit.qtInit)) ? tinyMCEPreInit.qtInit[ getter_tmce ] : qt_defaults;
			var tmc_settings, id, tqt_settings;
			var editor_tools_html = $( '#wp-' + getter_tmce + '-editor-tools' ).html();
			var editor_tools_class = $( '#wp-' + getter_tmce + '-editor-tools' ).attr( 'class' );
			$( element ).find( 'textarea' ).not( ":disabled" ).not( ".tm-no-editor" ).each( function ( i, textarea ) {
				id = $( textarea ).attr( 'id' );
				if ( id ) {
					tmc_settings = $.extend( {}, init_settings, {
						selector: "#" + id
					} );
					tqt_settings = $.extend( {}, qt_settings, {
						id: id
					} );
					if ( typeof tinyMCEPreInit == 'object' ) {
						tinyMCEPreInit.mceInit[ id ] = tmc_settings;
						tinyMCEPreInit.qtInit[ id ] = tqt_settings;
					}
					$( textarea ).addClass( "wp-editor-area" ).wrap( '<div id="wp-' + id + '-wrap" class="wp-core-ui wp-editor-wrap tmce-active tm_editor_wrap"></div>' )
						.before( '<div class="' + editor_tools_class + '">' + editor_tools_html + '</div>' )
						.wrap( '<div class="wp-editor-container"></div>' );
					$( '.tm_editor_wrap' ).find( '.wp-switch-editor' ).each( function ( n, s ) {
						if ( $( s ).attr( 'id' ) ) {
							var aid = $( s ).attr( 'id' ),
								l = aid.length,
								mode = aid.substr( l - 4 );
							$( s ).attr( 'id', id + '-' + mode );
							$( s ).attr( 'data-wp-editor-id', id );
						}
					} );
					$( '.tm_editor_wrap' ).find( '.insert-media' ).attr( 'data-editor', id );

					tinymce.init( tmc_settings );
					if ( QTags && quicktags ) {
						quicktags( tqt_settings );
						QTags._buttonsInit();
					}
					$( textarea ).closest( '.tm_editor_wrap' ).find( 'a.insert-media' ).data( 'editor', id ).attr( 'data-editor', id );
				}
			} );
		},

		removeTinyMCE: function ( element ) {
			if ( ! $( element ) || typeof tinyMCE === 'undefined' ) {
				return;
			}
			var id, _check = '';
			$( element ).find( 'textarea' ).not( ":disabled" ).not( ".tm-no-editor" ).each( function ( i, textarea ) {
				id = $( textarea ).attr( 'id' );
				if ( id && tinyMCE && tinyMCE.editors ) {

					var current_textarea_value = $( textarea ).val(),
						is_tinymce_active = (typeof tinyMCE != "undefined") && tinyMCE.editors[ id ] && ! tinyMCE.editors[ id ].isHidden();

					if ( id in tinyMCE.editors ) {
						_check = tinyMCE.editors[ id ].getContent();
						tinyMCE.editors[ id ].remove();
					}
					$( textarea ).closest( '.tm_editor_wrap' ).find( '.quicktags-toolbar,.wp-editor-tools' ).remove();
					$( textarea ).unwrap().unwrap();


					if ( is_tinymce_active ) {
						if ( _check == '' ) {
							$( textarea ).val( '' );
						} else {
							$( textarea ).val( _check );
						}
					} else {
						$( textarea ).val( current_textarea_value );
					}
				}
			} );
		},

		set_fields_change: function ( obj ) {
			if ( ! obj ) {
				obj = $( ".builder_textfield_price_type" );
			}
			if ( $( obj ).length == 0 ) {
				return;
			}
			obj.each( function ( i, el ) {
				var btxpd = $( this ).closest( ".builder_element_wrap" ).find( ".builder_price_div" );

				if ( $( this ).val() == "currentstep" ) {
					if ( $( btxpd ).length ) btxpd.hide();
				} else {
					if ( $( btxpd ).length ) btxpd.show();
				}
			} );
		},

		set_field_title: function ( obj ) {
			if ( ! obj ) {
				obj = $( ".bitem" );
				obj.each( function ( i, el ) {
					if ( $( el ).find( ".tm-header-title" ).length == 0 ) {
						return true;
					}

					var id = $( el );
					$( el ).find( ".tm-header-title" ).data( "id", id );
					var title = $( el ).find( ".tm-header-title" ).val();
					if ( ! (title === undefined || title == '') ) {
						$( el ).find( ".tm-label" ).html( title );
						//$(el).find(".tm-label-desc").html(original_title);
					}

				} );
			}

			if ( $( obj ).length == 0 || ! obj.is( ".tm-header-title" ) ) {
				return;
			}
			var title = obj.val(),
				el = obj.data( "id" );
			
			if ( title === undefined || title == '' ) {
				$( el ).find( ".tm-label" ).html( '&nbsp;' );
			} else {
				$( el ).find( ".tm-label" ).html( title );
			}
		},

		set_hidden: function () {
			$( '.builder_wrapper' ).each( function ( i, section ) {
				var $this = $( this );
				$this.find( ".tm_builder_sections" ).val( $( this ).find( ".bitem " ).length );
				$this.find( ".tm_builder_section_slides" ).val( function ( i, oldval ) {
					if ( ! $this.is( ".tm-slider-wizard" ) ) {
						return "";
					}
					return $this.find( '.bitem_wrapper' )
						.map( function ( i, e ) {
							return $( e ).children( '.bitem' ).not( '.pl2' ).length;
						} ).get().join( "," );
				} );
				$this.find( ".tm_builder_sections_size" ).val( function () {
					var _size = $( section ).attr( "class" ).split( ' ' )
						.map( function ( cls ) {
							if ( cls.match( /w\d+/g ) !== null ) {
								return cls;
							}
						} )
						.filter( function ( v, k, el ) {
							if ( v !== null && v !== undefined ) {
								return v;
							}
						} );
					return _size[ 0 ];
				} );
				$this.find( ".div_size" ).val( function () {
					var _size = $( this ).closest( ".bitem" ).attr( "class" ).split( ' ' )
						.map( function ( cls ) {
							if ( cls.indexOf( "w", 0 ) !== - 1 ) {
								return cls;
							}
						} )
						.filter( function ( v, k, el ) {
							if ( v !== null && v !== undefined ) {
								return v;
							}
						} );
					return _size[ 0 ];
				} );
			} );
		},

		tm_upload_button_remove_onClick: function ( e ) {
			var input = $( this ).prevAll( "input" ).first(),
				image = $( this ).nextAll( ".tm_upload_image" ).first().find( ".tm_upload_image_img" );
			$( input ).val( "" );
			$( image ).attr( "src", "" );
		},

		variations_display_as: function ( e ) {
			var $this, tm_attribute, tm_terms, tm_changes_product_image;
			if ( e ) {
				if ( $( this ).is( ".tm-changes-product-image" ) ) {
					$this = $( this ).closest( ".tm-attribute" ).find( ".variations-display-as" );
				} else {
					$this = $( this );
				}
			} else {
				$this = $( "#temp_for_floatbox_insert .variations-display-as" );
			}

			$this.each( function ( i, el ) {
				tm_attribute = $( el ).closest( ".tm-attribute" );
				tm_terms = tm_attribute.find( ".tm-term" );
				tm_changes_product_image = tm_attribute.find( ".tm-changes-product-image" );
				var selected_mode = $( el ).val();
				if ( selected_mode == "select" ) {
					tm_attribute.find( ".tma-hide-for-select-box" ).hide().addClass( "tm-row-hidden" );
				} else {
					tm_attribute.find( ".tma-hide-for-select-box" ).show().removeClass( "tm-row-hidden" );
				}
				if ( selected_mode == "image" || selected_mode == "color" || selected_mode == "radiostart" || selected_mode == "radioend" ) {
					tm_attribute.find( ".tma-show-for-swatches" ).show().removeClass( "tm-row-hidden" );
				} else {
					tm_attribute.find( ".tma-show-for-swatches" ).hide().addClass( "tm-row-hidden" );
				}
				tm_terms.each( function ( i2, term ) {
					$( term ).hide().find( ".tma-term-color,.tma-term-image,.tma-term-custom-image" ).hide();
					switch ( selected_mode ) {
						case "select":
							if ( tm_changes_product_image.val() == "images" ) {
								tm_changes_product_image.val( "" );
							}
							tm_changes_product_image.children( "option[value='images']" ).attr( 'disabled', 'disabled' ).hide();
							if ( tm_changes_product_image.val() == "custom" ) {
								$( term ).show().find( ".tma-term-custom-image" ).show();
							}

							break;
						case "radio":
							if ( tm_changes_product_image.val() == "images" ) {
								tm_changes_product_image.val( "" );
							}
							tm_changes_product_image.children( "option[value='images']" ).attr( 'disabled', 'disabled' ).hide();
							if ( tm_changes_product_image.val() == "custom" ) {
								$( term ).show().find( ".tma-term-custom-image" ).show();
							}
							break;
						case "image":
						case "radiostart":
						case "radioend":
							tm_changes_product_image.children( "option[value='images']" ).removeAttr( "disabled" ).show();
							$( term ).show().find( ".tma-term-image" ).show();
							if ( tm_changes_product_image.val() == "custom" ) {
								$( term ).show().find( ".tma-term-custom-image" ).show();
							}
							break;
						case "color":
							if ( tm_changes_product_image.val() == "images" ) {
								tm_changes_product_image.val( "" );
							}
							tm_changes_product_image.children( "option[value='images']" ).attr( 'disabled', 'disabled' ).hide();
							if ( tm_changes_product_image.val() == "custom" ) {
								$( term ).show().find( ".tma-term-custom-image" ).show();
							}
							$( term ).show().find( ".tma-term-color" ).show();
							break;
					}

				} );
			} );
		},
		tm_upload: function ( e ) {
			var $this = $( "#temp_for_floatbox_insert" ),
				$use_images_all = $this.find( ".use_images" ).not( ".tm-changes-product-image" ),
				$use_color_all = $this.find( ".use_colors" ),
				$use_div_images = $this.find( ".tm-use-images" ),
				$use_div_colors = $this.find( ".tm-use-colors" ),
				$use_imagesp_all = $this.find( ".tm-changes-product-image" ),
				$use_lightbox = $( "#temp_for_floatbox_insert .tm-show-when-use-images" ),
				tm_upload = $this.find( ".builder_element_wrap" ).find( ".tm_upload_button" ).not( ".tm_upload_buttonp,.tm_upload_buttonl" ),
				tm_upload_image = $this.find( ".builder_element_wrap" ).find( ".tm_upload_image" ).not( ".tm_upload_imagep,.tm_upload_imagel" ),
				tm_uploadp = $this.find( ".builder_element_wrap" ).find( ".tm_upload_buttonp" ),
				tm_upload_imagep = $this.find( ".builder_element_wrap" ).find( ".tm_upload_imagep" ),
				tm_uploadl = $this.find( ".builder_element_wrap" ).find( ".tm_upload_buttonl" ),
				tm_upload_imagel = $this.find( ".builder_element_wrap" ).find( ".tm_upload_imagel" ),
				tm_cell_images = $this.find( ".builder_element_wrap" ).find( ".tm_cell_images" ),
				tm_color_picker = $this.find( ".builder_element_wrap .panels_wrap" ).find( ".sp-replacer" );

			tm_upload.hide();
			tm_upload_image.hide();
			tm_uploadp.hide();
			tm_upload_imagep.hide();
			tm_uploadl.hide();
			tm_upload_imagel.hide();
			tm_cell_images.hide();
			tm_color_picker.hide();

			if ($use_images_all.val() != "" && $use_color_all.val() != ""){
				$use_div_colors.show();
				$use_div_images.show();
			}
			if ($use_images_all.val() == "" && $use_color_all.val() == ""){
				$use_div_colors.show();
				$use_div_images.show();
			}
			if ($use_images_all.val() != "" && $use_color_all.val() == ""){
				$use_div_colors.hide();
				$use_div_images.show();
			}
			if ($use_images_all.val() == "" && $use_color_all.val() != ""){
				$use_div_colors.show();
				$use_div_images.hide();
			}

			if ( $use_imagesp_all.val() == "images" && $use_images_all.val() == "" ) {
				var tm_option_image = $this.find( ".tm_option_image" ).not( ".tm_option_imagep" ),
					tm_option_imagep = $this.find( ".tm_option_imagep" ),
					tm_upload_imagep_img = $this.find( ".tm_upload_imagep .tm_upload_image_img" );
				$use_imagesp_all.val( "custom" );
				tm_option_image.each( function ( i, el ) {
					tm_option_imagep.eq( i ).val( $( this ).val() );
					tm_upload_imagep_img.attr( "src", $( this ).val() );
				} );
			}

			if ( ! $use_images_all.length || $use_images_all.val() != "images" ) {
				if ( $use_imagesp_all.val() == "images" ) {
					$use_imagesp_all.val( "" );
				}
				$use_imagesp_all.find( "option[value='images']" ).attr( 'disabled', 'disabled' ).hide();
			} else {
				$use_imagesp_all.find( "option[value='images']" ).removeAttr( 'disabled' ).show();
			}
			if ( ( $use_images_all.val() == "images" || $use_images_all.val() == "start" || $use_images_all.val() == "end" ) || ( $use_images_all.val() == "images" && $use_imagesp_all.val() == "images") ) {
				tm_upload.show();
				tm_upload_image.show();
				tm_cell_images.show();
			}
			if ( $use_imagesp_all.val() == "custom" ) {
				tm_uploadp.show();
				tm_upload_imagep.show();
				tm_cell_images.show();
			}
			if ( $use_images_all.val() == "images" ) {
				$use_lightbox.show();
				if ( $( "#temp_for_floatbox_insert .tm-use-lightbox" ).val() == "lightbox" ) {
					tm_uploadl.show();
					tm_upload_imagel.show();
				}
			} else {
				$use_lightbox.hide();
			}

			if ( ( $use_color_all.val() == "color" || $use_color_all.val() == "start" || $use_color_all.val() == "end" ) ) {
				tm_cell_images.show();
				tm_color_picker.show();
			}

		},

		tm_weekdays: function ( e ) {
			var obj;
			if ( e ) {
				obj = $( e );
			} else {
				obj = $( "body" );
			}

			obj.find( ".tm-weekdays" ).each( function ( i, el ) {
				var val = $( el ).val(),
					values = val.split( "," ),
					wrap = $( el ).next( ".tm-weekdays-picker-wrap" ),
					pickers = $( wrap ).find( ".tm-weekday-picker" );

				pickers.each( function ( x, picker ) {
					if ( values.indexOf( $( picker ).val() ) != - 1 ) {
						$( picker ).attr( "checked", "checked" ).prop( "checked", true );
						$( picker ).closest( ".tm-weekdays-picker" ).addClass( "tm-checked" );
					} else {
						$( picker ).removeAttr( "checked" ).prop( "checked", false );
						$( picker ).closest( ".tm-weekdays-picker" ).removeClass( "tm-checked" );
					}
				} );
			} );

		},

		tm_weekday_picker: function ( e ) {
			var weekdays = $( this ).closest( '.tm-weekdays-picker-wrap' ).prev( '.tm-weekdays' ),
				values = $( weekdays ).val().split( "," ),
				c = values.indexOf( $( this ).val() );
			if ( $( this ).is( ":checked" ) ) {
				if ( c == - 1 ) {
					values.push( $( this ).val() );
				}
			} else {
				if ( c != - 1 ) {
					values.splice( c, 1 );
				}
			}
			values = $.map( values, function ( item ) {
				return item === '' ? null : item
			} );
			$( weekdays ).val( values.join( "," ) );
			$.tmEPOAdmin.tm_weekdays( $( weekdays ).parent() );
		},

		tm_qty_selector: function ( e ) {
			var $this, $use_url;
			if ( e ) {
				$use_url = $( this );
			} else {
				$use_url = $( "#temp_for_floatbox_insert .tm-qty-selector" );
			}
			$this = $( "#temp_for_floatbox_insert" );
			var use_url = $this.find( ".builder_element_wrap" ).find( ".tm-show-for-quantity" );
			if ( $use_url.val() != "" ) {
				use_url.show();
			} else {
				use_url.hide();
			}
		},

		tm_pricetype_selector: function ( e ) {
			var $this, $use_url;
			if ( e ) {
				$use_url = $( this );
			} else {
				$use_url = $( "#temp_for_floatbox_insert .tm-pricetype-selector" );
			}
			$this = $( "#temp_for_floatbox_insert" );
			var use_url = $this.find( ".builder_element_wrap" ).find( ".tm-show-for-per-chars" );
			if ( $use_url.val() == "charnon" || $use_url.val() == "charnonnospaces" || $use_url.val() == "charpercentnon" || $use_url.val() == "charpercentnonnospaces" ) {
				use_url.show();
			} else {
				use_url.hide();
			}
		},

		tm_url: function ( e ) {
			var $this = $( "#temp_for_floatbox_insert" ), 
				$use_url = $( this ),
				use_url = $this.find( ".builder_element_wrap" ).find( ".tm_cell_url" );

			if ( !e ) {
				$use_url = $( "#temp_for_floatbox_insert .use_url" );
			}
			
			if ( $use_url.val() == "url" ) {
				use_url.show();
			} else {
				use_url.hide();
			}
		},

		upload: function ( e ) {
			e.preventDefault();
			if ( wp && wp.media ) {
				var _this = $( this ).prev( "input" ),
					_this_image = $( this ).nextAll( ".tm_upload_image" ).first().find( "img" );
				if ( _this.data( 'tm_upload_frame' ) ) {
					_this.data( 'tm_upload_frame' ).open();
					return;
				}
				var insertImage = wp.media.controller.Library.extend( {
					defaults: _.defaults( {
						id: 'insert-image',
						title: 'Insert Image Url',
						allowLocalEdits: true,
						displaySettings: true,
						displayUserSettings: true,
						multiple: true,
						type: 'image'//audio, video, application/pdf, ... etc
					}, wp.media.controller.Library.prototype.defaults )
				} );
				var $tm_upload_frame = wp.media( {
					button: { text: 'Select' },
					state: 'insert-image',
					states: [
						new insertImage()
					]
				} );

				$tm_upload_frame.on( 'close', function () {
					return;
				} );
				$tm_upload_frame.on( 'select', function () {
					var state = $tm_upload_frame.state( 'insert-image' );
					var selection = state.get( 'selection' );

					if ( ! selection ) return;

					_this_image.attr( 'src', '' );
					_this.val( '' );

					selection.each( function ( attachment ) {
						var display = state.display( attachment ).toJSON();
						var obj_attachment = attachment.toJSON()
						var caption = obj_attachment.caption, options, html;

						// If captions are disabled, clear the caption.
						if ( ! wp.media.view.settings.captions ) {
							delete obj_attachment.caption;
						}
						display = wp.media.string.props( display, obj_attachment );

						options = {
							id: obj_attachment.id,
							post_content: obj_attachment.description,
							post_excerpt: caption
						};

						if ( display.linkUrl ) {
							options.url = display.linkUrl;
						}
						if ( 'image' === obj_attachment.type ) {
							html = wp.media.string.image( display );
							_.each( {
								align: 'align',
								size: 'image-size',
								alt: 'image_alt'
							}, function ( option, prop ) {
								if ( display[ prop ] ) {
									options[ option ] = display[ prop ];
								}
							} );
							if ( options[ 'image-size' ] && attachment.attributes.sizes[ options[ 'image-size' ] ] ) {
								options.url = attachment.attributes.sizes[ options[ 'image-size' ] ].url;
							}
						} else if ( 'video' === obj_attachment.type ) {
							html = wp.media.string.video( display, obj_attachment );
						} else if ( 'audio' === obj_attachment.type ) {
							html = wp.media.string.audio( display, obj_attachment );
						} else {
							html = wp.media.string.link( display );
							options.post_title = display.title;
						}

						//attach info to attachment.attributes object
						/*attachment.attributes['nonce'] = wp.media.view.settings.nonce.sendToEditor;
						 attachment.attributes['attachment'] = options;
						 attachment.attributes['html'] = html;
						 attachment.attributes['post_id'] = wp.media.view.settings.post.id;*/

						_this_image.attr( 'src', options.url );
						_this.val( options.url );
					} );
				} );

				$tm_upload_frame.on( 'open', function () {
					var selection = $tm_upload_frame.state().get( 'library' ).toJSON(),
						isinit = true;
					$.each( selection, function ( i, _el ) {
						if ( _el.url == _this.val() ) {
							var attachment = wp.media.attachment( _el.id );
							$tm_upload_frame.state().get( 'selection' ).add( attachment ? [ attachment ] : [] );
							$( '.attachment-display-settings' ).find( 'select.size' ).val( 'full' );
							isinit = false;
						} else if ( _el.sizes ) {
							$.each( _el.sizes, function ( s, size ) {
								if ( size.url == _this.val() ) {
									var attachment = wp.media.attachment( _el.id );
									$tm_upload_frame.state().get( 'selection' ).add( attachment ? [ attachment ] : [] );
									$( '.attachment-display-settings' ).find( 'select.size' ).val( s );
									isinit = false;
								}
							} );
						}
						if ( isinit ) {
							$( '.attachment-display-settings' ).find( 'select.size' ).val( 'full' );
						}
					} );
				} );
				_this.data( 'tm_upload_frame', $tm_upload_frame );
				$tm_upload_frame.open();
			} else {
				return false;
			}
		}
	}

	var _tm_ajax_check = 0;

	function tm_license_check( action ) {
		if ( _tm_ajax_check == 0 ) {
			_tm_ajax_check = 0
			$( '.tm-license-button' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff url(' + tm_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
					opacity: 0.6
				}
			} );
			var data = {
				action: 'tm_' + action + '_license',
				username: $( '#tm_epo_envato_username' ).val(),
				key: $( '#tm_epo_envato_purchasecode' ).val(),
				api_key: $( '#tm_epo_envato_apikey' ).val(),
				security: tm_epo_admin.settings_nonce
			};
			$.post( tm_epo_admin.ajax_url, data, function ( response ) {
				var html;
				if ( ! response || response == - 1 ) {
					html = tm_epo_admin.i18n_invalid_request;
				} else if ( response && response.message && response.result
					&& (response.result == '-3' || response.result == '-2'
					|| response.result == 'wp_error' || response.result == 'server_error') ) {
					html = response.message;
				} else if ( response && response.message && response.result && (response.result == '4') ) {
					html = response.message;
				} else {
					html = '';
				}
				$( '.tm-license-result' ).html( html );
				$( '.tm-license-button' ).unblock();
			}, 'json' )
				.always( function ( response ) {
					$( '.tm-license-button' ).unblock();
					_tm_ajax_check = 0;
					if ( response && response.result && (response.result == '4') ) {
						if ( action == 'activate' ) {
							$( '.tm-deactivate-license' ).removeClass( 'tm-hidden' );
							$( '.tm-activate-license' ).removeClass( 'tm-hidden' ).addClass( 'tm-hidden' );
						}
						if ( action == 'deactivate' ) {
							$( '.tm-deactivate-license' ).removeClass( 'tm-hidden' ).addClass( 'tm-hidden' );
							$( '.tm-activate-license' ).removeClass( 'tm-hidden' );
						}
					}
				} );
		}
	}

	function tm_display_settings( select ) {
		var val = select.val(),
			row1 = $( '#tm_epo_options_placement' ).closest( 'tr' ),
			row2 = $( '#tm_epo_totals_box_placement' ).closest( 'tr' ),
			row3 = $( '#tm_epo_options_placement_custom_hook' ).closest( 'tr' ),
			row4 = $( '#tm_epo_totals_box_placement_custom_hook' ).closest( 'tr' );


		if ( val == "action" ) {
			row1.hide();
			row2.hide();
			row3.hide();
			row4.hide();
		} else {
			row1.show();
			row2.show();
			tm_options_placement_settings( $( '#tm_epo_options_placement' ) );
			tm_totals_box_placement_settings( $( '#tm_epo_totals_box_placement' ) );
		}
	}

	function tm_options_placement_settings( select ) {
		var val = select.val(),
			row1 = $( '#tm_epo_options_placement_custom_hook' ).closest( 'tr' );
		if ( val == "custom" ) {
			row1.show();
		} else {
			row1.hide();
		}
	}

	function tm_epo_floating_totals_box_settings( select ) {
		var val = select.val(),
			row1 = $( '#tm_epo_floating_totals_box_visibility' ).closest( 'tr' ),
			row2 = $( '#tm_epo_floating_totals_box_add_button' ).closest( 'tr' );
		if ( val == "disable" ) {
			row1.hide();
			row2.hide();
		} else {
			row1.show();
			row2.show();
		}
	}

	function tm_totals_box_placement_settings( select ) {
		var val = select.val(),
			row1 = $( '#tm_epo_totals_box_placement_custom_hook' ).closest( 'tr' );

		if ( val == "custom" ) {
			row1.show();
		} else {
			row1.hide();
		}
	}

	function tm_css_styles_settings( select ) {
		var val = select.val(),
			row1 = $( '#tm_epo_css_styles_style' ).closest( 'tr' );
		if ( val == "on" ) {
			row1.show();
		} else {
			row1.hide();
		}
	}

	function tm_epo_css_selected_border( select ) {
		var row = $( select ).closest( 'td' );
		row.css( 'position', 'relative' ).append( '<div class="tm-border-type"></div>' );
	}

	function tm_epo_css_selected_border_settings( select ) {
		var val = select.val(),
			border = $( '.tm-border-type' );
		border.removeClass( 'square round shadow thinline' ).addClass( val );
	}

	function tc_find_row( obj ) {
		return obj.closest( 'tr' );
	}

	function show_sub_section( mitem, item, inputs, table ) {
		tc_find_row( inputs ).addClass( 'tm-hidden' );
		tc_find_row( inputs.filter( '.' + mitem ) ).removeClass( 'tm-hidden' );
		table.find( '.tm-section-desc .tm-section-menu-item' ).removeClass( 'active' );
		item.addClass( 'active' );
	}

	$( document ).ready( function () {
		$.tmEPOAdmin.initialitize();
		var bulk_action_selector_top = $( '#bulk-action-selector-top' );
		if ( bulk_action_selector_top.length > 0 ) {
			var found = false;
			bulk_action_selector_top.children( 'option' ).each( function ( i, o ) {
				if ( $( o ).val() === "tcline" ) {
					found = true;
					$( o ).replaceWith( $( '<optgroup class="tc-bulk-opt" label="' + $( o ).text() + '">' ) );
				} else if ( found && ( $( o ).val() !== "tcclear" && $( o ).val() !== "tcclearexclude" && $( o ).val() !== "tcclearexcludeadd" ) ) {
					$( o ).appendTo( $( '.tc-bulk-opt' ) );
				}
				if ( $( o ).val() === "tcline2" ) {
					$( o ).remove();
				}
			} );
		}
		var tm_settings_wrap = $( '.tm-settings-wrap' );
		if ( tm_settings_wrap.length > 0 ) {
			var tm_settings_wrap_checkbox = tm_settings_wrap.find( ':checkbox' );
			tm_settings_wrap_checkbox.closest( 'label' ).addClass( 'tm-epo-style-wrapper-label' );
			tm_settings_wrap_checkbox.wrap( '<span class="tm-epo-style-wrapper tc"></span>' );
			tm_settings_wrap_checkbox.after( '<span class="tc-label tm-epo-style tc"></span>' );

			$( window ).on( "tc-opentab.tmtabs tc-isopentab.tmtabs", function ( e, o ) {
				var items = o.table.find( '.tm-section-desc .tm-section-menu-item' );

				if ( items.length > 0 ) {
					var mitem = Cookies.get( 'tmadminextratab' ),
						item = items.filter( '[data-menu="' + mitem + '"]' ),
						inputs = o.table.find( 'select,input,text,textarea' );

					if ( ! mitem || item.length === 0 ) {
						item = $( items ).eq( 0 );
						mitem = item.attr( 'data-menu' );
					}
					show_sub_section( mitem, item, inputs, o.table );
				}
			} );
			$( document ).on( 'click', '.tm-section-menu-item', function ( e ) {
				var item = $( this ),
					mitem = item.attr( 'data-menu' ),
					table = item.closest( '.tm-tab' ),
					inputs = table.find( 'select,input,text,textarea' );
				show_sub_section( mitem, item, inputs, table );
				Cookies.set( 'tmadminextratab', mitem, { expires: 7, path: '' } );
			} );
			$( window ).on( "tc-tmtabs-clicked", function ( e, o ) {
				var items = o.table.find( '.tm-section-desc .tm-section-menu-item' );
				if ( items.length > 0 ) {
					var mitem = Cookies.get( 'tmadminextratab' ),
						c = Cookies.get( 'tmadminextratab-context' ),
						con = o.header.attr( o.options.dataattribute ),
						items = o.table.find( '.tm-section-desc .tm-section-menu-item' ),
						item,
						inputs = o.table.find( 'select,input,text,textarea' );

					if ( mitem && c == con ) {
						item = items.filter( '[data-menu="' + mitem + '"]' );
						//show_sub_section(mitem,item,inputs,o.table);
					} else {
						item = $( items ).eq( 0 );
						mitem = item.attr( 'data-menu' );
					}
					if ( item.length > 0 && ! item.is( '.active' ) ) {
						show_sub_section( mitem, item, inputs, o.table );
						Cookies.set( 'tmadminextratab-context', con );
					}
				}
			} );

			$( document ).on( 'click', '.tc-save-button', function ( e ) {
				e.preventDefault();

				var form = $( this ).closest( 'form' );
				form.block( {
					message: null,
					overlayCSS: {
						background: '#fff url(' + tm_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
						opacity: 0.6
					}
				} );
				var security= '&security='+tm_epo_admin.settings_nonce;
				$.post( tm_epo_admin.ajax_url, form.serialize() + '&save=save&action=tm_save_settings' + security , function ( response ) {
					if ( response ) {
						if ( response.error == 1 ) {
							toastr.error( response.message, tm_epo_admin.i18n_epo );
						} else {
							toastr.success( response.message, tm_epo_admin.i18n_epo );
						}
					}
				}, 'json' )
					.always( function ( response ) {
						form.unblock();
					} );

			} );

			$( ".tm-settings-wrap .tm-tabs" ).tmtabs();


			$( '.tm-activate-license' ).on( 'click', function ( e ) {
				e.preventDefault();
				tm_license_check( 'activate' );
			} );
			$( '.tm-deactivate-license' ).on( 'click', function ( e ) {
				e.preventDefault();
				tm_license_check( 'deactivate' );
			} );

			$( '#tm_epo_display' ).on( 'change', function ( e ) {
				tm_display_settings( $( this ) );
			} );
			$( '#tm_epo_options_placement' ).on( 'change', function ( e ) {
				tm_options_placement_settings( $( this ) );
			} );
			$( '#tm_epo_totals_box_placement' ).on( 'change', function ( e ) {
				tm_totals_box_placement_settings( $( this ) );
			} );
			$( '#tm_epo_css_styles' ).on( 'change', function ( e ) {
				tm_css_styles_settings( $( this ) );
			} );
			$( '#tm_epo_css_selected_border' ).on( 'change', function ( e ) {
				tm_epo_css_selected_border_settings( $( this ) );
			} );
			$( '#tm_epo_floating_totals_box' ).on( 'change', function ( e ) {
				tm_epo_floating_totals_box_settings( $( this ) );
			} );
			tm_display_settings( $( '#tm_epo_display' ) );
			tm_options_placement_settings( $( '#tm_epo_options_placement' ) );
			tm_totals_box_placement_settings( $( '#tm_epo_totals_box_placement' ) );
			tm_css_styles_settings( $( '#tm_epo_css_styles' ) );
			tm_epo_css_selected_border( $( '#tm_epo_css_selected_border' ) );
			tm_epo_css_selected_border_settings( $( '#tm_epo_css_selected_border' ) );
			tm_epo_floating_totals_box_settings( $( '#tm_epo_floating_totals_box' ) );

			$( document ).on( "click.cpf", ".tm-mn-movetodir,.tm-mn-deldir,.tm-mn-delfile", function ( e ) {
				e.preventDefault();
				var $this = $( this ),
					forminp_tm_html = $( '.forminp-tm_html' );

				if ( forminp_tm_html.length > 0 ) {
					if ( forminp_tm_html.data( 'doing_ajax' ) ) {
						return;
					}
					if ( $this.is( '.tm-mn-deldir' ) && ! confirm( tm_epo_admin.i18n_mn_delete_folder ) ) {
						return;
					} else if ( $this.is( '.tm-mn-delfile' ) && ! confirm( tm_epo_admin.i18n_mn_delete_file ) ) {
						return;
					}
					$this.prepend( '<i class="tm-icon tcfa tcfa-refresh tcfa-spin"></i>' );

					forminp_tm_html.data( 'doing_ajax', 1 ).block( {
						message: null,
						overlayCSS: {
							background: '#fff url(' + tm_epo_admin.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
							opacity: 0.6
						}
					} );
					var action = 'tm_mn_movetodir',
						data = {
							action: action,
							dir: $this.attr( 'data-tm-dir' ),
							security: tm_epo_admin.settings_nonce
						};

					if ( $this.is( '.tm-mn-deldir' ) ) {
						data.action = 'tm_mn_deldir';
						data.tmdir = $this.attr( 'data-tm-deldir' );
					} else if ( $this.is( '.tm-mn-delfile' ) ) {
						data.action = 'tm_mn_delfile';
						data.tmfile = $this.attr( 'data-tm-delfile' );
						data.tmdir = $this.attr( 'data-tm-deldir' );
					}
					$.post( tm_epo_admin.ajax_url, data, function ( response ) {
						if ( response && response.result && response.result != '' ) {
							forminp_tm_html.html( response.result );
						} else {
							if ( response && response.error && response.message ) {
								var $_html = $.tmEPOAdmin.builder_floatbox_template_import( {
									"id": "temp_for_floatbox_insert",
									"html": '<div class="tm-inner">' + response.message + '</div>',
									"title": tm_epo_admin.i18n_error_title
								} );
								var temp_floatbox = $( "body" ).tm_floatbox( {
									"fps": 1,
									"ismodal": true,
									"refresh": "fixed",
									"width": "50%",
									"height": "300px",
									"classname": "flasho tm_wrapper tm-error",
									"data": $_html
								} );
								$( ".details_cancel" ).click( function () {
									if ( temp_floatbox ) temp_floatbox.cancelfunc();
								} );
							}
						}
					}, 'json' )
						.always( function ( response ) {
							forminp_tm_html.data( 'doing_ajax', 0 ).unblock();
							$this.find( '.tm-icon' ).remove();
						} );


				}
			} );

		}
		$.tm_tooltip();
	} );
})( jQuery );