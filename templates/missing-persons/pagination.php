<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response, $last_page, $next_page, $prev_page, $current_page;

if($last_page > 1) : ?>
<nav class="col-sm-12 mt-3" id="missing-persons-pagination">
	<div class="bs-pagination next_prev clearfix">
		<a class="btn-bs-pagination prev<?php
			echo !($current_page > 1) ? ' disabled' : '';
		?>" rel="prev" title="<?php esc_attr_e('Previous', 'registar-nestalih'); ?>" href="<?php
			if($current_page > 1) {
				echo esc_url($prev_page);
			} else {
				echo 'javascript:void(0);';
			}
		?>"<?php
			echo !($current_page > 1) ? ' disabled' : '';
		?>><i class="fa fa-angle-left" aria-hidden="true"></i> <?php _e('Previous', 'registar-nestalih'); ?></a>

		<span class="bs-pagination-label label-light"><?php
			printf(__('%d of %d', 'registar-nestalih'), absint($missing_response->current_page), absint($last_page));
		?></span>

		<a rel="next" class="btn-bs-pagination next<?php
			echo ($current_page >= $last_page) ? ' disabled' : '';
		?>" title="<?php esc_attr_e('Next', 'registar-nestalih'); ?>" href="<?php
			if($current_page < $last_page) {
				echo esc_url($next_page);
			} else {
				echo 'javascript:void(0);';
			}
		?>"<?php
			echo ($current_page >= $last_page) ? ' disabled' : '';
		?>><?php _e('Next', 'registar-nestalih'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>
	</div>
</nav>
<?php endif; ?>