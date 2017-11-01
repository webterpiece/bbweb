<?php

class DHVCWooCommerce {
	
	
	public function __construct() {
		
	}
	public function init() {
		if(defined('WPB_VC_VERSION') && function_exists('vc_add_param')){
			add_action('vc_admin_inline_editor',array($this,'enqueue_iniline_script'));
			$params_script = DHVC_WOO_URL.'assets/js/params.js';
			add_shortcode_param ( 'dhvc_woo_field_products_ajax', 'dhvc_woo_setting_field_products_ajax',$params_script);
			add_shortcode_param ( 'dhvc_woo_field_exclude_products_ajax', 'dhvc_woo_setting_field_products_ajax',$params_script);
			add_shortcode_param ( 'dhvc_woo_field_id', 'dhvc_woo_setting_field_id');
			add_shortcode_param ( 'dhvc_woo_field_categories', 'dhvc_woo_setting_field_categories');
			add_shortcode_param ( 'dhvc_woo_field_exclude_categories', 'dhvc_woo_setting_field_categories');
			add_shortcode_param ( 'dhvc_woo_field_tags', 'dhvc_woo_setting_field_tags');
			add_shortcode_param ( 'dhvc_woo_field_exclude_tags', 'dhvc_woo_setting_field_tags');
			add_shortcode_param ( 'dhvc_woo_field_brands', 'dhvc_woo_setting_field_brands');
			add_shortcode_param ( 'dhvc_woo_field_exclude_brands', 'dhvc_woo_setting_field_brands');
			add_shortcode_param ( 'dhvc_woo_field_attributes', 'dhvc_woo_setting_field_attributes');
			add_shortcode_param ( 'dhvc_woo_field_heading', 'dhvc_woo_setting_field_heading');

			require_once DHVC_WOO_DIR.'/includes/map.php';
		}
		
		add_shortcode('dhvc_woo_products',array(&$this,'dhvc_woo_products_shortcode'));
		
	}
	
	public function enqueue_iniline_script(){
		wp_enqueue_script('dhvc-woo-iniline',DHVC_WOO_URL.'assets/js/iniline.js',array('vc_inline_build_js'),DHVC_WOO_VERSION,true);
	}

	protected function _get_upsell_product($query_args){
		global $product;
		$upsells = $product->get_upsells();
		if ( sizeof( $upsells ) == 0 ) return array();
		$query_args['post__in']= $upsells;
		$query_args['post__not_in'] = array($product->id);
		return $query_args;
	}
	
	protected function _get_related_product($query_args){
		global $product;
		$related = $product->get_related( $query_args['posts_per_page'] );
		if ( sizeof( $related ) == 0 ) return array();
		$query_args['post__in']= $related;
		$query_args['post__not_in'] = array($product->id);
		return $query_args;
	}
	
	protected function _get_crosssell_product($query_args){
		global $woocommerce,$product;
		
		$crosssells =$woocommerce->cart->get_cross_sells();
		
		if(is_product())
			$crosssells = $product->get_cross_sells();
		
		if ( sizeof( $crosssells ) == 0 ) return array();
		
		$query_args['post__in']= $crosssells;
		$query_args['post__not_in'] = array($product->id);
		return $query_args;
	}
	public function get_extraclass(){
		$output = '';
		if ( $el_class != '' ) {
			$output = " " . str_replace(".", "", $el_class);
		}
		return esc_attr($output);
	}
	
	
	protected function get_woo_attribute_taxonomies(){
		$attribute_array = array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		if ( $attribute_taxonomies ):
			foreach ( $attribute_taxonomies as $tax ):
				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ):
					$attribute_array[ $tax->attribute_name ] = $tax->attribute_name;
				endif;
			endforeach;
		endif;
		return $attribute_array;
	}

	public function dhvc_woo_products_shortcode($atts, $content = null){
		
		extract ( shortcode_atts ( array (
				'id' => '',
				'heading' => '',
				'heading_color'=>'#47A3DA',
				'heading_font_size'=>'20px',
				'query_options' => '',
				'query_type'=>'1',
				'products'=>'',
				'exclude_products'=>'',
				'category' => '',
				'exclude_category'=>'',
				'brand'=>'',
				'exclude_brand'=>'',
				'tag'=>'',
				'exclude_tag'=>'',
				'attribute'=>'',
				'posts_per_page' => '6',
				'post_per_row' => '2',
				'show' => '',
				'orderby' => 'date',
				'order' => 'ASC',
				'hide_free' => '',
				'show_hidden' => '',
				'style_options' => '',
				'display' => 'grid',
				'hide_result_count'=>'',
				'hide_ordering_list'=>'',
				'show_grid_pagination' => '',
				'show_masonry_filter' => '1',
				'masonry_filters_background' => '#ffffff',
				'masonry_filters_selected_background' => '#47A3DA',
				'masonry_filters_color' => '#666666',
				'masonry_filters_selected_color' => '#ffffff',
				'masonry_filters_border_color' => '#ffffff',
				'masonry_filters_selected_border_color' => '#47A3DA',
				'masonry_gutter' => 10,
				'hide_carousel_arrows' => '1',
				'show_carousel_pagination' => '',
				'carousel_arrow_background' => '#CFCDCD',
				'carousel_arrow_hover_background' => '#47A3DA',
				'carousel_arrow_color' => '#ffffff',
				'carousel_arrow_hover_color' => '#ffffff',
				'carousel_arrow_size' => '24px',
				'carousel_arrow_front_size' => '12px',
				'carousel_arrow_border_radius' => '3px',
				'carousel_pagination_background' => '#869791',
				'carousel_pagination_active_background' => '#47A3DA',
				'carousel_pagination_size' => '12px',
				'item_border_style' => 'solid',
				'item_border_color' => '#e1e1e1',
				'item_border_width' => '1px',
				'row_separator_color' => '#e1e1e1',
				'row_separator_height' => '20px',
				'row_separator_border_style' => 'solid',
				'hide_thumbnail' => '1',
				'thumbnail_background_color' => '#ffffff',
				'thumbnail_border_style' => 'solid',
				'thumbnail_border_color' => '#e1e1e1',
				'thumbnail_border_width' => '1px',
				'thumbnail_border_radius' => '3px',
				'thumbnail_padding' => '0',
				'thumbnail_margin' => '0',
				'thumbnail_width' => '',
				'thumbnail_height' => '',
				'hide_title' => '1',
				'title_align' => 'center',
				'title_color' => '#47A3DA',
				'title_hover_color' => '#98D2F7',
				'title_font_size' => '14px',
				'title_padding' => '0',
				'title_margin' => '0',
				'show_excerpt' => '',
				'excerpt_length' => '15',
				'excerpt_align' => '',
				'excerpt_color' => '#333333',
				'excerpt_font_size' => '12px',
				'excerpt_padding' => '0',
				'excerpt_margin' => '0',
				'hide_price' => '1',
				'price_color' => '#47A3DA',
				'no_discount_price_color' => '#333333',
				'price_font_size' => '14px',
				'no_discount_price_font_size' => '12px',
				'price_padding' => '0',
				'price_margin' => '0',
				'hide_addtocart' => '1',
				'addtocart_text' => 'Add to cart',
				'addtocart_color' => '',
				'addtocart_font_size' => '14px',
				'addtocart_padding' => '0',
				'addtocart_margin' => '0',
				'show_rating' => '1',
				'show_sale_flash'=>'1',
				'el_class' => '' 
		), $atts ) );
		$inline_style = '

#'.$id.' .dhvc-woo-heading {
	color:' . $heading_color . ';
	font-size:' . $heading_font_size . ';
}
			
#'.$id.' .dhvc-woo-item {
	border: '.$item_border_width.' '.$item_border_style.' '.$item_border_color.';
}
#'.$id.' .dhvc-woo-separator{
	border-top-color:' . $row_separator_color . ';
	margin-top:' . (absint ( $row_separator_height ) / 2) . 'px;
	margin-bottom:' . (absint ( $row_separator_height ) / 2) . 'px;
	border-top-style:' . $row_separator_border_style . ';
}
#'.$id.' .dhvc-woo-images{
	background-color:' . $thumbnail_background_color . ';	
}
#'.$id.' .dhvc-woo-images img{
	border-style:' . $thumbnail_border_style . ';
	border-width:' . $thumbnail_border_width . ';
	border-color:' . $thumbnail_border_color . ';
	border-radius:' . $thumbnail_border_radius . ';
	-webkit-border-radius:' . $thumbnail_border_radius . ';
	padding:' . $thumbnail_padding . ';
	margin:' . $thumbnail_margin . ';
}
#'.$id.' .dhvc-woo-title{
	text-align:' . $title_align . ';
	padding:'.$title_padding.';
	margin:'.$title_margin.';		
}		
#'.$id.' .dhvc-woo-title a{
	color:' . $title_color . ';
	font-size:' . $title_font_size . ';
				
}
#'.$id.' .dhvc-woo-title a:hover{
	color:' . $title_hover_color . '
}
#'.$id.' .dhvc-woo-excerpt{
	text-align:' . $excerpt_align . ';
	color:'.$excerpt_color.';
	font-size:'.$excerpt_font_size.';
	padding:'.$excerpt_padding.';
	margin:'.$excerpt_margin.';
}
#'.$id.' .dhvc-woo-price{
	padding:'.$price_padding.';
	margin:'.$price_margin.';			
}
#'.$id.' .dhvc-woo-price .amount,
#'.$id.' .dhvc-woo-price ins .amount{
	color:'.$price_color.';
	font-size:'.$price_font_size.';
	
}
#'.$id.' .dhvc-woo-price del .amount{
	color:'.$no_discount_price_color.';
	font-size:'.$no_discount_price_font_size.'
}
#'.$id.' .dhvc-woo-addtocart{
	padding:'.$addtocart_padding.';
	margin:'.$addtocart_margin.';
}
#'.$id.' .dhvc-woo-addtocart a{
	color:'.$addtocart_color.';
	font-size:'.$addtocart_font_size.';
}
#'.$id.' .dhvc-woo-masonry-list .dhvc-woo-masonry-item{
	margin-bottom:'.absint($masonry_gutter).'px;
}
#'.$id.' .dhvc-woo-filters a {
	border-color:'.$masonry_filters_border_color.';
    background-color:'.$masonry_filters_background.';
    color:'.$masonry_filters_color.';
    				
}
#'.$id.' .dhvc-woo-filters a.selected,
#'.$id.' .dhvc-woo-filters a:hover{
	background-color:'.$masonry_filters_selected_background.';
	color:'.$masonry_filters_selected_color.';
	border-color:'.$masonry_filters_selected_border_color.';
}
#'.$id.' .dhvc-woo-carousel-arrows a{
	background:'.$carousel_arrow_background.';
	width:'.$carousel_arrow_size.';
	height:'.$carousel_arrow_size.';
	border-radius:'.$carousel_arrow_border_radius.';
	-webkit-border-radius:'.$carousel_arrow_border_radius.';
}
#'.$id.' .dhvc-woo-carousel-arrows a:hover{
	background:'.$carousel_arrow_hover_background.';
}	
#'.$id.' .dhvc-woo-carousel-arrows a i{
	color:'.$carousel_arrow_color.';
	font-size:'.$carousel_arrow_front_size.';
}
#'.$id.' .dhvc-woo-carousel-arrows a:hover i{
	color:'.$carousel_arrow_hover_color.';
}						
#'.$id.' .owl-controls .owl-page span{
	width:'.$carousel_pagination_size.';
	height:'.$carousel_pagination_size.';
	background:'.$carousel_pagination_background.';
}
			
#'.$id.' .owl-controls .owl-page.active span,
#'.$id.' .owl-controls.clickable .owl-page:hover span{
	background:'.$carousel_pagination_active_background.';
}
';
		
if(defined('YITH_WCWL')){
$label = apply_filters( 'yith_wcwl_button_label', get_option( 'yith_wcwl_add_to_wishlist_text' ) );
$br = apply_filters( 'yith-wcwl-browse-wishlist-label', __( 'Browse Wishlist', 'yit' ) );
$inline_style .= '#'.$id.' .dhvc-woo-images .add_to_wishlist:after{
	content:"'.$label.'";
}	
#'.$id.' .dhvc-woo-images .yith-wcwl-wishlistexistsbrowse a:after,
#'.$id.' .dhvc-woo-images .yith-wcwl-wishlistaddedbrowse a:after{
	content:"'.$br.'";
}
';
}
		

		
		//wp_enqueue_style('dhvc-woo');
		global $woocommerce,$product,$wp_the_query;
		$posts_per_page      	= absint( $posts_per_page );
		$show        			= sanitize_title( $show );
		$orderby    		 	= sanitize_title( $orderby );
		$order       			= sanitize_title( $order );
		
		
		if( is_front_page() ) {
			$paged = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1; 
		} else { 
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 ; 
		}
		
		$query_args = array(
				'paged' 			=> $paged,
				'posts_per_page' 	=> $posts_per_page,
				'post_status' 	 	=> 'publish',
				'post_type' 	 	=> 'product',
				'offset'		 	=>	0,
				'order'          	=> $order == 'asc' ? 'ASC' : 'DESC'
		);
		
		if($query_type == '1'){
			if(!empty($products)){
				$products_ids = explode(',',$products);
				$query_args ['post__in'] = $products_ids;
			}
			if(!empty($exclude_products)){
				$exclude_products_ids =  explode(',',$exclude_products);
				$query_args [' post__not_in'] = $exclude_products_ids;
			}
			$categories = array();
			if(is_product()){
				$cat_terms = get_the_terms($product->id, 'product_cat');
				if($cat_terms){
					foreach ($cat_terms as $cat){
						$categories[] = $cat->term_id;
					}
				}
			}
			if(!empty($category)){
				
				$category = str_replace('-1', implode(',', $categories), $category);
				$query_args['tax_query'][] =
						array(
								'taxonomy'			=> 'product_cat',
								'field'				=> 'id',
								'terms'				=> explode(',',$category),
								'operator'			=> 'IN'
						);
			
				
			}
			if(!empty($exclude_category)){
				$exclude_category = str_replace('-1', implode(',', $categories), $exclude_category);
				$query_args['tax_query'][] =
						array(
								'taxonomy'			=> 'product_cat',
								'field'				=> 'id',
								'terms'				=> explode(',',$exclude_category),
								'operator'			=> 'NOT IN'
						);
				
			}
			if(!empty($brand)){
				$query_args['tax_query'][] =
				array(
						'taxonomy'			=> 'product_brand',
						'field'				=> 'id',
						'terms'				=> explode(',',$brand),
						'operator'			=> 'IN'
				);
			}
			if(!empty($exclude_brand)){
				$query_args['tax_query'][] =
				array(
						'taxonomy'			=> 'product_brand',
						'field'				=> 'id',
						'terms'				=> explode(',',$exclude_brand),
						'operator'			=> 'NOT IN'
				);
			}
			
			if(!empty($tag)){
				$query_args['tax_query'][] =
						array(
								'taxonomy'			=> 'product_tag',
								'field'				=> 'id',
								'terms'				=> explode(',',$tag),
								'operator'			=> 'IN'
						);
			}
			if(!empty($exclude_tag)){
				$query_args['tax_query'][] =
						array(
								'taxonomy'			=> 'product_tag',
								'field'				=> 'id',
								'terms'				=> explode(',',$exclude_tag),
								'operator'			=> 'NOT IN'
						);
			}
			
			if(!empty($attribute)){
				$attribute_arr = explode(',', $attribute);
				$t = array();
				foreach ($attribute_arr as $attr){
					$attr_arr = explode('|', $attr);
					$t[$attr_arr[0]][] = $attr_arr[1];
					
				}
				if(!empty($t)){
					foreach ($t as $tarr){
						$query_args['tax_query'][] =
							array(
									'taxonomy'			=> $attr_arr[0],
									'field'				=> 'slug',
									'terms'				=> $tarr,
									'operator'			=> 'IN'
							);
					}
				}
			}
		}
		if($query_type == 'upsell'){
			$query_args = $this->_get_upsell_product($query_args);
			if(empty($query_args))
				return ;
		}
		if($query_type == 'related'){
			$query_args = $this->_get_related_product($query_args);
			if(empty($query_args))
				return ;
		}
		if($query_type == 'crosssell'){
			$query_args = $this->_get_crosssell_product($query_args);
			if(empty($query_args))
				return ;
		}
		
		$query_args['meta_query'] = array();
		
		if ( empty( $show_hidden ) ) {
			$query_args['meta_query'][] = array(
									    'key'     => '_visibility',
									    'value'   => array( 'visible', 'catalog' ),
									    'compare' => 'IN'
									);
			$query_args['post_parent']  = 0;
		}
		
		if ( ! empty( $hide_free ) ) {
			$query_args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
			);
		}
		
		$stock_status = array();
		if ( get_option( 'woocommerce_hide_out_of_stock_items' ) == 'yes' ) {
			$stock_status = array(
					'key' 		=> '_stock_status',
					'value' 	=> 'instock',
					'compare' 	=> '='
			);
		}

		$query_args['meta_query'][] = $stock_status;
		$query_args['meta_query']   = array_filter( $query_args['meta_query'] );
		
		switch ( $show ) {
			case 'featured' :
				$query_args['meta_query'][] = array(
				'key'   => '_featured',
				'value' => 'yes'
						);
						break;
			case 'onsale' :
				$product_ids_on_sale = wc_get_product_ids_on_sale();
				$product_ids_on_sale[] = 0;
				$query_args['post__in'] = $product_ids_on_sale;
				break;
		}
		if(isset($_GET['orderby']) && !empty($_GET['orderby'])){
			$orderby = sanitize_text_field($_GET['orderby']);
		}
		switch ( $orderby ) {
			case 'title':
				$query_args['orderby']  = 'title';
				break;
			case 'modified':
				$query_args['orderby']  = 'modified';
				break;
			case 'comment_count':
				$query_args['orderby']  = 'comment_count';
			break;
			case 'popularity' :
				$args['meta_key'] = 'total_sales';
				// Sorting handled later though a hook
				add_filter( 'posts_clauses', array( $woocommerce->query, 'order_by_popularity_post_clauses' ) );
				break;
			case 'rating' :
				// Sorting handled later though a hook
				add_filter( 'posts_clauses', array( $woocommerce->query, 'order_by_rating_post_clauses' ) );
				break;
			case 'date' :
				$query_args['orderby']  = 'date';
				$query_args['order']    = 'ASC' ? 'ASC' : 'DESC';
				break;
			case 'rand' :
				$query_args['orderby']  = 'rand';
				break;
			case 'sales' :
				$query_args['meta_key'] = 'total_sales';
				$query_args['orderby']  = 'meta_value_num';
				break;
			case 'price':
				$query_args['meta_key'] = '_price';
				$query_args['orderby'] = 'meta_value_num';
				$query_args['order'] = 'asc';
				break;
			case 'price-desc':
				$query_args['meta_key'] = '_price';
				$query_args['orderby'] = 'meta_value_num';
				$query_args['order'] = 'desc';
				break;
			default :
				 $ordering_args = $woocommerce->query->get_catalog_ordering_args($orderby, $order);
                 $query_args['orderby'] = $ordering_args['orderby'];
                 $query_args['order'] =$ordering_args['order'];
            break;
		}
		
		$r = new WP_Query( $query_args );
		
		$output = '';
		if(defined('WPB_VC_VERSION') && function_exists('vc_add_param')){
			if(!dhvc_is_editor() && !dhvc_is_editor()){
				//wp_add_inline_style('dhvc-woo',$inline_style);
				$output .= '<style type="text/css">'.$inline_style.'</style>';
			}else{
				$output .= '<style type="text/css">'.$inline_style.'</style>';
			}
		}else{
			//wp_add_inline_style('dhvc-woo',$inline_style);
			$output .= '<style type="text/css">'.$inline_style.'</style>';
		}
// 		wp_enqueue_style('dhvc-woo-font-awesome');
// 		wp_enqueue_style('dhvc-woo-chosen');
// 		wp_enqueue_style('dhvc-woo');
		
		$output .= '<div id="' . $id . '" class="woocommerce dhvc-woo ' . $el_class . '">';
		if (! empty ( $heading )) :
			$output .= '<div class="dhvc-woo-heading">';
			$output .= $heading;
			$output .= '</div>';
		endif;
		
		if ($display == 'masonry' && ! empty ( $show_masonry_filter )) :
			$output .= '<div class="dhvc-woo-filters dhvc-woo-clearfix">';
			$output .= '<ul data-option-key="filter">';
			$output .= '<li>';
			$output .= '<a class="dhvc-woo-filter selected" href="#" data-option-value="*">' . __ ( 'All', DHVC_WOO ) . '</a>';
			$output .= '</li>';
			
			$product_cats = array ();
			if ($r->have_posts ()) {
				while ( $r->have_posts () ) :
					$r->the_post ();
					global $product;
					$cats = get_the_terms ( $product->id, 'product_cat' );
					if (! empty ( $cats )) {
						foreach ( $cats as $cat ) {
							$product_cats [$cat->slug] = $cat->name;
						}
					}
				endwhile
				;
				
				foreach ( $product_cats as $slug => $name ) {
					
					$output .= '<li>';
					$output .= '<a href="#" class="dhvc-woo-filter" data-option-value= ".' . $slug . '">' . $name . '</a>';
					$output .= '</li>';
				}
				wp_reset_postdata ();
				wp_reset_query();
			}
			
			$output .= '</ul>';
			$output .= '</div>';
		endif;
	
		if ($display == 'carousel' && ! empty ( $hide_carousel_arrows ) && ($r->post_count > $post_per_row) ) :
			$output .= '<div class="dhvc-woo-carousel-arrows dhvc-woo-clearfix">';
			$output .= '<a href="#" class="dhvc-woo-carousel-prev"><i class="fa fa-chevron-left"></i></a>';
			$output .= '<a href="#" class="dhvc-woo-carousel-next"><i class="fa fa-chevron-right"></i></a>';
			$output .= '</div>';
		endif;
		if( $display != 'carousel' && $display != 'masonry'){
			if(empty($hide_result_count) || empty($hide_ordering_list)){
			$output .= '<div class="dhvc-woo-row-fluid dhvc-woo-toolbar">';
			if(empty($hide_result_count)){
				ob_start();
				dhvc_result_count($r);
				$output .= ob_get_clean();
			}
			
			if(empty($hide_ordering_list)){
				ob_start();
				dhvc_woo_orderby($orderby);
				$output .= ob_get_clean();
			}
			$output .= '</div>';
			
			}
		}
		$output .= '<div class="dhvc-woo-row-fluid dhvc-woo-' . $display . '-list"' . ($display == 'masonry' ? ' data-masonry-gutter="' . absint ( $masonry_gutter ) . '"' : '') . '' . ($display == 'carousel' ? ' data-items="' . absint ( $post_per_row ) . '"' : '').($display == 'carousel' && ! empty ( $show_carousel_pagination ) ? ' data-pagination="true"' : '') . '>';
		
		if ($r->have_posts ()) {
			
			$i = 0;
			while ( $r->have_posts () ) :
				$r->the_post ();
				global $product,$post;
				$cats = get_the_terms ( $product->id, 'product_cat' );
				$product_cats = array ();
				if (! empty ( $cats )) {
					foreach ( $cats as $cat ) {
						$product_cats [] = $cat->slug;
					}
				}
				
				if ($display == 'grid') :
					if ($i ++ % $post_per_row == 0) :
						$output .= '<div class="dhvc-woo-row-fluid">';
					
					endif;
				endif;
				
				$output .= '<div class="dhvc-woo-item';
				if ($display != 'carousel' && $display != 'list') :
					$output .= ' dhvc-woo-span' . (12 / absint ( $post_per_row ));
				endif;
				$output .= ' dhvc-woo-' . $display . '-item ' . implode ( ' ', $product_cats );
				
				$output .= '">';
				
				if (! empty ( $hide_thumbnail )) :
					$output .= '<div class="dhvc-woo-images">';
					if(defined('YITH_WCWL')){
						$output .= do_shortcode('[yith_wcwl_add_to_wishlist]');
					}
					if(!empty($show_sale_flash) && $product->is_on_sale()):
						ob_start();
						woocommerce_show_product_loop_sale_flash();
						$output .= ob_get_clean();
					endif;
					if(function_exists('dhwcpl_shop_loop_item')){
						ob_start();
						dhwcpl_shop_loop_item();
						$output .= ob_get_clean();
					}
					if (has_post_thumbnail ()) {
						$image_title = esc_attr ( get_the_title ( get_post_thumbnail_id () ) );
						$image_link = esc_url ( get_permalink () );
// 						$image = get_the_post_thumbnail ( $product->id, apply_filters ( 'single_product_large_thumbnail_size', 'shop_single' ), array (
// 								'title' => $image_title 
// 						) );
						$thumb_size = 'shop_single';
						if(!empty($thumbnail_height) && !empty($thumbnail_width)){
							$thumb_size = $thumbnail_width.'x'.$thumbnail_height;
						}
						$thumbnail_data = dhvc_woo_getImageBySize(array( 'post_id' => $product->id, 'thumb_size' => $thumb_size ));
						$thumbnail_image = $thumbnail_data['thumbnail'];
						$output .= '<a href="'.$image_link.'" itemprop="image" title="'.$image_title.'" >'.$thumbnail_image.'</a>';
// 						$attachment_count = count ( $product->get_gallery_attachment_ids () );
						
// 						if ($attachment_count > 0) {
// 							$gallery = '[product-gallery]';
// 						} else {
// 							$gallery = '';
// 						}
						
// 						$output .= apply_filters ( 'woocommerce_single_product_image_html', sprintf ( '<a href="%s" itemprop="image" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $image_link, $image_title, $image ), $product->id );
 					} else {
						$output .=  apply_filters ( 'woocommerce_single_product_image_html', sprintf ( '<a href="%s" itemprop="image" title="%s"><img src="%s" alt="Placeholder" /></a>', esc_url ( get_permalink () ), $image_title, wc_placeholder_img_src () ), $product->id );
					}
					if(is_product()){
						if(function_exists('dhwcpl_single_product')){
							ob_start();
							dhwcpl_single_product();
							$output .= ob_get_clean();
						}
					}else{
						if(function_exists('dhwcpl_shop_loop_item')){
							ob_start();
							dhwcpl_shop_loop_item();
							$output .= ob_get_clean();
						}
					}
					
					$output .= '</div>';
				
				endif;
				
				$output .= '<div class="dhvc-woo-info"';
				$image_config_size = absint($thumbnail_width);
				if(empty($image_config_size)){
					$shop_single_image_arr  = wc_get_image_size('shop_single');
					$image_config_size = $shop_single_image_arr['width'];
				}
				$image_config_size = $image_config_size + absint($thumbnail_border_width) + absint($thumbnail_border_width);
				if($display == 'list'){
					$output .= 'style="width: calc(100% - '.$image_config_size.'px);"';
				}
				$output .= '>';
				if (! empty ( $hide_title )) :
					$output .= '<h2 class="dhvc-woo-title dhvc-woo-clearfix">';
					$output .= '<a href="' . get_permalink () . '">'.the_title ('','',false) . '</a>';
					$output .= '</h2>';
				
				endif;
				if (! empty ( $show_excerpt )) :
					$output .= '<div class="dhvc-woo-excerpt">';
					$output .= wp_trim_words ($post->post_excerpt, $excerpt_length );
					$output .= '</div>';
				
				endif;
				if (! empty ( $show_rating )) :
					$output .= '<div class="woocommerce dhvc-woo-rating dhvc-woo-clearfix">';
					$rating = $product->get_average_rating();
					$rating = absint($rating);
					$rating_html  = '<div class="star-rating" title="' . sprintf( __( 'Rated %s out of 5', 'woocommerce' ), $rating ) . '">';
					$rating_html .= '<span style="width:' . ( ( $rating / 5 ) * 100 ) . '%"><strong class="rating">' . $rating . '</strong> ' . __( 'out of 5', 'woocommerce' ) . '</span>';
					$rating_html .= '</div>';
					$output .=$rating_html;
					$output .= '</div>';
					
				
				endif;
				
				$output .= '<div class="dhvc-woo-extra dhvc-woo-row-fluid">';
					if($display == 'list'){
						if (! empty ( $hide_price )) :
							$output .= '<div class="dhvc-woo-price">';
							$output .= $product->get_price_html ();
							$output .= '</div>';
						endif;
						
						if (! empty ( $hide_addtocart )) :
							$output .= '<div class="dhvc-woo-addtocart">';
							$output .= '<a href="' . do_shortcode ( '[add_to_cart_url id="' . $product->id . '"]' ) . '">';
							$output .= '<i class="fa fa-shopping-cart"></i>';
							$output .= $addtocart_text;
							$output .= '</a>';
							$output .= '</div>';
						endif;
					}else{
						if (! empty ( $hide_addtocart )) :
							$output .= '<div class="dhvc-woo-addtocart dhvc-woo-span6">';
							$output .= '<a href="' . do_shortcode ( '[add_to_cart_url id="' . $product->id . '"]' ) . '">';
							$output .= '<i class="fa fa-shopping-cart"></i>';
							$output .= $addtocart_text;
							$output .= '</a>';
							$output .= '</div>';
						endif;
						
						if (! empty ( $hide_price )) :
							$output .= '<div class="dhvc-woo-price dhvc-woo-span6">';
							$output .= $product->get_price_html ();
							$output .= '</div>';
						endif;
					}
				$output .= '</div>';
				
				$output .= '</div>';
				$output .= '</div>';
				if ($display == 'grid') :
					if ($i % $post_per_row == 0 || $i == $r->post_count) :
						$output .= '</div>';
						$output .= '<div class="dhvc-woo-separator"></div>';
					endif;
				endif;
			endwhile
			;
			if (! empty ( $show_grid_pagination )) :
				ob_start();
				dhvc_woo_pagination(array('echo'=>1),$r);
				$output .= ob_get_clean();
			endif;
		}
		$output .= '</div>';
		$output .= '</div>';
		wp_reset_postdata ();
		wp_reset_query ();
				
		return $output;
	}
}