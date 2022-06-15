<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Widgets') ) : class Registar_Nestalih_Widgets {
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
		add_action('after_setup_theme', [&$this, 'register'], 10);
	}
	
	/* 
	 * Register widgets
	 * @verson    1.0.0
	 */
	public function register(){
		// Call main classes
		$classes = apply_filters('registar_nestalih_widget_classes', [
			'Registar_Nestalih_Widget_Latest_Missing',
			'Registar_Nestalih_Widget_News'
		]);
		
		// For each class include file and collect widgets
		$load_widgets = [];
		
		// Find widgets root
		$widgets_root = apply_filters(
			'registar_nestalih_widget_classes_root',
			(MISSING_PERSONS_ROOT . '/widgets/'),			// Direct path
			(dirname( realpath(__DIR__) ) . '/widgets/')	// Alternate path
		);

		// Load widget classes
		if( file_exists($widgets_root) ) {
			// Chenck and include widgets
			foreach($classes as $i => $class){
				$widget_path = $widgets_root . str_replace('Registar_Nestalih_Widget_', '', $class) . '.php';

				// Include
				if(!class_exists($class) && file_exists($widget_path)) {
					include_once $widget_path;
				}

				// Load
				if( class_exists($class) ) {
					$load_widgets[] = $class;
				}
			}
			
			// Register widget
			if( !empty($load_widgets) ) {
				add_action( 'widgets_init', function() use ($load_widgets){
					foreach($load_widgets as $widget) {
						if( class_exists($widget) ) {
							register_widget( $widget );
						}
					}
				} );
			}
		}
		
		// Clear memory
		unset($load_widgets);
	}
}
endif;