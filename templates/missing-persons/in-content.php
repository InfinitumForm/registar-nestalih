<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_in_content', $missing_response);

$title = sprintf(
	(
		$missing_response->is_female() 
		? _x( '%s (%d) is missing', 'female title', 'registar-nestalih') 
		: _x( '%s (%d) is missing', 'male title', 'registar-nestalih')
	),
	$missing_response->ime_prezime,
	$missing_response->age()
);

$description = '';
if( !empty($missing_response->okolnosti_nestanka) && strlen($missing_response->okolnosti_nestanka) < 160 ) {
	$description = $missing_response->okolnosti_nestanka;
	
	if( !empty($missing_response->dodatne_informacije) ) {
		$description.= '<br><br><b>' . __('Additional information:', 'registar-nestalih') . '</b><br>' . $missing_response->dodatne_informacije;
	}
	
} else if( !empty($missing_response->okolnosti_nestanka) ) {
	$description = $missing_response->okolnosti_nestanka;
} else if( !empty($missing_response->opis_nestanka) && strlen($missing_response->opis_nestanka) < 160 ) {
	$description = $missing_response->opis_nestanka;
	
	if( !empty($missing_response->dodatne_informacije) ) {
		$description.= '<br><br><b>' . __('Additional information:', 'registar-nestalih') . '</b><br>' . $missing_response->dodatne_informacije;
	}
	
} else if( !empty($missing_response->opis_nestanka) ) {
	$description = $missing_response->opis_nestanka;
} else if( !empty($missing_response->dodatne_informacije) ) {
	$description = $missing_response->dodatne_informacije;
}

?>
<div class="registar-nestalih-container">
	<div class="row">
		<div class="col col-12 col-sm-4 col-md-4 col-lg-3 text-center">
			<a href="<?php echo esc_url( $missing_response->profile_url() ); ?>">
				<div class="missing-item-image" title="<?php echo esc_attr(esc_html($missing_response->ime_prezime)); ?>"><img src="<?php echo esc_url( $missing_response->profile_image() ); ?>" alt="<?php echo esc_attr(esc_html($missing_response->ime_prezime)); ?>"></div>
			</a>
		</div>
		<div class="col col-12 col-sm-8 col-md-8 col-lg-9">
			<h3 class="missing-person-title"><a href="<?php echo esc_url( $missing_response->profile_url() ); ?>"><?php echo esc_html($title); ?></a></h3>
			<?php if(!empty($description)) : ?>
			<div class="missing-person-content"><?php echo nl2br(wp_kses_post($description)); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php do_action('registar_nestalih_after_in_content', $missing_response);