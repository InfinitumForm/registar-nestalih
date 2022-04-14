<?php
/**
 * Plugin Name:       Register of Missing Persons of Serbia
 * Plugin URI:        https://www.nestalisrbija.rs/
 * Description:       Show on your site all missing persons from the central Register of Missing Persons of Serbia
 * Version:           1.0.0
 * Author:            Ivijan-Stefan Sipić
 * Author URI:        https://infinitumform.com/
 * Requires at least: 5.0
 * Tested up to:      5.9
 * Requires PHP:      7.0
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       registar-nestalih
 * Domain Path:       /languages
 * Network:           true
 * Update URI:        https://github.com/InfinitumForm/registar-nestalih
 *
 * Copyright (C) 2022 Ivijan-Stefan Stipic
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! defined( 'MISSING_PERSONS_ROOT' ) ) {
	define( 'MISSING_PERSONS_ROOT', __DIR__ );
}

if ( ! defined( 'MISSING_PERSONS_FILE' ) ) {
	define( 'MISSING_PERSONS_FILE', __FILE__ );
}

if( !class_exists('Registar_Nestalih') ) : class Registar_Nestalih {
	
	// Textdomain for the locales
	const TEXTDOMAIN = 'registar-nestalih';
	// Prefix for the databases, fields etc
	const PREFIX = 'registar_nestalih_';
	// Cache classes that are loaded
	private $class_loaded = [];
	
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
		// Register activation hook
		register_activation_hook( MISSING_PERSONS_FILE,  [ 'Registar_Nestalih', 'register_plugin_activation' ] );
		// Register deactivation hook
		register_deactivation_hook( MISSING_PERSONS_FILE,  [ 'Registar_Nestalih', 'register_plugin_deactivation' ] );
		// On plugin uninstallation
		register_uninstall_hook( MISSING_PERSONS_FILE,  [ 'Registar_Nestalih', 'uninstall_plugin' ] );
		// Load translations
		add_action( 'plugins_loaded', [ $this, 'register_textdomain' ], 10, 0 );
		// Register plugin classes
		$this->register_plugin_classes();
	}
	
	// Register plugin classes
	public function register_plugin_classes(){
		$register_classes = apply_filters( 'registar_nestalih_classes', [
			__DIR__ . '/classes/Options.php' => [
				'class' => 'Registar_Nestalih_Options',
				'load' => false
			],
			__DIR__ . '/classes/API.php' => [
				'class' => 'Registar_Nestalih_API',
				'load' => false
			],
			__DIR__ . '/classes/Render.php' => [
				'class' => 'Registar_Nestalih_Render',
				'load' => false
			],
			__DIR__ . '/classes/Template.php' => [
				'class' => 'Registar_Nestalih_Template',
				'load' => true
			],
			__DIR__ . '/classes/Content.php' => [
				'class' => 'Registar_Nestalih_Content',
				'load' => true
			],
			__DIR__ . '/classes/Rewrite.php' => [
				'class' => 'Registar_Nestalih_Rewrite',
				'load' => true
			],
			__DIR__ . '/classes/Admin.php' => [
				'class' => 'Registar_Nestalih_Admin',
				'load' => true
			],
			__DIR__ . '/classes/Shortcodes.php' => [
				'class' => 'Registar_Nestalih_Shortcodes',
				'load' => true
			],
			__DIR__ . '/classes/Yoast_SEO.php' => [
				'class' => 'Registar_Nestalih_Yoast_SEO',
				'load' => true
			]
		], $this );
		
		if( !empty($register_classes) && is_array($register_classes) ) {
			foreach($register_classes as $path => $options) {
				
				if( $this->class_loaded[ $options['class'] ] ?? false ) {
					continue;
				}
				
				$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
				
				if( !class_exists($options['class']) && file_exists($path) && is_readable($path) ) {
					include_once $path;
					
					if( $options['load'] === true && method_exists($options['class'], 'instance') ) {
						$options['class']::instance();
					}
					
					$this->class_loaded[ $options['class'] ] = true;
				}
			}
		}
	}
	
	// On plugin Activation
	public static function register_plugin_activation () {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		
		// Reload textdomain on update
		if ( is_textdomain_loaded( self::TEXTDOMAIN ) ) {
			unload_textdomain( self::TEXTDOMAIN );
		}
		
		// Let's save activation dates
		if($activation = get_option(self::TEXTDOMAIN . '-activation')) {
			$activation[] = date('Y-m-d H:i:s');
			update_option(self::TEXTDOMAIN . '-activation', $activation, false);
		} else {
			add_option(self::TEXTDOMAIN . '-activation', [date('Y-m-d H:i:s')], false);
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	// On plugin Dectivation
	public static function register_plugin_deactivation () {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		
		// Reload textdomain on update
		if ( is_textdomain_loaded( self::TEXTDOMAIN ) ) {
			unload_textdomain( self::TEXTDOMAIN );
		}
		
		// Add deactivation date
		if($deactivation = get_option(self::TEXTDOMAIN . '-deactivation')) {
			$deactivation[] = date('Y-m-d H:i:s');
			update_option(self::TEXTDOMAIN . '-deactivation', $deactivation, false);
		} else {
			add_option(self::TEXTDOMAIN . '-deactivation', [date('Y-m-d H:i:s')], false);
		}
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
	
	// On plugin uninstallation
	public static function uninstall_plugin () {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Delete options
		if(get_option(self::TEXTDOMAIN . '-activation')) {
			delete_option(self::TEXTDOMAIN . '-activation');
		}
		if(get_option(self::TEXTDOMAIN . '-deactivation')) {
			delete_option(self::TEXTDOMAIN . '-deactivation');
		}
	}
	
	// Register site translations
	public function register_textdomain() {		
		// Get locale
		$locale = apply_filters( 'registar_nestalih_locale', get_locale(), self::TEXTDOMAIN );
		
		// We need standard file
		$mofile = sprintf( '%s-%s.mo', self::TEXTDOMAIN, $locale );
		
		// Check first inside `/wp-content/languages/plugins`
		$domain_path = path_join( WP_LANG_DIR, 'plugins' );
		$loaded = load_textdomain( self::TEXTDOMAIN, path_join( $domain_path, $mofile ) );
		
		// Or inside `/wp-content/languages`
		if ( ! $loaded ) {
			$loaded = load_textdomain( self::TEXTDOMAIN, path_join( WP_LANG_DIR, $mofile ) );
		}
		
		// Or inside `/wp-content/plugin/registar-nestalih/languages`
		if ( ! $loaded ) {
			$domain_path = __DIR__ . DIRECTORY_SEPARATOR . 'languages';
			$loaded = load_textdomain( self::TEXTDOMAIN, path_join( $domain_path, $mofile ) );
			
			// Or load with only locale without prefix
			if ( ! $loaded ) {
				$loaded = load_textdomain( self::TEXTDOMAIN, path_join( $domain_path, "{$locale}.mo" ) );
			}

			// Or old fashion way
			if ( ! $loaded && function_exists('load_plugin_textdomain') ) {
				load_plugin_textdomain( self::TEXTDOMAIN, false, $domain_path );
			}
		}
	}
	
} endif;

// Load plugin
Registar_Nestalih::instance();