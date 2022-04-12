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
		global $wp_query;
		
		$attr = shortcode_atts( [
			'per_page'	=> 8,
			'page'		=> $wp_query->get( 'registar_nestalih_list' ) ?? 1,
			'search'	=> NULL,
			'order'		=> '-id',
			'person'	=> absint($wp_query->get( 'registar_nestalih_id' ) ?? 0)
		], $attr, $tag );
		
		if( $attr['person'] && $attr['person'] > 0 ) {
			$query = [
				'id' => $attr['person']
			];
		} else {
			$query = [
				'paginate'	=> 'true',
				'per_page'	=> absint($attr['per_page']),
				'page'		=> absint($attr['page']),
				'search'	=> $attr['search'],
				'order'		=> $attr['order']
			];
		}
		
		$response = Registar_Nestalih_API::get( $query );
		
		wp_enqueue_style( 'registar-nestalih' );
		wp_enqueue_script( 'registar-nestalih' );
		
		if( $attr['person'] && $attr['person'] > 0 ) {
			return Registar_Nestalih_Content::render('missing-persons-single', $response);
		} else {
			return Registar_Nestalih_Content::render('missing-persons', $response);
		}
	}
	
} endif;