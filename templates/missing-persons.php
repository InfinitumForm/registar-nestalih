<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

$target_blank = (Registar_Nestalih_Options::get('open-in-new-window', 0) == 1 ? ' target="blank"' : ''); 

do_action('registar_nestalih_before_main_container', $missing_response);
?>
<div class="row missing-persons-row pt-3 pb-3" id="missing-persons-main-container">
	<?php
		do_action('registar_nestalih_breadcrumb', $missing_response);
	
		foreach($missing_response->data as $i=>$missing) :
		$missing = new Registar_Nestalih_Render($missing, $i);
	?>
	<div class="col col-12 col-xs-12 col-sm-6 col-md-4 col-lg-3 col-xl-3 missing-item missing-item-<?php echo absint($i); ?> missing-item-id-<?php echo absint($missing->id); ?>" id="missing-item-<?php echo sanitize_title($missing->ime_prezime); ?>">
		<div class="card">
			<a class="missing-item-image card-img-top" href="<?php echo $missing->profile_url(); ?>" title="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"<?php echo $target_blank; ?>>
				<img src="<?php echo $missing->profile_image(); ?>" alt="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>">
			</a>
			<div class="card-body">
				<h3 class="card-title missing-item-title">
					<a href="<?php echo $missing->profile_url(); ?>"<?php echo $target_blank; ?>><?php echo esc_html($missing->ime_prezime); ?></a>
				</h3>
				<ul class="list-group missing-item-info">
					<li class="list-group-item"><b><?php _e('Gender:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo esc_html($missing->pol); ?></span></li>
					<li class="list-group-item"><b><?php _e('Age:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo $missing->age(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Place of disappearance:', Registar_Nestalih::TEXTDOMAIN); ?></b> <span><?php echo esc_html($missing->mesto_nestanka ? $missing->mesto_nestanka : __('(undefined)', Registar_Nestalih::TEXTDOMAIN)); ?></span></li>
				</ul>
				<a class="btn btn-primary" href="<?php echo $missing->profile_url(); ?>" title="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"<?php echo $target_blank; ?>>
					<?php _e('More Informations', Registar_Nestalih::TEXTDOMAIN); ?>
				</a>
			</div>
		</div>	
	</div>
	<?php
		endforeach;
		
		do_action('registar_nestalih_pagination', $missing_response);
	?>
</div>
<?php do_action('registar_nestalih_after_main_container', $missing_response);