<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

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
		add_action( 'plugins_loaded', [ &$this, 'register_textdomain' ], 10, 0 );
		// Register plugin classes
		$this->register_plugin_classes();
		// Load remote actions
		add_action( 'init', [ &$this, 'remote_actions' ], 1, 0 );
	}

	// Load remote actions
	public function remote_actions(){
		if( isset($_GET['registar_nestalih_ping']) && $_GET['registar_nestalih_ping'] == 'true' ) {
			Registar_Nestalih_API::flush_cache();
		}
	}

	// Register plugin classes
	public function register_plugin_classes(){
		
		// Keep this in the place
		$root = realpath(__DIR__);
		
		// Include files
		$register_classes = apply_filters( 'registar_nestalih_classes', [
			$root . '/Utilities.php' => [
				'class' => 'Registar_Nestalih_U',
				'load' => false
			],
			$root . '/Options.php' => [
				'class' => 'Registar_Nestalih_Options',
				'load' => false
			],
			$root . '/API.php' => [
				'class' => 'Registar_Nestalih_API',
				'load' => false
			],
			$root . '/Render.php' => [
				'class' => 'Registar_Nestalih_Render',
				'load' => false
			],
			$root . '/Template.php' => [
				'class' => 'Registar_Nestalih_Template',
				'load' => true
			],
			$root . '/Content.php' => [
				'class' => 'Registar_Nestalih_Content',
				'load' => true
			],
			$root . '/Rewrite.php' => [
				'class' => 'Registar_Nestalih_Rewrite',
				'load' => true
			],
			$root . '/Admin.php' => [
				'class' => 'Registar_Nestalih_Admin',
				'load' => true
			],
			$root . '/Shortcodes.php' => [
				'class' => 'Registar_Nestalih_Shortcodes',
				'load' => true
			],
			$root . '/Widgets.php' => [
				'class' => 'Registar_Nestalih_Widgets',
				'load' => true
			],
			$root . '/Yoast_SEO.php' => [
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
		
		// Plugin is already installed, update some things
		if($activation = get_option(self::TEXTDOMAIN . '-activation')) {
			//Set activation date
			$activation[] = date('Y-m-d H:i:s');
			update_option(self::TEXTDOMAIN . '-activation', $activation, false);
		}
		// Plugin is new, let's do some things first time
		else {
			// Set activation date
			add_option(self::TEXTDOMAIN . '-activation', [date('Y-m-d H:i:s')], false);
			
			// Create new main plugin page
			if( $page_id = wp_insert_post( [
				'post_title'    => wp_strip_all_tags( __('Missing persons', self::TEXTDOMAIN) ),
				'post_content'  => '[registar_nestalih]',
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'     => 'page'
			] ) ) {
				Registar_Nestalih_Options::set('main-page', $page_id);
			}
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
			$domain_path = MISSING_PERSONS_ROOT . DIRECTORY_SEPARATOR . 'languages';
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