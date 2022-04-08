<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Template') ) : class Registar_Nestalih_Template {
	// Run this class on the safe and protected way
	private static $instance;
	private static function instance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function get( string $template, $response = [] ) {
		return self::instance()->__load_template( $template, $response );
	}
	
	// PRIVATE Load template
	private function __load_template( string $template, $response = [] ) {
		$active_theme_path = get_stylesheet_directory();
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