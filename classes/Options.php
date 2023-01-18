<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Options') ) : class Registar_Nestalih_Options {
	
	// Default settings
	private static $default_options = [
		'main-page' => NULL,
		'found-page' => NULL,
		'pagination-slug' => 'page',
		'search-slug' => 'search',
		'person-slug' => 'person',
		'news-slug' => 'missing-persons-news',
		'open-in-new-window' => 0,
		'latest-missing-post-types' => [],
		'enable-notification' => 1,
		'enable-bootstrap' => 0,
		'enable-news' => 0,
		'enable-latest-missing' => 0
	];
	
	/*
	 * Get plugin option
	 *
	 * @pharam   (string)   $name                        If exists, return value for single option, if empty return all options
	 * @pharam   (string)   $default                     Default values
	 *
	 * @return   (mixed)                                 plugin option/s
	 */
	public static function get ($name = NULL, $default = '') {
		static $options;
		
		// If not cached let's get it
		if( !$options ) {
			if( $options = get_option( 'registar_nestalih' ) ) {
				$options = array_merge(self::$default_options, $options);
			}
		}
		
		// Return data
		if($name) {
			return ( ( isset($options[$name]) && !empty($options[$name]) ) ? $options[$name] : $default );
		} else {
			return $options ?? $default;
		}
	}
	
	/*
	 * Set plugin option
	 *
	 * @pharam   (string|array)   $name_or_array       array of option name and values or just single option name
	 * @pharam   (string)         $value               if single option name is set, this is the value
	 *
	 * @return   (array|bool)                          plugin options or false on fail
	 */
	public static function set($name_or_array=array(), $value=NULL) {
		// Clear cache;
		$clear_cache = false;
		
		// Get plugin options
		$options = self::get();
		
		// Fix array
		if( !$options ) {
			$options = [];
		}
		
		// Get default keys
		$filter = array_keys(self::$default_options);
		
		// Collect and set new values
		if(!empty($name_or_array))
		{
			if(is_array($name_or_array)) {
				$clear_cache = true;
				$name_or_array = array_merge(
					(!empty($options) ? $options : self::$default_options),
					$name_or_array
				);
				foreach($name_or_array as $key => $val) {
					if(in_array($key, $filter) !== false) {
						$options[$key] = self::sanitize($val);
					} else {
						unset($name_or_array[$key]);
					}
				}
			} else if(!is_numeric($name_or_array) && is_string($name_or_array)) {
				$name = $name_or_array;
				if(in_array($name, $filter) !== false) {
					$options[$name] = self::sanitize($value);
				}
			}
		}

		// Return on the bad data
		if(empty($options)) return false;
		
		// Save new options
		update_option('registar_nestalih', $options, true);
		
		// Return
		return $options;
	}
	
	/*
	 * Delete plugin option
	 *
	 * @pharam   (string|array)   $name_or_array       array of option name and values or just single option name
	 *
	 * @return   (mixed)                               plugin options
	 */
	public static function delete($name_or_array) {
		// Get plugin options
		$options = self::get();
		
		// Get default keys
		$filter = array_keys(self::$default_options);
		
		// Remove options
		if(is_array($name_or_array)) {
			$name_or_array = array_map('trim', $name_or_array);
			
			foreach($name_or_array as $key) {
				if(isset($options[$key]) && in_array($key, $filter) !== false) {
					unset($options[$key]);
				}
			}
		} else if(isset($options[$name_or_array]) && in_array($name_or_array, $filter) !== false) {
			unset($options[$name_or_array]);
		}
		
		// Set defaults
		$options = array_merge(self::$default_options, $options);

		// Update options
		update_option('registar_nestalih', $options, true);
		
		// Return
		return $options;
	}
	
	/**
	 * Sanitize string or array
	 * This functionality do automatization for the certain type of data expected in this plugin
	 *
	 * @pharam   (string|array)   $str
	 *
	 * @return   (string|array)   sanitized options
	 */
	public static function sanitize( $str ) {
		if( is_array($str) )
		{
			$data = array();
			if(!empty($str)) {
				foreach($str as $key => $obj) {
					$data[$key]=self::sanitize( $obj ); 
				}
			}
			return $data;
		}
		else
		{			
			if(is_numeric($str))
			{
				if(intval( $str ) == $str) {
					$str = absint( $str );
				} else if(floatval($str) == $str) {
					$str = floatval( $str );
				} else {
					$str = sanitize_text_field( $str );
				}
			}
			else if(filter_var($str, FILTER_VALIDATE_URL) !== false)
			{
				$str = esc_url( sanitize_url( $str ) );
				return mb_strtolower($str);
			}
			else if(preg_match('/^([0-9a-z-_.]+@[0-9a-z-_.]+.[a-z]{2,8})$/i', $str))
			{
				$str = trim($str, "&$%#?!.;:,");
				$str = sanitize_email($str);

				return mb_strtolower($str);
			}
			else if(is_bool($str))
			{
				$str = ($str ? true : false);
			}
			else if(!is_bool($str) && in_array(strtolower($str), array('true','false'), true))
			{
				$str = ( strtolower($str) == 'true' );
			}
			else
			{
				$str = html_entity_decode($str);
				if(preg_match('/<\/?[a-z][\s\S]*>/i', $str)) {
					$str = wp_kses($str, wp_kses_allowed_html('post'));
				} else {
					$str = sanitize_text_field( $str );
				}
			}
		}
		
		return $str;
	}
	
} endif;