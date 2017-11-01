jQuery(document).ready(function($){
    jQuery('.hide-add-cart').closest('li').addClass('hide_add_to_cart');
    jQuery('.hide_add_to_cart a.button').remove();
    jQuery('.hide-add-cart-2').closest('div.summary.entry-summary').addClass('hide_add_to_cart');
    jQuery('.hide_add_to_cart form.cart').remove(); 
})