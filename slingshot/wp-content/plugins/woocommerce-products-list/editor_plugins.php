<?php


add_action('admin_head', 'wcplpro_add_editor_button');
        
add_action( 'wp_ajax_wcplpro_insert_dialog', 'wcplpro_insert_dialog' );


function wcplpro_add_editor_button() {
  if ( get_user_option('rich_editing') == 'true' && current_user_can('edit_posts')) {
    add_filter('mce_buttons', 'wcplpro_register_buttons', 10);
    add_filter('mce_external_plugins', 'wcplpro_register_tinymce_javascript', 10);
  }
  
  return;
}
function wcplpro_insert_dialog() {
  
  // get all columns
  $columns = wcplpro_sortable();
  $column_keys = array_keys($columns);
  
  // get woo categories
  $terms = get_categories( array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false
  ) );
  
  // get woo tags
  $tags = get_categories( array(
    'taxonomy' => 'product_tag',
    'hide_empty' => false
  ) );
  
  $out = '
  <div class="wcplpro_box_wrap">
  <table class="wp-list-table widefat fixed striped table options_table" id="wcplpro_shorcode_table" data-columns="'. implode(',', $column_keys) .'">
    <tr>
      <td colspan="2">
      <h3>'. __('Filters', 'wcplpro') .'</h3>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_keyword-l" class="mce-widget mce-label mce-first" for="wcplpro_keyword" aria-disabled="false">'. __('Search term', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_keyword" id="wcplpro_keyword" value="">
      </td>
    </tr>
  
    <tr>
      <td class="first_column">
        <label id="wcplpro_categories_inc-l" class="mce-widget mce-label mce-first" for="wcplpro_categories_inc" aria-disabled="false">'. __('Include Categories', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select id="wcplpro_categories_inc" name="wcplpro_categories_inc" class="wcprpro-enhanced-select enhanced" multiple  data-placeholder="'. __('Select or type to search', 'wcplpro') .'">
          
  ';
  
  if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    foreach ( $terms as $term ) {
      $out .= '<option value="'. $term->term_id .'">'. $term->name .'</option>';
    }
  }
  
  $out .= '
        </select>
      </td>
    </tr>
    
    <tr>
      <td>
        <label id="wcplpro_categories_exc-l" class="mce-widget mce-label mce-first" for="wcplpro_categories_exc" aria-disabled="false">'. __('Exculde Categories', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select id="wcplpro_categories_exc" name="wcplpro_categories_exc" class="wcprpro-enhanced-select enhanced" multiple  data-placeholder="'. __('Select or type to search', 'wcplpro') .'">
          
  ';
  
  if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    foreach ( $terms as $term ) {
      $out .= '<option value="'. $term->term_id .'">'. $term->name .'</option>';
    }
  }
  
  $out .= '</select>
      </td>
    </tr>
    
    <tr>
      <td>
        <label id="wcplpro_categories_inc-l" class="mce-widget mce-label mce-first" for="wcplpro_tag_inc" aria-disabled="false">'. __('Include Tags', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select id="wcplpro_tag_inc" name="wcplpro_tag_inc" class="wcprpro-enhanced-select enhanced" multiple  data-placeholder="'. __('Select or type to search', 'wcplpro') .'">
          
  ';
  
  if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
    foreach ( $tags as $tag ) {
      $out .= '<option value="'. $tag->term_id .'">'. $tag->name .'</option>';
    }
  }
  
  $out .= '
        </select>
      </td>
    </tr>
    
    
    <tr>
      <td>
        <label id="wcplpro_tag_exc-l" class="mce-widget mce-label mce-first" for="wcplpro_tag_exc" aria-disabled="false">'. __('Exculde Tags', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select id="wcplpro_tag_exc" name="wcplpro_tag_exc" class="wcprpro-enhanced-select enhanced" multiple  data-placeholder="'. __('Select or type to search', 'wcplpro') .'">
          
  ';
  
  if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
    foreach ( $tags as $tag ) {
      $out .= '<option value="'. $tag->term_id .'">'. $tag->name .'</option>';
    }
  }
  
  $out .= '</select>
      </td>
    </tr>
    
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_posts_inc-l" class="mce-widget mce-label mce-first" for="wcplpro_posts_inc" aria-disabled="false">'. __('Include Products by ID', 'wcplpro').'<br /><small>'.__('(enter IDs eg. 14,22,73)', 'wcplpro') .'</small></label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_posts_inc" id="wcplpro_posts_inc" value="">
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_posts_exc-l" class="mce-widget mce-label mce-first" for="wcplpro_posts_exc" aria-disabled="false">'. __('Exclude Products by ID', 'wcplpro') .'<br /><small>'.__('(enter IDs eg. 14,22,73)', 'wcplpro') .'</small></label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_posts_exc" id="wcplpro_posts_exc" value="">
      </td>
    </tr>
    
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_posts_sku_inc-l" class="mce-widget mce-label mce-first" for="wcplpro_posts_sku_inc" aria-disabled="false">'. __('Include Products by SKU', 'wcplpro').'<br /><small>'.__('(enter SKUs to include)', 'wcplpro') .'</small></label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_posts_sku_inc" id="wcplpro_posts_sku_inc" value="">
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_posts_sku_exc-l" class="mce-widget mce-label mce-first" for="wcplpro_posts_sku_exc" aria-disabled="false">'. __('Exclude Products by SKU', 'wcplpro') .'<br /><small>'.__('(enter SKUs to exclude)', 'wcplpro') .'</small></label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_posts_sku_exc" id="wcplpro_posts_sku_exc" value="">
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_filter_cat-l" class="mce-widget mce-label mce-first" for="wcplpro_filter_cat" aria-disabled="false">'. __('Enable categories dropdown filter', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_filter_cat" id="wcplpro_filter_cat">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="no">'. __('Disable', 'wcplpro') .'</option>
          <option value="yes">'. __('Enable', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_filter_tag-l" class="mce-widget mce-label mce-first" for="wcplpro_filter_tag" aria-disabled="false">'. __('Enable tags dropdown filter', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_filter_tag" id="wcplpro_filter_tag">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="no">'. __('Disable', 'wcplpro') .'</option>
          <option value="yes">'. __('Enable', 'wcplpro') .'</option>
        </select>
      </td>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_filter_search-l" class="mce-widget mce-label mce-first" for="wcplpro_filter_search" aria-disabled="false">'. __('Enable search filter', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_filter_search" id="wcplpro_filter_search">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="no">'. __('Disable', 'wcplpro') .'</option>
          <option value="yes">'. __('Enable', 'wcplpro') .'</option>
        </select>
      </td>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_filters_position-l" class="mce-widget mce-label mce-first" for="wcplpro_filters_position" aria-disabled="false">'. __('Filters Position', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_filters_position" id="wcplpro_filters_position">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="no">'. __('Hide', 'wcplpro') .'</option>
          <option value="before">'. __('Before the list', 'wcplpro') .'</option>
          <option value="after">'. __('After the list', 'wcplpro') .'</option>
          <option value="both">'. __('Both, before and after the list', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_pagination-l" class="mce-widget mce-label mce-first" for="wcplpro_pagination" aria-disabled="false">'. __('Enable pagination', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_pagination" id="wcplpro_pagination">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="no">'. __('No', 'wcplpro') .'</option>
          <option value="before">'. __('Before the list', 'wcplpro') .'</option>
          <option value="after">'. __('After the list', 'wcplpro') .'</option>
          <option value="both">'. __('Both, before and after the list', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_posts_per_page-l" class="mce-widget mce-label mce-first" for="wcplpro_posts_per_page" aria-disabled="false">'. __('Products per page', 'wcplpro') .'</label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_posts_per_page" id="wcplpro_posts_per_page" value="">
      </td>
    </tr>
  
    <tr>
      <td colspan="2">
      <h3>'. __('Sorting', 'wcplpro') .'</h3>
      </td>
    </tr>   

    
    <tr>
      <td>
        <label id="wcplpro_order_direction-l" class="mce-widget mce-label mce-first" for="wcplpro_order_direction" aria-disabled="false">
          '. __('Order Direction', 'wcplpro') .'
        </label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_order_direction" id="wcplpro_order_direction">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="desc" >'. __('Descending', 'wcplpro') .'</option>
          <option value="asc" >'. __('Ascending', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
    
    
    <tr>
      <td>
        <label id="wcplpro_orderby-l" class="mce-widget mce-label mce-first" for="wcplpro_orderby" aria-disabled="false">
          '. __('Order By', 'wcplpro') .'
        </label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_orderby" id="wcplpro_orderby">
          <option value="">'. __('Default', 'wcplpro') .'</option>
      ';
            
      $orderby_values = wcplpro_orderby_values();

      foreach($orderby_values as $order_key => $order_text) {
        $out .= '<option value="'. $order_key .'" '. ($orderby == $order_key ? 'selected="selected"' : '') .'>'. $order_text .'</option>';
      }

  $out .= '
        </select>
      </td>
    </tr>
    
    
    <tr>
      <td colspan="2">
      <h3>'. __('Columns', 'wcplpro') .'</h3>
      </td>
    </tr>

    ';
  
  
  
  foreach($columns as $key => $name) {
    
    $out .= '<tr>
      <td>
      <label id="'. $key .'-l" class="mce-widget mce-label mce-first" for="'. $key .'" aria-disabled="false">
        '. $name .'
      </label>
      </td>
      <td class="setting_value">
        <select name="'. $key .'" id="'. $key .'">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="0">'. __('Hide', 'wcplpro') .'</option>
          <option value="1">'. __('Show', 'wcplpro') .'</option>
          '. ($key == 'wcplpro_globalcart' ? '<option value="2">'.__('Hide, but keep the buttons', 'wcplpro').'</option>' : '') .'
        </select>
      </td>
    </tr>';
  }
  
  
  
  if (class_exists( 'YITH_WCQV_Frontend' )) {

    $out .= '
      <tr>
        <td>
          <label id="wcplpro_quickview-l" class="mce-widget mce-label mce-first" for="wcplpro_quickview" aria-disabled="false">
            '. __('YITH Quick View', 'wcplpro') .'
          </label>
        </td>
        <td class="setting_value">
          <select name="wcplpro_quickview" id="wcplpro_quickview">
            <option value="">'. __('Default', 'wcplpro') .'</option>
            <option value="no">'. __('Hide', 'wcplpro') .'</option>
            <option value="simple">'. __('On all product types but variable', 'wcplpro') .'</option>
            <option value="variable">'. __('On variable products', 'wcplpro') .'</option>
            <option value="all">'. __('On all types of products', 'wcplpro') .'</option>
          </select>
         </td>
      </tr>
      ';
    
  }
  

    $out .= '
    
    <tr>
      <td colspan="2">
      <h3>'. __('Settings', 'wcplpro') .'</h3>
      </td>
    </tr>
    
    
    <tr>
      <td>
        <label id="wcplpro_globalposition-l" class="mce-widget mce-label mce-first" for="wcplpro_globalposition" aria-disabled="false">
          '. __('Global Add To Cart Button Position', 'wcplpro') .'
        </label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_globalposition" id="wcplpro_globalposition">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="bottom">'. __('Bottom', 'wcplpro') .'</option>
          <option value="top">'. __('Top', 'wcplpro') .'</option>
          <option value="both">'. __('Both', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
    
    <tr>
      <td>
        <label id="wcplpro_global_status-l" class="mce-widget mce-label mce-first" for="wcplpro_global_status" aria-disabled="false">
          '. __('Global Add To Cart Button Status', 'wcplpro') .'
        </label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_global_status" id="wcplpro_global_status">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="0" >'. __('Un-Checked', 'wcplpro') .'</option>                
          <option value="1" >'. __('Checked', 'wcplpro') .'</option>  
        </select>
        
        <input type="hidden" id="wcplid" name="wcplid" value="'. uniqid() .'" />
      </td>
    </tr>
    
    <tr>
      <td class="first_column">
        <label id="wcplpro_metafield" class="mce-widget mce-label mce-first" for="wcplpro_metafield" aria-disabled="false">'. __('Custom meta', 'wcplpro').'<br /><small>'.__('(Comma separated, no spaces)', 'wcplpro') .'</small></label>
      </td>
      <td class="setting_value">
        <input type="text" name="wcplpro_metafield" id="wcplpro_metafield" value="">
      </td>
    </tr>
    
    <tr>
      <td>
        <label id="wcplpro_attributes-l" class="mce-widget mce-label mce-first" for="wcplpro_attributes" aria-disabled="false">
          '. __('Display Attributes', 'wcplpro') .'
        </label>
      </td>
      <td class="setting_value">
        <select name="wcplpro_attributes" id="wcplpro_attributes">
          <option value="">'. __('Default', 'wcplpro') .'</option>
          <option value="0">'. __('No', 'wcplpro') .'</option>
          <option value="1">'. __('Yes', 'wcplpro') .'</option>
        </select>
      </td>
    </tr>
        
  </table>
  </div>
  
  <style>
    .select2-search-choice-close { 
     display: block;
      width: 12px;
      height: 13px;
      position: absolute;
      right: 7px;
      top: 6px;
      font-size: 1px;
      outline: 0;
      background: url('. WCPLPRO_URI .'/images/select2.png) right top no-repeat;
    }
    
    .first_column small { font-size: 12px; color: #999; }
    
    .wcplpro_box_wrap { max-height: 398px; overflow-y: auto; }
    table.options_table h3 { font-size: 18px; font-weight: bold; }
    table.options_table {
      width: 100%;
      border: 0;
      -webkit-box-shadow: none;
      box-shadow: none;
    }
    table.options_table td {
      vertical-align: middle;
    }
    table.options_table td.first_column {
      width: 47%;
    }
    table.options_table td input[type="text"] {
      width: 94%;
      padding: 3px 5px;
    }
  </style>
  ';
  
  die($out);
}


function wcplpro_register_buttons($buttons) {
  array_push($buttons, 'separator', 'wcplpro');
  return $buttons;
}

function wcplpro_register_tinymce_javascript($plugin_array) {
  $plugin_array['wcplpro'] = plugins_url('/assets/js/wcplpro-editor-plugin.js',__FILE__);
  return $plugin_array;
}

// add_action('admin_footer', 'wcplpro_admin_footer_function');
function wcplpro_admin_footer_function() {
	?>
  <script>
    jQuery(document).ready(function() {
      jQuery(".wcprpro-enhanced-select").select2({
          allowClear: true
      });
    });
  </script>
  <?php
}


?>