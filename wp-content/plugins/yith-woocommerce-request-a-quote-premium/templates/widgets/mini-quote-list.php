<?php
/**
 * Table view to Request A Quote in the widget
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/widgets/quote-list.php.
 *
 * HOWEVER, on occasion YITHEMES will need to update template files and you
 * will need to copy the new files to your theme to maintain compatibility.
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @author  Yithemes
 * @version 1.4.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$num_items = count( $raq_content );

?>

<?php do_action( 'ywraq_before_raq_list_widget' ); ?>
	<div class="raq-info">
		<a class="raq_label" href="<?php echo YITH_Request_Quote()->get_raq_page_url(); ?>">
			<span class="handler-label" style="color:#989898; font-weight:400;"><?php echo $title ?></span>
			<span class="raq-tip-counter">
	            <span class="raq-items-number">(<?php echo $num_items ?></ span> <?php echo _n( $item_name, $item_plural_name, $num_items, 'yith-woocommerce-request-a-quote' ); ?>)
	        </span>
			
		</a>
	</div>
	<div class="yith-ywraq-list-wrapper">
		<div class="yith-ywraq-list-content">
			<ul class="yith-ywraq-list">
				<?php if ( ! $num_items ): ?>
				<li ><?php _e( 'No products in the list', 'yith-woocommerce-request-a-quote' ) ?></li>
				<?php else: ?>

				<?php foreach ( $raq_content as $key => $raq ):
					$_product = wc_get_product( isset( $raq['variation_id'] ) ? $raq['variation_id'] : $raq['product_id'] );

					if( ! $_product ){
						continue;
					}
					
					$thumbnail = ( $show_thumbnail ) ? $_product->get_image() : '';
					$product_name = $_product->get_title();
					?>

					<li class="yith-ywraq-list-item">
						<?php
						echo apply_filters( 'yith_ywraq_item_remove_link', sprintf( '<a href="#"  data-remove-item="%s" data-wp_nonce="%s"  data-product_id="%d" class="yith-ywraq-item-remove remove" title="%s">&times;</a>', $key, wp_create_nonce( 'remove-request-quote-' . $_product->get_id() ), $_product->get_id(),  __( 'Remove this item', 'yith-woocommerce-request-a-quote' ) ), $key );
						?>

						<?php if ( ! $_product->is_visible() ) : ?>
							<?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ) . $product_name . '&nbsp;'; ?>
						<?php else : ?>
							<a class="yith-ywraq-list-item-info" href="<?php echo esc_url( $_product->get_permalink( ) ); ?>">
								<?php echo str_replace( array( 'http:', 'https:' ), '', $thumbnail ) . $product_name . '&nbsp;'; ?>
							</a>
						<?php endif; ?>
						<?php if ( isset( $raq['variations'] ) && $show_variations ): ?>
							<small><?php yith_ywraq_get_product_meta( $raq ); ?></small>
						<?php endif ?>

						<?php if ( $show_quantity || $show_price ): ?>
							<span class="quantity">
	                         <?php
	                         echo ( $show_quantity ) ? $raq['quantity'] : '';
	                         if ( $show_price ) {
		                         $x = ( $show_quantity ) ? ' x ' : '';

		                         //wc 2.7
		                         if ( function_exists( 'wc_get_price_to_display' ) ) {
			                         $price = apply_filters( 'yith_ywraq_product_price', wc_get_price_to_display( $_product, array( 'qty' => $raq['quantity'] ) ), $_product, $raq );
		                         } else {
			                         $price = apply_filters( 'yith_ywraq_product_price', $_product->get_display_price( '', $raq['quantity'] ), $_product, $raq );
		                         }

								 $price = apply_filters( 'yith_ywraq_product_price_html', WC()->cart->get_product_subtotal( $_product, $raq[ 'quantity' ] ), $_product, $raq );
								 echo apply_filters( 'yith_ywraq_hide_price_template', $x . $price, $_product->get_id(), $raq );

	                         } ?>
	                          </span>
						<?php endif; ?>
					</li>
				<?php endforeach ?>
					
				<?php endif ?>
			</ul>
			<a href="<?php echo YITH_Request_Quote()->get_raq_page_url() ?>" class="button"><?php echo apply_filters( 'yith_ywraq_quote_list_button_label', __( 'View list', 'yith-woocommerce-request-a-quote' ) ) ?></a>
		</div>
	</div>

<?php do_action( 'ywraq_after_raq_list_widget' ); ?>