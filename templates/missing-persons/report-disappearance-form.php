<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

$POST 					= ($_POST['report-missing-person'] ?? NULL);
$first_name 			= sanitize_text_field($POST['first_name'] ?? NULL);
$last_name 				= sanitize_text_field($POST['last_name'] ?? NULL);
$gender 				= sanitize_text_field($POST['gender'] ?? NULL);
$date_of_birth 			= sanitize_text_field($POST['date_of_birth'] ?? NULL);
$place_of_birth			= sanitize_text_field($POST['place_of_birth'] ?? NULL);
$citizenship 			= sanitize_text_field($POST['citizenship'] ?? NULL);
$residence 				= sanitize_text_field($POST['residence'] ?? NULL);
$height 				= floatval($POST['height'] ?? NULL);
$weight 				= floatval($POST['weight'] ?? NULL);
$hair_color 			= sanitize_text_field($POST['hair_color'] ?? NULL);
$eye_color 				= sanitize_text_field($POST['eye_color'] ?? NULL);
$date_of_disappearance 	= sanitize_text_field($POST['date_of_disappearance'] ?? NULL);
$place_of_disappearance = sanitize_text_field($POST['place_of_disappearance'] ?? NULL);
$date_of_report 		= sanitize_text_field($POST['date_of_report'] ?? NULL);
$police_station 		= sanitize_text_field($POST['police_station'] ?? NULL);
$additional_information	= sanitize_textarea_field($POST['additional_information'] ?? NULL);
$disappearance_description 	= sanitize_textarea_field($POST['disappearance_description'] ?? NULL);
$circumstances_disappearance 	= sanitize_textarea_field($POST['circumstances_disappearance'] ?? NULL);
$applicant_name 		= sanitize_text_field($POST['applicant_name'] ?? NULL);
$applicant_telephone 	= sanitize_text_field($POST['applicant_telephone'] ?? NULL);
$applicant_email 		= sanitize_email($POST['applicant_email'] ?? NULL);
$applicant_relationship = sanitize_text_field($POST['applicant_relationship'] ?? NULL);
$external_link 			= sanitize_url($POST['external_link'] ?? NULL);

do_action('registar_nestalih_before_report_disappearance_form_container', $missing_response); ?>
<h3 id="missing-persons-form-title"><label for="report-missing-person-first-name"><?php _e('Report missing person', 'registar-nestalih'); ?></label></h3>
<form method="post" id="report-missing-person-form">
	<fieldset class="row">
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-first-name"><?php _e('First Name', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[first_name]" id="report-missing-person-first-name" tabindex="1" value="<?php echo esc_attr($first_name); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-last-name"><?php _e('Last name', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[last_name]" id="report-missing-person-last-name" tabindex="2" value="<?php echo esc_attr($last_name); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-gender"><?php _e('Gender', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<select class="form-control required" name="report-missing-person[gender]" id="report-missing-person-gender" tabindex="3">
					<option value="Muško"<?php selected('Muško', $gender); ?>><?php _e('Male', 'registar-nestalih'); ?></option>
					<option value="Žensko"<?php selected('Žensko', $gender); ?>><?php _e('Female', 'registar-nestalih'); ?></option>
					<option value="Nedefinisano"<?php selected('Nedefinisano', $gender); ?>><?php echo _x('Undefined', 'undefined gender in the form.', 'registar-nestalih'); ?></option>
				</select>
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-date-of-birth"><?php _e('Date of birth', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="date" class="form-control required" name="report-missing-person[date_of_birth]" id="report-missing-person-date-of-birth" tabindex="4" value="<?php echo esc_attr($date_of_birth); ?>" placeholder="dd.mm.yyyy"
        min="<?php echo esc_attr(date('Y-m-d', strtotime('-100 Years'))); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-4 col-lg-4">
			<div class="form-group">
				<label for="report-missing-person-place-of-birth"><?php _e('Place of birth', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[place_of_birth]" id="report-missing-person-place-of-birth" tabindex="5" value="<?php echo esc_attr($place_of_birth); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-4 col-lg-4">
			<div class="form-group">
				<label for="report-missing-person-citizenship"><?php _e('Citizenship', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[citizenship]" id="report-missing-person-citizenship" tabindex="6" value="<?php echo esc_attr($citizenship); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-4 col-lg-4">
			<div class="form-group">
				<label for="report-missing-person-residence"><?php _e('Residence', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[citizenship]" id="report-missing-person-residence" tabindex="7" value="<?php echo esc_attr($residence); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-height"><?php _e('Height (in centimeters)', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="number" step="0.1" min="0" max="300" class="form-control required" name="report-missing-person[height]" id="report-missing-person-height" tabindex="8" value="<?php echo esc_attr($height); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-weight"><?php _e('Weight (in kilograms)', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="number" step="0.1" min="0" max="300" class="form-control required" name="report-missing-person[weight]" id="report-missing-person-weight" tabindex="9" value="<?php echo esc_attr($weight); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-hair-color"><?php _e('Hair Color', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[hair_color]" id="report-missing-person-hair-color" tabindex="10" value="<?php echo esc_attr($hair_color); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-eye-color"><?php _e('Eye Color', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[eye_color]" id="report-missing-person-eye-color" tabindex="11" value="<?php echo esc_attr($eye_color); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12">
			<div class="form-group">
				<label for="report-missing-person-image"><?php _e('Picture of a missing person', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="file" class="form-control-file required"  name="report-missing-person[image]" id="report-missing-person-image" tabindex="12">
			</div>
		</div>
	</fieldset>
	
	<fieldset class="row">
		<legend class="col col-12 col-sm-12"><?php _e('Information about the disappearance', 'registar-nestalih'); ?><br><small><?php _e('Please be precise with the information.', 'registar-nestalih'); ?></small></legend>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-date-of-disappearance"><?php _e('Date of disappearance', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="date" class="form-control required" name="report-missing-person[date_of_disappearance]" id="report-missing-person-date-of-disappearance" tabindex="13" value="<?php echo esc_attr($date_of_disappearance); ?>" placeholder="dd.mm.yyyy"
        min="<?php echo esc_attr(date('Y-m-d', strtotime('-100 Years'))); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-place-of-disappearance"><?php _e('Place of disappearance', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[place_of_disappearance]" id="report-missing-person-place-of-disappearance" tabindex="14" value="<?php echo esc_attr($place_of_disappearance); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-date-of-report"><?php _e('Date of report', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="date" class="form-control required" name="report-missing-person[date_of_report]" id="report-missing-person-date-of-report" tabindex="15" value="<?php echo esc_attr($date_of_report); ?>" placeholder="dd.mm.yyyy"
        min="<?php echo esc_attr(date('Y-m-d', strtotime('-100 Years'))); ?>" max="<?php echo esc_attr(date('Y-m-d')); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-police-station"><?php _e('Police station', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[police_station]" id="report-missing-person-police-station" tabindex="16" value="<?php echo esc_attr($police_station); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12">
			<div class="form-group">
				<label for="report-missing-person-additional-information"><?php _e('Additional information', 'registar-nestalih'); ?></label>
				<textarea class="form-control" name="report-missing-person[additional_information]" id="report-missing-person-additional-information" tabindex="17" rows="6">
					<?php echo wp_kses_post($additional_information); ?>
				</textarea>
			</div>
		</div>
		
		<div class="col col-12 col-sm-12">
			<div class="form-group">
				<label for="report-missing-person-disappearance-description"><?php _e('Disappearance description', 'registar-nestalih'); ?></label>
				<textarea class="form-control" name="report-missing-person[disappearance_description]" id="report-missing-person-disappearance-description" tabindex="18" rows="6">
					<?php echo wp_kses_post($disappearance_description); ?>
				</textarea>
			</div>
		</div>
		
		<div class="col col-12 col-sm-12">
			<div class="form-group">
				<label for="report-missing-person-circumstances-disappearance"><?php _e('Circumstances of disappearance', 'registar-nestalih'); ?></label>
				<textarea class="form-control" name="report-missing-person[circumstances_disappearance]" id="report-missing-person-circumstances-disappearance" tabindex="19" rows="6">
					<?php echo wp_kses_post($circumstances_disappearance); ?>
				</textarea>
			</div>
		</div>
	</fieldset>
	
	<fieldset class="row">
		<legend class="col col-12 col-sm-12"><?php _e('Applicant information', 'registar-nestalih'); ?><br><small><?php _e('This information is hidden from the global public and serves for direct contact.', 'registar-nestalih'); ?></small></legend>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-applicant-name"><?php _e('Name and last name of the applicant', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[applicant_name]" id="report-missing-person-applicant-name" tabindex="20" value="<?php echo esc_attr($applicant_name); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-applicant-telephone"><?php _e('Applicant\'s telephone', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[applicant_telephone]" id="report-missing-person-applicant-telephone" tabindex="21" value="<?php echo esc_attr($applicant_telephone); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-applicant-email"><?php _e('Email of the applicant', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[applicant_email]" id="report-missing-person-applicant-email" tabindex="22" value="<?php echo esc_attr($applicant_email); ?>">
			</div>
		</div>
		<div class="col col-12 col-sm-12 col-md-6 col-lg-6">
			<div class="form-group">
				<label for="report-missing-person-applicant-relationship"><?php _e('Relationship with a missing person', 'registar-nestalih'); ?> <span class="asterisk-required">*</span></label>
				<input type="text" class="form-control required" name="report-missing-person[applicant_relationship]" id="report-missing-person-applicant-relationship" tabindex="23" value="<?php echo esc_attr($applicant_relationship); ?>">
			</div>
		</div>
		
		<div class="col col-12 col-sm-12">
			<div class="form-group">
				<label for="report-missing-person-external-link"><?php _e('External link', 'registar-nestalih'); ?></label>
				<input type="url" class="form-control" name="report-missing-person[external_link]" id="report-missing-person-external-link" tabindex="24" value="<?php echo esc_url($external_link); ?>">
			</div>
		</div>
		
	</fieldset>
</form>
<?php do_action('registar_nestalih_after_report_disappearance_form_container', $missing_response);