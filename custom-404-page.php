<?php
/*
Plugin Name: Custom 404 Error Page
Plugin URI: 
Description: Set any page to be used as 404 error page.
Version: 0.2.4
Author: Kaspars Dambis
Domain Path: /lang
Text Domain: custom-404-page
*/


if ( defined( 'ABSPATH' ) )
	Custom404Page::instance();


class Custom404Page {

	var $page_for_404 = null;
	private static $instance;
	var $plugin_path;


	public static function instance() {

		if ( self::$instance ) {
			return self::$instance;
		}

		self::$instance = new self();

		return self::$instance;

	}


	private function __construct() {

		// This will allow for symlinked plugins
		$this->plugin_path = sprintf( '%s/%s/%s', WP_PLUGIN_DIR, basename( dirname( __FILE__ ) ), basename( __FILE__ ) );

		$this->page_for_404 = (int) get_option( 'page_for_404' );

		// Add Page 404 settings to Settings > Reading
		add_action( 'admin_init', array( $this, 'custom_404_page_admin_settings' ) );

		// Add settings to Theme Customizer as well
		add_action( 'customize_register', array( $this, 'custom_404_page_customizer_init' ) );

		// Load the translation files
		add_action( 'plugins_loaded', array( $this, 'custom_404_page_textdomain' ) );

		if ( $this->page_for_404 ) {

			// Set WP to use page template (page.php) even when returning 404
			add_filter( '404_template', array( $this, 'maybe_use_custom_404_template' ) );
			
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

		/**
		 * Add a direct link to the plugin config on the plugin list page
		 */
		
		add_filter( 
			'plugin_action_links_' . plugin_basename( $this->plugin_path ), 
			array( $this, 'plugin_settings_link' ) 
		);
		

		/**
		 * Use Settings API to add our field to the Reading Options page
		 */
		
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


	function custom_404_page_customizer_init( $wp_customize ) {

		$wp_customize->add_setting( 
			'page_for_404', 
			array(
				'type' => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control( 
			'page_for_404', 
			array(
				'label' => __( 'Error 404 page', 'custom-404-page' ),
				'section' => 'static_front_page',
				'type' => 'dropdown-pages',
				'priority' => 20
			)
		);

		return $wp_customize;

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

		global $wp_query, $post;

		if ( is_404() && $this->page_for_404 ) {

			// Get our custom 404 post object. We need to assign
			// $post global in order to force get_post() to work
			// during page template resolving.
			$post = get_post( $this->page_for_404 );

			// Populate the posts array with our 404 page object
			$wp_query->posts = array( $post );

			// Set the query object to enable support for custom page templates
			$wp_query->queried_object_id = $this->page_for_404;
			$wp_query->queried_object = $post;
			
			// Set post counters to avoid loop errors
			$wp_query->post_count = 1;
			$wp_query->found_posts = 1;
			$wp_query->max_num_pages = 0;

			// Return the page.php template instead of 404.php
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


	function plugin_settings_link( $links ) {
		
		$links[] = sprintf( 
			'<a href="%s">%s</a>', 
			admin_url( 'options-reading.php' ), 
			__( 'Settings', 'custom-404-page' ) 
		);
		
		return $links;

	}


}

