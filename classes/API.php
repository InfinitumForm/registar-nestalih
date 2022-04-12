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
		
		if( !($posts = get_transient($cache_name)) ) {
			$request = wp_remote_get( add_query_arg(
				$query,
				"{$this->url}/nestale_osobe"
			) );
			
			$posts = NULL;
			if( !is_wp_error( $request ) ) {
				if($json = wp_remote_retrieve_body( $request )) {
					$posts = json_decode($json);
				}
			}
			
			set_transient($cache_name, $posts, HOUR_IN_SECONDS);
		}
		
		$__get_missing[$cache_name] = $posts;
		
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
	
} endif;