<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @package   WC-Memberships/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Membership Rules Handler class.
 *
 * This class handles all rules-related functionality in Memberships.
 *
 * @since 1.0.0
 */
class WC_Memberships_Rules {


	/** @var \WC_Memberships_Membership_Plan_Rule[] all rules (associative array of rule IDs and initialized rule objects) */
	private $rules = array();

	/** @var array|\WC_Memberships_Membership_Plan_Rule[] queried rules (associative array with cache keys according to rule query) */
	private $applied_rules = array();


	/**
	 * Initializes the handler.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// load the rule object
		require_once( wc_memberships()->get_plugin_path() .'/includes/class-wc-memberships-membership-plan-rule.php' );
	}


	/**
	 * Initializes the rules.
	 *
	 * @since 1.9.0
	 */
	private function init_rules() {

		// init rules
		if ( empty( $this->rules ) ) {

			$rules = $this->get_rules_raw();

			if ( is_array( $rules ) && ! empty( $rules ) ) {
				foreach ( $rules as $rule ) {
					if ( is_array( $rule ) && ! empty( $rule ) ) {

						$rule = new WC_Memberships_Membership_Plan_Rule( $rule );

						if ( $rule->has_id() ) {
							$this->rules[ $rule->get_id() ] = $rule;
						}
					}
				}
			}
		}
	}


	/**
	 * Flushes cached rules and reinitializes them.
	 *
	 * @since 1.9.0
	 */
	private function flush_rules() {

		$this->rules         = array();
		$this->applied_rules = array();

		$this->init_rules();
	}


	/**
	 * Returns the raw option with rules.
	 *
	 * @since 1.9.0
	 *
	 * @return array
	 */
	public function get_rules_raw() {
		return get_option( 'wc_memberships_rules', array() );
	}


	/**
	 * Returns the rules.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args optional array of arguments {
	 *   @type string|array $rule_type Optional. Rule type. One or more of 'content_restriction', 'product_restriction' or 'purchasing_discount'
	 *   @type string $content_type Optional. Content type. One of 'post_type' or 'taxonomy'
	 *   @type string $content_type_name Optional. Content type name. A valid post type or taxonomy name.
	 *   @type string|int $id Optional. Post or taxonomy term ID/slug
	 *   @type bool $exclude_inherited Optional. Whether to exclude inherited rules (from post type or taxonomy) when requesting rules for a specific post.
	 *   @type bool $include_specific Optional. Whether to include specific (child) rules for specific objects, when querying forwide/general rules. When true, will include for example, term-specific rules when requesting for taxonomy rules.
	 *   @type mixed $plan_status Optional. Filter rules by plan status. Either a single plan status, array of statuses or 'any' for any status.
	 * }
	 * @return \WC_Memberships_Membership_Plan_Rule[] associative array of rule IDs and rule objects
	 */
	public function get_rules( $args = array() ) {

		$this->init_rules();

		$args = wp_parse_args( $args, array(
			'rule_type'          => $this->get_valid_rule_types(),
			'content_type'       => null,
			'content_type_name'  => null,
			'object_id'          => null,
			'exclude_inherited'  => false,
			'include_specific'   => false,
			'plan_status'        => 'publish'
		) );

		if ( $args['rule_type'] && ! is_array( $args['rule_type'] ) ) {
			$args['rule_type'] = (array) $args['rule_type'];
		}

		if ( ! $args['content_type'] && ( $args['object_id'] || $args['content_type_name'] ) ) {

			// bail out if object ID or content type name is provided, but content type itself is missing
			return array();

		} elseif ( empty( $args['content_type'] ) ) {

			// if no content type is specified, return all rules that match the rule type(s)
			$rules = array();

			if ( ! empty( $this->rules ) ) {
				foreach ( $this->rules as $rule ) {
					if ( in_array( $rule->get_rule_type(), $args['rule_type'], true ) ) {
						$rules[ $rule->get_id() ] = $rule;
					}
				}
			}

			return $rules;
		}

		// normalize object ID
		if ( ! empty( $args['object_id'] ) ) {

			// if the object ID is not numeric, try to get id from slug
			$args['object_id'] = $this->get_queried_rules_object_id( $args['object_id'], $args['content_type'], $args['content_type_name'] );

			// bail out if we could not determine the ID (the ID is still not a positive integer by this point)
			if ( null === $args['object_id'] || $args['object_id'] <= 0 ) {
				return array();
			}
		}

		// unique key for caching the applied rule results
		$applied_rule_key = http_build_query( $args );

		// fetch all rules or rules that apply to specific content types or objects and cache them
		if ( ! isset( $this->applied_rules[ $applied_rule_key ] ) ) {
			$this->applied_rules[ $applied_rule_key ] = $this->query_rules( $args );
		}

		$found_rules = $this->applied_rules[ $applied_rule_key ];

		if ( ! empty( $found_rules ) && isset( $args['fields'] ) && 'ids' === $args['fields'] ) {
			$found_rules = array_keys( $found_rules );
		}

		return $found_rules;
	}


	/**
	 * Returns valid rule types.
	 *
	 * @since 1.9.0
	 *
	 * @return string[]
	 */
	public function get_valid_rule_types() {
		return array(
			'content_restriction',
			'product_restriction',
			'purchasing_discount',
		);
	}


	/**
	 * Checks if the rule type is of a valid type.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type the rule type to check
	 * @return bool
	 */
	public function is_valid_rule_type( $type ) {
		return in_array( $type, $this->get_valid_rule_types(), true );
	}


	/**
	 * Returns valid access types for content and product restriction rules.
	 *
	 * @since 1.9.0
	 *
	 * @param string $rule_type either 'product_restriction' (default) or 'content_restriction'
	 * @return string[]
	 */
	public function get_rules_valid_access_types( $rule_type = 'product_restriction' ) {

		$access_types = array( array(
			'content_restriction' => array(
				'view',
			),
			'product_restriction' => array(
				'view',
				'purchase',
			),
		) );

		return isset( $access_types[ $rule_type ] ) ? $access_types[ $rule_type ] : array();
	}


	/**
	 * Checks if an access type is of a valid type.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type access type to check
	 * @param string $rule_type either 'product_restriction' (default) or 'content_restriction'
	 * @return bool
	 */
	public function is_valid_rule_access_type( $type, $rule_type = 'product_restriction' ) {
		return in_array( $type, $this->get_rules_valid_access_types( $rule_type ), true );
	}


	/**
	 * Returns valid content types for rules.
	 *
	 * @since 1.9.0
	 *
	 * @return string[]
	 */
	public function get_rule_valid_content_types() {
		return array(
			'post_type',
			'taxonomy',
		);
	}


	/**
	 * Checks if a content type is of a valid type for rules.
	 *
	 * Note: does not check whether the content type actually exists.
	 * @see \WC_Memberships_Rules::rule_content_type_exists()
	 *
	 * @since 1.9.0
	 *
	 * @param string $type content type to check
	 * @return bool
	 */
	public function is_valid_rule_content_type( $type ) {
		return in_array( $type, $this->get_rule_valid_content_types(), true );
	}


	/**
	 * Checks if a rule target content type type exists.
	 *
	 * @since 1.9.0
	 *
	 * @param \WC_Memberships_Membership_Plan_Rule $rule a rule object
	 * @return bool
	 */
	public function rule_content_type_exists( WC_Memberships_Membership_Plan_Rule $rule ) {
		// content is either a taxonomy or a post type
		return 'post_type' === $rule->get_content_type() ? post_type_exists( $rule->get_content_type_name() ) : taxonomy_exists( $rule->get_content_type_name() );
	}


	/**
	 * Checks if a content type name is suitable for a rule type.
	 *
	 * E.g. content restriction rules should not target products and product categories.
	 *
	 * Note: does not check whether the content type actually exists.
	 * @see \WC_Memberships_Rules::rule_content_type_exists()
	 *
	 * @since 1.9.0
	 *
	 * @param string $name
	 * @param string $rule_type
	 * @return bool
	 */
	public function is_valid_rule_content_type_name( $name, $rule_type ) {

		if ( $taxonomy = get_taxonomy( $name ) ) {
			$is_products = in_array( 'product', $taxonomy->object_type, true );
		} elseif ( 'product' === $name ) {
			$is_products = true;
		} else {
			$is_products = false;
		}

		if ( in_array( $rule_type, array( 'product_restriction', 'purchasing_discount' ), true ) ) {
			$is_valid = $is_products;
		} else {
			$is_valid = ! $is_products;
		}

		return $is_valid;
	}


	/**
	 * Returns valid purchasing discount valid types.
	 *
	 * @since 1.9.0
	 *
	 * @param bool $with_labels whether to return discount type keys or associative array with labels
	 * @return string[]|array
	 */
	public function get_valid_discount_types( $with_labels = false ) {

		$types = array(
			'amount'     => __( 'Amount',     'woocommerce-memberships' ),
			'percentage' => __( 'Percentage', 'ultimatewoo-pro' ),
		);

		return $with_labels ? $types : array_keys( $types );
	}


	/**
	 * Checks if a discount is of a valid type.
	 *
	 * @since 1.9.0
	 *
	 * @param string $type discount type
	 * @return bool
	 */
	public function is_valid_discount_type( $type ) {
		return in_array( $type, $this->get_valid_discount_types(), true );
	}


	/**
	 * Returns the object ID as the unique identifier or maybe a slug.
	 *
	 * @see \WC_Memberships_Rules::get_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param int|string|null $object_id object identifier as unique ID or slug
	 * @param string $content_type post type, taxonomy...
	 * @param string $content_type_name post type name or taxonomy name
	 * @return int|null
	 */
	private function get_queried_rules_object_id( $object_id, $content_type, $content_type_name ) {

		$object_id = is_numeric( $object_id ) ? (int) $object_id : $object_id;

		// maybe the object ID is a slug (string)
		if ( is_string( $object_id ) && is_string( $content_type ) && is_string( $content_type_name ) ) {

			switch ( $content_type ) {

				case 'post_type':

					$slug      = $object_id;
					$post_type = $content_type_name;

					if ( is_string( $slug ) && '' !== $slug &&! empty( $post_type ) && '' !== $post_type ) {
						$posts = get_posts( array(
							'name'           => $slug,
							'post_type'      => $post_type,
							'posts_per_page' => 1,
						) );
					}

					$object_id = ! empty( $posts ) ? $posts[0]->ID : null;

				break;

				case 'taxonomy':

					$term      = get_term_by( 'slug', $object_id, $content_type_name );
					$object_id = is_object( $term ) && ! is_wp_error( $term ) ? $term->term_id : null;

				break;

			}
		}

		return is_numeric( $object_id ) ? absint( $object_id ) : null;
	}


	/**
	 * Queries rules to be returned.
	 *
	 * @see \WC_Memberships_Rules::get_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param array $args associative array of arguments to determine rules that apply for
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	private function query_rules( array $args ) {

		$applicable_rules = array();

		if ( ! empty( $this->rules ) ) {
			foreach ( $this->rules as $key => $rule ) {

				$rule_type   = $rule->get_rule_type();

				// Sanity checks:
				// - skip invalid rule types (shouldn't happen)
				// - skip rules not linked to a plan (shouldn't happen)
				// - skip rules not matching the queried type
				if ( empty( $rule_type ) || ! $rule->has_membership_plan_id() || ! in_array( $rule_type, (array) $args['rule_type'], true ) ) {
					continue;
				}

				$apply_rule  = false;
				$plan_status = get_post_status( $rule->get_membership_plan_id() );

				// check if the membership plan of this rule matches the requested status
				if ( is_array( $args['plan_status'] ) ) {
					$matches_plan_status = in_array( $plan_status, $args['plan_status'], true );
				} elseif ( in_array( $args['plan_status'], array( 'any', 'all' ), true ) ) {
					$matches_plan_status = true;
				} else {
					$matches_plan_status = $plan_status === $args['plan_status'];
				}

				// further processing makes sense only if plan status matches
				if ( $matches_plan_status ) {

					$rule_object_ids            = $rule->get_object_ids();
					$matches_content_type       = $rule->applies_to( 'content_type',      $args['content_type'] );
					$matches_content_type_name  = $rule->applies_to( 'content_type_name', $args['content_type_name'] );
					$matches_object_id          = $rule->applies_to( 'object_id',         $args['object_id'] );
					$no_object_id_match         = ! $args['object_id']         &&   empty( $rule_object_ids );
					$no_content_type_name_match = ! $args['content_type_name'] && ! $rule->get_content_type_name();

					if ( $matches_content_type && ( ( $no_object_id_match && $no_content_type_name_match ) || ( ! $no_object_id_match && ! $no_content_type_name_match && $args['include_specific'] ) ) ) {

						// content type matches, but not the object ID & content type name
						$apply_rule = true;

					} elseif ( $matches_content_type && $matches_content_type_name && ( $no_object_id_match || $args['include_specific'] ) ) {

						// content type & name matches, but not the object ID
						$apply_rule = true;

					} elseif ( $matches_content_type && $matches_content_type_name && $args['object_id'] ) {

						if ( $matches_object_id ) {

							// object ID, content type & name all match
							$apply_rule = true;

						} elseif ( 'product_variation' === get_post_type( $args['object_id'] ) && in_array( 'purchasing_discount', (array) $args['rule_type'], true ) ) {

							// special handling for purchasing discounts that apply to variable products
							$apply_rule = $rule->applies_to( 'object_id', wp_get_post_parent_id( $args['object_id'] ) );
						}
					}

					// Handle rule inheritance.
					// For example, rules that apply to a taxonomy or post type must be applied to specific objects that match the taxonomy or post type.
					if ( ! $args['exclude_inherited'] && $args['object_id'] ) {

						switch ( $args['content_type'] ) {

							case 'post_type':

								// Handle post-taxonomy inheritance/relationships:
								if ( $rule->applies_to( 'content_type', 'taxonomy' ) ) {

									// does the requested post have any of the terms specified in the rule?
									if ( ! empty( $rule_object_ids ) && is_array( $rule_object_ids ) ) {

										$taxonomy_name   = $rule->get_content_type_name();
										$taxonomy_object = get_taxonomy( $taxonomy_name );
										$object_id       = $args['object_id'];

										// Special handling for purchasing discounts that apply to product categories:
										// the product_cat taxonomy does not include the product_variation as an object type,
										// nor do any product_variation posts have product_cat terms,
										// so use use the parent (variable) product when checking if the rule applies
										if (     $rule->applies_to( 'content_type_name', 'product_cat' )
										     && 'product_variation' === get_post_type( $object_id )
										     && in_array( 'purchasing_discount', $args['rule_type'], true ) ) {

											$object_id = wp_get_post_parent_id( $object_id );
										}

										// skip if the term taxonomy does not exist or does apply to the post type
										if ( ! $taxonomy_object || ! in_array( get_post_type( $object_id ), (array) $taxonomy_object->object_type, true ) ) {
											break;
										}

										// ensure child terms inherit their parent handling (either granted or restricted)
										foreach ( $rule_object_ids as $rule_object_id ) {

											$children_object_ids = get_term_children( $rule_object_id, $taxonomy_name );

											if ( ! empty( $children_object_ids ) && ! is_wp_error( $children_object_ids ) ) {
												$rule_object_ids = array_unique( array_merge( $rule_object_ids, $children_object_ids ) );
											}
										}

										// finally check if any of the terms (and term children) apply to the post
										foreach ( $rule_object_ids as $term_id ) {
											if ( has_term( $term_id, $taxonomy_name, $object_id ) ) {
												// break as soon we have match
												$apply_rule = true;
												break;
											}
										}

									// the taxonomy rule does not specify any terms
									} else {

										// does the queried object_id have any terms from that particular taxonomy?
										$taxonomy_name   = $rule->get_content_type_name();
										$taxonomy_object = get_taxonomy( $taxonomy_name );

										// sanity check: is the taxonomy currently registered for the post type?
										if ( $taxonomy_object ) {

											// if it's the product category and we are querying a product or variation we assume it does
											if ( 'product_cat' === $taxonomy_name && in_array( get_post_type( $args['object_id'] ), array( 'product', 'product_variation' ), true ) ) {

												// note: product_cat is not registered for product variations, so the check below would fail
												$apply_rule = true;

											// otherwise, since get_the_terms does not care about this, so we need to make sure we do!
											} elseif ( in_array( get_post_type( $args['object_id'] ), (array) $taxonomy_object->object_type, true ) || in_array( get_post_type( wp_get_post_parent_id( $args['object_id'] ) ), (array) $taxonomy_object->object_type, true ) ) {

												$terms = get_the_terms( $args['object_id'], $taxonomy_name );

												if ( ! empty( $terms ) && is_array( $terms ) ) {
													$apply_rule = true;
												}
											}
										}
									}

								} elseif ( empty( $rule_object_ids ) && $matches_content_type && $matches_content_type_name ) {

									// Handle post-post type inheritance:
									// rules that apply to the same post type and have no object_ids specified, apply as well.
									$apply_rule = true;
								}

							break;

							case 'taxonomy':

								// does the term belong to the taxonomy?
								if ( $matches_content_type_name && empty( $rule_object_ids ) && $rule->applies_to( 'content_type', 'taxonomy' ) ) {
									$apply_rule = true;
								}

							break;
						}
					}
				}

				if ( $apply_rule ) {
					$applicable_rules[ $rule->get_id() ] = $rule;
				}
			}
		}

		return $applicable_rules;
	}


	/**
	 * Returns all rules belonging to a specific plan.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WC_Memberships_Membership_Plan $plan_id plan object or identifier
	 * @param bool $edit whether to fetch all rules (default true) or only rules that become applicable when plan is published
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_plan_rules( $plan_id, $edit = true ) {

		$plan_id    = $plan_id instanceof WC_Memberships_Membership_Plan ? $plan_id->get_id() : $plan_id;
		$rules      = $this->get_rules( $edit ? array( 'plan_status' => 'any' ) : array() );
		$plan_rules = array();

		if ( is_numeric( $plan_id ) && $plan_id > 0 && ! empty( $rules ) ) {

			foreach ( $rules as $rule_id => $rule ) {

				if ( (int) $plan_id === $rule->get_membership_plan_id() ) {

					$plan_rules[ $rule_id ] = $rule;
				}
			}
		}

		return $plan_rules;
	}


	/**
	 * Returns an individual rule by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param string $rule_id rule ID (alphanumeric string)
	 * @return \WC_Memberships_Membership_Plan_Rule|null
	 */
	public function get_rule( $rule_id ) {

		$rules = $this->get_rules();

		return is_string( $rule_id ) && isset( $rules[ $rule_id ] ) ? $rules[ $rule_id ] : null;
	}


	/**
	 * Checks if a rule exists.
	 *
	 * @since 1.9.0
	 *
	 * @param string $rule_id alphanumeric identifier
	 * @return bool
	 */
	public function rule_exists( $rule_id ) {

		$rule = $this->get_rule( $rule_id );

		return $rule instanceof WC_Memberships_Membership_Plan_Rule;
	}


	/**
	 * Appends new rules to the existing ones.
	 *
	 * If a rule is found to be existing it will be updated.
	 *
	 * @see \WC_Memberships_Rules::set_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param array|string|\WC_Memberships_Membership_Plan_Rule[] $rules
	 */
	public function add_rules( $rules ) {

		if ( ! empty( $rules ) ) {
			$this->set_rules( array( 'add' => (array) $rules ) );
		}
	}


	/**
	 * Updates existing rules.
	 *
	 * @see \WC_Memberships_Rules::set_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param array|string|\WC_Memberships_Membership_Plan_Rule[] $rules
	 */
	public function update_rules( $rules ) {

		if ( ! empty( $rules ) ) {
			$this->set_rules( array( 'update' => (array) $rules ) );
		}
	}


	/**
	 * Deletes rules.
	 *
	 * If no arguments are passed to this method, all rules will be delete at once!
	 *
	 * @see \WC_Memberships_Rules::set_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param array|string|\WC_Memberships_Membership_Plan_Rule[] $rules
	 */
	public function delete_rules( $rules = null ) {

		if ( null === $rules ) {

			update_option( 'wc_memberships_rules', array() );

			$this->flush_rules();

		} elseif ( ! empty( $rules ) ) {

			$this->set_rules( array( 'delete' => (array) $rules ) );
		}
	}


	/**
	 * Saves, updates or deletes rules at once.
	 *
	 * This method runs every time rules are changed.
	 *
	 * @since 1.9.0
	 *
	 * @param array $rule_data associative array with instructions to handle new or existing rules
	 */
	public function set_rules( array $rule_data ) {

		$rules          = array();
		$existing_rules = $this->get_rules();
		$add_rules      = isset( $rule_data['add'] )    && is_array( $rule_data['add'] )    ? $rule_data['add']    : array();
		$update_rules   = isset( $rule_data['update'] ) && is_array( $rule_data['update'] ) ? $rule_data['update'] : array();
		$delete_rules   = isset( $rule_data['delete'] ) && is_array( $rule_data['delete'] ) ? $rule_data['delete'] : array();

		// add new rules
		foreach ( $add_rules as $add_rule ) {

			if ( is_array( $add_rule ) ) {

				$add_rule = new WC_Memberships_Membership_Plan_Rule( $add_rule );

				if ( $add_rule->is_new() ) {
					$add_rule->set_id();
				}
			}

			// check if a rule exists, and maybe move to the array of rules to update
			if ( $add_rule instanceof WC_Memberships_Membership_Plan_Rule ) {

				// it shouldn't happen that rules have no set rule type, plan no content type or content type name
				if ( null === $add_rule->get_target() || 0 === $add_rule->get_membership_plan_id() || ! $this->is_valid_rule_type( $add_rule->get_rule_type() ) ) {
					continue;
				}

				if ( $add_rule->has_id() && isset( $existing_rules[ $add_rule->get_id() ] ) ) {
					$update_rules[ $add_rule->get_id() ]   = $add_rule;
				} else {
					$existing_rules[ $add_rule->get_id() ] = $add_rule;
				}
			}
		}

		// update existing rules
		foreach ( $update_rules as $update_rule ) {

			if ( is_array( $update_rule ) ) {
				$update_rule = new WC_Memberships_Membership_Plan_Rule( $update_rule );
			}

			if ( $update_rule instanceof WC_Memberships_Membership_Plan_Rule && $update_rule->has_id() && isset( $existing_rules[ $update_rule->get_id() ] ) ) {
				$existing_rules[ $update_rule->get_id() ] = $update_rule;
			}
		}

		// delete rules
		foreach ( $delete_rules as $delete_rule ) {

			if ( $delete_rule instanceof WC_Memberships_Membership_Plan_Rule ) {
				$rule_id = $delete_rule->get_id();
			} elseif ( is_array( $delete_rule ) && isset( $delete_rule['id'] ) ) {
				$rule_id = $delete_rule['id'];
			} elseif ( is_string( $delete_rule ) ) {
				$rule_id = $delete_rule;
			}

			if ( ! empty( $rule_id ) && isset( $existing_rules[ $rule_id ] ) ) {
				unset( $existing_rules[ $rule_id ] );
			}
		}

		if ( ! empty( $add_rules ) || ! empty( $update_rules ) || ! empty( $delete_rules ) ) {

			// get rules in array format to be saved
			foreach ( $existing_rules as $existing_rule ) {
				$rules[] = $existing_rule->get_raw_data();
			}

			update_option( 'wc_memberships_rules', $rules );

			$this->flush_rules();
		}
	}


	/**
	 * Compacts rules for all plans.
	 *
	 * @since 1.9.0
	 */
	public function compact_rules() {

		$plans = array();
		$rules = $this->get_rules();

		foreach ( $rules as $rule ) {
			if ( $rule->has_membership_plan_id() ) {
				$plans[] = $rule->get_membership_plan_id();
			}
		}

		$plans   = array_unique( $plans );
		$invalid = array();

		foreach ( $plans as $plan_id ) {
			if ( $plan = wc_memberships_get_membership_plan( $plan_id ) ) {
				$plan->compact_rules();
			} else {
				$invalid[] = $plan_id;
			}
		}

		$delete_rules = array();

		foreach ( $invalid as $plan_id ) {
			foreach ( $rules as $rule ) {
				if ( (int) $plan_id === $rule->get_membership_plan_id() ) {
					$delete_rules[] = $rule->get_id();
				}
			}
		}

		$this->delete_rules( $delete_rules );
	}


	/**
	 * Returns content restriction rules for a post.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id WP_Post ID
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_post_content_restriction_rules( $post_id ) {
		return $this->get_rules( array(
			'rule_type'         => 'content_restriction',
			'content_type'      => 'post_type',
			'content_type_name' => get_post_type( $post_id ),
			'object_id'         => $post_id,
		) );
	}


	/**
	 * Returns content restriction rules for a taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_content_restriction_rules( $taxonomy ) {
		return $this->get_rules( array(
			'rule_type'         => 'content_restriction',
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
		) );
	}


	/**
	 * Returns content restriction rules for a taxonomy term.
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @param string|int $term_id term ID or slug
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_term_content_restriction_rules( $taxonomy, $term_id ) {
		return $this->get_rules( array(
			'rule_type'         => 'content_restriction',
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
			'object_id'         => $term_id,
		) );
	}


	/**
	 * Returns content restriction rules for a post type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Post type name
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_post_type_content_restriction_rules( $post_type ) {
		return $this->get_rules( array(
			'rule_type'         => 'content_restriction',
			'content_type'      => 'post_type',
			'content_type_name' => $post_type,
		) );
	}


	/**
	 * Returns product restriction rules.
	 *
	 * @see \WC_Memberships::get_rules()
	 *
	 * @since 1.9.0
	 *
	 * @param array $args optional arguments
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	private function get_products_restriction_rules( $args = array() ) {

		// if an object id is set, default to the product post_type
		if ( isset( $args['object_id'] ) ) {
			$args = wp_parse_args( $args, array(
				'content_type'      => 'post_type',
				'content_type_name' => 'product',
			) );
		}

		// force 'product' as the only valid post_type
		if ( isset( $args['content_type'] ) && 'post_type' === $args['content_type'] ) {
			$args['content_type_name'] = 'product';
		}

		$args['rule_type'] = 'product_restriction';

		return $this->get_rules( $args );
	}


	/**
	 * Returns product restriction rules for a product.
	 *
	 * @see \WC_Memberships::get_product_restriction_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id WC_Product ID
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_product_restriction_rules( $product_id ) {
		return $this->get_products_restriction_rules( array(
			'object_id' => (int) $product_id,
		) );
	}


	/**
	 * Returns product restriction rules for a taxonomy.
	 *
	 * @see \WC_Memberships::get_product_restriction_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_product_restriction_rules( $taxonomy ) {
		return $this->get_products_restriction_rules( array(
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
		) );
	}


	/**
	 * Returns product restriction rules for a taxonomy term.
	 *
	 * @see \WC_Memberships::get_product_restriction_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @param string|int $term_id term ID or slug
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_term_product_restriction_rules( $taxonomy, $term_id ) {
		return $this->get_products_restriction_rules( array(
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
			'object_id'         => $term_id,
		) );
	}


	/**
	 * Returns purchasing discount rules.
	 *
	 * @see \WC_Memberships::get_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param array $args associative array of arguments
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	private function get_purchasing_discount_rules( $args = array() ) {

		// if an object ID is set, default to the product post type
		if ( isset( $args['object_id'] ) ) {
			$args = wp_parse_args( $args, array(
				'content_type'      => 'post_type',
				'content_type_name' => 'product',
			) );
		}

		// force 'product' as the only valid post_type
		if ( isset( $args['content_type'] ) && 'post_type' === $args['content_type'] ) {
			$args['content_type_name'] = 'product';
		}

		$args['rule_type'] = 'purchasing_discount';

		return $this->get_rules( $args );
	}


	/**
	 * Returns purchasing discount rules for a product.
	 *
	 * @since 1.0.0
	 *
	 * @param int $product_id Product ID
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_product_purchasing_discount_rules( $product_id ) {
		return $this->get_purchasing_discount_rules( array(
			'object_id' => (int) $product_id,
		) );
	}


	/**
	 * Returns purchasing discount rules for a taxonomy.
	 *
	 * @see \WC_Memberships_Rules::get_purchasing_discount_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_purchasing_discount_rules( $taxonomy ) {
		return $this->get_purchasing_discount_rules( array(
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
		) );
	}


	/**
	 * Returns purchasing discount rules for a taxonomy term.
	 *
	 * @see \WC_Memberships_Rules::get_purchasing_discount_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param string $taxonomy taxonomy name
	 * @param string|int $term_id term ID or slug
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_taxonomy_term_purchasing_discount_rules( $taxonomy, $term_id ) {
		return $this->get_purchasing_discount_rules( array(
			'content_type'      => 'taxonomy',
			'content_type_name' => $taxonomy,
			'object_id'         => $term_id,
		) );
	}


	/**
	 * Returns a user's purchasing discount for a specific product.
	 *
	 * @see \WC_Memberships_Rules::get_purchasing_discount_rules()
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_User $user user ID
	 * @param int|\WC_Product $product product ID or object
	 * @return \WC_Memberships_Membership_Plan_Rule[]
	 */
	public function get_user_product_purchasing_discount_rules( $user, $product ) {

		$product_id          = $product instanceof WC_Product ? $product->get_id() : $product;
		$user_discount_rules = array();

		if ( is_numeric( $product_id ) ) {

			$all_discount_rules  = $this->get_product_purchasing_discount_rules( $product_id );

			if ( ! empty( $all_discount_rules ) ) {
				foreach ( $all_discount_rules as $rule ) {
					if ( $rule->is_active() && wc_memberships_is_user_active_member( $user, $rule->get_membership_plan_id() ) ) {
						$user_discount_rules[] = $rule;
					}
				}
			}
		}

		return $user_discount_rules;
	}


	/**
	 * Checks if a product has any member discount rules.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WC_Product $product product ID or object to check
	 * @return bool
	 */
	public function product_has_purchasing_discount_rules( $product ) {

		$product_id = $product instanceof WC_Product ? $product->get_id() : $product;

		if ( is_numeric( $product_id ) ) {

			$rules = $this->get_product_purchasing_discount_rules( $product_id );

			if ( ! empty( $rules ) ) {
				foreach ( $rules as $key => $discount ) {
					if ( $discount->is_inactive() ) {
						unset( $rules[ $key ] );
					}
				}
			}
		}

		return ! empty( $rules );
	}


	/**
	 * Handles deprecated methods.
	 *
	 * TODO remove these in a future major update of the plugin, 3 minor x.Y.z versions from deprecation {FN 2017-06-21}
	 *
	 * @since 1.9.0
	 *
	 * @param string $method called method not found
	 * @param array $args possible arguments passed to method invoked
	 * @return mixed|null
	 */
	public function __call( $method, $args ) {

		switch ( $method ) {

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_content_restriction_rules' :

				_deprecated_function( 'WC_Memberships_Rules::get_content_restriction_rules()', '1.9.0', 'WC_Memberships_Rules::get_rules()' );

				return isset( $args[0] ) ? $this->get_rules( $args[0] ) : $this->get_rules();

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_the_product_restriction_rules' :

				_deprecated_function( 'WC_Memberships_Rules::get_the_product_restriction_rules()', '1.9.0', 'WC_Memberships_Rules::get_product_restriction_rules()' );

				return $this->get_product_restriction_rules( isset( $args[0] ) ? $args[0] : $args );

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_public_posts' :

				_deprecated_function( 'WC_Memberships_Rules::get_public_posts()', '1.9.0', 'get_posts()' );

				return get_posts( array(
					'post_type'      => get_post_types(),
					'post_status'    => 'any',
					'meta_key'       => '_wc_memberships_force_public',
					'meta_value'     => 'yes',
				) );

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_public_products' :

				_deprecated_function( 'WC_Memberships_Rules::get_public_products()', '1.9.0', 'get_posts()' );

				return get_posts( array(
					'post_type'      => 'product',
					'post_status'    => 'any',
					'meta_key'       => '_wc_memberships_force_public',
					'meta_value'     => 'yes',
				) );

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_user_content_restriction_rules' :

				_deprecated_function( 'WC_Memberships_Rules::get_user_content_restriction_rules()', '1.9.0', 'WC_Memberships_Rules::get_rules()' );

				$method_args              = isset( $args[1] ) ? $args[1] : array();
				$method_args['rule_type'] = 'content_restriction';
				$all_rules                = $this->get_rules( $method_args );
				$user_rules               = array();

				if ( ! empty( $all_rules ) ) {
					foreach ( $all_rules as $rule ) {
						if ( wc_memberships_is_user_active_or_delayed_member( $args[0], $rule->get_membership_plan_id() ) ) {
							$user_rules[] = $rule;
						}
					}
				}

				return $user_rules;

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'get_user_product_restriction_rules' :

				_deprecated_function( 'WC_Memberships_Rules::get_user_product_restriction_rules()', '1.9.0', 'WC_Memberships_Rules::get_rules()' );

				$method_args = isset( $args[1] ) ? $args[1] : array();
				$access_type = isset( $args[2] ) ? $args[2] : null;
				$all_rules   = $this->get_products_restriction_rules( $method_args );
				$user_rules  = array();

				if ( ! empty( $all_rules ) ) {

					foreach ( $all_rules as $rule ) {

						$matches_access_type = true;

						if ( 'view' === $access_type ) {
							$matches_access_type = in_array( $rule->get_access_type(), array( 'view', 'purchase' ), true );
						} elseif ( 'purchase' === $args ) {
							$matches_access_type = 'purchase' === $rule->get_access_type();
						}

						if ( $matches_access_type && wc_memberships_is_user_active_or_delayed_member( $args[0], $rule->get_membership_plan_id() ) ) {
							$user_rules[] = $rule;
						}
					}
				}

				return $user_rules;

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'product_has_member_discount' :

				_deprecated_function( 'WC_Memberships_Rules::product_has_member_discount()', '1.9.0', 'WC_Memberships_Rules::product_has_purchasing_discount_rules()' );

				return $this->product_has_purchasing_discount_rules( isset( $args[0] ) ? $args[0] : $args );

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'user_has_content_access_from_rules' :

				_deprecated_function( 'WC_Memberships_Rules::user_has_content_access_from_rules()', '1.9.0' );

				list( $user_id, $rules ) = $args;
				$object_id = ! empty( $args[2] ) ? $args[2] : null;

				$has_access = true;

				if ( empty( $user_id ) ) {

					$has_access = false;

				} elseif ( ! empty( $rules ) ) {

					$has_access = false;

					/** @type \WC_Memberships_Membership_Plan_Rule[] $rules */
					foreach ( $rules as $rule ) {
						if ( empty( $object_id ) && $rule->has_object_ids() ) {
							continue;
						} elseif ( wc_memberships_is_user_active_or_delayed_member( $user_id, $rule->get_membership_plan_id() ) ) {
							$has_access = true;
							break;
						}
					}
				}

				return $has_access;

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'user_has_product_member_discount' :

				_deprecated_function( 'WC_Memberships_Rules::user_has_product_member_discount()', '1.9.0' );

				list( $user_id, $product_id ) = $args;

				$rules = $this->get_user_product_purchasing_discount_rules( $user_id, $product_id );

				if ( ! empty( $rules ) ) {
					foreach ( $rules as $key => $rule ) {
						if ( ! $rule->is_active() || ! wc_memberships_is_user_active_member( $user_id, $rule->get_membership_plan_id() ) ) {
							unset( $rules[ $key ] );
						}
					}
				}

				return ! empty( $rules );

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'user_has_product_view_access_from_rules' :

				_deprecated_function( 'WC_Memberships_Rules::user_has_product_view_access_from_rules()', '1.9.0' );

				list( $user_id, $rules ) = $args;
				$object_id = ! empty( $args[2] ) ? $args[2] : null;

				$has_access = true;

				if ( empty( $user_id ) ) {

					$has_access = false;

				} elseif ( ! empty( $rules ) ) {

					/** @type \WC_Memberships_Membership_Plan_Rule[] $rules */
					foreach ( $rules as $rule ) {
						if ( ! $object_id && $rule->has_object_ids() ) {
							continue;
						} elseif ( 'view' === $rule->get_access_type() ) {
							$has_access = false;
							break;
						}
					}

					if ( $user_id && ! $has_access ) {
						foreach ( $rules as $rule ) {
							if ( ! $object_id && $rule->has_object_ids() ) {
								continue;
							} elseif ( in_array( $rule->get_access_type(), array( 'view', 'purchase' ), true ) && wc_memberships_is_user_active_or_delayed_member( $user_id, $rule->get_membership_plan_id() ) ) {
								$has_access = true;
								break;
							}
						}
					}
				}

				return $has_access;

			/* @deprecated since 1.9.0 - remove by version 1.12.0 */
			case 'user_has_product_purchase_access_from_rules' :

				_deprecated_function( 'WC_Memberships_Rules::user_has_product_purchase_access_from_rules()', '1.9.0' );

				list( $user_id, $rules ) = $args;

				$has_access = true;

				if ( empty( $user_id ) ) {

					$has_access = false;

				} elseif ( ! empty( $rules ) ) {

					/** @type \WC_Memberships_Membership_Plan_Rule[] $rules */
					foreach ( $rules as $rule ) {
						if ( 'purchase' === $rule->get_access_type() ) {
							$has_access = false;
							break;
						}
					}

					if ( ! $has_access ) {
						foreach ( $rules as $rule ) {
							if ( 'purchase' === $rule->get_access_type() && wc_memberships_is_user_active_or_delayed_member( $user_id, $rule->get_membership_plan_id() ) ) {
								$has_access = true;
								break;
							}
						}
					}
				}

				return $has_access;
		}

		// you're probably doing it wrong
		trigger_error( 'Call to undefined method ' . __CLASS__ . '::' . $method . '()', E_USER_ERROR );
		return null;
	}


}
