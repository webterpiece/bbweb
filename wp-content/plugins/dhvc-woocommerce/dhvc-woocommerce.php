<?php
/*
* Plugin Name: DHVC Woocommerce products
* Plugin URI: http://getextension.net/
* Description: DHVC Woocommerce Products Shortcodes - manage Woocommerce products
* Version: 2.1.4
* Author: DHZoanku
* Author URI: http://getextension.net/
* License: License GNU General Public License version 2 or later;
* Copyright 2014  DH Zoanku
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!defined('DHVC_WOO'))
	define('DHVC_WOO','dhvc-woocommerce');

if(!defined('DHVC_WOO_VERSION'))
	define('DHVC_WOO_VERSION','2.1.4');

if(!defined('DHVC_WOO_URL'))
	define('DHVC_WOO_URL',plugin_dir_url( __FILE__ ));

if(!defined('DHVC_WOO_DIR'))
	define('DHVC_WOO_DIR',dirname( __FILE__ ));

if (!function_exists('dhwc_is_active')){
	/**
	 * Check woocommerce plugin is active
	 *
	 * @return boolean .TRUE is active
	 */
	function dhwc_is_active(){
		$active_plugins = (array) get_option( 'active_plugins', array() );
		
		if ( is_multisite() )
			$active_plugins = array_merge($active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		
		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

global $dhvc_woo_category_page,$dhvc_woo_tag_page,$dhvc_woo_brand_page;
$dhvc_woo_category_page = $dhvc_woo_tag_page = $dhvc_woo_brand_page = 0;

if(!class_exists('DHVCWoo',false)){

	require_once DHVC_WOO_DIR.'/includes/functions.php';
	
	class DHVCWoo{

		public function __construct(){
			require_once DHVC_WOO_DIR.'/includes/dhwc-brand/dhwc-brand.php';
			
			add_action('init',array(&$this,'init'));
			add_action('wp_print_scripts',array(&$this,'print_scripts'));
			//add_action( 'admin_bar_menu',array($this,"admin_bar_menu"), 2000);
		}
		
		public function print_scripts(){
			global $post;
			wp_enqueue_style('dhvc-woo-font-awesome');
			wp_enqueue_style('dhvc-woo-chosen');
			wp_enqueue_style('dhvc-woo');
		}

		public function admin_bar_menu(){
			global $wp_admin_bar,$dhvc_woo_category_page,$dhvc_woo_tag_page,$dhvc_woo_brand_page;
			if(!empty($dhvc_woo_category_page)){
				if($this->show_button($dhvc_woo_category_page->ID)){
					$wp_admin_bar->add_menu( array(
							'id' 	=>'dhvc_woo_category_page',
							'title' => __('Edit Category Page Template', DHVC_WOO),
							'href' => admin_url().'post.php?post='.$dhvc_woo_category_page->ID.'&action=edit',
					) );
					
				}	
			}elseif (!empty($dhvc_woo_brand_page)){
				if($this->show_button($dhvc_woo_brand_page->ID)){
					$wp_admin_bar->add_menu( array(
							'id' 	=>'dhvc_woo_brand_page',
							'title' => __('Edit Brand Page Template', DHVC_WOO),
							'href' => admin_url().'post.php?post='.$dhvc_woo_brand_page->ID.'&action=edit',
					) );
				}
			}elseif (!empty($dhvc_woo_tag_page)){
				if($this->show_button($dhvc_woo_tag_page->ID)){
					$wp_admin_bar->add_menu( array(
							'id' 	=>'dhvc_woo_tag_page',
							'title' => __('Edit Tag Page Template', DHVC_WOO),
							'href' => admin_url().'post.php?post='.$dhvc_woo_tag_page->ID.'&action=edit',
					) );
				}
			}
		}
		
		public function show_button($post_id){
			global $current_user;
			get_currentuserinfo();
			if(!current_user_can('edit_post', $post_id)) return false;
			return true;
		}
		
		public function init(){
			load_plugin_textdomain( DHVC_WOO, false, basename(DHVC_WOO_DIR) . '/languages/' );

			wp_register_style('dhvc-woo-chosen', DHVC_WOO_URL.'assets/css/chosen.min.css');
			wp_register_style('dhvc-woo-font-awesome', DHVC_WOO_URL.'assets/fonts/awesome/css/font-awesome.min.css',array(),'4.0.3');
			wp_register_style('dhvc-woo', DHVC_WOO_URL.'assets/css/style.css');
			
				
			if(is_admin()){
				add_action('admin_enqueue_scripts',array(&$this,'admin_enqueue_styles'));
				add_action('admin_enqueue_scripts',array(&$this,'enqueue_scripts'));
			}else{
				add_action('wp_print_scripts',array(&$this,'enqueue_scripts'));
				add_filter( 'term_link', array($this,'term_link'), 100,10);
				add_action( 'template_redirect', array($this,'template_redirect'),1000);
				//add_filter( 'template_include', array( &$this, 'template_loader' ) ,1000);
			}
			
			if(!dhwc_is_active()){
				add_action('admin_notices', array(&$this,'woocommerce_notice'));
				return;
			}
			
			
			
			if(is_admin()){
				require_once DHVC_WOO_DIR.'/includes/admin.php';
			}
			
			require_once DHVC_WOO_DIR.'/includes/class.php';
				
			$shortcode = new DHVCWooCommerce();
			$shortcode->init();
				
		}
		

		public function notice(){
			$plugin = get_plugin_data(__FILE__);
			echo '
			  <div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/1gKaeh5" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', DHVC_WOO), $plugin['Name']) . '</p>
			  </div>';
		}
		
		public function woocommerce_notice(){
			$plugin = get_plugin_data(__FILE__);
			echo '
			  <div class="updated">
			    <p>' . sprintf(__('<strong>%s</strong> requires <strong><a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a></strong> plugin to be installed and activated on your site.', DHVC_WOO), $plugin['Name']) . '</p>
			  </div>';
		}
		
		public function admin_enqueue_styles(){
			wp_enqueue_style('dhvc-woo-chosen');
		}

		public function enqueue_scripts(){
			// JavaScript
			wp_register_script('dhvc-woo',DHVC_WOO_URL.'assets/js/script.js',array('jquery'),DHVC_WOO_VERSION,true);
			wp_enqueue_script('dhvc-woo');
		}
		
		public function template_redirect(){
			if ( is_tax( 'product_cat' )) {
				$category_slug = get_query_var('product_cat');
				$category = get_term_by('slug', $category_slug, 'product_cat');
				$category_page_id = get_woocommerce_term_meta($category->term_id,'dhvc_woo_category_page_id',true);
				if($category_page_id){
					wp_redirect(get_permalink($category_page_id));
				}
			
			}elseif (is_tax('product_brand')){
				$brand_slug = get_query_var('product_brand');
				$brand = get_term_by('slug', $brand_slug, 'product_brand');
				$brand_page_id = get_woocommerce_term_meta($brand->term_id,'dhvc_woo_brand_page_id',true);
				if($brand_page_id){
					wp_redirect(get_permalink($brand_page_id));
				}
			}
		}
		
		public function term_link( $link, $term, $taxonomy ){
			if ( $term->taxonomy === 'product_cat' ) {
				$category_page_id = get_woocommerce_term_meta($term->term_id,'dhvc_woo_category_page_id',true);
				if($category_page_id){
					return get_permalink($category_page_id);
				}
			}
			if ( $term->taxonomy=== 'product_brand' ) {
				$brand_page_id = get_woocommerce_term_meta($term->term_id,'dhvc_woo_brand_page_id',true);
				if($brand_page_id){
					return get_permalink($brand_page_id);
				}
			}
			return $link;
		}
		
		public function template_loader($template){
			global $dhvc_woo_category_page,$dhvc_woo_tag_page,$dhvc_woo_brand_page;
			if ( is_tax( 'product_cat' )) {
			
				$category_slug = get_query_var('product_cat');
				$category = get_term_by('slug', $category_slug, 'product_cat');
				$category_page_id = get_woocommerce_term_meta($category->term_id,'dhvc_woo_category_page_id',true);
				if($category_page_id){
					$dhvc_woo_category_page = get_post($category_page_id);
					$file = 'category.php';
					$find[] = 'dhvc-woocommerce/' . $file;
					$template       = locate_template( $find );
					$status_options = get_option( 'woocommerce_status_options', array() );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
						$template = DHVC_WOO_DIR . '/templates/' . $file;
						
					return $template;
				}
				
			}elseif (is_tax('product_tag')){
				$tag_slug = get_query_var('product_tag');
				$tag = get_term_by('slug', $tag_slug, 'product_tag');
				$tag_page_id = get_woocommerce_term_meta($tag->term_id,'dhvc_woo_tag_page_id',true);
				if($tag_page_id){
					$dhvc_woo_tag_page = get_post($tag_page_id);
					$file = 'tag.php';
					$find[] = 'dhvc-woocommerce/' . $file;
					$template       = locate_template( $find );
					$status_options = get_option( 'woocommerce_status_options', array() );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
						$template = DHVC_WOO_DIR . '/templates/' . $file;
				
					return $template;
				}
				
				
			}elseif (is_tax('product_brand')){
				$brand_slug = get_query_var('product_brand');
				$brand = get_term_by('slug', $brand_slug, 'product_brand');
				$brand_page_id = get_woocommerce_term_meta($brand->term_id,'dhvc_woo_brand_page_id',true);
				if($brand_page_id){
					$dhvc_woo_brand_page = get_post($brand_page_id);
					$file = 'brand.php';
					$find[] = 'dhvc-woocommerce/' . $file;
					$template       = locate_template( $find );
					$status_options = get_option( 'woocommerce_status_options', array() );
					if ( ! $template || ( ! empty( $status_options['template_debug_mode'] ) && current_user_can( 'manage_options' ) ) )
						$template = DHVC_WOO_DIR . '/templates/' . $file;
				
					return $template;
				}
			}
			return $template;
		}
		
		
	}

	new DHVCWoo();
}

