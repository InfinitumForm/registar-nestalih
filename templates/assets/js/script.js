(function($){
	$(document).ready(function(){
		// Add orientation classes to the images
		$('.missing-item-image > img, img.missing-person-photo, #missing-persons-single-container .missing-person-personal-image > .missing-item-image > img').each(function(){
			$(this).load(function(){
				var $image = $(this);
				
				setTimeout(function(){
					var $w = $image.width(),
						$h = $image.height();
					if( $h > $w ) {
						$image.addClass('img-portrait');
					} else if( $h === $w ) {
						$image.addClass('img-square');
					} else {
						$image.addClass('img-landscape');
					}
				}, 100);
			});
		});
	});
	
	$(document).on('submit', '#missing-persons-form', function(e){
		var $this = $(this),
			$has_error = false;
		
		$this.find(".has-error").removeClass('has-error');
		$('#missing-persons-form-errors').html('');
		
		$this.find("textarea.required, input.required").each(function() {
			var $input = $(this);
			if( $input.val().length <= 0 ) {
				$has_error = true;
				$input.addClass('has-error');
			}
		});
		
		if( $has_error ) {
			e.preventDefault();
			$('#missing-persons-form-errors').html('<div class="alert alert-danger" role="alert">' + registar_nestalih.label.form_error + '</div>');
			return;
		}
	});
	
}(jQuery || window.jQuery));