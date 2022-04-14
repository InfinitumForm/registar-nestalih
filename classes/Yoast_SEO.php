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
		if( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			add_filter( 'wpseo_sitemap_index', [&$this, 'wpseo_sitemap_index'] );
			add_action( 'init', [&$this, 'init'] );
		}
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
		
		$output = '';
		
		foreach ($registar_nestalih as $key => $registar ){

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

			$output .= "\t<url>\n";
			$output .= "\t\t<loc>" . $url['loc'] . "</loc>\n";
			if( !empty( $date ) ) {
				$output .= "\t\t<lastmod>" . htmlspecialchars( $date, ENT_COMPAT, 'UTF-8', false ) . "</lastmod>\n";
			}
			
			if($registar->icon) {
				$output .= "\t\t<image:image>\n";
				$output .= "\t\t\t\t<image:loc>" . $registar->icon . "</image:loc>\n";
				$output .= "\t\t</image:image>\n";
			}
			
			$output .= "\t</url>\n";

		}
		
		if ( empty( $output ) ) {
            $wpseo_sitemaps->bad_sitemap = true;
            return;
        }

        //Build the full sitemap
        $sitemap = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
			. 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd '
			. 'http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" '
			. 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $sitemap .= $output . '</urlset>';

        //echo $sitemap;
        $wpseo_sitemaps->set_sitemap($sitemap);
	}
	
	// Get registar nestalih
	public function get_registar_nestalih () {
		static $response;
		
		if( NULL === $response ) {
			$response = Registar_Nestalih_API::get();
			$response = array_map( function( $return ) {
				return new Registar_Nestalih_Render($return);
			}, $response );
		}
		
		return $response;
	}
	
} endif;