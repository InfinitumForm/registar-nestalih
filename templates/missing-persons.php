<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_main_container', $missing_response);
?>
<div class="row missing-persons-row" id="missing-persons-main-container">
	<?php
		do_action('registar_nestalih_breadcrumb', $missing_response);
	
		foreach($missing_response->data as $i=>$post) :
		$age = (date('Y') - date('Y', strtotime($post->datum_rodjenja . ' 01:00:01')));
	?>
	<div class="col col-md-4 missing-item missing-item-<?php echo absint($i); ?> missing-item-id-<?php echo absint($post->id); ?>" id="missing-item-<?php echo sanitize_title($post->ime_prezime); ?>">
	
		<a class="missing-item-image" href="<?php echo esc_url($post->share_link); ?>" title="<?php echo esc_attr(esc_html($post->ime_prezime)); ?>" target="_blank"><img src="<?php echo esc_url($post->icon); ?>" alt="<?php echo esc_attr(esc_html($post->ime_prezime)); ?>"></a>
		
		<h3 class="missing-item-title"><a href="<?php echo esc_url($post->share_link); ?>" target="_blank"><?php echo esc_html($post->ime_prezime); ?></a></h3>
		
		<ul class="missing-item-info">
			<li><b><?php _e('Gender:', 'registar-nestalih'); ?></b> <span><?php echo esc_html($post->pol); ?></span></li>
			<li><b><?php _e('Age:', 'registar-nestalih'); ?></b> <span><?php echo absint($age); ?></span></li>
			<li><b><?php _e('Date of Birth:', 'registar-nestalih'); ?></b> <span><?php echo date_i18n( get_option( 'date_format' ), strtotime($post->datum_rodjenja . ' 01:00:01')); ?></span></li>
			<li><b><?php _e('Missing date:', 'registar-nestalih'); ?></b> <span><?php echo date_i18n( get_option( 'date_format' ), strtotime($post->datum_nestanka . ' 01:00:01')); ?></span></li>
			<li><b><?php _e('Reported date:', 'registar-nestalih'); ?></b> <span><?php echo date_i18n( get_option( 'date_format' ), strtotime($post->datum_prijave . ' 01:00:01')); ?></span></li>
			<li><b><?php _e('Place of disappearance:', 'registar-nestalih'); ?></b> <span><?php echo esc_html($post->mesto_nestanka ? $post->mesto_nestanka : __('(undefined)', 'registar-nestalih')); ?></span></li>
			<li><b><?php _e('Residence:', 'registar-nestalih'); ?></b> <span><?php echo esc_html($post->prebivaliste ? $post->prebivaliste : __('(undefined)', 'registar-nestalih')); ?></span></li>
		</ul>
		
	</div>
	<?php
		endforeach;
		
		do_action('registar_nestalih_pagination', $missing_response);
	?>
</div>
<?php do_action('registar_nestalih_after_main_container', $missing_response);