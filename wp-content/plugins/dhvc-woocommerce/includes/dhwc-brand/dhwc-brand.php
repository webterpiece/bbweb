<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once dirname(__FILE__).'/includes/dhwc-brand-functions.php';

if (dhwc_is_active()){
	class DHWC_Brand {
		
		public function __construct(){
			if(taxonomy_exists('product_brand'))
				return;
			add_action( 'woocommerce_register_taxonomy',array($this,'dhwc_init_taxonomy'));
			add_action( 'loop_shop_post_in', array($this,'dhwc_loop_shop_post_in_brand'), 11 );
			add_action('widgets_init',array($this,'dhwc_widget_init'));
			
			if (is_admin()){
				include_once dirname(__FILE__).'/includes/dhwc-brand-admin.php';
			}

			add_filter( 'template_include',array($this,'dhwc_template_include'));
			
			//add_filter('body_class',array($this,'dhwc_add_body_class'));
			
			if(get_option('dhwc_product_brand_show_desc') =='yes'){
				add_action( 'woocommerce_archive_description',array($this,'dhwc_show_product_brand_descs'));
			}

			add_action( 'woocommerce_product_meta_end',array($this,'dhwc_add_prouduct_brand_meta'));
		}
		
		function dhwc_init_taxonomy(){
			global $woocommerce;
			
		
		
			$permalinks 	= get_option( 'woocommerce_permalinks' );
			$shop_page_id = woocommerce_get_page_id('shop');
			$base_slug 		= $shop_page_id > 0 && get_post( $shop_page_id ) ? get_page_uri( $shop_page_id ) : 'shop';
		
			$product_brand_slug = empty( $permalinks['brand_base'] ) ? _x( 'product-brand', 'slug', DHVC_WOO ) : $permalinks['brand_base'];
		
			register_taxonomy ( 'product_brand', 
				apply_filters ( 'dhwc_taxonomy_objects_product_brand', array ('product' ) ), 
				apply_filters ( 'dhwc_taxonomy_args_product_brand', array (
					'hierarchical' => true,
					'update_count_callback' => '_update_post_term_count',
					'label' => __ ( 'Product Brands', DHVC_WOO ),
					'labels' => array (
							'name' => __ ( 'Product Brands', DHVC_WOO ),
							'singular_name' => __ ( 'Product Brand', DHVC_WOO ),
							'menu_name' => _x ( 'Brands', 'Admin menu name', DHVC_WOO ),
							'search_items' => __ ( 'Search Product Brands', DHVC_WOO ),
							'all_items' => __ ( 'All Product Brands', DHVC_WOO ),
							'parent_item' => __ ( 'Parent Product Brand', DHVC_WOO ),
							'parent_item_colon' => __ ( 'Parent Product Brand:', DHVC_WOO ),
							'edit_item' => __ ( 'Edit Product Brand', DHVC_WOO ),
							'update_item' => __ ( 'Update Product Brand', DHVC_WOO ),
							'add_new_item' => __ ( 'Add New Product Brand', DHVC_WOO ),
							'new_item_name' => __ ( 'New Product Brand Name', DHVC_WOO ) 
					),
					'show_ui' => true,
					'show_in_nav_menus' => true,
					'query_var' => true,
					'capabilities' => array (
							'manage_terms' => 'manage_product_terms',
							'edit_terms' => 'edit_product_terms',
							'delete_terms' => 'delete_product_terms',
							'assign_terms' => 'assign_product_terms' 
					),
					'rewrite' => array (
							'slug' => $product_brand_slug,
							'with_front' => false,
							'hierarchical' => true 
					) 
			) ) );
		}
		
		
		/**
		 * Filter product by brand
		 *
		 * @param $filtered_posts
		*/
		function dhwc_loop_shop_post_in_brand($filtered_posts){
			global $woocommerce, $_chosen_attributes;
			if ( is_active_widget( false, false, 'dhwc_widget_layered_nav', true ) && ! is_admin() ) {
				if ( ! empty( $_GET[ 'filter_product_brand' ] ) ) {
					$terms 	= explode( ',', $_GET[ 'filter_product_brand' ] );
		
					if ( sizeof( $terms ) > 0 ) {
		
						$_chosen_attributes['product_brand']['terms'] = $terms;
						$_chosen_attributes['product_brand']['query_type'] = 'and';
		
						$matched_products = get_posts(
								array(
										'post_type' 	=> 'product',
										'numberposts' 	=> -1,
										'post_status' 	=> 'publish',
										'fields' 		=> 'ids',
										'no_found_rows' => true,
										'tax_query' => array(
												'relation' => 'AND',
												array(
														'taxonomy' 	=> 'product_brand',
														'terms' 	=> $terms,
														'field' 	=> 'id'
												)
										)
								)
						);
		
						$woocommerce->query->layered_nav_post__in = array_merge( $woocommerce->query->layered_nav_post__in, $matched_products );
						$woocommerce->query->layered_nav_post__in[] = 0;
		
						if ( sizeof( $filtered_posts ) == 0 ) {
							$filtered_posts = $matched_products;
							$filtered_posts[] = 0;
						} else {
							$filtered_posts = array_intersect( $filtered_posts, $matched_products );
							$filtered_posts[] = 0;
						}
					}
				}
			}
			return (array) $filtered_posts;
		}
		
	
		
		/**
		 * register widget
		*/
		function dhwc_widget_init(){
			// Includes
			include_once dirname(__FILE__).'/includes/dhwc-widget-brands.php';
			include_once dirname(__FILE__).'/includes/dhwc-widget-layered-nav.php';
			include_once dirname(__FILE__).'/includes/dhwc-widget-slider-brands.php';
		
			// Register widgets
			register_widget('DHWC_Widget_Layered_Nav');
			register_widget('DHWC_Widget_Brands');
			register_widget('DHWC_Widget_Brand_Slider');
		}
		
		
		/**
		 * Load a template.
		 *
		 * Handles template usage so that we can use our own templates instead of the themes.
		 *
		 * Templates are in the 'templates' folder. woocommerce looks for theme
		 * overrides in /theme/woocommerce/ by default
		 *
		 * For beginners, it also looks for a woocommerce.php template first. If the user adds
		 * this to the theme (containing a woocommerce() inside) this will be used for all
		 * woocommerce templates.
		 *
		 * @access public
		 * @param mixed $template
		 * @return string
		 */
		function dhwc_template_include($template){
			$template_url = apply_filters( 'woocommerce_template_url', 'woocommerce/' );
			$find = array( 'woocommerce.php' );
			$file = '';
			if ( is_tax( 'product_brand' ) ) {
				$term = get_queried_object();
	
				$file 		= 'taxonomy-' . $term->taxonomy . '.php';
				$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] 	= $template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] 	= $file;
				$find[] 	= $template_url . $file;
	
			}
			if ( $file ) {
				$template = locate_template( $find );
				if ( ! $template ) $template = untrailingslashit(DHVC_WOO_DIR) . '/templates/' . $file;
			}
			return $template;
		}
		
		
		/**
		 * add class to body tag
		*/
		function dhwc_add_body_class($classes) {
			return $classes[] = 'woocommerce woocommerce-page';
		}
		/**
		 * show brand description
		*/
		function dhwc_show_product_brand_descs(){
			if (!is_tax('product_brand'))
				return;
			if (!get_query_var('term'))
				return;
				
			$thumbnail = '';
			$term = get_term_by( 'slug', get_query_var( 'term' ),'product_brand');
			$thumbnail = dhwc_get_product_brand_thumbnail_url( $term->term_id, 'shop_catalog' );
		
			?>
			<div class="term-description product-brand-desc">
					<?php if ($thumbnail){?>
					<img src="<?php echo $thumbnail; ?>" />
					<?php }?>
					<div class="text"><?php echo wpautop( wptexturize( term_description() ) ); ?></div>
			</div>
<?php
		 }
		    
		    
	    /**
	     * dhwc_add_prouduct_brand_meta function
	     */
	    function dhwc_add_prouduct_brand_meta(){
	    	global $post;
			if ( is_singular( 'product' ) ) {
				echo dhwc_get_brands( $post->ID, ', ', ' <span class="posted_in">' . __('Brand:', DHVC_WOO).' ', '.</span>' );
			}
	    }
	}
	new DHWC_Brand();
}