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
	
	public function __construct( $data, $index = 0 ) {
		
		$this->index = $index;
		$this->date_format = get_option( 'date_format' );
		
		if( empty($data) ) {
			return $this;
		}
		
		if( is_array($data) ) {
			$data = (object)$data;
		}
		
		foreach($data as $key => $value) {
			
			if( property_exists($this, $key) ) {
				$this->{$key} = $value;
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
	
	// Generate URL
	public function profile_url(){
		if( empty($this->id) ) {
			return '#';
		}
		
		if( get_option('permalink_structure') ) {
			$page_link = get_page_link( get_the_ID() );
			$url = sprintf(
				'%s/osoba/%d/%s',
				rtrim($page_link, '/'),
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
		return end(explode(' ', $this->ime_prezime));
	}
	
	// Generate age
	public function age(){
		if( empty($this->datum_rodjenja) ) {
			return NULL;
		}
		
		return (date('Y') - date('Y', strtotime($this->datum_rodjenja . ' 01:00:01')));
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
	
} endif;