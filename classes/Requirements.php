<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('Registar_Nestalih_Requirements')) : class Registar_Nestalih_Requirements {
	
	private $title = '';
	private $php = '7.0';
	private $wp = '5.0';
	private $slug = '';
	private $file;
	
	// Run this class on the safe and protected way
	private static $instance;
	private static function instance( $args ) {
		if( !self::$instance ) {
			self::$instance = new self( $args );
		}
		return self::$instance;
	}
	
	// PRIVATE: Main construct
	private function __construct( $args ) {
		foreach ( array( 'title', 'php', 'wp', 'file', 'slug' ) as $setting ) {
			if ( isset($args[$setting]) && property_exists($this, $setting) ) {
				$this->{$setting} = $args[$setting];
			}
		}
		
		add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", [&$this, 'in_plugin_update_message'], 10, 2 );
	}
	
	/*
	 * Detect if plugin passes all checks 
	 */
	public static function passes( $args ) {
		$load = self::instance( $args );
		
		$passes = ( $load->validate_php_version() && $load->validate_wp_version() );
		
		if ( ! $passes ) {
			add_action( 'admin_notices', function () use ($load) {
				if ( isset( $load->file ) ) {
					deactivate_plugins( plugin_basename( $load->file ) );
				}
			} );
		}
		
		return $passes;
	}

	/*
	 * Check PHP version 
	 */
	private function validate_php_version() {
		if ( version_compare( phpversion(), $this->php, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on PHP versions older than PHP %2$s. Please contact your host and ask them to upgrade.', 'registar-nestalih'), esc_html( $this->title ), $this->php).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}

	/*
	 * Check WordPress version 
	 */
	private function validate_wp_version() {
		if ( version_compare( get_bloginfo( 'version' ), $this->wp, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on WordPress versions older than %2$s. Please update your WordPress installation.', 'registar-nestalih'), esc_html( $this->title ), $this->wp).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}
	
	/*
	 * Check WordPress version 
	 */
	function in_plugin_update_message($args, $response) {
		
	//	echo '<pre>', var_dump($response), '</pre>';
		
	   if (isset($response->upgrade_notice) && strlen(trim($response->upgrade_notice)) > 0) : ?>
<style media="all" id="registar-nestalih-plugin-update-message-css">
/* <![CDATA[ */
.registar-nestalih-upgrade-notice{
padding: 10px;
color: #000;
margin-top: 10px
}
.registar-nestalih-upgrade-notice-list ol{
list-style-type: decimal;
padding-left:0;
margin-left: 15px;
}
.registar-nestalih-upgrade-notice + p{
display:none;
}
.registar-nestalih-upgrade-notice-info{
margin-top:32px;
font-weight:600;
}
/* ]]> */
</style>
<div class="registar-nestalih-upgrade-notice">
<h3><?php printf(__('Important upgrade notice for the version %s:', 'registar-nestalih'), $response->new_version); ?></h3>
<div class="registar-nestalih-upgrade-notice-list">
	<?php echo str_replace(
		array(
			'<ul>',
			'</ul>'
		),array(
			'<ol>',
			'</ol>'
		),
		$response->upgrade_notice
	); ?>
</div>
<div class="registar-nestalih-upgrade-notice-info">
	<?php _e('NOTE: Before doing the update, it would be a good idea to backup your WordPress installations and settings.', 'registar-nestalih'); ?>
</div>
</div> 
		<?php endif;
	}
} endif;