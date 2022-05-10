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

global $wpdb;

$prefix = 'registar-nestalih';

// Delete options
if(get_option($prefix.'-activation')) {
	delete_option($prefix.'-activation');
}
if(get_option($prefix.'-deactivation')) {
	delete_option($prefix.'-deactivation');
}
if(get_option($prefix.'-db-version')) {
	delete_option($prefix.'-db-version');
}

// Delete tables
$db_prefix = $wpdb->get_blog_prefix();
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->db_prefix}registar_nestalih_cache" );