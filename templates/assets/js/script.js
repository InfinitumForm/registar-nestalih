(function($){
	$(document).ready(function(){
		// Add orientation classes to the images
		$('.missing-item-image > img, img.missing-person-photo').each(function(){
			var $image = $(this),
				$w = $image.width(),
				$h = $image.height();
				
			if( $h > $w ) {
				$image.addClass('img-portrait');
			} else if( $h === $w ) {
				$image.addClass('img-square');
			} else {
				$image.addClass('img-landscape');
			}
		});
	});
}(jQuery || window.jQuery));