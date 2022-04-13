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
		$this->page_id = self::option('main-page');
		
		add_action( 'init', [$this, 'add_rewrite_rule'], 1 );
		add_action( 'query_vars', [$this, 'query_vars'] );
		add_action( 'template_redirect', [$this, 'wp_redirect'] );
	}
	
	// Get options
	public static function option ($name = NULL, $default = '') {
		static $options;
		
		if( !$options ) {
			$options = get_option( 'registar_nestalih' );
		}
		
		if($name) {
			return $options[$name] ?? $default;
		} else {
			return $options ?? $default;
		}
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
		
		add_rewrite_tag('%registar_nestalih_list%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_id%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_name%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_search%', '([^&]+)');
		
		$page_data = self::get_post();
		
		if( ! is_object($page_data) ) { // post not there
			return;
		}
		
		// Paginaton
		add_rewrite_rule(
			$page_data->post_name . '/' . self::option('pagination-slug', 'page') . '/([0-9]+)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_list=$matches[1]',
			'top'
		);
		
		// Person
		add_rewrite_rule(
			$page_data->post_name . '/osoba/([0-9]+)/([^/]*)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_id=$matches[1]&registar_nestalih_name=$matches[2]',
			'top'
		);
		
		// Search
		add_rewrite_rule(
			$page_data->post_name . '/' . self::option('search-slug', 'search') . '/([^/]*)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_search=$matches[1]',
			'top'
		);
		
		// Search with pagination
		add_rewrite_rule(
			$page_data->post_name . '/' . self::option('search-slug', 'search') . '/([^/]*)/' . self::option('pagination-slug', 'page') . '/([0-9]+)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_search=$matches[1]&registar_nestalih_list=$matches[2]',
			'top'
		);
	}
	
	// Add rewrite query vars
	public function query_vars( $query_vars ) {
		$query_vars[] = 'registar_nestalih_list';
		$query_vars[] = 'registar_nestalih_id';
		$query_vars[] = 'registar_nestalih_name';
		$query_vars[] = 'registar_nestalih_search';
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
					$redirect = str_replace('/' . self::option('search-slug', 'search'), '', $current_url);
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
	
} endif;