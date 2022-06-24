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
		add_action( 'wp_enqueue_scripts', [ &$this, 'enqueue_scripts' ] );
	}

	// Get URL
	public static function url ($location) {
		static $cache = [];
		
		if( !isset($cache[$location]) ) {		
			$active_theme_path = rtrim(get_stylesheet_directory(), '/');
			$plugin_path = MISSING_PERSONS_ROOT;
			
			$path = $active_theme_path . '/registar-nestalih/' . ltrim($location, '/');
			$url = str_replace(
				$active_theme_path,
				rtrim(get_stylesheet_directory_uri(), '/'),
				$path
			);

			if( !file_exists($path) ) {
				$path = $plugin_path . '/templates/' . ltrim($location, '/');
				$url = str_replace(
					$plugin_path,
					rtrim(plugin_dir_url( MISSING_PERSONS_FILE ), '/'),
					$path
				);
			}
			
			$cache[$location] = $url;
		}
		
		return apply_filters('registar_nestalih_template_path_url', $cache[$location], $location, $cache);
	}
	
	// Get path
	public static function path ($location) {
		static $cache = [];
		
		if( !isset($cache[$location]) ) {		
			$active_theme_path = rtrim(get_stylesheet_directory(), '/');
			$plugin_path = MISSING_PERSONS_ROOT;
			
			$path = $active_theme_path . '/registar-nestalih/' . ltrim($location, '/');

			if( !file_exists($path) ) {
				$path = $plugin_path . '/templates/' . ltrim($location, '/');
			}
			
			$cache[$location] = $path;
		}
		
		return apply_filters('registar_nestalih_template_path', $cache[$location], $location, $cache);
	}
	
	// Register plugin scripts
	public function enqueue_scripts() {
		
		$bootstrap_css_url = self::url('assets/css/bootstrap.min.css');
		$bootstrap_css_path = self::path('assets/css/bootstrap.min.css');
		
		$css_url = self::url('assets/css/style.css');
		$css_path = self::path('assets/css/style.css');
		
		$js_url = self::url('assets/js/script.js');	
		$js_path = self::path('assets/js/script.js');
		
		wp_register_style( 'registar-nestalih', $css_url, 1, '1.' . absint(filesize($css_path)) );
		
		if( Registar_Nestalih_Options::get('enable-bootstrap', 0) ) {
			wp_register_style( 'registar-nestalih-bootstrap', $bootstrap_css_url, 1, '1.' . absint(filesize($bootstrap_css_path)) );
			wp_register_style( 'registar-nestalih', $css_url, ['registar-nestalih-bootstrap'], '1.' . absint(filesize($css_path)) );
		} else {
			wp_register_style( 'registar-nestalih', $css_url, 1, '1.' . absint(filesize($css_path)) );
		}
		
		wp_register_script( 'registar-nestalih', $js_url, ['jquery'], '1.' . absint(filesize($js_path)), true );
		wp_localize_script( 'registar-nestalih', 'registar_nestalih', [
			'ajax' => admin_url('/admin-ajax.php'),
			'label' => [
				'loading' => __('Please wait...', 'registar-nestalih'),
				'form_error' => __('All fields in this form are required. Fill in the fields and send a message.', 'registar-nestalih'),
				'terms_error' => __('You must accept the terms and conditions of the Register of Missing Persons of Serbia.', 'registar-nestalih')
			]
		] );
	}

	// Get template
	public static function get( string $template, $response = [] ) {
		return self::instance()->__load_template( $template, $response );
	}
	
	// PRIVATE Load template
	private function __load_template( string $template, $response = [] ) {
		$active_theme_path = rtrim(get_stylesheet_directory(), '/');
		$plugin_path = MISSING_PERSONS_ROOT;
		
		$path = $active_theme_path . '/registar-nestalih/' . $template . '.php';
		if( !file_exists($path) ) {
			$path = $plugin_path . '/templates/' . $template . '.php';
		}
		
		if( file_exists($path) ) {
			global $missing_response;
			$missing_response = $response;
			include_once $path;
		}
	}
	
} endif;