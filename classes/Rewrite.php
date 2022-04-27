<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Rewrite') ) : class Registar_Nestalih_Rewrite {
	public $page_id;
	
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
		// here will be option
		$this->page_id = Registar_Nestalih_Options::get('main-page');
		
		add_action( 'init', [&$this, 'add_rewrite_rule'], 1 );
		add_action( 'query_vars', [&$this, 'query_vars'] );
		add_action( 'template_redirect', [&$this, 'wp_redirect'] );
		add_action( 'template_redirect', [&$this, 'push_notification'] );
	}
	
	// Get page
	public static function get_post () {
		static $get_post;
		
		if( !$get_post ) {
			$get_post = get_post( self::instance()->page_id );
		}
		
		return $get_post;
	}
	
	// Add rewrite rules
	public function add_rewrite_rule () {
		global $wp;
		
		add_rewrite_tag('%registar_nestalih_list%', '([0-9]+)');
		add_rewrite_tag('%registar_nestalih_id%', '([0-9]+)');
		add_rewrite_tag('%registar_nestalih_name%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_search%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_push_notification%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_hash%', '([^&]+)');
		
		$page_data = self::get_post();
		
		if( ! is_object($page_data) ) { // post not there
			return;
		}
		
		// Paginaton
		add_rewrite_rule(
			$page_data->post_name . '/' . Registar_Nestalih_Options::get('pagination-slug', 'page') . '/([0-9]+)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_list=$matches[1]',
			'top'
		);
		
		// Person
		add_rewrite_rule(
			$page_data->post_name . '/' . Registar_Nestalih_Options::get('person-slug', 'person') . '/([0-9]+)/([^/]*)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_id=$matches[1]&registar_nestalih_name=$matches[2]',
			'top'
		);
		
		// Search
		add_rewrite_rule(
			$page_data->post_name . '/' . Registar_Nestalih_Options::get('search-slug', 'search') . '/([^/]*)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_search=$matches[1]',
			'top'
		);
		
		// Search with pagination
		add_rewrite_rule(
			$page_data->post_name . '/' . Registar_Nestalih_Options::get('search-slug', 'search') . '/([^/]*)/' . Registar_Nestalih_Options::get('pagination-slug', 'page') . '/([0-9]+)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_search=$matches[1]&registar_nestalih_list=$matches[2]',
			'top'
		);
		
		// Endpoint URL Push notification https://yoursite.com/rnp-notification/{ID}
		add_rewrite_rule(
			'rnp-notification/([^/]*)',
			'index.php?registar_nestalih_push_notification=true&registar_nestalih_hash=$matches[1]',
			'top'
		);
	}
	
	// Add rewrite query vars
	public function query_vars( $query_vars ) {
		$query_vars[] = 'registar_nestalih_list';
		$query_vars[] = 'registar_nestalih_id';
		$query_vars[] = 'registar_nestalih_name';
		$query_vars[] = 'registar_nestalih_search';
		$query_vars[] = 'registar_nestalih_push_notification';
		$query_vars[] = 'registar_nestalih_hash';
		return $query_vars;
	}
	
	// Redirect WordPress in some cases
	public function wp_redirect () {
		global $wp;
		$current_url = home_url( $wp->request );

		$redirect = false;
		
		if( get_option('permalink_structure') ) {
			
			// Fix redirection for the search
			if( isset($_POST['registar_nestalih_search']) ) {
				$page_data = self::get_post();
			
				if(empty($_POST['registar_nestalih_search'])) {
					$redirect = str_replace('/' . Registar_Nestalih_Options::get('search-slug', 'search'), '', $current_url);
				} else {
					$redirect = sprintf(
						'%s/%s',
						rtrim($current_url, '/'),
						trim( esc_html( $_POST['registar_nestalih_search'] ) )
					);
				}
			}
			
		} else {
			
			// Fix redirection for the search
			if( isset($_POST['registar_nestalih_search']) ) {
				if( empty($_POST['registar_nestalih_search']) ) {
					$redirect = remove_query_arg([
						'registar_nestalih_search'
					]);
				} else {					
					$redirect = add_query_arg([
						'registar_nestalih_search' => trim( esc_html( $_POST['registar_nestalih_search'] ) )
					]);
				}
			}
			
		}
		
		// Do redirection
		if( $redirect && wp_safe_redirect($redirect) ) {
			exit;
		}
	}
	
	public function push_notification () {
		global $wp_query;
		
		$push_notification = ( $wp_query->get( 'registar_nestalih_push_notification' ) ?? false );
		
		if( $push_notification ) {
			if( Registar_Nestalih_U::key() === $wp_query->get( 'registar_nestalih_hash' ) ) {
				wp_send_json([
					'status' => __('Success!', Registar_Nestalih::TEXTDOMAIN),
					'id' => Registar_Nestalih_U::id(),
					'timestamp'=>date('c'),
					'error' => false,
					'code' => 200
				], 200);
				Registar_Nestalih_U::cache_flush(true);
				Registar_Nestalih_API::flush_cache();
			} else {
				wp_send_json([
				'status' => __('Sync key does not match.', Registar_Nestalih::TEXTDOMAIN),
				'error' => true,
				'code' => 401
			], 401);
			}
			exit;
		}
		
	}
	
} endif;