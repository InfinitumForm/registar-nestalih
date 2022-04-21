<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_contact_form_container', $missing_response); ?>
<h3 id="missing-persons-form-title"><label for="missing-persons-message"><?php _e('Do you know anything about this person?', Registar_Nestalih::TEXTDOMAIN); ?></label></h3>
<form method="post" id="missing-persons-form">
	<fieldset class="row">
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<div class="form-group">
				<textarea name="missing-persons[message]" id="missing-persons-message" class="form-control required" tabindex="1" rows="6" placeholder="<?php esc_attr_e('Enter all information about this person in detail...', Registar_Nestalih::TEXTDOMAIN); ?>"><?php echo esc_html(isset($_POST['missing-persons']) ? ($_POST['missing-persons']['message'] ?? '') : ''); ?></textarea>
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<div class="form-group">
				<label for="missing-persons-first-name"><?php _e('First and Last name', Registar_Nestalih::TEXTDOMAIN); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="missing-persons[first_last_name]" id="missing-persons-first-name" placeholder="<?php esc_attr_e('Your First and Last name', Registar_Nestalih::TEXTDOMAIN); ?>" tabindex="2" value="<?php echo esc_attr(isset($_POST['missing-persons']) ? ($_POST['missing-persons']['first_last_name'] ?? '') : ''); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="missing-persons-email"><?php _e('Your e-mail', Registar_Nestalih::TEXTDOMAIN); ?> <span class="asterisk-required">*</span></label>
				<input type="email" class="form-control required" name="missing-persons[email]" id="missing-persons-email" tabindex="3" value="<?php echo esc_attr(isset($_POST['missing-persons']) ? ($_POST['missing-persons']['email'] ?? '') : ''); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="missing-persons-phone"><?php _e('Your phone number', Registar_Nestalih::TEXTDOMAIN); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="missing-persons[phone]" id="missing-persons-phone" tabindex="4" value="<?php echo esc_attr(isset($_POST['missing-persons']) ? ($_POST['missing-persons']['phone'] ?? '') : ''); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12" id="missing-persons-form-errors"></div>
		<div class="col col-12 col-sm-12 col-md-12 col-lg-12">
			<button type="submit" class="btn btn-primary" tabindex="5"><?php _e('Send Message', Registar_Nestalih::TEXTDOMAIN); ?></button>
			<input type="hidden" name="missing-persons[nonce]" value="<?php echo esc_attr( wp_create_nonce( 'missing-persons-form-' . $missing_response->id() ) ); ?>">
			<input type="hidden" name="missing-persons[ID]" value="<?php echo $missing_response->id(); ?>">
		</div>
	</fieldset>
</form>
<?php do_action('registar_nestalih_after_contact_form_container', $missing_response);