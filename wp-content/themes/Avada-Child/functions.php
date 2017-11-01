<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

add_filter( 'category_description', 'do_shortcode' );


	function yith_ywraq_quote_list_shortcode( $shortcodes ) {
		$shortcodes['%yith-request-a-quote-list%'] = yith_ywraq_get_email_template( true );

		return $shortcodes;
	}

	add_filter( 'yit_contact_form_shortcodes', 'yith_ywraq_quote_list_shortcode' );
