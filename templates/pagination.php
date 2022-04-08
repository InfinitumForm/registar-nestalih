<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response, $last_page, $current_url;

?>
<?php if($last_page > 1 || true) : ?>
<div class="col-sm-12">
	<div class="bs-pagination next_prev clearfix">
		<a class="btn-bs-pagination prev<?php
			echo !((absint($missing_response->current_page)-1) > 0) ? ' disabled' : '';
		?>" rel="prev" title="<?php esc_attr_e('Previous', 'registar-nestalih'); ?>" href="<?php
			if((absint($missing_response->current_page)-1) > 0) {
				echo add_query_arg([
					'lista'=>(absint($missing_response->current_page)-1)
				], $current_url);
			} else {
				echo 'javascript:void(0);';
			}
		?>"><i class="fa fa-angle-left" aria-hidden="true"></i> <?php _e('Previous', 'registar-nestalih'); ?></a>

		<a rel="next" class="btn-bs-pagination next<?php
			echo !((absint($missing_response->current_page)+1) <= $last_page) ? ' disabled' : '';
		?>" title="<?php esc_attr_e('Next', 'registar-nestalih'); ?>" href="<?php
			if((absint($missing_response->current_page)+1) <= $last_page) {
				echo add_query_arg([
					'lista'=>(absint($missing_response->current_page)+1)
				], $current_url);
			} else {
				echo 'javascript:void(0);';
			}
		?>"><?php _e('Next', 'registar-nestalih'); ?> <i class="fa fa-angle-right" aria-hidden="true"></i></a>

		<span class="bs-pagination-label label-light"><?php
			printf(__('%d of %d', 'registar-nestalih'), absint($missing_response->current_page), absint($last_page));
		?></span>
	</div>
</div>
<?php endif; ?>