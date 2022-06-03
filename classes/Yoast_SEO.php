<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Yoast_SEO') ) : class Registar_Nestalih_Yoast_SEO {
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
		if( !function_exists('is_plugin_active') ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			add_filter( 'wpseo_sitemap_index', [&$this, 'wpseo_sitemap_index'] );
			add_action( 'init', [&$this, 'init'] );
			
			add_action( 'wpseo_add_opengraph_additional_images', [&$this, 'add_default_share_images'], 99, 1 );
			add_filter( 'wpseo_opengraph_image', [&$this, 'add_share_images'], 10, 1 );
			add_filter( 'wpseo_twitter_image', [&$this, 'add_share_images'], 10, 1 );
			
			add_filter( 'wpseo_opengraph_desc', [&$this, 'add_share_description'], 10, 1 );
			add_filter( 'wpseo_twitter_description', [&$this, 'add_share_description'], 10, 1 );
			add_filter( 'wpseo_metadesc', [&$this, 'add_share_description'], 10, 1 );
			
			add_filter( 'wpseo_opengraph_title', [&$this, 'add_share_title'], 10, 1 );
			add_filter( 'wpseo_metatitle', [&$this, 'add_share_title'], 10, 1 );
			add_filter( 'wpseo_title', [&$this, 'add_share_title'], 10, 1 );
			
			add_filter( 'wpseo_opengraph_url', [&$this, 'add_share_url'], 10, 1 );
			add_filter( 'wpseo_canonical', [&$this, 'add_share_url'], 10, 1 );
		}
	}
	
	private function is_active() {
		global $wp_query;
		static $pass;
		
		if( is_null($pass) ) {
			$pass = false;
			if( isset($wp_query->queried_object) ) {
				$pass = ( ($wp_query->queried_object->ID ?? -1) === Registar_Nestalih_Options::get('main-page', 0) );
			}
		}
		
		return $pass;
	}
	
	public function add_default_share_images( $opengraph_image ) {
		global $wp_query;
		
		if ( $this->is_active() && !$opengraph_image->has_images() ) {
			if( $person_id = $wp_query->get( 'registar_nestalih_id' ) )	{
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					$opengraph_image->add_image('registar-nestalih-single-seo-img');
				}
			}
		}
	}
	
	public function add_share_images ($img) {
		global $wp_query;

		if( $this->is_active() || $img === 'registar-nestalih-single-seo-img' ) {
			if( $person_id = $wp_query->get( 'registar_nestalih_id' ) )	{
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					$img = $response->profile_image_self();
				}
			}
		}
		
		return $img;
	}
	
	public function add_share_description ($desc) {
		global $wp_query;

		if( $this->is_active() ) {
			if( $person_id = $wp_query->get( 'registar_nestalih_id' ) ) {
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					if($response->ime_prezime) {
						$desc = sprintf(
							(
								$response->is_female() 
								? _x( '%s (%d) is missing on %s in %s, from place %s', 'female title', 'registar-nestalih') 
								: _x( '%s (%d) is missing on %s in %s, from place %s', 'male title', 'registar-nestalih')
							),
							$response->ime_prezime,
							$response->age(),
							$response->missing_date(),
							$response->mesto_nestanka,
							$response->mesto_rodjenja
						);
						
						if($response->age() === 0) {
							$desc = str_replace('(0) ', '', $desc);
						}
					}
				}
			}
		}
		
		return $desc;
	}
	
	public function add_share_title ($title) {
		global $wp_query;

		if( $this->is_active() ) {
			if( $person_id = $wp_query->get( 'registar_nestalih_id' ) ) {
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					if($response->ime_prezime) {
						$wpseo_titles = get_option('wpseo_titles');
						$sep_options = WPSEO_Option_Titles::get_instance()->get_separator_options();
						$current_filter = current_filter();
					
						$title = sprintf(
							(
								$current_filter == 'wpseo_title' 
								? (
									$response->is_female() 
									? _x( '%s (%d) is missing %s', 'female title', 'registar-nestalih') 
									: _x( '%s (%d) is missing %s', 'male title', 'registar-nestalih')
								)
								: (
									$response->is_female() 
									? _x( '%s (%d) is missing', 'female title', 'registar-nestalih') 
									: _x( '%s (%d) is missing', 'male title', 'registar-nestalih')
								)
							),
							$response->ime_prezime,
							$response->age(),
							($sep_options[$wpseo_titles['separator']??NULL] ?? '-') . ' ' . get_bloginfo('name')
						);
						
						if($response->age() === 0) {
							$title = str_replace('(0) ', '', $title);
						}
					}
				}
			}
		}
		
		return $title;
	}
	
	public function add_share_url ($url) {
		global $wp_query;

		if( $this->is_active() ) {
			if( $person_id = $wp_query->get( 'registar_nestalih_id' ) ) {
				if( $response = Registar_Nestalih_API::get( ['id' => $person_id] ) ) {
					$response = new Registar_Nestalih_Render($response);
					if($response->ime_prezime) {
						$url = $response->profile_url();
					}
				}
			}
		}
		
		return $url;
	}
	
	// Add to Yoast SEO new sitemap index
	public function init(){
		if( $this->get_registar_nestalih() ) {
			add_action( 'wpseo_do_sitemap_registar-nestalih', [&$this, 'wpseo_do_sitemap_registar_nestalih'], 10, 1);
			
		}
	}
	
	// Let's build sitemap index
	public function wpseo_sitemap_index(){
		global $wpseo_sitemaps;
		
		$date = $wpseo_sitemaps->get_last_modified('post');

		$smp = [
			'<sitemap>',
			'<loc>' . home_url('/registar-nestalih-sitemap.xml') .'</loc>',
			'<lastmod>' . htmlspecialchars( $date ) . '</lastmod>',
			'</sitemap>'
		];


		return join(PHP_EOL, $smp);
	}
	
	// Create sitemap
	public function wpseo_do_sitemap_registar_nestalih ($building_function) {
		global $wpseo_sitemaps;

		wp_reset_query();
		wp_reset_postdata();
		
		$registar_nestalih = $this->get_registar_nestalih();
		
		$output = [];
		
		foreach ($registar_nestalih as $key => $registar ) {

			$url = [
				'loc' => $registar->profile_url(),
				'pri' => 1.0,
				'mod' => $registar->created_at,
				'chf' => 'weekly'
			];

			$date = null;
			if ( ! empty( $url['mod'] ) ) {
				$date = YoastSEO()->helpers->date->format( $url['mod'] );
			}

			$url['loc'] = htmlspecialchars( $url['loc'], ENT_COMPAT, 'UTF-8', false );

			$output []= "\t<url>";
			$output []= "\t\t<loc>" . $url['loc'] . '</loc>';
			if( !empty( $date ) ) {
				$output []= "\t\t<lastmod>" . htmlspecialchars( $date, ENT_COMPAT, 'UTF-8', false ) . '</lastmod>';
			}
			
			if($registar->icon) {
				$output []= "\t\t<image:image>";
				$output []= "\t\t\t\t<image:loc>" . $registar->icon . "</image:loc>";
				$output []= "\t\t</image:image>";
			}
			
			$output []= "\t</url>";

		}
		
		if ( empty( $output ) ) {
            $wpseo_sitemaps->bad_sitemap = true;
            return;
        }

        //Build the full sitemap
        $sitemap = '<urlset ' 
			. 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
			. 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
			. 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd '
			. 'http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" '
			. 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
		. '>' . "\n"
			. join("\n", $output) 
		. "\n</urlset>";

		unset($output);

        //echo $sitemap;
        $wpseo_sitemaps->set_sitemap($sitemap);
	}
	
	// Get registar nestalih
	public function get_registar_nestalih () {
		static $response;
		
		if( NULL === $response ) {
			$response = Registar_Nestalih_API::get();
			
			if( is_array($response) ) {
				$response = array_map( function( $return ) {
					return new Registar_Nestalih_Render($return);
				}, $response );
			}
		}
		
		return $response;
	}
	
} endif;