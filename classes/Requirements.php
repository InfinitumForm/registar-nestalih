<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('Registar_Nestalih_Requirements')) : class Registar_Nestalih_Requirements {
	
	private $title = '';
	private $php = '7.0';
	private $wp = '5.0';
	private $slug = '';
	private $file;
	private $required_php_extensions = [];
	
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
		
		if( is_admin() ) {
			$this->update_database_alert();
		}
		
		$this->required_php_extensions = array(
			'curl_version' => (object)array(
				'name' => esc_html( 'cURL', 'registar-nestalih'),
				'desc' => esc_html( 'cURL PHP extension', 'registar-nestalih'),
				'link' => esc_url('https://www.php.net/manual/en/curl.installation.php')
			),
			'mb_substr' => (object)array(
				'name' => esc_html( 'Multibyte String', 'registar-nestalih'),
				'desc' => esc_html( 'Multibyte String PHP extension (mbstring)', 'registar-nestalih'),
				'link' => esc_url('https://www.php.net/manual/en/mbstring.installation.php')
			),
			'hash' => (object)array(
				'name' => esc_html( 'Hash', 'registar-nestalih'),
				'desc' => esc_html( 'Generate a hash value (message digest)', 'registar-nestalih'),
				'link' => esc_url('https://www.php.net/manual/en/function.hash.php')
			)
		);
		
		add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", [&$this, 'in_plugin_update_message'], 10, 2 );
	}
	
	/*
	 * Detect if plugin passes all checks 
	 */
	public static function passes( $args ) {
		$load = self::instance( $args );
		
		$passes = ( $load->validate_php_modules() && $load->validate_php_version() && $load->validate_wp_version() );
		
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
	 * Check PHP modules 
	 */
	private function validate_php_modules() {
		if(empty($this->required_php_extensions)) {
			return true;
		}
		
		$modules = array_map('function_exists', array_keys($this->required_php_extensions));
		$modules = array_filter($modules, function($m){return !empty($m);} );
		
		if ( count($modules) === count($this->required_php_extensions) ) {
			return true;
		}
		
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error">';
			printf('<p><strong>%s</strong></p><ol>', sprintf(__('%s requires the following PHP modules (extensions) to be activated:', 'registar-nestalih'), $this->title));
			foreach($this->required_php_extensions as $fn => $obj) {
				if( !function_exists($fn) ) {
					printf('<li>%1$s - <a href="%2s" target="_blank">%3$s</a></li>', $obj->desc, $obj->link, __('install', 'registar-nestalih'));
				}
			}
			echo '</ol>';
			printf('<p>%s</p>', __('Without these PHP modules you will not be able to use this plugin.', 'registar-nestalih'));
			printf('<p>%s</p>', __('Your hosting providers can help you to solve this problem. Contact them and request activation of the missing PHP modules.', 'registar-nestalih'));
			echo '</div>';
		} );
		
		return false;
	}
	
	/*
	 * Update database alert 
	 */
	private function update_database_alert() {
		$current_db_version = (get_option('registar-nestalih-db-version') ?? '0.0.0');
		if( version_compare($current_db_version, MISSING_PERSONS_DB_VERSION, '!=') ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-info" id="registar-nestalih-database-update">';
					echo '<p><strong>'.sprintf(__('%1$s database update required!', 'registar-nestalih'), esc_html( $this->title ), esc_html( MISSING_PERSONS_DB_VERSION )).'</strong></p>';
					echo '<p>'.sprintf(__('%1$s has been updated! To keep things running smoothly, we have to update your database to the newest version.', 'registar-nestalih'), esc_html( $this->title ), esc_html( MISSING_PERSONS_DB_VERSION )).'</p>';
					echo '<p class="submit"><a href="'.add_query_arg([
						'registar_nestalih_db_update' => 'true',
						'registar_nestalih_nonce' => wp_create_nonce('registar_nestalih_db_update')
					]).'" class="button button-primary">'.__('Update Database', 'registar-nestalih').'</a></p>';
				echo '</div>';
			} );
			return false;
		}
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