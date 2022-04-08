<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Shortcodes') ) : class Registar_Nestalih_Shortcodes {
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
		$this->register( 'registar_nestalih', 'callback__registar_nestalih' );
	}
	
	// Register shortcodes
	public function register( string $tag, $callback ) {
		if( !shortcode_exists( $tag ) ) {
			add_shortcode( $tag, [$this, $callback] );
		}
	}
	
	// Register Nestalih
	public function callback__registar_nestalih	($attr, $content='', $tag) {
		$attr = shortcode_atts( [
			'per_page'	=> 20,
			'page'		=> 1,
			'search'	=> NULL,
			'order'		=> '-id',
		], $attr, $tag );
		
		$query = [
			'paginate'	=> 'true',
			'per_page'	=> absint($attr['per_page']),
			'page'		=> absint($attr['page']),
			'search'	=> $attr['search'],
			'order'		=> $attr['order']
		];
		
		
		$response = Registar_Nestalih_API::get( $query );
		
		return Registar_Nestalih_Content::render('missing-persons', $response);
	}
	
} endif;