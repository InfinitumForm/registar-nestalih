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
	
	// Get news 
	public static function get_news( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__get_news( $query ) );
	}
	
	// Get questions and answers 
	public static function get_qa( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__get_qa( $query ) );
	}
	
	// Get Amber Alert 
	public static function get_amber_alert( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__get_amber_alert( $query ) );
	}
	
	// Get Notification History 
	public static function get_notification_history( array $query = [] ) {
		return self::instance()->__sanitize_query( self::instance()->__get_notification_history( $query ) );
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
					
					if( $posts->error ?? NULL ) {
						if( $page = Registar_Nestalih_Options::get('found-page') ) {
							if( wp_safe_redirect( get_page_link($page) ) ) {
								exit;
							}
						} else {
							if( wp_safe_redirect( home_url('/404') ) ) {
								exit;
							}
						}
						
						return;
					}
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
			if ( isset($_FILES['report-missing-person-image']) && isset($_FILES['report-missing-person-image']['name']) ) {
				$file = explode('.', $_FILES['report-missing-person-image']['name']);
				$file_name = $file[0];
				$file_ext = strtolower($file[1]);
				
				if( in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp']) !== false ) {
					$headers = [
						'Content-Type: multipart/form-data',
						'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
					];
					
					$fields = [
						'file' => new CURLFile($_FILES['report-missing-person-image']['tmp_name'])
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
	
	// PRIVATE: Get push notifications
	private function __get_notification_history( array $query = [] ) {
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
		
		// Enable development mode
		if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
			$this->url = $this->test_url;
		}

		// Send remote request
		$request = wp_remote_get( add_query_arg(
			$query,
			"{$this->url}/istorija_notifikacija"
		) );
		
		// Get posts
		$posts = NULL;
		if( !is_wp_error( $request ) ) {
			if($json = wp_remote_retrieve_body( $request )) {
				$posts = json_decode($json);
			}
		}
		
		// Return
		return $posts;
	}
	
	// PRIVATE: Get questions and answers
	private function __get_qa( array $query = [] ) {
		global $wpdb;
		
		if( !function_exists('wp_generate_attachment_metadata') ) {
			include_once ABSPATH  . 'wp-admin/includes/image.php';
		}
		
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
		
		$cache_name = 'api-qa-' . hash('sha512', serialize($query));
		
		// Enable development mode
		if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
			$this->url = $this->test_url;
		}

		// Send remote request
		$request = wp_remote_get( add_query_arg(
			$query,
			"{$this->url}/qa/pitanja_i_saveti"
		) );
		
		// If there is no errors clean it
		$posts = Registar_Nestalih_Cache::get($cache_name);
		
		if( !$posts ) {
			if( !is_wp_error( $request ) ) {
				if($json = wp_remote_retrieve_body( $request )) {
					$posts = json_decode($json);
					// Render
					if( is_array($posts) ) {
						foreach($posts as &$post) {
							
							$post->id = absint($post->id);
							$post->question = strip_tags($post->question);
							$post->answer = wp_kses_post( sanitize_textarea_field($post->answer) );
							$post->created_at = date(get_option( 'date_format' ), strtotime($post->created_at));
							
							$content = explode("\n", $post->answer);
							
							$list_exists = false;
							foreach($content as $i => &$part) {
								
								$part = preg_replace('"<a[^>]+>.+?</a>(*SKIP)(*FAIL)|\b(?:https?)://\S+"', '<a href="$0">$0</a>', $part);
								$part = preg_replace('"<a[^>]+>.+?</a>(*SKIP)(*FAIL)|\b(\S+@\S+\.\S+)\S+"', '<a href="mailto:$0">$0</a>', $part);
								
								if( preg_match_all('/^([1-9]+)\.\s(.*?)(\n|$)/i', $part, $match) ) {
									if( !$list_exists ) {
										$list_exists = true;
										$part = '<ol><li>' . nl2br($match[2][0]) . '</li>';
									} else {
										$part = '<li>' . nl2br($match[2][0]) . '</li>';
									}
									continue;
								} else if( $list_exists ) {
									$list_exists = false;
									$content[$i-1] = $content[$i-1].'</ol>';
									continue;
								} else {
									$part = '<p>' . nl2br($part) . '</p>';
								}
							}
							
							if( $list_exists ) {
								$list_exists = false;
								$content[count($content)-1] = $content[count($content)-1].'</ol>';
							}
							
							$post->answer = join("\n", $content);
						}
						
						Registar_Nestalih_Cache::set($cache_name, $posts, MINUTE_IN_SECONDS*MISSING_PERSONS_CACHE_IN_MINUTES);
					}
				}
			}
		}
		
		return $posts;
	}
	
	// PRIVATE: Get Amber Alert
	private function __get_amber_alert( array $query = [] ) {
		global $wpdb;
		
		if( !function_exists('wp_generate_attachment_metadata') ) {
			include_once ABSPATH  . 'wp-admin/includes/image.php';
		}
		
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
		
		$cache_name = 'amber-alert-' . hash('sha512', serialize($query));
		
		// Enable development mode
		if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
			$this->url = $this->test_url;
		}

		// Send remote request
		$request = wp_remote_get( add_query_arg(
			$query,
			"{$this->url}/qa/amber_alert"
		) );
		
		// If there is no errors clean it
		$posts = Registar_Nestalih_Cache::get($cache_name);
		
		if( !$posts ) {
			if( !is_wp_error( $request ) ) {
				if($json = wp_remote_retrieve_body( $request )) {
					$posts = json_decode($json);
					// Render
					if( is_array($posts) ) {
						foreach($posts as &$post) {
							
							$post->id = absint($post->id);
							$post->question = strip_tags($post->question);
							$post->answer = wp_kses_post( sanitize_textarea_field($post->answer) );
							$post->created_at = date(get_option( 'date_format' ), strtotime($post->created_at));
							
							$content = explode("\n", $post->answer);
							
							$list_exists = false;
							foreach($content as $i => &$part) {
								
								$part = preg_replace('"<a[^>]+>.+?</a>(*SKIP)(*FAIL)|\b(?:https?)://\S+"', '<a href="$0">$0</a>', $part);
								$part = preg_replace('"<a[^>]+>.+?</a>(*SKIP)(*FAIL)|\b(\S+@\S+\.\S+)\S+"', '<a href="mailto:$0">$0</a>', $part);
								
								$part = '<p>' . nl2br($part) . '</p>';
							}
							
							if( $list_exists ) {
								$list_exists = false;
								$content[count($content)-1] = $content[count($content)-1].'</ol>';
							}
							
							$post->answer = join("\n", $content);
						}
						
						Registar_Nestalih_Cache::set($cache_name, $posts, MINUTE_IN_SECONDS*MISSING_PERSONS_CACHE_IN_MINUTES);
					}
				}
			}
		}
		
		return $posts;
	}
	
	// PRIVATE: Get missing persons
	private function __get_news( array $query = [] ) {
		global $wpdb;
		
		if( !function_exists('wp_generate_attachment_metadata') ) {
			include_once ABSPATH  . 'wp-admin/includes/image.php';
		}
		
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
		
		// Enable development mode
		if( defined('MISSING_PERSONS_DEV_MODE') && MISSING_PERSONS_DEV_MODE === true ) {
			$this->url = $this->test_url;
		}

		// Send remote request
		$request = wp_remote_get( add_query_arg(
			$query,
			"{$this->url}/vesti"
		) );
		
		// If there is no errors clean it
		$posts = [];
		if( !is_wp_error( $request ) ) {
			if($json = wp_remote_retrieve_body( $request )) {
				$posts = json_decode($json);
			}
		}
		
	//	return $posts; // DEBUG
		
		if( !empty($posts) ) {			
			$count_posts = wp_count_posts('missing-persons-news');
			if( count($posts) > ($count_posts->publish + $count_posts->draft + $count_posts->pending) ) {
				
				// Exclude existings
				$exclude = [];
				if( $remote_ids = wp_list_pluck($posts, 'id') ) {
					$remote_ids_map = implode( ',', array_fill( 0, count( $remote_ids ), '%d' ) );
					if( $post_ids = $wpdb->get_results( $wpdb->prepare("
						SELECT `meta_value` FROM `{$wpdb->postmeta}` 
						WHERE `{$wpdb->postmeta}`.`meta_key` = '_remote_id' 
						AND `{$wpdb->postmeta}`.`meta_value` IN ({$remote_ids_map})
					", $remote_ids) ) ) {
						$exclude = wp_list_pluck($post_ids, 'meta_value');
						$exclude = array_map('absint', $exclude);
					}
				}
				
				// Get post author ID
				$post_author = get_current_user_id();
				if( $get_users = get_users([
					'role__in' => ['administrator', 'editor']
				]) ) {
					$get_users = wp_list_pluck($get_users, 'ID');
					$post_author = (int)$get_users[0];
					unset($get_users);
				}
				
				// Loop, render and save
				foreach($posts as $i=>$post) {
					if( in_array(absint($post->id), $exclude) ) {
						unset($posts[$i]);
						continue;
					}
					
					$description = wp_kses_post( sanitize_textarea_field( $post->description ) );
					$get_excerpt = explode("\n", $description);
					$excerpt = $get_excerpt[0];
					unset($get_excerpt);
					
					$news_ID = wp_insert_post( [
						'post_title'    => wp_strip_all_tags( sanitize_text_field( $post->title ) ),
						'post_content'  => $description,
						'post_date'		=> wp_strip_all_tags( sanitize_text_field( $post->created_at ) ),
						'post_author'	=> $post_author,
						'post_excerpt'	=> wp_strip_all_tags( $excerpt ),
						'post_status'   => 'publish',
						'post_type'		=> 'missing-persons-news',
						'meta_input'	=> [
							'_remote_id' => absint($post->id),
							'_remote_image' => esc_url(sanitize_url( $post->icon )),
							'_yoast_wpseo_metadesc' => mb_strimwidth($excerpt, 0, 160, '...')
						]
					] );

					// Upload image
					$img_exists = true;
					
					// Get extension
					$ext = explode('.', $post->icon);
					$ext = '.' . strtolower(end($ext));
					
					// Validate format
					if(in_array($ext, ['.jpg','.jpeg','.png','.gif','.webp'])) {							
						// Validate image exists
						$test_image = wp_remote_get($post->icon, ['Content-Type' => 'application/json']);
						if ( is_array( $test_image ) && ! is_wp_error( $test_image ) ) {
							$test_image = json_decode($test_image['body']);
							if($test_image->error_type == 'missing_inputs'){
								$img_exists = false;
							}
							unset($test_image);
						}
						
						// When image exists
						if($img_exists) {
							$upload_dir = wp_upload_dir();
							$folder = MISSING_PERSONS_NEWS_IMG_UPLOAD_DIR;
							
							// Create base dir
							if( !file_exists($upload_dir['basedir'] . $folder) ) {
								mkdir($upload_dir['basedir'] . $folder, 0755, true);
								touch($upload_dir['basedir'] . $folder . '/index.php');
							}
							
							// Create post news folder
							if( file_exists($upload_dir['basedir'] . $folder) ) {
								$folder = $folder . '/' . $news_ID;
								if( !file_exists($upload_dir['basedir'] . $folder) ) {
									mkdir($upload_dir['basedir'] . $folder, 0755, true);
									touch($upload_dir['basedir'] . $folder . '/index.php');
								}
								
								// Let's try to upload image
								// https://wordpress.stackexchange.com/questions/50123/image-upload-from-url
								if( file_exists($upload_dir['basedir'] . $folder) ) {
									$filename = sanitize_title($post->title) . $ext;
									$image = $folder . '/' . $filename;
									
									if( !( function_exists('copy') && copy($post->icon, $upload_dir['basedir'] . $image)) ) {
										file_put_contents($upload_dir['basedir'] . $image, file_get_contents($post->icon));
									}
									
									// If image is uploaded let's save it and assign
									if( file_exists($upload_dir['basedir'] . $image) ) {
										$wp_filetype = wp_check_filetype(basename($image), null );
										$attach_id = wp_insert_attachment( [
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => $filename,
											'post_content' => '',
											'post_status' => 'inherit',
											'post_author'	=> $post_author,
										], $upload_dir['basedir'] . $image );
										$imagenew = get_post( $attach_id );
										$fullsizepath = get_attached_file( $imagenew->ID );
										$attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
										wp_update_attachment_metadata( $attach_id, $attach_data );
										
										// Assign
										set_post_thumbnail( $news_ID, $attach_id );
									}
								}
							}							
						}
					}
				}
			}
		}
		
		return $posts;
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