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
		// Call main classes
		$classes = [
			'Registar_Nestalih_Widget_Latest_Missing',
		];
		
		// For each class include file and collect widgets
		$load_widgets = [];
		foreach($classes as $i => $class){
			$widget_path = MISSING_PERSONS_ROOT . '/widgets/' . str_replace('Registar_Nestalih_Widget_', '', $class) . '.php';

			// Include
			if(!class_exists($class) && file_exists($widget_path)) {
				include_once $widget_path;
			}

			// Register widget
			if( class_exists($class) ) {
				$load_widgets[] = $class;
			}
		}
		// Register widget
		if( !empty($load_widgets) ) {
			add_action( 'widgets_init', function() use ($load_widgets){
				foreach($load_widgets as $widget) {
					register_widget( $widget );
				}
			} );
		}
	}
}
endif;