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
				esc_html__( 'Settings', Registar_Nestalih::TEXTDOMAIN )
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
					esc_html__( 'Donate', Registar_Nestalih::TEXTDOMAIN )
				),
				'registar_nestalih_foundation'	=> sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-foundation">%s</a>',
					esc_url( 'https://cnzd.rs/' ),
					esc_html__( 'Foundation', Registar_Nestalih::TEXTDOMAIN )
				),
				'registar_nestalih_vote'	=> sprintf(
					'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-vote" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> %s</a>',
					esc_url( 'https://wordpress.org/support/plugin/registar-nestalih/reviews/?filter=5' ),
					esc_attr__( 'Give us five if you like!', Registar_Nestalih::TEXTDOMAIN ),
					esc_html__( '5 Stars?', Registar_Nestalih::TEXTDOMAIN )
				)
			);

			$links = array_merge( $links, $row_meta );
		}
		return $links;
	}
	
	// Display posts state
	public function display_post_states ($states, $post) {
		if ( ( 'page' == get_post_type( $post->ID ) ) && ( Registar_Nestalih_Options::get('main-page') === $post->ID )) {
			$states[] = __( 'Missing Persons Page', Registar_Nestalih::TEXTDOMAIN );
		}
		return $states;
	}
	
	// Add menu pages
	public function admin_menu () {
		add_menu_page(
			__( 'Missing Persons', Registar_Nestalih::TEXTDOMAIN ),
			__( 'Missing Persons', Registar_Nestalih::TEXTDOMAIN ),
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
		if( wp_verify_nonce( ($_POST['__nonce'] ?? NULL), Registar_Nestalih::TEXTDOMAIN ) && isset($_POST[Registar_Nestalih::TEXTDOMAIN]) ) {
			if( Registar_Nestalih_Options::set( $_POST[Registar_Nestalih::TEXTDOMAIN] ) ) {			
				if( function_exists('flush_rewrite_rules') ) {
					Registar_Nestalih_API::flush_cache();
					flush_rewrite_rules();
				}
			}
		}
	}
	
	// Admin page
	public function page__missing_persons () {
		$pages = get_pages();
		$options = get_option( 'registar_nestalih' );
	?>
<div class="wrap">
	<h1><?php _e('Plugin Settings', Registar_Nestalih::TEXTDOMAIN); ?></h1>
	<hr>
	<form method="post">
		<h3><?php _e('Missing Persons Settings', Registar_Nestalih::TEXTDOMAIN); ?></h3>
		<p><?php _e('This option sets the API and shortcode for missing persons.', Registar_Nestalih::TEXTDOMAIN); ?></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php _e('Missing Persons Page', Registar_Nestalih::TEXTDOMAIN); ?></th>
				<td>
					<select name="registar-nestalih[main-page]">
						<option value="">- <?php _e('Select a Page', Registar_Nestalih::TEXTDOMAIN); ?> -</option>
						<?php foreach( $pages as $page ) { ?>
							<option value="<?php 
								echo $page->ID; 
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
				<th scope="row"><?php _e('Pagination slug', Registar_Nestalih::TEXTDOMAIN); ?></th>
				<td>
					<input type="text" name="registar-nestalih[pagination-slug]" value="<?php echo esc_attr( ($options['pagination-slug'] ?? 'page') ); ?>" placeholder="page" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Search slug', Registar_Nestalih::TEXTDOMAIN); ?></th>
				<td>
					<input type="text" name="registar-nestalih[search-slug]" value="<?php echo esc_attr( ($options['search-slug'] ?? 'search') ); ?>" placeholder="search" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Person slug', Registar_Nestalih::TEXTDOMAIN); ?></th>
				<td>
					<input type="text" name="registar-nestalih[person-slug]" value="<?php echo esc_attr( ($options['person-slug'] ?? 'person') ); ?>" placeholder="person" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Open links in new window', Registar_Nestalih::TEXTDOMAIN); ?></th>
				<td>
					<label for="open-in-new-window-0">
						<input type="radio" id="open-in-new-window-0" name="registar-nestalih[open-in-new-window]" value="1" <?php 
							checked( ($options['open-in-new-window'] ?? 0), 1 ); 
						?> /> <?php _e('Yes', Registar_Nestalih::TEXTDOMAIN); ?>
					</label>&nbsp;&nbsp;&nbsp;
					<label for="open-in-new-window-1">
						<input type="radio" id="open-in-new-window-1" name="registar-nestalih[open-in-new-window]" value="0" <?php 
							checked( ($options['open-in-new-window'] ?? 0), 0 ); 
						?> /> <?php _e('No', Registar_Nestalih::TEXTDOMAIN); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button( __('Save', Registar_Nestalih::TEXTDOMAIN) ); ?>
		<input type="hidden" name="__nonce" value="<?php echo esc_attr( wp_create_nonce(Registar_Nestalih::TEXTDOMAIN) ); ?>" />
	</form>
</div>
	<?php }
	
} endif;