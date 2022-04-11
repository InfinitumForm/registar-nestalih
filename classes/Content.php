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
		add_action( 'registar_nestalih_pagination', [$this, 'do_missing_persons_pagination'] );
		add_action( 'registar_nestalih_breadcrumb', [$this, 'do_breadcrumb'] );
		add_filter( 'document_title_parts', [$this, 'document_title_parts'], 100, 2 );
	}
	
	// Change page title
	public function document_title_parts( $parts ) {
		global $wp_query;

		if( $wp_query && 0 < ( $person_id = absint($wp_query->get( 'registar_nestalih_id' ) ?? 0) ) ) {
			if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
				$response = new Registar_Nestalih_Render($response);
				$parts['title'] = esc_html($response->ime_prezime);
			}
		}

		return $parts;
	}
	
	// Render content from template
	public static function render ( string $type = 'missing-persons', $response = [] ) {
		ob_start();
			switch ($type) {
				
				case 'missing-persons':
					Registar_Nestalih_Template::get('missing-persons', $response);
					break;
				
				case 'missing-persons-single':
					Registar_Nestalih_Template::get('missing-persons/single', $response);
					break;
				
			}
		return ob_get_clean();
	}
	
	// Render paginations
	public function do_missing_persons_pagination( $response ){
		
		global $last_page, $next_page, $prev_page;
		
		// Get last page
		$last_page = ceil(absint($response->total)/absint($response->per_page));

		$prev_page = (absint($response->current_page ?? $last_page)-1);
		if($prev_page < 0) {
			$prev_page = 0;
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( get_the_ID() );
			
			$next_page = sprintf(
				'%s/lista/%d',
				rtrim($page_link, '/'),
				(absint($response->current_page ?? 0)+1)
			);
			$prev_page = sprintf(
				'%s/lista/%d',
				rtrim($page_link, '/'),
				$prev_page
			);
		} else {
			$next_page = add_query_arg([
				'lista'=>(absint($response->current_page ?? 0)+1)
			]);
			$prev_page = add_query_arg([
				'lista'=>$prev_page
			]);
		}
		

		Registar_Nestalih_Template::get('missing-persons/pagination', $response);
	}
	
	// Render breadcrumb
	public function do_breadcrumb( $response ){
		/* TO DO */
	}
	
} endif;