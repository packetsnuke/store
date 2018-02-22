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
 * @package   WC-Memberships/Frontend/Checkout
 * @author    SkyVerge
 * @category  Frontend
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Handler responsible for restricting access to generic content such as posts.
 *
 * @since 1.9.0
 */
class WC_Memberships_Posts_Restrictions {


	/** @var int[] memoization of post IDs that have been processed for restriction */
	private $content_restricted = array();

	/** @var int[] memoized array of sticky post IDs that perhaps need to be restricted */
	private $restricted_sticky_posts = array();

	/** @var array memoized array of restricted posts by term IDs by member */
	private $restricted_comments_by_post_id = array();


	/**
	 * Handles generic content restrictions.
	 *
	 * The constructor normally runs during `wp` action time.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {

		// decide whether attempting to access restricted content has to be redirected
		add_action( 'wp', array( $this, 'handle_restriction_modes' ) );

		// restrict the post by filtering the post object and replacing the content with a message and maybe excerpt
		add_action( 'the_post', array( $this, 'restrict_post' ), 0 );

		// adjust queries to account for restricted content
		add_filter( 'posts_clauses',  array( $this, 'handle_posts_clauses' ), 999, 2 );
		add_filter( 'get_terms_args', array( $this, 'handle_get_terms_args' ), 999, 2 );
		add_filter( 'terms_clauses',  array( $this, 'handle_terms_clauses' ), 999 );

		// handle post and page queries to exclude restricted content to non-members
		add_filter( 'pre_get_posts',       array( $this, 'exclude_restricted_posts' ), 999 );
		add_filter( 'option_sticky_posts', array( $this, 'exclude_restricted_sticky_posts' ), 999 );
		add_filter( 'get_pages',           array( $this, 'exclude_restricted_pages' ), 999 );

		// handle comment queries to hide comments to comment that is restricted to non-members
		add_filter( 'the_posts',        array( $this, 'exclude_restricted_content_comments' ), 999, 2 );
		add_filter( 'pre_get_comments', array( $this, 'exclude_restricted_comments' ), 999 );

		// redirect to restricted content or product upon login
		add_filter( 'woocommerce_login_redirect', array( $this, 'redirect_to_member_content_upon_login' ), 40 );
	}


	/**
	 * Handles restriction modes.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 */
	public function handle_restriction_modes() {
		global $post;

		if ( $restrictions = wc_memberships()->get_restrictions_instance() ) {

			switch ( $restrictions->get_restriction_mode() ) {

				case 'hide_content' :
					$this->hide_restricted_content_comments();
				break;

				case 'redirect' :

					$redirect_page_id = $restrictions->get_restricted_content_redirect_page_id();

					// do not process content redirection for the page to redirect to in any case
					if ( ( ! $redirect_page_id || ! $post ) || ( $post && $redirect_page_id !== $post->ID ) ) {
						$this->redirect_restricted_content();
					}

				break;
			}
		}
	}


	/**
	 * Hides restricted content comments (including product reviews).
	 *
	 * @since 1.9.0
	 */
	private function hide_restricted_content_comments() {
		global $post, $wp_query;

		if ( $post ) {

			if ( in_array( $post->post_type, array( 'product', 'product_variation' ), true ) ) {
				$restricted = wc_memberships_is_product_viewing_restricted() && ! current_user_can( 'wc_memberships_view_restricted_product',      $post->ID );
			} else {
				$restricted = wc_memberships_is_post_content_restricted()    && ! current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID );
			}

			if ( $restricted ) {
				$wp_query->comment_count   = 0;
				$wp_query->current_comment = 999999;
			}
		}
	}


	/**
	 * Redirects restricted content based on content/product restriction rules.
	 *
	 * @see \WC_Memberships_Posts_Restrictions::redirect_to_member_content_upon_login()
	 *
	 * @since 1.9.0
	 */
	private function redirect_restricted_content() {
		global $post;

		if ( $post ) {

			$restricted = false;

			if ( ! is_shop() && in_array( $post->post_type, array( 'product', 'product_variation' ), true ) ) {
				$restricted = wc_memberships_is_product_viewing_restricted() && ! current_user_can( 'wc_memberships_view_restricted_product',      $post->ID );
			} elseif ( is_singular() ) {
				$restricted = wc_memberships_is_post_content_restricted()    && ! current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID );
			}

			if ( $restricted ) {

				if (      'product' === get_post_type( $post )
				     && ! is_singular( 'product' )
				     &&   is_tax( 'product_cat' )
				     && ! wc_memberships()->get_restrictions_instance()->hiding_restricted_products() ) {

					// bail out if we are on a product category page but the post was not hidden from showing:
					// otherwise this would redirect the whole category page!
					return;
				}

				wp_redirect( wc_memberships()->get_restrictions_instance()->get_restricted_content_redirect_url( $post->ID ) );
				exit;
			}
		}
	}


	/**
	 * Redirects user to restricted content after successful login.
	 *
	 * @see \WC_Memberships_Posts_Restrictions::redirect_restricted_content()
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param string $redirect_to URL to redirect to
	 * @return string
	 */
	public function redirect_to_member_content_upon_login( $redirect_to ) {

		$content = null;

		if ( isset( $_GET['wcm_redirect_to'], $_GET['wcm_redirect_id'] ) ) {

			if ( in_array( $_GET['wcm_redirect_to'], array( 'post', 'page' ), true ) ) {
				$content = get_post( (int) $_GET['wcm_redirect_id'] );
			} elseif ( taxonomy_exists( $_GET['wcm_redirect_to'] ) ) {
				$content = get_term_link( (int) $_GET['wcm_redirect_id'], $_GET['wcm_redirect_to'] );
			}
		}

		if ( ! empty( $content ) && ( $permalink = get_permalink( $content ) ) ) {
			$redirect_to = is_string( $permalink ) ? $permalink : $redirect_to;
		}

		return $redirect_to;
	}


	/**
	 * Excludes restricted post types, taxonomies & terms by altering posts query clauses.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param array $pieces SQL clause pieces
	 * @param \WP_Query $wp_query instance of WP_Query
	 * @return array modified pieces
	 */
	public function handle_posts_clauses( $pieces, WP_Query $wp_query ) {

		// - bail out if:
		//  a) user is an admin / can access all restricted content
		//  b) query is for user memberships or membership plans post types
		//  c) the following applies:
		//      1. we are on products query;
		//      2. restriction mode is not "hide" completely;
		//      3. we are not hiding restricted products from archive and search
		if (    current_user_can( 'wc_memberships_access_all_restricted_content' )
		     || in_array( $wp_query->get( 'post_type' ), array( 'wc_user_membership', 'wc_membership_plan' ), true )
		     || ( ! wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) && ! ( wc_memberships()->get_restrictions_instance()->hiding_restricted_products() && 'product_query' === $wp_query->get('wc_query' ) ) ) ) {

			return $pieces;
		}

		$conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();

		// some post types are restricted: exclude them from the query
		if ( ! empty( $conditions['restricted']['post_types'] ) && is_array( $conditions['restricted']['post_types'] ) ) {
			$pieces['where'] .= $this->exclude_restricted_posts_types( $conditions['restricted']['post_types'] );
		}

		// some taxonomies are restricted: exclude them from query
		if ( ! empty( $conditions['restricted']['taxonomies'] ) && is_array( $conditions['restricted']['taxonomies'] ) ) {
			$pieces['where'] .= $this->exclude_restricted_taxonomies( $conditions['restricted']['taxonomies'] );
		}

		// exclude taxonomy terms
		if ( ! empty( $conditions['restricted']['terms'] ) && is_array( $conditions['restricted']['terms'] ) ) {
			$pieces['where'] .= $this->exclude_restricted_terms( $conditions['restricted']['terms'] );
		}

		return $pieces;
	}


	/**
	 * Excludes restricted post types from the query.
	 *
	 * @since 1.9.0
	 *
	 * @param string[] $restricted_post_types conditions
	 * @return string SQL clause
	 */
	private function exclude_restricted_posts_types( array $restricted_post_types ) {
		global $wpdb;

		$post_type_taxonomies = $this->get_taxonomies_for_post_types( $restricted_post_types );
		$granted_posts        = wc_memberships()->get_restrictions_instance()->get_user_granted_posts( $restricted_post_types );
		$granted_terms        = wc_memberships()->get_restrictions_instance()->get_user_granted_terms( $post_type_taxonomies );
		$granted_taxonomies   = array_intersect( $restricted_post_types, $post_type_taxonomies );

		// no special handling: simply restrict access to all the restricted post types
		if ( empty( $granted_posts ) && empty( $granted_terms ) && empty( $granted_taxonomies ) ) {

			$post_types = implode( ', ', array_fill( 0, count( $restricted_post_types ), '%s' ) );
			$clause     = $wpdb->prepare( " AND $wpdb->posts.post_type NOT IN ($post_types) ", $restricted_post_types );

		// while general access to these post types is restricted,
		// there are extra rules that grant the user access to some taxonomies, terms or posts in one or more restricted post types
		} else {

			$post_types = implode( ', ', array_fill( 0, count( $restricted_post_types ), '%s' ) );

			// Prepare main subquery, which gets all post IDs with the restricted post types.
			// The main idea behind the following queries is as follows:
			// 1. Instead of excluding post types, use a subquery to get IDs of all posts of the restricted post types and exclude them from the results.
			// 2. If user has access to specific posts, taxonomies or terms that would be restricted by the post type, use subqueries to exclude posts that user should have access to from the exclusion list.
			$subquery  = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type IN ($post_types)", $restricted_post_types );
			// allow access to whole taxonomies
			$subquery .= $this->get_taxonomy_access_where_clause( $granted_taxonomies );
			// allow access to specific terms
			$subquery .= $this->get_term_access_where_clause( $granted_terms );
			// allow access to specific posts
			$subquery .= $this->get_post_access_where_clause( $granted_posts );

			// we are checking that post ID is not one of the restricted post IDs:
			$clause = " AND $wpdb->posts.ID NOT IN ($subquery) ";
		}

		return $clause;
	}


	/**
	 * Excludes restricted taxonomies from the query.
	 *
	 * @since 1.9.0
	 *
	 * @param string[] $restricted_taxonomies conditions
	 * @return string SQL clause
	 */
	private function exclude_restricted_taxonomies( array $restricted_taxonomies ) {
		global $wpdb;

		$clause              = '';
		$taxonomy_post_types = array();

		foreach ( $restricted_taxonomies as $taxonomy ) {
			if ( $the_taxonomy = get_taxonomy( $taxonomy ) ) {
				$taxonomy_post_types[ $taxonomy ] = $this->get_post_types_for_taxonomies( (array) $taxonomy );
			}
		}

		if ( ! empty( $taxonomy_post_types ) ) {

			// Use case statement to check if the post type for the object is registered for the restricted taxonomy.
			// If it is not, then don't restrict.
			// This fixes issues when a taxonomy was once registered for a post type but is not anymore, but restriction rules still apply to that post type via term relationships in database.
			$case       = '';
			// main taxonomy query is always the same, regardless if user has access to specific terms or posts under these taxonomies
			$taxonomies = implode( ', ', array_fill( 0, count( $restricted_taxonomies ), '%s' ) );

			foreach ( $taxonomy_post_types as $tax => $post_types ) {

				$args                   = array_merge( array( $tax ), $post_types );
				$post_types_placeholder = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

				$case .= $wpdb->prepare( " WHEN $wpdb->term_taxonomy.taxonomy = %s THEN $wpdb->posts.post_type IN ( $post_types_placeholder )", $args );
			}

			$subquery = $wpdb->prepare( "
				SELECT object_id FROM $wpdb->term_relationships
				LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
				LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
				WHERE CASE $case END
				AND $wpdb->term_taxonomy.taxonomy IN ($taxonomies)
			", $restricted_taxonomies );

			$granted_posts = wc_memberships()->get_restrictions_instance()->get_user_granted_posts( $this->get_post_types_for_taxonomies( $restricted_taxonomies ) );
			$granted_terms = wc_memberships()->get_restrictions_instance()->get_user_granted_terms( $restricted_taxonomies );

			// It looks like while general access to these taxonomies is restricted,
			// there are some rules that grant the user access to some terms or posts in one or more restricted taxonomies.
			if ( ! empty( $granted_terms ) || ! empty( $granted_posts ) ) {
				// allow access to specific terms
				$subquery .= $this->get_term_access_where_clause( $granted_terms );
				// allow access to specific posts
				$subquery .= $this->get_post_access_where_clause( $granted_posts );
			}

			$clause = " AND $wpdb->posts.ID NOT IN ($subquery) ";
		}

		return $clause;
	}


	/**
	 * Excludes restricted terms from the query.
	 *
	 * @since 1.9.0
	 *
	 * @param string[]|int[] $restricted_terms conditions
	 * @return string SQL clause
	 */
	private function exclude_restricted_terms( array $restricted_terms ) {
		global $wpdb;

		$clause     = '';
		$term_ids   = array();
		$taxonomies = array_keys( $restricted_terms );

		foreach ( $restricted_terms as $taxonomy => $terms ) {
			$term_ids = array_merge( $term_ids, $terms );
		}

		if ( ! empty( $term_ids ) ) {

			$taxonomy_post_types = array();

			foreach ( $taxonomies as $taxonomy ) {
				if ( get_taxonomy( $taxonomy ) ) {
					$taxonomy_post_types[ $taxonomy ] = $this->get_post_types_for_taxonomies( (array) $taxonomy );
				}
			}

			if ( ! empty ( $taxonomy_post_types ) ) {

				// main term query is always the same, regardless if user has access
				// to specific posts under with these terms
				$taxonomy_terms = implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) );

				// Use case statement to check if the post type for the object is registered for the restricted taxonomy.
				// If it is not, then don't restrict.
				// This fixes issues when a taxonomy was once registered for a post type but is not anymore, but restriction rules still apply to that post type via term relationships in database.
				$case = '';

				foreach ( $taxonomy_post_types as $tax => $post_types ) {

					$args                   = array_merge( array( $tax ), $post_types );
					$post_types_placeholder = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

					$case .= $wpdb->prepare( " WHEN $wpdb->term_taxonomy.taxonomy = %s THEN $wpdb->posts.post_type IN ( $post_types_placeholder )", $args );
				}

				$subquery = $wpdb->prepare( "
					SELECT object_id FROM $wpdb->term_relationships
					LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
					LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
					WHERE CASE $case END
					AND $wpdb->term_relationships.term_taxonomy_id IN ($taxonomy_terms)
				", $term_ids );

				$all_taxonomy_post_types = $this->get_post_types_for_taxonomies( $taxonomies );
				$granted_posts           = wc_memberships()->get_restrictions_instance()->get_user_granted_posts( $all_taxonomy_post_types );

				// It looks like while general access to these terms is restricted,
				// there are some rules that grant the user access to some posts in one or more restricted terms.
				if ( ! empty( $granted_posts ) ) {
					$subquery .= $this->get_post_access_where_clause( $granted_posts );
				}

				$clause = " AND $wpdb->posts.ID NOT IN ($subquery) ";
			}
		}

		return $clause;
	}


	/**
	 * Hides restricted posts/products based on content/product restriction rules.
	 *
	 * This method works by modifying the $query object directly.
	 * Since WP_Query does not support excluding whole post types or taxonomies, we need to use custom SQL clauses for them.
	 * Also, tax_query is not respected on is_singular(), so we need to use custom SQL for specific term restrictions as well.
	 * @see \WC_Memberships_Restrictions::get_user_content_access_conditions()
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Query $wp_query instance of WP_Query
	 */
	public function exclude_restricted_posts( WP_Query $wp_query ) {

		if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) ) {

			// restriction mode is set to "hide completely":
		    if ( wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) ) {

				$restricted_posts = wc_memberships()->get_restrictions_instance()->get_user_restricted_posts();

				// exclude restricted posts and products from queries
				if ( ! empty( $restricted_posts ) ) {
					$wp_query->set( 'post__not_in', array_unique( array_merge(
						$wp_query->get( 'post__not_in' ),
						$restricted_posts
					) ) );
				}

			// products should be hidden in the catalog and search content if related option is set:
			} elseif ( 'product_query' === $wp_query->get( 'wc_query' ) && wc_memberships()->get_restrictions_instance()->hiding_restricted_products() ) {

				$conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();

				if ( isset( $conditions['restricted']['posts']['product'] ) ) {
					$wp_query->set( 'post__not_in', array_unique( array_merge(
						$wp_query->get( 'post__not_in' ),
						$conditions['restricted']['posts']['product']
					) ) );
				}
			}
		}
	}


	/**
	 * Removes sticky posts from ever showing up when using the "hide completely" restriction mode and the user doesn't have access.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param int[] $sticky_posts array of sticky post IDs
	 * @return int[]
	 */
	public function exclude_restricted_sticky_posts( $sticky_posts ) {

		if ( ! empty( $sticky_posts ) ) {

			if ( ! empty( $this->restricted_sticky_posts ) && is_array( $this->restricted_sticky_posts ) ) {

				$sticky_posts = $this->restricted_sticky_posts;

			} elseif ( wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) ) {

				$restricted_sticky_posts = array();

				// avoid infinite filter loops as the capability check might incur checking the sticky posts again
				remove_filter( 'option_sticky_posts', array( $this, 'exclude_restricted_sticky_posts' ), 999 );

				foreach ( $sticky_posts as $sticky_post_id ) {
					if ( is_numeric( $sticky_post_id ) && ! current_user_can( 'wc_memberships_view_restricted_post_content', $sticky_post_id ) ) {
						$restricted_sticky_posts[] = $sticky_post_id;
					}
				}

				if ( ! empty( $restricted_sticky_posts ) ) {
					$sticky_posts = array_diff( $sticky_posts, $restricted_sticky_posts );
				}

				// reinstate the current filter
				add_filter( 'option_sticky_posts', array( $this, 'exclude_restricted_sticky_posts' ), 999 );

				$this->restricted_sticky_posts = $sticky_posts;
			}
		}

		return $sticky_posts;
	}


	/**
	 * Excludes restricted pages from `get_pages()` calls.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post[] $pages indexed array of page objects
	 * @return \WP_Post[]
	 */
	public function exclude_restricted_pages( $pages ) {

		// sanity check: if restriction mode is not to "hide completely", return all pages
		if ( wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) ) {

			foreach ( $pages as $index => $page ) {
				if (    ! current_user_can( 'wc_memberships_view_restricted_post_content', $page->ID )
				     && ! current_user_can( 'wc_memberships_view_delayed_post_content',    $page->ID ) ) {
					unset( $pages[ $index ] );
				}
			}

			$pages = array_values( $pages );
		}

		return $pages;
	}


	/**
	 * Excludes restricted taxonomies by filtering `terms_clauses`.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param array $pieces terms query SQL clauses (associative array)
	 * @return array modified clauses
	 */
	public function handle_terms_clauses( $pieces ) {
		global $wpdb;

		// sanity check: if restriction mode is not "hide all content", return all posts
		if (    ! current_user_can( 'wc_memberships_access_all_restricted_content' )
		     &&   wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) ) {

			$conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();

			if ( ! empty( $conditions['restricted']['taxonomies'] ) ) {

				$restricted_taxonomies = $conditions['restricted']['taxonomies'];
				$granted_terms         = wc_memberships()->get_restrictions_instance()->get_user_granted_terms( $restricted_taxonomies );
				// main taxonomy query is always the same, regardless if user has access to specific terms under these taxonomies
				$taxonomies            = implode( ', ', array_fill( 0, count( $restricted_taxonomies ), '%s' ) );
				$subquery              = $wpdb->prepare("
					SELECT sub_t.term_id FROM $wpdb->terms AS sub_t
					INNER JOIN $wpdb->term_taxonomy AS sub_tt ON sub_t.term_id = sub_tt.term_id
					WHERE sub_tt.taxonomy IN ($taxonomies)
				", $restricted_taxonomies );

				// it looks like while general access to these taxonomies is restricted, there are some rules that grant the user access to some terms or posts in one or more restricted taxonomies
				if ( ! empty( $granted_terms ) ) {
					// allow access to specific terms
					$subquery .= $this->get_term_access_where_clause( $granted_terms, 'taxonomies' );
				}

				$pieces['where'] .= " AND t.term_id NOT IN ($subquery) ";
			}
		}

		return $pieces;
	}


	/**
	 * Adjusts `get_terms` arguments, exclude restricted terms.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param array $args query arguments
	 * @param string|array $taxonomies the taxonomies for the queried terms
	 * @return array
	 */
	public function handle_get_terms_args( $args, $taxonomies ) {

		// sanity check: if restriction mode is not to "hide all content", return all posts
		if (    ! current_user_can( 'wc_memberships_access_all_restricted_content' )
		     &&   wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide' ) ) {

			$conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();
			$conditions = isset( $conditions['restricted']['terms'] ) && is_array( $conditions['restricted']['terms'] ) ? $conditions['restricted']['terms'] : array();

			if ( ! empty( $conditions ) && array_intersect( array_keys( $conditions ), $taxonomies ) ) {

				$args['exclude'] = $args['exclude'] ? wp_parse_id_list( $args['exclude'] ) : array();

				foreach ( $conditions as $tax => $terms ) {
					$args['exclude'] = array_unique( array_merge( $terms, $args['exclude'] ) );
				}
			}
		}

		return $args;
	}


	/**
	 * Handles exclude taxonomies WHERE SQL clause.
	 *
	 * @since 1.9.0
	 *
	 * @param array $taxonomies array of taxonomies
	 * @return string SQL clause
	 */
	private function get_taxonomy_access_where_clause( $taxonomies ) {
		global $wpdb;

		if ( ! empty( $taxonomies ) ) {

			$term_taxonomies = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );
			$subquery        = $wpdb->prepare( "
				SELECT object_id FROM $wpdb->term_relationships
				LEFT JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
				WHERE $wpdb->term_taxonomy.taxonomy IN ($term_taxonomies)
			", $taxonomies );

			$clause          = " AND $wpdb->posts.ID NOT IN ($subquery) ";

		} else {

			$clause          = '';
		}

		return $clause;
	}


	/**
	 * Handles exclude term IDs WHERE SQL clause.
	 *
	 * @since 1.9.0
	 *
	 * @param int[] $term_ids array of term IDs
	 * @param string $query_type optional, either 'posts' (default) or 'taxonomies'
	 * @return string SQL clause
	 */
	private function get_term_access_where_clause( $term_ids, $query_type = 'posts' ) {
		global $wpdb;

		$clause = '';

		if ( ! empty( $term_ids ) ) {

			$placeholder = implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) );

			if ( 'posts' === $query_type ) {

				$subquery = $wpdb->prepare( "
					SELECT object_id FROM $wpdb->term_relationships
					WHERE term_taxonomy_id IN ($placeholder)
				", $term_ids );

				$clause   = " AND $wpdb->posts.ID NOT IN ( " . $subquery . " ) ";

			} elseif ( 'taxonomies' === $query_type ) {

				$clause = $wpdb->prepare( " AND sub_t.term_id NOT IN ($placeholder) ", $term_ids );
			}
		}

		return $clause;
	}


	/**
	 * Handles exclude post IDs WHERE SQL clause.
	 *
	 * @since 1.9.0
	 *
	 * @param int[] $post_ids Array of post IDs
	 * @return string SQL clause
	 */
	private function get_post_access_where_clause( $post_ids ) {
		global $wpdb;

		if ( ! empty( $post_ids ) ) {
			$placeholder = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );
			$clause      = $wpdb->prepare( " AND ID NOT IN ($placeholder)", $post_ids );
		} else {
			$clause      = '';
		}

		return $clause;
	}


	/**
	 * Helper method that returns taxonomies that apply to provided post types.
	 *
	 * @since 1.9.0
	 *
	 * @param string[] $post_types array of post types
	 * @return string[] array with taxonomy names
	 */
	private function get_taxonomies_for_post_types( $post_types ) {

		$taxonomies = array();

		foreach ( $post_types as $post_type ) {
			$taxonomies = array_merge( $taxonomies, get_object_taxonomies( $post_type ) );
		}

		return array_unique( $taxonomies );
	}


	/**
	 * Helper method that returns post types that the provided taxonomies are registered for.
	 *
	 * @since 1.9.0
	 *
	 * @param string[] $taxonomies array of taxonomy names
	 * @return string[] array with post types
	 */
	private function get_post_types_for_taxonomies( $taxonomies ) {

		$post_types = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( $the_taxonomy = get_taxonomy( $taxonomy ) ) {
				foreach ( $the_taxonomy->object_type as $object_type ) {
					$post_types[] = $object_type;
				}
			}
		}

		return ! empty( $post_types ) ? array_unique( $post_types ) : array();
	}


	/**
	 * Restricts a post based on content restriction rules.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $post the post object, passed by reference
	 */
	public function restrict_post( $post ) {

		if (    ! in_array( $post->ID, $this->content_restricted, false )
		     &&   wc_memberships_is_post_content_restricted( $post->ID ) ) {

			$message_code = null;

			if ( ! current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID ) ) {
				$message_code = 'restricted';
			} elseif ( ! current_user_can( 'wc_memberships_view_delayed_post_content', $post->ID ) ) {
				$message_code = 'delayed';
			}

			if ( null !== $message_code ) {

				$args = array(
					'post'         => $post,
					'message_type' => $message_code,
				);

				if ( 'delayed' === $message_code ) {
					$args['access_time'] = wc_memberships()->get_capabilities_instance()->get_user_access_start_time_for_post( get_current_user_id(), $post->ID );
				}

				$message_code = WC_Memberships_User_Messages::get_message_code_shorthand_by_post_type( $post, $args );
				$content      = WC_Memberships_User_Messages::get_message_html( $message_code, $args );

				$this->restrict_post_content( $post, $content );
				$this->restrict_post_comments( $post );
			}
		}

		// flag post processed for restrictions
		$this->content_restricted[] = (int) $post->ID;
	}


	/**
	 * Restricts post content.
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $post the post object, passed by reference
	 * @param string $restricted_content the new content HTML
	 */
	private function restrict_post_content( WP_Post $post, $restricted_content ) {
		global $page, $pages, $multipages, $numpages;

		// update the post object passed by reference
		$post->post_content = $restricted_content;
		$post->post_excerpt = $restricted_content;

		/* @see \WP_Query::setup_postdata() for globals being updated here*/
		$page       = 1;
		$pages      = array( $restricted_content );
		$multipages = 0;
		$numpages   = 1;
	}


	/**
	 * Closes comments when post content is restricted.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post $post the post object, passed by reference
	 */
	private function restrict_post_comments( WP_Post $post ) {

		if ( $post->comment_status !== 'closed' ) {

			if ( in_array( $post->post_type, array( 'product', 'product_variation' ), true ) ) {
				$comments_open = current_user_can( 'wc_memberships_view_restricted_product',      $post->ID );
			} else {
				$comments_open = current_user_can( 'wc_memberships_view_restricted_post_content', $post->ID );
			}

			if ( ! $comments_open ) {
				$post->comment_status = 'closed';
				$post->comment_count  = 0;
			}
		}
	}


	/**
	 * Excludes restricted comments from comment feed.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param \WP_Post[] $posts array of posts
	 * @param \WP_Query $query instance of query
	 * @return \WP_Post[]
	 */
	public function exclude_restricted_content_comments( $posts, WP_Query $query ) {

		if ( ! empty( $query->comment_count ) && is_comment_feed() ) {

			foreach ( $query->comments as $key => $comment ) {

				$post_id = (int) $comment->comment_post_ID;

				if ( in_array( get_post_type( $post_id ), array( 'product', 'product_variation' ), true ) ) {
					// products
					$can_view = current_user_can( 'wc_memberships_view_restricted_product',      $post_id );
				} else {
					// posts
					$can_view = current_user_can( 'wc_memberships_view_restricted_post_content', $post_id );
				}

				// if not, exclude this comment from the feed
				if ( ! $can_view ) {
					unset( $query->comments[ $key ] );
				}
			}

			// re-index and re-count comments
			$query->comments      = array_values( $query->comments );
			$query->comment_count = count( $query->comments );
		}

		return $posts;
	}


	/**
	 * Filters the comment query to exclude posts the user has not access to.
	 *
	 * @internal
	 *
	 * @since 1.9.6
	 *
	 * @param \WP_Comment_Query $comment_query the comment query
	 */
	public function exclude_restricted_comments( WP_Comment_Query $comment_query ) {

		if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) && ( $restrictions = wc_memberships()->get_restrictions_instance() ) ) {

			$member_id = get_current_user_id();

			if ( isset( $this->restricted_comments_by_post_id[ $member_id ] ) ) {

				$post__not_in = $this->restricted_comments_by_post_id[ $member_id ];

			} else {

				$post__not_in         = isset( $comment_query->query_vars['post__not_in'] ) && is_array( $comment_query->query_vars['post__not_in'] ) ? array_filter( $comment_query->query_vars['post__not_in'] ) : array();
				$original_post_not_in = $post__not_in; // used later to make sure posts marked for exclusion are not removed from this array
				$restricted_posts     = $restrictions->get_user_restricted_posts();

				// exclude restricted posts from the query
				if ( ! empty( $restricted_posts ) ) {
					$post__not_in = array_merge( $restricted_posts, (array) $post__not_in );
				}

				// exclude all posts from restricted post type collections
				foreach ( get_post_types() as $post_type ) {

					if ( ! current_user_can( 'wc_memberships_view_restricted_post_type', $post_type ) ) {

						remove_filter( 'posts_clauses', array( $this, 'handle_posts_clauses' ), 999 );
						remove_filter( 'pre_get_posts', array( $this, 'exclude_restricted_posts' ), 999 );

						$restricted_posts_by_post_type = get_posts( array(
							'fields'    => 'ids',
							'nopaging'  => true,
							'post_type' => $post_type,
						) );

						add_filter( 'posts_clauses', array( $this, 'handle_posts_clauses' ), 999, 2 );
						add_filter( 'pre_get_posts', array( $this, 'exclude_restricted_posts' ), 999 );

						if ( ! empty( $restricted_posts_by_post_type ) ) {
							$post__not_in = array_merge( $restricted_posts_by_post_type, (array) $post__not_in );
						}
					}
				}

				// exclude posts belonging to restricted terms from the query
				$taxonomies = get_taxonomies( array(), 'objects' );
				$tax_query  = array();

				if ( ! empty( $taxonomies ) ) {

					foreach ( $taxonomies as $taxonomy ) {

						$restricted_terms = isset( $taxonomy->name ) ? $restrictions->get_user_restricted_terms( $taxonomy->name ) : null;

						if ( ! empty( $restricted_terms ) ) {

							$tax_query[] = array(
								'taxonomy' => $taxonomy->name,
								'field'    => 'id',
								'terms'    => $restricted_terms,
							);
						}
					}

					// if querying more than one taxonomy, we can get posts for any relationship
					if ( count( $tax_query ) > 1 ) {
						$tax_query[] = 'OR';
					}

					if ( ! empty( $tax_query ) ) {

						remove_filter( 'posts_clauses',  array( $this, 'handle_posts_clauses' ), 999 );
						remove_filter( 'get_terms_args', array( $this, 'handle_get_terms_args' ), 999 );
						remove_filter( 'terms_clauses',  array( $this, 'handle_terms_clauses' ), 999 );

						$restricted_posts_by_taxonomy = get_posts( array(
							'fields'    => 'ids',
							'nopaging'  => true,
							'tax_query' => $tax_query,
						) );

						add_filter( 'posts_clauses',  array( $this, 'handle_posts_clauses' ), 999, 2 );
						add_filter( 'get_terms_args', array( $this, 'handle_get_terms_args' ), 999, 2 );
						add_filter( 'terms_clauses',  array( $this, 'handle_terms_clauses' ), 999 );

						if ( ! empty( $restricted_posts_by_taxonomy ) ) {
							$post__not_in = array_merge( $restricted_posts_by_taxonomy, (array) $post__not_in );
						}
					}
				}

				// handles exclusions
				if ( ! empty( $post__not_in ) ) {
					foreach ( $post__not_in as $i => $post_id ) {
						if ( ! in_array( $post_id, $original_post_not_in, false ) && 'yes' === wc_memberships_get_content_meta( $post_id, '_wc_memberships_force_public' ) ) {
							unset( $post__not_in[ $i ] );
						}
					}
				}

				$post__not_in = array_unique( $post__not_in );

				$this->restricted_comments_by_post_id[ $member_id ] = $post__not_in;
			}

			if ( ! empty( $post__not_in ) ) {
				$comment_query->query_vars['post__not_in'] = $post__not_in;
			}
		}
	}


	/**
	 * Filters the recent comments widget args to prevent displaying comments from restricted posts.
	 *
	 * @since 1.9.0
	 * @deprecated since 1.9.6
	 *
	 * TODO remove this deprecated callback method by version 1.11.0 {FN 2018-01-04}
	 *
	 * @param array $args
	 * @return array
	 */
	public function exclude_restricted_content_recent_comments( $args ) {
		_deprecated_function( 'WC_Memberships_Posts_Restrictions::exclude_restricted_content_recent_comments()', '1.9.6' );
		return $args;
	}


}
