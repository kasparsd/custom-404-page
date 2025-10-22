<?php
/**
 * Plugin Name: Custom 404 Error Page
 * Plugin URI: https://github.com/kasparsd/custom-404-page
 * Description: Set any page to be used as 404 error page.
 * Version: 0.2.6
 * Author: Kaspars Dambis
 * Text Domain: custom-404-page
 */

if ( ! function_exists( 'add_action' ) ) {
	return;
}

require_once __DIR__ . '/php/class-plugin.php';

function custom_404_page() {
	static $instance;

	if ( ! isset( $instance ) ) {
		$instance = new Custom404Page( __FILE__ );
	}

	return $instance;
}

add_action( 'plugins_loaded', [ custom_404_page(), 'init' ] );
