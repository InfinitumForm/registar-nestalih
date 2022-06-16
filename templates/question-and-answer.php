<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_question_and_answer', $missing_response); ?>
<div class="registar-nestalih-qa-page">
	<?php if($missing_response) : foreach($missing_response as $fetch) : $anchor = sanitize_title($fetch->question); ?>
	<h3 id="<?php echo $anchor; ?>"><a name="<?php echo $anchor; ?>" href="#<?php echo $anchor; ?>">&#9839;</a> <?php echo esc_html($fetch->question); ?></h3>
	<div><?php echo wp_kses_post($fetch->answer); ?></div>
	<?php endforeach; else : ?>
	<p style="padding:25px;"><?php _e('There is no questions and answers.', 'registar-nestalih') ?></p>
	<?php
	endif; ?>
</div>
<?php do_action('registar_nestalih_after_question_and_answer', $missing_response);