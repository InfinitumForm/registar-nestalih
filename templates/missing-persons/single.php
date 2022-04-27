<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

$missing = new Registar_Nestalih_Render($missing_response);

do_action('registar_nestalih_before_single_container', $missing);
?>
<div class="row missing-persons-row missing-persons-single missing-persons-single-<?php echo $missing->id; ?>" id="missing-persons-single-container">
	<div class="col col-sm-12 col-missing-person col-missing-person-<?php echo $missing->id; ?>" id="missing-person-<?php echo $missing->id; ?>">
	
		<h1 class="missing-person-title"><?php echo esc_html($missing->ime_prezime); ?></h1>
		
		<div class="row missing-person-personal-row">
			<div class="col col-sm-12 col-md-6 col-lg-5 missing-person-personal-image">
				<div class="missing-item-image" title="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"><img src="<?php echo $missing->profile_image(); ?>" alt="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"></div>
			</div>
			<div class="col col-sm-12 col-md-6 col-lg-4">
				<ul class="list-group missing-person-personal-info">
					<li class="list-group-item active">
						<?php _e('Personal information', Registar_Nestalih::TEXTDOMAIN); ?>
					</li>
					<li class="list-group-item"><b><?php _e('First Name:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->first_name(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Last Name:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->last_name(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Gender:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo esc_html($missing->pol); ?></span></li>
					<li class="list-group-item"><b><?php _e('Age:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->age(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Date of Birth:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->birth_date(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Place of Birth:', Registar_Nestalih::TEXTDOMAIN); ?></b> <?php echo $missing->mesto_rodjenja; ?></li>
					<li class="list-group-item"><b><?php _e('Citizenship:', Registar_Nestalih::TEXTDOMAIN); ?></b> <?php echo $missing->drzavljanstvo; ?></li>
					<li class="list-group-item"><b><?php _e('Residence:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo esc_html($missing->prebivaliste ? $missing->prebivaliste : __('(undefined)', Registar_Nestalih::TEXTDOMAIN)); ?></span></li>
				</ul>
			</div>
			<div class="col col-sm-12 col-md-12 col-lg-3">
				<ul class="list-group missing-person-personal-description">
					<li class="list-group-item active">
						<?php _e('Personal description', Registar_Nestalih::TEXTDOMAIN); ?>
					</li>
					<li class="list-group-item"><b><?php _e('Height:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->visina; ?></span></li>
					<li class="list-group-item"><b><?php _e('Weight:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->tezina; ?></span></li>
					<li class="list-group-item"><b><?php _e('Eye color:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->boja_ociju; ?></span></li>
					<li class="list-group-item"><b><?php _e('Heir color:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->boja_kose; ?></span></li>
				</ul>
			</div>
		</div>

		<ul class="list-group missing-person-details-about-missing">
			<li class="list-group-item active">
				<?php _e('Details about missing', Registar_Nestalih::TEXTDOMAIN); ?>
			</li>
			<li class="list-group-item"><b><?php _e('Missing date:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->missing_date(); ?></span></li>
			<li class="list-group-item"><b><?php _e('Place of disappearance:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo esc_html($missing->mesto_nestanka ? $missing->mesto_nestanka : __('(undefined)', Registar_Nestalih::TEXTDOMAIN)); ?></span></li>
			<li class="list-group-item"><b><?php _e('Reported date:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->reporting_date(); ?></span></li>
		</ul>
		
		<ul class="list-group missing-person-other-informations">
			<li class="list-group-item active">
				<?php _e('Other informations', Registar_Nestalih::TEXTDOMAIN); ?>
			</li>
			<li class="list-group-item">
				<b><?php _e('Circumstances of disappearance:', Registar_Nestalih::TEXTDOMAIN); ?></b>
				<br>
				<span><?php echo nl2br(esc_html($missing->okolnosti_nestanka)); ?></span>
			</li>
			<li class="list-group-item">
				<b><?php _e('Description at the time of disappearance:', Registar_Nestalih::TEXTDOMAIN); ?></b>
				<br>
				<span><?php echo nl2br(esc_html($missing->opis_nestanka)); ?></span>
			</li>
			<li class="list-group-item">
				<b><?php _e('Additional information:', Registar_Nestalih::TEXTDOMAIN); ?></b>
				<br>
				<span><?php echo nl2br(esc_html($missing->dodatne_informacije)); ?></span>
			</li>
		</ul>
		
	</div>
</div>
<?php do_action('registar_nestalih_after_single_container', $missing);