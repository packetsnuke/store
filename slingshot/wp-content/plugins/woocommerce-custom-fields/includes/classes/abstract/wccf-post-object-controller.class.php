<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Post object controller class
 *
 * @class WCCF_Post_Object_Controller
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Post_Object_Controller')) {

class WCCF_Post_Object_Controller
{
    protected static $is_configurable = true;

    // Define main post type
    protected static $main_post_type = 'wccf_product_field';

    // Define post types that use this class
    protected static $post_types = array(
        'wccf_product_field'    => 'WCCF_Product_Field',
        'wccf_product_prop'     => 'WCCF_Product_Property',
        'wccf_checkout_field'   => 'WCCF_Checkout_Field',
        'wccf_order_field'      => 'WCCF_Order_Field',
        'wccf_user_field'       => 'WCCF_User_Field',
    );

    // Define post object controller classes by post types
    protected static $post_object_controller_classes = array(
        'wccf_product_field'    => 'WCCF_Product_Field_Controller',
        'wccf_product_prop'     => 'WCCF_Product_Property_Controller',
        'wccf_checkout_field'   => 'WCCF_Checkout_Field_Controller',
        'wccf_order_field'      => 'WCCF_Order_Field_Controller',
        'wccf_user_field'       => 'WCCF_User_Field_Controller',
    );

    // Cache objects so they don't need to be retrieved more than once
    protected static $cache = array();

    // Cache lists of all available objects split by trashed / not trashed
    protected static $cache_all = array();

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Hook some actions on init
        add_action('init', array($this, 'on_init'), $this->init_priority());

        // Save object configuration
        add_action('save_post', array($this, 'save_post'), 9, 2);

        // Allow filtering by custom taxonomies
        add_action('restrict_manage_posts', array($this, 'add_list_filters'));

        // Handle list filter query
        add_filter('parse_query', array($this, 'handle_list_filter_query'));

        // Remove date filter
        add_filter('months_dropdown_results', array($this, 'remove_date_filter'));

        // Manage list view columns
        add_action('manage_' . $this->get_post_type() . '_posts_columns', array($this, 'manage_list_columns'));

        // Manage list view columns values
        add_action('manage_' . $this->get_post_type() . '_posts_custom_column', array($this, 'manage_list_column_values'), 10, 2);

        // Manage list views
        add_filter('views_edit-' . $this->get_post_type(), array($this, 'manage_list_views'));

        // Manage list bulk actions
        add_filter('bulk_actions-edit-' . $this->get_post_type(), array($this, 'manage_list_bulk_actions'));

        // Expand list search context
        add_filter('posts_join', array($this, 'expand_list_search_context_join'));
        add_filter('posts_where', array($this, 'expand_list_search_context_where'));
        add_filter('posts_groupby', array($this, 'expand_list_search_context_group_by'));

        // Remove default post row actions
        add_filter('post_row_actions', array($this, 'remove_post_row_actions'));

        // Change default post updated notice
        add_filter('post_updated_messages', array($this, 'change_post_updated_notice'));

        // Other hooks
        add_action('add_meta_boxes', array($this, 'remove_meta_boxes'), 99, 2);

        // Add class to actions meta box
        add_filter('postbox_classes_' . $this->get_post_type() . '_' . $this->get_post_type() . '_actions', array($this, 'add_actions_meta_box_class'));

        // Disable autosave
        add_action('admin_enqueue_scripts', array($this, 'disable_autosave'));

        // Allow children to execute their own code when plugins are loaded
        $this->plugins_loaded_own();
    }

    /**
     * Get post type
     *
     * @access protected
     * @return string
     */
    protected function get_post_type()
    {
        return $this->post_type;
    }

    /**
     * Get short form of post type
     *
     * @access protected
     * @return string
     */
    protected function get_post_type_short()
    {
        return $this->post_type_short;
    }

    /**
     * Check if object of this post type is configurable
     *
     * @access protected
     * @return bool
     */
    public function is_configurable()
    {
        return self::$is_configurable;
    }

    /**
     * Set up post type controller
     *
     * @access public
     * @return void
     */
    public function plugins_loaded_own()
    {
    }

    /**
     * Run on WP init
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Add post type
        $this->add_post_type();

        // Allow children to do their own stuff on init
        $this->on_init_own();
    }

    /**
     * Run on WP init
     *
     * @access public
     * @return void
     */
    public function on_init_own()
    {
        // This is overriden in context-specific classes
    }

    /**
     * Add post type
     *
     * @access public
     * @return void
     */
    public function add_post_type()
    {
        // Get admin capability
        $admin_capability = WCCF::get_admin_capability('manage_posts');

        // Set up capabilities
        $capabilities = array_merge(array(
            'edit_post'             => $admin_capability,
            'read_post'             => $admin_capability,
            'delete_post'           => $admin_capability,
            'edit_posts'            => $admin_capability,
            'edit_others_posts'     => $admin_capability,
            'delete_posts'          => $admin_capability,
            'publish_posts'         => $admin_capability,
            'read_private_posts'    => $admin_capability,
        ), $this->get_post_type_custom_capabilities());

        // Define settings
        $args = array_merge($this->get_post_type_labels(), array(
            'public'                => $this->is_post_type_public(),
            'show_ui'               => $this->show_post_type_ui(),
            'show_in_menu'          => $this->show_post_type_in_menu(),
            'menu_position'         => $this->post_type_menu_position(),
            'capabilities'          => $capabilities,
            'supports'              => $this->post_type_supports(),
            'register_meta_box_cb'  => array($this, 'add_meta_boxes'),
        ));

        // Register new post type
        register_post_type($this->get_post_type(), $args);

        // Register custom taxonomies
        $this->register_taxonomies();
    }

    /**
     * Get custom capabilities for custom post type
     *
     * @access public
     * @return array
     */
    public function get_post_type_custom_capabilities()
    {
        return array();
    }

    /**
     * Check if post type is public
     *
     * @access public
     * @return bool
     */
    public function is_post_type_public()
    {
        return false;
    }

    /**
     * Check if post type UI needs to be displayed
     *
     * @access public
     * @return bool
     */
    public function show_post_type_ui()
    {
        return true;
    }

    /**
     * Specify menu to add this post type to
     *
     * @access public
     * @return string
     */
    public function show_post_type_in_menu()
    {
        if ($this->get_post_type() !== self::$main_post_type) {
            return 'edit.php?post_type=' . self::$main_post_type;
        }
    }

    /**
     * Specify position in admin menu
     *
     * @access public
     * @return int
     */
    public function post_type_menu_position()
    {
        return 56;
    }

    /**
     * Define post type support for WP UI elements
     *
     * @access public
     * @return array
     */
    public function post_type_supports()
    {
        return array('');
    }

    /**
     * Get taxonomies
     *
     * @access public
     * @return array
     */
    public function get_taxonomies()
    {
        return array();
    }

    /**
     * Register custom taxonomies
     *
     * @access public
     * @return void
     */
    public function register_taxonomies()
    {
        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            // Register taxonomy
            register_taxonomy($taxonomy_key, $this->get_post_type(), array(
                'label'             => $labels['singular'],
                'labels'            => array(
                    'name'          => $labels['plural'],
                    'singular_name' => $labels['singular'],
                ),
                'public'            => false,
                'show_admin_column' => true,
                'query_var'         => true,
            ));

            // Register custom terms for this taxonomy
            $method = 'get_' . $key . '_list';

            foreach ($this->$method() as $term_key => $term) {
                if (!term_exists($term_key, $taxonomy_key)) {
                    wp_insert_term($term['title'], $taxonomy_key, array(
                        'slug' => $term_key,
                    ));
                }
            }
        }
    }

    /**
     * Add meta boxes
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function add_meta_boxes($post)
    {
        // Proceed only if post type is ours
        if ($post->post_type !== $this->get_post_type()) {
            return;
        }

        // Allow child classes to add their own meta boxes
        $this->add_meta_boxes_own($post);
    }

    /**
     * Add meta boxes
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function add_meta_boxes_own($post)
    {
    }

    /**
     * Render edit page meta box Actions content
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function render_meta_box_actions($post)
    {
        // Render meta box content
        $this->render_meta_box('actions', $post, 'shared/actions', array(
            'id'                => $post->ID,
            'object'            => $this->cache($post->ID),
            'actions'           => $this->get_action_list(),
            'post_type_short'   => $this->get_post_type_short(),
        ));

        // Include preloader
        add_action('dbx_post_sidebar', array($this, 'include_preloader'));
    }

    /**
     * Render edit page meta box content
     *
     * @access protected
     * @param string $meta_box
     * @param object $post
     * @param string $view
     * @param array $variables
     * @return void
     */
    protected function render_meta_box($meta_box, $post, $view, $variables = array())
    {
        // Proceed only if post type is ours
        if ($post->post_type !== $this->get_post_type()) {
            return;
        }

        // Get object for existing post
        $object = self::get($post->ID);

        // Let object pass additional variables into view
        if ($object && method_exists($object, 'get_meta_box_variables')) {
            $object_variables = (array) $object->get_meta_box_variables($meta_box);
            $variables = array_merge($variables, $object_variables);
        }

        // Exctract variables
        extract($variables);

        // Load view
        include WCCF_PLUGIN_PATH . 'includes/views/' . $view . '.php';
    }

    /**
     * Include preloader
     *
     * @access public
     * @return void
     */
    public function include_preloader()
    {
        include WCCF_PLUGIN_PATH . 'includes/views/shared/preloader.php';
    }

    /**
     * Get array of actions available
     *
     * @access public
     * @return array
     */
    public function get_action_list()
    {
        $actions = array();

        // Make sure we know object type title
        if (!method_exists($this, 'get_object_type_title')) {
            return $actions;
        }

        // Get title
        $title = $this->get_object_type_title();

        // Save
        $actions['save'] = __('Save', 'rp_wccf') . ' ' . $title;

        // Allow child classes to modify actions list
        $actions = $this->modify_action_list($actions);

        return $actions;
    }

    /**
     * Modify actions list
     *
     * @access protected
     * @param array $actions
     * @return array
     */
    protected function modify_action_list($actions = array())
    {
        return $actions;
    }

    /**
     * Save post object field values
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @param array $posted
     * @return void
     */
    public function save_post($post_id, $post, $posted = array())
    {
        // Get post type of this controller
        $post_type = $this->get_post_type();

        // Only proceed if it's current post type
        if ($post->post_type !== $post_type) {
            return;
        }

        // Only proceed if object is configurable
        if (!$this->is_configurable()) {
            return;
        }

        // Get posted values
        $posted = !empty($posted) ? $posted : $_POST;

        // Check if required properties were passed in
        if (empty($post_id) || empty($post)) {
            return;
        }

        // Make sure the correct post ID was passed from form
        if (empty($posted['post_ID']) || $posted['post_ID'] != $post_id) {
            return;
        }

        // Make sure it is not a draft save action
        if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_autosave($post)) || is_int(wp_is_post_revision($post))) {
            return;
        }

        // Make sure user has permissions to edit config
        if (!WCCF::is_authorized('manage_posts')) {
            return;
        }

        // Get action
        if (!empty($posted[$post_type . '_button']) && $posted[$post_type . '_button'] == 'actions' && !empty($posted[$post_type . '_actions'])) {
            $action = $posted[$post_type . '_actions'];
        }
        else {
            $action = 'save';
        }

        // Get object by id
        $object = self::get($post_id);

        // Update existing object
        if ($object) {
            $object->save_configuration($posted, $action);
        }
        // Create new object
        else {

            // Retrieve class name by post type
            $class_name = self::$post_types[$this->get_post_type()];

            // Initialize object
            $object = new $class_name($post_id);

            // Save configuration
            $object->save_configuration($posted, $action);
        }
    }

    /**
     * Check list action request and return object if action request is valid
     *
     * @access public
     * @param string $action
     * @return mixed
     */
    public function get_valid_list_action_object($action)
    {
        // Check action
        if (empty($_REQUEST[$action])) {
            return false;
        }

        // Make sure this is our post type
        if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] !== $this->get_post_type()) {
            return false;
        }

        // Make sure user is allowed to execute this action
        if (!WCCF::is_authorized('manage_posts')) {
            return false;
        }

        // Make sure object is of this type
        if (!isset($_REQUEST['wccf_object_id']) || !RightPress_Helper::post_type_is($_REQUEST['wccf_object_id'], $this->get_post_type())) {
            return false;
        }

        // Load object
        $object = self::get($_REQUEST['wccf_object_id']);

        // Check if object is valid and return
        return $object ?: false;
    }

    /**
     * Add filtering capabilities
     *
     * @access public
     * @return void
     */
    public function add_list_filters()
    {
        global $typenow;
        global $wp_query;

        if ($typenow != $this->get_post_type()) {
            return;
        }

        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            // Extract selected filter options
            $selected = array();

            if (!empty($wp_query->query[$taxonomy_key]) && is_numeric($wp_query->query[$taxonomy_key])) {
                $selected[$taxonomy_key] = $wp_query->query[$taxonomy_key];
            }
            else if (!empty($wp_query->query[$taxonomy_key])) {
                $term = get_term_by('slug', $wp_query->query[$taxonomy_key], $taxonomy_key);
                $selected[$taxonomy_key] = $term ? $term->term_id : 0;
            }
            else {
                $selected[$taxonomy_key] = 0;
            }

            // Add options
            wp_dropdown_categories(array(
                'show_option_all'   =>  $labels['all'],
                'taxonomy'          =>  $taxonomy_key,
                'name'              =>  $taxonomy_key,
                'selected'          =>  $selected[$taxonomy_key],
                'show_count'        =>  true,
                'hide_empty'        =>  false,
            ));
        }
    }

    /**
     * Handle list filter query
     *
     * @access public
     * @param object $query
     * @return void
     */
    public function handle_list_filter_query($query)
    {
        global $pagenow;
        global $typenow;

        if ($pagenow != 'edit.php' || $typenow != $this->get_post_type()) {
            return;
        }

        $qv = &$query->query_vars;

        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            if (isset($qv[$taxonomy_key]) && is_numeric($qv[$taxonomy_key]) && $qv[$taxonomy_key] != 0) {
                $term = get_term_by('id', $qv[$taxonomy_key], $taxonomy_key);
                $qv[$taxonomy_key] = $term->slug;
            }
        }
    }

    /**
     * Remove date filter
     *
     * @access public
     * @param array $months
     * @return void
     */
    public function remove_date_filter($months)
    {
        global $typenow;

        // Only proceed if this call is for our post type and our object is not date dependent
        if ($typenow === $this->get_post_type() && $this->is_configurable()) {
            return array();
        }

        return $months;
    }

    /**
     * Manage list columns
     *
     * @access public
     * @param array $columns
     * @return array
     */
    public function manage_list_columns($columns)
    {
        global $typenow;

        $new_columns = array();

        foreach ($columns as $column_key => $column) {
            $allowed_columns = array();

            if ($this->is_configurable()) {
                $allowed_columns[] = 'cb';
            }

            if (in_array($column_key, $allowed_columns)) {
                $new_columns[$column_key] = $column;
            }
        }

        // Allow children to add more columns
        $new_columns = $this->add_list_columns($new_columns);

        return $new_columns;
    }

    /**
     * Add list columns
     *
     * @access protected
     * @param array $columns
     * @return array
     */
    protected function add_list_columns($columns)
    {
        return $columns;
    }

    /**
     * Manage list column values
     *
     * @access public
     * @param array $column
     * @param int $post_id
     * @return void
     */
    public function manage_list_column_values($column, $post_id)
    {
        // Load object
        $object = self::get($post_id);

        // No object?
        if (!$object) {
            return;
        }

        // Allow children to add their own column values
        $this->print_list_column_value($object, $column);
    }

    /**
     * Print list column value
     *
     * @access protected
     * @param object $object
     * @param string $column
     * @return void
     */
    protected function print_list_column_value($object, $column)
    {
        echo '';
    }

    /**
     * Print post actions
     *
     * @access public
     * @return void
     */
    public function print_post_actions()
    {
        global $post;
        $post_type_object = get_post_type_object($this->get_post_type());

        // Store actions
        $actions = array();

        // Edit
        if ($post->post_status !== 'trash') {
            $actions['edit'] = '<a href="' . get_edit_post_link($post->ID, true) . '" title="' . esc_attr(__('Edit', 'rp_wccf')) . '">' . __('Edit', 'rp_wccf') . '</a>';
        }

        // Trash
        if ($post->post_status !== 'trash' && EMPTY_TRASH_DAYS) {
            $actions['trash'] = '<a class="submitdelete" title="' . esc_attr(__('Trash', 'rp_wccf')) . '" href="' . get_delete_post_link($post->ID) . '">' . __('Trash', 'rp_wccf') . '</a>';
        }

        // Delete
        if ($post->post_status !== 'trash' && !EMPTY_TRASH_DAYS) {
            $actions['delete'] = '<a class="submitdelete" title="' . esc_attr(__('Delete Permanently', 'rp_wccf')) . '" href="' . get_delete_post_link($post->ID, '', true) . '">' . __('Delete Permanently', 'rp_wccf') . '</a>';
        }

        // Untrash
        if ($post->post_status === 'trash') {
            $actions['untrash'] = '<a title="' . esc_attr(__('Restore', 'rp_wccf')) . '" href="' . wp_nonce_url(admin_url(sprintf($post_type_object->_edit_link . '&amp;action=untrash', $post->ID)), 'untrash-post_' . $post->ID) . '">' . __('Restore', 'rp_wccf') . '</a>';
        }

        // Allow child classes to modify post actions list
        $actions = $this->modify_list_view_actions_list($actions);

        // Style action links
        foreach ($actions as $action_key => $action_link) {
            $actions[$action_key] = '<span class="' . $action_key . '">' . $action_link . '</span>';
        }

        // Print post actions row
        echo '<div class="row-actions">' . join(' | ', $actions) . '</div>';
    }

    /**
     * Allow child classes to modify list view actions list
     *
     * @access protected
     * @param array $actions
     * @return array
     */
    protected function modify_list_view_actions_list($actions)
    {
        return $actions;
    }

    /**
     * Manage list views
     *
     * @access public
     * @param array $views
     * @return array
     */
    public function manage_list_views($views)
    {
        $new_views = array();

        foreach ($views as $view_key => $view) {
            if (in_array($view_key, array('all', 'trash'))) {
                $new_views[$view_key] = $view;
            }
        }

        // Allow child classes to modify list views
        if (method_exists($this, 'filter_list_views')) {
            $new_views = $this->filter_list_views($new_views);
        }

        return $new_views;
    }

    /**
     * Manage list bulk actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function manage_list_bulk_actions($actions)
    {
        $new_actions = array();

        if ($this->is_configurable()) {
            foreach ($actions as $action_key => $action) {
                if (in_array($action_key, array('trash', 'untrash', 'delete'))) {
                    $new_actions[$action_key] = $action;
                }
            }
        }

        return $new_actions;
    }

    /**
     * Expand list search context
     *
     * @access public
     * @param string $join
     * @return string
     */
    public function expand_list_search_context_join($join)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == $this->get_post_type() && isset($_GET['s']) && $_GET['s'] != '') {
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' pm ON ' . $wpdb->posts . '.ID = pm.post_id ';
        }

        return $join;
    }

    /**
     * Expand list search context with more fields
     *
     * @access public
     * @param string $where
     * @return string
     */
    public function expand_list_search_context_where($where)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        // Define post types with search contexts, meta field whitelist (searchable meta fields) etc
        if (method_exists($this, 'expand_list_search_context_where_properties')) {
            $post_types = array(
                $this->get_post_type() => $this->expand_list_search_context_where_properties(),
            );
        }
        else {
            $post_types = array();
        }

        // Search
        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && isset($post_types[$_GET['post_type']]) && !empty($_GET['s'])) {

            $search_phrase = trim($_GET['s']);
            $exact_match = false;
            $context = null;

            // Exact match?
            if (preg_match('/^\".+\"$/', $search_phrase) || preg_match('/^\'.+\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 1, -1);
            }
            else if (preg_match('/^\\\\\".+\\\\\"$/', $search_phrase) || preg_match('/^\\\\\'.+\\\\\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 2, -2);
            }
            // Or search with context?
            else {

                foreach ($post_types[$_GET['post_type']]['contexts'] as $context_key => $context_value) {
                    if (preg_match('/^' . $context_key . '\:/i', $search_phrase)) {
                        $context = $context_value;
                        $search_phrase = trim(preg_replace('/^' . $context_key . '\:/i', '', $search_phrase));
                        break;
                    }
                }
            }

            // Search by ID?
            if ($context === 'ID') {
                $replacement = $wpdb->prepare(
                    '(' . $wpdb->posts . '.ID LIKE %s)',
                    $search_phrase
                );
            }

            // Search within other context
            else if ($context) {
                $replacement = $wpdb->prepare(
                    '(pm.meta_key LIKE %s) AND (pm.meta_value LIKE %s)',
                    $context,
                    $search_phrase
                );
            }

            // Regular search
            else {
                $whitelist = 'pm.meta_key IN (\'' . join('\', \'', $post_types[$_GET['post_type']]['meta_whitelist']) . '\')';

                // Exact match?
                if ($exact_match) {
                    $replacement = $wpdb->prepare(
                        '(' . $wpdb->posts . '.ID LIKE %s) OR (pm.meta_value LIKE %s)',
                        $search_phrase,
                        $search_phrase
                    );
                    $replacement = '(' . $whitelist . ' AND ' . $replacement . ')';

                }

                // Regular match
                else {
                    $replacement = '(' . $whitelist . ' AND ((' . $wpdb->posts . '.ID LIKE $1) OR (pm.meta_value LIKE $1)))';
                }
            }

            $where = preg_replace('/\(\s*' . $wpdb->posts . '.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/', $replacement, $where);
        }

        return $where;
    }

    /**
     * Expand list search context with more fields - group results by id
     *
     * @access public
     * @param string $groupby
     * @return string
     */
    public function expand_list_search_context_group_by($groupby)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == $this->get_post_type() && isset($_GET['s']) && $_GET['s'] != '') {
            $groupby = $wpdb->posts . '.ID';
        }

        return $groupby;
    }

    /**
     * Remove default post row actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function remove_post_row_actions($actions)
    {
        global $post;

        // Make sure it's our post type
        if ($post->post_type === $this->get_post_type()) {
            return array();
        }

        return $actions;
    }

    /**
     * Load object from cache
     *
     * @access public
     * @param string $type
     * @param int $id
     * @return object
     */
    public static function cache($id)
    {
        // Check if object exists in cache
        if (!isset(self::$cache[$id])) {

            // Get object by ID
            $object = self::get_by_id($id);

            // Check if object was retrieved
            if (!$object) {
                return false;
            }

            // Store in cache
            self::$cache[$id] = $object;
        }

        // Return from cache
        return self::$cache[$id];
    }

    /**
     * Get object by it's id
     *
     * @access public
     * @param int $id
     * @return object
     */
    public static function get_by_id($id)
    {
        // Check if post ID is numeric
        if (is_numeric($id)) {

            // Retrieve post
            $post = get_post($id);

            // Check if post is of known type
            if ($post && isset(self::$post_types[$post->post_type])) {

                // All our posts must be either published or trashed
                if (in_array($post->post_status, array('publish', 'trash'))) {

                    // Retrieve class name by post type
                    $class_name = self::$post_types[$post->post_type];

                    // Initialize and return object
                    return new $class_name($id);
                }
            }
        }

        // Nothing found
        return false;
    }

    /**
     * Get list of all items for admin display
     *
     * @access public
     * @param string $post_type
     * @param string $title_key
     * @param string $subtitle_key
     * @param bool $include_trashed
     * @return array
     */
    public static function get_object_list_for_display($post_type, $title_key = 'label', $subtitle_key = null, $include_trashed = false)
    {
        $items = array();

        // Check if list of objects with this post type exists in cache
        if (!isset(self::$cache_all[$post_type])) {

            // Define object list for this post type
            self::$cache_all[$post_type] = array(
                'trashed'       => array(),
                'not_trashed'   => array(),
            );

            // Retrieve and iterate over all objects of this type
            foreach (self::get_all_objects($post_type, true) as $object) {

                // Check if corresponding post is trashed
                $is_trashed = RightPress_Helper::post_is_trashed($object->id);
                $trashed_key = $is_trashed ? 'trashed' : 'not_trashed';

                // Get suffix to display
                $trashed_suffix = ((!empty($subtitle_key) && !empty($object->$subtitle_key)) ? (' (' . $object->$subtitle_key . ')') : '');
                $trashed_suffix .= self::trashed_suffix($object->id, $is_trashed);

                // Add to list
                self::$cache_all[$post_type][$trashed_key][$object->id] = $object->$title_key . $trashed_suffix;
            }
        }

        // Add not trashed items to results array
        $items = self::$cache_all[$post_type]['not_trashed'];

        // Maybe add trashed items to results array
        if ($include_trashed) {
            $items = $items + self::$cache_all[$post_type]['trashed'];
        }

        // Sort items by object id
        ksort($items);

        // Return items
        return $items;
    }

    /**
     * Get array with all objects
     *
     * @access public
     * @param string $post_type
     * @param array $meta_query
     * @param array $tax_query
     * @param int $limit
     * @return array
     */
    public static function get_all_objects($post_type, $include_trashed = false, $meta_query = array(), $tax_query = array(), $limit = -1)
    {
        $objects = array();

        // Iterate list of all IDs and iterate over them
        foreach (self::get_list_of_all_ids($post_type, $include_trashed, $meta_query, $tax_query, $limit) as $id) {

            // Try to get object from cache
            if ($object = self::get($id)) {

                // Add object to list
                $objects[$id] = $object;
            }
        }

        // Return objects
        return $objects;
    }

    /**
     * Get list of all object ids
     *
     * @access public
     * @param string $post_type
     * @param bool $include_trashed
     * @param array $meta_query
     * @param array $tax_query
     * @param int $limit
     * @return array
     */
    public static function get_list_of_all_ids($post_type, $include_trashed = false, $meta_query = array(), $tax_query = array(), $limit = -1)
    {
        // Set up query
        $args = array(
            'post_type'         => $post_type,
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private'),
            'posts_per_page'    => $limit,
            'fields'            => 'ids',
        );

        // Maybe search for trashed objects
        if ($include_trashed) {
            $args['post_status'][] = 'trash';
        }

        // Maybe add meta query
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        // Maybe add tax query
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // Run query
        $query = new WP_Query($args);

        // Return ids
        return $query->posts;
    }

    /**
     * Change default post updated message
     *
     * @access public
     * @param array $messages
     * @return array
     */
    public function change_post_updated_notice($messages)
    {
        global $post_ID;

        // Check if this is our post type
        if (RightPress_Helper::post_type_is($post_ID, $this->get_post_type())) {
            unset($messages['post'][4]);
        }

        return $messages;
    }

    /**
     * Remove meta boxes from own pages
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function remove_meta_boxes($post_type, $post)
    {
        // Remove third party meta boxes from own pages
        if ($post_type === $this->get_post_type()) {

            // Allow developers to leave specific meta boxes
            $meta_boxes_to_leave = apply_filters('wccf_meta_boxes_whitelist', array());
            $meta_boxes_to_leave = apply_filters('rp_wccf_meta_boxes_whitelist', $meta_boxes_to_leave); // Legacy filter

            foreach (self::get_meta_boxes() as $context => $meta_boxes_by_context) {
                foreach ($meta_boxes_by_context as $subcontext => $meta_boxes_by_subcontext) {
                    foreach ($meta_boxes_by_subcontext as $meta_box_id => $meta_box) {
                        if (!in_array($meta_box_id, $meta_boxes_to_leave)) {
                            remove_meta_box($meta_box_id, $post_type, $context);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get list of meta boxes for current screent
     *
     * @access public
     * @return array
     */
    public static function get_meta_boxes()
    {
        global $wp_meta_boxes;

        $screen = get_current_screen();
        $page = $screen->id;

        return isset($wp_meta_boxes[$page]) ? $wp_meta_boxes[$page] : array();
    }

    /**
     * Init hook priority
     *
     * @access public
     * @return int
     */
    protected function init_priority()
    {
        return 0;
    }

    /**
     * Get all supported post types
     *
     * @access public
     * @return array
     */
    public static function get_post_types()
    {
        return self::$post_types;
    }

    /**
     * Check if post type exists
     *
     * @access public
     * @param string $post_type
     * @return bool
     */
    public static function post_type_exists($post_type)
    {
        $post_types = self::get_post_types();
        return isset($post_types[$post_type]);
    }

    /**
     * Add class to actions meta boxes
     *
     * @access public
     * @param array $classes
     * @return array
     */
    public function add_actions_meta_box_class($classes)
    {
        array_push($classes, 'wccf_actions_meta_box');
        return $classes;
    }

    /**
     * Disable autosave
     *
     * @access public
     * @return void
     */
    public function disable_autosave()
    {
        global $typenow;

        if ($typenow === $this->get_post_type()) {
            wp_dequeue_script('autosave');
        }
    }

    /**
     * Get general singular short object name by post type
     *
     * @access public
     * @param string $post_type
     * @return string
     */
    public static function get_general_short_name($post_type)
    {
        $names = array(
            'wccf_product_field'    => __('Field', 'rp_wccf'),
            'wccf_product_prop'     => __('Property', 'rp_wccf'),
            'wccf_checkout_field'   => __('Field', 'rp_wccf'),
            'wccf_order_field'      => __('Field', 'rp_wccf'),
            'wccf_user_field'       => __('Field', 'rp_wccf'),
        );

        return isset($names[$post_type]) ? $names[$post_type] : '';
    }

    /**
     * Get post object controller classes
     *
     * @access public
     * @return array
     */
    public static function get_post_object_controller_classes()
    {
        return self::$post_object_controller_classes;
    }

    /**
     * Check if post is trashed and return corresponding suffix
     *
     * @access public
     * @param int $id
     * @param bool $is_trashed
     * @return string
     */
    public static function trashed_suffix($id, $is_trashed = null)
    {
        // Do we know if this post is trashed?
        if ($is_trashed === null) {
            $is_trashed = RightPress_Helper::post_is_trashed($id);
        }

        // Check if post is trashed and return appropriate suffix
        return $is_trashed ? ' (' . __('trashed', 'rp_wccf') . ')' : '';
    }

    /**
     * Print link to post edit page
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @param int $max_chars
     * @return void
     */
    public static function print_link_to_post($id, $title = '', $pre = '', $post = '', $max_chars = null)
    {
        echo self::get_link_to_post_html($id, $title, $pre, $post, $max_chars);
    }

    /**
     * Format link to post edit page
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @param int $max_chars
     * @return string
     */
    public static function get_link_to_post_html($id, $title = '', $pre = '', $post = '', $max_chars = null)
    {
        // Get title to display
        $link_title = '';
        $title_to_display = !empty($title) ? $title : '#' . $id;

        // Maybe shorten title
        if ($max_chars !== null && strlen($title_to_display) > ($max_chars + 3)) {
            $link_title = $title_to_display;
            $title_to_display = RightPress_Helper::shorten_text($title_to_display, $max_chars);
        }

        // Make link and return
        return $pre . ' <a href="post.php?post=' . $id . '&action=edit" title="' . $link_title . '">' . $title_to_display . '</a> ' . $post;
    }

    /**
     * Get controller instance from post type
     *
     * @access public
     * @param string $post_type
     * @return object
     */
    public static function get_controller_instance_by_post_type($post_type)
    {
        $controller_classes = WCCF_Post_Object_Controller::get_post_object_controller_classes();
        $controller_class = $controller_classes[$post_type];
        return $controller_class::get_instance();
    }

    /**
     * Get object by id and optionally check post type
     *
     * @access public
     * @param int $id
     * @param string $post_type
     * @return object
     */
    public static function get($id, $post_type = null)
    {
        // Get object from cache
        $object = self::cache($id);

        // Check if object was loaded
        if (!$object) {
            return false;
        }

        // Optional post type check
        if ($post_type !== null && $object->get_post_type() !== $post_type) {
            return false;
        }

        // Return object
        return $object;
    }

    /**
     * Get object count by taxonomy
     *
     * Currently only searches for published posts
     *
     * @access public
     * @param string $taxonomy
     * @param mixed $value
     * @return int
     */
    public function get_count_by_taxonomy($taxonomy, $value)
    {
        $value = (array) $value;

        // Run query
        $query = new WP_Query(array(
            'post_type'         => $this->get_post_type(),
            'post_status'       => 'publish',
            'fields'            => 'ids',
            'posts_per_page'    => 1,
            'tax_query' => array(
                array(
                    'taxonomy'  => $taxonomy,
                    'field'     => 'slug',
                    'terms'     => $value,
                    'operator'  => 'IN',
                ),
            ),
        ));

        // Return count
        return (int) $query->found_posts;
    }

    /**
     * Filter array of objects by property
     * Uses strict === comparison and checks for (bool) true if value is not provided
     * Does not work for NULL value
     *
     * @access public
     * @param array $objects
     * @param string $property
     * @param mixed $value
     * @return array
     */
    public static function filter_by_property($objects, $property, $value = null)
    {
        $results = array();
        $getter = 'get_' . $property;

        // Iterate over objects
        foreach ((array) $objects as $object_key => $object) {

            // Make sure property exists
            if (!property_exists($object, $property)) {
                continue;
            }

            // Check if property can be read, use getter method if not
            $reflection_property = new ReflectionProperty($object, $property);
            $is_public = $reflection_property->isPublic();

            // Get property value for comparison
            if ($is_public && isset($object->$property)) {
                $property_value = $object->$property;
            }
            else if (!$is_public && method_exists($object, $getter)) {
                $property_value = $object->$getter();
            }
            else {
                continue;
            }

            // Property does not equal provided value
            if ($value !== null && $value !== $property_value) {
                continue;
            }

            // Property is not positive
            if ($value === null && !$property_value) {
                continue;
            }

            // Add to results array
            $results[$object_key] = $object;
        }

        return $results;
    }


}
}
