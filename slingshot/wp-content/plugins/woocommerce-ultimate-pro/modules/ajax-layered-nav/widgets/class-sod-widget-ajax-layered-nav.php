<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Ajax Layered Nav Widget
 * Ajax widget to control layaered navigation
 */
class SOD_Widget_Ajax_Layered_Nav extends WC_Widget {
/**
     * Constructor.
     */
    public function __construct() {
        add_action('woocommerce_widget_field_typeselector', array($this,'add_typeselector_field'), 10, 4);
        
        $this->widget_cssclass    = 'widget_layered_nav';
        $this->widget_description = __( 'Shows a custom attribute in a widget which lets you narrow down the list of products when viewing product categories.', 'ultimatewoo-pro' );
        $this->widget_id          = 'sod_ajax_layered_nav';
        $this->widget_name        = __('WooCommerce Ajax Layered Nav', 'ultimatewoo-pro' );
        parent::__construct();
    }
    /*
     * Widgets Fields
     * /
     */
    function add_typeselector_field($key, $value, $setting, $instance){
        if(isset($instance['display_type'])):
            $args=array(
                'hide_empty'=>'0'
            );
            $attributes = isset($instance['attribute'])? get_terms('pa_'.$instance['attribute'],$args):NULL; 
            $labels = isset( $instance['labels'] ) ? unserialize($instance['labels']) : false;
            $colors = isset( $instance['colors'] ) ? unserialize($instance['colors']) : false;
              
            ?>
            <div id="<?php echo esc_attr( $this->get_field_id('labels') ); ?>">
                
            <?php switch ( $instance['display_type'] ) {
                case "colorpicker":?>
                    <table class="color">
                       <thead>
                            <tr>
                                <td><?php _e('Name', 'ultimatewoo-pro');?></td>
                                <td><?php _e('Color Code', 'ultimatewoo-pro');?></td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($attributes):
                            foreach($attributes as $attribute){
                                if($colors){
                                    if( isset($colors[$attribute->term_id])){
                                        $term_color = esc_attr($colors[$attribute->term_id]);
                                        $value = $term_color ? $term_color :""; 
                                    }else{
                                        $value = '';
                                    }
                                }else{
                                    $value = '';
                                }?>
                                <tr>
                                   <td class="labels">
                                       <label for="
                                            <?php echo $this->get_field_name('colors');?>[<?php echo $instance['attribute'];?>]">
                                            <?php echo $attribute->name;?>
                                       </label>
                                   </td>
                                   <td class="inputs">
                                        <input 
                                            class="color_input" 
                                            type="text" 
                                            name="<?php echo $this->get_field_name('colors')?>[<?php echo $attribute->term_id; ?>]" 
                                            id="<?php echo $this->get_field_id('colors');?>[<?php echo $attribute->term_id; ?>]" 
                                            value="<?php echo $value; ?>" 
                                            size="3" 
                                            maxlength="3"
                                        />
                                        <div class="colorSelector">
                                            <div style="background-color:<?php echo $value;?>"></div>
                                        </div>
                                   </td>
                                </tr>
                        <?php    }
                         endif;?>
                        </tbody>
                    </table>            
                    <?php
                    break;
                case "sizeselector":?>
                    <table class="sizes">
                        <thead>
                            <tr>
                                <td><?php _e('Name', 'ultimatewoo-pro'); ?></td>
                                <td><?php _e('Label', 'ultimatewoo-pro'); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                     <?php if($attributes):
                        foreach($attributes as $attribute){
                            if($labels){
                                if( isset($labels[$attribute->term_id])){
                                    $label = esc_attr($labels[$attribute->term_id]);
                                    $value = $label ? $label :"";   
                                }else{
                                    $value = '';
                                }
                            }else{
                                $value = '';
                            }?>
                            <tr>
                               <td class="labels">
                                   <label for="<?php echo $this->get_field_name('labels'); ?>[<?php echo $instance['attribute'];?>]">
                                       <?php echo $attribute->name; ?>
                                   </label>
                               </td>
                               <td class="inputs">
                                   <input 
                                        type="text" 
                                        name="<?php echo $this->get_field_name('labels');?>[<?php echo $attribute->term_id; ?>]" 
                                        id="<?php echo $this->get_field_id('labels');?>[<?php echo $attribute->term_id; ?>]" 
                                        value="<?php echo $value; ?>" 
                                        size="3"
                                    />
                               </td>
                            </tr>
                        <?php }
                        endif;?>
                        </tbody>
                    </table>
                    <?php
                    break;
    
            }?>
        </div>
       <?php 
       endif;    
    }
    
    public function update( $new_instance, $old_instance ) {
        $this->init_settings();
        $instance['title']          = isset($new_instance['title']) ? strip_tags(stripslashes($new_instance['title'])) :'';
        $instance['attribute']      = stripslashes($new_instance['attribute']);
        $instance['query_type']     = stripslashes($new_instance['query_type']);
        $instance['display_type']   = stripslashes($new_instance['display_type']);
        $instance['labels']         =  isset($new_instance['labels'])?stripslashes(serialize($new_instance['labels'])) :'';
        $instance['colors']         =  isset($new_instance['colors'])?stripslashes(serialize($new_instance['colors'])) :'';
        $instance['show_count']     =  isset($new_instance['show_count'])?stripslashes($new_instance['show_count']) :'';
        $instance['hide_empty']     =  isset($new_instance['hide_empty'])?stripslashes($new_instance['hide_empty']) :'';
        
        parent::flush_widget_cache();
        return $instance;
    }
     /**
     * Outputs the settings update form.
     *
     * @see WP_Widget->form
     *
     * @param array $instance
     */
    public function form( $instance ) {
        $this->init_settings();
        parent::form( $instance );
    }
        /**
     * Init settings after post types are registered.
     */
    public function init_settings() {
        global $woocommerce;
        $attribute_array      = array();
        if(function_exists('wc_get_attribute_taxonomies')):
            $attribute_taxonomies = wc_get_attribute_taxonomies();
        else:
            $attribute_taxonomies = $woocommerce->get_attribute_taxonomies();
        endif;
        
        if ( $attribute_taxonomies ) {
            foreach ( $attribute_taxonomies as $tax ) {
                if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
                    $attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
                }
            }
        }

        $this->settings = array(
            'title' => array(
                'type'  => 'text',
                'std'   => __( 'Filter by', 'ultimatewoo-pro' ),
                'label' => __( 'Title', 'ultimatewoo-pro' )
            ),
            'attribute' => array(
                'type'    => 'select',
                'std'     => '',
                'label'   => __( 'Attribute', 'ultimatewoo-pro' ),
                'class'   => 'layered_nav_attributes',  
                'options' => $attribute_array
            ),
            'show_count'=> array(
                'type'    => 'checkbox',
                'std'     => '',
                'label'   => __( 'Show Attribute Count', 'ultimatewoo-pro' ),
            ),
            'hide_empty'=> array(
                'type'    => 'checkbox',
                'std'     => '',
                'label'   => __( 'Hide attributes if filtered count goes to zero', 'ultimatewoo-pro' ),
            ),
            'display_type' => array(
                'type'    => 'select',
                'std'     => 'list',
                'class'   => 'layered_nav_type',
                'label'   => __( 'Display type', 'ultimatewoo-pro' ),
                'options' => array(
                    'list'          => __( 'List', 'ultimatewoo-pro' ),
                    'dropdown'      => __( 'Dropdown', 'ultimatewoo-pro' ),
                    'checkbox'      => __( 'Checkbox', 'ultimatewoo-pro' ),
                    'sizeselector'  => __( 'Size Selector', 'ultimatewoo-pro'),
                    'colorpicker'   => __( 'Color Picker', 'ultimatewoo-pro')
                ),
            ),
            'types' => array(
                'type'    => 'typeselector',
                'std'     => '',
                'label'   => __( '', 'ultimatewoo-pro' ),
                //'attributes'=> $attribute_array
            
            ),
            'query_type' => array(
                'type'    => 'select',
                'std'     => 'and',
                'label'   => __( 'Query type', 'ultimatewoo-pro' ),
                'options' => array(
                    'and' => __( 'AND', 'ultimatewoo-pro' ),
                    'or'  => __( 'OR', 'ultimatewoo-pro' )
                )
            ),
        );
    }
    
    protected function ajax_layered_nav_dropdown( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
         $found = false;

        if ( $taxonomy !== $this->get_current_taxonomy() ) {
            if($show_count != ""){
                $ul_class="show-count";
            }else{
                $ul_class="";
            }
            $term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
                $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            }else{
                $_chosen_attributes = self::get_layered_nav_chosen_attributes();
            }
            $taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );    
            $link = $this->get_page_base_url( $taxonomy );
            echo '<nav>
                <div class="ajax-layered"><select class="dropdown">';
               // echo '<select class="dropdown_layered_nav_' . esc_attr( $taxonomy_filter_name ) . '">';
                echo '<option data-filter="'.urldecode(esc_url($link)).'" data-link="'.urldecode(esc_url($link)).'" value="">' . sprintf( __( 'Any %s', 'woocommerce' ), wc_attribute_label( $taxonomy ) ) . '</option>';
                
                foreach ($terms as $term) {
                    $current_values    = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
                    $option_is_set     = in_array( $term->slug, $current_values );
                    $count             = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                    // skip the term for the current archive
                    if ( $this->get_current_term_id() === $term->term_id ) {
                        continue;
                    }
                    if ( 0 < $count ) {
                        $found = true;
                    } elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
                        continue;
                    }
                    $filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
                    $current_filter = array();
                    if (!is_array($current_filter)){
                      $current_filter = array();  
                    }; 
                    $current_filter[] = $term->slug;
                    $link = $this->get_page_base_url( $taxonomy );
                    if ( ! empty( $current_filter ) ) {
                        $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
                        if ( $query_type === 'or' && ! ( 1 === sizeof( $current_filter ) && $option_is_set ) ) {
                            $link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
                        }
                    }
                    if($option_is_set ){
                        $class = 'chosen filter-selected';
                    }else{
                        $class="";
                    }
                    if ($count > 0 || $option_is_set):
                        if( $count == 0 && $hide_empty ){
                            continue;
                        }else{
                            $taxonomy_filter = str_replace( 'pa_', '', $taxonomy );
                            if($show_count !=""){
                                $name = sprintf('%s (%s)', $term->name, $count); 
                            }else{
                                $name = $term->name;
                            }
                            $selected = selected( isset( $_GET[ 'filter_' . $taxonomy_filter ] ) ? $_GET[ 'filter_' . $taxonomy_filter ] : '', $term->term_id, false ) ;
                                echo '<option data-link="'.urldecode(esc_url($link)).'" data-filter="'.urldecode(esc_url($link)).'" value="' . esc_attr( $term->slug ) . '" ' . selected( $option_is_set, true, false ) . '>' . esc_html( $name ) . '</option>';
                        }
                    endif;
                }
                echo "</select></div></nav>";
         }
        return $found;
    }
    protected function ajax_layered_nav_list( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
        $found = false;
        
        if ( $taxonomy !== $this->get_current_taxonomy() ) {
            if($show_count !=""){
                $ul_class="show-count";
            }else{
                $ul_class="";
            }
            
            $term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
                $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            }else{
                $_chosen_attributes = self::get_layered_nav_chosen_attributes();
            }
            $taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );    
            echo '<nav>
            <div class="ajax-layered"><ul>';
            foreach ($terms as $term) {
                $current_values    = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
                $option_is_set     = in_array( $term->slug, $current_values );
                $count             = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                    // skip the term for the current archive
                if ( $this->get_current_term_id() === $term->term_id ) {
                    continue;
                }
                if ( 0 < $count ) {
                    $found = true;
                } elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
                    continue;
                }
                $filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
                $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( $_GET[ $filter_name ] ) ) : array();
                $current_filter = array_map( 'sanitize_title', $current_filter );
    
                if ( ! in_array( $term->slug, $current_filter ) ) {
                    $current_filter[] = $term->slug;
                }

                $link = $this->get_page_base_url( $taxonomy );
                // Add current filters to URL.
            foreach ( $current_filter as $key => $value ) {
                // Exclude query arg for current term archive term
                if ( $value === $this->get_current_term_slug() ) {
                    unset( $current_filter[ $key ] );
                }

                // Exclude self so filter can be unset on click.
                if ( $option_is_set && $value === $term->slug ) {
                    unset( $current_filter[ $key ] );
                }
            }

            if ( ! empty( $current_filter ) ) {
                $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

                // Add Query type Arg to URL
                if ( $query_type === 'or' && ! ( 1 === sizeof( $current_filter ) && $option_is_set ) ) {
                    $link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
                }
            }

                if($option_is_set ){
                    $class = 'chosen filter-selected';
                }else{
                    $class="";
                }
                 echo '<li class="wc-layered-nav-term ' . ( $option_is_set ? 'chosen filter-selected' : '' ) . '">';

                echo ( $count > 0  ) ? '<a href="#" data-filter="'.urldecode(esc_url($link)).'" data-count="'.$count.'" data-link="'.urldecode(esc_url($link)).'">' : '<span>';
                    if( $count == 0 && $hide_empty ){
                        continue;
                    }else{
                        echo esc_html( $term->name );
                    }
                echo ( $count > 0 || $option_is_set ) ? '</a> ' : '</span> ';
                if( $show_count != ""){
                    echo apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );
                }
                echo '</li>';
                
    
            }
           echo "</ul></div></nav>";
           }
        return $found;    
    }
    protected function ajax_layered_nav_checkbox( $terms, $taxonomy, $query_type, $show_count, $hide_empty ) {
         $found = false;

        if ( $taxonomy !== $this->get_current_taxonomy() ) {
            if($show_count !="" ){
                $ul_class="show-count";
            }else{
                $ul_class="";
            }
            $term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
                $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            }else{
                $_chosen_attributes = self::get_layered_nav_chosen_attributes();
            }
            $taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );    
            echo '<nav>
            <div class="ajax-layered"><ul class="checkboxes">';
            foreach ($terms as $term) {
                $current_values    = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
                $option_is_set     = in_array( $term->slug, $current_values );
                $count             = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                // skip the term for the current archive
                if ( $this->get_current_term_id() === $term->term_id ) {
                    continue;
                }
                if ( 0 < $count ) {
                    $found = true;
                } elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
                    continue;
                }
                $filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
                $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( $_GET[ $filter_name ] ) ) : array();
                $current_filter = array_map( 'sanitize_title', $current_filter );
                if ( ! in_array( $term->slug, $current_filter ) ) {
                    $current_filter[] = $term->slug;
                }
                $link = $this->get_page_base_url( $taxonomy );
                foreach ( $current_filter as $key => $value ) {
                    // Exclude query arg for current term archive term
                    if ( $value === $this->get_current_term_slug() ) {
                        unset( $current_filter[ $key ] );
                    }

                // Exclude self so filter can be unset on click.
                    if ( $option_is_set && $value === $term->slug ) {
                        unset( $current_filter[ $key ] );
                    }
                }

                if ( ! empty( $current_filter ) ) {
                    $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
                    if ( $query_type === 'or' && ! ( 1 === sizeof( $current_filter ) && $option_is_set ) ) {
                        $link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
                    }
                }
                if($option_is_set ){
                    $class = 'chosen filter-selected';
                    $checked = 'checked="checked"';
                }else{
                    $class="";
                    $checked = '';
                }
                if( $show_count !=""){
                    $class = "chosen filter-selected show-count";
                }
                //$checked = $show_count ? $class=="chosen filter-selected show-count" ? 'checked="checked"':"" : $class=="chosen filter-selected" ? 'checked="checked"':"";
                if ($count > 0 || $option_is_set):
                    echo '<li class="'.$class.'">';
                    echo '<input type="checkbox" data-filter="'.urldecode(esc_url($link)).'" '.$checked.'  data-link="'.urldecode(esc_url($link)).'" data-count="'.$count.'" id="'.$term->name.'" name="'.$term->name.'" value="'.$term->name.'" />';
                    echo '<label for="'.$term->name.'">'.$term->name.'</label>';
                    if($show_count !=""){
                        echo ' <small class="count">'.$count.'</small>';
                    }
                    echo '</li>';
                endif;
            }
            echo "</ul></div></nav>";
         }
       return $found;
    }
    protected function ajax_layered_nav_sizeselector( $terms, $taxonomy, $query_type, $labels, $show_count, $hide_empty ) {
        $found = false;

        if ( $taxonomy !== $this->get_current_taxonomy() ) {
            
            if($show_count !=""){
                $ul_class="show-count";
            }else{
                $ul_class="";
            }
            $term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
                $_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
            }else{
                $_chosen_attributes   = self::get_layered_nav_chosen_attributes();
                
            }
            $taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
            echo '<nav>
            <div class="ajax-layered"><ul class="sizes ' . $ul_class . '">';
            foreach ($terms as $term) {
                $current_values    = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
                $option_is_set     = in_array( $term->slug, $current_values );
                $count             = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                // skip the term for the current archive
                if ( $this->get_current_term_id() === $term->term_id ) {
                    continue;
                }
                if ( 0 < $count ) {
                    $found = true;
                } elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
                    continue;
                }
                $filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
                $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( $_GET[ $filter_name ] ) ) : array();
                $current_filter = array_map( 'sanitize_title', $current_filter );
                if ( ! in_array( $term->slug, $current_filter ) ) {
                    $current_filter[] = $term->slug;
                }
                $link = $this->get_page_base_url( $taxonomy );
                foreach ( $current_filter as $key => $value ) {
                    // Exclude query arg for current term archive term
                    if ( $value === $this->get_current_term_slug() ) {
                        unset( $current_filter[ $key ] );
                    }

                // Exclude self so filter can be unset on click.
                    if ( $option_is_set && $value === $term->slug ) {
                        unset( $current_filter[ $key ] );
                    }
                }

                if ( ! empty( $current_filter ) ) {
                    $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
                    if ( $query_type === 'or' && ! ( 1 === sizeof( $current_filter ) && $option_is_set ) ) {
                        $link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
                    }
                }
                if($option_is_set ){
                    $class = 'chosen filter-selected';
                }else{
                    $class="";
                }

                if ( ( $count > 0 || $option_is_set )  ):
                    if( $count == 0 && $hide_empty ){
                        continue;
                    }else{
                        $temp_term_id = apply_filters('wc_ajax_layered_nav_sizeselector_term_id', $term->term_id);
                        
                        if( $labels[$temp_term_id] ){
                            echo '<li class="'.$class.'">';
                            echo '<a href="#" data-count="'.$count.'" data-filter="'.urldecode(esc_url($link)).'" data-link="'.urldecode(esc_url($link)).'" >';
                            echo '<div class="size-filter">'.$labels[$temp_term_id].'</div>';
                            echo '</a>';
                            if($show_count !=""){
                                echo ' <small class="count">'.$count.'</small>';
                            }
                            echo '</li>';
                        }
                    }
                endif;
        }
       echo "</ul></div></nav>";
       }
        return $found;
    }
    protected function ajax_layered_nav_colorpicker( $terms, $taxonomy, $query_type, $colors, $show_count, $hide_empty ) {
        $found = true;
        if ( $taxonomy !== $this->get_current_taxonomy() ) {
            
            if($show_count != ""){
                $ul_class="show-count";
            }else{
                $ul_class="";
            }
            $term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
            if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
                $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
            }else{
                $_chosen_attributes = self::get_layered_nav_chosen_attributes();
            }
            $taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
            
            echo '<nav>
            <div class="ajax-layered"><ul class="colors '.$ul_class.'">';
            
            foreach ($terms as $term) {
                $current_values    = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
                $option_is_set     = in_array( $term->slug, $current_values );
                $count             = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;
                // skip the term for the current archive
                if ( $this->get_current_term_id() === $term->term_id ) {
                    continue;
                }
                if ( 0 < $count ) {
                    $found = true;
                } elseif ( 'and' === $query_type && 0 === $count && ! $option_is_set ) {
                    $found = true;    
                    continue;
                }
                $filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
                $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( $_GET[ $filter_name ] ) ) : array();
                $current_filter = array_map( 'sanitize_title', $current_filter );
                if ( ! in_array( $term->slug, $current_filter ) ) {
                    $current_filter[] = $term->slug;
                }
                $link = $this->get_page_base_url( $taxonomy );
                foreach ( $current_filter as $key => $value ) {
                    // Exclude query arg for current term archive term
                    if ( $value === $this->get_current_term_slug() ) {
                        unset( $current_filter[ $key ] );
                    }

                // Exclude self so filter can be unset on click.
                    if ( $option_is_set && $value === $term->slug ) {
                        unset( $current_filter[ $key ] );
                    }
                }

                if ( ! empty( $current_filter ) ) {
                    $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );
                    if ( $query_type === 'or' && ! ( 1 === sizeof( $current_filter ) && $option_is_set ) ) {
                        $link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
                    }
                }
                if($option_is_set ){
                    $class = 'chosen filter-selected';
                }else{
                    $class="";
                }
                
                echo '<li class="'.$class.'">';
                $temp_term_id = apply_filters('wc_ajax_layered_nav_sizeselector_term_id', $term->term_id);
                if ($count>0 || $option_is_set):
                    if( $count == 0 && $hide_empty ){
                        continue;
                    }else{
                        echo '<a href="#" data-filter="'.urldecode(esc_url($link)).'" data-count="'.$count.'" data-link="'.urldecode(esc_url($link)).'" >';
                        echo '<div class="box has-count" style="background-color:'.$colors[$temp_term_id].';"></div>';
                    }
                else:
                    echo '<div class="box no-count" style="background-color:'.$colors[$temp_term_id].';"></div>';
                    
                endif;
                if ($count>0 || $option_is_set):
                    echo '</a>';
                endif;
                if($show_count !=""){
                    echo ' <small class="count">'.$count.'</small>';
                }
                echo '</li>';
            }
            echo "</ul></div></nav>";
        }
        return $found;
    }
        /**
     * Get current page URL for layered nav items.
     * @return string
     */
    protected function get_page_base_url( $taxonomy ) {
        if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
            $link = home_url();
        } elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {
            $link = get_post_type_archive_link( 'product' );
        } elseif ( is_product_category() ) {
            $link = get_term_link( get_query_var( 'product_cat' ), 'product_cat' );
        } elseif ( is_product_tag() ) {
            $link = get_term_link( get_query_var( 'product_tag' ), 'product_tag' );
        } else {
            $link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
        }

        // Min/Max
        if ( isset( $_GET['min_price'] ) ) {
            $link = add_query_arg( 'min_price', wc_clean( $_GET['min_price'] ), $link );
        }

        if ( isset( $_GET['max_price'] ) ) {
            $link = add_query_arg( 'max_price', wc_clean( $_GET['max_price'] ), $link );
        }

        // Orderby
        if ( isset( $_GET['orderby'] ) ) {
            $link = add_query_arg( 'orderby', wc_clean( $_GET['orderby'] ), $link );
        }

        // Search Arg
        if ( get_search_query() ) {
            $link = add_query_arg( 's', get_search_query(), $link );
        }

        // Post Type Arg
        if ( isset( $_GET['post_type'] ) ) {
            $link = add_query_arg( 'post_type', wc_clean( $_GET['post_type'] ), $link );
        }

        // Min Rating Arg
        if ( isset( $_GET['min_rating'] ) ) {
            $link = add_query_arg( 'min_rating', wc_clean( $_GET['min_rating'] ), $link );
        }

        // All current filters
        if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
            $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
        }else{
            $_chosen_attributes = self::get_layered_nav_chosen_attributes();
        }
        if ( $_chosen_attributes  ) {
            foreach ( $_chosen_attributes as $name => $data ) {
                if ( $name === $taxonomy ) {
                    continue;
                }
                $filter_name = sanitize_title( str_replace( 'pa_', '', $name ) );
                if ( ! empty( $data['terms'] ) ) {
                    $link = add_query_arg( 'filter_' . $filter_name, implode( ',', $data['terms'] ), $link );
                }
                if ( 'or' == $data['query_type'] ) {
                    $link = add_query_arg( 'query_type_' . $filter_name, 'or', $link );
                }
            }
        }

        return $link;
    }
	/** @see WP_Widget */
	function widget( $args, $instance ) {
        if ( ! is_post_type_archive( 'product' ) && ! is_tax( get_object_taxonomies( 'product' ) ) ) {
            return;
        }
        if(function_exists('wc_attribute_taxonomy_name')):
            $taxonomy           = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];
        else:
            $taxonomy           = isset( $instance['attribute'] ) ? $woocommerce->attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];
        endif;
        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }
        
        $labels     = (isset($instance['labels'])) ? unserialize($instance['labels']) : null;
        $colors     = (isset($instance['colors'])) ? unserialize($instance['colors']) : null;
        $show_count = (isset($instance['show_count'])) ? $instance['show_count'] : null;
        $hide_empty = (isset($instance['hide_empty'])) ? $instance['hide_empty'] : null;
        
        if(method_exists('WC_Query', 'get_layered_nav_chosen_attributes')){
           $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
        }else{
            $_chosen_attributes = self::get_layered_nav_chosen_attributes();
        }
        $query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];
        $display_type       = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];

        $get_terms_args = array( 'hide_empty' => '1' );

        $orderby = wc_attribute_orderby( $taxonomy );

		//$taxonomy 	= wc_attribute_taxonomy_name($instance['attribute']);
		
        
        switch ( $orderby ) {
            case 'name' :
                $get_terms_args['orderby']    = 'name';
                $get_terms_args['menu_order'] = false;
            break;
            case 'id' :
                $get_terms_args['orderby']    = 'id';
                $get_terms_args['order']      = 'ASC';
                $get_terms_args['menu_order'] = false;
            break;
            case 'menu_order' :
                $get_terms_args['menu_order'] = 'ASC';
            break;
        }

        $terms = get_terms( $taxonomy, $get_terms_args );
        
        if ( 0 === sizeof( $terms ) ) {
            return;
        }
        
        ob_start();
        
        $this->widget_start( $args, $instance );
       switch($display_type){
		/* List of Checkboxes */
	   	  case "checkbox":
			    $found = $this->ajax_layered_nav_checkbox($terms, $taxonomy, $query_type, $show_count, $hide_empty );			
		        break;
          case "dropdown":
                $found = $this->ajax_layered_nav_dropdown($terms, $taxonomy, $query_type, $show_count, $hide_empty );
                break;
                     
		      /*Regular List of Terms*/
		  case "list":
              $found = $this->ajax_layered_nav_list($terms, $taxonomy, $query_type, $show_count, $hide_empty );
		      break;
					/* Size Labels */
		  case "sizeselector":
              $found = $this->ajax_layered_nav_sizeselector($terms, $taxonomy, $query_type, $labels, $show_count, $hide_empty );
		      break;
					/* Color Boxes*/
		  case "colorpicker":
              $found = $this->ajax_layered_nav_colorpicker($terms, $taxonomy, $query_type, $colors, $show_count, $hide_empty );
	          break;
		}
        
		echo '<div class="clear"></div>';
        $this->widget_end( $args );
    
            // Force found when option is selected - do not force found on taxonomy attributes
        if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
            $found = true;
        }
    
        if ( ! $found ) {
            ob_end_clean();
        } else {
            echo ob_get_clean();
        }
	}

	public static function get_main_tax_query() {
        global $wp_the_query;

        $args      = $wp_the_query->query_vars;
        $tax_query = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
        
        if ( ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
            $terms = isset($args['tax_query'][0] ) ? $args['tax_query'][0]['terms'] : array( $args['term'] );  
            $tax_query[] = array(
                'taxonomy' => $args['taxonomy'],
                'terms'    => $terms,//,
                'field'    => 'slug',
            );
        }
        
        if ( ! empty( $args['product_cat'] ) ) {
            $tax_query[ 'product_cat' ] = array(
                'taxonomy' => 'product_cat',
                'terms'    => array( $args['product_cat'] ),
                'field'    => 'slug',
            );
        }

        if ( ! empty( $args['product_tag'] ) ) {
            $tax_query[ 'product_tag' ] = array(
                'taxonomy' => 'product_tag',
                'terms'    => array( $args['product_tag'] ),
                'field'    => 'slug',
            );
        }
        return $tax_query;
    }
    public static function get_main_meta_query() {
        global $wp_the_query;

        $args       = $wp_the_query->query_vars;
        $meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

        return $meta_query;
    }
    /**
     * Layered Nav Init.
     */
    public static function get_layered_nav_chosen_attributes() {
        $_chosen_attributes = '';    
        if ( ! is_array( $_chosen_attributes ) ) {
            $_chosen_attributes = array();

            if ( $attribute_taxonomies = wc_get_attribute_taxonomies() ) {
                foreach ( $attribute_taxonomies as $tax ) {
                    $attribute    = wc_sanitize_taxonomy_name( $tax->attribute_name );
                    $taxonomy     = wc_attribute_taxonomy_name( $attribute );
                    $filter_terms = ! empty( $_GET[ 'filter_' . $attribute ] ) ? explode( ',', wc_clean( $_GET[ 'filter_' . $attribute ] ) ) : array();

                    if ( empty( $filter_terms ) || ! taxonomy_exists( $taxonomy ) ) {
                        continue;
                    }

                    $query_type = ! empty( $_GET[ 'query_type_' . $attribute ] ) && in_array( $_GET[ 'query_type_' . $attribute ], array( 'and', 'or' ) ) ? wc_clean( $_GET[ 'query_type_' . $attribute ] ) : '';
                    $_chosen_attributes[ $taxonomy ]['terms']      = array_map( 'sanitize_title', $filter_terms ); // Ensures correct encoding
                    $_chosen_attributes[ $taxonomy ]['query_type'] = $query_type ? $query_type : apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
                }
            }
        }
        return $_chosen_attributes;
    }

 // class SOD_Widget_Ajax_Layered_Nav
    /**
     * Count products within certain terms, taking the main WP query into consideration.
     * @param  array $term_ids
     * @param  string $taxonomy
     * @param  string $query_type
     * @return array
     */
    protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
        global $wpdb;

       // if(method_exists('WC_Query','get_main_tax_query')){
       //     $tax_query  = WC_Query::get_main_tax_query();
            
       // }else{
        $tax_query  = self::get_main_tax_query();
        //}
        if(method_exists('WC_Query', 'get_main_meta_query')){
            $meta_query = WC_Query::get_main_meta_query();
        }else{
            $meta_query = self::get_main_meta_query();
        }
          
       if ( 'or' === $query_type ) {
            foreach ( $tax_query as $key => $query ) {
                if ( $taxonomy === $query['taxonomy'] ) {
                    unset( $tax_query[ $key ] );
                }
            }
        }
        $meta_query     = new WP_Meta_Query( $meta_query );
        $tax_query      = new WP_Tax_Query( $tax_query );
        $meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $tax_query_sql   = $tax_query->get_sql( $wpdb->posts, 'ID' );
        
        $query           = array();
        $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
        $query['from']   = "FROM {$wpdb->posts}";
        $query['join']   = "
            INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
            INNER JOIN {$wpdb->terms} AS terms USING( term_id )
            " . $tax_query_sql['join'] . $meta_query_sql['join'];

        $query['where']   = "
            WHERE {$wpdb->posts}.post_type IN ( 'product' )
            AND {$wpdb->posts}.post_status = 'publish'
            " . $tax_query_sql['where'] . $meta_query_sql['where'] . "
            AND terms.term_id IN (" . implode( ',', array_map( 'absint', $term_ids ) ) . ")
        ";

        if ( $search = WC_Query::get_main_search_query_sql() ) {
            $query['where'] .= ' AND ' . $search;
        }

        $query['group_by'] = "GROUP BY terms.term_id";
        $query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
        $query             = implode( ' ', $query );
        $results           = $wpdb->get_results( $query );

        return wp_list_pluck( $results, 'term_count', 'term_count_id' );
    }
    /**
     * Return the currently viewed taxonomy name.
     * @return string
     */
    protected function get_current_taxonomy() {
        return is_tax() ? get_queried_object()->taxonomy : '';
    }

    /**
     * Return the currently viewed term ID.
     * @return int
     */
    protected function get_current_term_id() {
        return absint( is_tax() ? get_queried_object()->term_id : 0 );
    }

    /**
     * Return the currently viewed term slug.
     * @return int
     */
    protected function get_current_term_slug() {
        return absint( is_tax() ? get_queried_object()->slug : 0 );
    }
    
}