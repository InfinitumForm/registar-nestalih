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
		add_action( 'init', [$this, 'add_rewrite_rule'] );
		add_action( 'query_vars', [$this, 'query_vars'] );
		add_action( 'parse_request', [$this, 'parse_request'] );
	}
	
	// Add rewrite rules
	public function add_rewrite_rule () {
		add_rewrite_rule('nestali/(\d*)$', 'index.php?nestali=$matches[1]', 'top');
	}
	
	// Add rewrite query vars
	function query_vars( $query_vars )
	{
		$query_vars[] = 'nestali';
		return $query_vars;
	}
	
	// We must parse request to get content from template
	function parse_request( &$wp ){
		if ( array_key_exists( 'nestali', $wp->query_vars ) ) {
			Registar_Nestalih_Template::get('nestali', $response); /* TO DO!!! */
			exit();
		}
	}
	
} endif;