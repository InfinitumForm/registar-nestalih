<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_main_container', $missing_response);
?>
<div class="row missing-persons-row" id="missing-persons-main-container">
	<?php
		do_action('registar_nestalih_breadcrumb', $missing_response);
	
		foreach($missing_response->data as $i=>$missing) :
		$missing = new Registar_Nestalih_Render($missing, $i);
	?>
	<div class="col col-sm-12 col-md-6 col-lg-4 missing-item missing-item-<?php echo absint($i); ?> missing-item-id-<?php echo absint($missing->id); ?>" id="missing-item-<?php echo sanitize_title($missing->ime_prezime); ?>">
		<div class="card">
		<?php if(!empty($missing->icon)) : ?>
			<a class="missing-item-image card-img-top" href="<?php echo $missing->profile_url(); ?>" title="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"><img src="<?php echo esc_url($missing->icon); ?>" alt="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>"></a>
		<?php endif; ?>
			<div class="card-body">
				<h3 class="card-title missing-item-title"><a href="<?php echo $missing->profile_url(); ?>"><?php echo esc_html($missing->ime_prezime); ?></a></h3>
				
				<ul class="list-group missing-item-info">
					<li class="list-group-item"><b><?php _e('Gender:', 'registar-nestalih'); ?></b> <span><?php echo esc_html($missing->pol); ?></span></li>
					<li class="list-group-item"><b><?php _e('Age:', 'registar-nestalih'); ?></b> <span><?php echo $missing->age(); ?></span></li>
					<li class="list-group-item"><b><?php _e('Place of disappearance:', 'registar-nestalih'); ?></b> <span><?php echo esc_html($missing->mesto_nestanka ? $missing->mesto_nestanka : __('(undefined)', 'registar-nestalih')); ?></span></li>
				</ul>
			</div>
		</div>	
	</div>
	<?php
		endforeach;
		
		do_action('registar_nestalih_pagination', $missing_response);
	?>
</div>
<?php do_action('registar_nestalih_after_main_container', $missing_response);