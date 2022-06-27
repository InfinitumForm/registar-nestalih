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
		$this->register( 'registar_nestalih_najnoviji', 'callback__registar_nestalih_najnoviji' );
		$this->register( 'registar_nestalih_prijava', 'callback__registar_nestalih_prijava' );
		$this->register( 'registar_nestalih_pitanja_saveti', 'callback__registar_nestalih_pitanja_saveti' );
		$this->register( 'registar_nestalih_amber_alert', 'callback__registar_nestalih_amber_alert' );
	}
	
	// Register shortcodes
	public function register( string $tag, $callback ) {
		if( !shortcode_exists( $tag ) ) {
			add_shortcode( $tag, [&$this, $callback] );
		}
	}
	
	/*
	 * Register Nestalih
	 */
	public function callback__registar_nestalih	($attr, $content='', $tag) {
		global $wp_query;
		
		$attr = shortcode_atts( [
			'per_page'	=> 8,
			'page'		=> ( $wp_query->get( 'registar_nestalih_list' ) ?? 1 ),
			'search'	=> ( $wp_query->get( 'registar_nestalih_search' ) ?? NULL ),
			'order'		=> '-id',
			'person'	=> absint( $wp_query->get( 'registar_nestalih_id' ) ?? 0 )
		], $attr, $tag );
		
		if( !empty($attr['search']) ) {
			$attr['per_page'] = 9999;
		}
		
		
		if( $attr['person'] && $attr['person'] > 0 ) {
			$query = [
				'id' => $attr['person']
			];
		} else {
			$query = [
				'paginate'	=> 'true',
				'per_page'	=> absint($attr['per_page']),
				'page'		=> absint($attr['page']),
				'search'	=> sanitize_text_field($attr['search']),
				'order'		=> sanitize_text_field($attr['order'])
			];
		}
		
		$response = Registar_Nestalih_API::get( $query );
		
		if( Registar_Nestalih_Options::get('enable-bootstrap', 0) ) {
			wp_enqueue_style( 'registar-nestalih-bootstrap' );
			wp_enqueue_style( 'registar-nestalih' );
		} else {
			wp_enqueue_style( 'registar-nestalih' );
		}
		wp_enqueue_script( 'registar-nestalih' );
		
		if( $attr['person'] && $attr['person'] > 0 ) {
			return Registar_Nestalih_Content::render('missing-persons-single', $response);
		} else {
			return Registar_Nestalih_Content::render('missing-persons', $response);
		}
	}
	
	/*
	 * Register Nestalih Najnoviji
	 */
	public function callback__registar_nestalih_najnoviji ($attr, $content='', $tag) {
		$attr = shortcode_atts( [
			'position' => 'before'
		], $attr, $tag );
		
		$missing = Registar_Nestalih_API::get([
			'paginate' => 'true',
			'per_page' => 1,
			'page' => 1,
			'order' => '-id'
		]);
		
		if($missing && !empty($missing->data ?? NULL)) {
			$missing = new Registar_Nestalih_Render($missing->data[0]);
			
			if( Registar_Nestalih_Options::get('enable-bootstrap', 0) ) {
				wp_enqueue_style( 'registar-nestalih-bootstrap' );
				wp_enqueue_style( 'registar-nestalih' );
			} else {
				wp_enqueue_style( 'registar-nestalih' );
			}
			
			if( !empty($content) ) {
				$prepend = $append = '';
				
				if( $attr['position'] == 'both' ) {
					$prepend = $append = Registar_Nestalih_Content::render('missing-persons-in-content', $missing);
				} else if( $attr['position'] == 'after' ) {
					$append = Registar_Nestalih_Content::render('missing-persons-in-content', $missing);
				} else {
					$prepend = Registar_Nestalih_Content::render('missing-persons-in-content', $missing);
				}

				return $prepend . $content . $append;
			} else {
				return Registar_Nestalih_Content::render('missing-persons-in-content', $missing);
			}
		}
		
		return '';
	}
	
	/*
	 * Register Nestalih Prijava
	 */
	public function callback__registar_nestalih_prijava	($attr, $content='', $tag) {
		$attr = shortcode_atts( [
			
		], $attr, $tag );
		
		if( Registar_Nestalih_Options::get('enable-bootstrap', 0) ) {
			wp_enqueue_style( 'registar-nestalih-bootstrap' );
			wp_enqueue_style( 'registar-nestalih' );
		} else {
			wp_enqueue_style( 'registar-nestalih' );
		}
		wp_enqueue_script( 'registar-nestalih' );
		
		return Registar_Nestalih_Content::render('report-disappearance');
	}
	
	/*
	 * Register Nestalih Questons & Answers
	 */
	public function callback__registar_nestalih_pitanja_saveti ($attr, $content='', $tag) {	
		$attr = array_filter( shortcode_atts( [
	/*		'per_page'	=> NULL,
			'page'		=> NULL,
			'search'	=> NULL,
			'order'		=> NULL	*/
		], $attr, $tag ) );
		
		$query = [];
		/*
		if( $attr['per_page'] ?? NULL ) {
			$query = [
				'paginate'	=> 'true',
				'per_page'	=> absint($attr['per_page']),
				'page'		=> absint($attr['page']),
				'search'	=> sanitize_text_field($attr['search']),
				'order'		=> sanitize_text_field($attr['order'])
			];
		}
		*/
		
		$response = Registar_Nestalih_API::get_qa();
		
		sort($response);
		
		return Registar_Nestalih_Content::render('question-and-answer', $response);
	}
	
	/*
	 * Register Nestalih Amber Alert
	 */
	public function callback__registar_nestalih_amber_alert ($attr, $content='', $tag) {
		$attr = array_filter( shortcode_atts( [
			'per_page'	=> NULL,
			'page'		=> NULL,
			'search'	=> NULL,
			'order'		=> NULL
		], $attr, $tag ) );
		
		$query = [];
		if( $attr['per_page'] ?? NULL ) {
			$query = [
				'paginate'	=> 'true',
				'per_page'	=> absint($attr['per_page']),
				'page'		=> absint($attr['page']),
				'search'	=> sanitize_text_field($attr['search']),
				'order'		=> sanitize_text_field($attr['order'])
			];
		}
		
		$response = Registar_Nestalih_API::get_amber_alert($query);
		
		return Registar_Nestalih_Content::render('amber-alert', $response);
	}
	
} endif;