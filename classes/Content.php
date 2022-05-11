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
		add_action( 'registar_nestalih_before_main_container', [&$this, 'do_missing_persons_search'], 10 );
		add_action( 'registar_nestalih_pagination', [&$this, 'do_missing_persons_pagination'] );
		add_action( 'registar_nestalih_breadcrumb', [&$this, 'do_breadcrumb'] );
		
		if( Registar_Nestalih_Options::get('enable-notification') ) {
			add_action( 'registar_nestalih_before_single_container', [&$this, 'do_missing_persons_contact_form_http'] );
			add_action( 'registar_nestalih_after_single_container', [&$this, 'do_missing_persons_contact_form'] );
		}
		
		add_filter( 'document_title_parts', [&$this, 'document_title_parts'], 100, 2 );
		
	//	add_action( 'template_redirect', [&$this, 'load_image'], 99, 0 );
	}
	
	// load image from the site
	/*
	public function load_image() {
		static $cache = [];
		global $wp_query;
		
		$person_id = $wp_query->get( 'registar_nestalih_img_id' );
		
		$image = NULL;
		$filename = 'avatar';
		
		
		if( isset($cache[$person_id]) ) {
			$image = $cache[$person_id]['image'];
			$filename = $cache[$person_id]['filename'];
		} else {		
			if( $person_id )	{
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					if( $get_image = $response->profile_image() ) {
						$image = file_get_contents($get_image);
					}
					$filename = sanitize_title($response->ime_prezime);
				}
			}
			
			if( empty($image) ) {
				$image = file_get_contents(Registar_Nestalih_Template::path('assets/images/no-image-male.gif'));
			}
			
			$cache[$person_id]['image'] = $image;
			$cache[$person_id]['filename'] = $filename;
		}
		
		header('Content-type: image/jpeg');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: no-store, no-cache');
		header('Pragma: public');
		header('Content-Disposition: inline; filename="' . $filename . '.jpg"');
		
		echo $image;
		
		exit;
	}
	*/
	
	// Get date format
	public static function get_date_format() {
		static $format;
		
		if( !$format ) {
			$format = get_option( 'date_format' );
		}
		
		return $format;
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
					
				case 'sidebar':
					Registar_Nestalih_Template::get('missing-persons/sidebar', $response);
					break;
				
			}
		return ob_get_clean();
	}
	
	// Render paginations
	public function do_missing_persons_pagination( $response ){
		
		global $last_page, $next_page, $prev_page, $current_page;
		
		// Get last page
		$last_page = ceil(absint($response->total??0)/absint($response->per_page??0));
		
		$current_page = absint($response->current_page ?? 0);

		$prev_page = ($current_page-1);
		if($prev_page < 0) {
			$prev_page = 0;
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( Registar_Nestalih_Options::get('main-page') );
			
			$next_page = sprintf(
				'%s/%s/%d',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('pagination-slug', 'page'),
				($current_page+1)
			);
			$prev_page = sprintf(
				'%s/%s/%d',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('pagination-slug', 'page'),
				$prev_page
			);
		} else {
			$next_page = add_query_arg([
				'registar_nestalih_list'=>($current_page+1)
			]);
			$prev_page = add_query_arg([
				'registar_nestalih_list'=>$prev_page
			]);
		}
		

		Registar_Nestalih_Template::get('missing-persons/pagination', $response);
	}
	
	// Render breadcrumb
	public function do_breadcrumb( $response ){
		/* TO DO */
	}
	
	// Render Search Form
	public function do_missing_persons_search( $response ){
		global $action_url;
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( Registar_Nestalih_Options::get('main-page') );
			
			$action_url = sprintf(
				'%s/%s',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('search-slug', 'search')
			);
		} else {
			$action_url = add_query_arg([
				'registar_nestalih_search'=>''
			]);
		}
		
		Registar_Nestalih_Template::get('missing-persons/search-form', $response);
	}
	
	// Contact form HTTP response
	public function do_missing_persons_contact_form_http ( $response ) {
		$send_message = $response->send_information();
		
		if(NULL !== $send_message) {
			if( $send_message ) {
				printf(
					'<div class="alert alert-success" role="alert">%s</div>',
					sprintf(
						__('Information about %s sent successfully.', 'registar-nestalih'),
						$response->ime_prezime
					)
				);
			} else {
				printf(
					'<div class="alert alert-danger" role="alert">%s</div>',
					__('The message was not sent due to technical problems.', 'registar-nestalih')
				);
			}
		}
	}
	
	// Add contact form to the single
	public function do_missing_persons_contact_form ( $response ) {
		Registar_Nestalih_Template::get('missing-persons/contact-form', $response);
	}
	
} endif;