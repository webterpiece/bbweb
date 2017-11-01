<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DHWC_Widget_Brands extends WP_Widget {
	
	/**
	 * Constructor
	 */
	public function __construct(){
		$widget_options = array( 'classname' => 'dhwc_widget_brands', 'description' => __( 'A list or dropdown of product brands.',DHVC_WOO) );
		$control_options = array();
		parent::__construct('dhwc_widget_brands',__('DHWOO Product Brands',DHVC_WOO), $widget_options, $control_options);
	}
	/**
	 * (non-PHPdoc)
	 * @see wp-includes/WP_Widget::widget()
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product brands', DHVC_WOO ) : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? true : false;
		$s = (isset($instance['show_children_only']) && $instance['show_children_only']) ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$o = isset($instance['orderby']) ? $instance['orderby'] : 'order';

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;

		$brand_args = array( 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'product_brand' );

		$brand_args['menu_order'] = false;

		if ( $o == 'order' ) {
			$brand_args['menu_order'] = 'asc';
		} else {
			$brand_args['orderby'] = 'title';

		}

		if ( $d ) {
			dhwc_product_dropdown_brands( $c, $h, 0, $o );
			?>
			<script type='text/javascript'>
			/* <![CDATA[ */
				var product_brand_dropdown = document.getElementById("dropdown_product_brand");
				function onProductBrandChange() {
					if ( product_brand_dropdown.options[product_brand_dropdown.selectedIndex].value !=='' ) {
						location.href = "<?php echo home_url(); ?>/?product_brand="+product_brand_dropdown.options[product_brand_dropdown.selectedIndex].value;
					}
				}
				product_brand_dropdown.onchange = onProductBrandChange;
			/* ]]> */
			</script>
			<?php

		} else {

			global $wp_query, $post, $woocommerce;

			$this->current_brand = false;
			$this->brand_ancestors = array();

			if ( is_tax('product_brand') ) {

				$this->current_brand = $wp_query->queried_object;
				$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'product_brand' );

			} elseif ( is_singular('product') ) {

				$product_brand = wc_get_product_terms( $post->ID, 'product_brand', array( 'orderby' => 'parent' ) );

				if ( $product_brand ) {
					$this->current_brand   = end( $product_brand );
					$this->brand_ancestors = get_ancestors( $this->current_brand->term_id, 'product_brand' );
				}

			}
			
			include_once(dirname(__FILE__) . '/dhwc_product_brand_list_walker.php' );

			$brand_args['walker'] 			= new DHWC_Product_Brand_List_Walker;
			$brand_args['title_li'] 			= '';
			$brand_args['show_children_only']	= ( isset( $instance['show_children_only'] ) && $instance['show_children_only'] ) ? 1 : 0;
			$brand_args['pad_counts'] 		= 1;
			$brand_args['show_option_none'] 	= __('No product brands exist.', DHVC_WOO );
			$brand_args['current_brand']	= ( $this->current_brand ) ? $this->current_brand->term_id : '';
			$brand_args['current_brand_ancestors']	= $this->brand_ancestors;

			echo '<ul class="product-brands">';

			dhwc_list_brands( apply_filters( 'dhwc_product_brands_widget_args', $brand_args ) );

			echo '</ul>';

		}

		echo $after_widget;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wp-includes/WP_Widget::form()
	 */
	public function form($instance){
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'order';
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		$show_children_only = isset( $instance['show_children_only'] ) ? (bool) $instance['show_children_only'] : false;
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', DHVC_WOO ); ?></label>
		<input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
		
		<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Order by:', DHVC_WOO ) ?></label>
		<select id="<?php echo esc_attr( $this->get_field_id('orderby') ); ?>" name="<?php echo esc_attr( $this->get_field_name('orderby') ); ?>">
			<option value="order" <?php selected($orderby, 'order'); ?>><?php _e( 'Order', DHVC_WOO ); ?></option>
			<option value="name" <?php selected($orderby, 'name'); ?>><?php _e( 'Name', DHVC_WOO ); ?></option>
		</select></p>

		<p>
		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('dropdown') ); ?>" name="<?php echo esc_attr( $this->get_field_name('dropdown') ); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Show as dropdown', DHVC_WOO ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('count') ); ?>" name="<?php echo esc_attr( $this->get_field_name('count') ); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts', DHVC_WOO ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('hierarchical') ); ?>" name="<?php echo esc_attr( $this->get_field_name('hierarchical') ); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', DHVC_WOO ); ?></label><br/>

		<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id('show_children_only') ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_children_only') ); ?>"<?php checked( $show_children_only ); ?> />
		<label for="<?php echo $this->get_field_id('show_children_only'); ?>"><?php _e( 'Only show children for the current brand', DHVC_WOO ); ?></label></p>
		
		<?php 
	}
	
	/**
	 * (non-PHPdoc)
	 * @see wp-includes/WP_Widget::update()
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['orderby'] = strip_tags($new_instance['orderby']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? true : false;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;
		$instance['show_children_only'] = !empty($new_instance['show_children_only']) ? 1 : 0;

		return $instance;
	}
}