<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Rewrite') ) : class Registar_Nestalih_Rewrite {
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
		add_action( 'init', [$this, 'add_rewrite_rule'], 1 );
		add_action( 'query_vars', [$this, 'query_vars'] );
	}
	
	// Add rewrite rules
	public function add_rewrite_rule () {
		global $wp;
		
		add_rewrite_tag('%registar_nestalih_list%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_id%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_name%', '([^&]+)');
		add_rewrite_tag('%registar_nestalih_search%', '([^&]+)');
		
		$page_id = 44; // update 2 TEST
		$page_data = get_post( $page_id );
		
		if( ! is_object($page_data) ) { // post not there
			return;
		}
		
		// Paginaton
		add_rewrite_rule(
			$page_data->post_name . '/lista/([0-9]+)[/]?$',
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
			$page_data->post_name . '/pretraga/([^/]*)[/]?$',
			'index.php?pagename=' . $page_data->post_name . '&registar_nestalih_search=$matches[1]',
			'top'
		);
	}
	
	// Add rewrite query vars
	function query_vars( $query_vars ) {
		$query_vars[] = 'registar_nestalih_list';
		$query_vars[] = 'registar_nestalih_id';
		$query_vars[] = 'registar_nestalih_name';
		$query_vars[] = 'registar_nestalih_search';
		return $query_vars;
	}
	
} endif;