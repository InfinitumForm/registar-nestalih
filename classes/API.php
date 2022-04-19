<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_API') ) : class Registar_Nestalih_API {
	// PRIVATE: API URL
	private $url = 'https://nestaliapi.delfin.rs/api';
	
	// Run this class on the safe and protected way
	private static $instance;
	private static function instance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// Get remote data
	public static function get( array $query = [] ) {
		return self::instance()->__get_missing( $query );
	}
	
	// PRIVATE: Get missing persons
	private function __get_missing( array $query = [] ) {
		static $__get_missing;
		
		$query_allowed = [
			'paginate',
			'per_page',
			'page',
			'search',
			'order',
			'id'
		];
	
		$query = array_filter($query, function($value, $key) use ($query_allowed){
			return !empty($value) && in_array($key, $query_allowed) !== false;
		}, ARRAY_FILTER_USE_BOTH);
		
		$cache_name = 'registar-nestalih-api-' . md5(serialize($query));
		
		if( $__get_missing[$cache_name] ?? NULL ) {
			return $__get_missing[$cache_name];
		}
		
		$posts = get_transient($cache_name);
		
		if( empty($posts) ) {
			$request = wp_remote_get( add_query_arg(
				$query,
				"{$this->url}/nestale_osobe"
			) );
			
			if( !is_wp_error( $request ) ) {
				if($json = wp_remote_retrieve_body( $request )) {
					$posts = json_decode($json);
				}
			}
			
			set_transient($cache_name, $posts, (HOUR_IN_SECONDS*MISSING_PERSONS_CACHE_IN_HOURS));
			$__get_missing[$cache_name] = $posts;
		}
		
		return $posts;
	}
	
	
	// PRIVATE: Seralize and protect query
	private function __serialize_query( $query ) {
		if( empty($query) && $query !== 0 ) {
			return NULL;
		}
		
		if( is_array($query) ) {
			$serialized_query = [];
			foreach($query as $key => $value) {
				$serialized_query[$key] = self::__serialize_query( $value );
			}
			return $serialized_query;
		} else {
			if( is_numeric($query) ) {
				if( $query == absint($query) ) {
					return absint($query);
				} else if( $query == floatval($query) ) {
					return floatval($query);
				}
			} else if( preg_match('/[^a-z0-9+_.@-]/i', $query) ) {
				return sanitize_email($query);
			} else if( in_array($query, ['true', 'false', true, false]) !== false ) {
				return ($query === 'true' || $query === true);
			}
		}
		
		return sanitize_text_field($query);
	}
	
	// Flush plugin cache
	public static function flush_cache() {
		global $wpdb;
		// Remove all transients
		if ( is_multisite() && is_main_site() && is_main_network() ) {
			$wpdb->query("DELETE FROM
				`{$wpdb->sitemeta}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-api-%'
			)");
		} else {
			$wpdb->query("DELETE FROM
				`{$wpdb->options}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_transient_timeout_registar-nestalih-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-api-%'
			)");
		}
	}
	
} endif;