<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YWCTM_Custom_Url_Table' ) ) {

	/**
	 * Displays the custom url table in YWCTM plugin admin tab
	 *
	 * @class   YWCTM_Custom_Url_Table
	 * @package Yithemes
	 * @since   1.3.0
	 * @author  Your Inspiration Themes
	 *
	 */
	class YWCTM_Custom_Url_Table {

		/**
		 * Single instance of the class
		 *
		 * @var \YWCTM_Custom_Url_Table
		 * @since 1.3.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YWCTM_Custom_Url_Table
		 * @since 1.3.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST );

			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since   1.3.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			add_filter( 'set-screen-option', array( $this, 'set_options' ), 10, 3 );
			add_action( 'current_screen', array( $this, 'add_options' ) );
			add_action( 'wp_ajax_ywctm_json_search_product_categories', array( $this, 'json_search_product_categories' ), 10 );
			add_action( 'wp_ajax_ywctm_json_search_product_tags', array( $this, 'json_search_product_tags' ), 10 );

		}

		/**
		 * Outputs the custom url table template with insert form in plugin options panel
		 *
		 * @since   1.3.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function output() {

			global $wpdb;

			$current_section = isset( $_GET['section'] ) ? $_GET['section'] : 'products';

			$sections   = array(
				'products'   => array(
					'section' => __( 'Products', 'yith-woocommerce-catalog-mode' ),
					'args'    => array(
						'singular' => __( 'product', 'yith-woocommerce-catalog-mode' ),
						'plural'   => __( 'products', 'yith-woocommerce-catalog-mode' ),
						'id'       => 'product'
					),
					'options' => array(
						'select_table'     => $wpdb->prefix . 'posts a INNER JOIN ' . $wpdb->prefix . 'postmeta b ON a.ID = b.post_id',
						'select_columns'   => array(
							'a.ID',
							'a.post_title',
							'MAX( CASE WHEN b.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" THEN b.meta_value ELSE NULL END ) AS custom_url_enabled',
						),
						'select_where'     => 'a.post_type = "product" AND ( b.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR b.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND b.meta_value = "yes"',
						'select_group'     => 'a.ID',
						'select_order'     => 'a.post_title',
						'select_order_dir' => 'ASC',
						'search_where'     => array(
							'a.post_title'
						),
						'per_page_option'  => 'products_per_page',
						'count_table'      => '( SELECT a.ID, a.post_title FROM ' . $wpdb->prefix . 'posts a INNER JOIN ' . $wpdb->prefix . 'postmeta b ON a.ID = b.post_id  WHERE a.post_type = "product" AND ( b.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR b.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND b.meta_value = "yes" GROUP BY a.ID ) AS a',
						'count_where'      => '',
						'key_column'       => 'ID',
						'view_columns'     => array(
							'cb'         => '<input type="checkbox" />',
							'product'    => __( 'Product', 'yith-woocommerce-catalog-mode' ),
							'custom_url' => __( 'Custom button URL', 'yith-woocommerce-catalog-mode' ),
						),
						'hidden_columns'   => array(),
						'sortable_columns' => array(
							'product' => array( 'post_title', true )
						),
						'custom_columns'   => array(
							'column_product'    => function ( $item, $me ) {

								$edit_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => ( isset( $_GET['section'] ) ? $_GET['section'] : 'products' ),
									'action'  => 'edit',
									'id'      => $item['ID']
								);
								$edit_url        = esc_url( add_query_arg( $edit_query_args, admin_url( 'admin.php' ) ) );

								$delete_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => ( isset( $_GET['section'] ) ? $_GET['section'] : 'products' ),
									'action'  => 'delete',
									'id'      => $item['ID']
								);
								$delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

								$product_query_args = array(
									'post'   => $item['ID'],
									'action' => 'edit'
								);
								$product_url        = esc_url( add_query_arg( $product_query_args, admin_url( 'post.php' ) ) );

								$actions = array(
									'edit'    => '<a href="' . $edit_url . '">' . __( 'Edit URL', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'product' => '<a href="' . $product_url . '" target="_blank">' . __( 'Edit product', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'delete'  => '<a href="' . $delete_url . '">' . __( 'Remove from list', 'yith-woocommerce-catalog-mode' ) . '</a>',
								);

								return sprintf( '<strong><a class="tips" href="%s" data-tip="%s">#%d %s </a></strong> %s', $edit_url, __( 'Edit URL', 'yith-woocommerce-catalog-mode' ), $item['ID'], $item['post_title'], $me->row_actions( $actions ) );
							},
							'column_custom_url' => function ( $item, $me ) {

								if ( get_post_meta( $item['ID'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ) == 'yes' ) {
									return __( 'Excluded from custom button', 'yith-woocommerce-catalog-mode' );
								}

								$protocol        = get_post_meta( $item['ID'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true );
								$link            = get_post_meta( $item['ID'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true );
								$target          = get_post_meta( $item['ID'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true );
								$button_url_type = $protocol == 'generic' ? '' : $protocol . ':';
								$button_url      = $link == '' ? '#' : $link;
								$new_tab         = ( $protocol == 'generic' && $target == 'yes' ? ' (' . __( 'Link opened in new tab', 'yith-woocommerce-catalog-mode' ) . ')' : '' );

								return sprintf( '%s%s%s', $button_url_type, $button_url, $new_tab );

							},
						),
						'bulk_actions'     => array(
							'actions'   => array(
								'delete' => __( 'Remove from list', 'yith-woocommerce-catalog-mode' )
							),
							'functions' => array(
								'function_delete' => function () {
									global $wpdb;

									$ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
									if ( is_array( $ids ) ) {
										$ids = implode( ',', $ids );
									}

									if ( ! empty( $ids ) ) {
										$wpdb->query( "UPDATE {$wpdb->prefix}postmeta
                                           SET meta_value = 'no'
                                           WHERE ( meta_key = '_ywctm_custom_url_enabled{$this->get_vendor_id()}' OR meta_key = '_ywctm_exclude_button{$this->get_vendor_id()}' ) AND post_id IN ( $ids )"
										);
									}
								}
							)
						),
					),
					'action'  => 'woocommerce_json_search_products'
				),
				'categories' => array(
					'section' => __( 'Categories', 'yith-woocommerce-catalog-mode' ),
					'args'    => array(
						'singular' => __( 'category', 'yith-woocommerce-catalog-mode' ),
						'plural'   => __( 'categories', 'yith-woocommerce-catalog-mode' ),
						'id'       => 'category'
					),
					'options' => array(
						'select_table'     => $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $wpdb->prefix . $this->get_table_id_wc_prefix() . 'termmeta c ON c.' . $this->get_table_id_wc_prefix() . 'term_id = a.term_id',
						'select_columns'   => array(
							'a.term_id AS ID',
							'a.name',
							'MAX( CASE WHEN c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" THEN c.meta_value ELSE NULL END ) AS custom_url_enabled',
						),
						'select_where'     => 'b.taxonomy = "product_cat" AND ( c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR c.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND c.meta_value = "yes"',
						'select_group'     => 'a.term_id',
						'select_order'     => 'a.name',
						'select_order_dir' => 'ASC',
						'per_page_option'  => 'categories_per_page',
						'search_where'     => array(
							'a.name'
						),
						'count_table'      => '( SELECT a.* FROM ' . $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $wpdb->prefix . $this->get_table_id_wc_prefix() . 'termmeta c ON c.' . $this->get_table_id_wc_prefix() . 'term_id = a.term_id WHERE b.taxonomy = "product_cat" AND ( c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR c.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND c.meta_value = "yes" GROUP BY a.term_id ) AS a',
						'count_where'      => '',
						'key_column'       => 'ID',
						'view_columns'     => array(
							'cb'         => '<input type="checkbox" />',
							'category'   => __( 'Category', 'yith-woocommerce-catalog-mode' ),
							'custom_url' => __( 'Custom button URL', 'yith-woocommerce-catalog-mode' ),
						),
						'hidden_columns'   => array(),
						'sortable_columns' => array(
							'category' => array( 'name', true )
						),
						'custom_columns'   => array(
							'column_category'   => function ( $item, $me ) {

								$edit_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => isset( $_GET['section'] ) ? $_GET['section'] : 'products',
									'action'  => 'edit',
									'id'      => $item['ID']
								);
								$edit_url        = esc_url( add_query_arg( $edit_query_args, admin_url( 'admin.php' ) ) );

								$delete_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => isset( $_GET['section'] ) ? $_GET['section'] : 'products',
									'action'  => 'delete',
									'id'      => $item['ID']
								);
								$delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

								$category_query_args = array(
									'taxonomy'  => 'product_cat',
									'post_type' => 'product',
									'tag_ID'    => $item['ID'],
									'action'    => 'edit'
								);
								$category_url        = esc_url( add_query_arg( $category_query_args, admin_url( 'edit-tags.php' ) ) );

								$actions = array(
									'edit'    => '<a href="' . $edit_url . '">' . __( 'Edit URL', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'product' => '<a href="' . $category_url . '" target="_blank">' . __( 'Edit category', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'delete'  => '<a href="' . $delete_url . '">' . __( 'Remove from list', 'yith-woocommerce-catalog-mode' ) . '</a>',
								);

								return sprintf( '<strong><a class="tips" href="%s" data-tip="%s">#%d %s </a></strong> %s', $edit_url, __( 'Edit URL', 'yith-woocommerce-catalog-mode' ), $item['ID'], $item['name'], $me->row_actions( $actions ) );
							},
							'column_custom_url' => function ( $item, $me ) {

								if ( get_term_meta( $item['ID'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ) == 'yes' ) {
									return __( 'Excluded from custom button', 'yith-woocommerce-catalog-mode' );
								}

								$protocol        = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true );
								$link            = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true );
								$target          = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true );
								$button_url_type = $protocol == 'generic' ? '' : $protocol . ':';
								$button_url      = $link == '' ? '#' : $link;
								$new_tab         = ( $protocol == 'generic' && $target == 'yes' ? ' (' . __( 'Link opened in new tab', 'yith-woocommerce-catalog-mode' ) . ')' : '' );

								return sprintf( '%s%s%s', $button_url_type, $button_url, $new_tab );

							},
						),
						'bulk_actions'     => array(
							'actions'   => array(
								'delete' => __( 'Remove from list', 'yith-woocommerce-catalog-mode' )
							),
							'functions' => array(
								'function_delete' => function () {

									global $wpdb;

									$ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
									if ( is_array( $ids ) ) {
										$ids = implode( ',', $ids );
									}

									if ( ! empty( $ids ) ) {
										$wpdb->query( "UPDATE {$wpdb->prefix}{$this->get_table_id_wc_prefix()}termmeta
                                           SET meta_value = 'no'
                                           WHERE ( meta_key = '_ywctm_custom_url_enabled{$this->get_vendor_id()}' OR meta_key = '_ywctm_exclude_button{$this->get_vendor_id()}' ) AND {$this->get_table_id_wc_prefix()}term_id IN ( $ids )"
										);
									}

								}
							)
						),
					),
					'action'  => 'ywctm_json_search_product_categories'
				),
				'tags'       => array(
					'section' => __( 'Tags', 'yith-woocommerce-catalog-mode' ),
					'args'    => array(
						'singular' => __( 'tag', 'yith-woocommerce-catalog-mode' ),
						'plural'   => __( 'tags', 'yith-woocommerce-catalog-mode' ),
						'id'       => 'tag'
					),
					'options' => array(
						'select_table'     => $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $wpdb->prefix . $this->get_table_id_wc_prefix() . 'termmeta c ON c.' . $this->get_table_id_wc_prefix() . 'term_id = a.term_id',
						'select_columns'   => array(
							'a.term_id AS ID',
							'a.name',
							'MAX( CASE WHEN c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" THEN c.meta_value ELSE NULL END ) AS custom_url_enabled',
						),
						'select_where'     => 'b.taxonomy = "product_tag" AND ( c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR c.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND c.meta_value = "yes"',
						'select_group'     => 'a.term_id',
						'select_order'     => 'a.name',
						'select_order_dir' => 'ASC',
						'per_page_option'  => 'tags_per_page',
						'search_where'     => array(
							'a.name'
						),
						'count_table'      => '( SELECT a.* FROM ' . $wpdb->prefix . 'terms a INNER JOIN ' . $wpdb->prefix . 'term_taxonomy b ON a.term_id = b.term_id INNER JOIN ' . $wpdb->prefix . $this->get_table_id_wc_prefix() . 'termmeta c ON c.' . $this->get_table_id_wc_prefix() . 'term_id = a.term_id WHERE b.taxonomy = "product_tag" AND ( c.meta_key = "_ywctm_custom_url_enabled' . $this->get_vendor_id() . '" OR c.meta_key = "_ywctm_exclude_button' . $this->get_vendor_id() . '" ) AND c.meta_value = "yes" GROUP BY a.term_id ) AS a',
						'count_where'      => '',
						'key_column'       => 'ID',
						'view_columns'     => array(
							'cb'         => '<input type="checkbox" />',
							'tag'        => __( 'Tag', 'yith-woocommerce-catalog-mode' ),
							'custom_url' => __( 'Custom button URL', 'yith-woocommerce-catalog-mode' ),
						),
						'hidden_columns'   => array(),
						'sortable_columns' => array(
							'tag' => array( 'name', true )
						),
						'custom_columns'   => array(
							'column_tag'        => function ( $item, $me ) {

								$edit_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => isset( $_GET['section'] ) ? $_GET['section'] : 'products',
									'action'  => 'edit',
									'id'      => $item['ID']
								);
								$edit_url        = esc_url( add_query_arg( $edit_query_args, admin_url( 'admin.php' ) ) );

								$delete_query_args = array(
									'page'    => $_GET['page'],
									'tab'     => $_GET['tab'],
									'section' => isset( $_GET['section'] ) ? $_GET['section'] : 'products',

									'action' => 'delete',
									'id'     => $item['ID']
								);
								$delete_url        = esc_url( add_query_arg( $delete_query_args, admin_url( 'admin.php' ) ) );

								$tag_query_args = array(
									'taxonomy'  => 'product_tag',
									'post_type' => 'product',
									'tag_ID'    => $item['ID'],
									'action'    => 'edit'
								);
								$tag_url        = esc_url( add_query_arg( $tag_query_args, admin_url( 'edit-tags.php' ) ) );

								$actions = array(
									'edit'    => '<a href="' . $edit_url . '">' . __( 'Edit URL', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'product' => '<a href="' . $tag_url . '" target="_blank">' . __( 'Edit tag', 'yith-woocommerce-catalog-mode' ) . '</a>',
									'delete'  => '<a href="' . $delete_url . '">' . __( 'Remove from list', 'yith-woocommerce-catalog-mode' ) . '</a>',
								);

								return sprintf( '<strong><a class="tips" href="%s" data-tip="%s">#%d %s </a></strong> %s', $edit_url, __( 'Edit URL', 'yith-woocommerce-catalog-mode' ), $item['ID'], $item['name'], $me->row_actions( $actions ) );
							},
							'column_custom_url' => function ( $item, $me ) {

								if ( get_term_meta( $item['ID'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ) == 'yes' ) {
									return __( 'Excluded from custom button', 'yith-woocommerce-catalog-mode' );
								}

								$protocol        = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true );
								$link            = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true );
								$target          = YITH_WCTM()->get_term_meta( $item['ID'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true );
								$button_url_type = $protocol == 'generic' ? '' : $protocol . ':';
								$button_url      = $link == '' ? '#' : $link;
								$new_tab         = ( $protocol == 'generic' && $target == 'yes' ? ' (' . __( 'Link opened in new tab', 'yith-woocommerce-catalog-mode' ) . ')' : '' );

								return sprintf( '%s%s%s', $button_url_type, $button_url, $new_tab );

							},
						),
						'bulk_actions'     => array(
							'actions'   => array(
								'delete' => __( 'Remove from list', 'yith-woocommerce-catalog-mode' )
							),
							'functions' => array(
								'function_delete' => function () {

									global $wpdb;

									$ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
									if ( is_array( $ids ) ) {
										$ids = implode( ',', $ids );
									}

									if ( ! empty( $ids ) ) {
										$wpdb->query( "UPDATE {$wpdb->prefix}{$this->get_table_id_wc_prefix()}termmeta
                                           SET meta_value = 'no'
                                           WHERE ( meta_key = '_ywctm_custom_url_enabled{$this->get_vendor_id()}' OR meta_key = '_ywctm_exclude_button{$this->get_vendor_id()}' ) AND {$this->get_table_id_wc_prefix()}term_id IN ( $ids )"
										);
									}

								}
							)
						),
					),
					'action'  => 'ywctm_json_search_product_tags'
				),
			);
			$array_keys = array_keys( $sections );

			$table = new YITH_Custom_Table( $sections[ $current_section ]['args'] );

			$table->options = $sections[ $current_section ]['options'];

			$message = '';
			$notice  = '';

			$list_query_args = array(
				'page'    => $_GET['page'],
				'tab'     => $_GET['tab'],
				'section' => $current_section
			);

			$list_url = esc_url( add_query_arg( $list_query_args, admin_url( 'admin.php' ) ) );

			if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], basename( __FILE__ ) ) ) {

				$item_valid = $this->validate_fields( $_POST, $current_section );

				if ( $item_valid !== true ) {

					$notice = $item_valid;

				} else {

					$enable_url = isset( $_POST['_ywctm_custom_url_enabled'] ) ? 'yes' : 'no';
					$exclude    = isset( $_POST['_ywctm_exclude_button'] ) ? 'yes' : 'no';
					$protocol   = $_POST['_ywctm_custom_url_protocol'];
					$link       = $_POST['_ywctm_custom_url_link'];
					$target     = isset( $_POST['_ywctm_custom_url_link_target'] ) ? 'yes' : 'no';

					switch ( $current_section ) {

						case 'categories':

							$category_ids = explode( ',', $_POST['category_ids'] );
							$count        = count( $category_ids );

							foreach ( $category_ids as $category_id ) {

								YITH_WCTM()->update_term_meta( $category_id, '_ywctm_custom_url_enabled' . $this->get_vendor_id(), $enable_url );
								YITH_WCTM()->update_term_meta( $category_id, '_ywctm_exclude_button' . $this->get_vendor_id(), $exclude );
								YITH_WCTM()->update_term_meta( $category_id, '_ywctm_custom_url_protocol' . $this->get_vendor_id(), $protocol );
								YITH_WCTM()->update_term_meta( $category_id, '_ywctm_custom_url_link' . $this->get_vendor_id(), $link );
								YITH_WCTM()->update_term_meta( $category_id, '_ywctm_custom_url_link_target' . $this->get_vendor_id(), $target );

							}

							break;

						case 'tags':

							$tag_ids = explode( ',', $_POST['tag_ids'] );
							$count   = count( $tag_ids );

							foreach ( $tag_ids as $tag_id ) {

								YITH_WCTM()->update_term_meta( $tag_id, '_ywctm_custom_url_enabled' . $this->get_vendor_id(), $enable_url );
								YITH_WCTM()->update_term_meta( $tag_id, '_ywctm_exclude_button' . $this->get_vendor_id(), $exclude );
								YITH_WCTM()->update_term_meta( $tag_id, '_ywctm_custom_url_protocol' . $this->get_vendor_id(), $protocol );
								YITH_WCTM()->update_term_meta( $tag_id, '_ywctm_custom_url_link' . $this->get_vendor_id(), $link );
								YITH_WCTM()->update_term_meta( $tag_id, '_ywctm_custom_url_link_target' . $this->get_vendor_id(), $target );

							}

							break;

						default:

							$product_ids = explode( ',', $_POST['product_ids'] );
							$count       = count( $product_ids );

							if ( ! empty( $product_ids ) ) {

								foreach ( $product_ids as $product_id ) {

									update_post_meta( $product_id, '_ywctm_custom_url_enabled' . $this->get_vendor_id(), $enable_url );
									update_post_meta( $product_id, '_ywctm_exclude_button' . $this->get_vendor_id(), $exclude );
									update_post_meta( $product_id, '_ywctm_custom_url_protocol' . $this->get_vendor_id(), $protocol );
									update_post_meta( $product_id, '_ywctm_custom_url_link' . $this->get_vendor_id(), $link );
									update_post_meta( $product_id, '_ywctm_custom_url_link_target' . $this->get_vendor_id(), $target );

								}

							}

					}

					if ( ! empty( $_POST['insert'] ) ) {

						$singular = sprintf( __( '1 %s added successfully', 'yith-woocommerce-catalog-mode' ), ucfirst( $sections[ $current_section ]['args']['singular'] ) );
						$plural   = sprintf( __( '%s %s added successfully', 'yith-woocommerce-catalog-mode' ), $count, ucfirst( $sections[ $current_section ]['args']['plural'] ) );
						$message  = $count > 1 ? $plural : $singular;

					} elseif ( ! empty( $_POST['edit'] ) ) {

						$message = sprintf( __( '%s updated successfully', 'yith-woocommerce-catalog-mode' ), ucfirst( $sections[ $current_section ]['args']['singular'] ) );

					}

				}

			}

			$table->prepare_items();

			$data_selected = '';
			$value         = '';
			$item          = array(
				'ID'                => 0,
				'enable_custom_url' => '',
				'exclude'           => '',
				'protocol'          => '',
				'link'              => '',
				'target'            => '',
			);

			if ( isset( $_GET['id'] ) && ! empty( $_GET['action'] ) && ( 'edit' == $_GET['action'] ) ) {

				switch ( $current_section ) {

					case'categories':

						$item = array(
							'ID'                => $_GET['id'],
							'enable_custom_url' => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_enabled' . $this->get_vendor_id(), true ),
							'exclude'           => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ),
							'protocol'          => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true ),
							'link'              => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true ),
							'target'            => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true ),
						);

						$category      = get_term( $_GET['id'], 'product_cat' );
						$data_selected = wp_kses_post( $category->name );
						break;

					case 'tags':

						$item = array(
							'ID'                => $_GET['id'],
							'enable_custom_url' => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_enabled' . $this->get_vendor_id(), true ),
							'exclude'           => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ),
							'protocol'          => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true ),
							'link'              => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true ),
							'target'            => YITH_WCTM()->get_term_meta( $_GET['id'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true ),
						);

						$tag           = get_term( $_GET['id'], 'product_tag' );
						$data_selected = wp_kses_post( $tag->name );
						break;

					default:

						$item = array(
							'ID'                => $_GET['id'],
							'enable_custom_url' => get_post_meta( $_GET['id'], '_ywctm_custom_url_enabled' . $this->get_vendor_id(), true ),
							'exclude'           => get_post_meta( $_GET['id'], '_ywctm_exclude_button' . $this->get_vendor_id(), true ),
							'protocol'          => get_post_meta( $_GET['id'], '_ywctm_custom_url_protocol' . $this->get_vendor_id(), true ),
							'link'              => get_post_meta( $_GET['id'], '_ywctm_custom_url_link' . $this->get_vendor_id(), true ),
							'target'            => get_post_meta( $_GET['id'], '_ywctm_custom_url_link_target' . $this->get_vendor_id(), true ),
						);

						$product       = wc_get_product( $_GET['id'] );
						$data_selected = wp_kses_post( $product->get_formatted_name() );
				}

				$value = $_GET['id'];

			}

			if ( 'delete' === $table->current_action() ) {

				$singular = sprintf( __( '1 %s removed successfully', 'yith-woocommerce-catalog-mode' ), ucfirst( $sections[ $current_section ]['args']['singular'] ) );
				$plural   = sprintf( __( '%s %s removed successfully', 'yith-woocommerce-catalog-mode' ), count( $_GET['id'] ), ucfirst( $sections[ $current_section ]['args']['plural'] ) );
				$message  = count( $_GET['id'] ) > 1 ? $plural : $singular;

			}

			?>
			<ul class="subsubsub">
				<?php foreach ( $sections as $id => $section ) : ?>
					<li>
						<?php
						$query_args  = array( 'page' => $_GET['page'], 'tab' => $_GET['tab'], 'section' => $id );
						$section_url = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
						?>

						<a href="<?php echo $section_url; ?>" class="<?php echo( $current_section == $id ? 'current' : '' ); ?>">
							<?php echo $section['section']; ?>
						</a>
						<?php echo( end( $array_keys ) == $id ? '' : '|' ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<br class="clear" />
			<div class="wrap">
				<div class="icon32 icon32-posts-post" id="icon-edit"><br /></div>
				<h1><?php _e( 'Custom Button URL list', 'yith-woocommerce-catalog-mode' ); ?>

					<?php if ( empty( $_GET['action'] ) || ( 'insert' !== $_GET['action'] && 'edit' !== $_GET['action'] ) ) : ?>
						<?php
						$query_args   = array( 'page' => $_GET['page'], 'tab' => $_GET['tab'], 'section' => $current_section, 'action' => 'insert' );
						$add_form_url = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
						?>
						<a class="page-title-action" href="<?php echo $add_form_url; ?>"><?php echo sprintf( __( 'Add %s', 'yith-woocommerce-catalog-mode' ), $sections[ $current_section ]['section'] ) ?></a>
					<?php endif; ?>
				</h1>

				<?php if ( ! empty( $notice ) ) : ?>
					<div id="notice" class="error below-h2"><p><?php echo $notice; ?></p></div>
				<?php endif; ?>

				<?php if ( ! empty( $message ) ) : ?>
					<div id="message" class="updated below-h2"><p><?php echo $message; ?></p></div>
				<?php endif; ?>

				<?php if ( ! empty( $_GET['action'] ) && ( 'insert' == $_GET['action'] || 'edit' == $_GET['action'] ) ) : ?>

					<form id="form" method="POST" action="<?php echo $list_url; ?>">
						<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
						<table class="form-table">
							<tbody>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="<?php echo $sections[ $current_section ]['args']['id']; ?>_ids">
										<?php echo ( 'edit' == $_GET['action'] ) ? sprintf( __( '%s to edit', 'yith-woocommerce-catalog-mode' ), ucfirst( $sections[ $current_section ]['args']['singular'] ) ) : sprintf( __( 'Select %s', 'yith-woocommerce-catalog-mode' ), ucfirst( $sections[ $current_section ]['args']['plural'] ) ); ?>
									</label>
								</th>
								<td class="forminp">

									<?php if ( 'edit' == $_GET['action'] ) : ?>
										<input id="<?php echo $sections[ $current_section ]['args']['id']; ?>_id" name="<?php echo $sections[ $current_section ]['args']['id']; ?>_ids" type="hidden" value="<?php echo esc_attr( $item['ID'] ); ?>" />
									<?php endif; ?>

									<input type="hidden" class="wc-product-search" id="<?php echo $sections[ $current_section ]['args']['id']; ?>_ids" name="<?php echo $sections[ $current_section ]['args']['id']; ?>_ids"
									       data-placeholder="<?php echo sprintf( __( 'Search for a %s&hellip;', 'yith-woocommerce-catalog-mode' ), $sections[ $current_section ]['args']['singular'] ); ?>"
									       data-action="<?php echo $sections[ $current_section ]['action']; ?>"
									       data-multiple="<?php echo ( 'edit' == $_GET['action'] ) ? 'false' : 'true'; ?>"
									       data-selected="<?php echo $data_selected; ?>"
									       value="<?php echo $value; ?>"
										<?php echo ( 'edit' == $_GET['action'] ) ? 'disabled="disabled"' : ''; ?>
									/>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="_ywctm_exclude_button"><?php _e( 'Exclude from custom button', 'yith-woocommerce-catalog-mode' ); ?></label>
								</th>
								<td class="forminp forminp-checkbox">
									<input id="_ywctm_exclude_button" name="_ywctm_exclude_button" type="checkbox" <?php echo ( esc_attr( $item['exclude'] ) == 'yes' ) ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="_ywctm_custom_url_enabled"><?php _e( 'Enable custom button URL override', 'yith-woocommerce-catalog-mode' ); ?></label>
								</th>
								<td class="forminp forminp-checkbox">
									<input id="_ywctm_custom_url_enabled" name="_ywctm_custom_url_enabled" type="checkbox" <?php echo ( esc_attr( $item['enable_custom_url'] ) == 'yes' ) ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="_ywctm_custom_url_protocol"><?php _e( 'URL protocol type', 'yith-woocommerce-catalog-mode' ); ?></label>
								</th>
								<td class="forminp forminp-select">
									<select id="_ywctm_custom_url_protocol" name="_ywctm_custom_url_protocol">
										<option value="generic" <?php selected( $item['protocol'], 'generic' ); ?>><?php _e( 'Generic URL', 'yith-woocommerce-catalog-mode' ); ?></option>
										<option value="mailto" <?php selected( $item['protocol'], 'mailto' ); ?>><?php _e( 'E-mail address', 'yith-woocommerce-catalog-mode' ); ?></option>
										<option value="tel" <?php selected( $item['protocol'], 'tel' ); ?>><?php _e( 'Phone number', 'yith-woocommerce-catalog-mode' ); ?></option>
										<option value="skype" <?php selected( $item['protocol'], 'skype' ); ?>><?php _e( 'Skype contact', 'yith-woocommerce-catalog-mode' ); ?></option>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="_ywctm_custom_url_link"><?php _e( 'URL Link', 'yith-woocommerce-catalog-mode' ); ?></label>
								</th>
								<td class="forminp forminp-text">

									<input id="_ywctm_custom_url_link" name="_ywctm_custom_url_link" type="text" value="<?php echo $item['link']; ?>" />

								</td>
							</tr>
							<tr valign="top">
								<th scope="row" class="titledesc">
									<label for="_ywctm_custom_url_link_target"><?php _e( 'Open link in new tab (Only for Generic URL)', 'yith-woocommerce-catalog-mode' ); ?></label>
								</th>
								<td class="forminp forminp-checkbox">
									<input id="_ywctm_custom_url_link_target" name="_ywctm_custom_url_link_target" type="checkbox" <?php echo ( esc_attr( $item['target'] ) == 'yes' ) ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							</tbody>
						</table>
						<input id="<?php echo $_GET['action'] ?>" name="<?php echo $_GET['action'] ?>" type="submit" class="button-primary"
						       value="<?php echo( ( 'insert' == $_GET['action'] ) ? __( 'Add custom URL', 'yith-woocommerce-catalog-mode' ) : __( 'Update custom URL', 'yith-woocommerce-catalog-mode' ) ); ?>"
						/>
						<a class="button-secondary" href="<?php echo $list_url; ?>"><?php _e( 'Return to list', 'yith-woocommerce-catalog-mode' ); ?></a>
					</form>

				<?php else : ?>
					<p>
						<i>
							<?php _e( 'If you activate the option "Custom Button", the items belonging to the following list will use a custom link for the button.', 'yith-woocommerce-catalog-mode' ); ?>
						</i>
					</p>
					<form id="custom-table" method="GET" action="<?php echo $list_url; ?>">
						<?php $table->search_box( sprintf( __( 'Search %s' ), $sections[ $current_section ]['args']['singular'] ), $sections[ $current_section ]['args']['singular'] ); ?>
						<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
						<input type="hidden" name="tab" value="<?php echo $_GET['tab']; ?>" />
						<input type="hidden" name="section" value="<?php echo $current_section; ?>" />
						<?php $table->display(); ?>
					</form>

				<?php endif; ?>

			</div>
			<?php

		}

		/**
		 * Get current vendor ID
		 *
		 * @since   1.3.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_vendor_id() {

			$vendor_id = '';

			if ( YITH_WCTM()->is_multivendor_active() ) {

				$vendor    = yith_get_vendor( 'current', 'user' );
				$vendor_id = ( $vendor->id > 0 ) ? '_' . $vendor->id : '';

			}

			return $vendor_id;

		}

		/**
		 * Get prefix for term table and ic column for WC 2.6 compatibility
		 *
		 * @since   1.4.0
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function get_table_id_wc_prefix() {

			return ( YITH_WCTM()->is_wc_lower_2_6 ) ? 'woocommerce_' : '';

		}

		/**
		 * Validate input fields
		 *
		 * @since   1.3.0
		 *
		 * @param   $item array POST data array
		 * @param   $current_section
		 *
		 * @return  bool|string
		 * @author  Alberto Ruggiero
		 */
		public function validate_fields( $item, $current_section ) {

			$messages = array();

			if ( ! empty( $item['insert'] ) ) {

				switch ( $current_section ) {

					case 'categories':

						if ( empty( $item['category_ids'] ) ) {
							$messages[] = __( 'Select at least one category', 'yith-woocommerce-catalog-mode' );
						}

						break;

					case 'tags':

						if ( empty( $item['tag_ids'] ) ) {
							$messages[] = __( 'Select at least one tag', 'yith-woocommerce-catalog-mode' );
						}

						break;

					default:

						if ( empty( $item['product_ids'] ) ) {
							$messages[] = __( 'Select at least one product', 'yith-woocommerce-catalog-mode' );
						}

				}

			}

			if ( empty( $messages ) ) {
				return true;
			}

			return implode( '<br />', $messages );
		}

		/**
		 * Add screen options for exclusions list table template
		 *
		 * @since   1.3.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_options() {

			$sections = array(
				'products'   => __( 'Products', 'yith-woocommerce-catalog-mode' ),
				'categories' => __( 'Categories', 'yith-woocommerce-catalog-mode' ),
				'tags'       => __( 'Tags', 'yith-woocommerce-catalog-mode' ),
			);

			$current_section = isset( $_GET['section'] ) ? $_GET['section'] : 'products';

			if ( ( 'yith-plugins_page_yith_wc_catalog_mode_panel' == get_current_screen()->id || 'toplevel_page_yith_vendor_ctm_settings' == get_current_screen()->id ) && ( isset( $_GET['tab'] ) && $_GET['tab'] == 'custom-url' ) && ( ! isset( $_GET['action'] ) || ( $_GET['action'] != 'edit' && $_GET['action'] != 'insert' ) ) ) {

				$option = 'per_page';

				$args = array(
					'label'   => $sections[ $current_section ],
					'default' => 10,
					'option'  => $current_section . '_per_page'
				);

				add_screen_option( $option, $args );

			}

		}

		/**
		 * Set screen options for exclusions list table template
		 *
		 * @since   1.3.0
		 *
		 * @param   $status
		 * @param   $option
		 * @param   $value
		 *
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function set_options( $status, $option, $value ) {

			$current_section = isset( $_GET['section'] ) ? $_GET['section'] : 'products';

			return ( $current_section . '_per_page' == $option ) ? $value : $status;

		}

		/**
		 * Get category name
		 *
		 * @since   1.3.0
		 *
		 * @param   $x
		 * @param   $taxonomy_types
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function json_search_product_categories( $x = '', $taxonomy_types = array( 'product_cat' ) ) {

			global $wpdb;

			$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );
			$term = '%' . $term . '%';

			$query_cat = $wpdb->prepare( "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.slug LIKE %s", implode( ',', $taxonomy_types ), $term );

			$product_categories = $wpdb->get_results( $query_cat );

			$to_json = array();

			foreach ( $product_categories as $product_category ) {

				$to_json[ $product_category->term_id ] = sprintf( '#%s &ndash; %s', $product_category->term_id, $product_category->name );

			}

			wp_send_json( $to_json );

		}

		/**
		 * Get tag name
		 *
		 * @since   1.3.0
		 *
		 * @param   $x
		 * @param   $taxonomy_types
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function json_search_product_tags( $x = '', $taxonomy_types = array( 'product_tag' ) ) {

			global $wpdb;

			$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );
			$term = '%' . $term . '%';

			$query_cat = $wpdb->prepare( "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name, {$wpdb->terms}.slug
                                   FROM {$wpdb->terms} INNER JOIN {$wpdb->term_taxonomy} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id
                                   WHERE {$wpdb->term_taxonomy}.taxonomy IN (%s) AND {$wpdb->terms}.slug LIKE %s", implode( ',', $taxonomy_types ), $term );

			$product_tags = $wpdb->get_results( $query_cat );

			$to_json = array();

			foreach ( $product_tags as $product_tag ) {

				$to_json[ $product_tag->term_id ] = sprintf( '#%s &ndash; %s', $product_tag->term_id, $product_tag->name );

			}

			wp_send_json( $to_json );

		}

	}

	/**
	 * Unique access to instance of YWCTM_Custom_Url_Table class
	 *
	 * @return \YWCTM_Custom_Url_Table
	 */
	function YWCTM_Custom_Url_Table() {

		return YWCTM_Custom_Url_Table::get_instance();

	}

	new YWCTM_Custom_Url_Table();
}