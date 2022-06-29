<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Content') ) : class Registar_Nestalih_Content {
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
		if(Registar_Nestalih_Options::get('enable-news', 0)) {
			add_action( 'init', [__CLASS__, 'register_post_types'] );
		}
		
		add_action( 'registar_nestalih_before_main_container', [&$this, 'do_missing_persons_search'], 10 );
		add_action( 'registar_nestalih_pagination', [&$this, 'do_missing_persons_pagination'] );
		add_action( 'registar_nestalih_breadcrumb', [&$this, 'do_breadcrumb'] );
		add_action( 'registar_nestalih_before_report_disappearance_form_container', [&$this, 'do_missing_persons_report_disappearance_form_http'] );
		add_filter( 'document_title_parts', [&$this, 'document_title_parts'], 100, 2 );
		
		if( Registar_Nestalih_Options::get('enable-notification') ) {
			add_action( 'registar_nestalih_before_single_container', [&$this, 'do_missing_persons_contact_form_http'] );
			add_action( 'registar_nestalih_after_single_container', [&$this, 'do_missing_persons_contact_form'] );
		}
		
		if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
			add_action( 'registar_nestalih_before_main_container', [&$this, 'development_notification'], 100 );
			add_action( 'registar_nestalih_before_single_container', [&$this, 'development_notification'], 100 );
			add_action( 'registar_nestalih_before_report_disappearance_form_container', [&$this, 'development_notification'], 100 );
			add_action( 'registar_nestalih_before_sidebar_container', [&$this, 'development_notification'], 100 );
		}
		
		if(Registar_Nestalih_Options::get('enable-news', 0)) {
			add_action( 'registar_nestalih_news_sync', ['Registar_Nestalih_API', 'get_news'], 10, 0 );
			add_action( 'before_delete_post', [&$this, 'registar_nestalih_news_delete'], 10, 2 );
		}
		
		if(Registar_Nestalih_Options::get('enable-latest-missing', 0)) {
			add_action( 'the_content', [&$this, 'the_content__include_latest_missing'], 10, 1 );
		}
	}
	
	// Register Post Type
	public static function register_post_types ( ) {
		register_post_type( 'missing-persons-news', [
			'labels'				=> [
				'name'               		=> __( 'Missing Persons News', 'registar-nestalih' ),
				'singular_name'      		=> __( 'Missing Persons News', 'registar-nestalih' ),
				'add_new'            		=> __( 'Add New News', 'registar-nestalih'),
				'add_new_item'       		=> __( "Add New News", 'registar-nestalih'),
				'edit_item'          		=> __( "Edit News", 'registar-nestalih'),
				'new_item'           		=> __( "New News", 'registar-nestalih'),
				'view_item'          		=> __( "View News", 'registar-nestalih'),
				'search_items'       		=> __( "Search News", 'registar-nestalih'),
				'not_found'          		=> __( 'No News Found', 'registar-nestalih'),
				'not_found_in_trash' 		=> __( 'No News Found in Trash', 'registar-nestalih'),
				'parent_item_colon'  		=> '',
				'featured_image'	 		=> __('News Image', 'registar-nestalih'),
				'set_featured_image'		=> __('Select News Image', 'registar-nestalih'),
				'remove_featured_image'		=> __('Remove News Image', 'registar-nestalih'),
				'use_featured_image'		=> __('Use News Image', 'registar-nestalih'),
				'insert_into_item'			=> __('Insert Into News', 'registar-nestalih')
			],
			'public'            	=> true,
			'exclude_from_search'	=> true,
			'publicly_queryable'	=> true, 
			'show_in_nav_menus'   	=> true,
			'show_ui'           	=> true,
			'query_var'         	=> true,
			'hierarchical'      	=> false,
			'has_archive'			=> true,
			'menu_position'     	=> 20,
			'capability_type'   	=> 'post',
			'supports'          	=> ['title', 'editor', 'thumbnail'],
			'menu_icon' 			=> 'dashicons-text-page',
			'show_in_menu'			=> false,
			'rewrite' => [ 
				'slug' => Registar_Nestalih_Options::get('news-slug'), 
				'with_front' => true
			]
		] );
	}
	
	// Development notification
	public function development_notification ( $response ) {
		printf(
			'<div class="alert alert-info" role="alert">%s</div>',
			__('The register of missing persons is in development mode and the content on it is intended for test purposes.', 'registar-nestalih')
		);
	}
	
	// Get date format
	public static function get_date_format() {
		static $format;
		
		if( !$format ) {
			$format = get_option( 'date_format' );
		}
		
		return $format;
	}
	
	// Change page title
	public function document_title_parts( $parts ) {
		global $wp_query;

		if( $wp_query && 0 < ( $person_id = absint($wp_query->get( 'registar_nestalih_id' ) ?? 0) ) ) {
			if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
				$response = new Registar_Nestalih_Render($response);
				$parts['title'] = esc_html($response->ime_prezime);
			}
		}

		return $parts;
	}
	
	// Render content from template
	public static function render ( string $type = 'missing-persons', $response = [] ) {
		ob_start();
			switch ($type) {
				case 'missing-persons':
					Registar_Nestalih_Template::get('missing-persons', $response);
					break;
				case 'missing-persons-single':
					Registar_Nestalih_Template::get('missing-persons/single', $response);
					break;
				case 'missing-persons-in-content':
					Registar_Nestalih_Template::get('missing-persons/in-content', $response);
					break;
				case 'sidebar':
					Registar_Nestalih_Template::get('missing-persons/sidebar', $response);
					break;
				case 'report-disappearance':
					Registar_Nestalih_Template::get('missing-persons/report-disappearance-form', $response);
					break;
				case 'widget-recent-news':
					Registar_Nestalih_Template::get('widgets/recent-news', $response);
					break;
				case 'question-and-answer':
					Registar_Nestalih_Template::get('question-and-answer', $response);
					break;
				case 'amber-alert':
					Registar_Nestalih_Template::get('amber-alert', $response);
					break;
			}
		return ob_get_clean();
	}
	
	// Render paginations
	public function do_missing_persons_pagination( $response ){
		
		global $last_page, $next_page, $prev_page, $current_page;
		
		// Get last page
		$last_page = ceil(absint($response->total??0)/absint($response->per_page??1));
		
		$current_page = absint($response->current_page ?? 0);

		$prev_page = ($current_page-1);
		if($prev_page < 0) {
			$prev_page = 0;
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( Registar_Nestalih_Options::get('main-page') );
			
			$next_page = sprintf(
				'%s/%s/%d',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('pagination-slug', 'page'),
				($current_page+1)
			);
			$prev_page = sprintf(
				'%s/%s/%d',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('pagination-slug', 'page'),
				$prev_page
			);
		} else {
			$next_page = add_query_arg([
				'registar_nestalih_list'=>($current_page+1)
			]);
			$prev_page = add_query_arg([
				'registar_nestalih_list'=>$prev_page
			]);
		}
		

		Registar_Nestalih_Template::get('missing-persons/pagination', $response);
	}
	
	// Render breadcrumb
	public function do_breadcrumb( $response ){
		/* TO DO */
	}
	
	// Render Search Form
	public function do_missing_persons_search( $response ){
		global $action_url;
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( Registar_Nestalih_Options::get('main-page') );
			
			$action_url = sprintf(
				'%s/%s',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('search-slug', 'search')
			);
		} else {
			$action_url = add_query_arg([
				'registar_nestalih_search'=>''
			]);
		}
		
		Registar_Nestalih_Template::get('missing-persons/search-form', $response);
	}
	
	// Contact form HTTP response
	public function do_missing_persons_contact_form_http ( $response ) {
		$send_message = $response->send_information();
		
		if(NULL !== $send_message) {
			if( $send_message ) {
				printf(
					'<div class="alert alert-success" role="alert">%s</div>',
					sprintf(
						__('Information about %s sent successfully.', 'registar-nestalih'),
						$response->ime_prezime
					)
				);
			} else {
				printf(
					'<div class="alert alert-danger" role="alert">%s</div>',
					__('The message was not sent due to technical problems.', 'registar-nestalih')
				);
			}
		}
	}
	
	// Report missing person form HTTP response
	public function do_missing_persons_report_disappearance_form_http ( $response ) {
		if( isset($_POST['report-missing-person']) ) {		
			Registar_Nestalih_API::report_missing_person($_POST['report-missing-person']);
			
			$errors = Registar_Nestalih_Cache::get('report_missing_person_submission_error');
			Registar_Nestalih_Cache::delete('report_missing_person_submission_error');
			
			if( $errors ) {
				printf(
					'<div class="alert alert-danger" role="alert">%s</div>',
					__('All fields in this form are required. Fill in the fields and send a message.', 'registar-nestalih')
				);
			} else {
				unset( $_POST['report-missing-person'] );
				printf(
					'<div class="alert alert-success" role="alert"><h3>%s</h3><p>%s</p></div>',
					__('The missing person was successfully reported.', 'registar-nestalih'),
					__('You must be patient for our administrators to verify the authenticity of the information and approve the missing person\'s profile.', 'registar-nestalih')
				);
			}
		}
	}
	
	// Add contact form to the single
	public function do_missing_persons_contact_form ( $response ) {
		Registar_Nestalih_Template::get('missing-persons/contact-form', $response);
	}
	
	// Include latest missing person
	public static function the_content__include_latest_missing ( $content ){

		// Get options
		$options = Registar_Nestalih_Options::get('latest-missing-post-types');

		// Get enabled post types
		$post_types = [];
		if(!empty($options) && is_array($options)) {
			foreach($options as $post_type=>$option) {
				if( ($option['enable'] ?? NULL) === 1 ) {
					$post_types[]= $post_type;
				}
			}
		}

		// Current post type
		$current_post_type = get_post_type();
		
		// Enable all
		if (
			!empty($post_types) 
			&& ( is_single() || is_page() ) 
			&& in_the_loop() 
			&& is_main_query() 
			&& in_array($current_post_type, $post_types)
		) {			
			return do_shortcode('[registar_nestalih_najnoviji position="' . $options[$current_post_type]['position'] . '"]' . $content . '[/registar_nestalih_najnoviji]');
		}
		
		return $content;
	}
	
	// Delete attachments related to the news on post delete
	public static function registar_nestalih_news_delete ( $post_id, $post ) {

		if( $post->post_type == 'missing-persons-news' ) {
			if( $attachments = get_attached_media( '', $post->ID ) ) {
				foreach ($attachments as $attachment) {
					wp_delete_attachment( $attachment->ID, true );
				}
			}
			if( $thumbnail_id = get_post_thumbnail_id($post) ) {
				wp_delete_attachment( $thumbnail_id, true );
			}
		}
	}
} endif;