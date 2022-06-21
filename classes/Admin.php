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
		add_action( 'admin_footer', [&$this, 'admin_footer'] );
		add_filter( 'display_post_states' , [&$this, 'display_post_states'], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename(MISSING_PERSONS_FILE), [&$this, 'plugin_action_links'] );
		add_filter( 'plugin_row_meta', [&$this, 'cfgp_action_links'], 10, 2 );
		add_action( 'admin_enqueue_scripts', [&$this, 'admin_enqueue_scripts'], 90, 1 );
	}
	
	// Include admin scripts
	public function admin_enqueue_scripts ( $page ) {
		if( 'toplevel_page_missing-persons' === $page ) {
			wp_enqueue_style(
				'registar-nestalih-highlight',
				MISSING_PERSONS_URL . '/assets/css/highlight.min.css',
				1,
				'1.' . filesize(MISSING_PERSONS_ROOT . '/assets/css/highlight.min.css')
			);
			wp_enqueue_style(
				'registar-nestalih-admin',
				MISSING_PERSONS_URL . '/assets/css/admin.css',
				['registar-nestalih-highlight'],
				'1.' . filesize(MISSING_PERSONS_ROOT . '/assets/css/admin.css')
			);
		}
	}
	
	// Admin footer scripts
	public function admin_footer () {
		if( in_array('missing-persons-news', array_filter([
			($_GET['post_type'] ?? NULL),
			get_post_type( absint($_GET['post'] ?? 0) )
		])) ) : ?>
<script>(function($){
	$('#toplevel_page_missing-persons')
		.addClass('wp-has-current-submenu wp-menu-open')
		.removeClass('wp-not-current-submenu')
		.find('.wp-submenu.wp-submenu-wrap > li:nth-child(3)')
		.addClass('current')
		.find('a')
		.addClass('current');
}(jQuery||window.jQuery));</script>
		<?php endif;
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
			$links['registar_nestalih_donate'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-donation">%s</a>',
				esc_url( 'https://donacije.cnzd.rs/proizvod/donirajte/' ),
				esc_html__( 'Donate', 'registar-nestalih' )
			);
			$links['registar_nestalih_foundation'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-foundation">%s</a>',
				esc_url( 'https://cnzd.rs/' ),
				esc_html__( 'Foundation', 'registar-nestalih' )
			);
			$links['registar_nestalih_vote'] = sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="registar-nestalih-plugins-action-vote" title="%s"><span style="color:#ffa000; font-size: 15px; bottom: -1px; position: relative;">&#9733;&#9733;&#9733;&#9733;&#9733;</span> %s</a>',
				esc_url( 'https://wordpress.org/support/plugin/registar-nestalih/reviews/?filter=5' ).'#new-post',
				esc_attr__( 'Give us five if you like!', 'registar-nestalih' ),
				esc_html__( '5 Stars?', 'registar-nestalih' )
			);
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
		
		if(Registar_Nestalih_Options::get('enable-news', 0)) {
			add_submenu_page(
				'missing-persons',
				__( 'News', 'registar-nestalih' ),
				__( 'News', 'registar-nestalih' ),
				'manage_options',
				admin_url('/edit.php?post_type=missing-persons-news')
			);
		}
		
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
		
		$wp_admin_bar->add_node(array(
			'id' => 'missing-persons',
			'title' => __( 'Missing Persons', 'registar-nestalih' ), 
			'href' => esc_url(admin_url('admin.php?page=missing-persons')), 
			'meta' => array(
				'class' => 'missing-persons',
				'title' => __( 'Missing Persons', 'registar-nestalih' ),
			)
		));
		
		
		
		$wp_admin_bar->add_menu(array(
			'parent' => 'missing-persons',
			'id' => 'missing-persons-link',
			'title' => __( 'Missing Persons', 'registar-nestalih' ), 
			'href' => esc_url(admin_url('admin.php?page=missing-persons')), 
			'meta' => array(
				'class' => 'missing-persons-link',
				'title' => __( 'Missing Persons', 'registar-nestalih' ),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => 'missing-persons',
			'id' => 'missing-persons-news',
			'title' => __( 'News', 'registar-nestalih' ), 
			'href' => esc_url(admin_url('edit.php?post_type=missing-persons-news')), 
			'meta' => array(
				'class' => 'missing-persons-news',
				'title' => __( 'News', 'registar-nestalih' ),
			)
		));
		
		$wp_admin_bar->add_menu(array(
			'parent' => 'missing-persons',
			'id' => 'registar-nestalih-clear-cache',
			'title' => __( 'Clear Cache', 'registar-nestalih' ), 
			'href' => esc_url(add_query_arg([
				'registar_nestalih_clear_cache' => 'true',
				'registar_nestalih_cache_nonce' => wp_create_nonce('registar-nestalih-clear-cache')
			])), 
			'meta' => array(
				'class' => 'registar-nestalih-clear-cache',
				'title' => __( 'Clear Cache of Missing Persons', 'registar-nestalih' ),
			)
		));
	}
	
	// Sanitize fields
	function register_setting__missing_persons() {
		$this->add_privacy_policy();
		if( wp_verify_nonce( ($_POST['__nonce'] ?? NULL), 'registar-nestalih' ) && isset($_POST['registar-nestalih']) ) {
			if( Registar_Nestalih_Options::set( $_POST['registar-nestalih'] ) ) {
				Registar_Nestalih_API::flush_cache();				
				if( function_exists('flush_rewrite_rules') ) {
					flush_rewrite_rules();
				}
				
				if(Registar_Nestalih_Options::get('enable-news', 0)) {
					Registar_Nestalih_API::get_news();
				}
				
				if( wp_safe_redirect( admin_url('/admin.php?page=missing-persons') ) ) {
					exit;
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
		
		add_action('admin_footer', function(){ ?>
<script>
( function($) {
	$('.wp-tab-bar a').click(function(event){
		event.preventDefault();
		
		// Limit effect to the container element.
		var context = $(this).closest('.wp-tab-bar').parent();
		$('.wp-tab-bar a', context).removeClass('wp-tab-active');
		$(this).addClass('wp-tab-active');
		$('.wp-tab-panel', context).hide();
		$( $(this).attr('href'), context ).show();
	});

	// Make setting wp-tab-active optional.
	$('.wp-tab-bar').each(function(){
		if ( $('.wp-tab-active', this).length ) {
			$('.wp-tab-active', this).click();
		} else {
			$('a', this).first().click();
		}
	});
}(jQuery || window.jQuery));
</script>
		<?php });
	?>
<div class="wrap" id="registar-nestalih-admin">
	<a href="https://www.nestalisrbija.rs/" target="_blank" id="logo"><img src="<?php echo esc_url(Registar_Nestalih_Template::url('assets/images/registar-nestalih-lica-srbije.png')); ?>" alt="<?php esc_attr_e('Register of Missing Persons of Serbia', 'registar-nestalih'); ?>" style="display:block; width:90%; max-width:300px;" ></a>
	<hr>
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar" id="<?php echo 'registar-nestalih'; ?>-settings-sidebar">
		
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
				
				<div class="postbox" id="cnzd">
					<div class="hndle centeralign" style="text-align:center"><a href="https://cnzd.rs/" target="_blank"><img src="<?php echo esc_url(Registar_Nestalih_Template::url('assets/images/cnzd-srbija-logo.png')); ?>" alt="<?php echo esc_attr(_x('The Center for Missing and Abused Children of Serbia', 'Admin sidebar title', 'registar-nestalih')); ?>" style="display:block; width:90%; max-width:300px; margin: 15px auto;" ></a></div>
					<div class="inside">
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

<h2 class="wp-tab-bar">
	<a href="#settings" class="wp-tab-active"><?php _e('Settings', 'registar-nestalih'); ?></a>
	<a href="#shortcodes"><?php _e('Available Shortcodes', 'registar-nestalih'); ?></a>
</h2>

<!-- Settings -->
<div class="wp-tab-panel" id="settings">
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
		<h3><?php _e('News settings', 'registar-nestalih'); ?></h3>
		<p><?php _e('If you want, you have the opportunity to download the latest news from our application and display it on your site as a special post format intended for this purpose.', 'registar-nestalih'); ?></p>
		<table class="form-table" role="utility">
			<tr>
				<th scope="row"><?php _e('Enable News', 'registar-nestalih'); ?></th>
				<td>
					<label for="enable-news-0">
						<input type="radio" id="enable-news-0" name="registar-nestalih[enable-news]" value="1" <?php 
							checked( ($options['enable-news'] ?? 0), 1 ); 
						?> /> <?php _e('Yes', 'registar-nestalih'); ?>
					</label>&nbsp;&nbsp;&nbsp;
					<label for="enable-news-1">
						<input type="radio" id="enable-news-1" name="registar-nestalih[enable-news]" value="0" <?php 
							checked( ($options['enable-news'] ?? 0), 0 ); 
						?> /> <?php _e('No', 'registar-nestalih'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('News slug', 'registar-nestalih'); ?></th>
				<td>
					<input type="text" name="registar-nestalih[news-slug]" value="<?php echo esc_attr( ($options['news-slug'] ?? 'missing-persons-news') ); ?>" placeholder="missing-persons-news" />
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
<!-- End Settings -->

<!-- Shortcodes -->
<div class="wp-tab-panel" id="shortcodes" style="display: none;">
	<h3><span><?php _e('Register of Missing Persons', 'registar-nestalih'); ?></span></h3>
	<p><?php _e('Print the advanced search page and view all missing persons from the global database.', 'registar-nestalih'); ?></p>
	<p><code class="lang-txt hljs plaintext">[<span class="hljs-title">registar_nestalih</span>]</code></p>
	<strong><?php _e('Optional shortcodes:', 'registar-nestalih'); ?></strong>
	<ul>
		<li><code>per_page</code> - <?php _e('(optional) number of items per page', 'registar-nestalih'); ?></li>
		<li><code>page</code> - <?php _e('(optional) page number', 'registar-nestalih'); ?></li>
		<li><code>search</code> - <?php _e('(optional) search terms', 'registar-nestalih'); ?></li>
		<?php if( defined('MISSING_PERSONS_GOD_MODE') && MISSING_PERSONS_GOD_MODE ) : ?>
			<li><code>order</code> - <?php _e('(optional) order by, (use a minus sign for DESC before the parameter)', 'registar-nestalih'); ?></li>
			<li><code>person</code> - <?php _e('(optional) person ID for individual missing person', 'registar-nestalih'); ?></li>
		<?php endif; ?>
	</ul>
	
	<h3><span><?php _e('Report of a Missing Person', 'registar-nestalih'); ?></span></h3>
	<p><?php _e('Prints the missing person registration form in the central register.', 'registar-nestalih'); ?></p>
	<p><code class="lang-txt hljs plaintext">[<span class="hljs-title">registar_nestalih_prijava</span>]</code></p>
	
	<h3><span><?php _e('Questions and Answers from the Register of Missing Persons Website', 'registar-nestalih'); ?></span></h3>
	<p><?php _e('Displays questions and answers from the missing persons site.', 'registar-nestalih'); ?></p>
	<p><code class="lang-txt hljs plaintext">[<span class="hljs-title">registar_nestalih_pitanja_saveti</span>]</code></p>
	<strong><?php _e('Optional shortcodes:', 'registar-nestalih'); ?></strong>
	<ul>
		<li><code>per_page</code> - <?php _e('(optional) number of items per page', 'registar-nestalih'); ?></li>
		<li><code>page</code> - <?php _e('(optional) page number', 'registar-nestalih'); ?></li>
		<li><code>search</code> - <?php _e('(optional) search terms', 'registar-nestalih'); ?></li>
		<?php if( defined('MISSING_PERSONS_GOD_MODE') && MISSING_PERSONS_GOD_MODE ) : ?>
			<li><code>order</code> - <?php _e('(optional) order by, (use a minus sign for DESC before the parameter)', 'registar-nestalih'); ?></li>
		<?php endif; ?>
	</ul>
	
	<h3><span><?php _e('Amber Alert information from the Missing Persons Register website.', 'registar-nestalih'); ?></span></h3>
	<p><?php _e('Displays amber alert informations from the missing persons site.', 'registar-nestalih'); ?></p>
	<p><code class="lang-txt hljs plaintext">[<span class="hljs-title">registar_nestalih_amber_alert</span>]</code></p>
	<strong><?php _e('Optional shortcodes:', 'registar-nestalih'); ?></strong>
	<ul>
		<li><code>per_page</code> - <?php _e('(optional) number of items per page', 'registar-nestalih'); ?></li>
		<li><code>page</code> - <?php _e('(optional) page number', 'registar-nestalih'); ?></li>
		<li><code>search</code> - <?php _e('(optional) search terms', 'registar-nestalih'); ?></li>
		<?php if( defined('MISSING_PERSONS_GOD_MODE') && MISSING_PERSONS_GOD_MODE ) : ?>
			<li><code>order</code> - <?php _e('(optional) order by, (use a minus sign for DESC before the parameter)', 'registar-nestalih'); ?></li>
		<?php endif; ?>
	</ul>
</div>
<!-- End Shortcodes -->

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
				'<a href="https://cnzd.rs/" target="_blank">'._x('Center for Missing and Abused Children', 'Privacy policy sentence', 'registar-nestalih' ).'</a>'
			),
			__( 'The Center for Missing and Abused Children is a non-profit organization established in accordance with the Law on Endowments and Foundations of the Republic of Serbia, June 2, 2015, with the basic task of improving the safety of children in Serbia.', 'registar-nestalih' ),
			sprintf(
				__( 'The Register of Missing Persons of Serbia is available 24 hours a day at %1$s. The register will be completed in accordance with the available data on missing persons to the Center. Within the data for each missing person, the citizens will have the opportunity to leave information about the missing person in a specially designated field, and thus contribute to a better and more efficient search. The editorial board of the register is available 24 hours a day via the address %2$s or via free phone number %3$s.', 'registar-nestalih' ),
				'<a href="https://www.nestalisrbija.rs/" target="_blank">www.nestalisrbija.rs</a>',
				'<a href="mailto:info@nestalisrbija.rs">info@nestalisrbija.rs</a>',
				'<a href="tel:+381800200880">0800/200-880</a>'
			)
		);
	 
		wp_add_privacy_policy_content(
			_x( 'Register of Missing Persons of Serbia', 'Privacy policy title', 'registar-nestalih' ),
			wp_kses_post( wpautop( join((PHP_EOL . PHP_EOL), $privacy_policy), false ) )
		);
	}
	
} endif;