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
if(get_option($prefix.'-push-notification')) {
	delete_option($prefix.'-push-notification');
}

// Delete tables
$db_prefix = $wpdb->get_blog_prefix();
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->db_prefix}registar_nestalih_cache" );

// Delete news posts
if( $news_posts = get_posts(['post_type' => 'missing-persons-news', 'posts_per_page' => -1]) ) {
	foreach ($news_posts as $post) {
		if( $attachments = get_attached_media( '', $post->ID ) ) {
			foreach ($attachments as $attachment) {
				wp_delete_attachment( $attachment->ID, true );
			}
		}
		if( $thumbnail_id = get_post_thumbnail_id($post) ) {
			wp_delete_attachment( $thumbnail_id, true );
		}
		wp_delete_post($post->ID, true);
	}
}