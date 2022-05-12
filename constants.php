<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Set cache time in minutes
if ( ! defined( 'MISSING_PERSONS_CACHE_IN_MINUTES' ) ) {
	define( 'MISSING_PERSONS_CACHE_IN_MINUTES', 60 );
}

// Set filename in /uploads folder
if ( ! defined( 'MISSING_PERSONS_IMG_UPLOAD_DIR' ) ) {
	define( 'MISSING_PERSONS_IMG_UPLOAD_DIR', '/registar-nestalih' );
}

