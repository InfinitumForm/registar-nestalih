<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wp_query, $missing_response, $action_url;

$search = sanitize_text_field( $wp_query->get( 'registar_nestalih_search' ) ?? '' );

do_action('registar_nestalih_before_search_container', $missing_response, $action_url);
?>
<div class="row missing-persons-row pt-3 pb-3" id="missing-persons-search-form">
	<div class="col col-sm-12">
		<form action="<?php echo esc_url($action_url); ?>" method="post">
			<div class="input-group">
				<input type="text" class="form-control" placeholder="<?php esc_attr_e('Search by first name, last name, location...', 'registar-nestalih'); ?>" name="registar_nestalih_search" value="<?php echo esc_attr($search); ?>">
				<span class="input-group-btn">
					<button class="btn btn-primary" type="submit"><?php _e('Search', 'registar-nestalih'); ?></button>
				</span>
			</div>
		</form>
	</div>
</div>
<?php do_action('registar_nestalih_after_search_container', $missing_response, $action_url);