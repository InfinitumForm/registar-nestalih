<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Admin') ) : class Registar_Nestalih_Admin {
	public $page_id;
	
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
		add_action( 'admin_menu', [&$this, 'admin_menu'], 90, 1 );
		add_action( 'admin_bar_menu', [&$this, 'admin_bar_menu'], 1 );
		add_action( 'admin_init', [&$this, 'register_setting__missing_persons'] );
		add_filter( 'display_post_states' , [&$this, 'display_post_states'], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename(MISSING_PERSONS_FILE), [&$this, 'plugin_action_links'] );
		add_filter( 'plugin_row_meta', [&$this, 'cfgp_action_links'], 10, 2 );
	}
	
	// WP Hidden links by plugin setting page
	public function plugin_action_links( $links ) {
		$links = array_merge( $links, [
			'settings'	=> sprintf(
				'<a href="' . self_admin_url( 'admin.php?page=missing-persons' ) . '" class="cfgeo-plugins-action-settings">%s</a>', 
				esc_html__( 'Settings', 'registar-nestalih' )
			)
		] );
		return $links;
	}
	
	// Plugin action links after details
	public function cfgp_action_links( $links, $file )
	{
		if( plugin_basename( MISSING_PERSONS_FILE ) == $file )
		{
			$row_meta = array(
				'registar_nestalih_donate' => sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-donation">%s</a>',
					esc_url( 'https://donacije.cnzd.rs/proizvod/donirajte/' ),
					esc_html__( 'Donate', 'registar-nestalih' )
				),
				'registar_nestalih_foundation'	=> sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-foundation">%s</a>',
					esc_url( 'https://cnzd.rs/' ),
					esc_html__( 'Foundation', 'registar-nestalih' )
				),
				'registar_nestalih_vote'	=> sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-vote" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> %s</a>',
					esc_url( 'https://wordpress.org/support/plugin/registar-nestalih/reviews/?filter=5' ),
					esc_attr__( 'Give us five if you like!', 'registar-nestalih' ),
					esc_html__( '5 Stars?', 'registar-nestalih' )
				)
			);

			$links = array_merge( $links, $row_meta );
		}
		return $links;
	}
	
	// Display posts state
	public function display_post_states ($states, $post) {
		if ( ( 'page' == get_post_type( $post->ID ) ) && ( Registar_Nestalih_Options::get('main-page') === $post->ID )) {
			$states[] = __( 'Missing Persons Page', 'registar-nestalih' );
		}
		return $states;
	}
	
	// Add menu pages
	public function admin_menu () {
		add_menu_page(
			__( 'Missing Persons', 'registar-nestalih' ),
			__( 'Missing Persons', 'registar-nestalih' ),
			'manage_options',
			'missing-persons',
			[ $this, 'page__missing_persons' ],
			'dashicons-heart',
			6
		);
	}
	
	// Add links to admin bar
	public function admin_bar_menu ($wp_admin_bar) {
		if ( ! (current_user_can( 'administrator' ) || current_user_can( 'editor' )) ){
			return $wp_admin_bar;
		}
		
		/* TO DO */
	}
	
	// Sanitize fields
	function register_setting__missing_persons() {
		if( wp_verify_nonce( ($_POST['__nonce'] ?? NULL), 'registar-nestalih' ) && isset($_POST['registar-nestalih']) ) {
			if( Registar_Nestalih_Options::set( $_POST['registar-nestalih'] ) ) {			
				if( function_exists('flush_rewrite_rules') ) {
					Registar_Nestalih_API::flush_cache();
					flush_rewrite_rules();
				}
			}
		}
	}
	
	// Admin page
	public function page__missing_persons () {
		$pages = get_pages([
			'post_status' => 'publish,private',
			'post_type' => 'page'
		]);
		$options = get_option( 'registar_nestalih' );
	?>
<div class="wrap">
	<h1><?php _e('Plugin Settings', 'registar-nestalih'); ?></h1>
	<hr>
	<form method="post">
		<h3><?php _e('Missing Persons Settings', 'registar-nestalih'); ?></h3>
		<p><?php _e('This option sets the API and shortcode for missing persons.', 'registar-nestalih'); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php _e('Missing Persons Page', 'registar-nestalih'); ?></th>
				<td>
					<select name="registar-nestalih[main-page]">
						<option value="">- <?php _e('Select a Page', 'registar-nestalih'); ?> -</option>
						<?php foreach( $pages as $page ) { ?>
							<option value="<?php 
								echo absint($page->ID); 
							?>" <?php 
								selected( ($options['main-page'] ?? NULL), $page->ID ); 
							?>><?php 
								echo esc_html($page->post_title); 
							?></option>
						<?php }; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Pagination slug', 'registar-nestalih'); ?></th>
				<td>
					<input type="text" name="registar-nestalih[pagination-slug]" value="<?php echo esc_attr( ($options['pagination-slug'] ?? 'page') ); ?>" placeholder="page" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Search slug', 'registar-nestalih'); ?></th>
				<td>
					<input type="text" name="registar-nestalih[search-slug]" value="<?php echo esc_attr( ($options['search-slug'] ?? 'search') ); ?>" placeholder="search" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Person slug', 'registar-nestalih'); ?></th>
				<td>
					<input type="text" name="registar-nestalih[person-slug]" value="<?php echo esc_attr( ($options['person-slug'] ?? 'person') ); ?>" placeholder="person" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Open links in new window', 'registar-nestalih'); ?></th>
				<td>
					<label for="open-in-new-window-0">
						<input type="radio" id="open-in-new-window-0" name="registar-nestalih[open-in-new-window]" value="1" <?php 
							checked( ($options['open-in-new-window'] ?? 0), 1 ); 
						?> /> <?php _e('Yes', 'registar-nestalih'); ?>
					</label>&nbsp;&nbsp;&nbsp;
					<label for="open-in-new-window-1">
						<input type="radio" id="open-in-new-window-1" name="registar-nestalih[open-in-new-window]" value="0" <?php 
							checked( ($options['open-in-new-window'] ?? 0), 0 ); 
						?> /> <?php _e('No', 'registar-nestalih'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Enable sending information about the person', 'registar-nestalih'); ?></th>
				<td>
					<label for="enable-notification-0">
						<input type="radio" id="enable-notification-0" name="registar-nestalih[enable-notification]" value="1" <?php 
							checked( ($options['enable-notification'] ?? 0), 1 ); 
						?> /> <?php _e('Yes', 'registar-nestalih'); ?>
					</label>&nbsp;&nbsp;&nbsp;
					<label for="enable-notification-1">
						<input type="radio" id="enable-notification-1" name="registar-nestalih[enable-notification]" value="0" <?php 
							checked( ($options['enable-notification'] ?? 0), 0 ); 
						?> /> <?php _e('No', 'registar-nestalih'); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button( __('Save', 'registar-nestalih') ); ?>
		<input type="hidden" name="__nonce" value="<?php echo esc_attr( wp_create_nonce('registar-nestalih') ); ?>" />
	</form>
</div>
	<?php }
	
} endif;