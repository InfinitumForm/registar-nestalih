<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response, $last_page, $next_page, $prev_page;

//	echo '<pre>', var_dump($last_page, $next_page, $prev_page), '</pre>';

if($last_page > 1) : ?>
<nav class="col-sm-12 mt-3">
	<div class="bs-pagination next_prev clearfix">
		<a class="btn-bs-pagination prev<?php
			echo !($prev_page > 0) ? ' disabled' : '';
		?>" rel="prev" title="<?php esc_attr_e('Previous', 'registar-nestalih'); ?>" href="<?php
			if($prev_page > 0) {
				echo $prev_page;
			} else {
				echo 'javascript:void(0);';
			}
		?>"><i class="fa fa-angle-left" aria-hidden="true"></i> <?php _e('Previous', 'registar-nestalih'); ?></a>

		<a rel="next" class="btn-bs-pagination next<?php
			echo !($next_page <= $last_page) ? ' disabled' : '';
		?>" title="<?php esc_attr_e('Next', 'registar-nestalih'); ?>" href="<?php
			if($last_page > $next_page) {
				echo $next_page;
			} else {
				echo 'javascript:void(0);';
			}
		?>"><?php _e('Next', 'registar-nestalih'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>

		<span class="bs-pagination-label label-light"><?php
			printf(__('%d of %d', 'registar-nestalih'), absint($missing_response->current_page), absint($last_page));
		?></span>
	</div>
</nav>
<?php endif; ?>