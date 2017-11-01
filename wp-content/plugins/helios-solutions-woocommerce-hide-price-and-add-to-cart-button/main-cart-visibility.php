<?php

/**
 * Plugin Name: Helios Solutions WooCommerce Hide Price and Add to Cart button
 * Plugin URI: http://heliossolutions.in/
 * Description: A plugin use for Hide price and add to cart button for woocommerce site.
 * Author: heliossolutions
 * Author URI: http://heliossolutions.in/
 * Version: 2.0.2
 * Compatible with woo commerce: 2.3.3
 *
 */
 if ( ! defined( 'ABSPATH' ) ) {
    echo "Hi there! Nice try. Come again.";
    die();
}
class HS_WC_visibility_Tab {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function hshidecart_init() {
       
        add_filter('woocommerce_settings_tabs_array', __CLASS__ . '::hshidecart_add_settings_tab', 50);
        add_action('woocommerce_settings_tabs_settings_tab_visibility', __CLASS__ . '::hshidecart_settings_tab');
        add_action('woocommerce_update_options_settings_tab_visibility', __CLASS__ . '::hshidecart_update_settings');
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 2);
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 1);
        add_action('after_setup_theme', __CLASS__ . '::hshidecart_activate_filter', 53);
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function hshidecart_add_settings_tab($settings_tabs) {
        $settings_tabs['settings_tab_visibility'] = __('Visibility', 'woocommerce-settings-tab-visibility');
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function hshidecart_settings_tab() {
        woocommerce_admin_fields(self::hshidecart_get_settings());
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function hshidecart_update_settings() {
        woocommerce_update_options(self::hshidecart_get_settings());
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function hshidecart_get_settings() {

        $settings = array(
            'section_title' => array(
                'name' => __('Hide Price and Cart button section', 'woocommerce-settings-tab-visibility'),
                'type' => 'title',
                'desc' => '',
                'id' => 'wc_settings_tab_visibility_section_title'
            ),
            'title' => array(
                'name' => __('Product Price', 'woocommerce-settings-tab-visibility'),
                'type' => 'checkbox',
                'desc' => __('Product price disable login user and guest user ', 'woocommerce-settings-tab-visibility'),
                'id' => 'wc_settings_tab_visibility_title'
            ),
            'product' => array(
                'name' => __('Product price', 'woocommerce-settings-tab-visibilitypro'),
                'type' => 'checkbox',
                'desc' => __('Product price option disable only guest user(non logged users)', 'woocommerce-settings-tab-visibilitypro'),
                'id' => 'wc_settings_tab_product_price_disable_product'
            ),
            'cart_button' => array(
                'name' => __('Add to cart button', 'woocommerce-settings-tab-visibilitypro'),
                'type' => 'checkbox',
                'desc' => __('Add to cart button disable for login user and guest user', 'woocommerce-settings-tab-visibilitypro'),
                'id' => 'wc_settings_tab_product_cart_button'
            ),
            'add_to_cart' => array(
                'name' => __('Add to cart button ', 'woocommerce-settings-tab-visibilitypro'),
                'type' => 'checkbox',
                'desc' => __('Add to cart button disable only guest user(non logged users)', 'woocommerce-settings-tab-visibilitypro'),
                'id' => 'wc_settings_tab_product_add_to_cart'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_tab_demo_section_end'
            )
        );

        return apply_filters('wc_settings_tab_demo_settings', $settings);
    }

    function hshidecart_activate_filter() {
        $pice_option = get_option('wc_settings_tab_visibility_title');
        add_filter('woocommerce_get_price_html', __CLASS__ . '::hshidecart_show_price_logged');
    }

    function hshidecart_show_price_logged($price) {
        $pice_option = get_option('wc_settings_tab_visibility_title');
        $disable_product_price = get_option('wc_settings_tab_product_price_disable_product');
        $cart_button = get_option('wc_settings_tab_product_cart_button');
        $add_to_cart = get_option('wc_settings_tab_product_add_to_cart');




        /* Add to cart button disable for non logged users and logged users */
        if (is_user_logged_in()) {

            if ($cart_button == 'yes') {
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            }
        } else {
            if ($add_to_cart == 'yes') {
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            } else if ($cart_button == 'yes') {
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            }
        }

        /* disable product price option  login user and non logged user  */
        if (is_user_logged_in()) {
            if ($pice_option == 'yes') {
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            } else {
                return $price;
            }
        } else {
            if ($disable_product_price == 'yes') {
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            } else if ($pice_option == 'yes') {
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
            } else {
                return $price;
            }
        }
    }

}

HS_WC_visibility_Tab::hshidecart_init();

/*
 * Settings link on plugin activation page
 */

function hshidecart_plugin_add_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=wc-settings&tab=settings_tab_visibility">' . __( 'Settings' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );

add_filter( "plugin_action_links_$plugin", 'hshidecart_plugin_add_settings_link' );

// add jQuery UI
function hshidecart_jquery_ui() {
    wp_register_style('jquery_ui', plugin_dir_url(__FILE__) . 'css/hs-jquery-ui-timepicker-addon.css');
    wp_enqueue_style('jquery_ui');
    wp_enqueue_script('jquery-time-picker', plugin_dir_url(__FILE__) . 'js/jquery-ui-timepicker-addon.js', array('jquery'));
    wp_enqueue_script('custom', plugin_dir_url(__FILE__) . 'js/custom_backend.js', array('jquery'));
}

add_action('admin_head', 'hshidecart_jquery_ui');


function hshidecart_scripts() {
    wp_register_style('jquery_ui', plugin_dir_url(__FILE__) . 'css/custom_frontend.css');
    wp_enqueue_script('theme_name_scripts', plugin_dir_url(__FILE__) . 'js/custom_frontend.js', array('jquery'), '1.0', true);
}

add_action('wp_enqueue_scripts', 'hshidecart_scripts');

// Add Custom Product Fileds
add_action('woocommerce_product_options_general_product_data', 'hshidecart_woocommerce_custom_product_data_field');
if (!function_exists('hshidecart_woocommerce_custom_product_data_field')) {

    function hshidecart_woocommerce_custom_product_data_field() {
        global $woocommerce, $post;
        echo '<div class="options_group hs_options_group">';
        woocommerce_wp_select(
                array(
                    'id' => 'woo_disable_add_to_cart_checkbox',
                    'label' => __('Show/Hide Add to Cart Button', 'woocommerce'),
                    'options' => array(
                        'default_button' => __('none', 'woocommerce'),
                        'show_button' => __('Show', 'woocommerce'),
                        'disable_button' => __('Hide', 'woocommerce'),
                    )
                )
        );
      echo '<span class="hs_notic" id="hs-notic">Set expiration period for hiding add to cart button.</span>';  
        woocommerce_wp_text_input(
                array(
                    'id' => 'woo_disable_add_to_cart_start_date',
                    'label' => __('Start date', 'woocommerce'),
                    'type' => 'text',
                )
        );
        woocommerce_wp_text_input(
                array(
                    'id' => 'woo_disable_add_to_cart_end_date',
                    'label' => __('End date', 'woocommerce'),
                    'type' => 'text',
                )
        );
         $current_time = current_time('mysql');
        
        echo '<span class="utc_time_field" id="utc-time">Current Universal time (<abbr>UTC</abbr>) is <code>'.$current_time.'</code>.</span>';
        echo '</div>';
        
    }

}

// Save Custom Product Fields
add_action('woocommerce_process_product_meta', 'hshidecart_woo_process_product_meta_fields_save');
/**
 * Product Meta Fields Save
 * @param type $post_id
 */
if (!function_exists('hshidecart_woo_process_product_meta_fields_save')) {

    function hshidecart_woo_process_product_meta_fields_save($post_id) {
        $woocheckbox = sanitize_text_field($_POST['woo_disable_add_to_cart_checkbox']);
        $woo_disable_add_to_cart_checkbox = isset($woocheckbox) ? $woocheckbox : 'Show';
        update_post_meta($post_id, 'woo_disable_add_to_cart_checkbox', $woo_disable_add_to_cart_checkbox);

        $woocommerce_start_date_text_field = sanitize_text_field($_POST['woo_disable_add_to_cart_start_date']);
        $woocommerce_end_date_text_field = sanitize_text_field($_POST['woo_disable_add_to_cart_end_date']);
        
        update_post_meta($post_id, 'woo_disable_add_to_cart_start_date', esc_attr($woocommerce_start_date_text_field));
        update_post_meta($post_id, 'woo_disable_add_to_cart_end_date', esc_attr($woocommerce_end_date_text_field));
    }

}

//
add_action('woocommerce_after_shop_loop_item', 'hshidecart_add_custom_field_into_loop', 11);

function hshidecart_add_custom_field_into_loop() {
    global $product;
    $show_hide_option = get_post_meta($product->id, 'woo_disable_add_to_cart_checkbox', 'false');
    $start_datetime = get_post_meta($product->id, 'woo_disable_add_to_cart_start_date', 'false');
    $end_datetime = get_post_meta($product->id, 'woo_disable_add_to_cart_end_date', 'false');
    //Start time str 
    $start_dt = new DateTime($start_datetime);
    $start_set_time = $start_dt->format('Y-m-d H:i:s');
    
    //End time str
     $endtime_dt = new DateTime($end_datetime);
     $end_set_time = $endtime_dt->format('Y-m-d H:i:s');
     
     //Current server time
     $current_time = current_time('mysql');

    if ($show_hide_option == 'show_button') {
        //  
    } else if ($show_hide_option == 'disable_button') {
       //Condition login for start date and end date 
       if ((strtotime($start_set_time) <= strtotime($current_time)) && ( strtotime($current_time) <= strtotime($end_set_time) )) {
            echo '<div class="hide-add-cart"></div>';
            ?>
            <style>
                ul.products > li.post-<?php echo $product->id; ?> a.button{display:none !important}
            </style>
            <?php
        }
    }
}

add_action('woocommerce_single_product_summary', 'hshidecart_add_custom_field_into_single', 31);

function hshidecart_add_custom_field_into_single() {
    global $product;
    $show_hide_option = get_post_meta($product->id, 'woo_disable_add_to_cart_checkbox', 'false');
    $start_datetime = get_post_meta($product->id, 'woo_disable_add_to_cart_start_date', 'false');
    $end_datetime = get_post_meta($product->id, 'woo_disable_add_to_cart_end_date', 'false');
    //Start time str 
    $start_dt = new DateTime($start_datetime);
    $start_set_time = $start_dt->format('Y-m-d H:i:s');
    //End time str
    $endtime_dt = new DateTime($end_datetime);
    $end_set_time = $endtime_dt->format('Y-m-d H:i:s');
     
    //Current server time
     $current_time = current_time('mysql');
     
   if ($show_hide_option == 'show_button') {
        
    } else if ($show_hide_option == 'disable_button') {
         if ((strtotime($start_set_time) <= strtotime($current_time)) && ( strtotime($current_time) <= strtotime($end_set_time) )) {
            ?>
            <style>
                #product-<?php echo $product->id; ?> div.summary.entry-summary form.cart{display:none !important}
            </style>
            <?php
            }
        
    }
}