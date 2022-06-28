<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

$message = sanitize_textarea_field($_POST['missing-persons']['message'] ?? '');
$message = wp_kses($message, wp_kses_allowed_html('post'));

$first_last_name = sanitize_text_field($_POST['missing-persons']['first_last_name'] ?? '');
$email = sanitize_email($_POST['missing-persons']['email'] ?? '');
$phone = sanitize_text_field($_POST['missing-persons']['phone'] ?? '');

do_action('registar_nestalih_before_contact_form_container', $missing_response); ?>
<div class="clearfix"></div>
<hr class="hr mt-5 mb-5">
<h3 id="missing-persons-form-title"><label for="missing-persons-message"><?php _e('Do you know anything about this person?', 'registar-nestalih'); ?></label></h3>
<form method="post" id="missing-persons-form">
	<fieldset class="row">
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<div class="form-group">
				<textarea name="missing-persons[message]" id="missing-persons-message" class="form-control required" tabindex="1" rows="6" placeholder="<?php esc_attr_e('Enter all information about this person in detail...', 'registar-nestalih'); ?>"><?php echo esc_html($message); ?></textarea>
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<div class="form-group">
				<label for="missing-persons-first-name"><?php _e('First and Last name', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="missing-persons[first_last_name]" id="missing-persons-first-name" placeholder="<?php esc_attr_e('Your First and Last name', 'registar-nestalih'); ?>" tabindex="2" value="<?php echo esc_attr($first_last_name); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="missing-persons-email"><?php _e('Your e-mail', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="email" class="form-control required" name="missing-persons[email]" id="missing-persons-email" tabindex="3" value="<?php echo esc_attr($email); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="missing-persons-phone"><?php _e('Your phone number', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="missing-persons[phone]" id="missing-persons-phone" tabindex="4" value="<?php echo esc_attr($phone); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12" id="missing-persons-form-errors"></div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<button type="submit" class="btn btn-primary" tabindex="5"><?php _e('Send Message', 'registar-nestalih'); ?></button>
			<input type="hidden" name="missing-persons[nonce]" value="<?php echo esc_attr( wp_create_nonce( 'missing-persons-form-' . $missing_response->id() ) ); ?>">
			<input type="hidden" name="missing-persons[ID]" value="<?php echo absint( $missing_response->id() ); ?>">
		</div>
	</fieldset>
</form>
<?php do_action('registar_nestalih_after_contact_form_container', $missing_response);