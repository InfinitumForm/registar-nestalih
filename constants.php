<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $WP_ADMIN_DIR, $WP_ADMIN_URL;

// Find wp-admin file path
$WP_ADMIN_URL = admin_url('/');
if (!defined('WP_ADMIN_DIR')) {
	if( strpos($WP_ADMIN_URL, 'wp-admin') !== false ) {
		$WP_ADMIN_DIR = rtrim(str_replace(home_url('/') , ABSPATH, $WP_ADMIN_URL) , '/\\');
	} else {
		$WP_ADMIN_DIR = dirname(WP_CONTENT_DIR) . '/wp-admin';
	}
	define('WP_ADMIN_DIR', $WP_ADMIN_DIR);
}

// Set cache time in minutes
if ( ! defined( 'MISSING_PERSONS_CACHE_IN_MINUTES' ) ) {
	define( 'MISSING_PERSONS_CACHE_IN_MINUTES', 60 );
}

