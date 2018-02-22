<?php
  
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
  
// add menu under woocommerce
add_action('admin_menu', 'wcplpro_menu_addition', 100);
function wcplpro_menu_addition() {
  add_submenu_page( 'woocommerce', 'Products List PRO', 'Products List PRO', 'manage_options', 'productslistpro', 'wcplpro_options' );
}

add_action( 'admin_init', 'wcplpro_register_settings' );


function wcplpro_sortable() {
  
  $sortable = array(
    'wcplpro_sku' => __('SKU', 'wcplpro'),
    'wcplpro_title' => __('Name', 'wcplpro'),
    'wcplpro_thumb' => __('Thumbnail', 'wcplpro'),
    'wcplpro_categories' => __('Categories', 'wcplpro'),
    'wcplpro_tags' => __('Tags', 'wcplpro'),
    'wcplpro_stock' => __('Stock', 'wcplpro'),   
    'wcplpro_price' => __('Price', 'wcplpro'),
    'wcplpro_total' => __('Total', 'wcplpro'),
    'wcplpro_offer' => __('Extra Image', 'wcplpro'),
    'wcplpro_cart' => __('Add to Cart Button', 'wcplpro'),
    'wcplpro_globalcart' => __('Global Add to Cart Checkbox', 'wcplpro'),
    'wcplpro_qty' => __('Quantity', 'wcplpro'),
    'wcplpro_weight' => __('Weight', 'wcplpro'),
    'wcplpro_dimensions' => __('Dimensions', 'wcplpro'),
    'wcplpro_wishlist' => __('Wishlist', 'wcplpro'),
    'wcplpro_gift' => __('Gift Wrap', 'wcplpro'),
    'wcplpro_desc' => __('Description', 'wcplpro'),
    'wcplpro_custommeta' => __('Custom Meta', 'wcplpro')
  );
  
  return apply_filters( 'wcplpro_sortable_filter', $sortable );
}


function wcplpro_not_sortable(){
  
  $notsoratble = array(
    'wcplpro_default_qty' => __('Default Quantity', 'wcplpro'), 
    'wcplpro_qty_control' => __('Display Quantity Controls ( - / + buttons)', 'wcplpro'),
    'wcplpro_thumb_size' => __('Thumbnail Size', 'wcplpro'), 
    'wcplpro_thumb_link' => __('Thumbnail Link', 'wcplpro'), 
    'wcplpro_image' => __('Extra Image File', 'wcplpro'),
    'wcplpro_order' => __('Columns Order', 'wcplpro'),
    'wcplpro_columns_names' => __('Columns Names', 'wcplpro'),
    'wcplpro_ajax' => __('Enable Ajax Add to Cart', 'wcplpro'), 
    'wcplpro_head' => __('Table Head', 'wcplpro'),
    'wcplpro_sorting' => __('Enable Sorting', 'wcplpro'), 
    'wcplpro_lightbox' => __('Enable Image Pop Up', 'wcplpro'), 
    'wcplpro_hide_zero' => __('Hide zero priced products', 'wcplpro'),
    'wcplpro_hide_outofstock' => __('Hide out of stock products', 'wcplpro'),
    'wcplpro_zero_to_out' => __('Treat zero quantity products as out of stock', 'wcplpro_zero_to_out'),
    'wcplpro_globalposition' => __('Global Add to Cart Button Position', 'wcplpro'),
    'wcplpro_desc_inline' => __('Description Inline', 'wcplpro'),
    'wcplpro_global_status' => __('Global Add to Cart Button Default Status', 'wcplpro'),
    'wcplpro_panel_manualclose' => __('Manually close the notification panel', 'wcplpro'),
    'wcplpro_hide_global_total' => __('Hide global button total', 'wcplpro'),
    'wcplpro_dont_link_to_product' => __('Do not link product title to product single page', 'wcplpro'),
    'wcplpro_excerpt_length' => __('Excerpt max length in characters', 'wcplpro'),
    'wcplpro_order_direction' => __('Order Direction', 'wcplpro'),
    'wcplpro_orderby' => __('Order By', 'wcplpro'),
    'wcplpro_quickview' => __('YITH Quick View', 'wcplpro'),
    'wcplpro_pagination' => __('Display Pagination', 'wcplpro'),
    'wcplpro_posts_per_page' => __('Products per page', 'wcplpro'),
    'wcplpro_filter_cat' => __('Enable Categories Dropdown Filter', 'wcplpro'),
    'wcplpro_filter_tag' => __('Enable Tags Dropdown Filter', 'wcplpro'),
    'wcplpro_filter_search' => __('Enable Search Filter', 'wcplpro'),
    'wcplpro_filters_position' => __('Filters Position', 'wcplpro'),
    'wcplpro_attributes' => __('Attributes', 'wcplpro'),
    'wcplpro_metafield' => __('Meta Keys', 'wcplpro')
  );

  return apply_filters( 'wcplpro_not_sortable_filter', $notsoratble);
  
}

function wcplpro_fields_func() {
  
  $notsoratble  = wcplpro_not_sortable();
  $sortable     = wcplpro_sortable();
  
  $fields_array = array_merge($notsoratble, $sortable);
  
  return (apply_filters( 'wcplpro_fields_func_filter', $fields_array));
}

// register settings
function wcplpro_register_settings(){
  $fields = wcplpro_fields_func();
  
  foreach($fields as $field => $fieldtext) {
    register_setting( 'wcplpro_group', $field ); 
  }
}


function wcplpro_orderby_values() {
  
  $orderby_values = apply_filters('wcplpro_orderby_values' ,array(
    'date' => __('Date', 'wcplpro'),
    'title' => __('Product Title', 'wcplpro'),
    'post__in' => __('Included products', 'wcplpro'),
    'menu_order' => __('Menu order', 'wcplpro'),
    '_price' => __('Price', 'wcplpro'),
    '_sale_price' => __('Sale Price', 'wcplpro'),
    '_regular_price' => __('Regular Price', 'wcplpro'),
    '_sku' => __('SKU', 'wcplpro'),
    '_weight' => __('Weight', 'wcplpro'),
    '_length' => __('Length', 'wcplpro'),
    '_width' => __('Width', 'wcplpro'),
    '_stock' => __('Stock', 'wcplpro'),
    'total_sales' => __('Total Sales', 'wcplpro'),
    '_stock_status' => __('Stock Status', 'wcplpro'),
    '_wc_average_rating' => __('Rating', 'wcplpro'),
  ));
  
  return $orderby_values;
  
}
function wcplpro_options() {
  
  if (isset($_GET['hidebar']) && $_GET['hidebar'] == 1) {
    update_option('wcplpro_sidehide', 1);
  }
  
  $hidebar = get_option('wcplpro_sidehide', 0);
  ?>
  <div class="wrap">
    <h2><?php _e('Woocommerce Products List Settings', 'wcplpro'); ?></h2>
    <div class="<?php if ($hidebar !=1) { echo 'leftpanel'; } ?>">    
      <form method="post" action="options.php">
            <?php settings_fields( 'wcplpro_group' ); ?>
            <?php do_settings_sections( 'wcplpro_group' ); ?>
            <div class="fieldwrap">
              <p><?php submit_button(__('Save Changes on All Tabs', 'wcplpro')); ?></p>
            </div>
            
            <h2 class="nav-tab-wrapper">
              <a href="#columns-tab" data-tab="columns-tab" class="nav-tab nav-tab-active">Columns</a>
              <a href="#uiux-tab" data-tab="uiux-tab"  class="nav-tab">UI/UX</a>
              <a href="#pagination-filters-tab" data-tab="pagination-filters-tab"  class="nav-tab">Pagination &amp; Filters</a>
              <a href="#ordering-tab" data-tab="ordering-tab"  class="nav-tab">Ordering</a>
            </h2>
            <?php
                        
              
            $fields = wcplpro_fields_func();

            foreach($fields as $field => $fieldtext) {
              ${$field}  = get_option($field);
            }
            
            do_action('wcplpro_before_extra_options');
            
            // columns
            echo '<div class="tab-content" id="columns-tab">';
            echo '<div class="padding"></div>';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_title">'. __('Display Product Title', 'wcplpro') .'</label>
              <select name="wcplpro_title" id="wcplpro_title">
                <option value="1" '. ($wcplpro_title == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_title == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_sku">'. __('Display SKU', 'wcplpro') .'</label>
              <select name="wcplpro_sku" id="wcplpro_sku">
                <option value="1" '. ($wcplpro_sku == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_sku == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
             echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_thumb">'. __('Display Thumbnail', 'wcplpro') .'</label>
              <select name="wcplpro_thumb" id="wcplpro_thumb">
                <option value="1" '. ($wcplpro_thumb == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_thumb == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_thumb_size">'. __('Thumbnail Width in Pixels', 'wcplpro') .'</label>
              <input type="text" name="wcplpro_thumb_size" id="wcplpro_thumb_size" value="'. $wcplpro_thumb_size .'">
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_thumb_link">'. __('Thumbnail Link', 'wcplpro') .'</label>
              <select name="wcplpro_thumb_link" id="wcplpro_thumb_link">
                <option value="image" '. ($wcplpro_thumb_link == 'image' ? 'selected="selected"' : '') .'>'. __('Image', 'wcplpro') .'</option>
                <option value="product" '. ($wcplpro_thumb_link == 'product' ? 'selected="selected"' : '') .'>'. __('Product', 'wcplpro') .'</option>
                <option value="productnew" '. ($wcplpro_thumb_link == 'productnew' ? 'selected="selected"' : '') .'>'. __('Product in new tab', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_categories">'. __('Display Categories', 'wcplpro') .'</label>
              <select name="wcplpro_categories" id="wcplpro_categories">
                <option value="1" '. ($wcplpro_categories == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_categories == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_tags">'. __('Display Tags', 'wcplpro') .'</label>
              <select name="wcplpro_tags" id="wcplpro_tags">
                <option value="1" '. ($wcplpro_tags == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_tags == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_stock">'. __('Display Stock', 'wcplpro') .'</label>
              <select name="wcplpro_stock" id="wcplpro_stock">
                <option value="1" '. ($wcplpro_stock == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_stock == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_hide_zero">'. __('Hide Zero Priced Products', 'wcplpro') .'</label>
              <select name="wcplpro_hide_zero" id="wcplpro_hide_zero">
                <option value="1" '. ($wcplpro_hide_zero == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_hide_zero == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_hide_outofstock">'. __('Hide Out of Stock Products', 'wcplpro') .'</label>
              <select name="wcplpro_hide_outofstock" id="wcplpro_hide_outofstock">
                <option value="1" '. ($wcplpro_hide_outofstock == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_hide_outofstock == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_zero_to_out">'. __('Treat zero quantity Products as Out of Stock', 'wcplpro') .'</label>
              <select name="wcplpro_zero_to_out" id="wcplpro_zero_to_out">
                <option value="1" '. ($wcplpro_zero_to_out == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_zero_to_out == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_price">'. __('Display Price', 'wcplpro') .'</label>
              <select name="wcplpro_price" id="wcplpro_price">
                <option value="1" '. ($wcplpro_price == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_price == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_total">'. __('Display Total', 'wcplpro') .'</label>
              <select name="wcplpro_total" id="wcplpro_total">
                <option value="1" '. ($wcplpro_total == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_total == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_qty">'. __('Display Quantity Field', 'wcplpro') .'</label>
              <select name="wcplpro_qty" id="wcplpro_qty">
                <option value="1" '. ($wcplpro_qty == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_qty == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_default_qty">'. __('Default Quantity Value', 'wcplpro') .'</label>
              <input type="number" name="wcplpro_default_qty" id="wcplpro_default_qty" value="'. $wcplpro_default_qty .'" min="0">
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_qty_control">'. __('Display Quantity Controls', 'wcplpro') .'</label>
              <select name="wcplpro_qty_control" id="wcplpro_qty_control">
                <option value="1" '. ($wcplpro_qty_control == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_qty_control == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_weight">'. __('Display Weight', 'wcplpro') .'</label>
              <select name="wcplpro_weight" id="wcplpro_weight">
                <option value="1" '. ($wcplpro_weight == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_weight == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_dimensions">'. __('Display Dimensions', 'wcplpro') .'</label>
              <select name="wcplpro_dimensions" id="wcplpro_dimensions">
                <option value="1" '. ($wcplpro_dimensions == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_dimensions == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_cart">'. __('Display Add To Cart', 'wcplpro') .'</label>
              <select name="wcplpro_cart" id="wcplpro_cart">
                <option value="1" '. ($wcplpro_cart == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_cart == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_globalcart">'. __('Display Global Add To Cart', 'wcplpro') .'</label>
              <select name="wcplpro_globalcart" id="wcplpro_globalcart">
                <option value="1" '. ($wcplpro_globalcart == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_globalcart == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="2" '. ($wcplpro_globalcart == 2 ? 'selected="selected"' : '') .'>'. __('No, but keep the buttons (all quantities greater than 0 will be added to the cart)', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_globalposition">'. __('Global Add To Cart Button Position', 'wcplpro') .'</label>
              <select name="wcplpro_globalposition" id="wcplpro_globalposition">
                <option value="bottom" '. ($wcplpro_globalposition == 'bottom' ? 'selected="selected"' : '') .'>'. __('Bottom', 'wcplpro') .'</option>
                <option value="top" '. ($wcplpro_globalposition == 'top' ? 'selected="selected"' : '') .'>'. __('Top', 'wcplpro') .'</option>
                <option value="both" '. ($wcplpro_globalposition == 'both' ? 'selected="selected"' : '') .'>'. __('Both', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            if (class_exists( 'YITH_WCQV_Frontend' )) {

              echo '
                <div class="fieldwrap">
                  <label class="vm_label" for="wcplpro_quickview">'. __('Display YITH Quick View', 'wcplpro') .'</label>
                  <small>'. __('The quick view button will be displayed below or beside the add to cart button, hence the add to cart has to be enabled for the quick view to be displayed', 'wcplpro') .'</small>
                  <select name="wcplpro_quickview" id="wcplpro_quickview">
                    <option value="no" '. ($wcplpro_quickview == 'no' ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                    <option value="simple" '. ($wcplpro_quickview == 'simple' ? 'selected="selected"' : '') .'>'. __('On all type of products but variable', 'wcplpro') .'</option>
                    <option value="variable" '. ($wcplpro_quickview == 'variable' ? 'selected="selected"' : '') .'>'. __('On variable products', 'wcplpro') .'</option>
                    <option value="all" '. ($wcplpro_quickview == 'all' ? 'selected="selected"' : '') .'>'. __('On all types of products', 'wcplpro') .'</option>
                  </select>
                </div>
                  <hr />
                ';
              
            }

            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_global_status">'. __('Global Add To Cart Button Default Status', 'wcplpro') .'</label>
              <select name="wcplpro_global_status" id="wcplpro_global_status">
                <option value="0" '. ($wcplpro_global_status == 0 ? 'selected="selected"' : '') .'>'. __('Un-Checked', 'wcplpro') .'</option>                
                <option value="1" '. ($wcplpro_global_status == 1 ? 'selected="selected"' : '') .'>'. __('Checked', 'wcplpro') .'</option>                
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_custommeta">'. __('Display Custom Meta', 'wcplpro') .'</label>
              <select name="wcplpro_custommeta" id="wcplpro_custommeta">
                <option value="1" '. ($wcplpro_custommeta == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_custommeta == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_metafield">'. __('Custom Meta Keys and Labels', 'wcplpro') .'</label>
              <small>'. __('Comma separate each key and label (no spaces). Separate keys and labels with a Vertical Pipe “|”, eg. custom_color|Color,custom_size|Size', 'wcplpro') .'</small>
              <textarea name="wcplpro_metafield" id="wcplpro_metafield">'. $wcplpro_metafield .'</textarea>
            </div>
              <hr />
            ';
            
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_attributes">'. __('Display Attributes', 'wcplpro') .'</label>
              <small>'. __('Available attributes will be displayed below the title', 'wcplpro') .'</small>
              <select name="wcplpro_attributes" id="wcplpro_attributes">
                <option value="1" '. ($wcplpro_attributes == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_attributes == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            

            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_wishlist">'. __('Display Wishlist', 'wcplpro') .'</label>
              <select name="wcplpro_wishlist" id="wcplpro_wishlist">
                <option value="1" '. ($wcplpro_wishlist == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_wishlist == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';

            
            if (is_plugin_active('woocommerce-product-gift-wrap/woocommerce-product-gift-wrap.php')) {
              echo '
              <div class="fieldwrap">
                <label class="vm_label" for="wcplpro_gift">'. __('Display Gift Wrap Option', 'wcplpro') .'</label>
                <select name="wcplpro_gift" id="wcplpro_gift">
                  <option value="1" '. ($wcplpro_gift == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                  <option value="0" '. ($wcplpro_gift == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                </select>
              </div>
                <hr />
              ';
            }
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_offer">'. __('Display Image', 'wcplpro') .'</label>
              <select name="wcplpro_offer" id="wcplpro_offer">
                <option value="1" '. ($wcplpro_offer == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_offer == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
              <div class="fieldwrap">
                <label class="vm_label" for="wcplpro_image">'. __('Add Image', 'wcplpro') .'</label>';
                wcplpro_media_upload('wcplpro_image', $wcplpro_image);
            echo '</div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_desc">'. __('Display Excerpt', 'wcplpro') .'</label>
              <select name="wcplpro_desc" id="wcplpro_desc">
                <option value="1" '. ($wcplpro_desc == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_desc == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="2" '. ($wcplpro_desc == 2 ? 'selected="selected"' : '') .'>'. __('Show full description', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_excerpt_length">'. __('Excerpt max length in characters', 'wcplpro') .'</label>
              <input type="text" name="wcplpro_excerpt_length" id="wcplpro_excerpt_length" value="'. $wcplpro_excerpt_length .'">
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_desc_inline">'. __('Display Excerpt Inline', 'wcplpro') .'</label>
              <select name="wcplpro_desc_inline" id="wcplpro_desc_inline">
                <option value="1" '. ($wcplpro_desc_inline == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_desc_inline == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '</div>'; // end of columns-tab
            
            echo '<div class="tab-content" id="uiux-tab">';
            echo '<div class="padding"></div>';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_head">'. __('Display Table Head', 'wcplpro') .'</label>
              <select name="wcplpro_head" id="wcplpro_head">
                <option value="1" '. ($wcplpro_head == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_head == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_head">'. __('Manually close the notification panel', 'wcplpro') .'</label>
              <select name="wcplpro_panel_manualclose" id="wcplpro_panel_manualclose">
                <option value="0" '. ($wcplpro_panel_manualclose == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="1" '. ($wcplpro_panel_manualclose == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_head">'. __('Hide global button total', 'wcplpro') .'</label>
              <select name="wcplpro_hide_global_total" id="wcplpro_hide_global_total">
                <option value="0" '. ($wcplpro_hide_global_total == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="1" '. ($wcplpro_hide_global_total == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_head">'. __('Do not link product title to product single page', 'wcplpro') .'</label>
              <select name="wcplpro_dont_link_to_product" id="wcplpro_dont_link_to_product">
                <option value="0" '. ($wcplpro_dont_link_to_product == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="1" '. ($wcplpro_dont_link_to_product == 1 ? 'selected="selected"' : '') .'>'. __('Yes, remove the link', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_sorting">'. __('Enable Sorting', 'wcplpro') .'</label> 
              <small>'. __('It will have strange results if you will also add description per variation', 'wcplpro') .'</small>
              <select name="wcplpro_sorting" id="wcplpro_sorting">
                <option value="0" '. ($wcplpro_sorting == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="1" '. ($wcplpro_sorting == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_lightbox">'. __('Enable Image Pop Up', 'wcplpro') .'</label> 
              <select name="wcplpro_lightbox" id="wcplpro_lightbox">
                <option value="0" '. ($wcplpro_lightbox == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="1" '. ($wcplpro_lightbox == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_ajax">'. __('Enable AJAX', 'wcplpro') .'</label>
              <small>'. __('Enabling this will disable the stock quantity check when adding to cart via the plugin', 'wcplpro') .'</small>
              <select name="wcplpro_ajax" id="wcplpro_ajax">
                <option value="1" '. ($wcplpro_ajax == 1 ? 'selected="selected"' : '') .'>'. __('Yes', 'wcplpro') .'</option>
                <option value="0" '. ($wcplpro_ajax == 0 ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_order_direction">'. __('Order Direction', 'wcplpro') .'</label>
              <select name="wcplpro_order_direction" id="wcplpro_order_direction">
                <option value="desc" '. ($wcplpro_order_direction == 'desc' ? 'selected="selected"' : '') .'>'. __('Descending', 'wcplpro') .'</option>
                <option value="asc" '. ($wcplpro_order_direction == 'asc' ? 'selected="selected"' : '') .'>'. __('Ascending', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            $orderby_values = wcplpro_orderby_values();
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_orderby">'. __('Order By', 'wcplpro') .'</label>
              <select name="wcplpro_orderby" id="wcplpro_orderby">
              ';
              foreach($orderby_values as $order_key => $order_text) {
                echo '<option value="'. $order_key .'" '. ($wcplpro_orderby == $order_key ? 'selected="selected"' : '') .'>'. $order_text .'</option>';
              }
              
                
            echo '
              </select>
            </div>
              <hr />
            ';
            
            do_action('wcplpro_after_extra_options');
            
            echo '</div>'; // end of uiux-tab
            
            echo '<div class="tab-content" id="pagination-filters-tab">';
            echo '<div class="padding"></div>';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_pagination">'. __('Display Pagination', 'wcplpro') .'</label>
              <select name="wcplpro_pagination" id="wcplpro_pagination">
                <option value="no" '. ($wcplpro_pagination == 'no' ? 'selected="selected"' : '') .'>'. __('No', 'wcplpro') .'</option>
                <option value="before" '. ($wcplpro_pagination == 'before' ? 'selected="selected"' : '') .'>'. __('Before the list', 'wcplpro') .'</option>
                <option value="after" '. ($wcplpro_pagination == 'after' ? 'selected="selected"' : '') .'>'. __('After the list', 'wcplpro') .'</option>
                <option value="both" '. ($wcplpro_pagination == 'both' ? 'selected="selected"' : '') .'>'. __('Both, before and after the list', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_posts_per_page">'. __('Products per page', 'wcplpro') .'</label>
              <input type="number" name="wcplpro_posts_per_page" id="wcplpro_posts_per_page" value="'. $wcplpro_posts_per_page .'" min="1">
            </div>
              <hr />
            ';
            
            
            do_action('wcplpro_after_pagination_settings');
            
            
            
            echo '<h3>'. __('Filters Settings', 'wcplpro') .'</h3>';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_filter_cat">
                <input type="checkbox" name="wcplpro_filter_cat" id="wcplpro_filter_cat" value="yes" '. ($wcplpro_filter_cat == 'yes' ? 'checked' : '')  .'>
                '. __('Enable Caregories Dropdown Filter', 'wcplpro') .'
              </label>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_filter_tag">
                <input type="checkbox" name="wcplpro_filter_tag" id="wcplpro_filter_tag" value="yes" '. ($wcplpro_filter_tag == 'yes' ? 'checked' : '')  .'>
                '. __('Enable Tags Dropdown Filter', 'wcplpro') .'
              </label>
            </div>
              <hr />
            ';
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_filter_search">
                <input type="checkbox" name="wcplpro_filter_search" id="wcplpro_filter_search" value="yes" '. ($wcplpro_filter_search == 'yes' ? 'checked' : '')  .'>
                '. __('Enable Search Filter', 'wcplpro') .'
              </label>
            </div>
              <hr />
            ';
            
            
            echo '
            <div class="fieldwrap">
              <label class="vm_label" for="wcplpro_filters_position">'. __('Filters Position', 'wcplpro') .'</label>
              <select name="wcplpro_filters_position" id="wcplpro_filters_position">
                <option value="no" '. ($wcplpro_filters_position == 'no' ? 'selected="selected"' : '') .'>'. __('Hide', 'wcplpro') .'</option>
                <option value="before" '. ($wcplpro_filters_position == 'before' ? 'selected="selected"' : '') .'>'. __('Before the list', 'wcplpro') .'</option>
                <option value="after" '. ($wcplpro_filters_position == 'after' ? 'selected="selected"' : '') .'>'. __('After the list', 'wcplpro') .'</option>
                <option value="both" '. ($wcplpro_filters_position == 'both' ? 'selected="selected"' : '') .'>'. __('Both, before and after the list', 'wcplpro') .'</option>
              </select>
            </div>
              <hr />
            ';
            
            
            do_action('wcplpro_after_filters_settings');
            
            echo '</div>'; // end of pagination-filters-tab
            
            
            echo '<div class="tab-content" id="ordering-tab">';
            echo '<div class="padding"></div>';
            
            $orderfields = wcplpro_sortable();
            
            if (!empty($wcplpro_order)) { $orderfields = array_merge($wcplpro_order, $orderfields); }            
            
            echo '
            <div class="fieldwrap">
              <h3><label class="vm_label" for="wcplpro_order">'. __('Order Columns', 'wcplpro') .'</label></h3>
              <small>'. __('Drag and drop the below list elements to order the columns of the table. Fill in the text box to override the column\'s name.', 'wcplpro') .'</small>
              <ul id="colsort">
            ';
            foreach($orderfields as $field => $fieldtext) {
              echo '
                <li>&#8597; <input type="hidden" name="wcplpro_order['. $field .']" value="'. $fieldtext .'" />
                  <span class="drag_title">'. $fieldtext .'</span>
                  <input class="custom_title_input" type="text" name="wcplpro_columns_names['. $field .']" value="'. $wcplpro_columns_names[$field].'" />
                </li>';
            }
            echo '
              </ul>
            </div>';        
            
            
            echo '</div>'; // end of ordering-tab
              
        ?>
        <div class="fieldwrap">
          <?php submit_button(__('Save Changes on All Tabs', 'wcplpro')); ?>
        </div>
      </form>
    </div> <!-- leftpanel end -->
    
    <?php if ($hidebar !=1) { ?>
    <div class="rightpanel">
      <div class="hideright"><a href="admin.php?page=Productstable&hidebar=1" title="<?php _e('Hide this sidebar forever', 'wcplpro'); ?>"><?php _e('Hide this sidebar forever', 'wcplpro'); ?> <span>&times;</span></a></div>
      <br />
      <hr />
      <div class="clearfix clear helpwrap">
        <div class="half standout someair">
          <div>Do you like this plugin?<br />
            <a href="https://codecanyon.net/item/woocommerce-products-list-pro/reviews/17893660" target="_blank">Rate it!</a> <span class="rate_stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span></div>
        </div>
        <div class="half standout someair">
          <div>Having problems?<br />
          <a href="https://codecanyon.net/item/woocommerce-products-list-pro/17893660/comments" target="_blank">We are here to help!</a></div>
        </div>
      </div>
      <hr />
      
      
      <h3><?php _e('More plugins to enhance your eshop', 'wcplpro'); ?></h3>
      
      <hr />
      
      <div class="plugitem">
        <h3>
          <a href="https://codecanyon.net/item/woocommerce-variations-to-table-grid/10494620" title="Woocommerce Products to Table - Grid" target="_blank">Woocommerce Products to Table - Grid</a>
        </h3>
        <a href="https://codecanyon.net/item/woocommerce-variations-to-table-grid/10494620" title="Woocommerce Products to Table - Grid" target="_blank" class="img">
          <img src="<?php echo WCPLPRO_URI; ?>/images/inlines/vartable-inline-preview-image.png" width="590" height="300" alt="Woocommerce Products to Table - Grid"/>
        </a>
      </div>
      
      
      <hr />
      
      <div class="plugitem">
        <h3>
          <a href="https://codecanyon.net/item/cart-to-quote-for-woocommerce/17477111" title="Cart to Quote for Woocommerce" target="_blank">Cart to Quote for Woocommerce</a>
        </h3>
        <a href="https://codecanyon.net/item/cart-to-quote-for-woocommerce/17477111" title="Cart to Quote for Woocommerce" target="_blank" class="img">
          <img src="<?php echo WCPLPRO_URI; ?>/images/inlines/woo-cart-to-quote-inline.png" width="590" height="300" alt="Cart to Quote for Woocommerce"/>
        </a>
      </div>
      
      
      <hr />
      
      <div class="plugitem">
        <h3>
          <a href="http://codecanyon.net/item/woocommerce-export-products-to-xls/9307040" title="Woocommerce Export Products to XLS" target="_blank">Woocommerce Export Products to XLS</a>
        </h3>
        <a href="http://codecanyon.net/item/woocommerce-export-products-to-xls/9307040" title="Woocommerce Export Products to XLS" target="_blank" class="img">
          <img src="<?php echo WCPLPRO_URI; ?>/images/inlines/wooxls-inline-preview-image.png" width="590" height="300" alt="Woocommerce Export Products to XLS"/>
        </a>
      </div>
      
      <hr />
      
      <div class="plugitem">
        <h3>
          <a href="https://codecanyon.net/item/woocommerce-xml-csv-feeds/19674505" title="Woocommerce XML - CSV Feeds" target="_blank">Woocommerce XML - CSV Feeds</a>
        </h3>
        <a href="https://codecanyon.net/item/woocommerce-xml-csv-feeds/19674505" title="Woocommerce XML - CSV Feeds" target="_blank" class="img">
          <img src="<?php echo WCPLPRO_URI; ?>/images/inlines/woo-feeds-inline-preview-image.png" width="590" height="300" alt="Woocommerce XML - CSV Feeds"/>
        </a>
      </div>
      
      <hr />
      
      <div class="plugitem">
        <h3>
          <a href="http://codecanyon.net/item/woocommerce-lowest-price-match/12156217" title="Woocommerce Lowest Price Match" target="_blank">Woocommerce Lowest Price Match</a>
        </h3>
        <a href="http://codecanyon.net/item/woocommerce-lowest-price-match/12156217" title="Woocommerce Lowest Price Match" target="_blank" class="img">
          <img src="<?php echo WCPLPRO_URI; ?>/images/inlines/wbpm-inline-preview-image.png" width="590" height="300" alt="Woocommerce Lowest Price Match"/>
        </a>
      </div>
            
    </div> <!-- rightpanel end -->
    <?php } ?>
  </div>

    <script>
      jQuery(document).ready(function(){
        jQuery("select").select2({ width: '100%' });
        jQuery( "#colsort" ).sortable();
        
        jQuery(document).on( 'click', '.nav-tab-wrapper a', function() {
          
          jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
          jQuery(this).addClass('nav-tab-active');
          
          jQuery('.tab-content').hide();
          jQuery('#'+ jQuery(this).attr('data-tab')).show();
          return false;
        });
        
      });
    </script>
  <?php
}
?>