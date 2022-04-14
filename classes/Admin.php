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
		add_action( 'admin_menu', [$this, 'admin_menu'], 1 );
		add_action( 'admin_init', [$this, 'register_setting__missing_persons'] );
		add_filter( 'display_post_states' , [$this, 'display_post_states'], 10, 2 );
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
	
	// Sanitize fields
	function register_setting__missing_persons() {
		if( wp_verify_nonce( ($_POST['__nonce'] ?? NULL), 'registar-nestalih' ) && isset($_POST['registar-nestalih']) ) {
			if( Registar_Nestalih_Options::set( $_POST['registar-nestalih'] ) ) {			
				if( function_exists('flush_rewrite_rules') ) {
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
								echo $page->ID; 
							?>" <?php 
								selected( ($options['main-page'] ?? NULL), $page->ID ); 
							?> ><?php 
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
		</table>
		<?php submit_button( __('Save', 'registar-nestalih') ); ?>
		<input type="hidden" name="__nonce" value="<?php echo esc_attr( wp_create_nonce('registar-nestalih') ); ?>" />
	</form>
</div>
	<?php }
	
} endif;