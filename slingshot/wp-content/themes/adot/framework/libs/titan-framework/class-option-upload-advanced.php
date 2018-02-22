<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TitanFrameworkOptionUploadAdvanced extends TitanFrameworkOption {

	private static $firstLoad = true;

	public $defaultSecondarySettings = array(
		'size' => 'full', // The size of the image to use in the generated CSS
		'placeholder' => '', // show this when blank
	);


	/**
	 * Constructor
	 *
	 * @return	void
	 * @since	1.5
	 */
	function __construct( $settings, $owner ) {
		parent::__construct( $settings, $owner );

		add_filter( 'tf_generate_css_upload_' . $this->getOptionNamespace(), array( $this, 'generateCSS' ), 10, 2 );
	}


	/**
	 * Generates CSS for the font, this is used in TitanFrameworkCSS
	 *
	 * @param	string $css The CSS generated
	 * @param	TitanFrameworkOption $option The current option being processed
	 * @return	string The CSS generated
	 * @since	1.5
	 */
	public function generateCSS( $css, $option ) {
		if ( $this->settings['id'] != $option->settings['id'] ) {
			return $css;
		}

		$value = $this->getFramework()->getOption( $option->settings['id'] );

		if ( empty( $value ) ) {
			return $css;
		}

		if ( is_numeric( $value ) ) {
			$size = ! empty( $option->settings['size'] ) ? $option->settings['size'] : 'thumbnail';
			$attachment = wp_get_attachment_image_src( $value, $size );
			$value = $attachment[0];
		}

		$css .= "\$" . $option->settings['id'] . ": url(" . $value . ");";

		if ( ! empty( $option->settings['css'] ) ) {
			// In the css parameter, we accept the term `value` as our current value,
			// translate it into the SaSS variable for the current option
			$css .= str_replace( 'value', '#{$' . $option->settings['id'] . '}', $option->settings['css'] );
		}

		return $css;
	}

	/*
	 * Display for options and meta
	 */
	public function display() {
		self::createUploaderScript();

		$this->echoOptionHeader();

		$previewImage = '';

		// display the preview image
		$value = $this->getValue();
		if ( is_numeric( $value ) ) {
			// gives us an array with the first element as the src or false on fail
			$value = wp_get_attachment_image_src( $value, array( 150, 150 ) );
		}else if (filter_var($value, FILTER_VALIDATE_URL) === TRUE){
			$value = $this->getValue();
		}else  {
			$value = explode( ',', $this->getValue());
		}

		if ( ! is_array( $value ) ) {
			if ( ! empty( $value ) ) {
				$previewImage = "<i class='dashicons dashicons-no-alt remove'></i><img src='" . esc_url( $value ) . "'/>";
			}
			echo "<div class='thumbnail tf-image-preview'>" . $previewImage . "</div>";
		} else {
			foreach ($value as $k => $v) {
				$id = $v;
				if ( ! empty( $v ) ) {
					$v = wp_get_attachment_image_src( $v, array( 150, 150 ) );
					$previewImage = "<i class='dashicons dashicons-no-alt remove'></i><img src='" . esc_url( $v[0] ) . "'/>";
				}
				if( $previewImage )
					echo "<div id='item_".$id."' class='thumbnail tf-image-preview'>" . $previewImage . "</div>";
			}
		}

		echo "<div id='insert' class='thumbnail tf-image-preview'></div>";
		printf("<input name=\"%s\" placeholder=\"%s\" id=\"%s\" type=\"hidden\" value=\"%s\" />",
			$this->getID(),
			$this->settings['placeholder'],
			$this->getID(),
			esc_attr( $this->getValue() )
		);
		$this->echoOptionFooter();
	}

	/*
	 * Display for theme customizer
	 */
	public function registerCustomizerControl( $wp_customize, $section, $priority = 1 ) {
		$wp_customize->add_control( new TitanFrameworkOptionUploadAdvancedControl( $wp_customize, $this->getID(), array(
			'label' => $this->settings['name'],
			'section' => $section->getID(),
			'settings' => $this->getID(),
			'description' => $this->settings['desc'],
			'priority' => $priority,
		) ) );
	}

	public static function createUploaderScript() {
		if ( ! self::$firstLoad ) {
			return;
		}
		self::$firstLoad = false;

		?>
		<script>
			(function($){
				ThimPressGalleryAdvanced = {

					init: function()
					{
						this.add_new();
						this.remove();
						this.sortable();
					},

					// add new item
					add_new: function(){
						var gallary = $('.tf-upload-advanced');
						// add new image
						$(document).on( 'click', '.tf-upload-advanced #insert', function(e){
							e.preventDefault();

							var _self = $(this),
								_input = _self.parent().find('input');

							// uploader frame properties
							var frame = wp.media({
								title: '<?php esc_attr_e( 'Select Image', 'thim' ) ?>',
								multiple: true,
								library: { type: 'image' },
								button : { text : '<?php esc_attr_e( 'Use image', 'thim' ) ?>' }
							});
							frame.on('open',function() {
								var selection = frame.state().get('selection');
								var ids = _input.val().split(',');
								ids.forEach(function(id) {
									var attachment = wp.media.attachment(id);
									attachment.fetch();
									selection.add( attachment ? [ attachment ] : [] );
								});
							});

							frame.on( 'select', function(){
								var attachments = frame.state().get('selection').toJSON(),
									ids = [],
									container = $('#insert');

									gallary.find( '.tf-upload-advanced div[id*="item_"]' ).remove();

								for( var i = 0; i < attachments.length; i++ )
								{
									var attachment = attachments[i],
										sizes = attachment.sizes,
										image = false,
										id = attachment.id;

									ids.push(id);

									if( typeof sizes === 'undefined' )
										continue;

									if( typeof sizes.full !== 'undefined' )
										image = sizes.full;

									if( typeof sizes.thumbnail !== 'undefined' )
										image = sizes.thumbnail;

									if( ! image ) continue;
									var url = image.url;

									container.before('<div id="item_'+attachment.id+'" class="thumbnail tf-image-preview">'+"<i class='dashicons dashicons-no-alt remove'></i>" + "<img src='" + url + "'/>"+ '</div>');
								}

								_input.val( ids.join(',') );
								frame.off('select');
							});
							frame.open();
						});
					},

					// remove item
					remove: function(){

						$(document).on( 'click', '.tf-upload-advanced .remove', function(e){
							e.preventDefault();

							var item = $(this).parent();
							item.remove(); // remove this item
							ThimPressGalleryAdvanced.generate();
						});
					},

					// sortable
					sortable: function()
					{
						// var gallary = $('#thim-meta-boxes-post-format-gallery');
						$( '.tf-upload-advanced' ).sortable({
					        placeholder: 'ui-state-highlight',
				            items      : 'div',
				            update: function(){
				            	ThimPressGalleryAdvanced.generate();
				            }
						});
					},

					generate: function(){

						var images = $('.tf-upload-advanced div[id*="item_"]'),
						 	_input = $('.tf-upload-advanced').find( 'input' ),
							ids = [];

						// each one by one item generate ids again
						for ( var i = 0; i < images.length; i++ )
						{
							var image = $(images[i]),
								id = image.attr( 'id' ).replace( 'item_', '' );

							ids.push(id);
						}

						_input.val( ids.join(',') );
					}
				};

				$(document).ready(function(){
					// initialize object
					ThimPressGalleryAdvanced.init();
				})
			})(jQuery);

		jQuery(document).ready(function($){
			"use strict";


			// function tfUploadOptionCenterImage($this) {
			// 	// console.log('preview image loaded');
			// 	var _preview = $this.parents('.tf-upload-advanced').find('.thumbnail');
			// 	$this.css({
			// 		'marginTop': ( _preview.height() - $this.height() ) / 2,
			// 		'marginLeft': ( _preview.width() - $this.width() ) / 2
			// 	}).show();
			// }


			// // Calculate display offset of preview image on load
			// $('.tf-upload-advanced .thumbnail img').load(function() {
			// 	tfUploadOptionCenterImage($(this));
			// }).each(function(){
			// 	// Sometimes the load event might not trigger due to cache
			// 	if(this.complete) {
			// 		$(this).trigger('load');
			// 	};
			// });


			// // In the theme customizer, the load event above doesn't work because of the accordion,
			// // the image's height & width are detected as 0. We bind to the opening of an accordion
			// // and adjust the image placement from there.
			// var tfUploadAccordionSections = [];
			// $('.tf-upload-advanced').each(function() {
			// 	var $accordion = $(this).parents('.control-section.accordion-section');
			// 	if ( $accordion.length > 0 ) {
			// 		if ( $.inArray( $accordion, tfUploadAccordionSections ) == -1 ) {
			// 			tfUploadAccordionSections.push($accordion);
			// 		}
			// 	}
			// });
			// $.each( tfUploadAccordionSections, function() {
			// 	var $title = $(this).find('.accordion-section-title:eq(0)'); // just opening the section
			// 	$title.click(function() {
			// 		var $accordion = $(this).parents('.control-section.accordion-section');
			// 		if ( ! $accordion.is('.open') ) {
			// 			$accordion.find('.tf-upload-advanced .thumbnail img').each(function() {
			// 				var $this = $(this);
			// 				setTimeout(function() {
			// 					tfUploadOptionCenterImage($this);
			// 				}, 1);
			// 			});
			// 		}
			// 	});
			// });


			// // remove the image when the remove link is clicked
			// $('body').on('click', '.tf-upload-advanced i.remove', function(event) {
			// 	event.preventDefault();
			// 	var $this = $(this).closest('.tf-upload-advanced');
			// 	$(this).parent().remove();

			// 	var value_after = "";
			// 	$this.children(".tf-image-preview").each( function() {
			// 	    var elem = $( this );
			// 	    console.log(elem.attr("id"));
			// 	    if (value_after)
			// 			value_after += ","+elem.attr("id").replace("item_", "");
			// 		else
			// 			value_after += elem.attr("id").replace("item_", "");
			// 	});
			// 	var _input = $this.find('input');
			// 	if (value_after== "") {
			// 		$('<div id="item_" class="thumbnail tf-image-preview"></div>').prependTo($this);
			// 	}
			// 	_input.val(value_after).trigger('change');
			// 	return false;
			// });

			// // Reorder images
		    // $( '.tf-upload-advanced' ).each( function() {
		    //     var $this = $( this );
		    //     $this.sortable( {
		    //         placeholder: 'ui-state-highlight',
		    //         items      : 'div',
		    //         update     : function()
		    //         {
		    //             var value_after = "";
		    //             $.each( $this.sortable('toArray'), function( key, value ) {
		    //             	if (value_after)
						// 		value_after += ","+value.replace("item_", "");
						// 	else
						// 		value_after += value.replace("item_", "");
						// });
						// var _input = $this.find('input');
						// _input.val(value_after);
		    //         }
		    //     });
		    // });


			// // open the upload media lightbox when the upload button is clicked
			// $('body').on('click', '.tf-upload-advanced .thumbnail, .tf-upload-advanced img', function(event) {
			// 	event.preventDefault();
			// 	// If we have a smaller image, users can click on the thumbnail
			// 	if ( $(this).is('.thumbnail') ) {
			// 		if ( $(this).parents('.tf-upload-advanced').find('img').length != 0 ) {
			// 			$(this).parents('.tf-upload-advanced').find('img').trigger('click');
			// 			return true;
			// 		}
			// 	}

			// 	var _input = $(this).parents('.tf-upload-advanced').find('input');
			// 	var _preview = $(this).parents('.tf-upload-advanced').find('div.thumbnail');
			// 	var _remove = $(this).siblings('.tf-upload-advanced-image-remove');

			// 	// uploader frame properties
			// 	var frame = wp.media({
			// 		title: '<?php //esc_attr_e( 'Select Image', 'thim' ) ?>',
			// 		multiple: true,
			// 		library: { type: 'image' },
			// 		button : { text : '<?php //esc_attr_e( 'Use image', 'thim' ) ?>' }
			// 	});
			// 	frame.on('open',function() {
			// 		var selection = frame.state().get('selection');
			// 		var ids = _input.val().split(',');
			// 		ids.forEach(function(id) {
			// 			var attachment = wp.media.attachment(id);
			// 			attachment.fetch();
			// 			selection.add( attachment ? [ attachment ] : [] );
			// 		});
			// 	});

			// 	// get the url when done
			// 	frame.on('select', function() {
			// 		var selection = frame.state().get('selection');
			// 		//
			// 		var ss = frame.state().get( 'selection' ).toJSON();
			// 		console.log(ss);

			// 		var ss_value = _.pluck( ss, 'id' );
			// 		if (_input.length > 0 && ss_value) {
			// 			_input.val(ss_value);
			// 		}
			// 		var container = _preview.parent(".tf-upload-advanced");
			// 		if ( _preview.length > 0 ) {
			// 			// remove current preview
			// 			container.find(".thumbnail").remove();
			// 		}

			// 		selection.each(function(attachment) {
			// 			if ( _preview.length > 0 ) {

			// 				// Get the preview image
			// 				var image = attachment.attributes.sizes.full;
			// 				if ( typeof attachment.attributes.sizes.thumbnail != 'undefined' ) {
			// 					image = attachment.attributes.sizes.thumbnail;
			// 				}
			// 				var url = image.url;
			// 				var marginTop = ( _preview.height() - image.height ) / 2;
			// 				var marginLeft = ( _preview.width() - image.width ) / 2;

			// 				$('<div id="item_'+attachment.id+'" class="thumbnail tf-image-preview">'+"<i class='dashicons dashicons-no-alt remove'></i>" + "<img src='" + url + "'/>"+ '</div>').prependTo(container);

			// 			}
			// 			// we need to trigger a change so that WP would detect that we changed the value
			// 			// or else the save button won't be enabled
			// 			_input.trigger('change');

			// 			_remove.show();
			// 		});
			// 		frame.off('select');
			// 	});

			// 	// open the uploader
			// 	frame.open();

			// 	return false;
			// });
		});
		</script>
		<?php
	}
}

/*
 * We create a new control for the theme customizer
 */
add_action( 'customize_register', 'registerTitanFrameworkOptionUploadAdvancedControl', 1 );
function registerTitanFrameworkOptionUploadAdvancedControl() {
	class TitanFrameworkOptionUploadAdvancedControl extends WP_Customize_Control {
		public $description;

		public function render_content() {
			TitanFrameworkOptionUploadAdvanced::createUploaderScript();

			$previewImage = '';
			$value = $this->value();
			if ( is_numeric( $value ) ) {
				// gives us an array with the first element as the src or false on fail
				$value = wp_get_attachment_image_src( $value, array( 150, 150 ) );
			}
			if ( ! is_array( $value ) ) {
				$value = $this->value();
			} else {
				$value = $value[0];
			}

			if ( ! empty( $value ) ) {
				$previewImage = "<i class='dashicons dashicons-no-alt remove'></i><img src='" . esc_url( $value ) . "' style='display: none'/>";
			}

			?>
			<div class='tf-upload-advanced'>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<div class='thumbnail tf-image-preview'><?php echo ent2ncr($previewImage) ?></div>
				<input type='hidden' value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?>/>
			</div>
			<?php

			if ( ! empty( $this->description ) ) {
				echo "<p class='description'>{$this->description}</p>";
			}
		}
	}
}