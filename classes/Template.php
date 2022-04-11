<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Template') ) : class Registar_Nestalih_Template {
	// Run this class on the safe and protected way
	private static $instance;
	public static function instance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// PRIVATE: Main construct
	private function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
	
	// Register plugin scripts
	public function enqueue_scripts() {
		$active_theme_path = rtrim(get_stylesheet_directory(), '/');
		$plugin_path = MISSING_PERSONS_ROOT;
		
		$css_path = $active_theme_path . '/registar-nestalih/assets/css/style.css';
		$css_location = str_replace(
			$active_theme_path,
			rtrim(get_stylesheet_directory_uri(), '/'),
			$css_path
		);

		if( !file_exists($css_path) ) {
			$css_path = $plugin_path . '/templates/assets/css/style.css';
			$css_location = str_replace(
				$plugin_path,
				rtrim(plugin_dir_url( MISSING_PERSONS_FILE ), '/'),
				$css_path
			);
		}
		
		wp_register_style( 'registar-nestalih', $css_location, 1, 'RV-1.' . absint(filesize($css_path)) );
	}

	// Get template
	public static function get( string $template, $response = [] ) {
		return self::instance()->__load_template( $template, $response );
	}
	
	// PRIVATE Load template
	private function __load_template( string $template, $response = [] ) {
		$active_theme_path = rtrim(get_stylesheet_directory(), '/');
		$plugin_path = MISSING_PERSONS_ROOT;
		
		$location = $active_theme_path . '/registar-nestalih';
		if( !file_exists($location) ) {
			$location = $plugin_path . '/templates';
		}
		
		$path = $location . '/' . $template . '.php';
		
		if( file_exists($path) ) {
			global $missing_response;
			$missing_response = $response;
			include_once $path;
		}
	}
	
} endif;