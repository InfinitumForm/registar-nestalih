<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_amber_alert', $missing_response); ?>
<div class="registar-nestalih-qa-page">
	<?php if($missing_response) : foreach($missing_response as $fetch) : $anchor = sanitize_title($fetch->question); ?>
	<h3 id="<?php echo $anchor; ?>"><a name="<?php echo $anchor; ?>" href="#<?php echo $anchor; ?>">&#9839;</a> <?php echo esc_html($fetch->question); ?></h3>
	<div><?php
		echo wp_kses_post($fetch->answer);
	if( !empty($fetch->icon) ) : ?>
		<img src="<?php echo esc_url($fetch->icon); ?>" alt="<?php echo esc_attr($fetch->question); ?>">
	<?php endif; ?></div>
	<?php endforeach; else : ?>
	<p style="padding:25px;"><?php _e('There is no informations.', 'registar-nestalih') ?></p>
	<?php endif; ?>
</div>
<?php do_action('registar_nestalih_after_amber_alert', $missing_response);