<?php
/* Security: Disables direct access to theme files */
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/** TM EPO Builder **/
final class TM_EPO_BUILDER_base {

	protected static $_instance = NULL;

	var $elements_namespace = 'TM Extra Product Options';

	public $all_elements;

	// element options
	public $elements_array;

	private $addons_array = array();

	private $addons_attributes = array();

	public $extra_multiple_options = array();

	private $default_attributes = array();

	// sections options
	public $_section_elements = array();

	// sizes display
	var $sizer;

	// WooCommerce Subscriptions check
	public $woo_subscriptions_check = FALSE;

	/* Main TM EPO Builder Instance */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function __construct() {

		$this->woo_subscriptions_check = tc_woocommerce_subscriptions_check();

		// extra multiple type options
		$this->extra_multiple_options = apply_filters( 'wc_epo_extra_multiple_choices', array() );
		add_action( 'tm_epo_register_extra_multiple_choices', array( $this, 'add_extra_choices' ), 50 );

		// element available sizes
		$this->element_available_sizes();

		// init section elements
		$this->init_section_elements();

		// init elements
		$this->init_elements();

	}

	public function add_extra_choices() {
		$this->extra_multiple_options = apply_filters( 'wc_epo_extra_multiple_choices', array() );
	}

	/**
	 * Holds all the elements types.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function _elements() {
		/*
		[name]=Displayed name
		[width]=Initial width
		[width_display]=Initial width display
		[icon]=icon
		[is_post]=if it is post enabled field
		[type]=if it can hold multiple or single options (for post enabled fields)
		[post_name_prefix]=name for post purposes
		[fee_type]=can set cart fees
		[subscription_fee_type]=can set subscription fees
		*/
		$this->all_elements = apply_filters( 'wc_epo_builder_element_settings', array(
				"header"       => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Heading", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-header",
					"is_post"               => "display",
					"type"                  => "",
					"post_name_prefix"      => "",
					"fee_type"              => "",
					"subscription_fee_type" => "",
					"tags"                  => "content",
					"show_on_backend"       => TRUE ),
				"divider"      => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Divider", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-long-arrow-right",
					"is_post"               => "none",
					"type"                  => "",
					"post_name_prefix"      => "",
					"fee_type"              => "",
					"subscription_fee_type" => "",
					"tags"                  => "content",
					"show_on_backend"       => TRUE ),
				"date"         => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Date", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-calendar",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "date",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"time"         => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Time", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-clock-o",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "time",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"range"        => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Range picker", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-arrows-h",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "range",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"color"        => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Color picker", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-eyedropper",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "color",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"textarea"     => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Text Area", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-terminal",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "textarea",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"textfield"    => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Text Field", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-terminal",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "textfield",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"upload"       => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Upload", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-upload",
					"is_post"               => "post",
					"type"                  => "single",
					"post_name_prefix"      => "upload",
					"fee_type"              => "single",
					"subscription_fee_type" => "single",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"selectbox"    => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Select Box", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-bars",
					"is_post"               => "post",
					"type"                  => "multiplesingle",
					"post_name_prefix"      => "select",
					"fee_type"              => "multiple",
					"subscription_fee_type" => "multiple",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"radiobuttons" => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Radio buttons", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-dot-circle-o",
					"is_post"               => "post",
					"type"                  => "multiple",
					"post_name_prefix"      => "radio",
					"fee_type"              => "multiple",
					"subscription_fee_type" => "multiple",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"checkboxes"   => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Checkboxes", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-check-square-o",
					"is_post"               => "post",
					"type"                  => "multipleall",
					"post_name_prefix"      => "checkbox",
					"fee_type"              => "multiple",
					"subscription_fee_type" => "multiple",
					"tags"                  => "price content",
					"show_on_backend"       => TRUE ),
				"variations"   => array(
					"_is_addon"             => FALSE,
					"namespace"             => $this->elements_namespace,
					"name"                  => __( "Variations", 'woocommerce-tm-extra-product-options' ),
					"description"           => "",
					"width"                 => "w100",
					"width_display"         => "1/1",
					"icon"                  => "tcfa-bullseye",
					"is_post"               => "display",
					"type"                  => "multiplesingle",
					"post_name_prefix"      => "variations",
					"fee_type"              => "",
					"subscription_fee_type" => "",
					"one_time_field"        => TRUE,
					"no_selection"          => TRUE,
					"tags"                  => "",
					"show_on_backend"       => FALSE ),
			)
		);

		do_action( 'wc_epo_builder_after_element_settings', $this->all_elements );
	}

	public final function get_elements() {
		return $this->all_elements;
	}

	private function set_elements( $args = array() ) {

		$element = $args["name"];
		$options = $args["options"];

		if ( !empty( $element ) && is_array( $options ) ) {
			$options["_is_addon"] = TRUE;

			if ( !isset( $args["namespace"] ) ) {
				$options["namespace"] = "EPD addon " . $element;
			} else {
				$options["namespace"] = $args["namespace"];
			}
			if ( $options["namespace"] == $this->elements_namespace ) {
				$options["namespace"] = $this->elements_namespace . " addon";
			}

			if ( !isset( $options["name"] ) ) {
				$options["name"] = "";
			}
			if ( !isset( $options["description"] ) ) {
				$options["description"] = "";
			}
			if ( !isset( $options["type"] ) ) {
				$options["type"] = "";
			}
			if ( !isset( $options["width"] ) ) {
				$options["width"] = "";
			}
			if ( !isset( $options["width_display"] ) ) {
				$options["width_display"] = "";
			}
			if ( !isset( $options["icon"] ) ) {
				$options["icon"] = "";
			}
			if ( !isset( $options["is_post"] ) ) {
				$options["is_post"] = "";
			}
			if ( !isset( $options["post_name_prefix"] ) ) {
				$options["post_name_prefix"] = "";
			}
			if ( !isset( $options["fee_type"] ) ) {
				$options["fee_type"] = "";
			}
			if ( !isset( $options["subscription_fee_type"] ) ) {
				$options["subscription_fee_type"] = "";
			}

			$options["tags"] = $options["name"];

			$options["show_on_backend"] = TRUE;

			$this->all_elements = array_merge( array( $element => $options ), $this->all_elements );
		}
	}

	public final function get_custom_properties( $builder, $_prefix, $_counter, $_elements, $k0, $current_builder, $current_counter, $wpml_element_fields, $current_element ) {
		$p = array();
		foreach ( $this->addons_attributes as $key => $value ) {
			$p[ $value ] = TM_EPO()->get_builder_element( $_prefix . $value, $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element );
		}

		return $p;
	}

	public final function get_default_properties( $builder, $_prefix, $_counter, $_elements, $k0 ) {
		$p = array();
		foreach ( $this->default_attributes as $key => $value ) {
			$p[ $value ] = isset( $builder[ $_prefix . $value ][ $_counter[ $_elements[ $k0 ] ] ] )
				? $builder[ $_prefix . $value ][ $_counter[ $_elements[ $k0 ] ] ]
				: "";
		}

		return $p;
	}

	public final function register_addon( $args = array() ) {
		if ( isset( $args["namespace"] ) && isset( $args["name"] ) && isset( $args["options"] ) && isset( $args["settings"] ) ) {
			$this->elements_array = array_merge(
				array(
					$args["name"] => $this->add_element( $args["name"], $args["settings"], TRUE, isset( $args["tabs_override"] ) ? $args["tabs_override"] : array() ),
				), $this->elements_array );
			$this->set_elements( $args );

			$this->addons_array[] = $args["name"];
		}
	}

	// element available sizes
	private function element_available_sizes() {
		$this->sizer = array(
			"w25"  => "1/4",
			"w33"  => "1/3",
			"w50"  => "1/2",
			"w66"  => "2/3",
			"w75"  => "3/4",
			"w100" => "1/1",
		);
	}

	// init section elements
	private function init_section_elements() {
		$this->_section_elements = array_merge(
			$this->_prepend_div( "", "tm-tabs" ),

			$this->_prepend_div( "section", "tm-tab-headers" ),
			$this->_prepend_tab( "section0", __( "Title options", 'woocommerce-tm-extra-product-options' ), "", "tma-tab-title" ),
			$this->_prepend_tab( "section1", __( "General options", 'woocommerce-tm-extra-product-options' ), "open", "tma-tab-general" ),
			$this->_prepend_tab( "section2", __( "Conditional Logic", 'woocommerce-tm-extra-product-options' ), "", "tma-tab-logic" ),
			$this->_append_div( "section" ),

			$this->_prepend_div( "section0" ),
			$this->_get_header_array( "section" . "_header" ),
			$this->_get_divider_array( "section" . "_divider", 0 ),
			$this->_append_div( "section0" ),

			$this->_prepend_div( "section1" ),
			apply_filters( 'tc_builder_section_settings',
				array(
					"sectionnum"       => array(
						"id"          => "sections",
						"wpmldisable" => 1,
						"default"     => 0,
						"nodiv"       => 1,
						"type"        => "hidden",
						"tags"        => array( "class" => "tm_builder_sections", "name" => "tm_meta[tmfbuilder][sections][]", "value" => 0 ),
						"label"       => "",
						"desc"        => "",
					),
					"sections_slides"  => array(
						"id"          => "sections_slides",
						"wpmldisable" => 1,
						"default"     => "",
						"nodiv"       => 1,
						"type"        => "hidden",
						"tags"        => array( "class" => "tm_builder_section_slides", "name" => "tm_meta[tmfbuilder][sections_slides][]", "value" => 0 ),
						"label"       => "",
						"desc"        => "",
					),
					"sectionsize"      => array(
						"id"          => "sections_size",
						"wpmldisable" => 1,
						"default"     => "w100",
						"nodiv"       => 1,
						"type"        => "hidden",
						"tags"        => array( "class" => "tm_builder_sections_size", "name" => "tm_meta[tmfbuilder][sections_size][]", "value" => "w100" ),
						"label"       => "",
						"desc"        => "",
					),
					"sectionuniqid"    => array(
						"id"      => "sections_uniqid",
						"default" => "",
						"nodiv"   => 1,
						"type"    => "hidden",
						"tags"    => array( "class" => "tm-builder-sections-uniqid", "name" => "tm_meta[tmfbuilder][sections_uniqid][]", "value" => "" ),
						"label"   => "",
						"desc"    => "",
					),
					"sectionstyle"     => array(
						"id"          => "sections_style",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "select",
						"tags"        => array( "class" => "sections_style", "id" => "tm_sections_style", "name" => "tm_meta[tmfbuilder][sections_style][]" ),
						"options"     => array(
							array( "text" => __( "Normal (clear)", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
							array( "text" => __( "Box", 'woocommerce-tm-extra-product-options' ), "value" => "box" ),
							array( "text" => __( "Expand and Collapse (start opened)", 'woocommerce-tm-extra-product-options' ), "value" => "collapse", "class" => "builder_hide_for_variation-reset" ),
							array( "text" => __( "Expand and Collapse (start closed)", 'woocommerce-tm-extra-product-options' ), "value" => "collapseclosed", "class" => "builder_hide_for_variation-reset" ),
							array( "text" => __( "Accordion", 'woocommerce-tm-extra-product-options' ), "value" => "accordion", "class" => "builder_hide_for_variation-reset" ),
						),
						"label"       => __( "Section style", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select this section's display style.", 'woocommerce-tm-extra-product-options' ),
					),
					"sectionplacement" => array(
						"id"               => "sections_placement",
						"message0x0_class" => "builder_hide_for_variation",
						"wpmldisable"      => 1,
						"default"          => "before",
						"type"             => "select",
						"tags"             => array( "id" => "sections_placement", "name" => "tm_meta[tmfbuilder][sections_placement][]" ),
						"options"          => array(
							array( "text" => __( "Before Local Options", 'woocommerce-tm-extra-product-options' ), "value" => "before" ),
							array( "text" => __( "After Local Options", 'woocommerce-tm-extra-product-options' ), "value" => "after" ),
						),
						"label"            => __( "Section placement", 'woocommerce-tm-extra-product-options' ),
						"desc"             => __( "Select where this section will appear compare to local Options.", 'woocommerce-tm-extra-product-options' ),
					),
					"sectiontype"      => array(
						"id"               => "sections_type",
						"message0x0_class" => "builder_hide_for_variation",
						"wpmldisable"      => 1,
						"default"          => "",
						"type"             => "select",
						"tags"             => array( "class" => "sections_type", "id" => "sections_type", "name" => "tm_meta[tmfbuilder][sections_type][]" ),
						"options"          => array(
							array( "text" => __( "Normal", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
							array( "text" => __( "Pop up", 'woocommerce-tm-extra-product-options' ), "value" => "popup" ),
							array( "text" => __( "Slider (wizard)", 'woocommerce-tm-extra-product-options' ), "value" => "slider", "class" => "builder_hide_for_variations" ),
						),
						"label"            => __( "Section type", 'woocommerce-tm-extra-product-options' ),
						"desc"             => __( "Select this section's display type.", 'woocommerce-tm-extra-product-options' ),
					),

					"sectionsclass" => array(
						"id"      => "sections_class",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "sections_class", "name" => "tm_meta[tmfbuilder][sections_class][]", "value" => "" ),
						"label"   => __( 'Section class name', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Enter an extra class name to add to this section', 'woocommerce-tm-extra-product-options' ),
					),
				)
			),

			$this->_append_div( "section1" ),

			$this->_prepend_div( "section2" ),
			array(
				"sectionclogic" => array(
					"id"      => "sections_clogic",
					"default" => "",
					"nodiv"   => 1,
					"type"    => "hidden",
					"tags"    => array( "class" => "tm-builder-clogic", "name" => "tm_meta[tmfbuilder][sections_clogic][]", "value" => "" ),
					"label"   => "",
					"desc"    => "",
				),
				"sectionlogic"  => array(
					"id"      => "sections_logic",
					"default" => "",
					"type"    => "select",
					"tags"    => array( "class" => "activate-sections-logic", "id" => "sections_logic", "name" => "tm_meta[tmfbuilder][sections_logic][]" ),
					"options" => array(
						array( "text" => __( "No", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( "Yes", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
					),
					"extra"   => $this->builder_showlogic(),
					"label"   => __( "Section Conditional Logic", 'woocommerce-tm-extra-product-options' ),
					"desc"    => __( "Enable conditional logic for showing or hiding this section.", 'woocommerce-tm-extra-product-options' ),
				),
			),
			$this->_append_div( "section2" ),

			$this->_append_div( "" )
		);
	}

	// init elements
	private function init_elements() {
		$this->_elements();
		$this->elements_array = array(
			"divider" => array_merge(
				$this->_prepend_div( "", "tm-tabs" ),

				$this->_prepend_div( "divider", "tm-tab-headers" ),
				$this->_prepend_tab( "divider2", __( "General options", 'woocommerce-tm-extra-product-options' ), "open" ),
				$this->_prepend_tab( "divider3", __( "Conditional Logic", 'woocommerce-tm-extra-product-options' ) ),
				$this->_prepend_tab( "divider4", __( "CSS settings", 'woocommerce-tm-extra-product-options' ) ),
				$this->_append_div( "divider" ),

				$this->_prepend_div( "divider2" ),
				$this->_get_divider_array(),

				$this->_append_div( "divider2" ),

				$this->_prepend_div( "divider3" ),
				$this->_prepend_logic( "divider" ),
				$this->_append_div( "divider3" ),

				$this->_prepend_div( "divider4" ),
				array(
					array(
						"id"      => "divider_class",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "builder_divider_class", "name" => "tm_meta[tmfbuilder][divider_class][]", "value" => "" ),
						"label"   => __( 'Element class name', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Enter an extra class name to add to this element', 'woocommerce-tm-extra-product-options' ),
					),
				),
				$this->_append_div( "divider4" ),

				$this->_append_div( "" )
			),

			"header" => array_merge(
				$this->_prepend_div( "", "tm-tabs" ),

				$this->_prepend_div( "header", "tm-tab-headers" ),
				$this->_prepend_tab( "header2", __( "General options", 'woocommerce-tm-extra-product-options' ), "open" ),
				$this->_prepend_tab( "header3", __( "Conditional Logic", 'woocommerce-tm-extra-product-options' ) ),
				$this->_prepend_tab( "header4", __( "CSS settings", 'woocommerce-tm-extra-product-options' ) ),
				$this->_append_div( "header" ),

				$this->_prepend_div( "header2" ),
				array(
					array(
						"id"          => "header_size",
						"wpmldisable" => 1,
						"default"     => "3",
						"type"        => "select",
						"tags"        => array( "id" => "builder_header_size", "name" => "tm_meta[tmfbuilder][header_size][]" ),
						"options"     => array(
							array( "text" => __( "H1", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
							array( "text" => __( "H2", 'woocommerce-tm-extra-product-options' ), "value" => "2" ),
							array( "text" => __( "H3", 'woocommerce-tm-extra-product-options' ), "value" => "3" ),
							array( "text" => __( "H4", 'woocommerce-tm-extra-product-options' ), "value" => "4" ),
							array( "text" => __( "H5", 'woocommerce-tm-extra-product-options' ), "value" => "5" ),
							array( "text" => __( "H6", 'woocommerce-tm-extra-product-options' ), "value" => "6" ),
							array( "text" => __( "p", 'woocommerce-tm-extra-product-options' ), "value" => "7" ),
							array( "text" => __( "div", 'woocommerce-tm-extra-product-options' ), "value" => "8" ),
							array( "text" => __( "span", 'woocommerce-tm-extra-product-options' ), "value" => "9" ),
						),
						"label"       => __( "Header type", 'woocommerce-tm-extra-product-options' ),
						"desc"        => "",
					),
					array(
						"id"      => "header_title",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t tm-header-title", "id" => "builder_header_title", "name" => "tm_meta[tmfbuilder][header_title][]", "value" => "" ),
						"label"   => __( 'Header title', 'woocommerce-tm-extra-product-options' ),
						"desc"    => "",
					),
					array(
						"id"          => "header_title_position",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "select",
						"tags"        => array( "id" => "builder_header_title_position", "name" => "tm_meta[tmfbuilder][header_title_position][]" ),
						"options"     => array(
							array( "text" => __( "Above field", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
							array( "text" => __( "Left of the field", 'woocommerce-tm-extra-product-options' ), "value" => "left" ),
							array( "text" => __( "Right of the field", 'woocommerce-tm-extra-product-options' ), "value" => "right" ),
							array( "text" => __( "Disable", 'woocommerce-tm-extra-product-options' ), "value" => "disable" ),
						),
						"label"       => __( "Header position", 'woocommerce-tm-extra-product-options' ),
						"desc"        => "",
					),
					array(
						"id"          => "header_title_color",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "tm-color-picker", "id" => "builder_header_title_color", "name" => "tm_meta[tmfbuilder][header_title_color][]", "value" => "" ),
						"label"       => __( 'Header color', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'Leave empty for default value', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"      => "header_subtitle",
						"default" => "",
						"type"    => "textarea",
						"tags"    => array( "id" => "builder_header_subtitle", "name" => "tm_meta[tmfbuilder][header_subtitle][]" ),
						"label"   => __( "Content", 'woocommerce-tm-extra-product-options' ),
						"desc"    => "",
					),
					array(
						"id"          => "header_subtitle_color",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "tm-color-picker", "id" => "builder_header_subtitle_color", "name" => "tm_meta[tmfbuilder][header_subtitle_color][]", "value" => "" ),
						"label"       => __( 'Content color', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'Leave empty for default value', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"               => "header_subtitle_position",
						"wpmldisable"      => 1,
						"message0x0_class" => "builder_hide_for_variation",
						"default"          => "",
						"type"             => "select",
						"tags"             => array( "id" => "builder_header_subtitle_position", "name" => "tm_meta[tmfbuilder][header_subtitle_position][]" ),
						"options"          => array(
							array( "text" => __( "Above field", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
							array( "text" => __( "Below field", 'woocommerce-tm-extra-product-options' ), "value" => "below" ),
							array( "text" => __( "Tooltip", 'woocommerce-tm-extra-product-options' ), "value" => "tooltip" ),
							array( "text" => __( "Icon tooltip left", 'woocommerce-tm-extra-product-options' ), "value" => "icontooltipleft" ),
							array( "text" => __( "Icon tooltip right", 'woocommerce-tm-extra-product-options' ), "value" => "icontooltipright" ),
						),
						"label"            => __( "Content position", 'woocommerce-tm-extra-product-options' ),
						"desc"             => "",
					),
				),

				$this->_append_div( "header2" ),

				$this->_prepend_div( "header3" ),
				$this->_prepend_logic( "header" ),
				$this->_append_div( "header3" ),

				$this->_prepend_div( "header4" ),
				array(
					array(
						"id"      => "header_class",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "builder_header_class", "name" => "tm_meta[tmfbuilder][header_class][]", "value" => "" ),
						"label"   => __( 'Element class name', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Enter an extra class name to add to this element', 'woocommerce-tm-extra-product-options' ),
					),
				),
				$this->_append_div( "header4" ),

				$this->_append_div( "" )
			),

			"textarea" => $this->add_element(
				"textarea",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type", "freechars", "hide_amount", "quantity", "placeholder", "min_chars", "max_chars", "default_value_multiple", "validation1" )
			),

			"textfield" => $this->add_element(
				"textfield",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type2", "freechars", "hide_amount", "quantity", "placeholder", "min_chars", "max_chars", "default_value", "min", "max", "validation1" )
			),

			"selectbox" => $this->add_element(
				"selectbox",
				array( "enabled", "required", "text_before_price", "text_after_price", ($this->woo_subscriptions_check) ? "price_type3" : "price_type4", "hide_amount", "quantity", "placeholder", "use_url", "changes_product_image", "options" )
			),

			"radiobuttons" => $this->add_element(
				"radiobuttons",
				array( "enabled", "required", "text_before_price", "text_after_price", "hide_amount", "quantity", "use_url", "use_images", "use_lightbox", "swatchmode", "use_colors", "changes_product_image", "items_per_row", "clear_options", "options" )
			),

			"checkboxes" => $this->add_element(
				"checkboxes",
				array( "enabled", "required", "text_before_price", "text_after_price", "hide_amount", "quantity", "limit_choices", "exactlimit_choices", "minimumlimit_choices", "use_images", "use_lightbox", "swatchmode", "use_colors", "changes_product_image", "items_per_row", "options" )
			),

			"upload" => $this->add_element(
				"upload",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type5", "hide_amount", "button_type" )
			),

			"date" => $this->add_element(
				"date",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type6", "hide_amount", "quantity", "button_type2", "date_format", "start_year", "end_year",
					array(
						"id"      => "date_default_value",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "builder_date_default_value", "name" => "tm_meta[tmfbuilder][date_default_value][]", "value" => "" ),
						"label"   => __( 'Default value', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Enter a value to be applied to the field automatically according to your selected date format. (Two digits for day, two digits for month and four digits for year).', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_min_date",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "t", "id" => "builder_date_min_date", "name" => "tm_meta[tmfbuilder][date_min_date][]", "value" => "" ),
						"label"       => __( 'Minimum selectable date', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'A number of days from today.', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_max_date",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "t", "id" => "builder_date_max_date", "name" => "tm_meta[tmfbuilder][date_max_date][]", "value" => "" ),
						"label"       => __( 'Maximum selectable date', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'A number of days from today.', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"      => "date_disabled_dates",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "builder_date_disabled_dates", "name" => "tm_meta[tmfbuilder][date_disabled_dates][]", "value" => "" ),
						"label"   => __( 'Disabled dates', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Comma separated dates according to your selected date format. (Two digits for day, two digits for month and four digits for year)', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"      => "date_enabled_only_dates",
						"default" => "",
						"type"    => "text",
						"tags"    => array( "class" => "t", "id" => "builder_date_enabled_only_dates", "name" => "tm_meta[tmfbuilder][date_enabled_only_dates][]", "value" => "" ),
						"label"   => __( 'Enabled dates', 'woocommerce-tm-extra-product-options' ),
						"desc"    => __( 'Comma separated dates according to your selected date format. (Two digits for day, two digits for month and four digits for year). Please note that this will override any other setting!', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_theme",
						"wpmldisable" => 1,
						"default"     => "epo",
						"type"        => "select",
						"tags"        => array( "id" => "builder_date_theme", "name" => "tm_meta[tmfbuilder][date_theme][]" ),
						"options"     => array(
							array( "text" => __( "Epo White", 'woocommerce-tm-extra-product-options' ), "value" => "epo" ),
							array( "text" => __( "Epo Black", 'woocommerce-tm-extra-product-options' ), "value" => "epo-black" ),
						),
						"label"       => __( "Theme", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the theme for the datepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_theme_size",
						"wpmldisable" => 1,
						"default"     => "medium",
						"type"        => "select",
						"tags"        => array( "id" => "builder_date_theme_size", "name" => "tm_meta[tmfbuilder][date_theme_size][]" ),
						"options"     => array(
							array( "text" => __( "Small", 'woocommerce-tm-extra-product-options' ), "value" => "small" ),
							array( "text" => __( "Medium", 'woocommerce-tm-extra-product-options' ), "value" => "medium" ),
							array( "text" => __( "Large", 'woocommerce-tm-extra-product-options' ), "value" => "large" ),
						),
						"label"       => __( "Size", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the size of the datepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_theme_position",
						"wpmldisable" => 1,
						"default"     => "normal",
						"type"        => "select",
						"tags"        => array( "id" => "builder_date_theme_position", "name" => "tm_meta[tmfbuilder][date_theme_position][]" ),
						"options"     => array(
							array( "text" => __( "Normal", 'woocommerce-tm-extra-product-options' ), "value" => "normal" ),
							array( "text" => __( "Top of screen", 'woocommerce-tm-extra-product-options' ), "value" => "top" ),
							array( "text" => __( "Bottom of screen", 'woocommerce-tm-extra-product-options' ), "value" => "bottom" ),
						),
						"label"       => __( "Position", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the position of the datepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "date_disabled_weekdays",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "hidden",
						"tags"        => array( "class" => "tm-weekdays", "id" => "builder_date_disabled_weekdays", "name" => "tm_meta[tmfbuilder][date_disabled_weekdays][]", "value" => "" ),
						"label"       => __( "Disable weekdays", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "This allows you to disable all selected weekdays.", 'woocommerce-tm-extra-product-options' ),
						"extra"       => $this->get_weekdays(),
					),
					array(
						"id"         => "date_tranlation_custom",
						"type"       => "custom",
						"label"      => __( 'Translations', 'woocommerce-tm-extra-product-options' ),
						"desc"       => "",
						"nowrap_end" => 1,
						"noclear"    => 1,
					),
					array(
						"id"                   => "date_tranlation_day",
						"default"              => "",
						"type"                 => "text",
						"tags"                 => array( "class" => "t", "id" => "builder_date_tranlation_day", "name" => "tm_meta[tmfbuilder][date_tranlation_day][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Day', 'woocommerce-tm-extra-product-options' ) . '</span> ',
						"nowrap_start"         => 1,
						"nowrap_end"           => 1,
					),
					array(
						"id"                   => "date_tranlation_month",
						"default"              => "",
						"type"                 => "text",
						"nowrap_start"         => 1,
						"nowrap_end"           => 1,
						"tags"                 => array( "class" => "t", "id" => "builder_date_tranlation_month", "name" => "tm_meta[tmfbuilder][date_tranlation_month][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Month', 'woocommerce-tm-extra-product-options' ) . '</span> ',
					),
					array(
						"id"                   => "date_tranlation_year",
						"default"              => "",
						"type"                 => "text",
						"tags"                 => array( "class" => "t", "id" => "builder_date_tranlation_year", "name" => "tm_meta[tmfbuilder][date_tranlation_year][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Year', 'woocommerce-tm-extra-product-options' ) . '</span> ',
						"nowrap_start"         => 1,
					),
				)
			),

			"time" => $this->add_element(
				"time",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type6", "hide_amount", "quantity", "time_format", "custom_time_format",
					array(
						"id"          => "time_min_time",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "t", "id" => "builder_time_min_time", "name" => "tm_meta[tmfbuilder][time_min_time][]", "value" => "" ),
						"label"       => __( 'Minimum selectable time', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'Enter the time the following format: 8:00 am', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "time_max_time",
						"wpmldisable" => 1,
						"default"     => "",
						"type"        => "text",
						"tags"        => array( "class" => "t", "id" => "builder_time_max_time", "name" => "tm_meta[tmfbuilder][time_max_time][]", "value" => "" ),
						"label"       => __( 'Maximum selectable time', 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( 'Enter the time the following format: 8:00 am', 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "time_theme",
						"wpmldisable" => 1,
						"default"     => "epo",
						"type"        => "select",
						"tags"        => array( "id" => "builder_time_theme", "name" => "tm_meta[tmfbuilder][time_theme][]" ),
						"options"     => array(
							array( "text" => __( "Epo White", 'woocommerce-tm-extra-product-options' ), "value" => "epo" ),
							array( "text" => __( "Epo Black", 'woocommerce-tm-extra-product-options' ), "value" => "epo-black" ),
						),
						"label"       => __( "Theme", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the theme for the timepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "time_theme_size",
						"wpmldisable" => 1,
						"default"     => "medium",
						"type"        => "select",
						"tags"        => array( "id" => "builder_time_theme_size", "name" => "tm_meta[tmfbuilder][time_theme_size][]" ),
						"options"     => array(
							array( "text" => __( "Small", 'woocommerce-tm-extra-product-options' ), "value" => "small" ),
							array( "text" => __( "Medium", 'woocommerce-tm-extra-product-options' ), "value" => "medium" ),
							array( "text" => __( "Large", 'woocommerce-tm-extra-product-options' ), "value" => "large" ),
						),
						"label"       => __( "Size", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the size of the timepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"          => "time_theme_position",
						"wpmldisable" => 1,
						"default"     => "normal",
						"type"        => "select",
						"tags"        => array( "id" => "builder_time_theme_position", "name" => "tm_meta[tmfbuilder][time_theme_position][]" ),
						"options"     => array(
							array( "text" => __( "Normal", 'woocommerce-tm-extra-product-options' ), "value" => "normal" ),
							array( "text" => __( "Top of screen", 'woocommerce-tm-extra-product-options' ), "value" => "top" ),
							array( "text" => __( "Bottom of screen", 'woocommerce-tm-extra-product-options' ), "value" => "bottom" ),
						),
						"label"       => __( "Position", 'woocommerce-tm-extra-product-options' ),
						"desc"        => __( "Select the position of the timepicker.", 'woocommerce-tm-extra-product-options' ),
					),
					array(
						"id"         => "time_tranlation_custom",
						"type"       => "custom",
						"label"      => __( 'Translations', 'woocommerce-tm-extra-product-options' ),
						"desc"       => "",
						"nowrap_end" => 1,
						"noclear"    => 1,
					),
					array(
						"id"                   => "time_tranlation_hour",
						"default"              => "",
						"type"                 => "text",
						"tags"                 => array( "class" => "t", "id" => "builder_time_tranlation_hour", "name" => "tm_meta[tmfbuilder][time_tranlation_hour][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Hour', 'woocommerce-tm-extra-product-options' ) . '</span> ',
						"nowrap_start"         => 1,
						"nowrap_end"           => 1,
					),
					array(
						"id"                   => "time_tranlation_minute",
						"default"              => "",
						"type"                 => "text",
						"nowrap_start"         => 1,
						"nowrap_end"           => 1,
						"tags"                 => array( "class" => "t", "id" => "builder_time_tranlation_month", "name" => "tm_meta[tmfbuilder][time_tranlation_minute][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Minute', 'woocommerce-tm-extra-product-options' ) . '</span> ',
					),
					array(
						"id"                   => "time_tranlation_second",
						"default"              => "",
						"type"                 => "text",
						"tags"                 => array( "class" => "t", "id" => "builder_time_tranlation_second", "name" => "tm_meta[tmfbuilder][time_tranlation_second][]", "value" => "" ),
						"label"                => "",
						"desc"                 => "",
						"prepend_element_html" => '<span class="prepend_span">' . __( 'Second', 'woocommerce-tm-extra-product-options' ) . '</span> ',
						"nowrap_start"         => 1,
					),
				)
			),

			"range" => $this->add_element(
				"range",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type7", "hide_amount", "quantity", "min", "max", "rangestep", "show_picker_value", "pips", "noofpips", "default_value" )
			),

			"color" => $this->add_element(
				"color",
				array( "enabled", "required", "price", "sale_price", "text_before_price", "text_after_price", "price_type6", "hide_amount", "quantity", "default_value" )
			),

			"variations" => $this->add_element(
				"variations",
				array( "variations_options" )
			),

		);
		if ( $this->woo_subscriptions_check ) {
			$this->elements_array["textarea"][24]['options'][] = array( "text" => __( "Subscription sign up fee", 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" );
			$this->elements_array["textfield"][24]['options'][] = array( "text" => __( "Subscription sign up fee", 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" );
			$this->elements_array["date"][24]['options'][] = array( "text" => __( "Subscription sign up fee", 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" );
			$this->elements_array["time"][24]['options'][] = array( "text" => __( "Subscription sign up fee", 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" );
		}

		$this->elements_array = apply_filters( 'wc_epo_builder_after_element_array', $this->elements_array );

	}

	public final function add_setting_pips( $name = "" ) {
		return array(
			"id"          => $name . "_pips",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_pips", "name" => "tm_meta[tmfbuilder][" . $name . "_pips][]" ),
			"options"     => array(
				array( "text" => __( "No", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Yes", 'woocommerce-tm-extra-product-options' ), "value" => "yes" ),
			),
			"label"       => __( "Enable points display?", 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( "This allows you to generate points along the range picker.", 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_noofpips( $name = "" ) {
		return array(
			"id"          => $name . "_noofpips",
			"wpmldisable" => 1,
			"default"     => "10",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_noofpips", "name" => "tm_meta[tmfbuilder][" . $name . "_noofpips][]", "value" => "" ),
			"label"       => __( 'Number of points', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter the number of values for the points display.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_show_picker_value( $name = "" ) {
		return array(
			"id"          => $name . "_show_picker_value",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_show_picker_value", "name" => "tm_meta[tmfbuilder][" . $name . "_show_picker_value][]" ),
			"options"     => array(
				array( "text" => __( "Tooltip", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Left side", 'woocommerce-tm-extra-product-options' ), "value" => "left" ),
				array( "text" => __( "Right side", 'woocommerce-tm-extra-product-options' ), "value" => "right" ),
				array( "text" => __( "Tooltip and Left side", 'woocommerce-tm-extra-product-options' ), "value" => "tleft" ),
				array( "text" => __( "Tooltip and Right side", 'woocommerce-tm-extra-product-options' ), "value" => "tright" ),
			),
			"label"       => __( "Show value on", 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( "Select how to show the value of the range picker.", 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_rangestep( $name = "" ) {
		return array(
			"id"          => $name . "_step",
			"wpmldisable" => 1,
			"default"     => "1",
			"type"        => "text",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_step", "name" => "tm_meta[tmfbuilder][" . $name . "_step][]", "value" => "" ),
			"label"       => __( 'Step value', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter the step for the handle.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_validation1( $name = "" ) {
		return array(
			"id"          => $name . "_validation1",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_validation1", "name" => "tm_meta[tmfbuilder][" . $name . "_validation1][]" ),
			"options"     => array(
				array( "text" => __( 'No validation', 'woocommerce-tm-extra-product-options' ), "value" => '' ),
				array( "text" => __( 'Email', 'woocommerce-tm-extra-product-options' ), "value" => 'email' ),
				array( "text" => __( 'Url', 'woocommerce-tm-extra-product-options' ), "value" => 'url' ),
				array( "text" => __( 'Number', 'woocommerce-tm-extra-product-options' ), "value" => 'number' ),
				array( "text" => __( 'Digits', 'woocommerce-tm-extra-product-options' ), "value" => 'digits' ),
				array( "text" => __( 'Letters only', 'woocommerce-tm-extra-product-options' ), "value" => 'lettersonly' ),
				array( "text" => __( 'Letters or Space only', 'woocommerce-tm-extra-product-options' ), "value" => 'lettersspaceonly' ),
				array( "text" => __( 'Alphanumeric', 'woocommerce-tm-extra-product-options' ), "value" => 'alphanumeric' ),
			),
			"label"       => __( 'Validate as', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether the field will be validated against the choosen method.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_required( $name = "" ) {
		return array(
			"id"          => $name . "_required",
			"wpmldisable" => 1,
			"default"     => "0",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_required", "name" => "tm_meta[tmfbuilder][" . $name . "_required][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => '0' ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => '1' ),
			),
			"label"       => __( 'Required', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether the user must fill out this field or not.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_enabled( $name = "" ) {
		return array(
			"id"          => $name . "_enabled",
			"wpmldisable" => 1,
			"default"     => "1",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_required", "name" => "tm_meta[tmfbuilder][" . $name . "_enabled][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => '0' ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => '1' ),
			),
			"label"       => __( 'Enabled', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether the option is enabled or not.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_price( $name = "" ) {
		return array(
			"id"               => $name . "_price",
			"wpmldisable"      => 1,
			"message0x0_class" => "builder_" . $name . "_price_div builder_price_div",
			"default"          => "",
			"type"             => "number",
			"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_price", "name" => "tm_meta[tmfbuilder][" . $name . "_price][]", "value" => "", "step" => "any" ),
			"label"            => __( 'Price', 'woocommerce-tm-extra-product-options' ),
			"desc"             => __( 'Enter the price for this field or leave it blank for no price.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_sale_price( $name = "" ) {
		return array(
			"id"               => $name . "_sale_price",
			"wpmldisable"      => 1,
			"message0x0_class" => "builder_" . $name . "_price_div builder_price_div",
			"default"          => "",
			"type"             => "number",
			"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_sale_price", "name" => "tm_meta[tmfbuilder][" . $name . "_sale_price][]", "value" => "", "step" => "any" ),
			"label"            => __( 'Sale Price', 'woocommerce-tm-extra-product-options' ),
			"desc"             => __( 'Enter the sale price for this field or leave it blankto use the default price.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_text_after_price( $name = "" ) {
		return array(
			"id"      => $name . "_text_after_price",
			"default" => "",
			"type"    => "text",
			"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_text_after_price", "name" => "tm_meta[tmfbuilder][" . $name . "_text_after_price][]", "value" => "" ),
			"label"   => __( 'Text after Price', 'woocommerce-tm-extra-product-options' ),
			"desc"    => __( 'Enter a text to display after the price for this field or leave it blank for no text.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_text_before_price( $name = "" ) {
		return array(
			"id"      => $name . "_text_before_price",
			"default" => "",
			"type"    => "text",
			"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_text_before_price", "name" => "tm_meta[tmfbuilder][" . $name . "_text_before_price][]", "value" => "" ),
			"label"   => __( 'Text before Price', 'woocommerce-tm-extra-product-options' ),
			"desc"    => __( 'Enter a text to display before the price for this field or leave it blank for no text.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//textarea
	public final function add_setting_price_type( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "tm-pricetype-selector", "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( 'Fixed amount', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Percent of the original price', 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( 'Percent of the original price + options', 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
				array( "text" => __( 'Price per char', 'woocommerce-tm-extra-product-options' ), "value" => "char" ),
				array( "text" => __( "Percent of the original price per char", 'woocommerce-tm-extra-product-options' ), "value" => "charpercent" ),
				array( "text" => __( 'Price per char (no first char)', 'woocommerce-tm-extra-product-options' ), "value" => "charnofirst" ),
				array( "text" => __( 'Price per char (no n-th char)', 'woocommerce-tm-extra-product-options' ), "value" => "charnon" ),
				array( "text" => __( 'Price per char (no n-th char and no spaces)', 'woocommerce-tm-extra-product-options' ), "value" => "charnonnospaces" ),
				array( "text" => __( "Percent of the original price per char (no first char)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnofirst" ),
				array( "text" => __( "Percent of the original price per char (no n-th char)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnon" ),
				array( "text" => __( "Percent of the original price per char (no n-th char and no spaces)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnonnospaces" ),
				array( "text" => __( 'Price per char (no spaces)', 'woocommerce-tm-extra-product-options' ), "value" => "charnospaces" ),
				array( "text" => __( 'Price per row', 'woocommerce-tm-extra-product-options' ), "value" => "row" ),
				array( "text" => __( 'Fee', 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
				array( "text" => __( "Quantity Fee", 'woocommerce-tm-extra-product-options' ), "value" => "stepfee" ),
				array( "text" => __( "Current value Fee", 'woocommerce-tm-extra-product-options' ), "value" => "currentstepfee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//textfield
	public final function add_setting_price_type2( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "tm-pricetype-selector", "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( "Fixed amount", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Quantity", 'woocommerce-tm-extra-product-options' ), "value" => "step" ),
				array( "text" => __( "Current value", 'woocommerce-tm-extra-product-options' ), "value" => "currentstep" ),
				array( "text" => __( "Percent of the original price", 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( "Percent of the original price + options", 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
				array( "text" => __( "Price per char", 'woocommerce-tm-extra-product-options' ), "value" => "char" ),
				array( "text" => __( "Percent of the original price per char", 'woocommerce-tm-extra-product-options' ), "value" => "charpercent" ),
				array( "text" => __( 'Price per char (no first char)', 'woocommerce-tm-extra-product-options' ), "value" => "charnofirst" ),
				array( "text" => __( 'Price per char (no n-th char)', 'woocommerce-tm-extra-product-options' ), "value" => "charnon" ),
				array( "text" => __( 'Price per char (no n-th char and no spaces)', 'woocommerce-tm-extra-product-options' ), "value" => "charnonnospaces" ),
				array( "text" => __( "Percent of the original price per char (no first char)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnofirst" ),
				array( "text" => __( "Percent of the original price per char (no n-th char)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnon" ),
				array( "text" => __( "Percent of the original price per char (no n-th char and no spaces)", 'woocommerce-tm-extra-product-options' ), "value" => "charpercentnonnospaces" ),
				array( "text" => __( 'Price per char (no spaces)', 'woocommerce-tm-extra-product-options' ), "value" => "charnospaces" ),
				array( "text" => __( "Fee", 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
				array( "text" => __( "Quantity Fee", 'woocommerce-tm-extra-product-options' ), "value" => "stepfee" ),
				array( "text" => __( "Current value Fee", 'woocommerce-tm-extra-product-options' ), "value" => "currentstepfee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_freechars( $name = "", $args = array() ) {
		return array_merge( array(
			"id"               => $name . "_freechars",
			"message0x0_class" => "tm-show-for-per-chars tm-qty-freechars",
			"wpmldisable"      => 1,
			"default"          => "",
			"type"             => "number",
			"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_freechars", "name" => "tm_meta[tmfbuilder][" . $name . "_freechars][]", "value" => "", "step" => "1" ),
			"label"            => __( 'Free chars', 'woocommerce-tm-extra-product-options' ),
			"desc"             => __( 'Enter the number of free chars.', 'woocommerce-tm-extra-product-options' ),
		), $args );
	}

	//selectbox with subscriptions active
	public final function add_setting_price_type3( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "n tm_select_price_type " . $name, "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( 'Use options', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Fee', 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
				array( "text" => __( 'Subscription sign up fee', 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//selectbox
	public final function add_setting_price_type4( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "n tm_select_price_type " . $name, "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( 'Use options', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Fee', 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//upload
	public final function add_setting_price_type5( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( "Fixed amount", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Percent of the original price", 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( "Percent of the original price + options", 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
				array( "text" => __( 'Fee', 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//date, time
	public final function add_setting_price_type6( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( "Fixed amount", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Percent of the original price", 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( "Percent of the original price + options", 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
				array( "text" => __( "Fee", 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	//range
	public final function add_setting_price_type7( $name = "" ) {
		return array(
			"id"          => $name . "_price_type",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_price_type][]" ),
			"options"     => array(
				array( "text" => __( "Fixed amount", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Step * price", 'woocommerce-tm-extra-product-options' ), "value" => "step" ),
				array( "text" => __( "Current value", 'woocommerce-tm-extra-product-options' ), "value" => "currentstep" ),
				array( "text" => __( "Price per Interval", 'woocommerce-tm-extra-product-options' ), "value" => "intervalstep" ),
				array( "text" => __( "Percent of the original price", 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( "Percent of the original price + options", 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
				array( "text" => __( "Fee", 'woocommerce-tm-extra-product-options' ), "value" => "fee" ),
				array( "text" => __( "Quantity Fee", 'woocommerce-tm-extra-product-options' ), "value" => "stepfee" ),
				array( "text" => __( "Current value Fee", 'woocommerce-tm-extra-product-options' ), "value" => "currentstepfee" ),
			),
			"label"       => __( 'Price type', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_min( $name = "", $args = array() ) {
		return array_merge( array(
			"id"          => $name . "_min",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_min", "name" => "tm_meta[tmfbuilder][" . $name . "_min][]", "value" => "", "step" => "any" ),
			"label"       => __( 'Min value', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter the minimum value.', 'woocommerce-tm-extra-product-options' ),
		), $args );
	}

	public final function add_setting_max( $name = "", $args = array() ) {
		return array_merge( array(
			"id"          => $name . "_max",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_max", "name" => "tm_meta[tmfbuilder][" . $name . "_max][]", "value" => "", "step" => "any" ),
			"label"       => __( 'Max value', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter the maximum value.', 'woocommerce-tm-extra-product-options' ),
		), $args );
	}

	public final function add_setting_date_format( $name = "" ) {
		return array(
			"id"      => $name . "_format",
			"default" => "0",
			"type"    => "select",
			"tags"    => array( "id" => "builder_" . $name . "_format", "name" => "tm_meta[tmfbuilder][" . $name . "_format][]" ),
			"options" => array(
				array( "text" => __( "Day / Month / Year", 'woocommerce-tm-extra-product-options' ), "value" => "0" ),
				array( "text" => __( "Month / Day / Year", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
				array( "text" => __( "Day . Month . Year", 'woocommerce-tm-extra-product-options' ), "value" => "2" ),
				array( "text" => __( "Month . Day . Year", 'woocommerce-tm-extra-product-options' ), "value" => "3" ),
				array( "text" => __( "Day - Month - Year", 'woocommerce-tm-extra-product-options' ), "value" => "4" ),
				array( "text" => __( "Month - Day - Year", 'woocommerce-tm-extra-product-options' ), "value" => "5" ),
			),
			"label"   => __( "Date format", 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_time_format( $name = "" ) {
		return array(
			"id"      => $name . "_time_format",
			"default" => "0",
			"type"    => "select",
			"tags"    => array( "id" => "builder_" . $name . "_format", "name" => "tm_meta[tmfbuilder][" . $name . "_time_format][]" ),
			"options" => array(
				array( "text" => __( "HH:mm", 'woocommerce-tm-extra-product-options' ), "value" => "HH:mm" ),
				array( "text" => __( "HH:m", 'woocommerce-tm-extra-product-options' ), "value" => "HH:m" ),
				array( "text" => __( "H:mm", 'woocommerce-tm-extra-product-options' ), "value" => "H:mm" ),
				array( "text" => __( "H:m", 'woocommerce-tm-extra-product-options' ), "value" => "H:m" ),
				array( "text" => __( "HH:mm:ss", 'woocommerce-tm-extra-product-options' ), "value" => "HH:mm:ss" ),
				array( "text" => __( "HH:m:ss", 'woocommerce-tm-extra-product-options' ), "value" => "HH:m:ss" ),
				array( "text" => __( "H:mm:ss", 'woocommerce-tm-extra-product-options' ), "value" => "H:mm:ss" ),
				array( "text" => __( "H:m:ss", 'woocommerce-tm-extra-product-options' ), "value" => "H:m:ss" ),
				array( "text" => __( "HH:mm:s", 'woocommerce-tm-extra-product-options' ), "value" => "HH:mm:s" ),
				array( "text" => __( "HH:m:s", 'woocommerce-tm-extra-product-options' ), "value" => "HH:m:s" ),
				array( "text" => __( "H:mm:s", 'woocommerce-tm-extra-product-options' ), "value" => "H:mm:s" ),
				array( "text" => __( "H:m:s", 'woocommerce-tm-extra-product-options' ), "value" => "H:m:s" ),

				array( "text" => __( "hh:mm", 'woocommerce-tm-extra-product-options' ), "value" => "hh:mm" ),
				array( "text" => __( "hh:m", 'woocommerce-tm-extra-product-options' ), "value" => "hh:m" ),
				array( "text" => __( "h:mm", 'woocommerce-tm-extra-product-options' ), "value" => "h:mm" ),
				array( "text" => __( "h:m", 'woocommerce-tm-extra-product-options' ), "value" => "h:m" ),
				array( "text" => __( "hh:mm:ss", 'woocommerce-tm-extra-product-options' ), "value" => "hh:mm:ss" ),
				array( "text" => __( "hh:m:ss", 'woocommerce-tm-extra-product-options' ), "value" => "hh:m:ss" ),
				array( "text" => __( "h:mm:ss", 'woocommerce-tm-extra-product-options' ), "value" => "h:mm:ss" ),
				array( "text" => __( "h:m:ss", 'woocommerce-tm-extra-product-options' ), "value" => "h:m:ss" ),
				array( "text" => __( "hh:mm:s", 'woocommerce-tm-extra-product-options' ), "value" => "hh:mm:s" ),
				array( "text" => __( "hh:m:s", 'woocommerce-tm-extra-product-options' ), "value" => "hh:m:s" ),
				array( "text" => __( "h:mm:s", 'woocommerce-tm-extra-product-options' ), "value" => "h:mm:s" ),
				array( "text" => __( "h:m:s", 'woocommerce-tm-extra-product-options' ), "value" => "h:m:s" ),
			),
			"label"   => __( "Time format", 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_custom_time_format( $name = "" ) {
		return array(
			"id"          => $name . "_custom_time_format",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "text",
			"tags"        => array( "class" => "t", "id" => "builder_" . $name . "_custom_time_format", "name" => "tm_meta[tmfbuilder][" . $name . "_custom_time_format][]", "value" => "" ),
			"label"       => __( 'Custom Time format', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'This will override the time format above.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_start_year( $name = "" ) {
		return array(
			"id"          => $name . "_start_year",
			"wpmldisable" => 1,
			"default"     => "1900",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_start_year", "name" => "tm_meta[tmfbuilder][" . $name . "_start_year][]", "value" => "" ),
			"label"       => __( 'Start year', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter starting year.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_end_year( $name = "" ) {
		return array(
			"id"          => $name . "_end_year",
			"wpmldisable" => 1,
			"default"     => (date( "Y" ) + 10),
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_end_year", "name" => "tm_meta[tmfbuilder][" . $name . "_end_year][]", "value" => "" ),
			"label"       => __( 'End year', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter ending year.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_use_url( $name = "" ) {
		return array(
			"id"          => $name . "_use_url",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "use_url", "id" => "builder_" . $name . "_use_url", "name" => "tm_meta[tmfbuilder][" . $name . "_use_url][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "url" ),
			),
			"label"       => __( 'Use URL replacements', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether to redirect to a URL if the option is click.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_options( $name = "" ) {
		return array(
			"id"         => $name . "_options",
			"tmid"       => "populate",
			"default"    => "",
			"type"       => "custom",
			"leftclass"  => "onerow",
			"rightclass" => "onerow",
			"html"       => $this->builder_sub_options( array(), 'multiple_' . $name . '_options' ),
			"label"      => __( 'Populate options', 'woocommerce-tm-extra-product-options' ),
			"desc"       => ($name == 'checkboxes') ? '' : __( 'Double click the radio button to remove its selected attribute.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_variations_options( $name = "" ) {
		return array(
			"id"         => $name . "_options",
			"default"    => "",
			"type"       => "custom",
			"leftclass"  => "onerow",
			"rightclass" => "onerow2 tm-all-attributes",
			"html"       => $this->builder_sub_variations_options( array() ),
			"label"      => __( 'Variation options', 'woocommerce-tm-extra-product-options' ),
			"desc"       => "",
		);
	}

	public final function add_setting_use_images( $name = "" ) {
		return array(
			"id"          => $name . "_use_images",
			"message0x0_class" => "tm-use-images",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "use_images", "id" => "builder_" . $name . "_use_images", "name" => "tm_meta[tmfbuilder][" . $name . "_use_images][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "images" ),
				array( "text" => __( 'Start of the label', 'woocommerce-tm-extra-product-options' ), "value" => "start" ),
				array( "text" => __( 'End of the label', 'woocommerce-tm-extra-product-options' ), "value" => "end" ),
			),
			"label"       => __( 'Use image replacements', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether to use images in place of the element choices.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_use_colors( $name = "" ) {
		return array(
			"id"          => $name . "_use_colors",
			"message0x0_class" => "tm-use-colors",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "use_colors", "id" => "builder_" . $name . "_use_colors", "name" => "tm_meta[tmfbuilder][" . $name . "_use_colors][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "color" ),
				array( "text" => __( 'Start of the label', 'woocommerce-tm-extra-product-options' ), "value" => "start" ),
				array( "text" => __( 'End of the label', 'woocommerce-tm-extra-product-options' ), "value" => "end" ),
			),
			"label"       => __( 'Use color replacements', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether to use a color swatch in place of the element choices.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_use_lightbox( $name = "" ) {
		return array(
			"id"               => $name . "_use_lightbox",
			"message0x0_class" => "tm-show-when-use-images",
			"wpmldisable"      => 1,
			"default"          => "",
			"type"             => "select",
			"tags"             => array( "class" => "use_lightbox tm-use-lightbox", "id" => "builder_" . $name . "_use_lightbox", "name" => "tm_meta[tmfbuilder][" . $name . "_use_lightbox][]" ),
			"options"          => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "lightbox" ),
			),
			"label"            => __( 'Use image lightbox', 'woocommerce-tm-extra-product-options' ),
			"desc"             => __( 'Choose whether to enable the lightbox on the thumbnail.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_changes_product_image( $name = "" ) {
		return array(
			"id"          => $name . "_changes_product_image",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "use_images tm-changes-product-image", "id" => "builder_" . $name . "_changes_product_image", "name" => "tm_meta[tmfbuilder][" . $name . "_changes_product_image][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Use the image replacements', 'woocommerce-tm-extra-product-options' ), "value" => "images" ),
				array( "text" => __( 'Use custom image', 'woocommerce-tm-extra-product-options' ), "value" => "custom" ),
			),
			"label"       => __( 'Changes product image', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Choose whether to change the product image.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_swatchmode( $name = "" ) {
		return array(
			"id"          => $name . "_swatchmode",
			"message0x0_class" => "tm-show-when-use-images",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "swatchmode", "id" => "builder_" . $name . "_swatchmode", "name" => "tm_meta[tmfbuilder][" . $name . "_swatchmode][]" ),
			"options"     => apply_filters( "wc_epo_add_setting_swatchmode", array(
					array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
					array( "text" => __( 'Show label', 'woocommerce-tm-extra-product-options' ), "value" => "swatch" ),
					array( "text" => __( 'Show description', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_desc" ),
					array( "text" => __( 'Show label and description', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_lbl_desc" ),
					array( "text" => __( 'Show image', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_img" ),
					array( "text" => __( 'Show image and label', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_img_lbl" ),
					array( "text" => __( 'Show image and description', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_img_desc" ),
					array( "text" => __( 'Show image, label and description', 'woocommerce-tm-extra-product-options' ), "value" => "swatch_img_lbl_desc" ),
				)
			),
			"label"       => __( 'Enable Swatch mode', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Swatch mode will show a tooltip when Use image replacements is active.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_clear_options( $name = "" ) {
		return array(
			"id"          => $name . "_clear_options",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "select",
			"tags"        => array( "class" => "clear_options", "id" => "builder_" . $name . "_clear_options", "name" => "tm_meta[tmfbuilder][" . $name . "_clear_options][]" ),
			"options"     => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "clear" ),
			),
			"label"       => __( 'Enable clear options button', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'This will add a button to clear the selected option.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_items_per_row( $name = "" ) {
		return array( '_multiple_values' => array(
			array(
				"id"          => $name . "_items_per_row",
				"wpmldisable" => 1,
				"default"     => "",
				"type"        => "text",
				"extra"       => "<span class='tc-enable-responsive'>" . __( 'Show responsive values', 'woocommerce-tm-extra-product-options' ) . " <span class='off tcfa tcfa-desktop'></span><span class='on tcfa tcfa-tablet tm-hidden'></span></span>",
				"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row][]" ),
				"label"       => __( 'Items per row (Desktops and laptops)', 'woocommerce-tm-extra-product-options' ),
				"desc"        => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) {
			array(
				"id"               => $name . "_items_per_row_tablets",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_tablets", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_tablets][]" ),
				"label"            => __( 'Items per row (Tablets landscape)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width : 481px) and (max-device-width : 767px) {
			array(
				"id"               => $name . "_items_per_row_tablets_small",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_tablets_small", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_tablets_small][]" ),
				"label"            => __( 'Items per row (Tablets portrait)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
			array(
				"id"               => $name . "_items_per_row_smartphones",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_smartphones", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_smartphones][]" ),
				"label"            => __( 'Items per row (Smartphones)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width: 320px) and (max-device-width: 568px) and (-webkit-min-device-pixel-ratio: 2) {
			array(
				"id"               => $name . "_items_per_row_iphone5",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_iphone5", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_iphone5][]" ),
				"label"            => __( 'Items per row (iPhone 5)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width: 375px) and (max-device-width: 667px) and (-webkit-min-device-pixel-ratio: 2) {
			array(
				"id"               => $name . "_items_per_row_iphone6",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_iphone6", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_iphone6][]" ),
				"label"            => __( 'Items per row (iPhone 6)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width: 414px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 2) {
			array(
				"id"               => $name . "_items_per_row_iphone6_plus",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_iphone6_plus", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_iphone6_plus][]" ),
				"label"            => __( 'Items per row (iPhone 6 +)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (device-width: 320px) and (device-height: 640px) and (-webkit-min-device-pixel-ratio: 2) {
			array(
				"id"               => $name . "_items_per_row_samsung_galaxy",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_samsung_galaxy", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_samsung_galaxy][]" ),
				"label"            => __( 'Items per row (Samnsung Galaxy)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			//@media only screen and (min-device-width : 800px) and (max-device-width : 1280px) {
			array(
				"id"               => $name . "_items_per_row_tablets_galaxy",
				"message0x0_class" => "builder_responsive_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_items_per_row_tablets_galaxy", "name" => "tm_meta[tmfbuilder][" . $name . "_items_per_row_tablets_galaxy][]" ),
				"label"            => __( 'Items per row (Galaxy Tablets landscape)', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),

		) );
	}

	public final function add_setting_limit_choices( $name = "" ) {
		return array(
			"id"          => $name . "_limit_choices",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_limit_choices", "name" => "tm_meta[tmfbuilder][" . $name . "_limit_choices][]", "min" => 0 ),
			"label"       => __( 'Limit selection', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter a number above 0 to limit the checkbox selection or leave blank for default behaviour.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_exactlimit_choices( $name = "" ) {
		return array(
			"id"          => $name . "_exactlimit_choices",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_exactlimit_choices", "name" => "tm_meta[tmfbuilder][" . $name . "_exactlimit_choices][]", "min" => 0 ),
			"label"       => __( 'Exact selection', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter a number above 0 to have the user select the exact number of checkboxes or leave blank for default behaviour.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_minimumlimit_choices( $name = "" ) {
		return array(
			"id"          => $name . "_minimumlimit_choices",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_minimumlimit_choices", "name" => "tm_meta[tmfbuilder][" . $name . "_minimumlimit_choices][]", "min" => 0 ),
			"label"       => __( 'Minimum selection', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter a number above 0 to have the user select at least that number of checkboxes or leave blank for default behaviour.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_button_type( $name = "" ) {
		return array(
			"id"      => $name . "_button_type",
			"default" => "",
			"type"    => "select",
			"tags"    => array( "id" => "builder_" . $name . "_button_type", "name" => "tm_meta[tmfbuilder][" . $name . "_button_type][]" ),
			"options" => array(
				array( "text" => __( 'Normal browser button', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Styled button', 'woocommerce-tm-extra-product-options' ), "value" => "button" ),
			),
			"label"   => __( 'Upload button style', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_button_type2( $name = "" ) {
		return array(
			"id"          => $name . "_button_type",
			"wpmldisable" => 1,
			"default"     => "picker",
			"type"        => "select",
			"tags"        => array( "id" => "builder_" . $name . "_button_type", "name" => "tm_meta[tmfbuilder][" . $name . "_button_type][]" ),
			"options"     => array(
				array( "text" => __( "Date field", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Date picker", 'woocommerce-tm-extra-product-options' ), "value" => "picker" ),
				array( "text" => __( "Date field and picker", 'woocommerce-tm-extra-product-options' ), "value" => "fieldpicker" ),
			),
			"label"       => __( "Date picker style", 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_hide_amount( $name = "" ) {
		return array(
			"id"               => $name . "_hide_amount",
			"message0x0_class" => "builder_" . $name . "_hide_amount_div",
			"wpmldisable"      => 1,
			"default"          => "",
			"type"             => "select",
			"tags"             => array( "id" => "builder_" . $name . "_hide_amount", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_amount][]" ),
			"options"          => array(
				array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
			),
			"label"            => __( 'Hide price', 'woocommerce-tm-extra-product-options' ),
			"desc"             => __( 'Choose whether to hide the price or not.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_quantity( $name = "" ) {
		return array( '_multiple_values' => array(
			array(
				"id"               => $name . "_quantity",
				"message0x0_class" => "builder_" . $name . "_quantity_div",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "select",
				"tags"             => array( "id" => "builder_" . $name . "_quantity", "class" => "tm-qty-selector", "name" => "tm_meta[tmfbuilder][" . $name . "_quantity][]" ),
				"options"          => array(
					array( "text" => __( 'Disable', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
					array( "text" => __( 'Right', 'woocommerce-tm-extra-product-options' ), "value" => "right" ),
					array( "text" => __( 'Left', 'woocommerce-tm-extra-product-options' ), "value" => "left" ),
					array( "text" => __( 'Top', 'woocommerce-tm-extra-product-options' ), "value" => "top" ),
					array( "text" => __( 'Bottom', 'woocommerce-tm-extra-product-options' ), "value" => "bottom" ),
				),
				"label"            => __( 'Quantity selector', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'This will show a quantity selector for this option.', 'woocommerce-tm-extra-product-options' ),
			),
			$this->add_setting_min( $name . "_quantity", array( "label" => __( 'Quantity min value', 'woocommerce-tm-extra-product-options' ), "message0x0_class" => "tm-show-for-quantity tm-qty-min" ) ),
			$this->add_setting_max( $name . "_quantity", array( "label" => __( 'Quantity max value', 'woocommerce-tm-extra-product-options' ), "message0x0_class" => "tm-show-for-quantity tm-qty-max" ) ),
			array(
				"id"               => $name . "_quantity_step",
				"message0x0_class" => "tm-show-for-quantity tm-qty-max",
				"wpmldisable"      => 1,
				"default"          => "",
				"type"             => "number",
				"tags"             => array( "class" => "n", "id" => "builder_" . $name . "_min", "name" => "tm_meta[tmfbuilder][" . $name . "_quantity_step][]", "value" => "", "step" => "any", "min" => 0 ),
				"label"            => __( 'Quantity step', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Enter the quantity step.', 'woocommerce-tm-extra-product-options' ),
			),
			$this->add_setting_default_value( $name . "_quantity", array( "label" => __( 'Quantity Default value', 'woocommerce-tm-extra-product-options' ), "message0x0_class" => "tm-show-for-quantity tm-qty-default", "desc" => __( 'Enter a value to be applied to the Quantity field automatically.', 'woocommerce-tm-extra-product-options' ) ) ),
		) );
	}

	public final function add_setting_placeholder( $name = "" ) {
		return array(
			"id"      => $name . "_placeholder",
			"default" => "",
			"type"    => "text",
			"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_placeholder", "name" => "tm_meta[tmfbuilder][" . $name . "_placeholder][]", "value" => "" ),
			"label"   => __( 'Placeholder', 'woocommerce-tm-extra-product-options' ),
			"desc"    => "",
		);
	}

	public final function add_setting_min_chars( $name = "" ) {
		return array(
			"id"          => $name . "_min_chars",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_min_chars", "name" => "tm_meta[tmfbuilder][" . $name . "_min_chars][]", "value" => "", "min" => 0 ),
			"label"       => __( 'Minimum characters', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter a value for the minimum characters the user must enter.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_max_chars( $name = "" ) {
		return array(
			"id"          => $name . "_max_chars",
			"wpmldisable" => 1,
			"default"     => "",
			"type"        => "number",
			"tags"        => array( "class" => "n", "id" => "builder_" . $name . "_max_chars", "name" => "tm_meta[tmfbuilder][" . $name . "_max_chars][]", "value" => "", "min" => 0 ),
			"label"       => __( 'Maximum characters', 'woocommerce-tm-extra-product-options' ),
			"desc"        => __( 'Enter a value to limit the maximum characters the user can enter.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	public final function add_setting_default_value( $name = "", $args = array() ) {
		return array_merge( array(
			"id"      => $name . "_default_value",
			"default" => "",
			"type"    => "text",
			"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_default_value", "name" => "tm_meta[tmfbuilder][" . $name . "_default_value][]", "value" => "" ),
			"label"   => __( 'Default value', 'woocommerce-tm-extra-product-options' ),
			"desc"    => __( 'Enter a value to be applied to the field automatically.', 'woocommerce-tm-extra-product-options' ),
		), $args );
	}

	public final function add_setting_default_value_multiple( $name = "" ) {
		return array(
			"id"      => $name . "_default_value",
			"default" => "",
			"type"    => "textarea",
			"tags"    => array( "class" => "t tm-no-editor", "id" => "builder_" . $name . "_default_value", "name" => "tm_meta[tmfbuilder][" . $name . "_default_value][]", "value" => "" ),
			"label"   => __( 'Default value', 'woocommerce-tm-extra-product-options' ),
			"desc"    => __( 'Enter a value to be applied to the field automatically.', 'woocommerce-tm-extra-product-options' ),
		);
	}

	private function get_weekdays() {
		$out = '<div class="tm-weekdays-picker-wrap">';
		// load wp translations
		if ( function_exists( 'wp_load_translations_early' ) ) {
			wp_load_translations_early();
			global $wp_locale;
			for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
				$out .= '<span class="tm-weekdays-picker"><label><input class="tm-weekday-picker" type="checkbox" value="' . esc_attr( $day_index ) . '"><span>' . $wp_locale->get_weekday( $day_index ) . '</span></label></span>';
			}
			// in case something goes wrong
		} else {
			$weekday[0] = /* translators: weekday */
				__( 'Sunday' );
			$weekday[1] = /* translators: weekday */
				__( 'Monday' );
			$weekday[2] = /* translators: weekday */
				__( 'Tuesday' );
			$weekday[3] = /* translators: weekday */
				__( 'Wednesday' );
			$weekday[4] = /* translators: weekday */
				__( 'Thursday' );
			$weekday[5] = /* translators: weekday */
				__( 'Friday' );
			$weekday[6] = /* translators: weekday */
				__( 'Saturday' );
			for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
				$out .= '<span class="tm-weekdays-picker"><label><input type="checkbox" value="' . esc_attr( $day_index ) . '"><span>' . $weekday[ $day_index ] . '</span></label></span>';
			}
		}
		$out .= '</div>';

		return $out;
	}

	private function remove_prefix( $str = "", $prefix = "" ) {
		if ( substr( $str, 0, strlen( $prefix ) ) == $prefix ) {
			$str = substr( $str, strlen( $prefix ) );
		}

		return $str;
	}

	private function _add_element_helper( $name = "", $value = "", $_value = array(), $additional_currencies = FALSE, $is_addon = FALSE ) {

		$return = array();

		if ( $value == "price" ) {

			if ( !empty( $additional_currencies ) && is_array( $additional_currencies ) ) {
				$_copy_value = $_value;
				$_value["label"] .= ' <span class="tm-choice-currency">' . TM_EPO_HELPER()->wc_base_currency() . '</span>';
				$return[] = $_value;
				foreach ( $additional_currencies as $ckey => $currency ) {
					$copy_value = $_copy_value;
					$copy_value["id"] .= "_" . $currency;
					$copy_value["label"] .= ' <span class="tm-choice-currency">' . $currency . '</span>';
					$copy_value["desc"] = sprintf( __( 'Leave it blank to calculate it automatically from the %s price', 'woocommerce-tm-extra-product-options' ), TM_EPO_HELPER()->wc_base_currency() );
					$copy_value["tags"]["id"] = "builder_" . $name . "_price" . "_" . $currency;
					$copy_value["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_price_" . $currency . "][]";
					$return[] = $copy_value;
				}
			} else {
				$return[] = $_value;
			}
		} elseif ( $value == "sale_price" ) {

			if ( !empty( $additional_currencies ) && is_array( $additional_currencies ) ) {
				$_copy_value = $_value;
				$_value["label"] .= ' <span class="tm-choice-currency">' . TM_EPO_HELPER()->wc_base_currency() . '</span>';
				$return[] = $_value;
				foreach ( $additional_currencies as $ckey => $currency ) {
					$copy_value = $_copy_value;
					$copy_value["id"] .= "_" . $currency;
					$copy_value["label"] .= ' <span class="tm-choice-currency">' . $currency . '</span>';
					$copy_value["desc"] = sprintf( __( 'Leave it blank to calculate it automatically from the %s sale price', 'woocommerce-tm-extra-product-options' ), TM_EPO_HELPER()->wc_base_currency() );
					$copy_value["tags"]["id"] = "builder_" . $name . "_sale_price" . "_" . $currency;
					$copy_value["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_sale_price_" . $currency . "][]";
					$return[] = $copy_value;
				}
			} else {
				$return[] = $_value;
			}
		} else {
			$return[] = $_value;
		}

		if ( isset( $_value["id"] ) ) {
			if ( $is_addon ) {
				$this->addons_attributes[] = $this->remove_prefix( $_value["id"], $name . "_" );
			}
			$this->default_attributes[] = $this->remove_prefix( $_value["id"], $name . "_" );
		}

		return $return;
	}

	public final function add_element( $name = "", $settings = array(), $is_addon = FALSE, $tabs_override = array() ) {

		$settings = apply_filters( 'tc_element_settings_override', $settings, $name );
		$tabs_override = apply_filters( 'tc_element_tabs_override', $tabs_override, $name, $settings, $is_addon );
		$options = array();
		$additional_currencies = TM_EPO_HELPER()->wc_aelia_cs_enabled_currencies();

		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $value["id"] ) ) {
					$this->default_attributes[] = $value["id"];
					if ( $is_addon ) {
						$value["id"] = $this->remove_prefix( $value["id"], $name . "_" );

						$this->addons_attributes[] = $value["id"];

						$value["id"] = $name . "_" . $value["id"];

						if ( !isset( $value["tags"] ) ) {
							$value["tags"] = array();
						}
						$value["tags"] = array_merge( $value["tags"], array(
								"id"    => "builder_" . $value["id"],
								"name"  => "tm_meta[tmfbuilder][" . $value["id"] . "][]",
								"value" => "",
							)
						);
					}

				}
				$options[] = $value;
			} else {
				$method = apply_filters('wc_epo_add_element_method', "add_setting_" . $value, $key, $value, $name, $settings, $is_addon, $tabs_override );
				
				$class_to_use = apply_filters('wc_epo_add_element_class', $this, $key, $value, $name, $settings, $is_addon, $tabs_override );
				if ( is_callable( array( $class_to_use, $method ) ) ) {
					$_value = $class_to_use->$method( $name );

					if ( isset( $_value['_multiple_values'] ) ) {
						foreach ( $_value['_multiple_values'] as $mkey => $mvalue ) {
							$r = $this->_add_element_helper( $name, $value, $mvalue, $additional_currencies, $is_addon );
							foreach ( $r as $rkey => $rvalue ) {
								$options[] = $rvalue;
							}
						}
					} else {
						$r = $this->_add_element_helper( $name, $value, $_value, $additional_currencies, $is_addon );
						foreach ( $r as $rkey => $rvalue ) {
							$options[] = $rvalue;
						}
					}

				}

			}
		}

		if ( !empty( $tabs_override ) ) {
			if ( !isset( $tabs_override["label_options"] ) ) {
				$tabs_override["label_options"] = 0;
			}
			if ( !isset( $tabs_override["general_options"] ) ) {
				$tabs_override["general_options"] = 0;
			}
			if ( !isset( $tabs_override["conditional_logic"] ) ) {
				$tabs_override["conditional_logic"] = 0;
			}
			if ( !isset( $tabs_override["css_settings"] ) ) {
				$tabs_override["css_settings"] = 0;
			}
			if ( !isset( $tabs_override["woocommerce_settings"] ) ) {
				$tabs_override["woocommerce_settings"] = 0;
			}
		} else {
			$tabs_override["label_options"] = 1;
			$tabs_override["general_options"] = 1;
			$tabs_override["conditional_logic"] = 1;
			$tabs_override["css_settings"] = 1;
			$tabs_override["woocommerce_settings"] = 1;
		}

		return array_merge(
			$this->_prepend_div( "", "tm-tabs" ),

			// add headers
			$this->_prepend_div( $name, "tm-tab-headers" ),
			!empty( $tabs_override["label_options"] ) ? $this->_prepend_tab( $name . "1", __( "Label options", 'woocommerce-tm-extra-product-options' ), "closed", "tma-tab-label" ) : array(),
			!empty( $tabs_override["general_options"] ) ? $this->_prepend_tab( $name . "2", __( "General options", 'woocommerce-tm-extra-product-options' ), "open", "tma-tab-general" ) : array(),
			!empty( $tabs_override["conditional_logic"] ) ? $this->_prepend_tab( $name . "3", __( "Conditional Logic", 'woocommerce-tm-extra-product-options' ), "closed", "tma-tab-logic" ) : array(),
			!empty( $tabs_override["css_settings"] ) ? $this->_prepend_tab( $name . "4", __( "CSS settings", 'woocommerce-tm-extra-product-options' ), "closed", "tma-tab-css" ) : array(),
			!empty( $tabs_override["woocommerce_settings"] ) ? $this->_prepend_tab( $name . "5", __( "WooCommerce settings", 'woocommerce-tm-extra-product-options' ), "closed", "tma-tab-woocommerce" ) : array(),
			$this->_append_div( $name ),

			// add Label options
			!empty( $tabs_override["label_options"] ) ? $this->_prepend_div( $name . "1" ) : array(),
			!empty( $tabs_override["label_options"] ) ? $this->_get_header_array( $name . "_header" ) : array(),
			!empty( $tabs_override["label_options"] ) ? $this->_get_divider_array( $name . "_divider", 0 ) : array(),
			!empty( $tabs_override["label_options"] ) ? $this->_append_div( $name . "1" ) : array(),

			// add General options
			!empty( $tabs_override["general_options"] ) ? $this->_prepend_div( $name . "2" ) : array(),
			!empty( $tabs_override["general_options"] ) ? apply_filters( 'wc_epo_admin_element_general_options', $options ) : array(),
			!empty( $tabs_override["general_options"] ) ? $this->_append_div( $name . "2" ) : array(),

			// add Contitional logic
			!empty( $tabs_override["conditional_logic"] ) ? $this->_prepend_div( $name . "3" ) : array(),
			!empty( $tabs_override["conditional_logic"] ) ? $this->_prepend_logic( $name ) : array(),
			!empty( $tabs_override["conditional_logic"] ) ? $this->_append_div( $name . "3" ) : array(),

			// add CSS settings
			!empty( $tabs_override["css_settings"] ) ? $this->_prepend_div( $name . "4" ) : array(),
			!empty( $tabs_override["css_settings"] ) ? apply_filters( 'wc_epo_admin_element_css_settings', array(
				array(
					"id"      => $name . "_class",
					"default" => "",
					"type"    => "text",
					"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_class", "name" => "tm_meta[tmfbuilder][" . $name . "_class][]", "value" => "" ),
					"label"   => __( 'Element class name', 'woocommerce-tm-extra-product-options' ),
					"desc"    => __( 'Enter an extra class name to add to this element', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"      => $name . "_container_id",
					"default" => "",
					"type"    => "text",
					"tags"    => array( "class" => "t", "id" => "builder_" . $name . "_container_id", "name" => "tm_meta[tmfbuilder][" . $name . "_container_id][]", "value" => "" ),
					"label"   => __( 'Element container id', 'woocommerce-tm-extra-product-options' ),
					"desc"    => __( 'Enter an id for the container of the element.', 'woocommerce-tm-extra-product-options' ),
				),
			) ) : array(),
			!empty( $tabs_override["css_settings"] ) ? $this->_append_div( $name . "4" ) : array(),

			// add WooCommerce settings
			!empty( $tabs_override["woocommerce_settings"] ) ? $this->_prepend_div( $name . "5" ) : array(),
			!empty( $tabs_override["woocommerce_settings"] ) ? apply_filters( 'wc_epo_admin_element_woocommerce_settings', array(
				array(
					"id"               => $name . "_include_tax_for_fee_price_type",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_include_tax_for_fee_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_include_tax_for_fee_price_type][]" ),
					"options"          => array(
						array( "text" => __( 'Inherit product setting', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "yes" ),
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "no" ),
					),
					"label"            => __( 'Include tax for Fee price type', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to include tax for Fee price type on this element.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_tax_class_for_fee_price_type",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_tax_class_for_fee_price_type", "name" => "tm_meta[tmfbuilder][" . $name . "_tax_class_for_fee_price_type][]" ),
					"options"          => $this->get_tax_classes(),
					"label"            => __( 'Tax class for Fee price type', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose the tax class for Fee price type on this element.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_label_in_cart",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_label_in_cart", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_label_in_cart][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
					),
					"label"            => __( 'Hide element label in cart', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element label in the cart or not.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_value_in_cart",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_value_in_cart", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_value_in_cart][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
						array( "text" => __( 'Yes, but show price', 'woocommerce-tm-extra-product-options' ), "value" => "price" ),
					),
					"label"            => __( 'Hide element value in cart', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element value in the cart or not.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_label_in_order",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_label_in_order", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_label_in_order][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
					),
					"label"            => __( 'Hide element label in order', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element label in the order or not.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_value_in_order",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_value_in_order", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_value_in_order][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
						array( "text" => __( 'Yes, but show price', 'woocommerce-tm-extra-product-options' ), "value" => "price" ),
					),
					"label"            => __( 'Hide element value in order', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element value in the order or not.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_label_in_floatbox",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_label_in_floatbox", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_label_in_floatbox][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
					),
					"label"            => __( 'Hide element label in floating totals box.', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element label in the floating totals box or not.', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $name . "_hide_element_value_in_floatbox",
					"message0x0_class" => "",
					"wpmldisable"      => 1,
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $name . "_hide_element_value_in_floatbox", "name" => "tm_meta[tmfbuilder][" . $name . "_hide_element_value_in_floatbox][]" ),
					"options"          => array(
						array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( 'Yes', 'woocommerce-tm-extra-product-options' ), "value" => "hidden" ),
					),
					"label"            => __( 'Hide element value in floating totals box', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Choose whether to hide the element value in the floating totals box or not.', 'woocommerce-tm-extra-product-options' ),
				),
			) ) : array(),
			!empty( $tabs_override["woocommerce_settings"] ) ? $this->_append_div( $name . "5" ) : array(),

			$this->_append_div( "" )
		);
	}

	private function _prepend_tab( $id = "", $label = "", $closed = "closed", $boxclass = "" ) {
		if ( !empty( $closed ) ) {
			$closed = " " . $closed;
		}
		if ( !empty( $boxclass ) ) {
			$boxclass = " " . $boxclass;
		}

		return array( array(
			"id"      => $id . "_custom_tabstart",
			"default" => "",
			"type"    => "custom",
			"nodiv"   => 1,
			"html"    => "<div class='tm-box" . $boxclass . "'>"
				. "<h4 data-id='" . $id . "-tab' class='tab-header" . $closed . "'>"
				. $label
				. "<span class='tcfa tcfa-angle-down tm-arrow'></span>"
				. "</h4></div>",
			"label"   => "",
			"desc"    => "",
		) );
	}

	private function _prepend_div( $id = "", $tmtab = "tm-tab" ) {
		if ( !empty( $id ) ) {
			$id .= "-tab";
		}

		return array( array(
			"id"      => $id . "_custom_divstart",
			"default" => "",
			"type"    => "custom",
			"nodiv"   => 1,
			"html"    => "<div class='transition " . $tmtab . " " . $id . "'>",
			"label"   => "",
			"desc"    => "",
		) );
	}

	private function _append_div( $id = "" ) {
		return array( array(
			"id"      => $id . "_custom_divend",
			"default" => "",
			"type"    => "custom",
			"nodiv"   => 1,
			"html"    => "</div>",
			"label"   => "",
			"desc"    => "",
		) );
	}

	private function builder_showlogic() {
		$h = "";
		$h .= '<div class="builder-logic-div">';
		$h .= '<div class="tm-row nopadding">';
		$h .= '<select class="epo-rule-toggle"><option value="show">' . __( 'Show', 'woocommerce-tm-extra-product-options' ) . '</option><option value="hide">' . __( 'Hide', 'woocommerce-tm-extra-product-options' ) . '</option></select><span>' . __( 'this field if', 'woocommerce-tm-extra-product-options' ) . '</span><select class="epo-rule-what"><option value="all">' . __( 'all', 'woocommerce-tm-extra-product-options' ) . '</option><option value="any">' . __( 'any', 'woocommerce-tm-extra-product-options' ) . '</option></select><span>' . __( 'of these rules match', 'woocommerce-tm-extra-product-options' ) . ':</span>';
		$h .= '</div>';

		$h .= '<div class="tm-logic-wrapper">';

		$h .= '</div>';
		$h .= '</div>';

		return $h;
	}

	/**
	 * Common element options.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $id element internal id. (key from $this->elements_array)
	 *
	 * @return array List of common element options adjusted by element internal id.
	 */
	private function _get_header_array( $id = "header" ) {
		return apply_filters( 'wc_epo_admin_element_label_options',
			array(
				array(
					"id"          => $id . "_size",
					"wpmldisable" => 1,
					"default"     => ($id == "section_header") ? "3" : "10",
					"type"        => "select",
					"tags"        => array( "id" => "builder_" . $id . "_size", "name" => "tm_meta[tmfbuilder][" . $id . "_size][]" ),
					"options"     =>
						($id != "section_header") ?
							array(
								array( "text" => __( "H1", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
								array( "text" => __( "H2", 'woocommerce-tm-extra-product-options' ), "value" => "2" ),
								array( "text" => __( "H3", 'woocommerce-tm-extra-product-options' ), "value" => "3" ),
								array( "text" => __( "H4", 'woocommerce-tm-extra-product-options' ), "value" => "4" ),
								array( "text" => __( "H5", 'woocommerce-tm-extra-product-options' ), "value" => "5" ),
								array( "text" => __( "H6", 'woocommerce-tm-extra-product-options' ), "value" => "6" ),
								array( "text" => __( "p", 'woocommerce-tm-extra-product-options' ), "value" => "7" ),
								array( "text" => __( "div", 'woocommerce-tm-extra-product-options' ), "value" => "8" ),
								array( "text" => __( "span", 'woocommerce-tm-extra-product-options' ), "value" => "9" ),
								array( "text" => __( "label", 'woocommerce-tm-extra-product-options' ), "value" => "10" ),
							) :
							array(
								array( "text" => __( "H1", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
								array( "text" => __( "H2", 'woocommerce-tm-extra-product-options' ), "value" => "2" ),
								array( "text" => __( "H3", 'woocommerce-tm-extra-product-options' ), "value" => "3" ),
								array( "text" => __( "H4", 'woocommerce-tm-extra-product-options' ), "value" => "4" ),
								array( "text" => __( "H5", 'woocommerce-tm-extra-product-options' ), "value" => "5" ),
								array( "text" => __( "H6", 'woocommerce-tm-extra-product-options' ), "value" => "6" ),
								array( "text" => __( "p", 'woocommerce-tm-extra-product-options' ), "value" => "7" ),
								array( "text" => __( "div", 'woocommerce-tm-extra-product-options' ), "value" => "8" ),
								array( "text" => __( "span", 'woocommerce-tm-extra-product-options' ), "value" => "9" ),
							)
				,
					"label"       => __( "Label type", 'woocommerce-tm-extra-product-options' ),
					"desc"        => "",
				),
				array(
					"id"               => $id . "_title",
					"message0x0_class" => "builder_hide_for_variation",
					"default"          => "",
					"type"             => "text",
					"tags"             => array( "class" => "t tm-header-title", "id" => "builder_" . $id . "_title", "name" => "tm_meta[tmfbuilder][" . $id . "_title][]", "value" => "" ),
					"label"            => __( 'Label', 'woocommerce-tm-extra-product-options' ),
					"desc"             => "",
				),
				array(
					"id"          => $id . "_title_position",
					"wpmldisable" => 1,
					"default"     => "",
					"type"        => "select",
					"tags"        => array( "id" => "builder_" . $id . "_title_position", "name" => "tm_meta[tmfbuilder][" . $id . "_title_position][]" ),
					"options"     => array(
						array( "text" => __( "Above field", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( "Left of the field", 'woocommerce-tm-extra-product-options' ), "value" => "left" ),
						array( "text" => __( "Right of the field", 'woocommerce-tm-extra-product-options' ), "value" => "right" ),
						array( "text" => __( "Disable", 'woocommerce-tm-extra-product-options' ), "value" => "disable" ),
					),
					"label"       => __( "Label position", 'woocommerce-tm-extra-product-options' ),
					"desc"        => "",
				),
				array(
					"id"          => $id . "_title_color",
					"wpmldisable" => 1,
					"default"     => "",
					"type"        => "text",
					"tags"        => array( "data-show-input" => "true", "data-show-initial" => "true", "data-allow-empty" => "true", "data-show-alpha" => "false", "data-show-palette" => "false", "data-clickout-fires-change" => "true", "data-show-buttons" => "false", "data-preferred-format" => "hex", "class" => "tm-color-picker", "id" => "builder_" . $id . "_title_color", "name" => "tm_meta[tmfbuilder][" . $id . "_title_color][]", "value" => "" ),
					"label"       => __( 'Label color', 'woocommerce-tm-extra-product-options' ),
					"desc"        => __( 'Leave empty for default value', 'woocommerce-tm-extra-product-options' ),
				),
				array(
					"id"               => $id . "_subtitle",
					"message0x0_class" => "builder_hide_for_variation",
					"default"          => "",
					"type"             => "textarea",
					"tags"             => array( "id" => "builder_" . $id . "_subtitle", "name" => "tm_meta[tmfbuilder][" . $id . "_subtitle][]" ),
					"label"            => __( "Subtitle", 'woocommerce-tm-extra-product-options' ),
					"desc"             => "",
				),
				array(
					"id"               => $id . "_subtitle_position",
					"wpmldisable"      => 1,
					"message0x0_class" => "builder_hide_for_variation",
					"default"          => "",
					"type"             => "select",
					"tags"             => array( "id" => "builder_" . $id . "_subtitle_position", "name" => "tm_meta[tmfbuilder][" . $id . "_subtitle_position][]" ),
					"options"          => array(
						array( "text" => __( "Above field", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
						array( "text" => __( "Below field", 'woocommerce-tm-extra-product-options' ), "value" => "below" ),
						array( "text" => __( "Tooltip", 'woocommerce-tm-extra-product-options' ), "value" => "tooltip" ),
						array( "text" => __( "Icon tooltip left", 'woocommerce-tm-extra-product-options' ), "value" => "icontooltipleft" ),
						array( "text" => __( "Icon tooltip right", 'woocommerce-tm-extra-product-options' ), "value" => "icontooltipright" ),
					),
					"label"            => __( "Subtitle position", 'woocommerce-tm-extra-product-options' ),
					"desc"             => "",
				),
				array(
					"id"               => $id . "_subtitle_color",
					"wpmldisable"      => 1,
					"message0x0_class" => "builder_hide_for_variation",
					"default"          => "",
					"type"             => "text",
					"tags"             => array( "class" => "tm-color-picker", "id" => "builder_" . $id . "_subtitle_color", "name" => "tm_meta[tmfbuilder][" . $id . "_subtitle_color][]", "value" => "" ),
					"label"            => __( 'Subtitle color', 'woocommerce-tm-extra-product-options' ),
					"desc"             => __( 'Leave empty for default value', 'woocommerce-tm-extra-product-options' ),
				),
			)
		);
	}

	/**
	 * Sets element divider option.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $id element internal id. (key from $this->elements_array)
	 *
	 * @return array Element divider options adjusted by element internal id.
	 */
	private function _get_divider_array( $id = "divider", $noempty = 1 ) {
		$_divider = array(
			array(
				"id"               => $id . "_type",
				"wpmldisable"      => 1,
				"message0x0_class" => "builder_hide_for_variation",
				"default"          => "hr",
				"type"             => "select",
				"tags"             => array( "id" => "builder_" . $id . "_type", "name" => "tm_meta[tmfbuilder][" . $id . "_type][]" ),
				"options"          => array(
					array( "text" => __( "Horizontal rule", 'woocommerce-tm-extra-product-options' ), "value" => "hr" ),
					array( "text" => __( "Divider", 'woocommerce-tm-extra-product-options' ), "value" => "divider" ),
					array( "text" => __( "Padding", 'woocommerce-tm-extra-product-options' ), "value" => "padding" ),
				),
				"label"            => __( "Divider type", 'woocommerce-tm-extra-product-options' ),
				"desc"             => "",
			),
		);
		if ( empty( $noempty ) ) {
			$_divider[0]["default"] = "none";
			array_push( $_divider[0]["options"], array( "text" => __( "None", 'woocommerce-tm-extra-product-options' ), "value" => "none" ) );
		}

		return $_divider;
	}

	private function _prepend_logic( $id = "" ) {
		return apply_filters( 'wc_epo_admin_element_conditional_logic', array(
			array(
				"id"      => $id . "_uniqid",
				"default" => "",
				"nodiv"   => 1,
				"type"    => "hidden",
				"tags"    => array( "class" => "tm-builder-element-uniqid", "name" => "tm_meta[tmfbuilder][" . $id . "_uniqid][]", "value" => "" ),
				"label"   => "",
				"desc"    => "",
			),
			array(
				"id"      => $id . "_clogic",
				"default" => "",
				"nodiv"   => 1,
				"type"    => "hidden",
				"tags"    => array( "class" => "tm-builder-clogic", "name" => "tm_meta[tmfbuilder][" . $id . "_clogic][]", "value" => "" ),
				"label"   => "",
				"desc"    => "",
			),
			array(
				"id"      => $id . "_logic",
				"default" => "",
				"type"    => "select",
				"tags"    => array( "class" => "activate-element-logic", "id" => "divider_element_logic", "name" => "tm_meta[tmfbuilder][" . $id . "_logic][]" ),
				"options" => array(
					array( "text" => __( "No", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
					array( "text" => __( "Yes", 'woocommerce-tm-extra-product-options' ), "value" => "1" ),
				),
				"extra"   => $this->builder_showlogic(),
				"label"   => __( "Element Conditional Logic", 'woocommerce-tm-extra-product-options' ),
				"desc"    => __( "Enable conditional logic for showing or hiding this element.", 'woocommerce-tm-extra-product-options' ),
			),
		) );
	}

	public function template_bitem( $args ) {
		/*
		 * args
		 * element
		 * width
		 * width_display
		 * internal_name
		 * fields
		 * label
		 * desc
		 * icon
		*/

		$internal_name_input = '<input type="text" value="' . esc_attr( $args["internal_name"] ) . '" name="tm_meta[tmfbuilder][' . $args["element"] . '_internal_name][]" class="t tm-internal-name">';
		$out = "";
		$out .= "<div class='bitem element-" . $args["element"] . " " . $args['width'] . "'>"
			. "<input class='builder_element_type' name='tm_meta[tmfbuilder][element_type][]' type='hidden' value='" . $args["element"] . "' />"
			. "<input class='div_size' name='tm_meta[tmfbuilder][div_size][]' type='hidden' value='" . $args["width"] . "' />"
			. "<div class='hstc2'>"
			. "<div class='bitem-inner'>"
			. "<div class='tmicon size'>" . $args["width_display"] . "</div>"
			. "<button type='button' class='tmicon tcfa tcfa-sort move'></button>"
			. "<button type='button' class='tmicon tcfa tcfa-minus minus'></button>"
			. "<button type='button' class='tmicon tcfa tcfa-plus plus'></button>"

			. "<button type='button' class='tmicon tcfa tcfa-pencil edit'></button>"
			. "<button type='button' class='tmicon tcfa tcfa-copy clone'></button>"
			. "<button type='button' class='tmicon tcfa tcfa-times delete'></button>"
			. "</div>"
			. "<div class='bitem-inner-info'>"
			. "<div class='tm-label-icon'><i class='tmfa tcfa " . $args["icon"] . "'></i></div>"
			. "<div class='tm-label-desc" . (($args["internal_name"] !== "") ? " tc-has-value" : " tc-empty-value") . "'>"
			. "<div class='tm-element-label'>" . $args["label"] . "</div>"
			. "<div class='tm-internal-label'>" . $args["internal_name"] . "</div>"
			. "</div>"
			. "<div class='tm-label-desc-edit tm-hidden'>" . $internal_name_input . "</div>"
			. "<div class='tm-label'>" . $args["desc"] . "</div>"
			. "<div class='tm-label-line'>"
			. "<div class='tm-label-line-inner'></div>"
			. "</div>"
			. "</div>"
			. "<div class='inside'>"
			. "<div class='manager'>"
			. "<div class='builder_element_wrap'>"

			. $args["fields"]

			. "</div>"//builder_element_wrap
			. "</div>"//manager
			. "</div>"//inside
			. "</div>"//hstc2
			. "</div>";//bitem
		return $out;
	}

	/**
	 * Generates all hidden elements for use in jQuery.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_elements( $echo = 0, $wpml_is_original_product = TRUE ) {
		$out1 = '';

		foreach ( $this->get_elements() as $element => $settings ) {
			if ( isset( $this->elements_array[ $element ] ) ) {

				// double quotes are problematic to json_encode.
				$settings["name"] = str_replace( '"', "'", $settings["name"] );
				$_temp_option = $this->elements_array[ $element ];
				$fields = '';
				foreach ( $_temp_option as $key => $value ) {
					$fields .= TM_EPO_HTML()->tm_make_field( $value, 0 );
				}
				$out1 .= $this->template_bitem( array(
					'element'       => $element,
					'width'         => $settings["width"],
					'width_display' => $settings["width_display"],
					'internal_name' => $settings["name"],
					'label'         => $settings["name"],
					'fields'        => $fields,
					'desc'          => '&nbsp;',
					'icon'          => $settings["icon"],
				) );

			}
		}
		$drag_elements = TM_EPO_ADMIN_GLOBAL()->js_element_data( "ditem" );
		$out = '<div class="builder_elements closed"><div class="tc-handle tmicon tcfa tcfa-caret-up"></div><div class="builder_hidden_elements" data-template="' . esc_html( json_encode( array( "html" => $out1 ) ) ) . '"></div>'
			. '<div class="builder_hidden_section" data-template="' . esc_html( json_encode( array( "html" => $this->section_elements( 0, $wpml_is_original_product ) ) ) ) . '"></div>'
			. (($wpml_is_original_product) ? '<div class="builder_drag_elements">' . $drag_elements . '</div>' : '')
			. (($wpml_is_original_product) ? '<div class="builder_actions">' . '<button type="button" class="builder_add_section tc tc-button"><i class="tcfa tcfa-plus-square"></i> ' . __( "Add section", 'woocommerce-tm-extra-product-options' ) . '</button>' . '</div>' : '')
			. "</div>";
		if ( empty( $echo ) ) {
			return $out;
		} else {
			echo $out;
		}
	}

	private function _section_template( $out = "", $size = "", $section_size = "", $sections_slides = "", $elements = "", $wpml_is_original_product = TRUE, $sections_internal_name = FALSE ) {
		if ( $sections_internal_name === FALSE ) {
			$sections_internal_name = __( "Section", 'woocommerce-tm-extra-product-options' );
		}

		$adder_prepend = '<div class="bitem-add tc-prepend tma-nomove"><div class="tm-add-element-action"><button type="button" title="' . __( "Add element", 'woocommerce-tm-extra-product-options' ) . '" class="builder-add-element tc-button tc-prepend tmfa tcfa tcfa-plus"></button></div></div>';
		$adder_append = '<div class="bitem-add tc-append tma-nomove"><div class="tm-add-element-action"><button type="button" title="' . __( "Add element", 'woocommerce-tm-extra-product-options' ) . '" class="builder-add-element tc-button tc-append tmfa tcfa tcfa-plus"></button></div></div>';
		if ( !$wpml_is_original_product ) {
			$adder_prepend = $adder_append = '';
		}

		$internal_name_input = '<input type="text" value="' . esc_attr( $sections_internal_name ) . '" name="tm_meta[tmfbuilder][sections_internal_name][]" class="t tm-internal-name">';
		$t0 = "<div class='builder_wrapper " . $section_size . "'><div class='builder-section-wrap'>";
		$t1 = "<div class='section_elements closed'>"
			. $out
			. "</div>"
			. "<div class='btitle'>"
			. "<div class='tmicon size'>" . $size . "</div>"
			. (($wpml_is_original_product) ? "<button type='button' class='tmicon tcfa tcfa-sort move'></button>" : "")
			. (($wpml_is_original_product) ? "<button type='button' class='tmicon tcfa tcfa-minus minus'></button>" : "")
			. (($wpml_is_original_product) ? "<button type='button' class='tmicon tcfa tcfa-plus plus'></button>" : "")

			. (($wpml_is_original_product) ? "<button type='button' class='tmicon tcfa tcfa-times delete'></button>" : "")
			. "<button type='button' class='tmicon tcfa tcfa-pencil edit'></button>"
			. (($wpml_is_original_product) ? "<button type='button' class='tmicon tcfa tcfa-copy clone'></button>" : "")
			. "<button type='button' class='tmicon tcfa tcfa-caret-down fold'></button>"
			. "<div class='tm-label-desc" . (($sections_internal_name !== "") ? " tc-has-value" : " tc-empty-value") . "'>"
			. "<div class='tm-element-label'>" . __( "Section", 'woocommerce-tm-extra-product-options' ) . "</div>"
			. "<div class='tm-internal-label'>" . $sections_internal_name . "</div>"
			. "</div>"
			. "<div class='tm-label-desc-edit tm-hidden'>" . $internal_name_input . "</div>"
			. "</div>";

		$t2 = "</div></div>";
		$h = '';

		if ( is_array( $elements ) ) {
			$elements = array_values( $elements );
		}
		if ( $sections_slides !== "" && is_array( $elements ) ) {
			$sections_slides = explode( ",", $sections_slides );


			$s = 0;
			$tabs = "";
			$add = '<div class="tm-box tm-add-box"><h4 class="tm-add-tab"><span class="tcfa tcfa-plus"></span></h4></div>';
			if ( !$wpml_is_original_product ) {
				$add = "";
			}
			foreach ( $sections_slides as $key => $value ) {

				$tab = '<div class="tm-box"><h4 class="tm-slider-wizard-header" data-id="tm-slide' . $s . '">' . ($s + 1) . '</h4></div>';
				$tabs .= $tab;

				$s++;

			}

			$c = 0;
			$s = 0;
			$h .= "<div class='builder_wrapper tm-slider-wizard " . $section_size . "'><div class='builder-section-wrap'>" . $t1;
			$h .= '<div class="transition tm-slider-wizard-headers">' . $tabs . $add . '</div>';
			$h .= $adder_prepend;
			foreach ( $sections_slides as $key => $value ) {

				$value = intval( $value );

				$h .= "<div class='bitem_wrapper tm-slider-wizard-tab tm-slide" . $s . "'>";
				for ( $_s = $c; $_s < ($c + $value); $_s++ ) {
					if ( isset( $elements[ $_s ] ) ) {
						$h .= $elements[ $_s ];
					}
				}
				$h .= "</div>";

				$c = $c + $value;
				$s++;

			}
			$h .= $adder_append;
			$h .= $t2;
		} else {
			if ( is_array( $elements ) ) {
				$elements = implode( "", $elements );
			}

			$h = $t0 . $t1 . $adder_prepend . "<div class='bitem_wrapper'>" . $elements . "</div>" . $adder_append . $t2;
		}


		return $h;
	}

	/**
	 * Generates all hidden sections for use in jQuery.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function section_elements( $echo = 0, $wpml_is_original_product = TRUE ) {
		$out = '';

		foreach ( $this->_section_elements as $k => $v ) {
			$out .= TM_EPO_HTML()->tm_make_field( $v, 0 );
		}

		$out = $this->_section_template( $out, $this->sizer["w100"], "", "", "", $wpml_is_original_product, FALSE );

		if ( empty( $echo ) ) {
			return $out;
		} else {
			echo $out;
		}

	}

	private function _tm_clear_array_values( $val ) {
		if ( is_array( $val ) ) {
			return array_map( array( $this, '_tm_clear_array_values' ), $val );
		} else {
			return "";
		}
	}

	/**
	 * Generates all saved elements.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_saved_elements( $echo = 0, $post_id = 0, $current_post_id = 0, $wpml_is_original_product = TRUE ) {
		$builder = tc_get_post_meta( $post_id, 'tm_meta', TRUE );
		$current_builder = tc_get_post_meta( $current_post_id, 'tm_meta_wpml', TRUE );
		if ( !$current_builder ) {
			$current_builder = array();
		} else {
			if ( !isset( $current_builder['tmfbuilder'] ) ) {
				$current_builder['tmfbuilder'] = array();
			}
			$current_builder = $current_builder['tmfbuilder'];
		}
		$out = '';
		if ( !isset( $builder['tmfbuilder'] ) ) {
			if ( !is_array( $builder ) ) {
				$builder = array();
			}
			$builder['tmfbuilder'] = array();
		}
		$builder = $builder['tmfbuilder'];

		/* only check for element_type meta
		   as if it exists div_size will exist too
		   unless database has been compromised
		*/
		if ( !empty( $post_id ) && is_array( $builder ) && count( $builder ) > 0 && isset( $builder['sections'] ) && isset( $builder['element_type'] ) && is_array( $builder['element_type'] ) && count( $builder['element_type'] ) > 0 ) {
			// All the elements
			$_elements = $builder['element_type'];
			// All element sizes
			$_div_size = $builder['div_size'];

			// All sections (holds element count for each section)
			$_sections = $builder['sections'];
			// All section sizes
			$_sections_size = $builder['sections_size'];

			$_sections_slides = isset( $builder['sections_slides'] ) ? $builder['sections_slides'] : '';

			$_sections_internal_name = isset( $builder['sections_internal_name'] ) ? $builder['sections_internal_name'] : '';

			if ( !is_array( $_sections ) ) {
				$_sections = array( count( $_elements ) );
			}
			if ( !is_array( $_sections_size ) ) {
				$_sections_size = array_fill( 0, count( $_sections ), "w100" );
			}

			if ( !is_array( $_sections_slides ) ) {
				$_sections_slides = array_fill( 0, count( $_sections ), "" );
			}

			if ( !is_array( $_sections_internal_name ) ) {
				$_sections_internal_name = array_fill( 0, count( $_sections ), FALSE );
			}

			$_helper_counter = 0;
			$_this_elements = $this->get_elements();

			$additional_currencies = TM_EPO_HELPER()->wc_aelia_cs_enabled_currencies();

			$t = array();

			$_counter = array();
			$id_counter = array();
			for ( $_s = 0; $_s < count( $_sections ); $_s++ ) {

				$section_html = '';
				foreach ( $this->_section_elements as $_sk => $_sv ) {
					$transition_counter = $_s;
					$section_use_wpml = FALSE;
					if ( isset( $current_builder["sections_uniqid"] )
						&& isset( $builder["sections_uniqid"] )
						&& isset( $builder["sections_uniqid"][ $_s ] )
					) {
						// get index of element id in internal array
						$get_current_builder_uniqid_index = array_search( $builder["sections_uniqid"][ $_s ], $current_builder["sections_uniqid"] );
						if ( $get_current_builder_uniqid_index !== NULL && $get_current_builder_uniqid_index !== FALSE ) {
							$transition_counter = $get_current_builder_uniqid_index;
							$section_use_wpml = TRUE;
						}
					}
					if ( isset( $builder[ $_sv['id'] ] ) && isset( $builder[ $_sv['id'] ][ $_s ] ) ) {
						$_sv['default'] = $builder[ $_sv['id'] ][ $_s ];
						if ( $section_use_wpml
							&& isset( $current_builder[ $_sv['id'] ] )
							&& isset( $current_builder[ $_sv['id'] ][ $transition_counter ] )
						) {
							$_sv['default'] = $current_builder[ $_sv['id'] ][ $transition_counter ];
						}
					}
					if ( isset( $_sv['tags']['id'] ) ) {
						// we assume that $_sv['tags']['name'] exists if tag id is set
						$_name = str_replace( array( "[", "]" ), "", $_sv['tags']['name'] );
						$_sv['tags']['id'] = $_name . $_s;
					}
					if ( $_sk == 'sectionuniqid' && !isset( $builder[ $_sv['id'] ] ) ) {
						$_sv['default'] = TM_EPO_HELPER()->tm_uniqid();
					}
					if ( $post_id != $current_post_id && !empty( $_sv['wpmldisable'] ) ) {
						$_sv['disabled'] = 1;
					}
					if ($_sv['id'] === "sections_clogic"){
						$_sv['default'] = stripslashes_deep($_sv['default']);
					}
					
					$section_html .= TM_EPO_HTML()->tm_make_field( $_sv, 0 );
				}

				$elements_html = '';
				$elements_html_array = array();
				for ( $k0 = $_helper_counter; $k0 < intval( $_helper_counter + intval( $_sections[ $_s ] ) ); $k0++ ) {
					if ( isset( $_elements[ $k0 ] ) ) {
						if ( isset( $this->elements_array[ $_elements[ $k0 ] ] ) ) {
							$elements_html_array[ $k0 ] = "";
							$_temp_option = $this->elements_array[ $_elements[ $k0 ] ];
							if ( !isset( $_counter[ $_elements[ $k0 ] ] ) ) {
								$_counter[ $_elements[ $k0 ] ] = 0;
							} else {
								$_counter[ $_elements[ $k0 ] ]++;
							}
							$internal_name = $_this_elements[ $_elements[ $k0 ] ]["name"];
							if ( isset( $builder[ $_elements[ $k0 ] . '_internal_name' ] )
								&& isset( $builder[ $_elements[ $k0 ] . '_internal_name' ][ $_counter[ $_elements[ $k0 ] ] ] )
							) {
								$internal_name = $builder[ $_elements[ $k0 ] . '_internal_name' ][ $_counter[ $_elements[ $k0 ] ] ];
								if ( $section_use_wpml
									&& isset( $current_builder[ $_elements[ $k0 ] . '_internal_name' ] )
									&& isset( $current_builder[ $_elements[ $k0 ] . '_internal_name' ][ $_counter[ $_elements[ $k0 ] ] ] )
								) {
									$internal_name = $current_builder[ $_elements[ $k0 ] . '_internal_name' ][ $_counter[ $_elements[ $k0 ] ] ];
								}
							}
							
							$fields = '';
							foreach ( $_temp_option as $key => $value ) {
								$transition_counter = $_counter[ $_elements[ $k0 ] ];
								$use_wpml = FALSE;
								if ( isset( $value['id'] ) ) {
									$_vid = $value['id'];
									if ( !isset( $t[ $_vid ] ) ) {
										$t[ $_vid ] = isset( $builder[ $value['id'] ] )
											? $builder[ $value['id'] ]
											: NULL;
										if ( $t[ $_vid ] !== NULL ) {
											if ( $post_id != $current_post_id && !empty( $value['wpmldisable'] ) ) {
												$value['disabled'] = 1;
											}

										}
									} elseif ( $t[ $_vid ] !== NULL ) {
										if ( $post_id != $current_post_id && !empty( $value['wpmldisable'] ) ) {
											$value['disabled'] = 1;
										}
									}
									if ( isset( $current_builder[ $_elements[ $k0 ] . "_uniqid" ] )
										&& isset( $builder[ $_elements[ $k0 ] . "_uniqid" ] )
										&& isset( $builder[ $_elements[ $k0 ] . "_uniqid" ][ $_counter[ $_elements[ $k0 ] ] ] )
									) {
										// get index of element id in internal array
										$get_current_builder_uniqid_index = array_search( $builder[ $_elements[ $k0 ] . "_uniqid" ][ $_counter[ $_elements[ $k0 ] ] ], $current_builder[ $_elements[ $k0 ] . "_uniqid" ] );
										if ( $get_current_builder_uniqid_index !== NULL && $get_current_builder_uniqid_index !== FALSE ) {
											$transition_counter = $get_current_builder_uniqid_index;
											$use_wpml = TRUE;
										}
									}
									if ( $t[ $_vid ] !== NULL && count( $t[ $_vid ] ) > 0 && isset( $value['default'] ) && isset( $t[ $_vid ][ $_counter[ $_elements[ $k0 ] ] ] ) ) {
										$value['default'] = $t[ $_vid ][ $_counter[ $_elements[ $k0 ] ] ];

										if ( $use_wpml
											&& isset( $current_builder[ $value['id'] ] )
											&& isset( $current_builder[ $value['id'] ][ $transition_counter ] )
										) {
											$value['default'] = $current_builder[ $value['id'] ][ $transition_counter ];

										}
										if ( $value['type'] == 'number' ) {
											$value['default'] = tc_convert_local_numbers( $value['default'] );
										}
									}
									if ( $_elements[ $k0 ].'_clogic' === $value['id'] ){
										$value['default'] = stripslashes_deep($value['default']);
									}
									
									if ( $value['id'] == "variations_options" ) {
										if ( $section_use_wpml
											&& isset( $current_builder[ $value['id'] ] )
										) {
											$value['html'] = $this->builder_sub_variations_options( isset( $current_builder[ $value['id'] ] ) ? $current_builder[ $value['id'] ] : NULL, $current_post_id );
										} else {
											$value['html'] = $this->builder_sub_variations_options( isset( $builder[ $value['id'] ] ) ? $builder[ $value['id'] ] : NULL, $current_post_id );
										}

									} elseif ( (isset( $value["tmid"] ) && $value["tmid"] == "populate") &&
										($this->all_elements[ $_elements[ $k0 ] ]["type"] == "multiple"
											|| $this->all_elements[ $_elements[ $k0 ] ]["type"] == "multipleall"
											|| $this->all_elements[ $_elements[ $k0 ] ]["type"] == "multiplesingle")
									) {


										/* holds the default checked values (cannot be cached in $t[$_vid]) */
										$_default_value = isset( $builder[ 'multiple_' . $value['id'] . '_default_value' ] ) ? $builder[ 'multiple_' . $value['id'] . '_default_value' ] : NULL;

										if ( is_null( $t[ $_vid ] ) ) {
											// needed for WPML
											$_titles_base = isset( $builder[ 'multiple_' . $value['id'] . '_title' ] )
												? $builder[ 'multiple_' . $value['id'] . '_title' ]
												: NULL;
											$_titles = isset( $builder[ 'multiple_' . $value['id'] . '_title' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_title' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_title' ]
													: $builder[ 'multiple_' . $value['id'] . '_title' ]
												: NULL;

											$_values_base = isset( $builder[ 'multiple_' . $value['id'] . '_value' ] )
												? $builder[ 'multiple_' . $value['id'] . '_value' ]
												: NULL;
											$_values = isset( $builder[ 'multiple_' . $value['id'] . '_value' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_value' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_value' ]
													: $builder[ 'multiple_' . $value['id'] . '_value' ]
												: NULL;

											$_prices_base = isset( $builder[ 'multiple_' . $value['id'] . '_price' ] )
												? $builder[ 'multiple_' . $value['id'] . '_price' ]
												: NULL;
											$_prices = isset( $builder[ 'multiple_' . $value['id'] . '_price' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_price' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_price' ]
													: $builder[ 'multiple_' . $value['id'] . '_price' ]
												: NULL;

											$_images_base = isset( $builder[ 'multiple_' . $value['id'] . '_image' ] )
												? $builder[ 'multiple_' . $value['id'] . '_image' ]
												: NULL;
											$_images = isset( $builder[ 'multiple_' . $value['id'] . '_image' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_image' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_image' ]
													: $builder[ 'multiple_' . $value['id'] . '_image' ]
												: NULL;

											$_imagesc_base = isset( $builder[ 'multiple_' . $value['id'] . '_imagec' ] )
												? $builder[ 'multiple_' . $value['id'] . '_imagec' ]
												: NULL;
											$_imagesc = isset( $builder[ 'multiple_' . $value['id'] . '_imagec' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_imagec' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_imagec' ]
													: $builder[ 'multiple_' . $value['id'] . '_imagec' ]
												: NULL;

											$_imagesp_base = isset( $builder[ 'multiple_' . $value['id'] . '_imagep' ] )
												? $builder[ 'multiple_' . $value['id'] . '_imagep' ]
												: NULL;
											$_imagesp = isset( $builder[ 'multiple_' . $value['id'] . '_imagep' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_imagep' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_imagep' ]
													: $builder[ 'multiple_' . $value['id'] . '_imagep' ]
												: NULL;

											$_imagesl_base = isset( $builder[ 'multiple_' . $value['id'] . '_imagel' ] )
												? $builder[ 'multiple_' . $value['id'] . '_imagel' ]
												: NULL;
											$_imagesl = isset( $builder[ 'multiple_' . $value['id'] . '_imagel' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_imagel' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_imagel' ]
													: $builder[ 'multiple_' . $value['id'] . '_imagel' ]
												: NULL;

											$_prices_type_base = isset( $builder[ 'multiple_' . $value['id'] . '_price_type' ] )
												? $builder[ 'multiple_' . $value['id'] . '_price_type' ]
												: NULL;
											$_prices_type = isset( $builder[ 'multiple_' . $value['id'] . '_price_type' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_price_type' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_price_type' ]
													: $builder[ 'multiple_' . $value['id'] . '_price_type' ]
												: NULL;

											$_sale_prices_base = isset( $builder[ 'multiple_' . $value['id'] . '_sale_price' ] )
												? $builder[ 'multiple_' . $value['id'] . '_sale_price' ]
												: NULL;
											$_sale_prices = isset( $builder[ 'multiple_' . $value['id'] . '_sale_price' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_sale_price' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_sale_price' ]
													: $builder[ 'multiple_' . $value['id'] . '_sale_price' ]
												: NULL;

											$c_prices_base = array();
											$c_prices = array();
											$c_sale_prices_base = array();
											$c_sale_prices = array();
											if ( !empty( $additional_currencies ) && is_array( $additional_currencies ) ) {
												foreach ( $additional_currencies as $ckey => $currency ) {
													$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $currency );
													$c_prices_base[ $currency ] = isset( $builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ] )
														? $builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ]
														: NULL;
													$c_prices[ $currency ] = isset( $builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ] )
														? isset( $current_builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ] )
															? $current_builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ]
															: $builder[ 'multiple_' . $value['id'] . '_price' . $mt_prefix ]
														: NULL;
													$c_sale_prices_base[ $currency ] = isset( $builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ] )
														? $builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ]
														: NULL;
													$c_sale_prices[ $currency ] = isset( $builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ] )
														? isset( $current_builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ] )
															? $current_builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ]
															: $builder[ 'multiple_' . $value['id'] . '_sale_price' . $mt_prefix ]
														: NULL;
												}
											}

											$_url_base = isset( $builder[ 'multiple_' . $value['id'] . '_url' ] )
												? $builder[ 'multiple_' . $value['id'] . '_url' ]
												: NULL;
											$_url = isset( $builder[ 'multiple_' . $value['id'] . '_url' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_url' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_url' ]
													: $builder[ 'multiple_' . $value['id'] . '_url' ]
												: NULL;

											$_description_base = isset( $builder[ 'multiple_' . $value['id'] . '_description' ] )
												? $builder[ 'multiple_' . $value['id'] . '_description' ]
												: NULL;
											$_description = isset( $builder[ 'multiple_' . $value['id'] . '_description' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_description' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_description' ]
													: $builder[ 'multiple_' . $value['id'] . '_description' ]
												: NULL;

											$_color_base = isset( $builder[ 'multiple_' . $value['id'] . '_color' ] )
												? $builder[ 'multiple_' . $value['id'] . '_color' ]
												: NULL;
											$_color = isset( $builder[ 'multiple_' . $value['id'] . '_color' ] )
												? isset( $current_builder[ 'multiple_' . $value['id'] . '_color' ] )
													? $current_builder[ 'multiple_' . $value['id'] . '_color' ]
													: $builder[ 'multiple_' . $value['id'] . '_color' ]
												: NULL;

											$_extra_options = $this->extra_multiple_options;
											$_extra_base = array();
											$_extra = array();
											foreach ( $_extra_options as $__key => $__name ) {
												if ( $value['id'] == $__name["type"] . "_options" ) {
													$_extra_name = $__name["name"];
													$_extra_base[] = isset( $builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ] )
														? $builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ]
														: NULL;
													$_extra[] = isset( $builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ] )
														? isset( $current_builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ] )
															? $current_builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ]
															: $builder[ 'multiple_' . $value['id'] . '_' . $_extra_name ]
														: NULL;
												}
											}

											if ( !is_null( $_titles_base ) && !is_null( $_values_base ) && !is_null( $_prices_base ) ) {
												$t[ $_vid ] = array();
												// backwards combatility

												if ( is_null( $_titles ) ) {
													$_titles = $_titles_base;
												}
												if ( is_null( $_values ) ) {
													$_values = $_values_base;
												}
												if ( is_null( $_prices ) ) {
													$_prices = $_prices_base;
												}
												if ( is_null( $_sale_prices_base ) ) {
													$_sale_prices_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}

												if ( is_null( $_sale_prices ) ) {
													$_sale_prices = $_sale_prices_base;
												}

												foreach ( $c_prices as $ckey => $cvalue ) {
													if ( is_null( $cvalue ) ) {
														$c_prices[ $ckey ] = $c_prices_base[ $ckey ];
													}
												}

												foreach ( $c_sale_prices as $ckey => $cvalue ) {
													if ( is_null( $cvalue ) ) {
														$c_sale_prices[ $ckey ] = $c_sale_prices_base[ $ckey ];
													}
												}

												if ( is_null( $_images_base ) ) {
													$_images_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_images ) ) {
													$_images = $_images_base;
												}

												if ( is_null( $_imagesc_base ) ) {
													$_imagesp_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_imagesc ) ) {
													$_imagesc = $_imagesc_base;
												}

												if ( is_null( $_imagesp_base ) ) {
													$_imagesp_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_imagesp ) ) {
													$_imagesp = $_imagesp_base;
												}

												if ( is_null( $_imagesl_base ) ) {
													$_imagesl_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_imagesl ) ) {
													$_imagesl = $_imagesl_base;
												}

												if ( is_null( $_prices_type_base ) ) {
													$_prices_type_base = array_map( array( $this, '_tm_clear_array_values' ), $_prices_base );
												}
												if ( is_null( $_prices_type ) ) {
													$_prices_type = $_prices_type_base;
												}

												if ( is_null( $_url_base ) ) {
													$_url_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_url ) ) {
													$_url = $_url_base;
												}
												if ( is_null( $_description_base ) ) {
													$_description_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_description ) ) {
													$_description = $_description_base;
												}
												if ( is_null( $_color_base ) ) {
													$_color_base = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
												}
												if ( is_null( $_color ) ) {
													$_color = $_color_base;
												}

												foreach ( $_extra_base as $_extra_base_key => $_extra_base_value ) {
													if ( is_null( $_extra_base[ $_extra_base_key ] ) ) {
														$_extra_base[ $_extra_base_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
													}
												}
												foreach ( $_extra as $_extra_key => $_extra_value ) {
													if ( is_null( $_extra_base[ $_extra_key ] ) ) {
														$_extra_base[ $_extra_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
													}
												}

												foreach ( $_titles_base as $option_key => $option_value ) {

													$use_original_builder = FALSE;
													$_option_key = $option_key;
													if ( isset( $current_builder[ $_elements[ $k0 ] . "_uniqid" ] )
														&& isset( $builder[ $_elements[ $k0 ] . "_uniqid" ] )
														&& isset( $builder[ $_elements[ $k0 ] . "_uniqid" ][ $option_key ] )
													) {
														// get index of element id in internal array
														$get_current_builder_uniqid_index = array_search( $builder[ $_elements[ $k0 ] . "_uniqid" ][ $option_key ], $current_builder[ $_elements[ $k0 ] . "_uniqid" ] );
														if ( $get_current_builder_uniqid_index !== NULL && $get_current_builder_uniqid_index !== FALSE ) {
															$_option_key = $get_current_builder_uniqid_index;
														} else {
															$use_original_builder = TRUE;
														}
													}

													if ( !isset( $_imagesc[ $_option_key ] ) ) {
														$_imagesc[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}
													if ( !isset( $_imagesc_base[ $_option_key ] ) ) {
														$_imagesc_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_imagesp[ $_option_key ] ) ) {
														$_imagesp[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}
													if ( !isset( $_imagesp_base[ $_option_key ] ) ) {
														$_imagesp_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_imagesl[ $_option_key ] ) ) {
														$_imagesl[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}
													if ( !isset( $_imagesl_base[ $_option_key ] ) ) {
														$_imagesl_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_sale_prices_base[ $_option_key ] ) ) {
														$_sale_prices_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_description_base[ $_option_key ] ) ) {
														$_description_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_sale_prices[ $_option_key ] ) ) {
														$_sale_prices[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_description[ $_option_key ] ) ) {
														$_description[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													if ( !isset( $_color[ $_option_key ] ) ) {
														$_color[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}
													if ( !isset( $_color_base[ $_option_key ] ) ) {
														$_color_base[ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base[ $_option_key ] );
													}

													foreach ( $_extra_base as $_extra_base_key => $_extra_base_value ) {
														if ( !isset( $_extra_base[ $_extra_base_key ][ $_option_key ] ) ) {
															$_extra_base[ $_extra_base_key ][ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
														}
													}
													foreach ( $_extra as $_extra_key => $_extra_value ) {
														if ( !isset( $_extra[ $_extra_key ][ $_option_key ] ) ) {
															$_extra[ $_extra_key ][ $_option_key ] = array_map( array( $this, '_tm_clear_array_values' ), $_titles_base );
														}
													}

													if ( $use_original_builder ) {
														$obvalues = array(
															"title"       => $_titles_base[ $_option_key ],
															"value"       => $_values_base[ $_option_key ],
															"price"       => $_prices_base[ $_option_key ],
															"sale_price"  => $_sale_prices_base[ $_option_key ],
															"image"       => $_images_base[ $_option_key ],
															"imagec"      => $_imagesc_base[ $_option_key ],
															"imagep"      => $_imagesp_base[ $_option_key ],
															"imagel"      => $_imagesl_base[ $_option_key ],
															"price_type"  => $_prices_type_base[ $_option_key ],
															"url"         => $_url_base[ $_option_key ],
															"description" => $_description_base[ $_option_key ],
															"color" 	  => $_color_base[ $_option_key ],
														);
														foreach ( $c_prices_base as $ckey => $cvalue ) {
															$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $ckey );
															$obvalues[ "price" . $mt_prefix ] = $cvalue[ $_option_key ];
														}
														foreach ( $c_sale_prices_base as $ckey => $cvalue ) {
															$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $ckey );
															$obvalues[ "sale_price" . $mt_prefix ] = $cvalue[ $_option_key ];
														}
														foreach ( $_extra_base as $_extra_base_key => $_extra_base_value ) {
															$obvalues[ $_extra_options[ $_extra_base_key ]["name"] ] = $_extra_base_value[ $_option_key ];
														}
														$t[ $_vid ][] = $obvalues;
													} else {
														$cbvalues = array(
															"title"       => TM_EPO_HELPER()->build_array( $_titles[ $_option_key ], $_titles_base[ $_option_key ] ),
															"value"       => TM_EPO_HELPER()->build_array( $_values[ $_option_key ], $_values_base[ $_option_key ] ),
															"price"       => TM_EPO_HELPER()->build_array( $_prices[ $_option_key ], $_prices_base[ $_option_key ] ),
															"sale_price"  => TM_EPO_HELPER()->build_array( $_sale_prices[ $_option_key ], $_sale_prices_base[ $_option_key ] ),
															"image"       => TM_EPO_HELPER()->build_array( $_images[ $_option_key ], $_images_base[ $_option_key ] ),
															"imagec"      => TM_EPO_HELPER()->build_array( $_imagesc[ $_option_key ], $_imagesc_base[ $_option_key ] ),
															"imagep"      => TM_EPO_HELPER()->build_array( $_imagesp[ $_option_key ], $_imagesp_base[ $_option_key ] ),
															"imagel"      => TM_EPO_HELPER()->build_array( $_imagesl[ $_option_key ], $_imagesl_base[ $_option_key ] ),
															"price_type"  => TM_EPO_HELPER()->build_array( $_prices_type[ $_option_key ], $_prices_type_base[ $_option_key ] ),
															"url"         => TM_EPO_HELPER()->build_array( $_url[ $_option_key ], $_url_base[ $_option_key ] ),
															"description" => TM_EPO_HELPER()->build_array( $_description[ $_option_key ], $_description_base[ $_option_key ] ),
															"color" 	  => TM_EPO_HELPER()->build_array( $_color[ $_option_key ], $_color_base[ $_option_key ] ),
														);
														foreach ( $c_prices as $ckey => $cvalue ) {
															$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $ckey );
															if ( !isset( $cvalue[ $_option_key ] ) ) {
																continue;
															}
															$cbvalues[ "price" . $mt_prefix ] = TM_EPO_HELPER()->build_array( $cvalue[ $_option_key ],
																$c_prices_base[ $ckey ][ $_option_key ] );
														}
														foreach ( $c_sale_prices as $ckey => $cvalue ) {
															$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $ckey );
															if ( !isset( $cvalue[ $_option_key ] ) ) {
																continue;
															}
															$cbvalues[ "sale_price" . $mt_prefix ] = TM_EPO_HELPER()->build_array(
																$cvalue[ $_option_key ],
																$c_sale_prices_base[ $ckey ][ $_option_key ] );
														}
														foreach ( $_extra_base as $_extra_base_key => $_extra_base_value ) {
															$cbvalues[ $_extra_options[ $_extra_base_key ]["name"] ] = $_extra_base_value[ $_option_key ];
														}
														$t[ $_vid ][] = $cbvalues;
													}
												}
											}
										}
										if ( !is_null( $t[ $_vid ] ) && isset( $t[ $_vid ][ $_counter[ $_elements[ $k0 ] ] ] ) ) {

											$value['html'] = $this->builder_sub_options(
												$t[ $_vid ][ $_counter[ $_elements[ $k0 ] ] ],
												'multiple_' . $value['id'],
												$_counter[ $_elements[ $k0 ] ],
												$_default_value
											);

										}
									}
								}
								// we assume that $value['tags']['name'] exists if tag id is set
								if ( isset( $value['tags']['id'] ) ) {
									$_name = str_replace( array( "[", "]" ), "", $value['tags']['name'] );
									if ( !isset( $id_counter[ $_name ] ) ) {
										$id_counter[ $_name ] = 0;
									} else {
										$id_counter[ $_name ] = $id_counter[ $_name ] + 1;
									}
									$value['tags']['id'] = $_name . $id_counter[ $_name ];
								}

								$fields .= TM_EPO_HTML()->tm_make_field( $value, 0 );
							}
							//$elements_html_array[$k0] .="</div></div></div></div></div>";

							$elements_html_array[ $k0 ] = $this->template_bitem( array(
								'element'       => $_elements[ $k0 ],
								'width'         => $_div_size[ $k0 ],
								'width_display' => $this->sizer[ $_div_size[ $k0 ] ],
								'internal_name' => $internal_name,
								'fields'        => $fields,
								'label'         => $_this_elements[ $_elements[ $k0 ] ]["name"],
								'desc'          => '&nbsp;',
								'icon'          => $_this_elements[ $_elements[ $k0 ] ]["icon"],
							) );
						}
					}
				}

				$out .= $this->_section_template( $section_html, $this->sizer[ $_sections_size[ $_s ] ], $_sections_size[ $_s ],
					isset( $_sections_slides[ $_s ] ) ? $_sections_slides[ $_s ] : "",
					$elements_html_array, $wpml_is_original_product,
					isset( $_sections_internal_name[ $_s ] ) ? $_sections_internal_name[ $_s ] : ""
				);
				$_helper_counter = intval( $_helper_counter + intval( $_sections[ $_s ] ) );
			}
		}
		if ( empty( $echo ) ) {
			return $out;
		} else {
			echo $out;
		}
	}

	public function get_tax_classes() {
		// Get tax class options
		$tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
		$classes_options = array();
		$classes_options[''] = __( 'Inherit product tax class', 'woocommerce-tm-extra-product-options' );
		$classes_options['@'] = __( 'Standard', 'woocommerce-tm-extra-product-options' );
		if ( $tax_classes ) {
			foreach ( $tax_classes as $class ) {
				$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}
		$classes = array();

		foreach ( $classes_options as $value => $label ) {
			$classes[] = array(
				"text"  => esc_html( $label ),
				"value" => esc_attr( $value ),
			);
		}

		return $classes;
	}

	/**
	 * Generates element sub-options for variations.
	 *
	 * @since 3.0.0
	 * @access private
	 */
	public function builder_sub_variations_options( $meta = array(), $product_id = 0 ) {
		$o = array();
		$name = "tm_builder_variation_options";
		$class = " withupload";

		$upload = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to use in place of the radio button.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span><span data-tm-tooltip-html="' . esc_attr( __( "Remove the image.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm-upload-button-remove cp-button tm-tooltip"><i class="tcfa tcfa-times"></i></span>';
		$uploadp = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to replace the product image with.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button tm_upload_buttonp cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span><span data-tm-tooltip-html="' . esc_attr( __( "Remove the image.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm-upload-button-remove cp-button tm-tooltip"><i class="tcfa tcfa-times"></i></span>';

		$settings_attribute = array(
			array(
				"id"      => "variations_display_as",
				"default" => "select",
				"type"    => "select",
				"tags"    => array( "class" => "variations-display-as", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]" ),
				"options" => array(
					array( "text" => __( "Select boxes", 'woocommerce-tm-extra-product-options' ), "value" => "select" ),
					array( "text" => __( "Radio buttons", 'woocommerce-tm-extra-product-options' ), "value" => "radio" ),
					array( "text" => __( "Radio buttons and image at start of the label", 'woocommerce-tm-extra-product-options' ), "value" => "radiostart" ),
					array( "text" => __( "Radio buttons and image at end of the label", 'woocommerce-tm-extra-product-options' ), "value" => "radioend" ),
					array( "text" => __( "Image swatches", 'woocommerce-tm-extra-product-options' ), "value" => "image" ),
					array( "text" => __( "Color swatches", 'woocommerce-tm-extra-product-options' ), "value" => "color" ),
				),
				"label"   => __( "Display as", 'woocommerce-tm-extra-product-options' ),
				"desc"    => __( "Select the display type of this attribute.", 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"      => "variations_label",
				"default" => "",
				"type"    => "text",
				"tags"    => array( "class" => "t", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]", "value" => "" ),
				"label"   => __( 'Attribute Label', 'woocommerce-tm-extra-product-options' ),
				"desc"    => __( 'Leave blank to use the original attribute label.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_show_reset_button",
				"message0x0_class" => "tma-hide-for-select-box",
				"default"          => "",
				"type"             => "select",
				"tags"             => array( "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]" ),
				"options"          => array(
					array( "text" => __( "Disable", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
					array( "text" => __( "Enable", 'woocommerce-tm-extra-product-options' ), "value" => "yes" ),
				),
				"label"            => __( 'Show reset button', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Enables the display of a reset button for this attribute.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"      => "variations_class",
				"default" => "",
				"type"    => "text",
				"tags"    => array( "class" => "t", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]", "value" => "" ),
				"label"   => __( 'Attribute element class name', 'woocommerce-tm-extra-product-options' ),
				"desc"    => __( 'Enter an extra class name to add to this attribute element', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_items_per_row",
				"message0x0_class" => "tma-hide-for-select-box",
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]", "value" => "" ),
				"label"            => __( 'Items per row', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Use this field to make a grid display. Enter how many items per row for the grid or leave blank for normal display.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_item_width",
				"message0x0_class" => "tma-show-for-swatches tma-hide-for-select-box",
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]", "value" => "" ),
				"label"            => __( 'Width', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Enter the width of the displayed item or leave blank for auto width.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_item_height",
				"message0x0_class" => "tma-show-for-swatches tma-hide-for-select-box",
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "n", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]", "value" => "" ),
				"label"            => __( 'Height', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Enter the height of the displayed item or leave blank for auto height.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"      => "variations_changes_product_image",
				"default" => "",
				"type"    => "select",
				"tags"    => array( "class" => "tm-changes-product-image", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]" ),
				"options" => array(
					array( "text" => __( 'No', 'woocommerce-tm-extra-product-options' ), "value" => "" ),
					array( "text" => __( 'Use the image replacements', 'woocommerce-tm-extra-product-options' ), "value" => "images" ),
					array( "text" => __( 'Use custom image', 'woocommerce-tm-extra-product-options' ), "value" => "custom" ),
				),
				"label"   => __( 'Changes product image', 'woocommerce-tm-extra-product-options' ),
				"desc"    => __( 'Choose whether to change the product image.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_show_name",
				"message0x0_class" => "tma-show-for-swatches",
				"default"          => "hide",
				"type"             => "select",
				"tags"             => array( "class" => "variations-show-name", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][%id%]" ),
				"options"          => array(
					array( "text" => __( 'Hide', 'woocommerce-tm-extra-product-options' ), "value" => "hide" ),
					array( "text" => __( 'Show bottom', 'woocommerce-tm-extra-product-options' ), "value" => "bottom" ),
					array( "text" => __( 'Show inside', 'woocommerce-tm-extra-product-options' ), "value" => "inside" ),
					array( "text" => __( 'Tooltip', 'woocommerce-tm-extra-product-options' ), "value" => "tooltip" ),
				),
				"label"            => __( 'Show attribute name', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Choose whether to show or hide the attribute name.', 'woocommerce-tm-extra-product-options' ),
			),
		);

		$settings_term = array(
			array(
				"id"               => "variations_color",
				"message0x0_class" => "tma-term-color",
				"default"          => "",
				"type"             => "text",
				"tags"             => array( "class" => "tm-color-picker", "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][[%id%]][%term_id%]", "value" => "" ),
				"label"            => __( 'Color', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Select the color to use.', 'woocommerce-tm-extra-product-options' ),
			),
			array(
				"id"               => "variations_image",
				"message0x0_class" => "tma-term-image",
				"default"          => "",
				"type"             => "hidden",
				"tags"             => array( "class" => "n tm_option_image" . $class, "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][[%id%]][%term_id%]" ),
				"label"            => __( 'Image replacement', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Select an image for this term.', 'woocommerce-tm-extra-product-options' ),
				"extra"            => $upload . '<span class="tm_upload_image"><img class="tm_upload_image_img" alt="" src="%value%" /></span>',
			),
			array(
				"id"               => "variations_imagep",
				"message0x0_class" => "tma-term-custom-image",
				"default"          => "",
				"type"             => "hidden",
				"tags"             => array( "class" => "n tm_option_image tm_option_imagep" . $class, "id" => "builder_%id%", "name" => "tm_meta[tmfbuilder][variations_options][%attribute_id%][[%id%]][%term_id%]" ),
				"label"            => __( 'Product Image replacement', 'woocommerce-tm-extra-product-options' ),
				"desc"             => __( 'Select the image to replace the product image with.', 'woocommerce-tm-extra-product-options' ),
				"extra"            => $uploadp . '<span class="tm_upload_image tm_upload_imagep"><img class="tm_upload_image_img" alt="" src="%value%" /></span>',
			),

		);

		$out = "";

		$attributes = array();

		$d_counter = 0;
		if ( !empty( $product_id ) ) {
			$product = wc_get_product( $product_id );

			if ( $product && is_object( $product ) && is_callable( array( $product, 'get_variation_attributes' ) ) ) {
				$attributes = $product->get_variation_attributes();
				$all_attributes = $product->get_attributes();
				if ( $attributes ) {
					foreach ( $attributes as $key => $value ) {
						if ( !$value ) {
							$attributes[ $key ] = array_map( 'trim', explode( "|", $all_attributes[ $key ]['value'] ) );
						}
					}
				}
			}

		}

		if ( empty( $attributes ) ) {
			return '<div class="errortitle"><p><i class="tcfa tcfa-exclamation-triangle"></i> ' . __( 'No saved variations found.', 'woocommerce-tm-extra-product-options' ) . '</p></div>';
		}

		foreach ( $attributes as $name => $options ) {
			$out .= '<div class="tma-handle-wrap tm-attribute">'
				. '<div class="tma-handle"><div class="tma-attribute_label">' . wc_attribute_label( $name ) . '</div><div class="tmicon tcfa fold tcfa-caret-up"></div></div>'
				. '<div class="tma-handle-wrapper tm-hidden">'
				. '<div class="tma-attribute w100">';
			$attribute_id = sanitize_title( $name );
			foreach ( $settings_attribute as $setting ) {
				$setting["tags"]["id"] = str_replace( "%id%", $setting["id"], $setting["tags"]["id"] );
				$setting["tags"]["name"] = str_replace( "%id%", $setting["id"], $setting["tags"]["name"] );
				$setting["tags"]["name"] = str_replace( "%attribute_id%", $attribute_id, $setting["tags"]["name"] );
				if ( !empty( $meta ) && isset( $meta[ $attribute_id ] ) && isset( $meta[ $attribute_id ][ $setting["id"] ] ) ) {
					$setting["default"] = $meta[ $attribute_id ][ $setting["id"] ];
				}
				$out .= TM_EPO_HTML()->tm_make_field( $setting, 0 );
			}

			if ( is_array( $options ) ) {
				$taxonomy_name = rawurldecode( sanitize_title( $name ) );
				if ( taxonomy_exists( $taxonomy_name ) ) {

					if ( function_exists( 'wc_get_product_terms' ) ) {
						$terms = wc_get_product_terms( $product_id, $name, array( 'fields' => 'all' ) );
					} else {

						$orderby = wc_epo_attribute_orderby( $taxonomy_name );
						$args = array();
						switch ( $orderby ) {
							case 'name' :
								$args = array( 'orderby' => 'name', 'hide_empty' => FALSE, 'menu_order' => FALSE );
								break;
							case 'id' :
								$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => FALSE, 'hide_empty' => FALSE );
								break;
							case 'menu_order' :
								$args = array( 'menu_order' => 'ASC', 'hide_empty' => FALSE );
								break;
						}
						$terms = get_terms( $taxonomy_name, $args );
					}
					if ( !empty( $terms ) ) {

						foreach ( $terms as $term ) {
							// Get only selected terms
							if ( !$has_term = has_term( (int) $term->term_id, $taxonomy_name, $product_id ) ) {
								continue;
							}
							$term_name = TM_EPO_HELPER()->html_entity_decode( $term->name );
							$term_id = TM_EPO_HELPER()->sanitize_key( $term->slug );
							$out .= '<div class="tma-handle-wrap tm-term">'
								. '<div class="tma-handle"><div class="tma-attribute_label">' . apply_filters( 'woocommerce_variation_option_name', $term_name ) . '</div><div class="tmicon tcfa fold tcfa-caret-up"></div></div>'
								. '<div class="tma-handle-wrapper tm-hidden">'
								. '<div class="tma-attribute w100">';
							foreach ( $settings_term as $setting ) {
								$setting["tags"]["id"] = str_replace( "%id%", $setting["id"], $setting["tags"]["id"] );
								$setting["tags"]["name"] = str_replace( "%id%", $setting["id"], $setting["tags"]["name"] );
								$setting["tags"]["name"] = str_replace( "%attribute_id%", sanitize_title( TM_EPO_HELPER()->sanitize_key( $name ) ), $setting["tags"]["name"] );
								$setting["tags"]["name"] = str_replace( "%term_id%", esc_attr( $term_id ), $setting["tags"]["name"] );

								if ( !empty( $meta )
									&& isset( $meta[ $attribute_id ] )
									&& isset( $meta[ $attribute_id ][ $setting["id"] ] )
									&& isset( $meta[ $attribute_id ][ $setting["id"] ][ $term_id ] )
								) {
									$setting["default"] = $meta[ $attribute_id ][ $setting["id"] ][ $term_id ];
									if ( isset( $setting["extra"] ) ) {
										$setting["extra"] = str_replace( "%value%", $meta[ $attribute_id ][ $setting["id"] ][ $term_id ], $setting["extra"] );
									}
								} else {
									if ( isset( $setting["extra"] ) ) {
										$setting["extra"] = str_replace( "%value%", "", $setting["extra"] );
									}
								}
								$out .= TM_EPO_HTML()->tm_make_field( $setting, 0 );
							}

							$out .= '</div></div></div>';
						}
					}

				} else {

					foreach ( $options as $option ) {
						$optiont = rawurldecode( TM_EPO_HELPER()->html_entity_decode( $option ) );
						$option = TM_EPO_HELPER()->html_entity_decode( TM_EPO_HELPER()->sanitize_key( $option ) );
						$out .= '<div class="tma-handle-wrap tm-term">'
							. '<div class="tma-handle"><div class="tma-attribute_label">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $optiont ) ) . '</div><div class="tmicon tcfa fold tcfa-caret-up"></div></div>'
							. '<div class="tma-handle-wrapper tm-hidden">'
							. '<div class="tma-attribute w100">';

						foreach ( $settings_term as $setting ) {
							$setting["tags"]["id"] = str_replace( "%id%", $setting["id"], $setting["tags"]["id"] );
							$setting["tags"]["name"] = str_replace( "%id%", $setting["id"], $setting["tags"]["name"] );
							$setting["tags"]["name"] = str_replace( "%attribute_id%", sanitize_title( TM_EPO_HELPER()->sanitize_key( $name ) ), $setting["tags"]["name"] );
							$setting["tags"]["name"] = str_replace( "%term_id%", esc_attr( $option ), $setting["tags"]["name"] );

							if ( !empty( $meta )
								&& isset( $meta[ $attribute_id ] )
								&& isset( $meta[ $attribute_id ][ $setting["id"] ] )
								&& isset( $meta[ $attribute_id ][ $setting["id"] ][ $option ] )
							) {
								$setting["default"] = $meta[ $attribute_id ][ $setting["id"] ][ $option ];
								if ( isset( $setting["extra"] ) ) {
									$setting["extra"] = str_replace( "%value%", $meta[ $attribute_id ][ $setting["id"] ][ $option ], $setting["extra"] );
								}
							} else {
								if ( isset( $setting["extra"] ) ) {
									$setting["extra"] = str_replace( "%value%", "", $setting["extra"] );
								}
							}
							$out .= TM_EPO_HTML()->tm_make_field( $setting, 0 );
						}

						$out .= '</div></div></div>';
					}

				}
			}

			$out .= '</div></div></div>';
		}

		return $out;
	}

	/**
	 * Generates element sub-options for selectbox, checkbox and radio buttons.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function builder_sub_options( $options = array(), $name = "multiple_selectbox_options", $counter = NULL, $default_value = NULL ) {
		$o = array();
		$upload = "";
		$uploadc = "";
		$uploadp = "";
		$uploadl = "";
		$class = "";
		$_extra_options = $this->extra_multiple_options;
		$additional_currencies = TM_EPO_HELPER()->wc_aelia_cs_enabled_currencies();

		if ( !$options ) {
			$options = array(
				"title"       => array( "" ),
				"value"       => array( "" ),
				"price"       => array( "" ),
				"sale_price"  => array( "" ),
				"image"       => array( "" ),
				"imagec"      => array( "" ),
				"imagep"      => array( "" ),
				"imagel"      => array( "" ),
				"price_type"  => array( "" ),
				"url"         => array( "" ),
				"description" => array( "" ),
				"color" 	  => array( "" ),
			);
			foreach ( $_extra_options as $__key => $__name ) {
				if ( "multiple_" . $__name["type"] . "_options" == $name ) {
					$options[ $__name["name"] ] = array( "" );
				}
			}
		}

		if ( $name == "multiple_radiobuttons_options" || $name == "multiple_checkboxes_options" ) {
			if ( $name == "multiple_radiobuttons_options" ) {
				$upload = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to use in place of the radio button.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
				$uploadc = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to use in place of the radio button when it is checked.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			} elseif ( $name == "multiple_checkboxes_options" ) {
				$upload = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to use in place of the checkbox.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
				$uploadc = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to use in place of the checkbox when it is checked.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			}
			$uploadp = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to replace the product image with.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button tm_upload_buttonp cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			$uploadl = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image for the lightbox.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button tm_upload_buttonl cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			$class = " withupload";
		}
		if ( $name == "multiple_selectbox_options" ) {
			$uploadp = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image to replace the product image with.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button tm_upload_buttonp cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			$uploadl = '&nbsp;<span data-tm-tooltip-html="' . esc_attr( __( "Choose the image for the lightbox.", 'woocommerce-tm-extra-product-options' ) ) . '" class="tm_upload_button tm_upload_buttonl cp_button tm-tooltip"><i class="tcfa tcfa-upload"></i></span>';
			$class = " withupload";
		}

		$o["title"] = array(
			"id"      => $name . "_title",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "t tm_option_title", "id" => $name . "_title", "name" => $name . "_title", "value" => "" ),
		);
		$o["value"] = array(
			"id"      => $name . "_value",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "t tm_option_value", "id" => $name . "_value", "name" => $name . "_value" ),
		);
		$o["price"] = array(
			"id"      => $name . "_price",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_price", "id" => $name . "_price", "name" => $name . "_price" ),
		);
		$o["sale_price"] = array(
			"id"      => $name . "_sale_price",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_sale_price", "id" => $name . "_price", "name" => $name . "_price" ),
		);
		$o["image"] = array(
			"id"      => $name . "_image",
			"default" => "",
			"type"    => "hidden",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_image" . $class, "id" => $name . "_image", "name" => $name . "_image" ),
			"extra"   => $upload,
		);
		$o["imagec"] = array(
			"id"      => $name . "_imagec",
			"default" => "",
			"type"    => "hidden",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_image tm_option_imagec" . $class, "id" => $name . "_imagec", "name" => $name . "_imagec" ),
		);
		$o["imagep"] = array(
			"id"      => $name . "_imagep",
			"default" => "",
			"type"    => "hidden",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_image tm_option_imagep" . $class, "id" => $name . "_imagep", "name" => $name . "_imagep" ),
		);
		$o["imagel"] = array(
			"id"      => $name . "_imagel",
			"default" => "",
			"type"    => "hidden",
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_image tm_option_imagel" . $class, "id" => $name . "_imagel", "name" => $name . "_imagel" ),
		);
		$o["price_type"] = array(
			"id"      => $name . "_price_type",
			"default" => "",
			"type"    => "select",
			"options" => array(
				array( "text" => __( "Fixed amount", 'woocommerce-tm-extra-product-options' ), "value" => "" ),
				array( "text" => __( "Percent of the original price", 'woocommerce-tm-extra-product-options' ), "value" => "percent" ),
				array( "text" => __( "Percent of the original price + options", 'woocommerce-tm-extra-product-options' ), "value" => "percentcurrenttotal" ),
			),
			"nodiv"   => 1,
			"tags"    => array( "class" => "n tm_option_price_type " . $name, "id" => $name . "_price_type", "name" => $name . "_price_type" ),
		);
		$o["url"] = array(
			"id"      => $name . "_url",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "t tm_option_url", "id" => $name . "_url", "name" => $name . "_url", "value" => "" ),
		);
		$o["description"] = array(
			"id"      => $name . "_description",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "t tm_option_description", "id" => $name . "_description", "name" => $name . "_description", "value" => "" ),
		);
		$o["color"] = array(
			"id"      => $name . "_color",
			"default" => "",
			"type"    => "text",
			"nodiv"   => 1,
			"tags"    => array( "class" => "tm-color-picker", "id" => $name . "_color", "name" => $name . "_color", "value" => "" ),
		);
		foreach ( $_extra_options as $__key => $__name ) {
			$_extra_name = $__name["name"];
			if ( "multiple_" . $__name["type"] . "_options" == $name ) {
				$o[ $_extra_name ] = $__name["field"];
				$o[ $_extra_name ]["id"] = $name . "_" . $_extra_name;
				$o[ $_extra_name ]["nodiv"] = 1;
				$o[ $_extra_name ]["tags"] = array_merge(
					$__name["field"]["tags"],
					array( "id" => $name . "_" . $_extra_name, "name" => $name . "_" . $_extra_name )
				);
			}
		}
		if ( $this->woo_subscriptions_check && $name != "multiple_selectbox_options" ) {
			$o["price_type"]['options'][] = array( "text" => __( "Subscription sign up fee", 'woocommerce-tm-extra-product-options' ), "value" => "subscriptionfee" );
		}
		if ( $name != "multiple_selectbox_options" ) {
			$o["price_type"]['options'][] = array( "text" => __( "Fee", 'woocommerce-tm-extra-product-options' ), "value" => "fee" );
		}
		$o = apply_filters( 'wc_epo_builder_after_multiple_element_array', $o );

		$del = TM_EPO_HTML()->tm_make_button( array(
			"text" => "<i class='tcfa tcfa-times'></i>",
			"tags" => array( "href" => "#delete", "class" => "tc tc-button small builder_panel_delete" ),
		), 0 );
		$drag =
			TM_EPO_HTML()->tm_make_button( array(
				"text" => "<i class='tcfa tcfa-angle-up'></i>",
				"tags" => array( "href" => "#move", "class" => "tc tc-button small builder_panel_up" ),
			), 0 ) .
			TM_EPO_HTML()->tm_make_button( array(
				"text" => "<i class='tcfa tcfa-angle-down'></i>",
				"tags" => array( "href" => "#move", "class" => "tc tc-button small builder_panel_down" ),
			), 0 );

		$out = "<div class='tm-row nopadding multiple_options tc-clearfix'>"
			. "<div class='tm-cell col-auto tm_cell_move'>" .

			TM_EPO_HTML()->tm_make_button( array(
				"text" => "<i class='tcfa tcfa-angle-up'></i>",
				"tags" => array( "href" => "#move", "class" => "tc tc-button small tm-hidden-inline" ),
			), 0 ) .
			TM_EPO_HTML()->tm_make_button( array(
				"text" => "<i class='tcfa tcfa-angle-down'></i>",
				"tags" => array( "href" => "#move", "class" => "tc tc-button small tm-hidden-inline" ),
			), 0 )
			. "</div>"
			. "<div class='tm-cell col-auto tm_cell_default'>" . (($name == "multiple_checkboxes_options") ? __( "Checked", 'woocommerce-tm-extra-product-options' ) : __( "Default", 'woocommerce-tm-extra-product-options' )) . "</div>"
			. "<div class='tm-cell col-3 tm_cell_title'>" . __( "Label", 'woocommerce-tm-extra-product-options' ) . "</div>"
			. "<div class='tm-cell col-3 tm_cell_images'>" . __( "Images", 'woocommerce-tm-extra-product-options' ) . "</div>"

			. "<div class='tm-cell col-0 tm_cell_value'>" . __( "Value", 'woocommerce-tm-extra-product-options' ) . "</div>"
			. "<div class='tm-cell col-auto tm_cell_price'>" . __( "Price", 'woocommerce-tm-extra-product-options' ) . "</div>"
			. "<div class='tm-cell col-auto tm_cell_delete'><button type='button' class='tc tc-button builder_panel_delete_all'>" . __( "Delete all options", 'woocommerce-tm-extra-product-options' ) . "</button></div>"
			. "</div>";

		$total_entries = count( $options["title"] );
		$per_page = apply_filters( 'tm_choices_shown', 20 );
		if ( $per_page <= 0 ) {
			$per_page = 20;
		}
		if ( $total_entries > $per_page ) {
			$pages = ceil( $total_entries / $per_page );
			$out .= '<div data-perpage="' . $per_page . '" data-totalpages="' . $pages . '" class="tcpagination tc-clearfix"></div>';
		} else {
			$out .= '<div data-perpage="' . $per_page . '" data-totalpages="0" class="tcpagination tc-clearfix"></div>';
		}
		$out .= "<div class='panels_wrap nof_wrapper'>";

		$d_counter = 0;
		$show_counter = 0;
		foreach ( $options["title"] as $ar => $el ) {
			$hidden_class = '';
			if ( $show_counter >= $per_page ) {
				$hidden_class = ' tm-hidden ';
			}
			$show_counter++;
			$out .= "<div class='options_wrap" . $hidden_class . "'>"
				. "<div class='tm-row nopadding tc-clearfix'>";

			if ( !isset( $options["title"][ $ar ] ) ) {
				$options["title"][ $ar ] = '';
			}
			if ( !isset( $options["value"][ $ar ] ) ) {
				$options["value"][ $ar ] = '';
			}
			if ( !isset( $options["price"][ $ar ] ) ) {
				$options["price"][ $ar ] = '';
			}
			if ( !isset( $options["sale_price"][ $ar ] ) ) {
				$options["sale_price"][ $ar ] = '';
			}
			if ( !isset( $options["image"][ $ar ] ) ) {
				$options["image"][ $ar ] = '';
			}
			if ( !isset( $options["imagec"][ $ar ] ) ) {
				$options["imagec"][ $ar ] = '';
			}
			if ( !isset( $options["imagep"][ $ar ] ) ) {
				$options["imagep"][ $ar ] = '';
			}
			if ( !isset( $options["imagel"][ $ar ] ) ) {
				$options["imagel"][ $ar ] = '';
			}
			if ( !isset( $options["price_type"][ $ar ] ) ) {
				$options["price_type"][ $ar ] = '';
			}
			if ( !isset( $options["url"][ $ar ] ) ) {
				$options["url"][ $ar ] = '';
			}
			if ( !isset( $options["description"][ $ar ] ) ) {
				$options["description"][ $ar ] = '';
			}
			if ( !isset( $options["color"][ $ar ] ) ) {
				$options["color"][ $ar ] = '';
			}
			foreach ( $_extra_options as $__key => $__name ) {
				if ( "multiple_" . $__name["type"] . "_options" == $name ) {
					$_extra_name = $__name["name"];
					if ( !isset( $options[ $_extra_name ][ $ar ] ) ) {
						$options[ $_extra_name ][ $ar ] = '';
					}
				}
			}

			$o["title"]["default"] = $options["title"][ $ar ];//label
			$o["title"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_title][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["title"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["title"]["tags"]["name"] ) . "_" . $ar;

			$o["value"]["default"] = $options["value"][ $ar ];//value
			$o["value"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_value][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["value"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["value"]["tags"]["name"] ) . "_" . $ar;

			$o["price"]["default"] = tc_convert_local_numbers( $options["price"][ $ar ] );//price
			$o["price"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_price][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["price"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["price"]["tags"]["name"] ) . "_" . $ar;

			$o["sale_price"]["default"] = tc_convert_local_numbers( $options["sale_price"][ $ar ] );//sale_price
			$o["sale_price"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_sale_price][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["sale_price"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["sale_price"]["tags"]["name"] ) . "_" . $ar;

			$o["image"]["default"] = $options["image"][ $ar ];//image
			$o["image"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_image][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["image"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["image"]["tags"]["name"] ) . "_" . $ar;
			$o["image"]["extra"] = $upload . '<span class="tm_upload_image"><img class="tm_upload_image_img" alt="" src="' . $options["image"][ $ar ] . '" /></span>';

			$o["imagec"]["default"] = $options["imagec"][ $ar ];//imagec
			$o["imagec"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_imagec][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["imagec"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["imagec"]["tags"]["name"] ) . "_" . $ar;
			$o["imagec"]["extra"] = $uploadc . '<span class="tm_upload_image tm_upload_imagec"><img class="tm_upload_image_img" alt="" src="' . $options["imagec"][ $ar ] . '" /></span>';

			$o["imagep"]["default"] = $options["imagep"][ $ar ];//imagep
			$o["imagep"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_imagep][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["imagep"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["imagep"]["tags"]["name"] ) . "_" . $ar;
			$o["imagep"]["extra"] = $uploadp . '<span class="tm_upload_image tm_upload_imagep"><img class="tm_upload_image_img" alt="" src="' . $options["imagep"][ $ar ] . '" /></span>';

			$o["imagel"]["default"] = $options["imagel"][ $ar ];//imagel
			$o["imagel"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_imagel][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["imagel"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["imagel"]["tags"]["name"] ) . "_" . $ar;
			$o["imagel"]["extra"] = $uploadl . '<span class="tm_upload_image tm_upload_imagel"><img class="tm_upload_image_img" alt="" src="' . $options["imagel"][ $ar ] . '" /></span>';

			$o["price_type"]["default"] = $options["price_type"][ $ar ];//price type
			$o["price_type"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_price_type][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["price_type"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["price_type"]["tags"]["name"] ) . "_" . $ar;

			$o["url"]["default"] = $options["url"][ $ar ];//url
			$o["url"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_url][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["url"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["url"]["tags"]["name"] ) . "_" . $ar;

			$o["description"]["default"] = $options["description"][ $ar ];//description
			$o["description"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_description][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["description"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["description"]["tags"]["name"] ) . "_" . $ar;

			$o["color"]["default"] = $options["color"][ $ar ];//color
			$o["color"]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_color][" . (is_null( $counter ) ? 0 : $counter) . "][]";
			$o["color"]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o["color"]["tags"]["name"] ) . "_" . $ar;

			foreach ( $_extra_options as $__key => $__name ) {
				if ( "multiple_" . $__name["type"] . "_options" == $name ) {
					$_extra_name = $__name["name"];
					$o[ $_extra_name ]["default"] = $options[ $_extra_name ][ $ar ];
					$o[ $_extra_name ]["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_" . $_extra_name . "][" . (is_null( $counter ) ? 0 : $counter) . "][]";
					$o[ $_extra_name ]["tags"]["id"] = str_replace( array( "[", "]" ), "", $o[ $_extra_name ]["tags"]["name"] ) . "_" . $ar;
					if ( isset( $o[ $_extra_name ]["admin_class"] ) ) {
						$o[ $_extra_name ]["admin_class"] = "tc-extra-option " . $o[ $_extra_name ]["admin_class"];
					} else {
						$o[ $_extra_name ]["admin_class"] = "tc-extra-option";
					}
				}
			}

			if ( $name == "multiple_checkboxes_options" ) {
				$default_select = '<input type="checkbox" value="' . $d_counter . '" name="tm_meta[tmfbuilder][' . $name . '_default_value][' . (is_null( $counter ) ? 0 : $counter) . '][]" class="tm-default-checkbox" ' . checked( (is_null( $counter ) ? "" : isset( $default_value[ $counter ] ) ? in_array( $d_counter, $default_value[ $counter ] ) : ""), TRUE, 0 ) . '>';
			} else {
				$default_select = '<input type="radio" value="' . $d_counter . '" name="tm_meta[tmfbuilder][' .
					$name . '_default_value][' . (is_null( $counter ) ? 0 : $counter) . ']" class="tm-default-radio" ' .
					checked( (is_null( $counter ) ? "" :
						(isset( $default_value[ $counter ] ) && !is_array( $default_value[ $counter ] )) ?
							(string) $default_value[ $counter ] : ""),
						$d_counter, 0 ) . '>';
			}
			$default_select = '<span class="tm-hidden-inline">' . (($name == "multiple_checkboxes_options") ? __( "Checked", 'woocommerce-tm-extra-product-options' ) : __( "Default", 'woocommerce-tm-extra-product-options' )) . '</span>' . $default_select;
			$out .= "<div class='tm-cell col-auto tm_cell_move'>" . $drag . "</div>";
			$out .= "<div class='tm-cell col-auto tm_cell_default'>" . $default_select . "</div>";
			$out .= "<div class='tm-cell col-3 tm_cell_title'>" . TM_EPO_HTML()->tm_make_field( $o["title"], 0 ) . "</div>";
			$out .= "<div class='tm-cell col-3 tm_cell_images'>" . 
					TM_EPO_HTML()->tm_make_field( $o["image"], 0 ) . 
					TM_EPO_HTML()->tm_make_field( $o["imagec"], 0 ) . 
					TM_EPO_HTML()->tm_make_field( $o["imagep"], 0 ) . 
					TM_EPO_HTML()->tm_make_field( $o["imagel"], 0 ) . 
					(( $name !== "multiple_selectbox_options" ) ? TM_EPO_HTML()->tm_make_field( $o["color"], 0 ):"") . 
					"</div>";

			$out .= "<div class='tm-cell col-0 tm_cell_value'>" . TM_EPO_HTML()->tm_make_field( $o["value"], 0 ) . "</div>";
			$out .= "<div class='tm-cell col-auto tm_cell_price'>";


			if ( !empty( $additional_currencies ) && is_array( $additional_currencies ) ) {
				$_copy_value = $o["price"];
				$_sale_copy_value = $o["sale_price"];
				$o["price"]["html_before_field"] = '<span class="tm-choice-currency">' . TM_EPO_HELPER()->wc_base_currency() . '</span>';
				$o["sale_price"]["html_before_field"] = '<span class="tm-choice-currency">' . TM_EPO_HELPER()->wc_base_currency() . '</span>' . '<span class="tm-choice-sale">' . __( "Sale", 'woocommerce-tm-extra-product-options' ) . '</span>';
				$out .= TM_EPO_HTML()->tm_make_field( $o["price"], 0 );
				$out .= TM_EPO_HTML()->tm_make_field( $o["sale_price"], 0 );
				foreach ( $additional_currencies as $ckey => $currency ) {
					$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $currency );
					$copy_value = $_copy_value;
					$copy_value["default"] = isset( $options[ "price_" . $currency ][ $ar ] ) ? $options[ "price" . $mt_prefix ][ $ar ] : "";
					$copy_value["id"] .= $mt_prefix;

					$copy_value["html_before_field"] = '<span class="tm-choice-currency">' . $currency . '</span>';
					$copy_value["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_price" . $mt_prefix . "][" . (is_null( $counter ) ? 0 : $counter) . "][]";
					$copy_value["tags"]["id"] = str_replace( array( "[", "]" ), "", $copy_value["tags"]["name"] ) . "_" . $ar;
					$out .= TM_EPO_HTML()->tm_make_field( $copy_value, 0 );

					$copy_value = $_sale_copy_value;
					$copy_value["default"] = isset( $options[ "sale_price_" . $currency ][ $ar ] ) ? $options[ "sale_price" . $mt_prefix ][ $ar ] : "";
					$copy_value["id"] .= $mt_prefix;

					$copy_value["html_before_field"] = '<span class="tm-choice-currency">' . $currency . '</span>' . '<span class="tm-choice-sale">' . __( "Sale", 'woocommerce-tm-extra-product-options' ) . '</span>';
					$copy_value["tags"]["name"] = "tm_meta[tmfbuilder][" . $name . "_sale_price" . $mt_prefix . "][" . (is_null( $counter ) ? 0 : $counter) . "][]";
					$copy_value["tags"]["id"] = str_replace( array( "[", "]" ), "", $copy_value["tags"]["name"] ) . "_" . $ar;
					$out .= TM_EPO_HTML()->tm_make_field( $copy_value, 0 );
				}
			} else {
				$out .= TM_EPO_HTML()->tm_make_field( $o["price"], 0 );
				$o["sale_price"]["html_before_field"] = '<span class="tm-choice-sale">' . __( "Sale", 'woocommerce-tm-extra-product-options' ) . '</span>';
				$out .= TM_EPO_HTML()->tm_make_field( $o["sale_price"], 0 );
			}


			$out .= TM_EPO_HTML()->tm_make_field( $o["price_type"], 0 ) . "</div>";
			$out .= "<div class='tm-cell col-auto tm_cell_delete'>" . $del . "</div>";

			$out .= "<div class='tm-cell col-12 tm_cell_description'><span class='tm-inline-label bsbb'>" . __( "Description", 'woocommerce-tm-extra-product-options' ) . "</span>" . TM_EPO_HTML()->tm_make_field( $o["description"], 0 ) . "</div>";
			foreach ( $_extra_options as $__key => $__name ) {
				if ( "multiple_" . $__name["type"] . "_options" == $name ) {
					$_extra_name = $__name["name"];
					$out .= "<div class='tm-cell col-12 " . $__name["admin_class"] . "'>";
					$out .= "<span class='tm-inline-label bsbb'>" . $__name["label"] . "</span>";
					$out .= TM_EPO_HTML()->tm_make_field( $o[ $_extra_name ], 0 );
					$out .= "</div>";
				}
			}
			$out .= "<div class='tm-cell col-12 tm_cell_url'><span class='tm-inline-label bsbb'>" . __( "URL", 'woocommerce-tm-extra-product-options' ) . "</span>" . TM_EPO_HTML()->tm_make_field( $o["url"], 0 ) . "</div>";

			$out .= "</div></div>";
			$d_counter++;
		}
		$out .= "</div>";
		$out .= ' <button type="button" class="tc tc-button builder-panel-add">' . __( "Add item", 'woocommerce-tm-extra-product-options' ) . '</button>';
		$out .= ' <button type="button" class="tc tc-button builder-panel-mass-add">' . __( "Mass add", 'woocommerce-tm-extra-product-options' ) . '</button>';

		return $out;
	}

}

