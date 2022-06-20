<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_sidebar_container', $missing_response);

foreach($missing_response->data as $i=>$missing) :
$missing = new Registar_Nestalih_Render($missing, $i);
?>
<a class="row missing-person-widget" href="<?php echo esc_url($missing->profile_url()); ?>">
	<div class="missing-person-img-top col col-6 col-sm-6 col-md-5 col-lg-5">
		<img class="missing-person-photo" src="<?php echo esc_url($missing->profile_image()); ?>" alt="<?php echo esc_attr(esc_html($missing->ime_prezime)); ?>">
	</div>
	<div class="missing-person-block col col-6 col-sm-6 col-md-7 col-lg-7">
		<h4 class="missing-person-title"><?php echo esc_html($missing->ime_prezime); ?></h4>
		<div class="meta">
			<p><?php _e('Gender:', 'registar-nestalih'); ?> <span><?php echo esc_html($missing->pol); ?></span></p>
			<p><?php _e('Age:', 'registar-nestalih'); ?> <span><?php echo esc_html($missing->age()); ?></span></p>
		</div>
		<div class="missing-person-text"><?php _e('Do you know anything about this person?', 'registar-nestalih'); ?></div>
	</div>
</a>
<?php
endforeach;

if( isset($missing_response->widget) && ($missing_response->widget['see_others'] ?? NULL) ) :
?>
<a href="<?php echo get_the_permalink( Registar_Nestalih_Options::get('main-page', 0) ); ?>" class="row missing-person-see-all"><div class="col col-12 col-sm-12"><?php 
	_e('Look at the others', 'registar-nestalih');
?></div></a>
<?php 
endif;

do_action('registar_nestalih_after_sidebar_container', $missing_response);