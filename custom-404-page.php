<?php
/*
Plugin Name: Custom 404 Error Page
Plugin URI: https://github.com/kasparsd/custom-404-page
Description: Set any page to be used as 404 error page.
Version: 0.2.5
Author: Kaspars Dambis
Domain Path: /lang
Text Domain: custom-404-page
*/

if ( ! function_exists( 'add_action' ) ) {
	return;
}

add_action( 'plugins_loaded', [ Custom404Page::class, 'instance' ] );
