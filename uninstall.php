<?php
/**
 * Uninstall plugin and clean everything
 *
 * @link              http://infinitumform.com/
 * @package           Registar_Nestalih
 */
 
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$prefix = 'registar-nestalih';

// Delete options
if(get_option($prefix.'-activation')) {
	delete_option($prefix.'-activation');
}
if(get_option($prefix.'-deactivation')) {
	delete_option($prefix.'-deactivation');
}