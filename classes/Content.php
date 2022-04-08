<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Content') ) : class Registar_Nestalih_Content {
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
		add_action( 'registar_nestalih_pagination', [$this, 'do_pagination'] );
		add_action( 'registar_nestalih_breadcrumb', [$this, 'do_breadcrumb'] );
	}
	
	// Render content from template
	public static function render ( string $type = 'missing-persons', $response = [] ) {
		ob_start();
			switch ($type) {
				
				case 'missing-persons':
					Registar_Nestalih_Template::get('missing-persons', $response);
					break;
				
				case 'single':
					Registar_Nestalih_Template::get('single', $response);
					break;
				
			}
		return ob_get_clean();
	}
	
	// Render paginations
	public function do_pagination( $response ){
		
		global $last_page, $current_url;
		
		// Get last page
		$last_page = ceil(absint($response->total)/absint($response->per_page));

		// Get current URL
		$current_url = 'http' . ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 ? 's' : '');
		$current_url.= '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '') . ($_SERVER['QUERY_STRING'] ?? '');

		Registar_Nestalih_Template::get('pagination', $response);
	}
	
	// Render breadcrumb
	public function do_breadcrumb( $response ){
		/* TO DO */
	}
	
} endif;