<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $WP_ADMIN_DIR, $WP_ADMIN_URL;

// Find wp-admin file path

if (!defined('WP_ADMIN_DIR')) {
	if( $WP_ADMIN_DIR ) {
		define('WP_ADMIN_DIR', $WP_ADMIN_DIR);
	} else {
		if( !$WP_ADMIN_URL ) {
			$WP_ADMIN_URL = admin_url('/');
		}
		
		if( strpos($WP_ADMIN_URL, 'wp-admin') !== false ) {
			$WP_ADMIN_DIR = rtrim(str_replace(home_url('/') , strtr(ABSPATH, '\\', '/'), $WP_ADMIN_URL) , '/\\');
		} else {
			$WP_ADMIN_DIR = dirname(WP_CONTENT_DIR) . DIRECTORY_SEPARATOR . 'wp-admin';
		}
		
		define('WP_ADMIN_DIR', $WP_ADMIN_DIR);
	}
}

// Is plugin in development mode
if ( ! defined( 'MISSING_PERSONS_DEV_MODE' ) ) {
	define( 'MISSING_PERSONS_DEV_MODE', false );
}

// Set cache time in minutes
if ( ! defined( 'MISSING_PERSONS_CACHE_IN_MINUTES' ) ) {
	define( 'MISSING_PERSONS_CACHE_IN_MINUTES', 60 );
}

// Set filename in /uploads folder
if ( ! defined( 'MISSING_PERSONS_IMG_UPLOAD_DIR' ) ) {
	define( 'MISSING_PERSONS_IMG_UPLOAD_DIR', '/registar-nestalih' );
}

