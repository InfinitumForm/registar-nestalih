<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Render') ) : class Registar_Nestalih_Render {
	
	public $id;
	public $ime_prezime;
	public $policijska_stanica;
	public $pol;
	public $datum_rodjenja;
	public $mesto_rodjenja;
	public $drzavljanstvo;
	public $prebivaliste;
	public $visina;
	public $tezina;
	public $boja_kose;
	public $boja_ociju;
	public $datum_nestanka;
	public $mesto_nestanka;
	public $dodatne_informacije;
	public $opis_nestanka;
	public $icon;
	public $okolnosti_nestanka;
	public $ime_prezime_podnosioca;
	public $telefon_podnosioca;
	public $email_podnosioca;
	public $odnos_sa_nestalom_osobom;
	public $datum_prijave;
	public $share_link;
	public $created_at;
	public $comments;
	
	protected $date_format = 'Y-m-d';
	protected $index;
	private $is_female;
	
	public function __construct( $data, $index = 0 ) {
		
		$this->index = $index;
		$this->date_format = Registar_Nestalih_Content::get_date_format();
		
		if( empty($data) ) {
			return $this;
		}
		
		if( is_array($data) ) {
			$data = (object)$data;
		}
		
		foreach($data as $key => $value) {
			
			if( property_exists($this, $key) ) {
				$this->{$key} = self::senitize( $value );
			}
		}
		
		if( $this->id ) {
			$this->index = $this->id;
		}
	}
	
	// Handle with all methods
	public function __call($function, $arguments)
    {
        if (property_exists($this, $function)) {
            return $this->{$function};
        }

        throw new Exception('No such method: ' . get_class($this) . '->$function()');
    }
	
	// generate image URL
	public function profile_image () {
		if( empty($this->icon) ) {
			$this->icon = Registar_Nestalih_Template::url('assets/images/no-image-male.gif');
			if( $this->is_female() ) {
				$this->icon = Registar_Nestalih_Template::url('assets/images/no-image-female.gif');
			}
		} else {
			
			/* @todo: Get image from remote URL, save into uploads folder and display it */
			
		}
		return esc_url($this->icon);
	}
	
	// Generate image link
	/*
	public function profile_image_self(){
		if( empty($this->id) ) {
			return '#';
		}
		
		$page = Registar_Nestalih_Options::get('main-page');
		
		if( empty($page) ) {
			return NULL;
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( $page );
			$url = sprintf(
				'%s/%s/%d',
				rtrim($page_link, '/'),
				'img',
				absint($this->id)
			);
		} else {
			$url = add_query_arg([
				'registar_nestalih_img_id' => absint($this->id)
			]);
		}
		
		return esc_url($url);
	}
	*/
	
	public function profile_base64_image () {
		// image to string conversion
		$image = file_get_contents($this->profile_image()); 
		  
		// image string data into base64
		echo base64_encode($image); 
	}
	
	// Is profile female
	public function is_female() {
		if(NULL === $this->is_female) {
			$this->is_female = in_array(mb_strtolower($this->pol), ['ženа', 'ženskо', 'ženski', 'zenа', 'zenskо', 'zenski', 'woman', 'female', 'f', 'ž', 'z']);
		}
		return $this->is_female;
	}
	
	// Is profile male
	public function is_male() {
		return !$this->is_female();
	}
	
	// Generate URL
	public function profile_url(){
		if( empty($this->id) ) {
			return '#';
		}
		
		$page = Registar_Nestalih_Options::get('main-page');
		
		if( empty($page) ) {
			return NULL;
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( $page );
			$url = sprintf(
				'%s/%s/%d/%s',
				rtrim($page_link, '/'),
				Registar_Nestalih_Options::get('person-slug', 'person'),
				absint($this->id),
				sanitize_title($this->ime_prezime)
			);
		} else {
			$url = add_query_arg([
				'registar_nestalih_id'		=> absint($this->id),
				'registar_nestalih_name'	=> sanitize_title($this->ime_prezime)
			]);
		}
		
		return esc_url($url);
	}
	
	// Generate first name
	public function first_name(){
		return current(explode(' ', $this->ime_prezime));
	}
	
	// Generate first name
	public function last_name(){
		$parts = explode(' ', $this->ime_prezime);
		return end($parts);
	}
	
	// Generate age
	public function age(){
		if( empty($this->datum_rodjenja) ) {
			return NULL;
		}
		
		return date_diff(date_create($this->datum_rodjenja), date_create('now'))->y;
	}
	
	// Generate birth date
	public function birth_date(){
		if( empty($this->datum_rodjenja) ) {
			return NULL;
		}
		
		return date_i18n( $this->date_format, strtotime($this->datum_rodjenja . ' 01:00:01'));
	}
	
	// Generate missing date
	public function missing_date(){
		if( empty($this->datum_nestanka) ) {
			return NULL;
		}
		
		return date_i18n( $this->date_format, strtotime($this->datum_nestanka . ' 01:00:01'));
	}
	
	// Generate reporting date
	public function reporting_date(){
		if( empty($this->datum_prijave) ) {
			return NULL;
		}
		
		return date_i18n( $this->date_format, strtotime($this->datum_prijave . ' 01:00:01'));
	}
	
	// Send notification message
	public function send_information( $fields = [] ) {
		
		$defaults = [
			'message' 			=> NULL,
			'first_last_name' 	=> NULL,
			'phone' 			=> NULL,
			'email' 			=> NULL,
			'nonce' 			=> NULL
		];
		
		$post = ($_POST['missing-persons'] ?? $defaults);
		if( !is_array($post) ) {
			$post = $defaults;
		}
		
		$fields = array_map('trim', $post ?? $fields);

		if( !empty($fields) && wp_verify_nonce(($fields['nonce'] ?? ''), 'missing-persons-form-' . $this->id()) ) {
			
			if( $response = wp_remote_post( 'https://nestaliapi.delfin.rs/api/save_info_o_osobi', [
				'body' => [
					'nestale_osobe_id' 		=> absint($this->id()),
					'informacije_o_osobi' 	=> sanitize_textarea_field($fields['message'] ?? NULL),
					'ime_prezime' 			=> sanitize_text_field($fields['first_last_name'] ?? NULL),
					'telefon' 				=> sanitize_text_field($fields['phone'] ?? NULL),
					'email' 				=> sanitize_email($fields['email'] ?? NULL)
				],
			] ) ) {
				$response = json_decode($response['body']);
				
				if( $response->result ?? NULL ) {
					if( isset($_POST['missing-persons']) ) {
						unset($_POST['missing-persons']);
					}
					
					return true;
				}
			}
			
			return false;
		}
		
		return NULL;
	}
	
	// Clean and escape data
	private static function senitize ( $str ) {
		
		if( is_array($str) ) {
			$array = array();
			foreach( $str as $k => $v ) {
				$array[$k] = self::senitize( $v );
			}
			return $array;
		} else if( empty($str) && $str != 0 ) {
			return NULL;
		} else if ( is_numeric($str) ) {
			if( absint($str) == $str ) {
				return absint($str);
			} else if( floatval($str) == $str ) {
				return floatval($str);
			}
		} else if ( is_string($str) ) {
			if ( preg_match('/[a-z0-9\.-_]+\@[a-z0-9\.-_]+/i', $str) ) {
				return sanitize_email($str);
			} else if ( preg_match('/https?\:\/\//i', $str) ) {
				return sanitize_url($str);
			}
		}
		
		$str = html_entity_decode($str);
		if(preg_match('/<\/?[a-z][\s\S]*>/i', $str)) {
			$str = wp_kses(
				$str,
				wp_kses_allowed_html('post')
			);
		} else {
			$str = sanitize_text_field( $str );
		}
		
		if( in_array($str, array('-', '/')) ) {
			$str = NULL;
		}
		
		return $str;
	}
	
} endif;