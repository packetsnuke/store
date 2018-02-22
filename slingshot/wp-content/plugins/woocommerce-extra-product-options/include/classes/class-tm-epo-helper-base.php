<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_HELPER_base {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function __construct( $args = array() ) {
	}

	public function array_serialize( $a ) {
		if ( is_array( $a ) ) {
			$r = array();
			foreach ( $a as $key => $value ) {
				if ( is_array( $value ) ) {
					$r[] = serialize( $value );
				} else {
					$r[] = $value;
				}
			}
			$r = implode( "|", $r );

			return $r;
		}

		return $a;
	}

	public function array_unserialize( $a ) {
		$a = explode( "|", $a );
		$r = array();
		foreach ( $a as $key => $value ) {
			$r[] = maybe_unserialize( $value );
		}

		return $r;
	}

	public function get_attachment_id( $attachment_url = '' ) {

		$attachment_id = FALSE;

		if ( '' == $attachment_url ){
			return $attachment_id;
		}

		$session_get = WC()->session->get('tc_attachment_array');
		
		if (!is_array($session_get)){
			global $wpdb;
			$session_get = array();
			$all = $wpdb->get_results( "SELECT wposts.ID,wpostmeta.meta_value FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wposts.post_type = 'attachment'", 'ARRAY_A') ;
			foreach ($all as $key => $value) {
				$session_get[ $value['ID'] ] = $value['meta_value'];
			}
			WC()->session->set('tc_attachment_array', $session_get);
		}

		// Get the upload directory paths
		$upload_dir_paths = wp_upload_dir();		

		if ( FALSE !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

			// If this is the URL of an auto-generated thumbnail, get the URL of the original image
			$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

			// Remove the upload path base directory from the attachment URL
			$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

		}

		$attachment_id = array_search( $attachment_url, $session_get); 

		return $attachment_id;
	}

	public function convert_to_select_options( $a = array() ) {
		$r = array();
		foreach ( $a as $key => $value ) {
			$r[] = array( "text" => $value, "value" => $key );
		}

		return $r;
	}

	public function recreate_element_ids( $meta = array() ) {
		$meta = $builder = maybe_unserialize( $meta );
		$original_meta = FALSE;
		$parsed_meta = FALSE;
		$invalid = FALSE;
		if ( isset( $meta["tmfbuilder"] ) ) {
			$original_meta = TRUE;
			$builder = $meta["tmfbuilder"];
		} else {
			if ( isset( $meta['element_type'] ) ) {
				$parsed_meta = TRUE;
			} else {
				$invalid = TRUE;
			}
		}

		if ( $invalid ) {
			return $meta;
		}

		if ( isset( $builder ) ) {
			$new_ids = array();
			$ids = $this->array_contains_key( $builder, "_uniqid" );
			$logics = $this->array_contains_key( $builder, "_clogic" );

			foreach ( $ids as $idx => $idelement ) {
				foreach ( $idelement as $idy => $id ) {
					$new_ids[ $id ] = TM_EPO_HELPER()->tm_uniqid();
				}
			}
			foreach ( $ids as $idx => $idelement ) {
				foreach ( $idelement as $idy => $id ) {
					$ids[ $idx ][ $idy ] = $new_ids[ $id ];
				}
			}

			foreach ( $logics as $lx => $logicelement ) {
				foreach ( $logicelement as $ly => $logic ) {
					$logic = str_replace( array_keys( $new_ids ), array_values( $new_ids ), $logic );
					$logics[ $lx ][ $ly ] = $logic;
				}
			}

			$builder = array_merge( $builder, $ids );
			$builder = array_merge( $builder, $logics );

			if ( $original_meta ) {
				$meta["tmfbuilder"] = $builder;
			} else {
				$meta = $builder;
			}
		}

		return $meta;
	}

	public function html_entity_decode( $string = "" ) {
		return html_entity_decode( $string, version_compare( phpversion(), '5.4', '<' ) ? ENT_COMPAT : (ENT_COMPAT | ENT_HTML401), 'UTF-8' );
	}

	/* Check if current request is made via AJAX */
	public function is_ajax_request() {
		if ( !empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
			return TRUE;
		}

		return FALSE;
	}

	public function sum_array_values( $epos = array(), $include_variation_prices = FALSE, $minkey = 'min' ) {
		$r = array();

		if ( is_array( $epos ) ) {
			$input = $epos['price'];
			$variation_section_id = $epos['variation_section_id'];
			$variations_max = array();
			$variations_min = array();
			$variations_all = 0;

			$logictree = array();
			$logictree_helper = array();
			$logictree_max = array();
			$logictree_helper_max = array();
			$section_ids = array();

			$add_logic_prices = 0;
			$add_logic_prices_max = 0;

			foreach ( $input as $key => $value ) {
				if ( is_array( $value ) ) {

					$j = json_decode( $value['clogic'] );
					$has_logic = json_decode( $value['logic'] );

					if ( $value['required'] == "1" ){
						if ( !isset( $section_ids[ $value['section_uniqueid'] ] ) ) {
							$section_ids[ $value['section_uniqueid'] ] = array();
						}
						$section_ids[ $value['section_uniqueid'] ][$value['element']] = $value['uniqueid'];
					}
					foreach ( $value as $key2 => $value2 ) {
						if ( !($key2 == $minkey || $key2 == "max") ) {
							continue;
						}
						$a = 0;
						if ( isset( $r[ $key2 ] ) ) {
							$a = $r[ $key2 ];
						}
						
						if ( $j ) {						
							foreach ( $j->rules as $jkey => $rules ) {
								if ( $rules ) {
									$section = $rules->section;
									$element = $rules->element;
									$lvalue = $rules->value;
									$operator = $rules->operator;

									if ( $section == $variation_section_id && $element == 0 ) {
										if ( $key2 == "max" ) {											
											
											if ( !$has_logic ) {
												$variations_all = $variations_all + floatval( $value2 );
											}
											if ( $has_logic && in_array( $operator, array( "is", "isnotempty" ) ) ) {
												if ( !isset( $variations_max[ $lvalue ] ) ) {
													$variations_max[ $lvalue ] = 0;
												}
												$variations_max[ $lvalue ] = $variations_max[ $lvalue ] + floatval( $value2 );
											}

										}
										if ( $key2 == $minkey ) {
											if ( !isset( $variations_min[ $lvalue ] ) ) {
												$variations_min[ $lvalue ] = 0;
											}
											$variations_min[ $lvalue ] = $variations_min[ $lvalue ] + floatval( $value2 );
										}
									}else{

										if ( $has_logic){
											if ( $key2 == $minkey ) {
												if ( !isset( $logictree[ $section ] ) ) {
													$logictree[ $section ] = array();
												}
												if ( !isset( $logictree[ $section ][ $element ] ) ) {
													$logictree[ $section ][ $element ] = array();
												}
												if ( !isset( $logictree[ $section ][ $element ][ $value['uniqueid'] ] ) ) {
													$logictree[ $section ][ $element ][ $value['uniqueid'] ] = 0;
												}
												if ( !isset( $logictree_helper[ $section ] ) ) {
													$logictree_helper[ $section ] = array();
												}
												if ( !isset( $logictree_helper[ $section ][ $element ] ) ) {
													$logictree_helper[ $section ][ $element ] = array();
												}
												if ( !isset( $logictree_helper[ $section ][ $element ][ $value['uniqueid'] ] ) ) {
													$logictree_helper[ $section ][ $element ][ $value['uniqueid'] ] = $j;
												}
												$logictree[ $section ][ $element ][ $value['uniqueid'] ] = $logictree[ $section ][ $element ][ $value['uniqueid'] ] + floatval( $value2 );

											}

											if ( $key2 == "max" ) {
												if ( !isset( $logictree_max[ $section ] ) ) {
													$logictree_max[ $section ] = array();
												}
												if ( !isset( $logictree_max[ $section ][ $element ] ) ) {
													$logictree_max[ $section ][ $element ] = array();
												}
												if ( !isset( $logictree_max[ $section ][ $element ][ $value['uniqueid'] ] ) ) {
													$logictree_max[ $section ][ $element ][ $value['uniqueid'] ] = 0;
												}
												if ( !isset( $logictree_helper_max[ $section ] ) ) {
													$logictree_helper_max[ $section ] = array();
												}
												if ( !isset( $logictree_helper_max[ $section ][ $element ] ) ) {
													$logictree_helper_max[ $section ][ $element ] = array();
												}
												if ( !isset( $logictree_helper_max[ $section ][ $element ][ $value['uniqueid'] ] ) ) {
													$logictree_helper_max[ $section ][ $element ][ $value['uniqueid'] ] = $j;
												}
												$logictree_max[ $section ][ $element ][ $value['uniqueid'] ] = $logictree_max[ $section ][ $element ][ $value['uniqueid'] ] + floatval( $value2 );

											}

										}
										
									}
								}
							}
						}

						if ( $key2 == $minkey ) {
							if ( !$value['section_logic'] && !$value['logic'] ) {
								$r[ $key2 ] = floatval( $value2 ) + $a;
							}
						}
						if ( $key2 == "max" ) {
							$r[ $key2 ] = floatval( $value2 ) + $a;
						}
					}
				}
			}

			$logic_prices = array();
			$required_not_found = array();

			$checkmultiple=array();

			foreach ($logictree as $section_id => $section ) {

				foreach ($section as $element => $element_ids) {

					foreach ($element_ids as $id => $price) {

						if( !isset($required_not_found[ $id ]) && isset( $section_ids[ $section_id ] ) && isset( $section_ids[ $section_id ][ $element ] ) ){

							if ( !isset( $logic_prices[ $section_id ] ) ) {
								$logic_prices[ $section_id ] = array();
							}
							if ( !isset( $logic_prices[ $section_id ][$element] ) ) {
								$logic_prices[ $section_id ][$element] = array();
							}

							$rules = $logictree_helper[ $section_id ][ $element ][ $id ];

							foreach ( $rules->rules as $jkey => $rule ) {
								if ( $rule ) {
									$isection = $rule->section;
									$ielement = $rule->element;
									$ilvalue = rawurlencode( apply_filters( 'tm_translate', rawurldecode( $rule->value ) ) );
									$ioperator = $rule->operator;

									foreach ($epos['global'] as $priority => $pid) {
										foreach ($pid as $keydata => $data) {
											foreach ($data['sections'] as $epo_section) {
												if ( $section_id == $epo_section['sections_uniqid'] ){
													if (isset($epo_section['elements'][$ielement])){
														$el = $epo_section['elements'][$ielement];
														foreach ($el['options'] as $xk => $xv) {
															$el['options'][$xk] = rawurlencode( apply_filters( 'tm_translate', rawurldecode( $xv ) ) );
														}
														switch ($el['type']) {
															case 'radio':
															case 'select':
																
																if ( count( $el['options'] )>1 ){
																	if (!isset($checkmultiple[ $el['uniqid'] ])){
																		$checkmultiple[ $el['uniqid'] ]=array("options"=>array_flip($el['options']),"found"=>array());
																	}
																	$checkmultiple[ $el['uniqid'] ]["found"][]=array("value"=>$ilvalue,"price"=>$price);
																}else{
																	$logic_prices[ $section_id ][$element][] = $price;
																}
															
																# code...
																break;
															
															default:
																# code...
																break;
														}
													}
												}else{
													continue;
												}
											}

										}
									}

								}
							}

						}else{

							if ( isset( $logictree_helper[ $section_id ] ) && isset( $logictree_helper[ $section_id ][ $element ] ) && isset( $logictree_helper[ $section_id ][ $element ][ $id ] ) ){
								$rules = $logictree_helper[ $section_id ][ $element ][ $id ];
								if ( $rules->toggle == 'show'){

									if ( $rules->what == 'all'){
										$required_not_found[ $id ] = $id;
									}
									if ( $rules->what == 'any'){
										// don't add it
									}

								}
								elseif ( $rules->toggle == 'hide'){

									// not enough information so we just add it
									$required_not_found[ $id ] = $id;

								}
							}else{
								$required_not_found[ $id ] = $id;
							}

						}

					}

				}
			}

			foreach ($checkmultiple as $idata) {
				$found = false;
				$min_max=array();
				if (count($idata['options'])<=count($idata['found'])){
					foreach ($idata['found'] as $price_data) {
						$ivalue = $price_data['value'];
						if (isset($idata['options'][$ivalue])){
							unset($idata['options'][$ivalue]);
							$min_max[]= floatval($price_data['price']);
							if (count($idata['options'])==0){
								$found = true;
							}
						}
					}
				}

				if ($found){
					$add_logic_prices = $add_logic_prices + min($min_max);
				}
			}

			foreach ($logic_prices as $key => $section_id) {
				foreach ($section_id as $prices) {
					foreach ($prices as $price) {
						$add_logic_prices = $add_logic_prices + $price;
					}
				}
			}
///////////////////////////////////// MAX /////////////////////////////////////////////////////////////////////////////////////////
			$logic_prices = array();
			$required_not_found = array();
			$checkmultiple=array();

			foreach ($logictree_max as $section_id => $section ) {

				foreach ($section as $element => $element_ids) {

					foreach ($element_ids as $id => $price) {

						if( !isset($required_not_found[ $id ]) && isset( $section_ids[ $section_id ] ) && isset( $section_ids[ $section_id ][ $element ] ) ){

							if ( !isset( $logic_prices[ $section_id ] ) ) {
								$logic_prices[ $section_id ] = array();
							}
							if ( !isset( $logic_prices[ $section_id ][$element] ) ) {
								$logic_prices[ $section_id ][$element] = array();
							}

							$rules = $logictree_helper_max[ $section_id ][ $element ][ $id ];

							foreach ( $rules->rules as $jkey => $rule ) {
								if ( $rule ) {
									$isection = $rule->section;
									$ielement = $rule->element;
									$ilvalue = $rule->value;
									$ioperator = $rule->operator;

									foreach ($epos['global'] as $priority => $pid) {
										foreach ($pid as $keydata => $data) {
											foreach ($data['sections'] as $epo_section) {
												if ( $section_id == $epo_section['sections_uniqid'] ){
													if (isset($epo_section['elements'][$ielement])){
														$el = $epo_section['elements'][$ielement];

														switch ($el['type']) {
															case 'radio':
															case 'select':
																
																if ( count( $el['options'] )>1 ){
																	if (!isset($checkmultiple[ $el['uniqid'] ])){
																		$checkmultiple[ $el['uniqid'] ]=array("options"=>array_flip($el['options']),"found"=>array());
																	}
																	$checkmultiple[ $el['uniqid'] ]["found"][]=array("value"=>$ilvalue,"price"=>$price);
																}else{
																	$logic_prices[ $section_id ][$element][] = $price;
																}
															
																# code...
																break;
															
															default:
																# code...
																break;
														}
													}
												}else{
													continue;
												}
											}

										}
									}

								}
							}

						}else{

							if ( isset( $logictree_helper_max[ $section_id ] ) && isset( $logictree_helper_max[ $section_id ][ $element ] ) && isset( $logictree_helper_max[ $section_id ][ $element ][ $id ] ) ){
								$rules = $logictree_helper_max[ $section_id ][ $element ][ $id ];
								if ( $rules->toggle == 'show'){

									if ( $rules->what == 'all'){
										$required_not_found[ $id ] = $id;
									}
									if ( $rules->what == 'any'){
										// don't add it
									}

								}
								elseif ( $rules->toggle == 'hide'){

									// not enough information so we just add it
									$required_not_found[ $id ] = $id;

								}
							}else{
								$required_not_found[ $id ] = $id;
							}

						}

					}

				}
			}

			foreach ($checkmultiple as $idata) {
				$found = false;
				$min_max=array();
				if (count($idata['options'])<=count($idata['found'])){
					foreach ($idata['found'] as $price_data) {
						$ivalue = $price_data['value'];
						if (isset($idata['options'][$ivalue])){
							unset($idata['options'][$ivalue]);
							$min_max[]= floatval($price_data['price']);
							if (count($idata['options'])==0){
								$found = true;
							}
						}
					}
				}

				if ($found){
					$add_logic_prices_max = $add_logic_prices_max + max($min_max);
				}
			}

			foreach ($logic_prices as $key => $section_id) {
				foreach ($section_id as $prices) {
					foreach ($prices as $price) {
						$add_logic_prices_max = $add_logic_prices_max + $price;
					}
				}
			}

		}

		if ( !empty( $variations_max ) ) {
			foreach ( $variations_max as $key => $value ) {
				$variations_max[ $key ] = $value + $variations_all;
			}
			$r["max"] = ($include_variation_prices) ? $variations_max : max( $variations_max );
		}
		if ( !empty($variations_min)  ) {
			$check = min( $variations_min );
			if (!empty( $check )){
				$r[ $minkey ] = ($include_variation_prices) ? $variations_min : min( $variations_min );
			}
		}

		if ( isset($r["max"])){
			
			if ( is_array($r[ "max" ])){
				array_walk($r[ "max" ], array($this,'add_values_walker'), $add_logic_prices);	
			}else{
				$r["max"] = $r["max"] + $add_logic_prices_max;
			}
			
		}
		if ( isset($r[ $minkey ])){
			if ( $include_variation_prices && is_array($r[ $minkey ])){
				array_walk($r[ $minkey ], array($this,'add_values_walker'), $add_logic_prices);	
			}else{
				$r[ $minkey ] = $r[ $minkey ] + $add_logic_prices;	
			}			
		}

		return $r;
	}
	
	public function add_values_walker( $value, $key, $num ) {
		$value = floatval($value) + floatval($num);
	}

	public function add_array_values( $input = array(), $add = array() ) {
		$r = array();

		if ( is_array( $input ) && is_array( $add ) ) {
			foreach ( $input as $key => $value ) {
				$a = 0;
				if ( isset( $add[ $key ] ) ) {
					$a = floatval( $add[ $key ] );
				}

				$r[ $key ] = floatval( $value ) + $a;
			}
		}

		return $r;
	}

	public function merge_price_array( $a = array(), $b = array() ) {
		if ( !is_array( $a ) || !is_array( $b ) ) {
			return $a;
		}

		$r = array();

		foreach ( $b as $key => $value ) {
			if ( $value === '' ) {
				$r[ $key ] = $a[ $key ];
			} else {
				$r[ $key ] = $value;
			}
		}

		return $r;
	}

	public function build_array( $a = array(), $b = array() ) {
		if ( !is_array( $a ) || !is_array( $b ) ) {
			return $a;
		}

		$r = array();

		foreach ( $b as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $a[ $key ] ) ) {
					$r[ $key ] = $value;
				} else {
					$r[ $key ] = $this->build_array( $a[ $key ], $b[ $key ] );
				}
			} else {
				if ( isset( $a[ $key ] ) ) {
					$r[ $key ] = $a[ $key ];
				} else {
					$r[ $key ] = $value;
				}
			}
		}

		return $r;
	}

	/**
	 * Filters an $input array by key.
	 */
	public function array_filter_key( $input, $what = "tmcp_", $where = "start" ) {
		if ( !is_array( $input ) || empty( $input ) ) {
			return array();
		}

		$filtered_result = array();

		if ( $where == "end" ) {
			$what = strrev( $what );
		}

		foreach ( $input as $key => $value ) {
			$k = $key;
			if ( $where == "end" ) {
				$k = strrev( $key );
			}
			if ( strpos( $k, $what ) === 0 ) {
				$filtered_result[ $key ] = $value;
			}
		}

		return $filtered_result;
	}

	public function array_map_deep( $array, $array2, $callback ) {
		$new = array();
		if ( is_array( $array ) && is_array( $array2 ) ) {
			foreach ( $array as $key => $val ) {
				if ( is_array( $val ) && is_array( $array2[ $key ] ) ) {
					$new[ $key ] = $this->array_map_deep( $val, $array2[ $key ], $callback );
				} else {
					$new[ $key ] = call_user_func( $callback, $val, $array2[ $key ] );
				}
			}
		} else {
			$new = call_user_func( $callback, $array, $array2 );
		}

		return $new;

	}

	/* Post URLs to IDs function, supports custom post types - borrowed and modified from url_to_postid() in wp-includes/rewrite.php */
	public function get_url_to_postid( $url ) {
		if ( function_exists( 'url_to_postid' ) ) {
			return url_to_postid( $url );
		}
		global $wp_rewrite;

		$url = apply_filters( 'tm_url_to_postid', $url );

		// First, check to see if there is a 'p=N' or 'page_id=N' to match against
		if ( preg_match( '#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values ) ) {
			$id = absint( $values[2] );
			if ( $id )
				return $id;
		}

		// Check to see if we are using rewrite rules
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
		if ( empty( $rewrite ) )
			return 0;

		// Get rid of the #anchor
		$url_split = explode( '#', $url );
		$url = $url_split[0];

		// Get rid of URL ?query=string
		$url_split = explode( '?', $url );
		$url = $url_split[0];

		// Add 'www.' if it is absent and should be there
		if ( FALSE !== strpos( home_url(), '://www.' ) && FALSE === strpos( $url, '://www.' ) )
			$url = str_replace( '://', '://www.', $url );

		// Strip 'www.' if it is present and shouldn't be
		if ( FALSE === strpos( home_url(), '://www.' ) )
			$url = str_replace( '://www.', '://', $url );

		// Strip 'index.php/' if we're not using path info permalinks
		if ( !$wp_rewrite->using_index_permalinks() )
			$url = str_replace( 'index.php/', '', $url );

		if ( FALSE !== strpos( $url, home_url() ) ) {
			// Chop off http://domain.com
			$url = str_replace( home_url(), '', $url );
		} else {
			// Chop off /path/to/blog
			$home_path = parse_url( home_url() );
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '';
			$url = str_replace( $home_path, '', $url );
		}

		// Trim leading and lagging slashes
		$url = trim( $url, '/' );

		$request = $url;
		// Look for matches.
		$request_match = $request;
		foreach ( (array) $rewrite as $match => $query ) {
			// If the requesting file is the anchor of the match, prepend it
			// to the path info.
			if ( !empty( $url ) && ($url != $request) && (strpos( $match, $url ) === 0) )
				$request_match = $url . '/' . $request;

			if ( preg_match( "!^$match!", $request_match, $matches ) ) {
				// Got a match.
				// Trim the query of everything up to the '?'.
				$query = preg_replace( "!^.+\?!", '', $query );

				// Substitute the substring matches into the query.
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );

				// Filter out non-public query vars
				global $wp;
				parse_str( $query, $query_vars );
				$query = array();
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array( $key, $wp->public_query_vars ) )
						$query[ $key ] = $value;
				}

				// Taken from class-wp.php
				foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
					if ( $t->query_var )
						$post_type_query_vars[ $t->query_var ] = $post_type;

				foreach ( $wp->public_query_vars as $wpvar ) {
					if ( isset( $wp->extra_query_vars[ $wpvar ] ) )
						$query[ $wpvar ] = $wp->extra_query_vars[ $wpvar ];
					elseif ( isset( $_POST[ $wpvar ] ) )
						$query[ $wpvar ] = $_POST[ $wpvar ];
					elseif ( isset( $_GET[ $wpvar ] ) )
						$query[ $wpvar ] = $_GET[ $wpvar ];
					elseif ( isset( $query_vars[ $wpvar ] ) )
						$query[ $wpvar ] = $query_vars[ $wpvar ];

					if ( !empty( $query[ $wpvar ] ) ) {
						if ( !is_array( $query[ $wpvar ] ) ) {
							$query[ $wpvar ] = (string) $query[ $wpvar ];
						} else {
							foreach ( $query[ $wpvar ] as $vkey => $v ) {
								if ( !is_object( $v ) ) {
									$query[ $wpvar ][ $vkey ] = (string) $v;
								}
							}
						}

						if ( isset( $post_type_query_vars[ $wpvar ] ) ) {
							$query['post_type'] = $post_type_query_vars[ $wpvar ];
							$query['name'] = $query[ $wpvar ];
						}
					}
				}

				// Do the query
				$query = new WP_Query( $query );
				if ( !empty( $query->posts ) && $query->is_singular )
					return $query->post->ID;
				else
					return 0;
			}
		}

		return 0;
	}

	public function new_meta() {
		global $wp_version;

		return version_compare( $wp_version, '4.0.1', '>' );
	}

	public function build_meta_query( $relation = 'OR', $meta_key = '', $meta_value = '', $compare = '!=', $exists = 'NOT EXISTS' ) {
		$meta_array = array(
			'relation' => $relation,
			array(
				'key'     => $meta_key, // get only enabled global extra options
				'value'   => $meta_value,
				'compare' => $compare,
			),
			array(
				'key'     => $meta_key,// backwards compatibility
				'value'   => $meta_value,
				'compare' => $exists,
			),
		);
		if ( $this->new_meta() ) {
			$meta_array = array(
				'relation' => $relation,
				array(
					'key'     => $meta_key, // get only enabled global extra options
					'value'   => $meta_value,
					'compare' => $compare,
				),
				array(
					'key'     => $meta_key,// backwards compatibility
					'compare' => $exists,
				),
			);

		}

		return $meta_array;
	}

	public function tm_uniqid( $prefix = "" ) {
		return uniqid( $prefix, TRUE );
	}

	public function tm_temp_uniqid( $s ) {
		$a = array();
		for ( $m = 0; $m < $s; $m++ ) {
			$a[] = $this->tm_uniqid();
		}

		return $a;
	}

	public function encodeURIComponent( $str ) {
		$revert = array( '%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );

		return strtr( rawurlencode( $str ), $revert );
	}

	public function reverse_strrchr( $haystack, $needle, $trail = 0 ) {
		return strrpos( $haystack, $needle ) !== FALSE ? substr( $haystack, 0, strrpos( $haystack, $needle ) + $trail ) : FALSE;
	}

	private function _count_posts_cache_key( $type = 'post', $perm = '' ) {
		$cache_key = 'tm-posts-' . $type;
		if ( 'readable' == $perm && is_user_logged_in() ) {
			$post_type_object = get_post_type_object( $type );
			if ( $post_type_object && !current_user_can( $post_type_object->cap->read_private_posts ) ) {
				$cache_key .= '_' . $perm . '_' . get_current_user_id();
			}
		}

		return $cache_key;
	}

	public function wp_count_posts( $type = 'post', $perm = '' ) {
		global $wpdb;

		if ( !post_type_exists( $type ) )
			return new stdClass;

		$cache_key = $this->_count_posts_cache_key( $type, $perm );

		// WPML
		$_lang = TM_EPO_WPML()->get_lang();
		if ( TM_EPO_WPML()->is_active() && TM_EPO_WPML()->get_lang() != 'all' && $_lang == TM_EPO_WPML()->get_default_lang() ) {
			$query = "SELECT p.post_status, COUNT( DISTINCT ID ) AS num_posts FROM {$wpdb->posts} p";
		} else {
			$query = "SELECT p.post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} p";
		}
		// WPML
		if ( TM_EPO_WPML()->is_active() && TM_EPO_WPML()->get_lang() != 'all' ) {
			if ( $_lang == TM_EPO_WPML()->get_default_lang() ) {
				$query .= " LEFT JOIN {$wpdb->postmeta} ON (p.ID = {$wpdb->postmeta}.post_id)"
					. " LEFT JOIN {$wpdb->postmeta} AS mt1 ON (p.ID = mt1.post_id AND mt1.meta_key = '" . TM_EPO_WPML_LANG_META . "')";
			} else {
				$query .= " JOIN  {$wpdb->postmeta} pm";
			}
		}
		// WPML
		if ( TM_EPO_WPML()->is_active() && TM_EPO_WPML()->get_lang() != 'all' && $_lang == TM_EPO_WPML()->get_default_lang() ) {
			$query .= " WHERE 1=1 AND p.post_type = %s";
		} else {
			$query .= " WHERE p.post_type = %s";
		}

		if ( 'readable' == $perm && is_user_logged_in() ) {
			$post_type_object = get_post_type_object( $type );
			if ( !current_user_can( $post_type_object->cap->read_private_posts ) ) {
				$query .= $wpdb->prepare( " AND (p.post_status != 'private' OR ( p.post_author = %d AND p.post_status = 'private' ))",
					get_current_user_id()
				);
			}
		}

		// WPML
		if ( TM_EPO_WPML()->is_active() && TM_EPO_WPML()->get_lang() != 'all' ) {
			if ( $_lang == TM_EPO_WPML()->get_default_lang() ) {
				$query .= " AND ( ( " . $wpdb->prefix . "postmeta.meta_key = '" . TM_EPO_WPML_LANG_META . "' AND CAST(" . $wpdb->prefix . "postmeta.meta_value AS CHAR) = '" . TM_EPO_WPML()->get_lang() . "' ) OR mt1.post_id IS NULL ) ";
			} else {
				$query .= " AND p.ID=pm.post_id AND pm.meta_key = '" . TM_EPO_WPML_LANG_META . "' AND pm.meta_value = '" . TM_EPO_WPML()->get_lang() . "'";
			}
		}

		$query .= ' GROUP BY p.post_status';

		$counts = wp_cache_get( $cache_key, 'counts' );
		if ( FALSE === $counts ) {
			$results = (array) $wpdb->get_results( $wpdb->prepare( $query, $type ), ARRAY_A );
			$counts = array_fill_keys( get_post_stati(), 0 );

			foreach ( $results as $row )
				$counts[ $row['post_status'] ] = $row['num_posts'];

			$counts = (object) $counts;
			wp_cache_set( $cache_key, $counts, 'counts' );
		}

		return apply_filters( 'wp_count_posts', $counts, $type, $perm );
	}

	public function wc_base_currency() {
		$from_currency = get_option( 'woocommerce_currency' );

		return $from_currency;
	}

	public function get_currencies() {
		$enabled_currencies = apply_filters( 'wc_aelia_cs_enabled_currencies', array( $this->wc_base_currency() ) );
		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			$currencies = is_callable( array( $WOOCS, 'get_currencies' ) )?$WOOCS->get_currencies():array();

			if ( $currencies && is_array( $currencies ) ) {
				$enabled_currencies = array();
				foreach ( $currencies as $key => $value ) {
					$enabled_currencies[] = $value['name'];
				}
			}
		} elseif ( class_exists( 'WooCommerce_All_in_One_Currency_Converter_Main' ) ) {
			global $woocommerce_all_in_one_currency_converter;
			$currency_data = $woocommerce_all_in_one_currency_converter->settings->get_currency_data();
			if ( $currency_data && is_array( $currency_data ) ) {
				$enabled_currencies = array();
				foreach ( $currency_data as $key => $value ) {
					$enabled_currencies[] = $key;
				}
			}
		}

		return $enabled_currencies;
	}

	public function wc_aelia_cs_enabled_currencies() {
		$enabled_currencies = $this->get_currencies();
		$from_currency = $this->wc_base_currency();
		foreach ( $enabled_currencies as $key => $value ) {
			if ( $value == $from_currency ) {
				unset( $enabled_currencies[ $key ] );
				break;
			}
		}

		return $enabled_currencies;
	}

	public function wc_aelia_num_enabled_currencies() {
		$enabled_currencies = $this->wc_aelia_cs_enabled_currencies();
		if ( is_array( $enabled_currencies ) ) {
			return count( $enabled_currencies );
		}

		return 0;
	}

	public function get_currency_price_prefix( $currency = NULL ) {
		if ( $currency == NULL ) {
			if ( $this->wc_aelia_num_enabled_currencies() > 0 ) {
				$to_currency = tc_get_woocommerce_currency();

				return "_" . $to_currency;
			} else {
				return "";
			}
		} else {
			return (empty( $currency ) || $currency == $this->wc_base_currency()) ? "" : "_" . $currency;
		}
	}

	public function formatBytes( $bytes, $precision = 2 ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

		$bytes = max( $bytes, 0 );
		$pow = floor( ($bytes ? log( $bytes ) : 0) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );

		// Uncomment one of the following alternatives
		$bytes /= pow( 1024, $pow );

		// $bytes /= (1 << (10 * $pow));

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	public function convert_to_right_icon( $label = '', $icon = 'tcfa-angle-right' ) {
		$label = str_replace( "/", "", $label );
		$label .= '<i class="tm-icon tmfa tcfa ' . $icon . '"></i>';

		return $label;
	}

	public function url_to_links( $url = '', $main_path = '', $main_path_label = '' ) {

		$param = str_replace( $main_path, "", $url );
		$param = explode( "/", $param );

		$html = '';

		$a = '<a class="tm-mn-movetodir" data-tm-dir="" href="' . esc_attr( $main_path ) . '">' . $this->convert_to_right_icon( esc_html( $main_path_label ) ) . '</a>';
		$html .= $a;
		$todir = '';
		foreach ( $param as $key => $value ) {
			if ( $key == count( $param ) - 1 ) {
				$a = '<span class="tm-mn-currentdir">' . esc_html( $value ) . '</span>';
			} else {
				$data_tm_dir = (empty( $todir )) ? $value : $todir . "/" . $value;
				$a = '<a class="tm-mn-movetodir" data-tm-dir="' . esc_attr( $data_tm_dir ) . '" href="' . esc_attr( $main_path . $data_tm_dir ) . '">' . $this->convert_to_right_icon( esc_html( $value . "/" ) ) . '</a>';
				$todir = $data_tm_dir;
			}
			$html .= $a;
		}


		return $html;//$main_path_label.$param;
	}

	public function init_filesystem() {
		if ( function_exists( 'get_filesystem_method' ) ) {
			$access_type = get_filesystem_method();
			if ( $access_type === 'direct' ) {
				/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
				$creds = request_filesystem_credentials( site_url() . '/wp-admin/', '', FALSE, FALSE, array() );

				/* initialize the API */
				if ( !WP_Filesystem( $creds ) ) {
					/* any problems and we exit */
					return '';
				}

				return TRUE;
			}
		}

		return FALSE;
	}

	public function file_rmdir( $file = '' ) {
		if ( $this->init_filesystem() ) {
			global $wp_filesystem;
			$mn = $wp_filesystem->rmdir( $file, TRUE );
			clearstatcache();

			return $mn;
		}

		return FALSE;
	}

	public function file_delete( $file = '' ) {
		if ( $this->init_filesystem() ) {
			global $wp_filesystem;
			$mn = $wp_filesystem->delete( $file );
			clearstatcache();

			return $mn;
		}

		return FALSE;
	}

	public function file_manager( $main_path = '', $todir = '' ) {
		$html = "";

		if ( $main_path !== '//' && $this->init_filesystem() ) {

			global $wp_filesystem;

			$subdir = $main_path . $todir;
			$param = wp_upload_dir();
			if ( empty( $param['subdir'] ) ) {
				$base_url = $param['url'] . $main_path;
				$param['path'] = $param['path'] . $subdir;
				$param['url'] = $param['url'] . $subdir;
				$param['subdir'] = $subdir;
			} else {
				$param['path'] = str_replace( $param['subdir'], $subdir, $param['path'] );
				$param['url'] = str_replace( $param['subdir'], $subdir, $param['url'] );
				$param['subdir'] = str_replace( $param['subdir'], $subdir, $param['subdir'] );
				$base_url = str_replace( $param['subdir'], $main_path, $param['url'] );
			}

			clearstatcache();
			$mn = $wp_filesystem->dirlist( $param['path'], TRUE, FALSE );

			$files = array();
			$directories = array();
			if ( $mn ) {
				foreach ( $mn as $key => $value ) {
					if ( isset( $value["type"] ) && isset( $value["name"] ) && isset( $value["size"] ) ) {
						switch ( strtolower( $value["type"] ) ) {
							case 'd':
								$directories[] = array( "name" => $value["name"], "size" => 0 );
								break;

							case 'f':
								$files[] = array( "name" => $value["name"], "size" => $value["size"] );
								break;
						}
					}
				}
			}

			$html .= '<div class="tm-mn-header"><div class="tm-mn-path">' . $this->url_to_links( $param['url'], $base_url, $main_path ) . '</div></div>';
			$html .= '<div class="tm-mn-wrap-heading tm-row nopadding nomargin">';
			$html .= '<div class="tm-mn-name tm-cell col-6">' . __( 'Filename', 'woocommerce-tm-extra-product-options' ) . '</div>';
			$html .= '<div class="tm-mn-size tm-cell col-3">' . __( 'Size', 'woocommerce-tm-extra-product-options' ) . '</div>';
			$html .= '<div class="tm-mn-op tm-cell col-3">&nbsp;</div>';
			$html .= '</div>';
			foreach ( $directories as $key => $value ) {
				$filetype = wp_check_filetype( $value["name"] );
				$img = '<img class="tm-mime" src="' . esc_attr( wp_mime_type_icon( $filetype['type'] ) ) . '" /> ';
				$html .= '<div class="tm-mn-wrap-dir tm-row nopadding nomargin">';
				$data_tm_dir = (empty( $todir )) ? $value["name"] : $todir . "/" . $value["name"];
				$html .= '<div class="tm-mn-name tm-cell col-6">' . $img . '<a class="tm-mn-movetodir" data-tm-dir="' . esc_attr( $data_tm_dir ) . '" href="' . esc_attr( $param['url'] . $value["name"] ) . '">' . esc_html( $value["name"] ) . '</a></div>';
				$html .= '<div class="tm-mn-size tm-cell col-3">&nbsp;</div>';
				$html .= '<div class="tm-mn-op tm-cell col-3">'
					. '<a title="' . __( 'Delete', 'woocommerce-tm-extra-product-options' ) . '" href="#" data-tm-dir="' . esc_attr( $todir ) . '" data-tm-deldir="' . esc_attr( $data_tm_dir ) . '" class="tm-mn-deldir"><i class="tm-icon tmfa tcfa tcfa-times"></i></a>'
					. '</div>';
				$html .= '</div>';
			}
			foreach ( $files as $key => $value ) {
				$filetype = wp_check_filetype( $value["name"] );
				$img = '<img class="tm-mime" src="' . esc_attr( wp_mime_type_icon( $filetype['type'] ) ) . '" /> ';

				$html .= '<div class="tm-mn-wrap-file tm-row nopadding nomargin">';
				$data_tm_dir = $todir;
				$html .= '<div class="tm-mn-name tm-cell col-6">' . $img . '<a class="tm-download-file" download href="' . esc_attr( $base_url . $todir . '/' . $value["name"] ) . '">' . esc_html( $value["name"] ) . '</a>' . '</div>';
				$html .= '<div class="tm-mn-size tm-cell col-3">' . $this->formatBytes( $value["size"], 2 ) . '</div>';
				$html .= '<div class="tm-mn-op tm-cell col-3">'
					. '<a title="' . __( 'Delete', 'woocommerce-tm-extra-product-options' ) . '" href="#" data-tm-dir="' . esc_attr( $todir ) . '" data-tm-deldir="' . esc_attr( $data_tm_dir ) . '" data-tm-delfile="' . esc_attr( $value["name"] ) . '" class="tm-mn-delfile"><i class="tm-icon tmfa tcfa tcfa-times"></i></a>'
					. '</div>';
				$html .= '</div>';
			}

		}

		return $html;
	}

	public function get_saved_order_multiple_keys( $current_product_id = 0 ) {
		$this_land_epos = TM_EPO()->get_product_tm_epos( $current_product_id );
		$saved_order_multiple_keys = array();
		if ( isset( $this_land_epos['global'] ) && is_array( $this_land_epos['global'] ) ) {
			foreach ( $this_land_epos['global'] as $priority => $priorities ) {
				if ( is_array( $priorities ) ) {
					foreach ( $priorities as $pid => $field ) {
						if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
							foreach ( $field['sections'] as $section_id => $section ) {
								if ( isset( $section['elements'] ) && is_array( $section['elements'] ) ) {
									foreach ( $section['elements'] as $element ) {
										$saved_order_multiple_keys[ $element['uniqid'] ] = $element['label'];
										$saved_order_multiple_keys[ "options_" . $element['uniqid'] ] = $element['options'];
									}
								}
							}
						}
					}
				}
			}
		}

		return $saved_order_multiple_keys;
	}

	public function tm_get_order_object() {
		global $thepostid, $theorder;

		if ( !is_object( $theorder ) ) {
			$theorder = wc_get_order( $thepostid );
		}
		if ( !$theorder && isset( $_POST['order_id'] ) ) {
			$order_id = absint( $_POST['order_id'] );
			$order = wc_get_order( $order_id );

			return $order;
		} elseif ( !$theorder && isset( $_POST['post_ID'] ) ) {
			$order_id = absint( $_POST['post_ID'] );
			$order = wc_get_order( $order_id );

			return $order;
		}
		if ( !$theorder ) {
			global $post;
			if ( $post ) {
				$theorder = wc_get_order( $post->ID );
			}
		}

		return $theorder;
	}

	public function upload_to_png( $source, $target ) {

		$sourceImg = @imagecreatefromstring( file_get_contents( $source ) );

		if ( $sourceImg === FALSE ) {
			return FALSE;//Invalid image
		}

		$width = imagesx( $sourceImg );
		$height = imagesy( $sourceImg );
		$targetImg = imagecreatetruecolor( $width, $height );
		imagecolortransparent( $targetImg, imagecolorallocatealpha( $targetImg, 0, 0, 0, 127 ) );
		imagealphablending( $targetImg, FALSE );

		imagesavealpha( $targetImg, TRUE );

		imagecopy( $targetImg, $sourceImg, 0, 0, 0, 0, $width, $height );

		imagedestroy( $sourceImg );
		imagepng( $targetImg, $target );
		imagedestroy( $targetImg );

		return TRUE;
	}

	public function str_startswith( $source, $prefix ) {
		return strncmp( $source, $prefix, strlen( $prefix ) ) == 0;
	}

	public function str_endsswith( $source, $prefix ) {
		return $prefix === '' || (strlen( $prefix ) <= strlen( $source ) && substr_compare( $source, $prefix, -strlen( $prefix ) ) === 0);
	}

	/**
	 * Search through an array for a matching key.
	 *
	 * https://gist.github.com/steve-todorov/3671626
	 *
	 * @param array $input_array
	 * @param string $search_value
	 * @param bool $case_sensitive
	 *
	 * @return array
	 */
	public function array_contains_key( array $input_array, $search_value, $case_sensitive = TRUE ) {
		if ( $case_sensitive ) {
			$preg_match = '/' . $search_value . '/';
		} else {
			$preg_match = '/' . $search_value . '/i';
		}
		$return_array = array();
		$keys = array_keys( $input_array );
		foreach ( $keys as $k ) {
			if ( preg_match( $preg_match, $k ) ) {
				$return_array[ $k ] = $input_array[ $k ];
			}
		}

		return $return_array;
	}

	public function sanitize_key( $source ) {
		return str_replace( array( "[", "]" ), '', $source );
	}

}

