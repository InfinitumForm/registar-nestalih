<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_API') ) : class Registar_Nestalih_API {
	// PRIVATE: API URL
	private $test_url = 'https://nestaliapi.delfin.rs/api';
	private $url = 'https://api.nestalisrbija.rs/api';
	
	// Run this class on the safe and protected way
	private static $instance;
	private static function instance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	// Get remote data
	public static function get( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__get_missing( $query ) );
	}
	
	// Report missing person 
	public static function report_missing_person( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__report_missing_person( $query ) );
	}
	
	// PRIVATE: Get missing persons
	private function __get_missing( array $query = [] ) {
		static $__get_missing;
		
		$query_allowed = [
			'paginate',
			'per_page',
			'page',
			'search',
			'order',
			'id'
		];
	
		$query = array_filter($query, function($value, $key) use ($query_allowed){
			return !empty($value) && in_array($key, $query_allowed) !== false;
		}, ARRAY_FILTER_USE_BOTH);
		
		$cache_name = 'api-' . hash('sha512', serialize($query));
		
		if( $__get_missing[$cache_name] ?? NULL ) {
			return $__get_missing[$cache_name];
		}
		
		$posts = Registar_Nestalih_Cache::get($cache_name);
		
		if( empty($posts) ) {
			// Delete transients
			$this->delete_expired_transients();
		
			// Enable development mode
			if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
				$this->url = $this->test_url;
			}

			// Send remote request
			$request = wp_remote_get( add_query_arg(
				$query,
				"{$this->url}/nestale_osobe"
			) );
			
			// If there is no errors clean it
			if( !is_wp_error( $request ) ) {
				if($json = wp_remote_retrieve_body( $request )) {
					$posts = json_decode($json);
				}
			}
			
			// Set cache
			Registar_Nestalih_Cache::set($cache_name, $posts, (MINUTE_IN_SECONDS*MISSING_PERSONS_CACHE_IN_MINUTES));
			$__get_missing[$cache_name] = $posts;
		}
		
		return $posts;
	}
	
	// PRIVATE: Report missing person
	private function __report_missing_person( array $query = [] ) {
		
		if( is_array($query) )
		{
			// Clean errors
			Registar_Nestalih_Cache::delete('report_missing_person_submission_error');
		
			// Allowed query
			$query_allowed = ['first_name','last_name','gender','date_of_birth','place_of_birth','citizenship','residence','height','weight','hair_color','eye_color','date_of_disappearance','place_of_disappearance','date_of_report','police_station','additional_information','disappearance_description','circumstances_disappearance','applicant_name','applicant_telephone','applicant_email','applicant_relationship','external_link','nonce'];
			
			// Required fields
			$required_fields = ['ime_prezime', 'policijska_stanica', 'pol', 'datum_rodjenja', 'mesto_rodjenja', 'drzavljanstvo', 'prebivaliste', 'visina', 'tezina', 'boja_kose', 'boja_ociju', 'datum_nestanka', 'datum_prijave', 'mesto_nestanka', 'ime_prezime_podnosioca', 'telefon_podnosioca', 'email_podnosioca', 'odnos_sa_nestalom_osobom'/*, 'icon'*/];
			
			// Filter query
			$query = (object)array_filter($query, function($value, $key) use ($query_allowed){
				return !empty($value) && in_array($key, $query_allowed) !== false;
			}, ARRAY_FILTER_USE_BOTH);

			// Verify nonce
			if( !wp_verify_nonce(($query->nonce ?? NULL), 'report-missing-person-form') ) {
				Registar_Nestalih_Cache::set('report_missing_person_submission_error', ['nonce']);
				return false;
			}
			
			// Upload the file
			$icon = NULL;
			if ( isset($_FILES['fileToUpload']) && isset($_FILES['fileToUpload']['name']) ) {
				$file = explode('.', $_FILES['fileToUpload']['name']);
				$file_name = $file[0];
				$file_ext = strtolower($file[1]);
				
				if( in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp']) !== false ) {
					$headers = [
						'Content-Type: multipart/form-data',
						'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
					];
					
					$fields = [
						'file' => new CURLFile($_FILES['fileToUpload']['tmp_name'])
					];

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "{$this->url}/uploadfile");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
					
					if( empty( curl_error($ch) ) ) {
						$upload = json_decode(curl_exec($ch));
						if( $upload->result ) {
							$icon = $upload->result;
						} else {
					//		Registar_Nestalih_Cache::set('report_missing_person_submission_error', ['icon_not_uploaded']);
						}
					} else {
					//	Registar_Nestalih_Cache::set('report_missing_person_submission_error', ['icon_upload_error']);
					}
				} else {
				//	Registar_Nestalih_Cache::set('report_missing_person_submission_error', ['icon_extension']);
				}
			}
			
			// Build name
			$full_name = join( ' ', array_filter( array_map( 'trim', [
				sanitize_text_field($query->first_name ?? NULL),
				sanitize_text_field($query->last_name ?? NULL)
			] ) ) );
			
			// Build raw POST arguments
			$args = [
				'method'      => 'POST',
				'headers'     => [
					'accept'        => 'application/json', // The API returns JSON
				//	'content-type'  => 'multipart/form-data', // Set content type to binary
				],
				'body' => [
					'ime_prezime' => $full_name,
					'policijska_stanica' => sanitize_text_field($query->police_station ?? NULL),
					'pol' => sanitize_text_field($query->gender ?? NULL),
					'datum_rodjenja' => sanitize_text_field($query->date_of_birth ?? NULL),
					'mesto_rodjenja' => sanitize_text_field($query->place_of_birth ?? NULL),
					'drzavljanstvo' => sanitize_text_field($query->citizenship ?? NULL),
					'prebivaliste' => sanitize_text_field($query->residence ?? NULL),
					'visina' => floatval($query->height ?? NULL),
					'tezina' => floatval($query->weight ?? NULL),
					'boja_kose' => sanitize_text_field($query->hair_color ?? NULL),
					'boja_ociju' => sanitize_text_field($query->eye_color ?? NULL),
					'datum_nestanka' => sanitize_text_field($query->date_of_disappearance ?? NULL),
					'datum_prijave' => sanitize_text_field($query->date_of_report ?? NULL),
					'mesto_nestanka' => sanitize_text_field($query->place_of_disappearance ?? NULL),
					'dodatne_informacije' => sanitize_textarea_field($query->additional_information ?? NULL),
					'opis_nestanka' => sanitize_textarea_field($query->disappearance_description ?? NULL),
					'okolnosti_nestanka' => sanitize_textarea_field($query->circumstances_disappearance ?? NULL),
					'ime_prezime_podnosioca' => sanitize_text_field($query->applicant_name ?? NULL),
					'telefon_podnosioca' => sanitize_text_field($query->applicant_telephone ?? NULL),
					'email_podnosioca' => sanitize_email($query->applicant_email ?? NULL),
					'odnos_sa_nestalom_osobom' => sanitize_text_field($query->applicant_relationship ?? NULL),
					'share_link' => sanitize_url($query->external_link ?? NULL),
					'icon' => $icon
				]
			];
			
			// Save all fields to memory
			$field = $args['body'];

			// Find errors
			$has_error = [];
			foreach($required_fields as $key) {
				if( isset($field[$key]) && empty($field[$key]) ) {
					$has_error[]=$key;
				}
			}
			
			// If has error let's cache it or send POST
			if( !empty($has_error) ) {
				Registar_Nestalih_Cache::set('report_missing_person_submission_error', $has_error);
				return false;
			} else {
				// Fix weight
				if( !empty($field['tezina']) ) {
					$args['body']['tezina'] = $args['body']['tezina'] . 'kg';
				}
				
				// Fix height
				if( !empty($field['visina']) ) {
					$args['body']['visina'] = $args['body']['visina'] . 'cm';
				}
				
				// Clear memory
				unset($field);
				
				// Enable development mode
				if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
					$this->url = $this->test_url;
				}
				
				// Send remote request
				$save = wp_remote_post("{$this->url}/save_nestale_osobe", $args);
				$save = json_decode($save['body'] ?? '{\'exception\':\'ErrorException\'}');
				
				return true;
			}
		}
		
		return NULL;
	}
	
	// PRIVATE: Seralize and protect query
	private function __sanitize_query( $query ) {
		/* TO DO */
		return $query;
	}
	
	// Flush plugin cache
	public static function flush_cache() {
		global $wpdb;
		// Remove all transients
		if ( is_multisite() && is_main_site() && is_main_network() ) {
			$wpdb->query("DELETE FROM
				`{$wpdb->sitemeta}`
			WHERE (
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->sitemeta}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-api-%'
			)");
		} else {
			$wpdb->query("DELETE FROM
				`{$wpdb->options}`
			WHERE (
					`{$wpdb->options}`.`option_name` LIKE '_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->options}`.`option_name` LIKE '_transient_timeout_registar-nestalih-api-%'
				OR
					`{$wpdb->options}`.`option_name` LIKE '_site_transient_registar-nestalih-api-%'
				OR
					`{$wpdb->options}`.`option_name` LIKE '_site_transient_timeout_registar-nestalih-api-%'
			)");
		}
	}
	
	// Delete Expired Plugin Transients
	private static function delete_expired_transients( $force_db = false ) {
		global $wpdb;

		if ( ! $force_db && wp_using_ext_object_cache() ) {
			return;
		}
	 
		$wpdb->query(
			$wpdb->prepare(
				"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_registar-nestalih-api-', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %d",
				$wpdb->esc_like( '_transient_registar-nestalih-api-' ) . '%',
				$wpdb->esc_like( '_transient_timeout_registar-nestalih-api-' ) . '%',
				time()
			)
		);
	 
		if ( ! is_multisite() ) {
			// Single site stores site transients in the options table.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
					WHERE a.option_name LIKE %s
					AND a.option_name NOT LIKE %s
					AND b.option_name = CONCAT( '_site_transient_timeout_registar-nestalih-api-', SUBSTRING( a.option_name, 17 ) )
					AND b.option_value < %d",
					$wpdb->esc_like( '_site_transient_registar-nestalih-api-' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_registar-nestalih-api-' ) . '%',
					time()
				)
			);
		} elseif ( is_multisite() && is_main_site() && is_main_network() ) {
			// Multisite stores site transients in the sitemeta table.
			$wpdb->query(
				$wpdb->prepare(
					"DELETE a, b FROM {$wpdb->sitemeta} a, {$wpdb->sitemeta} b
					WHERE a.meta_key LIKE %s
					AND a.meta_key NOT LIKE %s
					AND b.meta_key = CONCAT( '_site_transient_timeout_registar-nestalih-api-', SUBSTRING( a.meta_key, 17 ) )
					AND b.meta_value < %d",
					$wpdb->esc_like( '_site_transient_registar-nestalih-api-' ) . '%',
					$wpdb->esc_like( '_site_transient_timeout_registar-nestalih-api-' ) . '%',
					time()
				)
			);
		}
	}
	
} endif;