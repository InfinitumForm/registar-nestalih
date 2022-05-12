<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_U') ) : class Registar_Nestalih_U {
	/*
	 * Get plugin ID
	 * @return        string
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function ID() {
		static $ID;

		if( !$ID ) {
			$ID = get_option('Registar-Nestalih-ID');
			
			if( !$ID ) {
				$ID = ('RN_'.self::generate_token(55).'_'.self::generate_token(4));
				add_option('Registar-Nestalih-ID', $ID, true);
			}
		}

		return $ID;
	}
	
	/*
	 * Get plugin KEY for the REST API
	 * @return        string
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function KEY() {
		static $key;
		
		if( !$key ) {
			$key = str_rot13(substr(self::ID(), 3, 21));
		}
		
		return $key;
	}
	
	/* 
	 * Generate unique token
	 * @author        Ivijan-Stefan Stipic
	*/
	public static function generate_token(int $length=16){
		if(function_exists('openssl_random_pseudo_bytes') || function_exists('random_bytes'))
		{
			if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
				return substr(str_rot13(bin2hex(random_bytes(ceil($length * 2)))), 0, $length);
			} else {
				return substr(str_rot13(bin2hex(openssl_random_pseudo_bytes(ceil($length * 2)))), 0, $length);
			}
		}
		else
		{
			return substr(str_replace(['.',' ','_'],mt_rand(1000,9999),uniqid('t'.microtime())), 0, $length);
		}
	}
	
	/*
	 * Flush Cache
	 * @verson    2.0.0
	*/
	public static function cache_flush ( $force = false ) {
		global $post, $user, $w3_plugin_totalcache;

		// Standard cache
		header('Expires: Tue, 01 Jan 2000 00:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');

		// Set nocache headers
		if(function_exists('nocache_headers')) {
			nocache_headers();
		}

		// Flush WP cache
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}

		// W3 Total Cache
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		} else if( $w3_plugin_totalcache ) {
			$w3_plugin_totalcache->flush_all();
		}

		// WP Fastest Cache
		if (function_exists('wpfc_clear_all_cache')) {
			wpfc_clear_all_cache(true);
		}
/*
		// WP Rocket
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
*/
		// WP Super Cache
		if(function_exists( 'prune_super_cache' ) && function_exists( 'get_supercache_dir' )) {
			prune_super_cache( get_supercache_dir(), true );
		}

		// Cache Enabler.
		if (function_exists( 'clear_site_cache' )) {
			clear_site_cache();
		}

		// Clean stanrad WP cache
		if($post && function_exists('clean_post_cache')) {
			clean_post_cache( $post );
		}

		// Comet Cache
		if(class_exists('comet_cache') && method_exists('comet_cache', 'clear')) {
			comet_cache::clear();
		}

		// Clean user cache
		if($user && function_exists('clean_user_cache')) {
			clean_user_cache( $user );
		}
		
		if( $force ) {
			self::flush_plugin_cache();
		}
	}
	
	/*
	 * Flush Plugin cache
	 * @verson    1.0.0
	*/
	public static function flush_plugin_cache() {
		global $wpdb;
		// Remove all transients
		if ( is_multisite() && is_main_site() && is_main_network() ) {
			$wpdb->query("DELETE FROM
				`{$wpdb->sitemeta}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_registar-nestalih-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-%'
			)");
		} else {
			$wpdb->query("DELETE FROM
				`{$wpdb->options}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_registar-nestalih-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_timeout_registar-nestalih-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_registar-nestalih-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-%'
			)");
		}
		
		Registar_Nestalih_Cache::flush();
	}
	
	/*
	 * Remove directory
	 * @verson    1.0.0
	*/
	function rmdir($dir) {
		
		if( strlen($dir, MISSING_PERSONS_IMG_UPLOAD_DIR) === false ) {
			return false;
		}
		
		if( DIRECTORY_SEPARATOR === '\\' ) {
			$dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
		}
		
		if ( !is_dir($dir) || is_link($dir) ) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $file) {
			if ( in_array($file, ['.', '..'], true) ) {
				continue;
			}
			
			if ( !self::rmdir($dir . DIRECTORY_SEPARATOR . $file) ) {
				
				chmod($dir . DIRECTORY_SEPARATOR . $file, 0777);
				
				if ( !self::rmdir($dir . DIRECTORY_SEPARATOR . $file) ) {
					return false;
				}
			}
		}
		
		return rmdir($dir);
	}
} endif;