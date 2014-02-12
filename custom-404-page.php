<?php
/*
Plugin Name: Custom 404 Error Page
Plugin URI: 
Description: Set any page to be used as 404 error page.
Version: 0.1
Author: Kaspars Dambis
Domain Path: /lang
Text Domain: custom-404-page
*/


if ( defined( 'ABSPATH' ) )
	Custom404Page::instance();


class Custom404Page {

	var $page_for_404 = null;
	private static $instance;


	public static function instance() {

		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();

		return self::$instance;

	}


	private function __construct() {

		$this->page_for_404 = get_option( 'page_for_404' );

		// Add Page 404 settings to Settings > Reading
		add_action( 'admin_init', array( $this, 'custom_404_page_admin_settings' ) );

		// Load the translation files
		add_action( 'plugins_loaded', array( $this, 'custom_404_page_textdomain' ) );

		if ( $this->page_for_404 ) {

			// Set WP to use page template (page.php) even when returning 404
			add_filter( '404_template', array( $this, 'maybe_use_custom_404_template' ) );
			
			// Set our custom 404 page for the loop
			add_filter( 'the_posts', array( $this, 'maybe_set_custom_404_page' ) );
			
			// Disable direct access to our custom 404 page
			add_action( 'template_redirect',  array( $this, 'maybe_redirect_custom_404_page' ) );

		}

	}


	function custom_404_page_textdomain() {

		load_plugin_textdomain( 
			'custom-404-page', 
			null, 
			basename( dirname( __FILE__ ) ) . '/lang/' 
		);

	}


	function custom_404_page_admin_settings() {
		
		register_setting( 
			'reading', 
			'page_for_404', 
			'intval'
		);

		add_settings_field( 
			'page_for_404', 
			__( 'Page for Error 404 (Not Found)', 'custom-404-page' ), 
			array( $this, 'page_for_404_callback' ), 
			'reading', 
			'default'
		);

	}


	function page_for_404_callback() {

		$exclude = array_filter( array(
			get_option( 'page_on_front' ),
			get_option( 'page_for_posts' )
		) );

		wp_dropdown_pages( array( 
			'show_option_none' => __( 'Default (404.php template)', 'custom-404-page' ),
			'option_none_value' => null,
			'selected' => $this->page_for_404, 
			'name' => 'page_for_404',
			'exclude' => implode( ',', $exclude )
		) );

		printf( 
			'<a href="%s">%s</a>',
			admin_url( '/post-new.php?post_type=page' ),
			__( 'Add New', 'custom-404-page' ) 
		);

	}


	function maybe_use_custom_404_template( $template ) {

		if ( is_404() && $this->page_for_404 ) {

			return get_page_template();

		}

		return $template;

	}


	function maybe_redirect_custom_404_page() {

		if ( ! is_user_logged_in() && is_page( $this->page_for_404 ) ) {

			wp_redirect( home_url(), 301 );
			exit;
		
		}

	}


	function maybe_set_custom_404_page( $posts ) {

		if ( is_404() && $this->page_for_404 ) {

			return array( get_post( $this->page_for_404 ) );

		}

		return $posts;

	}


}

