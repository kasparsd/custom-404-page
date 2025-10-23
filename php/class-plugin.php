<?php

class Custom404Page {

	const OPTION_404_PAGE = 'page_for_404';

	private $page_for_404;
	
	private $plugin_path;

	public function __construct( $plugin_path ) {
		$this->plugin_path = $plugin_path;
	}

	public static function instance() {
		return custom_404_page();
	}

	public function init() {
		$this->page_for_404 = (int) get_option( self::OPTION_404_PAGE );

		// Add Page 404 settings to Settings > Reading
		add_action( 'admin_init', array( $this, 'custom_404_page_admin_settings' ) );

		// Add settings to Theme Customizer as well
		add_action( 'customize_register', array( $this, 'custom_404_page_customizer_init' ) );

		if ( $this->page_for_404 ) {
			// Set WP to use page template (page.php) even when returning 404
			add_filter( '404_template', array( $this, 'maybe_use_custom_404_template' ) );

			// Disable direct access to our custom 404 page
			add_action( 'template_redirect',  array( $this, 'maybe_redirect_custom_404_page' ) );
		}
	}

	public function custom_404_page_admin_settings() {
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
			self::OPTION_404_PAGE,
			'intval'
		);

		add_settings_field(
			self::OPTION_404_PAGE,
			__( 'Page for Error 404 (Not Found)', 'custom-404-page' ),
			array( $this, 'page_for_404_callback' ),
			'reading',
			'default'
		);
	}

	public function custom_404_page_customizer_init( $wp_customize ) {
		$wp_customize->add_setting(
			self::OPTION_404_PAGE,
			array(
				'type' => 'option',
				'capability' => 'manage_options'
			)
		);

		$wp_customize->add_control(
			self::OPTION_404_PAGE,
			array(
				'label' => __( 'Error 404 page', 'custom-404-page' ),
				'section' => 'static_front_page',
				'type' => 'dropdown-pages',
				'priority' => 20
			)
		);

		return $wp_customize;
	}

	public function page_for_404_callback() {
		$exclude = array_filter( array(
			(int) get_option( 'page_on_front' ),
			(int) get_option( 'page_for_posts' )
		) );

		wp_dropdown_pages( array(
			'show_option_none' => __( 'Default (404.php template)', 'custom-404-page' ),
			'option_none_value' => null,
			'selected' => $this->page_for_404,
			'name' => self::OPTION_404_PAGE,
			'exclude' => implode( ',', $exclude )
		) );

		printf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( '/post-new.php?post_type=page' ) ),
			esc_html__( 'Add New', 'custom-404-page' )
		);
	}

	public function maybe_use_custom_404_template( $template ) {
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

	public function maybe_redirect_custom_404_page() {
		if ( ! is_user_logged_in() && is_page( $this->page_for_404 ) ) {
			wp_redirect( home_url(), 301 );
			exit;
		}
	}

	public function plugin_settings_link( $links ) {
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-reading.php' ) ),
			esc_html__( 'Settings', 'custom-404-page' )
		);

		return $links;
	}
}
