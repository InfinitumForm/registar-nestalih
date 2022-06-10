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
		add_action( 'admin_bar_menu', [&$this, 'admin_bar_menu'], 100 );
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
		
		add_submenu_page(
			'missing-persons',
			__( 'Clear Cache of Missing Persons', 'registar-nestalih' ),
			__( 'Clear Cache', 'registar-nestalih' ),
			'manage_options',
			add_query_arg(
				[
					'registar_nestalih_clear_cache' => 'true',
					'registar_nestalih_cache_nonce' => wp_create_nonce('registar-nestalih-clear-cache')
				],
				admin_url('/admin.php?page=missing-persons')
			)
		);
	}
	
	// Add links to admin bar
	public function admin_bar_menu ($wp_admin_bar) {
		if ( ! (current_user_can( 'administrator' ) || current_user_can( 'editor' )) ){
			return $wp_admin_bar;
		}
		
        $wp_admin_bar->add_node( [
            'id' => 'registar-nestalih-clear-cache', // Set the ID of your custom link
            'title' => __( 'Clear MP Cache', 'registar-nestalih' ), // Set the title of your link
            'href' => add_query_arg([
				'registar_nestalih_clear_cache' => 'true',
				'registar_nestalih_cache_nonce' => wp_create_nonce('registar-nestalih-clear-cache')
			]), // Define the destination of your link
            'meta' => [
                'target' => '_self', // Change to _blank for launching in a new window
                'class' => 'registar-nestalih-clear-cache-link', // Add a class to your link
                'title' => __( 'Clear Cache of Missing Persons', 'registar-nestalih' ) // Add a title to your link
			]
        ] );
	}
	
	// Sanitize fields
	function register_setting__missing_persons() {
		$this->add_privacy_policy();
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
	<a href="https://www.nestalisrbija.rs/" target="_blank"><img src="<?php echo esc_url(Registar_Nestalih_Template::url('assets/images/registar-nestalih-lica-srbije.png')); ?>" alt="<?php esc_attr_e('Register of Missing Persons of Serbia', 'registar-nestalih'); ?>" style="display:block; width:90%; max-width:300px;" ></a>
	<hr>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar" id="<?php echo 'registar-nestalih'; ?>-settings-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				
				<div class="postbox" id="cnzd">
					<div class="hndle centeralign" style="text-align:center"><a href="https://cnzd.rs/" target="_blank"><img src="<?php echo esc_url(Registar_Nestalih_Template::url('assets/images/cnzd-srbija-logo.png')); ?>" alt="<?php esc_attr_e('The Center for Missing and Abused Children of Serbia', 'registar-nestalih'); ?>" style="display:block; width:90%; max-width:300px; margin: 15px auto;" ></a></div>
					<div class="inside flex">
					<?php
						printf(
							'<p>%s</p>',
							__('The Center for Missing and Abused Children does not charge for the support it provides through the Net Patrol and Missing Serbia projects, as well as psychological and legal assistance to the wards. That is why your support is important to us - together we can make the world a safer place for all our children!', 'registar-nestalih')
						);
						printf(
							'<p>%s</p>',
							sprintf(
								__('Support the work of the foundation by %1$s or %2$s in the web store.', 'registar-nestalih'),
								'<a href="https://donacije.cnzd.rs/proizvod/donirajte/" target="_blank">' . __('donating', 'registar-nestalih') . '</a>',
								'<a href="https://cnzd.rs/online-store/" target="_blank">' . __('purchasing products', 'registar-nestalih') . '</a>'
							)
						);
						printf(
							'<p>%s</p>',
							sprintf(
								__('You can also %1$s of the association.', 'registar-nestalih'),
								'<a href="https://clanarina.cnzd.rs/" target="_blank">' . __('become a member', 'registar-nestalih') . '</a>'
							)
						);
					?>
					</div>
				</div>
				
				<?php if($plugin_info = Registar_Nestalih_U::plugin_info(array('contributors' => true, 'donate_link' => true))) : if(!empty($plugin_info->contributors)) : ?>
				<div class="postbox" id="contributors">
					<h3 class="hndle" style="margin-bottom:0;padding-bottom:0;"><span><?php _e('Contributors & Developers', 'registar-nestalih'); ?></span></h3><hr>
					<div class="inside flex">
						<?php foreach(($plugin_info->contributors ?? []) as $username => $info) : $info = (object)$info; ?>
						<div class="contributor contributor-<?php echo $username; ?>" id="contributor-<?php echo $username; ?>">
							<a href="<?php echo esc_url($info->profile); ?>" target="_blank">
								<img src="<?php echo esc_url($info->avatar); ?>">
								<h3><?php echo $info->display_name; ?></h3>
							</a>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="inside">
						<?php printf('<p>%s</p>', sprintf(__('If you want to support our work and effort, if you have new ideas or want to improve the existing code, %s.', 'registar-nestalih'), '<a href="https://github.com/InfinitumForm/registar-nestalih" target="_blank">' . __('join our team', 'registar-nestalih') . '</a>')); ?>
					</div>
				</div>
				<?php endif; endif; ?>
				
				<div class="centeralign" id="developed-by" style="text-align:center">
					<a href="https://infinitumform.com/" target="_blank"><img src="<?php echo esc_url(Registar_Nestalih_Template::url('assets/images/developed-by.png')); ?>" alt="<?php esc_attr_e('Developed by: INFINITUM FORM', 'registar-nestalih'); ?>" style="display:block; width:90%; max-width:210px; margin-left:auto; margin-right:auto;" ></a>
				</div>
			</div>
		</div>
	 
		<div id="post-body">
			<div id="post-body-content">
				<form method="post">
					<h3><?php _e('Cache', 'registar-nestalih'); ?></h3>
					<p><?php _e('If you need to clear your plugin\'s cache, you have the option to use the following URL:', 'registar-nestalih'); ?></p>
					<p><code><?php echo home_url('/rnp-notification/' . Registar_Nestalih_U::key()); ?></code></p>
					
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
					<hr>
					<h3><?php _e('Utility', 'registar-nestalih'); ?></h3>
					<p><?php _e('Small but useful options.', 'registar-nestalih'); ?></p>
					<table class="form-table" role="utility">
						<tr>
							<th scope="row"><?php _e('CSS support', 'registar-nestalih'); ?></th>
							<td>
								<label for="enable-bootstrap-0">
									<input type="radio" id="enable-bootstrap-0" name="registar-nestalih[enable-bootstrap]" value="1" <?php 
										checked( ($options['enable-bootstrap'] ?? 0), 1 ); 
									?> /> <?php _e('Yes', 'registar-nestalih'); ?>
								</label>&nbsp;&nbsp;&nbsp;
								<label for="enable-bootstrap-1">
									<input type="radio" id="enable-bootstrap-1" name="registar-nestalih[enable-bootstrap]" value="0" <?php 
										checked( ($options['enable-bootstrap'] ?? 0), 0 ); 
									?> /> <?php _e('No', 'registar-nestalih'); ?>
								</label>
								<p class="description"><?php _e('If you activate this option, we will insert the basic Twitter Bootstrap CSS to style the tabs and columns while the rest of the design will be intact.', 'registar-nestalih'); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __('Save', 'registar-nestalih') ); ?>
					<input type="hidden" name="__nonce" value="<?php echo esc_attr( wp_create_nonce('registar-nestalih') ); ?>" />
				</form>
			</div>
		</div>
		<br class="clear">
	</div>

</div>
	<?php }
	
	public function add_privacy_policy () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		
		$privacy_policy = array(
			__( 'This site uses the Register of Missing Persons of Serbia to present to public and private visitors the information about missing persons from the Republic of Serbia, but there may also be missing persons from the Federation of Bosnia and Herzegovina and the Republic of Croatia.', 'registar-nestalih' ),
			sprintf(
				__( 'The Register of Missing Persons of Serbia is owned by the %s of the Republic of Serbia.', 'registar-nestalih' ),
				'<a href="https://cnzd.rs/" target="_blank">'.__('Center for Missing and Abused Children', 'registar-nestalih' ).'</a>',
			),
			__( 'The Center for Missing and Abused Children is a non-profit organization established in accordance with the Law on Endowments and Foundations of the Republic of Serbia, June 2, 2015, with the basic task of improving the safety of children in Serbia.', 'registar-nestalih' ),
			sprintf(
				__( 'The Register of Missing Persons of Serbia is available 24 hours a day at %1$s. The register will be completed in accordance with the available data on missing persons to the Foundation. Within the data for each missing person, the citizens will have the opportunity to leave information about the missing person in a specially designated field, and thus contribute to a better and more efficient search. The editorial board of the register is available 24 hours a day via the address %2$s or via free phone number %3$s.', 'registar-nestalih' ),
				'<a href="https://www.nestalisrbija.rs/" target="_blank">www.nestalisrbija.rs</a>',
				'<a href="mailto:info@nestalisrbija.rs">info@nestalisrbija.rs</a>',
				'<a href="tel:+381800200880">0800/200-880</a>'
			)
		);
	 
		wp_add_privacy_policy_content(
			__( 'Register of Missing Persons of Serbia', 'registar-nestalih' ),
			wp_kses_post( wpautop( join((PHP_EOL . PHP_EOL), $privacy_policy), false ) )
		);
	}
	
} endif;